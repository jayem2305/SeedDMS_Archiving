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
	// CHART RELATED PROPERTIES AND METHODS (from your "charts working" version)
	// ========================================================================
	private $_dashboardChartTypes = [
		'docsperuser',
		'docspermonth',
		'docsperstatus',
		'docsaccumulated'
	];

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
		// ... (Implementation from the previous "charts working" answer)
		// This method is for rendering the HTML structure of a single chart + its table
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
		<div id="chart_<?php echo htmlspecialchars($chartType); ?>" style="height:400px; width:100%;" class="chart">
			<p style="text-align:center; padding-top:180px; color:#777;">Loading chart...</p>
		</div>
		<?php
		$this->contentContainerEnd();
		$this->columnEnd();

		if ($isPieChart && !$isBarOrLineChart) {
			$this->columnStart(4);
			$this->contentHeading(getMLText('legend', [], "Legend"));
			$this->contentContainerStart('', 'legend_container_' . $chartType, 'background-color:#fff; padding:10px; border:1px solid #e0e0e0; border-radius:4px; min-height:420px; max-height: 420px; overflow-y: auto;');
			$this->contentContainerEnd();
			$this->columnEnd();
		}
		$this->rowEnd();

		if (!empty($currentChartDataForTable)) {
			$this->rowStart('style="margin-top:20px;"');
			$this->columnStart(12);
			echo "<div class='table-responsive'><table class=\"table table-condensed table-sm table-hover\" style=\"margin-bottom:0; background-color:#fff;\">";
			echo "<thead class=\"thead-light\"><tr>";
			echo "<th>" . htmlspecialchars(getMLText('chart_table_header_key', [], 'Key')) . "</th>";
			echo "<th class='text-right'>" . htmlspecialchars(getMLText('total', [], 'Total')) . "</th>";
			if (in_array($chartType, ['docspermonth'])) {
				echo "<th class='text-right'>" . htmlspecialchars(getMLText('change', [], 'Change')) . "</th>";
			}
			echo "</tr></thead><tbody>";
			$grandTotal = 0;
			$oldtotal = 0;
			foreach ($currentChartDataForTable as $item) {
				echo "<tr>";
				$itemKey = $item['key'] ?? 'N/A';
				$itemTotal = $item['total'] ?? 0;
				if ($chartType == 'docsaccumulated') {
					$dateDisplay = getReadableDate($itemKey / 1000, $this->params['settings']->_dateformat ?? 'd.m.Y');
					echo "<td>" . htmlspecialchars($dateDisplay) . "</td>";
				} else {
					echo "<td>" . htmlspecialchars($itemKey) . "</td>";
				}
				if (in_array($chartType, ['sizeperuser', 'sizepermonth'])) {
					echo "<td class='text-right'>" . SeedDMS_Core_File::format_filesize((int) $itemTotal) . "</td>";
				} else {
					echo "<td class='text-right'>" . (int) $itemTotal . "</td>";
				}
				if (in_array($chartType, ['docspermonth'])) {
					echo "<td class='text-right'>" . sprintf('%+d', (int) $itemTotal - (int) $oldtotal) . "</td>";
				}
				$oldtotal = $itemTotal;
				$grandTotal += (float) $itemTotal;
				echo "</tr>";
			}
			echo "</tbody><tfoot><tr class='font-weight-bold table-info'><td>" . htmlspecialchars(getMLText('total_overall', [], 'Overall Total')) . "</td><td class='text-right'>";
			if (in_array($chartType, ['sizeperuser', 'sizepermonth'])) {
				echo SeedDMS_Core_File::format_filesize($grandTotal);
			} elseif ($chartType == 'docsaccumulated') {
				echo (int) $oldtotal;
			} else {
				echo (int) $grandTotal;
			}
			echo "</td>";
			if (in_array($chartType, ['docspermonth']))
				echo "<td></td>";
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
		echo '</div>';
	}

	// ========================================================================
	// END CHART RELATED METHODS
	// ========================================================================

	// ========================================================================
	// AJAX ACTION METHODS & HELPERS (from your original dashboard for lists)
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


	// JS method (handles both charts and AJAX list interactions)
	function js()
	{
		// ... (JS method from your previous "charts working" response,
		//      ensure it also includes parent::jsTranslations for original dashboard functionality)
		//      and the console.log("VIEW JS: Final allChartsData passed to Flot:", ...)
		//      This should be the version from my answer to "still does not work! but in this code it works"
		//      that starts with: header('Content-Type: application/javascript; charset=UTF-8');
		//      ... and has the Flot plotting loop ...
		//      ... and also calls:
		//      parent::jsTranslations([...]);
		//      And potentially:
		//      ob_start();
		//      $this->printDeleteDocumentButtonJs();
		//      $this->printClickDocumentJs();
		//      echo ob_get_clean();
		//      ... if these are not handled by parent/application.js for AJAX content.
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
		position: "absolute", display: "none", padding: "5px", color: "white",
		"background-color": "#000", "border-radius": "5px", opacity: 0.80, zIndex: 1050
		}).appendTo("body");

		// Initialize Dashboard Drop Upload
		<?php if ($enableDropUploadOnDashboard && $dashboardUploadFolder): ?>
			var uploadFolderId = null;
			<?php
			if (is_object($dashboardUploadFolder) && method_exists($dashboardUploadFolder, 'getID')) {
				echo "uploadFolderId = " . $dashboardUploadFolder->getID() . ";";
			} elseif (is_numeric($dashboardUploadFolder)) {
				echo "uploadFolderId = " . $dashboardUploadFolder . ";";
			}
			?>

			console.log("Dashboard Drop Upload: enableDropUploadOnDashboard=true, dashboardUploadFolder is set. uploadFolderId:",
			uploadFolderId);

			if (typeof SeedDMSUpload !== 'undefined' && uploadFolderId !== null) {
			console.log("Dashboard Drop Upload: SeedDMSUpload is defined and uploadFolderId is valid. Initializing...");
			SeedDMSUpload.setUrl('<?php echo rtrim($httpRoot, '/') . '/op/op.Ajax.php'; ?>');
			SeedDMSUpload.setAbortBtnLabel('<?php echo htmlspecialchars(addslashes(getMLText("cancel")), ENT_QUOTES, 'UTF-8'); ?>');
			SeedDMSUpload.setEditBtnLabel('<?php echo htmlspecialchars(addslashes(getMLText("edit_document_props")), ENT_QUOTES, 'UTF-8'); ?>');
			SeedDMSUpload.setMaxFileSize(<?php echo SeedDMS_Core_File::getBytes($maxuploadsize); ?>);
			SeedDMSUpload.setMaxFileSizeMsg('<?php echo htmlspecialchars(addslashes(getMLText("uploading_maxsize")), ENT_QUOTES, 'UTF-8'); ?>');

			if ($('#dashboard-drop-zone').length) {
			SeedDMSUpload.initDropZone($('#dashboard-drop-zone'), uploadFolderId);
			console.log("Dashboard Drop Upload initialized for folder ID: " + uploadFolderId + " on #dashboard-drop-zone");
			} else {
			console.error("Dashboard Drop Upload: #dashboard-drop-zone HTML element not found!");
			}
			} else {
			console.error("Dashboard Drop Upload: SeedDMSUpload library not found or uploadFolderId is invalid/null.",
			"SeedDMSUpload defined?", (typeof SeedDMSUpload !== 'undefined'), "uploadFolderId:", uploadFolderId);

			if ($('#dashboard-drop-zone').length) {
			$('#dashboard-drop-zone').html(`<?php
			echo "<p style='color:red; padding-top: 50px; text-align:center;'><em>" .
				htmlspecialchars(addslashes(getMLText('error_drop_upload_misconfigured', [], 'File drop upload is misconfigured or target folder is invalid.')), ENT_QUOTES, 'UTF-8') .
				"</em></p>";
			?>`);
			}
			}
		<?php else: ?>
			console.log("Dashboard Drop Upload: Conditions not met. enableDropUploadOnDashboard:",
			<?php echo json_encode($enableDropUploadOnDashboard); ?>, "dashboardUploadFolder set:",
			<?php echo json_encode(!!$dashboardUploadFolder); ?>);

			if ($('#dashboard-drop-zone').length && <?php echo json_encode($enableDropUploadOnDashboard); ?> &&
			!<?php echo json_encode(!!$dashboardUploadFolder); ?>) {
			$('#dashboard-drop-zone').html(`<?php
			echo "<p style='color:orange; padding-top: 50px; text-align:center;'><em>" .
				htmlspecialchars(addslashes(getMLText('info_drop_upload_no_folder', [], 'Quick upload enabled, but no target folder specified.')), ENT_QUOTES, 'UTF-8') .
				"</em></p>";
			?>`);
			}
		<?php endif; ?>

		// Chart rendering section
		if (typeof $.plot === 'undefined') {
		console.error("Flot library ($.plot) is not loaded.");
		$('.chart').html("<p style='color:red; text-align:center;'>Error: Charting library not loaded.</p>");
		} else {
		var allChartsDataFromPHP =
		<?php echo json_encode($jsChartDataArray, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK); ?>;
		var noDataMessage = <?php echo json_encode($noDataText); ?>;
		var monthNamesForFlot = <?php echo json_encode($monthNamesJs); ?>;

		function pieLabelFormatter(label, series) {
		return `<div
			style='font-size:8pt; line-height: 14px; text-align:center; padding:2px; color:black; background: white; border-radius: 5px;'>
			${label}<br />${series.data[0][1]} (${Math.round(series.percent)}%)</div>`;
		}

		if (!allChartsDataFromPHP || allChartsDataFromPHP.length === 0) {
		console.warn("No chart configurations to process for the dashboard. allChartsDataFromPHP is empty or not an array.");
		$('.chart').html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
		} else {
		allChartsDataFromPHP.forEach(function(chartInfo) {
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

		if (chartInfo.type === 'docspermonth') {
		flotDataSeries = [phpStyleDataForChart];
		plotOptions.xaxis = { mode: "categories", tickLength: 0 };
		plotOptions.series = { bars: { show: true, align: "center", barWidth: 0.8 } };
		plotOptions.legend = { show: false };

		$(chartDivSelector).bind("plothover", function(event, pos, item) {
		$("#chart_tooltip").hide();
		if (item) {
		var xLabel = item.series.xaxis.ticks[item.dataIndex].label;
		var yVal = item.datapoint[1];
		$("#chart_tooltip").html(xLabel + ": " + yVal)
		.css({ top: item.pageY - 35, left: item.pageX + 5 }).fadeIn(200);
		}
		});
		} else if (chartInfo.type === 'docsaccumulated') {
		flotDataSeries = [phpStyleDataForChart];
		plotOptions.xaxis = { mode: "time", timeformat: "%d.%m.%y", monthNames: monthNamesForFlot };
		plotOptions.series = { lines: { show: true, fill: 0.2 }, points: { show: true, radius: 3 } };
		plotOptions.legend = { position: "nw" };

		$(chartDivSelector).bind("plothover", function(event, pos, item) {
		$("#chart_tooltip").hide();
		if (item) {
		$("#chart_tooltip").html($.plot.formatDate(new Date(item.datapoint[0]), '%e. %b %Y') + ": " + item.datapoint[1])
		.css({ top: item.pageY - 35, left: item.pageX + 5 }).fadeIn(200);
		}
		});
		} else if (chartInfo.type === 'docsperuser' || chartInfo.type === 'docsperstatus') {
		flotDataSeries = phpStyleDataForChart;
		plotOptions.series = {
		pie: {
		show: true,
		radius: 1,
		label: {
		show: true,
		radius: 2/3,
		formatter: pieLabelFormatter,
		threshold: 0.05,
		background: { opacity: 0.8 }
		}
		}
		};

		var legendContainer = $('#legend_container_' + chartInfo.type);
		plotOptions.legend = legendContainer.length
		? { show: true, container: legendContainer, labelBoxBorderColor: "none" }
		: { show: true, noColumns: 2, labelBoxBorderColor: "none" };

		$(chartDivSelector).bind("plothover", function(event, pos, item) {
		$("#chart_tooltip").hide();
		if (item) {
		$("#chart_tooltip").html(item.series.label + ": " + item.series.data[0][1] + " (" + Math.round(item.series.percent) +
		"%)")
		.css({ top: pos.pageY - 35, left: pos.pageX + 5 }).fadeIn(200);
		}
		});
		}

		if (flotDataSeries && Array.isArray(flotDataSeries) && flotDataSeries.length > 0) {
		if (chartInfo.type === 'docsperuser' || chartInfo.type === 'docsperstatus') {
		let hasActualData = flotDataSeries.some(series => series.data && series.data.length > 0 &&
		series.data[0].length > 1 && series.data[0][1] > 0);
		if (!hasActualData && flotDataSeries.length > 0) {
		$(chartDivSelector).html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>");
		return;
		}
		}
		$.plot($(chartDivSelector), flotDataSeries, plotOptions);
		}
		});
		}
		}
		});

		<?php
	}


	public function show()
	{
		$dms = $this->params['dms'] ?? null;
		$user = $this->params['user'] ?? null;
		$settings = $this->params['settings'] ?? null;
		$allChartData = $this->params['allChartData'] ?? [];

		$enableDropUploadOnDashboard = $this->params['enableDropUploadOnDashboard'] ?? false;
		$dashboardUploadFolder = $this->params['dashboardUploadFolder'] ?? null;
		$maxuploadsize = $this->params['maxuploadsize'] ?? ini_get('upload_max_filesize');

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

		// Standard way to start content area and render the main site sidebar
		$this->contentStart();
		$this->pageSidebar(); // <<< THIS CALL RENDERS THE STANDARD SITE SIDEBAR

		// The dashboard content will now be placed *inside* the main content area
		// that is to the right of the $this->pageSidebar().
		// All dashboard content goes into a single, full-width wrapper within this area.

		echo '<div class="dashboard-main-content-wrapper" style="padding: 15px;">'; // Wrapper for all dashboard content

		// Optional: Quick Upload section (HTML for it)
		if ($enableDropUploadOnDashboard) {
			echo '<div class="dashboard-quick-upload-section" style="margin-bottom: 30px;">';
			$this->rowStart();
			$this->columnStart(12);
			$this->contentHeading(getMLText("quick_upload", [], "Quick Upload"));
			$dropText = getMLText('drop_files_here_dashboard', [], 'Drop files here to upload.');
			$uploadFolderId = null;
			if ($dashboardUploadFolder) {
				if (is_object($dashboardUploadFolder) && method_exists($dashboardUploadFolder, 'getID')) {
					$uploadFolderId = $dashboardUploadFolder->getID();
				} elseif (is_numeric($dashboardUploadFolder)) {
					$uploadFolderId = (int) $dashboardUploadFolder;
				}
			}
			if ($uploadFolderId) {
				$dropText .= '<br><small>' . getMLText('max_size_param', ['%size%' => SeedDMS_Core_File::format_filesize($maxuploadsize)], 'Max. size: %size%') . '</small>';
			}
			echo '<div id="dashboard-drop-zone" class="well droptarget" ';
			if ($uploadFolderId) {
				echo 'data-droptarget="folder_' . htmlspecialchars($uploadFolderId) . '" ';
				echo 'data-uploadformtoken="' . htmlspecialchars(createFormKey('')) . '" ';
			}
			echo 'style="text-align:center; padding:20px; border-style:dashed; min-height:120px; background-color:#f9f9f9; display:flex; flex-direction:column; justify-content:center; align-items:center;">';
			echo '<i class="fa fa-cloud-upload fa-3x" style="color:#aaa; margin-bottom:10px;"></i>';
			echo '<p>' . $dropText . '</p>';
			if (!$uploadFolderId && $enableDropUploadOnDashboard) {
				echo "<p style='color:orange; margin-top:10px;'><em>" . htmlspecialchars(getMLText('info_drop_upload_no_folder', [], 'Quick upload enabled, but no target folder specified or folder is invalid.')) . "</em></p>";
			}
			echo '</div>';
			$this->columnEnd();
			$this->rowEnd();
			echo '</div>';
		}

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
			['docspermonth', 'docsperuser'],
			['docsaccumulated', 'docsperstatus']
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

			foreach ($rowChartTypes as $chartType) {
				if (in_array($chartType, $this->_dashboardChartTypes)) {
					$hasData = isset($allChartData[$chartType]) && !empty($allChartData[$chartType]);
					if ($hasData && $this->showChartCondition($chartType, $dms)) {
						$this->columnStart($colWidth);
						$this->_renderChartAndTable($chartType, $allChartData, $dms);
						$this->columnEnd();
						$chartsActuallyRendered++;
					}
				}
			}
			$this->rowEnd();
		}
		if ($chartsActuallyRendered == 0) {
			$anyChartConfiguredAndShouldShow = false;
			foreach ($this->_dashboardChartTypes as $type) {
				if ($this->showChartCondition($type, $dms)) {
					$anyChartConfiguredAndShouldShow = true;
					break;
				}
			}
			if ($anyChartConfiguredAndShouldShow) {
				$noChartsToDisplayText = getMLText('no_chart_data_available', [], "No data available for charts.");
				echo "<div class='alert alert-info text-center' role='alert'>" . htmlspecialchars($noChartsToDisplayText) . "</div>";
			}
		}
		echo '</div>';

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
		$this->columnStart(4);
		echo '<h5 style="margin-bottom:10px;">' . htmlspecialchars(getMLText('status_change', [], 'Status Changes')) . '</h5>';
		echo '<div class="ajax well well-small" data-view="Dashboard" data-action="status" style="min-height: 150px; max-height:300px; overflow-y:auto; padding:10px;"><p class="text-center text-muted" style="padding-top: 50px;">' . htmlspecialchars(getMLText('loading_data', [], 'Loading...')) . '</p></div>';
		$this->columnEnd();
		$this->rowEnd();
		echo '</div>';

		echo '</div>'; // End dashboard-main-content-wrapper

		// No $this->columnEnd() here because we didn't start a $this->columnStart(9/10/12) for the wrapper.
		// The wrapper is directly inside the area provided by $this->contentStart() after $this->pageSidebar().

		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}