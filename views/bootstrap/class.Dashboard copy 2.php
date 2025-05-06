<?php
/**
 * Implementation of ViewFolder view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
//require_once("class.Bootstrap.php");

/**
 * Class which outputs the html page for ViewFolder view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_Dashboard extends SeedDMS_Theme_Style
{

	/**
	 * set a different name which is used to specify the hooks.
	 */
	//public $viewAliasName = '';

	function data()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];

		$jsondata = array('name' => $folder->getName());
		header('Content-Type: application/json');
		echo json_encode($jsondata);
	} /* }}} */

	function getAccessModeText($defMode)
	{ /* {{{ */
		switch ($defMode) {
			case M_NONE:
				return getMLText("access_mode_none");
				break;
			case M_READ:
				return getMLText("access_mode_read");
				break;
			case M_READWRITE:
				return getMLText("access_mode_readwrite");
				break;
			case M_ALL:
				return getMLText("access_mode_all");
				break;
		}
	} /* }}} */

	function printAccessList($obj)
	{ /* {{{ */
		$accessList = $obj->getAccessList();
		if (count($accessList["users"]) == 0 && count($accessList["groups"]) == 0)
			return;

		$content = '';
		for ($i = 0; $i < count($accessList["groups"]); $i++) {
			$group = $accessList["groups"][$i]->getGroup();
			$accesstext = $this->getAccessModeText($accessList["groups"][$i]->getMode());
			$content .= $accesstext . ": " . htmlspecialchars($group->getName());
			if ($i + 1 < count($accessList["groups"]) || count($accessList["users"]) > 0)
				$content .= "<br />";
		}
		for ($i = 0; $i < count($accessList["users"]); $i++) {
			$user = $accessList["users"][$i]->getUser();
			$accesstext = $this->getAccessModeText($accessList["users"][$i]->getMode());
			$content .= $accesstext . ": " . htmlspecialchars($user->getFullName());
			if ($i + 1 < count($accessList["users"]))
				$content .= "<br />";
		}

		if (count($accessList["groups"]) + count($accessList["users"]) > 3) {
			$this->printPopupBox(getMLText('list_access_rights'), $content);
		} else {
			echo $content;
		}
	} /* }}} */

	/**
	 * Output a single attribute in the document info section
	 *
	 * @param object $attribute attribute
	 */
	protected function printAttribute($attribute)
	{ /* {{{ */
		$attrdef = $attribute->getAttributeDefinition();
		?>
		<tr>
			<td><?php echo htmlspecialchars($attrdef->getName()); ?>:</td>
			<td><?php echo $this->getAttributeValue($attribute); ?></td>
		</tr>
		<?php
	} /* }}} */

	public function subtree()
	{ /* {{{ */
		$user = $this->params['user'];
		$node = $this->params['node'];
		$orderby = $this->params['orderby'];

		$this->printNewTreeNavigationSubtree($node->getID(), 0, $orderby);
	} /* }}} */

	public function js()
	{
		$data = $this->params['data'];
		$type = $this->params['type'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$orderby = $this->params['orderby'];
		$orderdir = (isset($orderby[1]) ? ($orderby[1] == 'd' ? 'desc' : 'asc') : 'asc');
		$expandFolderTree = $this->params['expandFolderTree'];
		$enableFolderTree = $this->params['enableFolderTree'];
		$enableDropUpload = $this->params['enableDropUpload'];
		$maxItemsPerPage = $this->params['maxItemsPerPage'];
		$maxuploadsize = $this->params['maxuploadsize'];
		$showtree = $this->params['showtree'];
		$onepage = $this->params['onepage'];
		$sitename = trim(strip_tags($this->params['sitename']));

		header('Content-Type: application/javascript; charset=UTF-8');
		parent::jsTranslations(array(
			'cancel',
			'splash_move_document',
			'confirm_move_document',
			'move_document',
			'confirm_transfer_link_document',
			'transfer_content',
			'link_document',
			'splash_move_folder',
			'confirm_move_folder',
			'move_folder',
			'must_drop_one_file',
			'confirm_upload_new_version',
			'upload_new_version'
		));
		?>
	$(document).ready(function() {
		$('#searchfield').focus();

		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			padding: "5px",
			color: "white",
			"background-color": "#000",
			"border-radius": "5px",
			opacity: 0.80
		}).appendTo("body");

		<?php if ($type === 'docspermonth' || $type === 'sizepermonth') { ?>
			var data = [
				<?php if ($data) {
					foreach ($data as $rec) {
						echo '["' . $rec['key'] . '",' . $rec['total'] . '],' . "\n";
					}
				} ?>
			];
			$.plot("#chart", [data], {
				xaxis: {
					mode: "categories",
					tickLength: 0
				},
				series: {
					bars: {
						show: true,
						align: "center",
						barWidth: 0.8
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				}
			});

			$("#chart").bind("plothover", function (event, pos, item) {
				if (item) {
					var x = item.datapoint[0], y = item.datapoint[1];
					var value = <?php echo ($type === 'sizepermonth') ? "formatFileSize(y, false, 2)" : "y"; ?>;
					$("#tooltip").html(item.series.xaxis.ticks[x].label + ": " + value)
						.css({ top: pos.pageY - 35, left: pos.pageX + 5 })
						.fadeIn(200);
				} else {
					$("#tooltip").hide();
				}
			});

		<?php } elseif ($type === 'docsaccumulated') { ?>
			var data = [
				<?php if ($data) {
					foreach ($data as $rec) {
						echo '[' . htmlspecialchars($rec['key']) . ',' . $rec['total'] . '],' . "\n";
					}
				} ?>
			];
			$.plot("#chart", [data], {
				xaxis: { mode: "time" },
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					hoverable: true,
					clickable: true
				}
			});

			$("#chart").bind("plothover", function (event, pos, item) {
				if (item) {
					var x = item.datapoint[0], y = item.datapoint[1];
					$("#tooltip").html($.plot.formatDate(new Date(x), '%e. %b %Y') + ": " + y)
						.css({ top: pos.pageY - 35, left: pos.pageX + 5 })
						.fadeIn(200);
				} else {
					$("#tooltip").hide();
				}
			});

		<?php } else { ?>
			var data = [
				<?php if ($data) {
					foreach ($data as $rec) {
						echo '{ label: "' . htmlspecialchars($rec['key']) . '", data: [[1,' . $rec['total'] . ']]},' . "\n";
					}
				} ?>
			];
			$.plot('#chart', data, {
				series: {
					pie: {
						show: true,
						radius: 1,
						label: {
							show: true,
							radius: 2 / 3,
							formatter: labelFormatter,
							threshold: 0.1,
							background: { opacity: 0.8 }
						}
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend: {
					show: true,
					container: '#legend'
				}
			});

			$("#chart").bind("plothover", function (event, pos, item) {
				if (item) {
					var y = item.series.data[0][1];
					$("#tooltip").html(item.series.label + ": " + y + " (" + Math.round(item.series.percent) + "%)")
						.css({ top: pos.pageY - 35, left: pos.pageX + 5 })
						.fadeIn(200);
				} else {
					$("#tooltip").hide();
				}
			});

			function labelFormatter(label, series) {
				return "<div style='font-size:8pt; line-height: 14px; text-align:center; padding:2px; color:black; background: white; border-radius: 5px;'>" +
					label + "<br />" + series.data[0][1] + " (" + Math.round(series.percent) + "%)</div>";
			}
		<?php } ?>
	});

	var seeddms_folder = <?= $folder->getID() ?>;

	function folderSelectedmaintree(id, name) {
		<?php if (!$onepage) { ?>
				window.location = '../out/out.ViewFolder.php?folderid=' + id;
		<?php } else { ?>
				seeddms_folder = id;
				var title_prefix = "<?= (strlen($sitename) > 0 ? $sitename : "SeedDMS") ?>";
				$('div.ajax').trigger('update', { folderid: id, orderby: '<?= $orderby ?>' });
				document.title = title_prefix + ": " + name;
				window.history.pushState({ "html": "", "pageTitle": title_prefix + ": " + name }, "", '../out/out.ViewFolder.php?folderid=' + id);
		<?php } ?>
	}

	<?php if ($maxItemsPerPage) { ?>
		function loadMoreObjects(element, limit, orderby) {
			if (!$(element).is(":visible")) return;

			element.text('<?= getMLText('more_objects_loading') ?>');
			element.prop("disabled", true);

			var folder = element.data('folder');
			var offset = element.data('offset');
			var url = seeddms_webroot + "out/out.ViewFolder.php?action=entries&folderid=" + folder + "&offset=" + offset + "&limit=" + limit + "&orderby=" + orderby;

			$.ajax({
				type: 'GET',
				url: url,
				dataType: 'json',
				async: false,
				success: function(data) {
					$('#viewfolder-table').append(data.html);
					if (data.count <= 0) {
						element.hide();
					} else {
						var str = '<?= getMLText('x_more_objects') ?>';
						element.text(str.replace('[number]', data.count));
						element.data('offset', offset + limit);
						element.prop("disabled", false);
					}
				}
			});
		}

		$(window).scroll(function () {
			if ($(window).scrollTop() + $(window).height() + 3 >= $(document).height()) {
				loadMoreObjects($('#loadmore'), $('#loadmore').data('limit'), $('#loadmore').data('orderby'));
			}
		});

		$('body').on('click', '#loadmore', function () {
			loadMoreObjects($(this), $(this).data('all'), $(this).data('orderby'));
		});
	<?php } ?>
<?php
		}


		function folderInfos()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$folder = $this->params['folder'];

		$txt = $this->callHook('folderInfos', $folder);
		if (is_string($txt))
			echo $txt;
		else {
			$owner = $folder->getOwner();
			$txt = $this->callHook('preFolderInfos', $folder);
			if (is_string($txt))
				echo $txt;
			ob_start();
			echo "<table class=\"table table-condensed table-sm\">\n";
			if ($user->isAdmin()) {
				echo "<tr>";
				echo "<td>" . getMLText("id") . ":</td>\n";
				echo "<td>" . htmlspecialchars($folder->getID()) . "</td>\n";
				echo "</tr>";
			}
			echo "<tr>";
			echo "<td>" . getMLText("owner") . ":</td>\n";
			echo "<td><a href=\"mailto:" . htmlspecialchars($owner->getEmail()) . "\">" . htmlspecialchars($owner->getFullName()) . "</a></td>\n";
			echo "</tr>";
			echo "<tr>";
			echo "<td>" . getMLText("creation_date") . ":</td>";
			echo "<td>" . getLongReadableDate($folder->getDate()) . "</td>";
			echo "</tr>";
			if ($folder->getComment()) {
				if ($settings->_markdownComments) {
					$Parsedown = new Parsedown();
					$comment = $Parsedown->text($folder->getComment());
				} else {
					$comment = htmlspecialchars($folder->getComment());
				}
				echo "<tr>";
				echo "<td>" . getMLText("comment") . ":</td>\n";
				echo "<td><div class=\"folder-comment\">" . $comment . "</div></td>\n";
				echo "</tr>";
			}

			if ($folder->getAccessMode($user) == M_ALL) {
				echo "<tr>";
				echo "<td>" . getMLText('default_access') . ":</td>";
				echo "<td>" . $this->getAccessModeText($folder->getDefaultAccess()) . "</td>";
				echo "</tr>";
				if ($folder->inheritsAccess()) {
					echo "<tr>";
					echo "<td>" . getMLText("access_mode") . ":</td>\n";
					echo "<td>";
					echo getMLText("inherited") . "<br />";
					$this->printAccessList($folder);
					echo "</tr>";
				} else {
					echo "<tr>";
					echo "<td>" . getMLText('access_mode') . ":</td>";
					echo "<td>";
					$this->printAccessList($folder);
					echo "</td>";
					echo "</tr>";
				}
			}
			$attributes = $folder->getAttributes();
			if ($attributes) {
				foreach ($attributes as $attribute) {
					$arr = $this->callHook('showFolderAttribute', $folder, $attribute);
					if (is_array($arr)) {
						echo "<tr>";
						echo "<td>" . $arr[0] . ":</td>";
						echo "<td>" . $arr[1] . "</td>";
						echo "</tr>";
					} elseif (is_string($arr)) {
						echo $arr;
					} else {
						$this->printAttribute($attribute);
					}
				}
			}
			$arrarr = $this->callHook('additionalFolderInfos', $folder);
			if (is_array($arrarr)) {
				foreach ($arrarr as $arr) {
					echo "<tr>";
					echo "<td>" . $arr[0] . ":</td>";
					echo "<td>" . $arr[1] . "</td>";
					echo "</tr>";
				}
			} elseif (is_string($arrarr)) {
				echo $arrarr;
			}
			echo "</table>\n";
			$infos = ob_get_clean();
			echo $infos;
			//			$this->printAccordion2(getMLText("folder_infos"), $infos);
			$txt = $this->callHook('postFolderInfos', $folder);
			if (is_string($txt))
				echo $txt;
		}
	} /* }}} */




	function folderList()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$folderid = $folder->getId();
		$orderby = $this->params['orderby'];
		$orderdir = (isset($orderby[1]) ? ($orderby[1] == 'd' ? 'desc' : 'asc') : 'asc');
		$cachedir = $this->params['cachedir'];
		$conversionmgr = $this->params['conversionmgr'];
		$maxItemsPerPage = $this->params['maxItemsPerPage'];
		$incItemsPerPage = $this->params['incItemsPerPage'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];
		$onepage = $this->params['onepage'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		if ($conversionmgr)
			$previewer->setConversionMgr($conversionmgr);
		else
			$previewer->setConverters($previewconverters);

		$txt = $this->callHook('listHeader', $folder);
		if (is_string($txt))
			echo $txt;
		else
			$this->contentHeading(getMLText("folder_contents"));

		$subFolders = $this->callHook('folderGetSubFolders', $folder, $orderby[0], $orderdir);
		if ($subFolders === null)
			$subFolders = $folder->getSubFolders($orderby[0], $orderdir);
		$subFolders = SeedDMS_Core_DMS::filterAccess($subFolders, $user, M_READ);
		$documents = $this->callHook('folderGetDocuments', $folder, $orderby[0], $orderdir);
		if ($documents === null)
			$documents = $folder->getDocuments($orderby[0], $orderdir);
		$documents = SeedDMS_Core_DMS::filterAccess($documents, $user, M_READ);

		$txt = $this->callHook('folderListPreContent', $folder, $subFolders, $documents);
		if (is_string($txt))
			echo $txt;
		$i = 0;
		if ((count($subFolders) > 0) || (count($documents) > 0)) {
			$txt = $this->callHook('folderListHeader', $folder, $orderby, $orderdir);
			if (is_string($txt)) {
				echo $txt;
			} elseif (is_array($txt)) {
				print "<table id=\"viewfolder-table\" class=\"table table-condensed table-sm table-hover\">";
				print "<thead>\n<tr>\n";
				foreach ($txt as $headcol)
					echo "<th>" . $headcol . "</th>\n";
				print "</tr>\n</thead>\n";
			} else {
				echo $this->folderListHeader();
			}
			print "<tbody>\n";

			foreach ($subFolders as $subFolder) {
				if (!$maxItemsPerPage || $i < $maxItemsPerPage) {
					$txt = $this->callHook('folderListItem', $subFolder, false, 'viewfolder');
					if (is_string($txt))
						echo $txt;
					else {
						echo $this->folderListRow($subFolder);
					}
				}
				$i++;
			}

			if ($subFolders && $documents) {
				if (!$maxItemsPerPage || $maxItemsPerPage > count($subFolders)) {
					$txt = $this->callHook('folderListSeparator', $folder);
					if (is_string($txt))
						echo $txt;
				}
			}

			foreach ($documents as $document) {
				if (!$maxItemsPerPage || $i < $maxItemsPerPage) {
					$document->verifyLastestContentExpriry();
					$txt = $this->callHook('documentListItem', $document, $previewer, false, 'viewfolder');
					if (is_string($txt))
						echo $txt;
					else {
						echo $this->documentListRow($document, $previewer);
					}
				}
				$i++;
			}

			$txt = $this->callHook('folderListFooter', $folder);
			if (is_string($txt))
				echo $txt;
			else
				echo "</tbody>\n</table>\n";

			if ($maxItemsPerPage && $i > $maxItemsPerPage)
				echo "<button id=\"loadmore\" style=\"width: 100%; margin-bottom: 20px;\" class=\"btn btn-secondary\" data-folder=\"" . $folder->getId() . "\"data-offset=\"" . $maxItemsPerPage . "\" data-limit=\"" . $incItemsPerPage . "\" data-orderby=\"" . $orderby . "\" data-all=\"" . ($i - $maxItemsPerPage) . "\">" . getMLText('x_more_objects', array('number' => ($i - $maxItemsPerPage))) . "</button>";
		} else
			printMLText("empty_folder_list");

		$txt = $this->callHook('folderListPostContent', $folder, $subFolders, $documents);
		if (is_string($txt))
			echo $txt;

	} /* }}} */

	function navigation()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];

		$txt = $this->callHook('folderMenu', $folder);
		if (is_string($txt))
			echo $txt;
		else {
			$this->pageNavigation($this->getFolderPathHTML($folder, true), "view_folder", $folder);

		}

		echo $this->callHook('preContent');
	} /* }}} */

	function dropUpload()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$maxuploadsize = $this->params['maxuploadsize'];

		if ($folder->getAccessMode($user) >= M_READWRITE) {
			$this->contentHeading(getMLText("dropupload"), true);
			?>
				<div id="draganddrophandler" class="well alert alert-warning"
					data-droptarget="folder_<?php echo $folder->getID(); ?>" data-target="<?php echo $folder->getID(); ?>"
					data-uploadformtoken="<?php echo createFormKey(''); ?>">
					<?php printMLText('drop_files_here', ['maxuploadsize' => SeedDMS_Core_File::format_filesize($maxuploadsize)]); ?>
				</div>
				<?php
		} else {
			//$this->errorMsg(getMLText('access_denied'));
		}
	} /* }}} */

	function entries()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$orderby = $this->params['orderby'];
		$orderdir = (isset($orderby[1]) ? ($orderby[1] == 'd' ? 'desc' : 'asc') : 'asc');
		$cachedir = $this->params['cachedir'];
		$conversionmgr = $this->params['conversionmgr'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];
		$offset = $this->params['offset'];
		$limit = $this->params['limit'];

		header('Content-Type: application/json');

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		if ($conversionmgr)
			$previewer->setConversionMgr($conversionmgr);
		else
			$previewer->setConverters($previewconverters);

		$subFolders = $this->callHook('folderGetSubFolders', $folder, $orderby[0]);
		if ($subFolders === null)
			$subFolders = $folder->getSubFolders($orderby[0], $orderdir);
		$subFolders = SeedDMS_Core_DMS::filterAccess($subFolders, $user, M_READ);
		$documents = $this->callHook('folderGetDocuments', $folder, $orderby[0]);
		if ($documents === null)
			$documents = $folder->getDocuments($orderby[0], $orderdir);
		$documents = SeedDMS_Core_DMS::filterAccess($documents, $user, M_READ);

		$content = '';
		if ((count($subFolders) > 0) || (count($documents) > 0)) {
			$i = 0; // counts all entries
			$j = 0; // counts only returned entries
			foreach ($subFolders as $subFolder) {
				if ($i >= $offset && $j < $limit) {
					$txt = $this->callHook('folderListItem', $subFolder, false, 'viewfolder');
					if (is_string($txt))
						$content .= $txt;
					else {
						$content .= $this->folderListRow($subFolder);
					}
					$j++;
				}
				$i++;
			}

			if ($subFolders && $documents) {
				if (($j && $j < $limit) || ($offset + $limit == $i)) {
					$txt = $this->callHook('folderListSeparator', $folder);
					if (is_string($txt))
						$content .= $txt;
				}
			}

			foreach ($documents as $document) {
				if ($i >= $offset && $j < $limit) {
					$document->verifyLastestContentExpriry();
					$txt = $this->callHook('documentListItem', $document, $previewer, false, 'viewfolder');
					if (is_string($txt))
						$content .= $txt;
					else {
						$content .= $this->documentListRow($document, $previewer);
					}
					$j++;
				}
				$i++;
			}

			echo json_encode(array('error' => 0, 'count' => $i - ($offset + $limit), 'html' => $content));
		}

	} /* }}} */





	// charts fucntions

	private function showChart($type)
	{ /* {{{ */
		$dms = $this->params['dms'];
		if ($type == 'docspercategory') {
			if ($cats = $dms->getDocumentCategories())
				return true;
			else
				return false;
		}
		return true;
	} /* }}} */









	function show()
	{ /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$folder = $this->params['folder'];
		$orderby = $this->params['orderby'];
		$orderdir = (isset($orderby[1]) ? ($orderby[1] == 'd' ? 'desc' : 'asc') : 'asc');
		$enableFolderTree = $this->params['enableFolderTree'];
		$enableClipboard = $this->params['enableclipboard'];
		$enableDropUpload = $this->params['enableDropUpload'];
		$expandFolderTree = $this->params['expandFolderTree'];
		$showtree = $this->params['showtree'];
		$cachedir = $this->params['cachedir'];
		$conversionmgr = $this->params['conversionmgr'];
		$enableRecursiveCount = $this->params['enableRecursiveCount'];
		$maxRecursiveCount = $this->params['maxRecursiveCount'];
		$maxItemsPerPage = $this->params['maxItemsPerPage'];
		$incItemsPerPage = $this->params['incItemsPerPage'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];
		$currenttab = $this->params['currenttab'];

		// charts

		$data = $this->params['data'];
		$type = $this->params['type'];
		$quota = $this->params['quota'];

		$folderid = $folder->getId();
		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		if ($conversionmgr)
			$previewer->setConversionMgr($conversionmgr);
		else
			$previewer->setConverters($previewconverters);
		//		echo $this->callHook('startPage');


		// for charts header
		$this->htmlAddHeader(
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.pie.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.categories.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.time.min.js"></script>' . "\n"
		);
		$this->htmlStartPage(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))));
		$this->globalNavigation($folder);
		$this->contentStart();
		$this->pageSidebar();

		$this->rowStart();
		$this->columnStart(3);
		$this->contentHeading(getMLText("chart_selection"));
		$this->contentContainerStart();
		foreach (array('docsperuser', 'foldersperuser', 'sizeperuser', 'sizepermonth', 'docspermimetype', 'docspercategory', 'docsperstatus', 'docspermonth', 'docsaccumulated') as $atype) {
			if ($this->showChart($atype))
				echo "<div><a href=\"?type=" . $atype . "\">" . getMLText('chart_' . $atype . '_title') . "</a></div>\n";
		}
		$this->contentContainerEnd();
		$this->columnEnd();

		if (in_array($type, array('sizepermonth', 'docspermonth', 'docsaccumulated'))) {
			$this->columnStart(9);
		} else {
			$this->columnStart(6);
		}
		$this->contentHeading(getMLText('chart_' . $type . '_title'));
		$this->contentContainerStart();
		?>
			<div id="chart" style="height: 400px;" class="chart"></div>
			<?php
			$this->contentContainerEnd();



			?>
			<div class="ajax" data-view="ViewFolder" data-action="navigation" data-no-spinner="true" <?php echo ($folder ? "data-query=\"folderid=" . $folder->getID() . "\"" : "") ?>></div>
			<?php
			$this->rowStart();
			echo '<div class="viewFolder-container">';


			// dynamic columns - left column removed if no content and right column then fills span12.
			if (!($enableFolderTree || $enableClipboard)) {
				$LeftColumnSpan = 0;
				$RightColumnSpan = 12;
			} else {
				$LeftColumnSpan = 4;
				$RightColumnSpan = 8;
			}
			if ($LeftColumnSpan > 0) {
				$this->columnStart($LeftColumnSpan);

				echo $this->callHook('leftContentPre');

				if ($enableFolderTree) {
					if ($showtree == 1) {
						$this->contentHeading("<a href=\"" . $this->params['settings']->_httpRoot . "out/out.ViewFolder.php?folderid=" . $folderid . "&showtree=0\"><i class=\"fa fa-minus-circle\"></i></a>", true);
						$this->contentContainerStart();
						/*
						 * access expandFolderTree with $this->params because it can
						 * be changed by preContent hook.
						 */
						$this->printNewTreeNavigationHtml($folderid, M_READ, 0, 'maintree', ($this->params['expandFolderTree'] == 1) ? -1 : 3, $orderby);
						$this->contentContainerEnd();
					} else {
						$this->contentHeading("<a href=\"" . $this->params['settings']->_httpRoot . "out/out.ViewFolder.php?folderid=" . $folderid . "&showtree=1\"><i class=\"fa fa-plus-circle\"></i></a>", true);
					}
				}

				echo $this->callHook('leftContent');

				if ($enableClipboard)
					$this->printClipboard($this->params['session']->getClipboard(), $previewer);

				echo $this->callHook('leftContentPost');

				$this->columnEnd();
			}
			$this->columnStart($RightColumnSpan);

			if ($enableDropUpload/* && $folder->getAccessMode($user) >= M_READWRITE*/) {
				$this->rowStart();
				$this->columnStart(8);
			}
			?>
			<ul class="nav nav-pills" id="folderinfotab" role="tablist">
				<li class="nav-item <?php if (!$currenttab || $currenttab == 'folderinfo')
					echo 'active'; ?>"><a class="nav-link <?php if (!$currenttab || $currenttab == 'folderinfo')
						  echo 'active'; ?>" data-target="#folderinfo" data-toggle="tab"
						role="button"><?php printMLText('folder_infos'); ?></a></li>
				<?php
				$tabs = $this->callHook('extraTabs', $folder);
				if ($tabs) {
					foreach ($tabs as $tabid => $tab) {
						echo '<li class="nav-item ' . ($currenttab == $tabid ? 'active' : '') . '"><a class="nav-link ' . ($currenttab == $tabid ? 'active' : '') . '" data-target="#' . $tabid . '" data-toggle="tab" role="button">' . $tab['title'] . '</a></li>';
					}
				}
				?>
			</ul>
			<div class="tab-content">
				<div class="tab-pane <?php if (!$currenttab || $currenttab == 'folderinfo')
					echo 'active'; ?>" id="folderinfo" role="tabpanel">
					<div class="ajax" data-view="ViewFolder" data-action="folderInfos" data-no-spinner="true" <?php echo ($folder ? "data-query=\"folderid=" . $folder->getID() . "\"" : "") ?>></div>
				</div>
				<?php
				if ($tabs) {
					foreach ($tabs as $tabid => $tab) {
						echo '<div class="tab-pane ' . ($currenttab == $tabid ? 'active' : '') . '" id="' . $tabid . '" role="tabpanel">';
						echo $tab['content'];
						echo "</div>\n";
					}
				}
				?>
			</div>
			<?php
			if ($enableDropUpload/* && $folder->getAccessMode($user) >= M_READWRITE*/) {
				$this->columnEnd();
				$this->columnStart(4);
				?>
				<div class="ajax" data-view="ViewFolder" data-action="dropUpload" data-no-spinner="true" <?php echo ($folder ? "data-query=\"folderid=" . $folder->getID() . "\"" : "") ?>></div>
				<?php

				$this->columnEnd();
				$this->rowEnd();
			}

			echo $this->callHook('rightContentPre');
			?>
			<div class="ajax" data-view="ViewFolder" data-action="folderList" <?php echo ($folder ? "data-query=\"folderid=" . $folder->getID() . "&orderby=" . $orderby . "\"" : "") ?>></div>
			<?php
			echo $this->callHook('rightContentPost');
			$this->columnEnd();
			$this->rowEnd();

			echo $this->callHook('postContent');
			echo '</div>';
			$this->contentEnd();
			$this->htmlEndPage();
	} /* }}} */
}

?>