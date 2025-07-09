<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class SeedDMS_View_Dashboard extends SeedDMS_Theme_Style
{
	private function decrypt($encrypted_combined_base64, $key)
	{
		$data = base64_decode($encrypted_combined_base64);
		if ($data === false || strlen($data) < 16) {
			return '[INVALID NAME]';
		}
		$iv = substr($data, 0, 16);
		$ciphertext = substr($data, 16);
		$decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
		return $decrypted === false ? '[DECRYPTION FAILED]' : $decrypted;
	}
	// ========================================================================
	// CHART RELATED PROPERTIES AND METHODS
	// ========================================================================
	private $_dashboardChartTypes = [
		'docspermonth',
		'sizepermonth',
		'docsaccumulated',
		'docsperuser',
		'foldersperuser',
		'sizeperuser',
		'docspermimetype',
		'docspercategory',
		'docsperstatus'
	];

	/**
	 * REDESIGNED: dropUpload function
	 *
	 * This function has been updated to use the user-requested visual design.
	 * It integrates the new HTML/CSS for the drop zone while preserving the critical backend logic:
	 * - It correctly identifies the target upload folder from the dashboard settings.
	 * - It verifies user write permissions for that specific folder.
	 * - It renders the drop zone with the ID 'dashboard-drop-zone', which is essential
	 *   for the JavaScript to function correctly.
	 * - It displays clear error messages if the uploader cannot be shown.
	 */
	function dropUpload()
	{ /* {{{ */
		$dms = $this->params['dms'] ?? null;
		$user = $this->params['user'] ?? null;

		// Get max upload size from parameter or fallback to php.ini
		$maxuploadsize = $this->params['maxuploadsize'] ?? ini_get('upload_max_filesize');
		$maxuploadsizeBytes = is_string($maxuploadsize)
			? SeedDMS_Core_File::parse_filesize($maxuploadsize)
			: (int) $maxuploadsize;

		// Hardcoded folder ID for dashboard upload (adjust as needed)
		$dashboardUploadFolderParam = 1;
		// Fetch the folder object
		$folder = null;
		if ($dashboardUploadFolderParam && $dms) {
			if (is_object($dashboardUploadFolderParam) && method_exists($dashboardUploadFolderParam, 'getID')) {
				$folder = $dashboardUploadFolderParam;
			} elseif (is_numeric($dashboardUploadFolderParam)) {
				$folder = $dms->getFolder((int) $dashboardUploadFolderParam);
			}
		}

		// Check access
		$hasWriteAccess = false;
		if ($folder && $user && is_object($folder) && method_exists($folder, 'getAccessMode')) {
			$hasWriteAccess = ($folder->getAccessMode($user) >= M_READWRITE);
		}

		if ($folder && $hasWriteAccess) {
			// Render drop zone with dynamic file size and JS enforcement
			?>
			<div id="dashboard-drop-zone" class="dashed-border alert alert-warning text-center droptarget"
				style="height: 240px; padding: 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed #343a40; border-radius: 0.25rem;"
				data-droptarget="folder_<?php echo $folder->getID(); ?>" data-uploadformtoken="<?php echo createFormKey(''); ?>">

				<!-- Cloud Upload Icon -->
				<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="none" stroke="black"
					stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="transform: scale(2);">
					<path d="M19.35 10.04C18.67 6.59 15.64 4 12 4a6.994 6.994 0 00-6.92 6H4a4 4 0 000 8h16a4 4 0 00-.65-7.96z" />
					<path d="M13 12v4h-2v-4H8l4-4 4 4h-3z" />
				</svg>

				<!-- Upload Instructions -->
				<h4 style="margin-top: 20px; font-size: 18px;">
					<?php echo htmlspecialchars(getMLText('drop_files_here_dashboard', [], 'Drop Files here')); ?>
				</h4>

				<!-- Display file size limit -->
				<h5 class="text-danger" style="font-size: 15px;">
					Max. size: <?php echo SeedDMS_Core_File::format_filesize($maxuploadsizeBytes); ?>
				</h5>


			</div>

			<!-- JavaScript: enforce max file size -->
			<script>
				if (typeof SeedDMSUpload !== 'undefined' && SeedDMSUpload.setMaxFileSize) {
					SeedDMSUpload.setMaxFileSize(<?= (int) $maxuploadsizeBytes ?>);
				}
			</script>
			<?php
		} else {
			// Error: Folder is invalid or access denied
			$message = !$folder
				? getMLText('info_drop_upload_no_folder', [], 'The hardcoded target folder ID is invalid or the folder does not exist.')
				: getMLText('access_denied_to_upload_folder', [], 'You do not have permission to upload to the configured quick upload folder.');

			echo '<div class="alert alert-warning text-center" role="alert" style="padding: 40px; min-height: 240px; display: flex; align-items: center; justify-content: center;">' . htmlspecialchars($message) . '</div>';
		}
	} /* }}} */



	private function getJsDataForChartType($type, $phpData)
	{
		$jsOutputMimic = [];
		if (empty($phpData) || !is_array($phpData)) {
			return $jsOutputMimic;
		}
		if (in_array($type, ['docspermonth', 'sizepermonth'])) {
			foreach ($phpData as $rec) {
				$jsOutputMimic[] = [(string) ($rec['key'] ?? 'N/A'), (float) ($rec['total'] ?? 0)];
			}
		} elseif ($type === 'docsaccumulated') {
			foreach ($phpData as $rec) {
				$jsOutputMimic[] = [(int) ($rec['key'] ?? 0), (int) ($rec['total'] ?? 0)];
			}
		} elseif (in_array($type, ['docsperuser', 'foldersperuser', 'sizeperuser', 'docspermimetype', 'docspercategory', 'docsperstatus'])) {
			foreach ($phpData as $rec) {
				$jsOutputMimic[] = [
					'label' => htmlspecialchars($rec['key'] ?? 'N/A'),
					'data' => [[1, (float) ($rec['total'] ?? 0)]]
				];
			}
		}
		return $jsOutputMimic;
	}

	private function prepareJsChartData()
	{
		$allChartDataPHP = $this->params['allChartData'] ?? [];
		$jsChartDataArray = [];
		$dms = $this->params['dms'] ?? null;

		foreach ($this->_dashboardChartTypes as $type) {
			if (!isset($allChartDataPHP[$type])) {
				$jsChartDataArray[] = ['type' => $type, 'data' => [], 'divId' => 'chart_' . $type];
				continue;
			}
			if (!$this->showChartCondition($type, $dms)) {
				continue;
			}
			$phpDataForType = $allChartDataPHP[$type];
			$jsFormattedData = $this->getJsDataForChartType($type, $phpDataForType);
			
			// Debug output for docspermimetype
			if ($type == 'docspermimetype') {
				error_log("Dashboard View: docspermimetype PHP data: " . print_r($phpDataForType, true));
				error_log("Dashboard View: docspermimetype JS formatted data: " . print_r($jsFormattedData, true));
			}
			
			$jsChartDataArray[] = [
				'type' => $type,
				'data' => $jsFormattedData,
				'divId' => 'chart_' . $type,
			];
		}
		return $jsChartDataArray;
	}

	private function showChartCondition($type, $dms)
	{
		if (!$dms)
			return true;
		if ($type == 'docspercategory') {
			if (method_exists($dms, 'getDocumentCategories') && ($cats = $dms->getDocumentCategories())) {
				return !empty($cats);
			}
			return false;
		}
		return true;
	}

	private function _renderChartAndTable($chartType, $allChartData, $dms)
	{
		if (!isset($allChartData[$chartType])) {
			$allChartData[$chartType] = [];
		}
		if (!$this->showChartCondition($chartType, $dms))
			return;


		$currentChartDataForTable = $allChartData[$chartType];
		$isPieChart = in_array($chartType, ['docsperuser', 'foldersperuser', 'sizeperuser', 'docspermimetype', 'docspercategory', 'docsperstatus']);
		$isBarOrLineChart = in_array($chartType, ['docspermonth', 'sizepermonth', 'docsaccumulated']);

		$chartTitle = getMLText('chart_' . $chartType . '_title', [], ucfirst(str_replace(['docsper', 'sizeper'], ['', ''], $chartType)));

		echo '<div class="chart-container-wrapper" style="margin-bottom:40px; padding:10px; border:1px solid #eee; border-radius:8px; background-color:#fdfdfd; box-shadow:0 2px 4px rgba(0,0,0,0.05);">';
		$this->contentHeading(htmlspecialchars($chartTitle));

		$this->rowStart();
		$chartColumnWidth = ($isPieChart && !$isBarOrLineChart) ? 8 : 12;
		$this->columnStart($chartColumnWidth);
		$this->contentContainerStart('chart-plot-area-' . $chartType, '', 'background-color:#fff; padding:10px; border:1px solid #e0e0e0; border-radius:4px; min-height:420px;');
		?>
		<div id="chart_<?php echo htmlspecialchars($chartType); ?>" style="height:600px; width:100%;" class="chart">
			<p style="text-align:center; padding-top:280px; color:#777;">Loading chart...</p>
		</div>
		<?php
		$this->contentContainerEnd();
		$this->columnEnd();

		if ($isPieChart && !$isBarOrLineChart) {
			$this->columnStart(4);
			$this->contentHeading(getMLText('legend', [], "Legend"));
			$this->contentContainerStart('', 'legend_container_' . $chartType, 'background-color:#fff; padding:10px; border:1px solid #e0e0e0; border-radius:4px; min-height:420px; max-height: 600px; overflow-y: auto;');
			$this->contentContainerEnd();
			$this->columnEnd();
		}
		$this->rowEnd();

		if (!empty($currentChartDataForTable)) {
			if (in_array($chartType, ['docspermonth', 'sizepermonth'])) {
				echo "<div style='color:red;font-weight:bold;'>DEBUG: Custom table logic for $chartType is running</div>";
			} else {
				echo "<div style='color:blue;'>DEBUG: Default table logic for $chartType</div>";
			}
			$this->rowStart('style="margin-top:20px;"');
			$this->columnStart(12);
			echo "<div class='table-responsive'><table class=\"table table-condensed table-sm table-hover\" style=\"margin-bottom:0; background-color:#fff;\">";
			echo "<thead class=\"thead-light\"><tr>";
			if (in_array($chartType, ['docspermonth', 'sizepermonth'])) {
				echo "<th>" . htmlspecialchars(getMLText('chart_table_header_key', [], 'Month')) . "</th>";
				echo "<th class='text-right'>" . htmlspecialchars(getMLText('total', [], 'Total')) . "</th>";
				if ($chartType === 'docspermonth') {
					echo "<th class='text-right'>" . htmlspecialchars(getMLText('change', [], 'Change')) . "</th>";
				}
			} else {
				echo "<th>" . htmlspecialchars(getMLText('chart_table_header_key', [], 'Key')) . "</th>";
				echo "<th class='text-right'>" . htmlspecialchars(getMLText('total', [], 'Total')) . "</th>";
			}
			echo "</tr></thead><tbody>";
			$grandTotal = 0;
			$oldtotal = 0;
			$debugRowAdded = false;
			foreach ($currentChartDataForTable as $item) {
				echo "<tr>";
				$itemKey = $item['key'] ?? 'N/A';
				$itemTotal = $item['total'] ?? 0;
				if (in_array($chartType, ['docspermonth', 'sizepermonth'])) {
					echo "<td>" . htmlspecialchars($itemKey) . "</td>";
					if ($chartType === 'sizepermonth') {
						echo "<td class='text-right'>" . SeedDMS_Core_File::format_filesize((int) $itemTotal) . "</td>";
					} else {
						echo "<td class='text-right'>" . (int) $itemTotal . "</td>";
					}
					if ($chartType === 'docspermonth') {
						echo "<td class='text-right'>" . sprintf('%+d', (int) $itemTotal - (int) $oldtotal) . "</td>";
					}
					if (!$debugRowAdded) {
						echo "<td colspan='3' style='color:red;font-weight:bold;'>DEBUG ROW: $chartType table logic is active</td>";
						$debugRowAdded = true;
					}
				} else {
					echo "<td>" . htmlspecialchars($itemKey) . "</td>";
					echo "<td class='text-right'>" . (int) $itemTotal . "</td>";
				}
				$oldtotal = $itemTotal;
				$grandTotal += (float) $itemTotal;
				echo "</tr>";
			}
			echo "</tbody><tfoot><tr class='font-weight-bold table-info'>";
			if (in_array($chartType, ['docspermonth', 'sizepermonth'])) {
				echo "<td>" . htmlspecialchars(getMLText('total_overall', [], 'Overall Total')) . "</td>";
				if ($chartType === 'sizepermonth') {
					echo "<td class='text-right'>" . SeedDMS_Core_File::format_filesize($grandTotal) . "</td>";
				} else {
					echo "<td class='text-right'>" . (int) $grandTotal . "</td>";
				}
				if ($chartType === 'docspermonth') {
					echo "<td></td>";
				}
			} else {
				echo "<td>" . htmlspecialchars(getMLText('total_overall', [], 'Overall Total')) . "</td>";
				echo "<td class='text-right'>" . (int) $grandTotal . "</td>";
			}
			echo "</tr></tfoot></table></div>";
			$this->columnEnd();
			$this->rowEnd();
		} else {
			$this->rowStart('style="margin-top:15px;"');
			$this->columnStart(12);
			echo "<p class='text-center'><em>" . htmlspecialchars(getMLText('no_data_for_table', [], 'No data to display in table.')) . "</em></p>";
			$this->columnEnd();
			$this->rowEnd();
		}

		// Calculate total for the current chart (PHP)
		$totalValue = 0;
		if (!empty($currentChartDataForTable) && is_array($currentChartDataForTable)) {
			foreach ($currentChartDataForTable as $item) {
				$totalValue += isset($item['total']) ? (float)$item['total'] : 0;
			}
		}
		// Output the total below the chart
		if ($totalValue > 0 || $totalValue === 0) {
			echo "<div class='chart-total-summary' style='margin-top:10px; font-weight:bold; color:#333; background:#f8f9fa; border:1px solid #e0e0e0; border-radius:4px; padding:8px 12px; display:inline-block;'>";
			echo htmlspecialchars(getMLText('total', [], 'Total')) . ": ";
			if (in_array($chartType, ['sizepermonth'])) {
				echo SeedDMS_Core_File::format_filesize($totalValue);
			} else {
				echo (int)$totalValue;
			}
			echo "</div>";
		}

		// After rendering the chart and before the data table, build the legend HTML and inject it into the legend container
		$legendHtml = "<div class='custom-legend' style='font-size:14px;'><b>Legend</b><ul style='list-style:none; padding-left:0;'>";
		foreach ($currentChartDataForTable as $item) {
			$grandTotal += isset($item['total']) ? (float)$item['total'] : 0;
		}
		foreach ($currentChartDataForTable as $item) {
			$label = isset($item['key']) ? htmlspecialchars($item['key']) : '';
			$value = isset($item['total']) ? (float)$item['total'] : 0;
			$percent = ($grandTotal > 0) ? round(($value / $grandTotal) * 100) : 0;
			if ($isPieChart) {
				$legendHtml .= "<li style='margin-bottom:4px;'><span style='display:inline-block;width:12px;height:12px;background-color:#ccc;margin-right:6px;vertical-align:middle;'></span> $label: <b>$value</b> ($percent%)</li>";
			} else {
				$legendHtml .= "<li style='margin-bottom:4px;'><span style='display:inline-block;width:12px;height:12px;background-color:#ccc;margin-right:6px;vertical-align:middle;'></span> $label: <b>$value</b></li>";
			}
		}
		$legendHtml .= "</ul></div>";
		echo "<script>document.getElementById('legend_container_" . htmlspecialchars($chartType) . "').innerHTML = " . json_encode($legendHtml) . ";</script>";

		echo '</div>';
	}

	// ========================================================================
	// END CHART RELATED METHODS
	// ========================================================================

	// ========================================================================
	// AJAX ACTION METHODS & HELPERS
	// ========================================================================
	protected function printList($documents, $previewer)
	{
		$txt = $this->callHook('folderListPreContent', null, [], $documents);
		if (is_string($txt))
			echo $txt;

		$headerTxt = $this->callHook('folderListHeader', null, '', '');
		if (is_string($headerTxt)) {
			echo $headerTxt;
		} elseif (is_array($headerTxt)) {
			echo "<table id=\"viewfolder-table\" class=\"table table-condensed table-sm table-hover\">";
			echo "<thead>\n<tr>\n";
			foreach ($headerTxt as $headcol)
				echo "<th>" . htmlspecialchars($headcol) . "</th>\n";
			echo "</tr>\n</thead>\n";
		} else {
			echo $this->folderListHeader();
		}
		echo "<tbody>\n";

		if (empty($documents)) {
			$colspan = 4; // Adjust if your table from folderListHeader has different number of columns
			if (is_array($headerTxt))
				$colspan = count($headerTxt);
			else if (is_string($headerTxt) && preg_match_all('/<th[^>]*>/i', $headerTxt, $matches))
				$colspan = count($matches[0]);

			echo '<tr><td colspan="' . $colspan . '" class="text-center text-muted" style="padding:20px;">' . htmlspecialchars(getMLText('no_documents_to_display', [], 'No documents to display.')) . '</td></tr>';
		} else {
			foreach ($documents as $document) {
				if (!is_object($document) || !method_exists($document, 'verifyLastestContentExpriry')) {
					error_log("Dashboard printList: Invalid document object or missing method verifyLastestContentExpriry.");
					continue;
				}
				$document->verifyLastestContentExpriry();
				$itemTxt = $this->callHook('documentListItem', $document, $previewer, false, 'dashboard');
				if (is_string($itemTxt)) {
					echo $itemTxt;
				} else {
					$extracontent = [];
					$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';

					if (method_exists($this, 'getListRowPath')) {
						$path = $this->getListRowPath($document); // Get the full encrypted path (e.g., /abc123/xyz456/...)

						// Split path into parts, decrypt each one
						$path_parts = explode('/', trim($path, '/')); // Remove leading/trailing slash and split by '/'
						$decrypted_parts = [];

						foreach ($path_parts as $part) {

							$decrypted = $this->decrypt($part, $encryption_key);

							// If decryption fails, fall back to original encrypted value
							$decrypted_parts[] = ($decrypted === '[DECRYPTION FAILED]' || $decrypted === '[INVALID NAME]')
								? $part
								: $decrypted;
						}

						// Rebuild the decrypted path with slashes
						$decrypted_path = implode('/', $decrypted_parts);
						$extracontent['below_title'] = $decrypted_path;
					}

					// Finally, pass the decrypted path info to the document row renderer
					echo $this->documentListRow($document, $previewer, false, 0, $extracontent);
				}
			}
		}
		$footerTxt = $this->callHook('folderListFooter', null);
		if (is_string($footerTxt))
			echo $footerTxt;
		else
			echo "</tbody>\n</table>\n";
	}

	public function newdocuments()
	{
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$cachedir = $this->params['cachedir'];
		$conversionmgr = $this->params['conversionmgr'] ?? null;
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$dayspastdashboard = (int) ($this->params['dayspastdashboard'] ?? 7); // Added default
		$excludedfolders = $this->params['excludedfolders'] ?? [];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		if ($conversionmgr)
			$previewer->setConversionMgr($conversionmgr);
		else
			$previewer->setConverters($previewconverters);

		$documents = $dms->getLatestChanges('newdocuments', mktime(0, 0, 0) - $dayspastdashboard * 86400, time());
		$documents = SeedDMS_Core_DMS::filterAccess($documents, $user, M_READ);
		$filtered_documents = [];
		if (is_array($documents)) {
			foreach ($documents as $i => $doc) {
				if (is_object($doc) && method_exists($doc, 'getFolderList')) {
					$fl = explode(':', $doc->getFolderList());
					if (!array_intersect($fl, $excludedfolders)) {
						$filtered_documents[] = $doc;
					}
				}
			}
		}
		$this->printList($filtered_documents, $previewer);
	}

	public function updateddocuments()
	{
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$cachedir = $this->params['cachedir'];
		$conversionmgr = $this->params['conversionmgr'] ?? null;
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$dayspastdashboard = (int) ($this->params['dayspastdashboard'] ?? 7);
		$excludedfolders = $this->params['excludedfolders'] ?? [];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		if ($conversionmgr)
			$previewer->setConversionMgr($conversionmgr);
		else
			$previewer->setConverters($previewconverters);

		$documents = $dms->getLatestChanges('updateddocuments', mktime(0, 0, 0) - $dayspastdashboard * 86400, time());
		$documents = SeedDMS_Core_DMS::filterAccess($documents, $user, M_READ);
		$filtered_documents = [];
		if (is_array($documents)) {
			foreach ($documents as $i => $doc) {
				if (is_object($doc) && method_exists($doc, 'getFolderList')) {
					$fl = explode(':', $doc->getFolderList());
					if (!array_intersect($fl, $excludedfolders)) {
						$filtered_documents[] = $doc;
					}
				}
			}
		}
		$this->printList($filtered_documents, $previewer);
	}

	public function status()
	{
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$cachedir = $this->params['cachedir'];
		$conversionmgr = $this->params['conversionmgr'] ?? null;
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$dayspastdashboard = (int) ($this->params['dayspastdashboard'] ?? 7);
		$excludedfolders = $this->params['excludedfolders'] ?? [];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		if ($conversionmgr)
			$previewer->setConversionMgr($conversionmgr);
		else
			$previewer->setConverters($previewconverters);

		$documents = $dms->getLatestChanges('statuschange', mktime(0, 0, 0) - $dayspastdashboard * 86400, time());
		$documents = SeedDMS_Core_DMS::filterAccess($documents, $user, M_READ);
		$filtered_documents = [];
		if (is_array($documents)) {
			foreach ($documents as $i => $doc) {
				if (is_object($doc) && method_exists($doc, 'getFolderList')) {
					$fl = explode(':', $doc->getFolderList());
					if (!array_intersect($fl, $excludedfolders)) {
						$filtered_documents[] = $doc;
					}
				}
			}
		}
		$this->printList($filtered_documents, $previewer);
	}
	// ========================================================================
	// END AJAX ACTION METHODS
	// ========================================================================


	// ========================================================================
	// JAVASCRIPT
	// ========================================================================
	function js()
	{
		header('Content-Type: application/javascript; charset=UTF-8');

		$jsChartDataArray = $this->prepareJsChartData();
		$noDataText = getMLText('no_data_available', [], "No data available for this chart");
		$monthNamesJs = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		$mlMonthNames = getMLText("datetime_monthname_short");
		if (is_array($mlMonthNames) && count($mlMonthNames) == 12)
			$monthNamesJs = array_values($mlMonthNames);

		// Parameters for Dashboard Drop Upload
		$enableDropUploadOnDashboard = $this->params['enableDropUploadOnDashboard'] ?? false;
		$dashboardUploadFolder = $this->params['dashboardUploadFolder'] ?? null; // Expects Folder object or ID
		$maxuploadsize = $this->params['maxuploadsize'] ?? ini_get('upload_max_filesize');
		$httpRoot = $this->params['settings']->_httpRoot ?? ($this->params['httpRoot'] ?? '/');

		$translations = ['cancel', 'edit_document_props', 'uploading_maxsize', 'splash_move_document', 'confirm_move_document', 'move_document', 'confirm_transfer_link_document', 'transfer_content', 'link_document', 'splash_move_folder', 'confirm_move_folder', 'move_folder'];
		parent::jsTranslations($translations);

		?>
		jQuery(document).ready(function($) {
		console.log("Dashboard JS Initialized (Combined - Charts, AJAX lists, Drop Upload)");

		$("<div id='chart_tooltip'></div>").css({
		position: "absolute",
		display: "none",
		padding: "5px",
		color: "white",
		"background-color": "#000",
		"border-radius": "5px",
		opacity: 0.80,
		zIndex: 1050
		}).appendTo("body");

		// Initialize Dashboard Drop Upload
		<?php // This JS block is still wrapped in a condition, which is good practice.
				// It ensures JS doesn't try to initialize the uploader if the backend
				// determined it shouldn't be displayed (e.g., no folder set).
				if ($enableDropUploadOnDashboard || true): // Forcing JS to always consider initialization.
					?>
			var uploadFolderId = <?php echo isset($folder) && is_object($folder) ? (int) $folder->getID() : 'null'; ?>;

			<?php
			if (is_object($dashboardUploadFolder) && method_exists($dashboardUploadFolder, 'getID')) {
				echo "uploadFolderId = " . $dashboardUploadFolder->getID() . ";";
			} elseif (is_numeric($dashboardUploadFolder)) {
				echo "uploadFolderId = " . (int) $dashboardUploadFolder . ";";
			}
			?>

			if (typeof SeedDMSUpload !== 'undefined' && uploadFolderId !== null) {
			console.log("Dashboard Drop Upload: Initializing...");
			SeedDMSUpload.setUrl('<?php echo rtrim($httpRoot, '/') . '/op/op.Ajax.php'; ?>');
			SeedDMSUpload.setAbortBtnLabel('<?php echo htmlspecialchars(addslashes(getMLText("cancel")), ENT_QUOTES, 'UTF-8'); ?>');
			SeedDMSUpload.setEditBtnLabel('<?php echo htmlspecialchars(addslashes(getMLText("edit_document_props")), ENT_QUOTES, 'UTF-8'); ?>');
			SeedDMSUpload.setMaxFileSize(<?php echo (int) $maxuploadsize; ?>);

			SeedDMSUpload.setMaxFileSizeMsg('<?php echo htmlspecialchars(addslashes(getMLText("uploading_maxsize")), ENT_QUOTES, 'UTF-8'); ?>');

			if ($('#dashboard-drop-zone').length) {
			SeedDMSUpload.initDropZone($('#dashboard-drop-zone'));
			console.log("Dashboard Drop Upload initialized on #dashboard-drop-zone");
			} else {
			// This is now an expected state if the user doesn't have permission, so we log it as info, not an error.
			console.log("Dashboard Drop Upload: #dashboard-drop-zone HTML element not found. " +
			"This is expected if the folder is not configured or user lacks permissions.");


			}
			} else {
			console.warn("Dashboard Drop Upload: SeedDMSUpload library not found or uploadFolderId is invalid/null.");
			}
		<?php else: ?>
			console.log("Dashboard Drop Upload: Not enabled or folder not configured. No action taken.");
		<?php endif; ?>

		// Chart rendering section
		function renderDashboardCharts(containerSelector) {
		if (typeof $.plot === 'undefined') {
		console.error("Flot library ($.plot) is not loaded.");
		$('.chart').html("<p style='color:red; text-align:center;'>Error: Charting library not loaded.</p>");
		return;
		}
		var allChartsDataFromPHP =
		<?php echo json_encode($jsChartDataArray, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK); ?>;
		var noDataMessage = <?php echo json_encode($noDataText); ?>;
		var monthNamesForFlot = <?php echo json_encode($monthNamesJs); ?>;

		function pieLabelFormatter(label, series) {
			return '<div style=\'font-size:10pt; line-height: 16px; text-align:center; padding:4px; color:black; background: white; border-radius: 5px; min-width:80px;\'>' + label + '<br />' + series.data[0][1] + ' (' + Math.round(series.percent) + '%)</div>';
		}

		var chartsToRender = allChartsDataFromPHP;
		if (containerSelector) {
		// Only render the chart(s) in the given container
		var id = containerSelector.replace('#', '');
		chartsToRender = allChartsDataFromPHP.filter(function(chartInfo) {
		return chartInfo.divId === id || $(containerSelector + ' .chart').length > 0;
		});
		}

		if (!chartsToRender || chartsToRender.length === 0) {
		if (containerSelector) {
		$(containerSelector + ' .chart').html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
		} else {
		$('.chart').html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
		}
		return;
		}

		chartsToRender.forEach(function(chartInfo) {
		var chartDivSelector = "#" + chartInfo.divId;
		var phpStyleDataForChart = chartInfo.data;

		if (!$(chartDivSelector).length) {
		console.error("Chart div NOT FOUND: " + chartDivSelector);
		return;
		}
		$(chartDivSelector).empty();

		if (!phpStyleDataForChart || phpStyleDataForChart.length === 0) {
		$(chartDivSelector).html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
		return;
		}

		var plotOptions = {
		grid: { hoverable: true, clickable: true, borderWidth: 1, borderColor: '#ddd' }
		};
		var flotDataSeries = [];

		if (["docspermonth", "sizepermonth"].includes(chartInfo.type)) {
		flotDataSeries = [phpStyleDataForChart];
		plotOptions.xaxis = { mode: "categories", tickLength: 0 };
		plotOptions.series = { bars: { show: true, align: "center", barWidth: 0.8 } };
		plotOptions.legend = { show: true, container: $('#legend_container_' + chartInfo.type), labelBoxBorderColor: 'none' };

		$(chartDivSelector).bind("plothover", function(event, pos, item) {
		$("#chart_tooltip").hide();
		if (item) {
		var xLabel = item.series.xaxis.ticks[item.dataIndex].label;
		var yVal = item.datapoint[1];
		$("#chart_tooltip").html(xLabel + ": " + yVal).css({ top: item.pageY - 35, left: item.pageX + 5 }).fadeIn(200);
		}
		});
		} else if (chartInfo.type === 'docsaccumulated') {
		flotDataSeries = [phpStyleDataForChart];
		plotOptions.xaxis = { mode: "time", timeformat: "%d.%m.%y", monthNames: monthNamesForFlot };
		plotOptions.series = { lines: { show: true, fill: 0.2 }, points: { show: true, radius: 3 } };
		plotOptions.legend = { show: true, container: $('#legend_container_' + chartInfo.type), labelBoxBorderColor: 'none' };

		$(chartDivSelector).bind("plothover", function(event, pos, item) {
		$("#chart_tooltip").hide();
		if (item) {
		$("#chart_tooltip").html($.plot.formatDate(new Date(item.datapoint[0]), '%e. %b %Y') + ": " + item.datapoint[1]).css({
		top: item.pageY - 35, left: item.pageX + 5 }).fadeIn(200);
		}
		});
		} else if (chartInfo.type === 'docsperuser' || chartInfo.type === 'docsperstatus' || chartInfo.type === 'foldersperuser' || chartInfo.type === 'sizeperuser' || chartInfo.type === 'docspermimetype' || chartInfo.type === 'docspercategory') {
		flotDataSeries = phpStyleDataForChart;
		// Check for only one data point
		let isSingleSlice = false;
		if (flotDataSeries.length === 1 && flotDataSeries[0].data && flotDataSeries[0].data.length === 1) {
			isSingleSlice = true;
		}
		if (chartInfo.type === 'docspermimetype') {
			plotOptions.series = {
				pie: {
					show: true,
					radius: 1,
					label: {
						show: false
					},
					combine: {
						threshold: 0,
						color: null
					}
				}
			};
		} else {
			plotOptions.series = {
				pie: {
					show: true,
					radius: 1,
					label: {
						show: true,
						radius: 0.7,
						formatter: function(label, series) {
							return '<div style="font-size:12px;text-align:center;">' +
								label + '<br>' +
								series.data[0][1] + ' (' + Math.round(series.percent) + '%)</div>';
						},
						threshold: 0.01
					},
					combine: {
						threshold: 0,
						color: null
					}
				}
			};
		}
		var legendContainer = $('#legend_container_' + chartInfo.type);
		plotOptions.legend = legendContainer.length ? { show: true, container: legendContainer, labelBoxBorderColor: "none" } : { show: true, noColumns: 2, labelBoxBorderColor: "none" };
		$(chartDivSelector).bind("plothover", function(event, pos, item) {
			$("#chart_tooltip").hide();
			if (item) {
				$("#chart_tooltip").html(item.series.label + ": " + item.series.data[0][1] + " (" + Math.round(item.series.percent) + "%").css({ top: pos.pageY - 35, left: pos.pageX + 5 }).fadeIn(200);
			}
		});
		}

		if (flotDataSeries && Array.isArray(flotDataSeries) && flotDataSeries.length > 0) {
			if (
				chartInfo.type === 'docsperuser' ||
				chartInfo.type === 'foldersperuser' ||
				chartInfo.type === 'sizeperuser' ||
				chartInfo.type === 'docspermimetype' ||
				chartInfo.type === 'docspercategory' ||
				chartInfo.type === 'docsperstatus'
			) {
				let hasActualData = flotDataSeries.some(series => series.data && series.data.length > 0 && series.data[0].length > 1 && series.data[0][1] > 0);
				if (!hasActualData && flotDataSeries.length > 0) {
					$(chartDivSelector).html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
					return;
				}
			}
			// Debug: Log the data being sent to Flot
			console.log('Pie chart data for', chartInfo.type, JSON.stringify(flotDataSeries), plotOptions);
			$.plot($(chartDivSelector), flotDataSeries, plotOptions);

			// After each $.plot call in renderDashboardCharts, add:
			if ($(chartDivSelector).length && $(chartDivSelector).data('plot')) {
				var plot = $(chartDivSelector).data('plot');
				var legendHtml = "<div class='custom-legend' style='font-size:14px;'><b>Legend</b><ul style='list-style:none; padding-left:0;'>";
				var total = 0;
				if (plot.getData) {
					var data = plot.getData();
					data.forEach(function(series) {
						if (series.data && series.data.length > 0) {
							total += series.data[0][1];
						}
					});
					data.forEach(function(series) {
						var color = series.color || '#ccc';
						var label = series.label || '';
						var value = (series.data && series.data.length > 0) ? series.data[0][1] : 0;
						var percent = (total > 0) ? Math.round((value / total) * 100) : 0;
						if (plot.getOptions().series.pie && plot.getOptions().series.pie.show) {
							legendHtml += "<li style='margin-bottom:4px;'><span style='display:inline-block;width:12px;height:12px;background-color:" + color + ";margin-right:6px;vertical-align:middle;'></span> " + label + ": <b>" + value + "</b> (" + percent + "%)</li>";
						} else {
							legendHtml += "<li style='margin-bottom:4px;'><span style='display:inline-block;width:12px;height:12px;background-color:" + color + ";margin-right:6px;vertical-align:middle;'></span> " + label + ": <b>" + value + "</b></li>";
						}
					});
				}
				legendHtml += "</ul></div>";
				$('#legend_container_' + chartInfo.type).html(legendHtml);
			}
		}
		});
		}
		renderDashboardCharts();
		<?php
	} /* }}} */

	// ========================================================================
	// MAIN PAGE RENDERER
	// ========================================================================
	public function show()
	{
		$dms = $this->params['dms'] ?? null;
		$user = $this->params['user'] ?? null;
		$settings = $this->params['settings'] ?? null;
		$allChartData = $this->params['allChartData'] ?? [];
		// Fix: Define JS chart data variables for inline JS
		$jsChartDataArray = $this->prepareJsChartData();
		$noDataText = getMLText('no_data_available', [], "No data available for this chart");
		$monthNamesJs = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		$mlMonthNames = getMLText("datetime_monthname_short");
		if (is_array($mlMonthNames) && count($mlMonthNames) == 12)
			$monthNamesJs = array_values($mlMonthNames);


		$enableDropUploadOnDashboard = $this->params['enableDropUploadOnDashboard'] ?? false;

		$httpRootForLibs = $this->params['settings']->_httpRoot ?? '../';
		$this->htmlAddHeader(
			'<script type="text/javascript" src="' . $httpRootForLibs . 'styles/bootstrap/flot/jquery.flot.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="' . $httpRootForLibs . 'styles/bootstrap/flot/jquery.flot.pie.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="' . $httpRootForLibs . 'styles/bootstrap/flot/jquery.flot.categories.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="' . $httpRootForLibs . 'styles/bootstrap/flot/jquery.flot.time.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="' . $httpRootForLibs . 'styles/bootstrap/flot/jquery.flot.resize.min.js"></script>' . "\n"
		);

		$pageTitle = getMLText("dashboard", [], "Dashboard");
		$this->htmlStartPage($pageTitle);
		$this->globalNavigation($this->params['folder'] ?? null);

		$this->contentStart();
		$this->pageSidebar();

		echo '<div class="dashboard-main-content-wrapper" style="padding: 15px;">';

		// Quick Upload section - The 'if' condition has been removed to make it always appear.
		echo '<div class="dashboard-quick-upload-section" >';
		$this->rowStart();
		$this->columnStart(12);
		//$this->contentHeading(getMLText("quick_upload", [], "Quick Upload"));
		//$this->dropUpload(); // Call the dropUpload function unconditionally.
		$this->columnEnd();
		$this->rowEnd();
		echo '</div>';

		// System Overview Stats Section
		echo '<div class="dashboard-stats-section" style="margin-bottom: 30px;">';
		$this->rowStart();
		$this->columnStart(12);
		echo '<div class="dashboard-stats-container">';
		$this->contentHeading(getMLText("system_overview", [], "System Overview"));
		$docCount = $this->params['globalStats']['documentCount'] ?? 'N/A';
		$folderCount = $this->params['globalStats']['folderCount'] ?? 'N/A';
		$userCount = $this->params['globalStats']['userCount'] ?? 'N/A';
		$diskSpaceUsed = 'N/A';
		if ($settings) {
			if (isset($settings->storageUsed))
				$diskSpaceUsed = SeedDMS_Core_File::format_filesize($settings->storageUsed);
			elseif (isset($settings->_storageUsed))
				$diskSpaceUsed = SeedDMS_Core_File::format_filesize($settings->_storageUsed);
		}
		$statsData = [
			['label_key' => 'files', 'icon' => 'fa-file', 'value' => $docCount],
			['label_key' => 'folders', 'icon' => 'fa-folder', 'value' => $folderCount],
			['label_key' => 'users', 'icon' => 'fa-user', 'value' => $userCount],
			//['label_key' => 'disk_space_used', 'icon' => 'fa-database', 'value' => $diskSpaceUsed],
		];
		$stat_chunks = array_chunk($statsData, 3);
		foreach ($stat_chunks as $chunk) {
			$this->rowStart();
			foreach ($chunk as $stat_item) {
				$this->columnStart(4);
				echo '<div class="details-grid" style="background:#fff; padding:10px; border:1px solid #ddd; border-radius:4px; text-align:center; min-height: 80px;">';
				echo '<div class="rightside" style="float:right; opacity:0.5;"><i class="fa ' . htmlspecialchars($stat_item['icon']) . ' fa-2x"></i></div>';
				echo '<div class="text-details" style="text-align:left;">';
				echo '<h3 style="font-size:1em; margin-top:0; color:#555;">' . htmlspecialchars(getMLText($stat_item['label_key'], [], ucfirst(str_replace('_', ' ', $stat_item['label_key'])))) . '</h3>';
				echo '<h1 style="font-size:1.8em; margin:5px 0; color:#333;">' . htmlspecialchars($stat_item['value']) . '</h1>';
				echo '</div>';
				echo '<div style="clear:both;"></div>';
				echo '</div>';
				$this->columnEnd();
			}
			$this->rowEnd();
		}
		echo '</div>';
		$this->columnEnd();
		$this->rowEnd();
		echo '</div>';

		// Charts Section
		echo '<div class="charts-dashboard-container">';
		$dashboardChartLayout = [
			['docspermonth', 'sizepermonth'],
			['docsaccumulated', 'docspermimetype']
		];
		$chartsActuallyRendered = 0;
		foreach ($dashboardChartLayout as $rowChartTypes) {
			$this->rowStart();
			$numChartsInRow = 0;
			foreach ($rowChartTypes as $chartType) {
				if (in_array($chartType, $this->_dashboardChartTypes) && $this->showChartCondition($chartType, $dms) && isset($allChartData[$chartType]) && !empty($allChartData[$chartType])) {
					$numChartsInRow++;
				}
			}
			$colWidth = $numChartsInRow > 0 ? floor(12 / $numChartsInRow) : 12;

			// Output a single container for the chart cards
			$allChartTypes = $this->_dashboardChartTypes;
			$jsChartDataArray = $this->prepareJsChartData();
			$noDataText = getMLText('no_data_available', [], "No data available for this chart");
			$monthNamesJs = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
			$mlMonthNames = getMLText("datetime_monthname_short");
			if (is_array($mlMonthNames) && count($mlMonthNames) == 12)
				$monthNamesJs = array_values($mlMonthNames);

			// Output a container for the chart cards

			echo '<div class="charts-dashboard-container row" id="dashboard-charts-row"></div>';

			$jsAllChartTypes = json_encode($allChartTypes);
			$jsChartData = json_encode($jsChartDataArray, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK);
			$jsNoDataText = json_encode($noDataText);
			$jsMonthNames = json_encode($monthNamesJs);
			$jsDashboardChartLayout = json_encode($dashboardChartLayout);

			// Output the JS for rendering and swapping charts
			echo <<<EOT
		<script>
		(function() {
			var allChartTypes = $jsAllChartTypes;
			var allChartsDataFromPHP = $jsChartData;
			var noDataMessage = $jsNoDataText;
			var monthNamesForFlot = $jsMonthNames;
			var dashboardChartLayout = $jsDashboardChartLayout;
			
			// Create visibleCharts from the dashboard layout
			var visibleCharts = [];
			dashboardChartLayout.forEach(function(row) {
				row.forEach(function(chartType) {
					if (visibleCharts.indexOf(chartType) === -1) {
						visibleCharts.push(chartType);
					}
				});
			});
			
			// If no charts from layout, fall back to first 4
			if (visibleCharts.length === 0) {
				visibleCharts = allChartTypes.slice(0, 4);
			}

			// Helper: get chart data by type
			function getChartDataByType(type) {
				for (var i = 0; i < allChartsDataFromPHP.length; i++) {
					if (allChartsDataFromPHP[i].type === type) return allChartsDataFromPHP[i];
				}
				return { type: type, data: [], divId: 'chart_' + type };
			}

			// Helper: get chart title
			function getChartTitle(type) {
				// Try to get translation from global getMLText if available
				if (typeof getMLText === 'function') {
					var t = getMLText('chart_' + type + '_title');
					if (t && typeof t === 'string') return t;
				}
				return type.replace(/([a-z])([A-Z])/g, '$1 $2').replace(/_/g, ' ').replace(/^./, function(s) { return s.toUpperCase(); });
			}

			function renderChartCards() {
				var row = document.getElementById('dashboard-charts-row');
				row.innerHTML = '';
				for (var i = 0; i < 4; i++) {
					var chartType = visibleCharts[i];
					var card = document.createElement('div');
					card.className = 'col-md-6 col-12 mb-4 dashboard-chart-col';
					var dropdownOptions = '';
					for (var j = 0; j < allChartTypes.length; j++) {
						var t = allChartTypes[j];
						if (visibleCharts.indexOf(t) === -1) {
							dropdownOptions += '<a class="dropdown-item chart-swap-btn" href="#" data-chart-index="' + i + '" data-chart-type="' + t + '">' + getChartTitle(t) + '</a>';
						}
					}
					card.innerHTML =
						'<div class="dashboard-card-container">' +
							'<div class="card shadow-sm h-100">' +
								'<div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">' +
									'<span class="chart-title font-weight-bold text-primary">' + getChartTitle(chartType) + '</span>' +
									'<div class="dropdown ml-2">' +
										'<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartDropdown' + i + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
											'<i class="fa fa-bar-chart mr-1"></i>Change Chart' +
										'</button>' +
										'<div class="dropdown-menu dropdown-menu-right" aria-labelledby="chartDropdown' + i + '" style="min-width: 200px;">' +
											dropdownOptions +
										'</div>' +
									'</div>' +
								'</div>' +
								'<div class="card-body p-3">' +
									'<div id="chart_' + chartType + '" class="chart" style="height:600px; width:100%;"><p style="text-align:center; padding-top:280px; color:#777;">Loading chart...</p></div>' +
									'<div id="legend_container_' + chartType + '"></div>' +
								'</div>' +
							'</div>' +
						'</div>';
					row.appendChild(card);
				}
				attachDropdownListeners();
				renderDashboardCharts();
			}

			// Attach dropdown listeners
			function attachDropdownListeners() {
				document.querySelectorAll('.chart-swap-btn').forEach(function(btn) {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						var chartIndex = parseInt(this.getAttribute('data-chart-index'));
						var newType = this.getAttribute('data-chart-type');
						// Swap the chart in visibleCharts
						var oldType = visibleCharts[chartIndex];
						visibleCharts[chartIndex] = newType;
						// To keep only unique charts, ensure no duplicates
						// (should not happen with dropdown logic, but just in case)
						for (var i = 0; i < 4; i++) {
							for (var j = i + 1; j < 4; j++) {
								if (visibleCharts[i] === visibleCharts[j]) {
									// revert
									visibleCharts[chartIndex] = oldType;
									return;
								}
							}
						}
						renderChartCards();
					});
				});
			}

			// Chart rendering section 
			function renderDashboardCharts() {
				if (typeof $.plot === 'undefined') {
					$('.chart').html("<p style='color:red; text-align:center;'>Error: Charting library not loaded.</p>");
					return;
				}
				var noDataMessage = $jsNoDataText;
				var monthNamesForFlot = $jsMonthNames;
				function pieLabelFormatter(label, series) {
					return '<div style="font-size:10pt; line-height: 16px; text-align:center; padding:4px; color:black; background: white; border-radius: 5px; min-width:80px;">' + label + '<br />' + series.data[0][1] + ' (' + Math.round(series.percent) + '%)</div>';
				}
				visibleCharts.forEach(function(chartType) {
					var chartInfo = getChartDataByType(chartType);
					var chartDivSelector = "#chart_" + chartType;
					var phpStyleDataForChart = chartInfo.data;
					if (!$(chartDivSelector).length) return;
					$(chartDivSelector).empty();
					if (!phpStyleDataForChart || phpStyleDataForChart.length === 0) {
						$(chartDivSelector).html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
						return;
					}
					var plotOptions = { grid: { hoverable: true, clickable: true, borderWidth: 1, borderColor: '#ddd' } };
					var flotDataSeries = [];
					if (["docspermonth", "sizepermonth"].includes(chartType)) {
						flotDataSeries = [phpStyleDataForChart];
						plotOptions.xaxis = { mode: "categories", tickLength: 0 };
						plotOptions.series = { bars: { show: true, align: "center", barWidth: 0.8 } };
						plotOptions.legend = { show: true, container: $('#legend_container_' + chartType), labelBoxBorderColor: 'none' };
						$(chartDivSelector).bind("plothover", function(event, pos, item) {
							$("#chart_tooltip").hide();
							if (item) {
								var xLabel = item.series.xaxis.ticks[item.dataIndex].label;
								var yVal = item.datapoint[1];
								$("#chart_tooltip").html(xLabel + ": " + yVal).css({ top: item.pageY - 35, left: item.pageX + 5 }).fadeIn(200);
							}
						});
					} else if (chartType === "docsaccumulated") {
						flotDataSeries = [phpStyleDataForChart];
						plotOptions.xaxis = { mode: "time", timeformat: "%d.%m.%y", monthNames: monthNamesForFlot };
						plotOptions.series = { lines: { show: true, fill: 0.2 }, points: { show: true, radius: 3 } };
						plotOptions.legend = { show: true, container: $('#legend_container_' + chartType), labelBoxBorderColor: 'none' };
						$(chartDivSelector).bind("plothover", function(event, pos, item) {
							$("#chart_tooltip").hide();
							if (item) {
								$("#chart_tooltip").html($.plot.formatDate(new Date(item.datapoint[0]), '%e. %b %Y') + ": " + item.datapoint[1]).css({ top: item.pageY - 35, left: item.pageX + 5 }).fadeIn(200);
							}
						});
					} else {
						flotDataSeries = phpStyleDataForChart;
						// Check for only one data point
						let isSingleSlice = false;
						if (flotDataSeries.length === 1 && flotDataSeries[0].data && flotDataSeries[0].data.length === 1) {
							isSingleSlice = true;
						}
						if (chartType === 'docspermimetype') {
							plotOptions.series = {
								pie: {
									show: true,
									radius: 1,
									label: {
										show: false
									},
									combine: {
										threshold: 0,
										color: null
									}
								}
							};
						} else {
							plotOptions.series = {
								pie: {
									show: true,
									radius: 1,
									label: {
										show: true,
										radius: 0.7,
										formatter: function(label, series) {
											return '<div style="font-size:12px;text-align:center;">' +
												label + '<br>' +
												series.data[0][1] + ' (' + Math.round(series.percent) + '%)</div>';
										},
										threshold: 0.01
									},
									combine: {
										threshold: 0,
										color: null
									}
								}
							};
						}
						var legendContainer = $('#legend_container_' + chartType);
						plotOptions.legend = legendContainer.length ? { show: true, container: legendContainer, labelBoxBorderColor: "none" } : { show: true, noColumns: 2, labelBoxBorderColor: "none" };
						$(chartDivSelector).bind("plothover", function(event, pos, item) {
							$("#chart_tooltip").hide();
							if (item) {
								$("#chart_tooltip").html(item.series.label + ": " + item.series.data[0][1] + " (" + Math.round(item.series.percent) + "%").css({ top: pos.pageY - 35, left: pos.pageX + 5 }).fadeIn(200);
							}
						});
					}
					if (flotDataSeries && Array.isArray(flotDataSeries) && flotDataSeries.length > 0) {
						if (plotOptions.series && plotOptions.series.pie) {
							let hasActualData = flotDataSeries.some(series => series.data && series.data.length > 0 && series.data[0].length > 1 && series.data[0][1] > 0);
							if (!hasActualData && flotDataSeries.length > 0) {
								$(chartDivSelector).html('<p class="text-muted text-center">' + noDataMessage + '</p>');
								return;
							}
						}
						// Debug: Log the data being sent to Flot
						console.log('Pie chart data for', chartType, JSON.stringify(flotDataSeries), plotOptions);
						$.plot($(chartDivSelector), flotDataSeries, plotOptions);
					}

					// After each $.plot call in renderDashboardCharts, add:
					if ($(chartDivSelector).length && $(chartDivSelector).data('plot')) {
						var plot = $(chartDivSelector).data('plot');
						var legendHtml = "<div class='custom-legend' style='font-size:14px;'><b>Legend</b><ul style='list-style:none; padding-left:0;'>";
						var total = 0;
						if (plot.getData) {
							var data = plot.getData();
							data.forEach(function(series) {
								if (series.data && series.data.length > 0) {
									total += series.data[0][1];
								}
							});
							data.forEach(function(series) {
								var color = series.color || '#ccc';
								var label = series.label || '';
								var value = (series.data && series.data.length > 0) ? series.data[0][1] : 0;
								var percent = (total > 0) ? Math.round((value / total) * 100) : 0;
								if (plot.getOptions().series.pie && plot.getOptions().series.pie.show) {
									legendHtml += "<li style='margin-bottom:4px;'><span style='display:inline-block;width:12px;height:12px;background-color:" + color + ";margin-right:6px;vertical-align:middle;'></span> " + label + ": <b>" + value + "</b> (" + percent + "%)</li>";
								} else {
									legendHtml += "<li style='margin-bottom:4px;'><span style='display:inline-block;width:12px;height:12px;background-color:" + color + ";margin-right:6px;vertical-align:middle;'></span> " + label + ": <b>" + value + "</b></li>";
								}
							});
						}
						legendHtml += "</ul></div>";
						$('#legend_container_' + chartType).html(legendHtml);
					}
				});
			}

			renderChartCards();
		})();
		</script>
EOT;

		echo '</div>'; // End charts-dashboard-container

		echo '<div class="dashboard-document-lists-section" style="margin-top: 30px;">';
		$this->contentHeading(getMLText("recent_activity", [], "Recent Activity"));
		$this->rowStart();
		$this->columnStart(4);
		echo '<h5 style="margin-bottom:10px;">' . htmlspecialchars(getMLText('new_documents', [], 'New Documents')) . '</h5>';
		echo '<div class="ajax well well-small" data-view="Dashboard" data-action="newdocuments" style="min-height: 150px; max-height:300px; overflow-y:auto; padding:10px;"><p class="text-center text-muted" style="padding-top: 50px;">' . htmlspecialchars(getMLText('loading_data', [], 'Loading...')) . '</p></div>';
		$this->columnEnd();
		$this->columnStart(4);
		echo '<h5 style="margin-bottom:10px;">' . htmlspecialchars(getMLText('updated_documents', [], 'Updated Documents')) . '</h5>';
		echo '<div class="ajax well well-small" data-view="Dashboard" data-action="updateddocuments" style="min-height: 150px; max-height:300px; overflow-y:auto; padding:10px;"><p class="text-center text-muted" style="padding-top: 50px;">' . htmlspecialchars(getMLText('loading_data', [], 'Loading...')) . '</p></div>';
		$this->columnEnd();
		$this->columnStart(3);
		echo '<h5 style="margin-bottom:10px;">' . htmlspecialchars(getMLText('status_change', [], 'Status Changes')) . '</h5>';
		echo '<div class="ajax well well-small" data-view="Dashboard" data-action="status" style="min-height: 150px; max-height:300px; overflow-y:auto; padding:10px;"><p class="text-center text-muted" style="padding-top: 50px;">' . htmlspecialchars(getMLText('loading_data', [], 'Loading...')) . '</p></div>';
		$this->columnEnd();
		$this->rowEnd();
		echo '</div>';

		echo '</div>'; // End dashboard-main-content-wrapper

		$this->contentEnd();
		$this->htmlEndPage();
		/* }}} */

	}
}
}