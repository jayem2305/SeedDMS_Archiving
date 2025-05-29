<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Implementation of Charts view (Dashboard View)
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

class SeedDMS_View_Dashboard extends SeedDMS_Theme_Style
{
    private $_allowedChartTypes = [
        'docspermonth',
        'docsperuser',
        'docsaccumulated'
    ];

	private function prepareJsChartData()
	{ /* {{{ */
		$allChartDataPHP = isset($this->params['allChartData']) ? $this->params['allChartData'] : [];
		$jsChartDataArray = [];
		$dms = isset($this->params['dms']) ? $this->params['dms'] : null;

		foreach ($this->_allowedChartTypes as $type) {
            if (!isset($allChartDataPHP[$type])) {
                continue;
            }
            $data = $allChartDataPHP[$type];

			if (!$this->showChart($type, $dms)) {
				continue;
			}

			$chartSpecificJsData = [];
			if (empty($data)) {
				$jsChartDataArray[] = ['type' => $type, 'data' => [], 'divId' => 'chart_' . $type,];
				continue;
			}

			if ($type === 'docspermonth') {
				foreach ($data as $rec) {
					$chartSpecificJsData[] = [(string) (isset($rec['key']) ? htmlspecialchars_decode($rec['key']) : 'N/A'), (int) (isset($rec['total']) ? $rec['total'] : 0)];
				}
			} elseif ($type === 'docsaccumulated') {
				foreach ($data as $rec) {
					$jsTimestamp = isset($rec['key']) && is_numeric($rec['key']) ? (int) $rec['key'] : 0;
					$chartSpecificJsData[] = [$jsTimestamp, (int) (isset($rec['total']) ? $rec['total'] : 0)];
				}
			} elseif ($type === 'docsperuser') {
                $pieFormattedData = [];
                foreach ($data as $rec) {
                    $pieFormattedData[] = ['label' => htmlspecialchars(isset($rec['key']) ? $rec['key'] : 'N/A'), 'data' => (int) (isset($rec['total']) ? $rec['total'] : 0)];
                }
                $chartSpecificJsData = $pieFormattedData;
			}

			$jsChartDataArray[] = ['type' => $type, 'data' => $chartSpecificJsData, 'divId' => 'chart_' . $type,];
		}
		return $jsChartDataArray;
	} /* }}} */

    function js()
    { /* {{{ */
        header('Content-Type: application/javascript; charset=UTF-8');

        $jsChartDataArray = $this->prepareJsChartData();
        $noDataText = "No data available for this chart";
        if (function_exists('getMLText') && class_exists('SeedDMS_Core_DMS')) {
            $noDataText = getMLText('no_data_available');
        } elseif (method_exists($this, 'getMLText')) {
            $noDataText = $this->getMLText('no_data_available');
        }
        $monthNamesJs = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        if (function_exists('getMLText') && class_exists('SeedDMS_Core_DMS')) {
            $mlMonthNames = getMLText("datetime_monthname_short");
            if (is_array($mlMonthNames))
                $monthNamesJs = array_values($mlMonthNames);
        } elseif (method_exists($this, 'getMLText')) {
            $mlMonthNames = $this->getMLText("datetime_monthname_short");
            if (is_array($mlMonthNames))
                $monthNamesJs = array_values($mlMonthNames);
        }

        $enableDropUploadOnDashboard = isset($this->params['enableDropUploadOnDashboard']) ? $this->params['enableDropUploadOnDashboard'] : false;
        $dashboardUploadFolder = isset($this->params['dashboardUploadFolder']) ? $this->params['dashboardUploadFolder'] : null;
        $maxuploadsize = isset($this->params['maxuploadsize']) ? $this->params['maxuploadsize'] : 0;
        $httpRoot = isset($this->params['httpRoot']) ? $this->params['httpRoot'] : '/';

        parent::jsTranslations(array('cancel', 'edit_document_props', 'uploading_maxsize'));

        ?>
            jQuery(document).ready(function($) {
                console.log("START Dashboard JS (Combined): Document ready!");

                $("<div id='chart_tooltip'></div>").css({
                    position: "absolute", display: "none", padding: "5px", color: "white",
                    "background-color": "#000", "border-radius": "5px", opacity: 0.80, zIndex: 1050
                }).appendTo("body");

                <?php if ($enableDropUploadOnDashboard && $dashboardUploadFolder): ?>
                        console.log("Setting up Drop Upload for Dashboard to folder ID: <?php echo $dashboardUploadFolder->getID(); ?>");
                        if (typeof SeedDMSUpload !== 'undefined') {
                            SeedDMSUpload.setUrl('<?php echo $httpRoot; ?>op/op.Ajax.php');
                            SeedDMSUpload.setAbortBtnLabel('<?php echo getMLText("cancel"); ?>');
                            SeedDMSUpload.setEditBtnLabel('<?php echo getMLText("edit_document_props"); ?>');
                            SeedDMSUpload.setMaxFileSize(<?php echo $maxuploadsize; ?>);
                            SeedDMSUpload.setMaxFileSizeMsg('<?php echo getMLText("uploading_maxsize"); ?>');
                            SeedDMSUpload.initDropZone($('#dashboard-drop-zone'), <?php echo $dashboardUploadFolder->getID(); ?>);
                        } else {
                            console.error("SeedDMSUpload object is not defined. Drop Upload will not work.");
                            $('#dashboard-drop-zone').html("<p style='color:red;'>File drop upload is misconfigured (SeedDMSUpload missing).</p>");
                        }
                <?php endif; ?>

                if (typeof $.plot === 'undefined') {
                    console.error("Dashboard JS FATAL: Flot ($.plot) is not loaded.");
                    $('.chart').html("<p style='color:red; text-align:center;'>Error: Charting library not loaded.</p>");
                } else {
                    var allChartsData = <?php echo json_encode($jsChartDataArray, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK); ?>;
                    var noDataMessage = <?php echo json_encode($noDataText); ?>;
                    var monthNamesForFlot = <?php echo json_encode($monthNamesJs); ?>;
                    console.log("Dashboard JS (Combined): Charts Data:", allChartsData);

                    function pieLabelFormatter(label, series) {
                        var displayPercent = 'N/A';
                        if (series && typeof series.percent === 'number' && !isNaN(series.percent)) {
                             displayPercent = Math.round(series.percent) + "%";
                        } else {
                             console.warn("pieLabelFormatter (Percent Only): Invalid percent data received for label '" + label + "'. Series:", series);
                        }
                        return "<div style='font-size:8pt; line-height:14px; text-align:center; padding:2px; color:black; background:white; border-radius:5px;'>" +
                               displayPercent + "</div>";
                    }

                    function formatFileSizeForTooltip(bytes) {
                        if (bytes === 0) return '0 Bytes'; const k = 1024; const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']; const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }

                    if (!allChartsData || !Array.isArray(allChartsData) || allChartsData.length === 0) {
                        console.warn("Dashboard JS (Combined): No chart configurations to process.");
                    } else {
                        console.log("Dashboard JS (Combined): Processing " + allChartsData.length + " chart configurations.");
                        allChartsData.forEach(function(chartInfo, index) {
                            var chartDivSelector = "#" + chartInfo.divId; var plotDataFromPhp = chartInfo.data;
                            if (!$(chartDivSelector).length) { console.error("Dashboard JS: Chart div NOT FOUND: " + chartDivSelector); return; }
                            $(chartDivSelector).empty();
                            if (!plotDataFromPhp || !Array.isArray(plotDataFromPhp) || plotDataFromPhp.length === 0) {
                                $(chartDivSelector).html("<p style='text-align:center; padding-top:50px;'>" + noDataMessage + "</p>"); return;
                            }
                            try {
                                var options = { grid: { hoverable: true, clickable: true, borderWidth: 1, borderColor: '#ddd' } }; var finalPlotData;
                                if (chartInfo.type === 'docspermonth') {
                                    finalPlotData = [plotDataFromPhp]; options.xaxis = { mode: "categories", tickLength: 0 };
                                    options.series = { bars: { show: true, align: "center", barWidth: 0.7, fill: 0.8 } }; options.legend = { show: false };
                                    $(chartDivSelector).bind("plothover", function(e,p,i){ $("#chart_tooltip").hide(); if(i){ var x=i.series.xaxis.ticks[i.dataIndex].label; var y=i.datapoint[1]; $("#chart_tooltip").html(x+": "+y).css({top:i.pageY-30,left:i.pageX+5}).fadeIn(200);}});
                                } else if (chartInfo.type === 'docsaccumulated') {
                                    finalPlotData = [plotDataFromPhp]; options.xaxis = { mode: "time", timeformat: "%d.%m.%y", monthNames: monthNamesForFlot };
                                    options.series = { lines: { show: true, fill: 0.2 }, points: { show: true, radius: 3 } }; options.legend = { position: "nw" };
                                    $(chartDivSelector).bind("plothover", function(e,p,i){ $("#chart_tooltip").hide(); if(i){ $("#chart_tooltip").html($.plot.formatDate(new Date(i.datapoint[0]),'%e. %b %Y')+": "+i.datapoint[1]).css({top:i.pageY-30,left:i.pageX+5}).fadeIn(200);}});
                                } else if (chartInfo.type === 'docsperuser') {
                                    finalPlotData = plotDataFromPhp; options.series = { pie: { show: true, radius: 1, label: { show: true, radius: 3/4, formatter: pieLabelFormatter, threshold: 0.03, background: {opacity:0.6,color:'#fff'}}}};
                                    var lc=$(chartDivSelector).closest('.chart-container-wrapper').find('.legend-container'); options.legend=lc.length?{container:lc,labelBoxBorderColor:"none"}:{show:true,noColumns:2,labelBoxBorderColor:"none"};
                                    $(chartDivSelector).bind("plothover", function(e,p,i){ $("#chart_tooltip").hide(); if(i){ var y=i.series.data; $("#chart_tooltip").html(i.series.label+": "+y+" ("+Math.round(i.series.percent)+"%)").css({top:p.pageY-30,left:p.pageX+5}).fadeIn(200);}});
                                }
                                if(finalPlotData){ console.log("Dashboard JS: Plotting " + chartInfo.type); $.plot(chartDivSelector, finalPlotData, options); }
                                else { console.error("Dashboard JS: finalPlotData undefined for " + chartInfo.type); $(chartDivSelector).html("<p style='color:red;'>Plot config error.</p>");}
                            } catch(e) { console.error("Dashboard JS: CATCH plotting " + chartInfo.divId, e.message, e.stack); $(chartDivSelector).html("<p style='color:red;'>JS render error.</p>");}
                        });
                    }
                }
                console.log("END Dashboard JS (Combined): Finished.");
            });
            <?php
    } /* }}} */

	private function showChart($type, $dms = null)
	{ /* {{{ */
		if (!$dms) $dms = isset($this->params['dms']) ? $this->params['dms'] : null;
		if (!$dms) return true;
		return true;
	} /* }}} */

    private function _renderChartAndTable($chartType, $allChartData, $dms, $quota) { /* {{{ */
        if (!isset($allChartData[$chartType])) return;
        if (!$this->showChart($chartType, $dms)) return;

        $currentChartDataForTable = $allChartData[$chartType];
        $isPieChart = ($chartType === 'docsperuser');
        
        $chartTitle = "Chart: " . $chartType;
        if (function_exists('getMLText')) $chartTitle = getMLText('chart_' . $chartType . '_title');
        elseif (method_exists($this, 'getMLText')) $chartTitle = $this->getMLText('chart_' . $chartType . '_title');
        
        echo '<div class="chart-container-wrapper" style="margin-bottom:40px; padding:10px; border:1px solid #eee; border-radius:8px; background-color:#fdfdfd; box-shadow:0 2px 4px rgba(0,0,0,0.05);">';
        $this->contentHeading($chartTitle);
        
        $this->rowStart();
        $chartPlotColumnClass = $isPieChart ? 'col-md-8' : 'col-md-12';
        $this->columnStart($chartPlotColumnClass);
        $this->contentContainerStart('chart-plot-area', '', 'background-color:#fff; padding:10px; border:1px solid #e0e0e0; border-radius:4px; min-height:420px;');
        ?>
        <div id="chart_<?php echo htmlspecialchars($chartType); ?>" style="height:400px; width:100%;" class="chart"><p style="text-align:center; padding-top:180px; color:#777;">Loading chart...</p></div>
        <?php
        $this->contentContainerEnd(); 
        $this->columnEnd();




										    
        if ($isPieChart) {
            $this->columnStart('col-md-4');
            $legendTitle = "Legend";
            if (function_exists('getMLText')) $legendTitle = getMLText('legend');
            elseif (method_exists($this, 'getMLText')) $legendTitle = $this->getMLText('legend');
            echo "<h5 style='margin-top:0; margin-bottom:10px; font-size:1.1em; color:#333;'>" . htmlspecialchars($legendTitle) . "</h5>";
            $this->contentContainerStart('legend-area', '', 'background-color:#fff; padding:10px; border:1px solid #e0e0e0; border-radius:4px; min-height:420px;');
            echo '<div class="legend-container" style="height:400px; overflow-y:auto;"></div>';
            $this->contentContainerEnd(); 
            $this->columnEnd();
        }
        $this->rowEnd();

        if (!empty($currentChartDataForTable)) {
            $this->rowStart('style="margin-top:20px;"'); $this->columnStart(12);
            // $dataTableTitle = "Data for: ".htmlspecialchars($chartTitle);
            // if (function_exists('getMLText')) $dataTableTitle = getMLText('chart_data_table_title_generic', ['chartname'=>htmlspecialchars($chartTitle)]);
            // elseif (method_exists($this, 'getMLText')) $dataTableTitle = $this->getMLText('chart_data_table_title_generic', ['chartname'=>htmlspecialchars($chartTitle)]);
            // echo "<h5 style='margin-top:10px; margin-bottom:10px; font-size:1.1em; color:#333;'>".$dataTableTitle."</h5>";
            echo "<div class='table-responsive'><table class=\"table table-bordered table-striped table-sm table-hover\" style=\"margin-bottom:0; background-color:#fff;\">";
            echo "<thead class=\"thead-light\"><tr>";
            $headerKeyText = "Key";
            if (function_exists('getMLText')) $headerKeyText = getMLText('chart_table_header_key');
            elseif (method_exists($this, 'getMLText')) $headerKeyText = $this->getMLText('chart_table_header_key');
            echo "<th>".htmlspecialchars($headerKeyText)."</th>";
            $headerTotalText = "Total";
            if (function_exists('getMLText')) $headerTotalText = getMLText('total');
            elseif (method_exists($this, 'getMLText')) $headerTotalText = $this->getMLText('total');
            echo "<th class='text-right'>".htmlspecialchars($headerTotalText)."</th>";
            $typesWithExtraColumn = [];
            if ($chartType==='docspermonth' || $chartType==='docsaccumulated') $typesWithExtraColumn[]=$chartType;
            if (in_array($chartType, $typesWithExtraColumn)) {
                $extraColText = "Change";
                if (function_exists('getMLText')) $extraColText = getMLText('change');
                elseif (method_exists($this, 'getMLText')) $extraColText = $this->getMLText('change');
                echo "<th class='text-right'>".htmlspecialchars($extraColText)."</th>";
            }
            echo "</tr></thead><tbody>";
            $grandTotal = 0; $oldtotal = 0;
            foreach ($currentChartDataForTable as $item) {
                echo "<tr>";
                $itemKey = isset($item['key'])?$item['key']:'N/A'; $itemTotal = isset($item['total'])?(int)$item['total']:0;
                if ($chartType == 'docsaccumulated') {
                    $dateDisplay = '';
                    if (isset($item['key']) && is_numeric($item['key'])) {
                        $tsForKey=$item['key']; $unixTsForReadableDate=$tsForKey/1000;
                        if(function_exists('getReadableDate')) $dateDisplay=getReadableDate($unixTsForReadableDate);
                        elseif(method_exists($this,'getReadableDate')) $dateDisplay=$this->getReadableDate($unixTsForReadableDate);
                        else $dateDisplay=date('d.m.Y',$unixTsForReadableDate);
                    }
                    echo "<td>".htmlspecialchars($dateDisplay)."</td>";
                } else echo "<td>".htmlspecialchars($itemKey)."</td>";
                echo "<td class='text-right'>".$itemTotal."</td>"; $grandTotal+=$itemTotal;
                if (in_array($chartType, $typesWithExtraColumn)) {
                    echo "<td class='text-right'>";
                    if ($chartType=='docspermonth' || $chartType=='docsaccumulated') { $change=$itemTotal-$oldtotal; echo sprintf('%+d',$change); $oldtotal=$itemTotal; }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody><tfoot><tr class='font-weight-bold table-info'><td>";
            $totalOverallText = "Overall Total";
            if (function_exists('getMLText')) $totalOverallText = getMLText('total_overall');
            elseif (method_exists($this, 'getMLText')) $totalOverallText = $this->getMLText('total_overall');
            echo htmlspecialchars($totalOverallText)."</td><td class='text-right'>";
            if ($chartType=='docsaccumulated') echo $oldtotal; else echo $grandTotal;
            echo "</td>"; if (in_array($chartType, $typesWithExtraColumn)) echo "<td></td>";
            echo "</tr></tfoot></table></div>"; $this->columnEnd(); $this->rowEnd();
        } else {
            $this->rowStart('style="margin-top:15px;"'); $this->columnStart(12);
            $noDataTableText = "No data to display in table.";
            if (function_exists('getMLText')) $noDataTableText = getMLText('no_data_for_table');
            elseif (method_exists($this, 'getMLText')) $noDataTableText = $this->getMLText('no_data_for_table');
            echo "<p class='text-center'><em>".htmlspecialchars($noDataTableText)."</em></p>";
            $this->columnEnd(); $this->rowEnd();
        }
        echo '</div>';
    } /* }}} */


	public function show()
	{ /* {{{ */

        $enableDropUpload = $this->params['enableDropUpload'];
        $folder = $this->params['folder'];


        $dms = isset($this->params['dms']) ? $this->params['dms'] : null;
		$user = isset($this->params['user']) ? $this->params['user'] : null;
		$settings = isset($this->params['settings']) ? $this->params['settings'] : null;
		$quota = 0;
        if ($settings) {
            if (isset($settings->quota)) $quota = $settings->quota;
            elseif (isset($settings->_quota)) $quota = $settings->_quota;
        }
        if (!$quota && isset($this->params['quota'])) $quota = $this->params['quota'];

		$allChartData = isset($this->params['allChartData']) ? $this->params['allChartData'] : [];
        
        $enableDropUploadOnDashboard = isset($this->params['enableDropUploadOnDashboard']) ? $this->params['enableDropUploadOnDashboard'] : false;
        $dashboardUploadFolder = isset($this->params['dashboardUploadFolder']) ? $this->params['dashboardUploadFolder'] : null;

		$this->htmlAddHeader(
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.pie.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.categories.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.time.min.js"></script>' . "\n" .
            '<script type="text/javascript" src="../styles/bootstrap/flot/jquery.flot.resize.min.js"></script>' . "\n" .
			'<script type="text/javascript" src="out.Dashboard.js.php"></script>' . "\n"
		);

		$pageTitle = "Folders and Documents Statistic";
		if (function_exists('getMLText')) $pageTitle = getMLText("folders_and_documents_statistic");
		elseif (method_exists($this, 'getMLText')) $pageTitle = $this->getMLText("folders_and_documents_statistic");
		$this->htmlStartPage($pageTitle);
		$this->globalNavigation();
		$this->contentStart();

		$adminToolsText = "Admin Tools";
		if (function_exists('getMLText')) $adminToolsText = getMLText("admin_tools");
		elseif (method_exists($this, 'getMLText')) $adminToolsText = $this->getMLText("admin_tools");

        $this->pageSidebar();
		// $this->pageNavigation($adminToolsText, "admin_tools");

        

        

		echo '<div class="charts-dashboard-container" style="padding:15px;">';
        
        echo '<div class="dashboard-container">';
        echo '<h1 class="content-header">';
        $this->contentHeading(getMLText(key: "dashboard"));
        echo '</h1>';

        echo '        <div class="container dash1">';
        echo '            <div class="dash-details">';
        echo '                <div class="details-grid">';
        echo '                    <div class="text-details">';
        echo '                    <h3>Files</h3>';
        echo '                    <h1>907</h1>';
        echo '                    </div>';
        echo '                    <div class="rightside">';
        echo '                        <i class="fa fa-file fa-lg"></i>';
        echo '                    </div>';
        echo '                </div>';
        echo '<div class="details-grid">';
        echo '                    <div class="text-details">';
        echo '                    <h3>Folders</h3>';
        echo '                    <h1>907</h1>';
        echo '                    </div>';
        echo '                    <div class="rightside">';
        echo '                        <i class="fa fa-folder fa-lg"></i>';
        echo '                    </div>';
        echo '                </div>';
        echo '<div class="details-grid">';
        echo '                    <div class="text-details">';
        echo '                    <h3>Users</h3>';
        echo '                    <h1>907</h1>';
        echo '                    </div>';
        echo '                    <div class="container rightside">';
        echo '                        <i class="fa fa-user fa-lg"></i>';
        echo '                    </div>';
        echo '                </div>';
        echo '<div class="details-grid">';
        echo '                    <div class="text-details">';
        echo '                    <h3>Disk Space</h3>';
        echo '                    <h1>907</h1>';
        echo '                    </div>';
        echo '                    <div class="rightside">';
        echo '                        <i class="fa fa-database fa-lg"></i>';
        echo '                    </div>';
        echo '                </div>';
        echo '            </div>';
        echo '            <div class="dash-upload">';

        if ($enableDropUpload/* && $folder->getAccessMode($user) >= M_READWRITE*/) {
            $this->columnStart(4);
            ?>
                        <div class="ajax" data-view="ViewFolder" data-action="dropUpload" data-no-spinner="true" <?php echo ($folder ? "data-query=\"folderid=" . $folder->getID() . "\"" : "") ?>></div>
                        <?php
                        echo '<i id="cloud-icon" class="fa fa-cloud fa-lg"></i>';
                        echo '<p>Drop Files here or browse files or browse folders</p>';
                        echo '<p class="text-danger">Note: File extension exe. is not acceptable</p>';
                        $this->columnEnd();
                        $this->rowEnd();
        }
        $this->columnEnd();

        echo '        </div>';
        echo '        </div>';
        echo '        </div>';

        $this->rowStart('mb-4'); 

        $this->columnStart(6);
        if (in_array('docspermonth', $this->_allowedChartTypes)) {
            $this->_renderChartAndTable('docspermonth', $allChartData, $dms, $quota);
        }
        $this->columnEnd();

        $this->columnStart(6);
        if (in_array('docsperuser', $this->_allowedChartTypes)) {
            $this->_renderChartAndTable('docsperuser', $allChartData, $dms, $quota);
        }
        $this->columnEnd();

        $this->rowEnd();

        $this->rowStart('mb-4');

        $this->columnStart(12);
        if (in_array('docsaccumulated', $this->_allowedChartTypes)) {
            $this->_renderChartAndTable('docsaccumulated', $allChartData, $dms, $quota);
        }
        $this->columnEnd();

        $this->rowEnd();

        $chartsAttempted = count(array_filter($this->_allowedChartTypes, function($type) use ($allChartData) {
            return isset($allChartData[$type]);
        }));

        if ($chartsAttempted == 0) {
            echo "<div class='alert alert-info text-center' role='alert'>";
            $noChartsText = "No charts are available.";
            if (function_exists('getMLText')) $noChartsText = getMLText('no_charts_to_display');
            elseif (method_exists($this, 'getMLText')) $noChartsText = $this->getMLText('no_charts_to_display');
            echo htmlspecialchars($noChartsText); echo "</div>";
        }
		echo '</div>'; 
        $this->contentEnd(); 
        $this->htmlEndPage();
	} /* }}} */
}
?>
