<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($settings))
	require_once("../inc/inc.Settings.php");
require_once("inc/inc.Utils.php");
require_once("inc/inc.LogInit.php");
require_once("inc/inc.Language.php");
require_once("inc/inc.Init.php");
require_once("inc/inc.Extension.php");
require_once("inc/inc.DBInit.php");
require_once("inc/inc.Authentication.php");
require_once("inc/inc.ClassUI.php");

$tmp = explode('.', basename($_SERVER['SCRIPT_FILENAME']));
$viewName = $tmp[1];
if (!isset($dms))
	$dms = new SeedDMS_Core_DMS();
if (!isset($user))
	$user = $dms->getUser();

$view = UI::factory($theme, $viewName, array('dms' => $dms, 'user' => $user, 'settings' => $settings));
$accessop = new SeedDMS_AccessOperation($dms, $user, $settings);

if (!$accessop->check_view_access($view, array())) {
	UI::exitError(getMLText("admin_tools"), getMLText("access_denied"));
}

if (!isset($_GET["folderid"]) || !is_numeric($_GET["folderid"]) || intval($_GET["folderid"]) < 1) {
	$folder = $dms->getRootFolder();
} else {
	$folder = $dms->getFolder(intval($_GET["folderid"]));
}

if (!is_object($folder)) {
	UI::exitError(getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))), getMLText("invalid_folder_id"));
}

if ($folder->getAccessMode($user) < M_READ) {
	UI::exitError(getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))), getMLText("access_denied"));
}

$selectedChartTypes = [
	'docspermonth',
	'docsperuser',
	'docsaccumulated'
];
$allChartData = [];
foreach ($selectedChartTypes as $currentType) {
	$dataForType = $dms->getStatisticalData($currentType);
	if ($dataForType) {
		switch ($currentType) {
			// Add specific data transformations here if needed for selected types
		}
		$allChartData[$currentType] = $dataForType;
	} else {
		$allChartData[$currentType] = [];
	}
}

$totalDocuments = 0;
if (method_exists($dms, 'getOverallDocumentCount'))
	$totalDocuments = $dms->getOverallDocumentCount();
$totalFolders = 0;
if (method_exists($dms, 'getOverallFolderCount'))
	$totalFolders = $dms->getOverallFolderCount();
$totalUsers = 0;
if (method_exists($dms, 'getUserCount'))
	$totalUsers = $dms->getUserCount();
$totalDiskSpaceUsed = 0;
if (method_exists($dms, 'getTotalDiskSpaceUsed'))
	$totalDiskSpaceUsed = $dms->getTotalDiskSpaceUsed();

if ($view) {
	$view->setParam('folder', $folder);

	$view->setParam('availableChartTypes', $selectedChartTypes);
	$view->setParam('allChartData', $allChartData);

	$view->setParam('totalDocuments', $totalDocuments);
	$view->setParam('totalFolders', $totalFolders);
	$view->setParam('totalUsers', $totalUsers);
	$view->setParam('totalDiskSpaceUsed', $totalDiskSpaceUsed);

	$enableDropUploadOnDashboard = false;
	if ($folder->getAccessMode($user) >= M_READWRITE && isset($settings->enabledropupload) && $settings->enabledropupload) {
		$enableDropUploadOnDashboard = true;
	}
	$view->setParam('enableDropUpload', $settings->_enableDropUpload);
	$view->setParam('dashboardUploadFolder', $folder);
	// $view->setParam('maxuploadsize', $settings->maxuploadsize);
	$view->setParam('httpRoot', $settings->_httpRoot);

	$view->setParam('accessobject', $accessop);
	$view->setParam('settings', $settings);
	$view->setParam('quota', isset($settings->quota) ? $settings->quota : (isset($settings->_quota) ? $settings->_quota : 0));

	$view($_GET);
	exit;
}