<?php
/**
 * Implementation of AdminTools view
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

class SeedDMS_View_AdminTools extends SeedDMS_Theme_Style {

    // These parent methods for row/column are IRRELEVANT for the button grid
    // as _display_buttons_for_row will manage its own structure.
	public function rowStart() { /* Parent implementation or empty if not needed by other parts */ }
	public function rowEnd() { /* Parent implementation or empty */ }
	public function columnStart($width = 0) { /* Parent implementation or empty */ }
	public function columnEnd() { /* Parent implementation or empty */ }

    /**
     * Generates HTML for a button. All styling is done via CSS classes
     * defined in the embedded <style> block in show().
     */
	public function rowButton($link, $icon, $label) {
		ob_start();
		// This div is our "column" or "flex item"
		echo "<div class=\"adm-tool-item\">\n";
        // This 'a' tag is our "button"
		echo '<a href="'.$link.'" class="adm-tool-button">';
		echo '<i class="fa fa-'.$icon.' adm-tool-icon"></i>';
		echo '<span class="adm-tool-label">'.getMLText($label).'</span>';
		echo '</a>';
		echo "</div>\n"; // End .adm-tool-item
		return ob_get_clean();
	}

    /**
     * Helper method to display a "row" of buttons.
     * It directly outputs a div with class "adm-tools-visual-row" which will be styled as a flex container.
     */
	private function _display_buttons_for_row($buttons_config_array, $row_index, $accessop, $settings) {
		$visible_buttons = [];
		foreach ($buttons_config_array as $button_config) {
			$condition_met = true;
			if (isset($button_config['access_check'])) {
				$condition_met = $accessop->check_view_access($button_config['access_check']);
			} elseif (isset($button_config['condition'])) {
				$condition_met = $button_config['condition'];
			}
			if ($condition_met) {
				$visible_buttons[] = [
					'link'  => $settings->_httpRoot . $button_config['path'],
					'icon'  => $button_config['icon'],
					'label' => $button_config['label']
				];
			}
		}

		if (empty($visible_buttons)) return;

        // This div acts as the flex container for the buttons in this logical "row".
        echo "<div class=\"adm-tools-visual-row\">\n";

		if ($this->hasHook('startOfRow')) echo $this->callHook('startOfRow', $row_index);
		
        foreach ($visible_buttons as $button) {
			echo $this->rowButton($button['link'], $button['icon'], $button['label']);
		}
		
        if ($this->hasHook('endOfRow')) echo $this->callHook('endOfRow', $row_index);

        echo "</div>\n"; // End .adm-tools-visual-row
	}

	function show() {
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$settings = $this->params['settings'];
		$logfileenable = $this->params['logfileenable'];
		$enablefullsearch = $this->params['enablefullsearch'];
		$accessop = $this->params['accessobject'];

		$this->htmlStartPage(getMLText("admin_tools"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("admin_tools"), "admin_tools");
?>
	<!-- 
        EMBEDDED CSS FOR ADMIN TOOLS - HIGHLY SPECIFIC
    -->
	<style type="text/css">
		/* Reset some potentially conflicting BS2 styles for this specific area */
		#admin-tools div,
		#admin-tools a,
		#admin-tools span,
		#admin-tools i {
			box-sizing: border-box; /* Apply to all elements within for consistency */
		}
        #admin-tools .btn { /* If any .btn class slips in from BS2 */
            white-space: normal !important; /* Critical for text wrapping */
        }

		#admin-tools {
			padding-top: 15px; /* Space from elements above */
            width: 100%; /* Ensure it can use full width */
		}

		/* This is the Flex Container for each logical row of buttons */
		#admin-tools .adm-tools-visual-row {
			display: flex !important;         /* FORCE FLEX */
			flex-wrap: wrap !important;       /* Allow items to wrap to new lines */
			margin-left: -10px !important;    /* Gutter compensation */
			margin-right: -10px !important;   /* Gutter compensation */
            /* Individual items will have margin-bottom */
		}

		/* This is the Flex Item (our "column" wrapper for each button) */
		#admin-tools .adm-tool-item {
			padding-left: 10px !important;    /* Horizontal gutter */
			padding-right: 10px !important;   /* Horizontal gutter */
			margin-bottom: 20px !important;   /* Vertical spacing between items/wrapped rows */
			display: flex !important;         /* To allow the 'a' tag to take full height */
			flex-direction: column !important;/* Ensures 'a' tag behaves as block for height */
			/* flex-basis and max-width will be set by media queries */
		}

		/* The Button (<a> tag) Styling */
		#admin-tools .adm-tool-button {
			display: flex !important;
			flex-direction: column !important;
			justify-content: center !important; /* Vertically center icon & text */
			align-items: center !important;     /* Horizontally center icon & text */
			width: 100% !important;
			height: 100% !important; /* Stretch to fill .adm-tool-item */
			min-height: 130px !important; /* CRITICAL: Adjust for tallest content */
			
			padding: 15px 10px !important; /* Internal padding */
			text-align: center !important;
			
			background-color: #f8f9fa !important;
			color: #333740 !important;
			border: 1px solid #d6d8db !important;
			border-radius: 6px !important;
			text-decoration: none !important;
			box-shadow: 0 2px 5px rgba(0,0,0,0.08) !important;
			transition: all 0.15s ease-in-out !important;

			font-size: 14px !important; /* Adjust for label readability */
			line-height: 1.5 !important;
            /* CRITICAL FOR TEXT WRAPPING */
			white-space: normal !important;
			word-wrap: break-word !important; /* Older browsers */
			overflow-wrap: break-word !important; /* Modern browsers */
		}

		#admin-tools .adm-tool-button:hover,
		#admin-tools .adm-tool-button:focus {
			background-color: #e9ecef !important;
			border-color: #b1b7c1 !important;
			color: #333740 !important;
			box-shadow: 0 4px 10px rgba(0,0,0,0.12) !important;
			transform: translateY(-2px) !important;
		}

		#admin-tools .adm-tool-icon {
			font-size: 2.5em !important; /* Icon size */
			margin-bottom: 12px !important; /* Space below icon */
			color: #4a5464 !important;
		}

		#admin-tools .adm-tool-label {
			display: block !important; /* Ensure it takes its own space for wrapping */
            max-width: 100%;
		}

		/* --- RESPONSIVE COLUMN WIDTHS for .adm-tool-item --- */
		/* Default (XS - Mobile First): 1 column */
		#admin-tools .adm-tool-item {
			flex-basis: 100% !important;
			max-width: 100% !important;
		}

		/* SM (Small screens >= 576px): 2 columns */
		@media (min-width: 576px) {
			#admin-tools .adm-tool-item {
				flex-basis: 50% !important;
				max-width: 50% !important;
			}
		}

		/* MD (Medium screens >= 768px): 3 columns */
		@media (min-width: 768px) {
			#admin-tools .adm-tool-item {
				flex-basis: 33.3333% !important;
				max-width: 33.3333% !important;
			}
		}

		/* LG (Large screens >= 992px): 4 columns */
		@media (min-width: 992px) {
			#admin-tools .adm-tool-item {
				flex-basis: 25% !important;
				max-width: 25% !important;
			}
		}

		/* XL (Extra Large screens >= 1200px): 6 columns */
		@media (min-width: 1200px) {
			#admin-tools .adm-tool-item {
				flex-basis: 16.6666% !important;
				max-width: 16.6666% !important;
			}
		}
	</style>

	<div id="admin-tools">
	<?php if ($this->hasHook('beforeRows')) echo $this->callHook('beforeRows'); ?>
<?php
		// --- Render button rows ---
		// _display_buttons_for_row now handles the .adm-tools-visual-row div creation.
		// No need to override $this->rowStart/rowEnd here.

		$row1_buttons_config = [
			['access_check' => 'UsrMgr',     'path' => "out/out.UsrMgr.php",     "icon" => "user",     "label" => "user_management"],
			['access_check' => 'GroupMgr',   'path' => "out/out.GroupMgr.php",   "icon" => "group",    "label" => "group_management"],
			['access_check' => 'RoleMgr',    'path' => "out/out.RoleMgr.php",    "icon" => "bullseye", "label" => "role_management"],
		];
		$this->_display_buttons_for_row($row1_buttons_config, 1, $accessop, $settings);

		$row2_buttons_config = [
			['access_check' => 'BackupTools', 'path' => "out/out.BackupTools.php", "icon" => "life-saver", "label" => "backup_tools"],
			['condition' => ($logfileenable && $accessop->check_view_access('LogManagement')), 'path' => "out/out.LogManagement.php", "icon" => "list", "label" => "log_management"],
		];
		$this->_display_buttons_for_row($row2_buttons_config, 2, $accessop, $settings);

		$row3_buttons_config = [
			['access_check' => 'DefaultKeywords', 'path' => "out/out.DefaultKeywords.php", "icon" => "reorder", "label" => "global_default_keywords"],
			['access_check' => 'Categories',      'path' => "out/out.Categories.php",      "icon" => "columns", "label" => "global_document_categories"],
			['access_check' => 'AttributeMgr',    'path' => "out/out.AttributeMgr.php",    "icon" => "tags",    "label" => "global_attributedefinitions"],
		];
		$this->_display_buttons_for_row($row3_buttons_config, 3, $accessop, $settings);

		if($this->params['workflowmode'] == 'advanced') {
			$row4_buttons_config = [
				['access_check' => 'WorkflowMgr',        'path' => "out/out.WorkflowMgr.php",        "icon" => "sitemap", "label" => "global_workflows"],
				['access_check' => 'WorkflowStatesMgr',  'path' => "out/out.WorkflowStatesMgr.php",  "icon" => "star",    "label" => "global_workflow_states"],
				['access_check' => 'WorkflowActionsMgr', 'path' => "out/out.WorkflowActionsMgr.php", "icon" => "bolt",    "label" => "global_workflow_actions"],
			];
			$this->_display_buttons_for_row($row4_buttons_config, 4, $accessop, $settings);
		}

		if($enablefullsearch) {
			$row5_buttons_config = [
				['access_check' => 'Indexer',    'path' => "out/out.Indexer.php",    "icon" => "refresh",     "label" => "update_fulltext_index"],
				['access_check' => 'CreateIndex','path' => "out/out.CreateIndex.php","icon" => "search",      "label" => "create_fulltext_index"],
				['access_check' => 'IndexInfo',  'path' => "out/out.IndexInfo.php",  "icon" => "info-circle", "label" => "fulltext_info"],
			];
			$this->_display_buttons_for_row($row5_buttons_config, 5, $accessop, $settings);
		}

		$row6_buttons_config = [
			['access_check' => 'Statistic',   'path' => "out/out.Statistic.php",   "icon" => "sitemap",    "label" => "folders_and_documents_statistic"],
			['access_check' => 'Charts',      'path' => "out/out.Charts.php",      "icon" => "bar-chart",  "label" => "charts"],
			['access_check' => 'ObjectCheck', 'path' => "out/out.ObjectCheck.php", "icon" => "check",      "label" => "objectcheck"],
			['access_check' => 'Timeline',    'path' => "out/out.Timeline.php",    "icon" => "signal",     "label" => "timeline"],
		];
		$this->_display_buttons_for_row($row6_buttons_config, 6, $accessop, $settings);

		$row7_buttons_config = [
			['access_check' => 'Settings',          'path' => "out/out.Settings.php",          "icon" => "wrench",      "label" => "settings"],
			['access_check' => 'ExtensionMgr',      'path' => "out/out.ExtensionMgr.php",      "icon" => "cogs",        "label" => "extension_manager"],
			['access_check' => 'SchedulerTaskMgr',  'path' => "out/out.SchedulerTaskMgr.php",  "icon" => "clock-o",     "label" => "scheduler_task_mgr"],
			['access_check' => 'Info',              'path' => "out/out.Info.php",              "icon" => "info-circle", "label" => "version_info"],
		];
		$this->_display_buttons_for_row($row7_buttons_config, 7, $accessop, $settings);
?>
	<?php if ($this->hasHook('afterRows')) echo $this->callHook('afterRows'); ?>
	</div>
<?php
		$this->contentEnd();
		$this->htmlEndPage();
	}
}
?>
