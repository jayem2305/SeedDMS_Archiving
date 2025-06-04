<?php
if (!isset($settings)) {
    require_once("../inc/inc.Settings.php");
}
require_once("inc/inc.Utils.php");
require_once("inc/inc.LogInit.php");
require_once("inc/inc.Language.php");
require_once("inc/inc.Init.php");
require_once("inc/inc.Extension.php");
require_once("inc/inc.DBInit.php");
require_once("inc/inc.Authentication.php"); // This should initialize $dms, $user, $session
require_once("inc/inc.ClassUI.php");

$tmp = explode('.', basename($_SERVER['SCRIPT_FILENAME'])); // $tmp[1] will be 'Dashboard'
$theme = $settings->_theme ?? 'bootstrap';

// Prepare base parameters for the view
$viewparams = [
    'dms' => $dms,
    'user' => $user,
    'settings' => $settings,
    'session' => $session, // Make sure $session is initialized (usually in inc.Authentication.php or inc.Init.php)
    'absbaseprefix' => $config['absbaseprefix'] ?? '', // $config might come from inc.Settings.php
    'cachedir' => $settings->_cacheDir,
    'fulltextservice' => $fulltextservice ?? null, // Ensure these are initialized if used by view
    'conversionmgr' => $conversionmgr ?? null,
    'showtree' => showtree(),
    'previewWidthList' => $settings->_previewWidthList ?? 64,
    'previewConverters' => isset($settings->_converters['preview']) ? $settings->_converters['preview'] : [],
    'convertToPdf' => $settings->_convertToPdf ?? false,
    'timeout' => $settings->_cmdTimeout ?? 30,
    'dayspastdashboard' => (int) ($settings->_daysPastDashboard ?? 7),
    'excludedfolders' => $settings->_excludeFoldersDashboard ?? [],
    'xsendfile' => $settings->_enableXsendfile ?? false,
    'httpRoot' => $settings->_httpRoot, // Essential for links in the view
    'onepage' => $settings->_onepage ?? true,
    'printdisclaimer' => $settings->_printdisclaimer ?? false,
    'footnote' => $settings->_footnote ?? '',
    'showmissingtranslations' => $settings->_showmissingtranslations ?? false,
    'enablemenutasks' => $settings->_enablemenutasks ?? false,
    'enabledropfolderlist' => $settings->_enabledropfolderlist ?? false,
    'enablelanguageselector' => $settings->_enablelanguageselector ?? false,
    'enableclipboard' => $settings->_enableclipboard ?? false,
    'enablesessionlist' => $settings->_enablesessionlist ?? false,
    'dropfolderdir' => $settings->_dropfolderdir ?? '',
    'enablecalendar' => $settings->_enablecalendar ?? false,
    'calendardefaultview' => $settings->_calendardefaultview ?? 'month',
    'enablehelp' => $settings->_enablehelp ?? false,
    'defaultsearchmethod' => $settings->_defaultsearchmethod ?? 'metadata',
    'sitename' => $settings->_sitename ?? 'SeedDMS',
    'folder' => $folder ?? null, // If $folder is relevant for this page context
    'enableDropUploadOnDashboard' => $settings->_enableDropUploadOnDashboard ?? false,
    'maxuploadsize' => $settings->_maxuploadsize ?? ini_get('upload_max_filesize'),
    // Resolve dashboardUploadFolder: View expects folder object or ID.
    // IMPORTANT: Ensure $settings->_dashboardUploadFolderID stores the NUMERIC ID of the target folder.
    'dashboardUploadFolder' => call_user_func(function() use ($settings, $dms, $user) {
        // Ensure this setting key matches what you have in your settings.xml or settings source
        $folderId = isset($settings->_dashboardUploadFolderID) ? (int)$settings->_dashboardUploadFolderID : null; 
        
        // error_log("out.Dashboard.php: DEBUG UPLOAD PARAMS - _enableDropUploadOnDashboard: " . var_export($settings->_enableDropUploadOnDashboard ?? 'not set', true)); // For debugging
        // error_log("out.Dashboard.php: DEBUG UPLOAD PARAMS - _dashboardUploadFolderID from settings: " . var_export($folderId, true)); // For debugging

        if ($folderId && $dms instanceof SeedDMS_Core_DMS) {
            $folderObj = $dms->getFolder($folderId);
            if ($folderObj instanceof SeedDMS_Core_Folder) {
                // error_log("out.Dashboard.php: DEBUG UPLOAD PARAMS - Successfully fetched folder object for ID " . $folderId . ". Name: " . $folderObj->getName()); // For debugging
                
                // Optional: Check user write access to this specific folder for dashboard uploads
                // if ($user && method_exists($folderObj, 'getAccessMode') && $folderObj->getAccessMode($user) >= M_READWRITE) {
                //    return $folderObj; // User has write access
                // } else {
                //    error_log("out.Dashboard.php: User ID " . ($user ? $user->getID() : 'N/A') . " does not have write access to dashboard upload folder ID " . $folderId);
                //    return null; // Deny if no write access, even if globally enabled
                // }
                return $folderObj; // Pass the folder object
            } else {
                error_log("out.Dashboard.php: ERROR - Configured _dashboardUploadFolderID " . $folderId . " not found or is not a folder instance.");
                return null;
            }
        }
        if ($folderId && !($dms instanceof SeedDMS_Core_DMS)) {
            error_log("out.Dashboard.php: ERROR - \$dms object not available to resolve _dashboardUploadFolderID.");
        }
        if (!$folderId && ($settings->_enableDropUploadOnDashboard ?? false)) {
             error_log("out.Dashboard.php: WARNING - _dashboardUploadFolderID setting is not set or is null, but _enableDropUploadOnDashboard is true.");
        }
        return null; // Return null if not configured, folder not found, or DMS not available
    }),
];

if (class_exists('SeedDMS_AccessOperation')) {
    $viewparams['accessobject'] = new SeedDMS_AccessOperation($dms, $user, $settings);
}


// --- Fetch Chart Data for the Dashboard ---
$allChartData = [];
// Define the specific charts you want on the dashboard
$dashboardChartTypes = [
    'docsperuser',
    'docspermonth',
    'docsperstatus',
    'docsaccumulated',
	'foldersperuser',
    'sizeperuser'
];

if ($dms && method_exists($dms, 'getStatisticalData')) {
    foreach ($dashboardChartTypes as $chartType) {
        $dataForType = $dms->getStatisticalData($chartType);
        if ($dataForType) {
            // Apply specific transformations if needed (like for 'docsperstatus')
            if ($chartType == 'docsperstatus') {
                foreach ($dataForType as &$rec) {
                    if (isset($rec['key'])) {
                        $rec['key'] = getOverallStatusText((int) $rec['key']);
                    }
                }
                unset($rec); // Unset reference
            }
            $allChartData[$chartType] = $dataForType;
        } else {
            // If no data, pass an empty array so the chart can display "No data"
            $allChartData[$chartType] = [];
        }
    }
} else {
    error_log("Dashboard Controller: SeedDMS_Core_DMS::getStatisticalData method not found or \$dms object not available.");
    // Initialize with empty arrays if data fetching fails
    foreach ($dashboardChartTypes as $chartType) {
        $allChartData[$chartType] = [];
    }
}
$viewparams['allChartData'] = $allChartData;
// --- End Fetch Chart Data ---


// --- Fetch Global Stats for Overview Boxes ---
$globalStats = [];
if ($dms instanceof SeedDMS_Core_DMS) {
    // User Count
    if (method_exists($dms, 'getAllUsers')) {
        $usersArray = $dms->getAllUsers();
        $globalStats['userCount'] = is_array($usersArray) ? count($usersArray) : 'N/A';
    } else {
        $globalStats['userCount'] = 'N/A';
        // error_log("Dashboard Controller: Method getAllUsers() not found in dms object.");
    }

    // Document Count (from 'docsaccumulated' chart data)
    if (isset($allChartData['docsaccumulated']) && !empty($allChartData['docsaccumulated'])) {
        $lastEntry = end($allChartData['docsaccumulated']);
        $globalStats['documentCount'] = $lastEntry['total'] ?? 'N/A';
        reset($allChartData['docsaccumulated']);
    } else {
        $globalStats['documentCount'] = 'N/A';
        // error_log("Dashboard Controller: Could not determine document count from 'docsaccumulated'.");
    }

    // Folder Count (sum from 'foldersperuser' chart data)
    if (isset($allChartData['foldersperuser']) && is_array($allChartData['foldersperuser']) && !empty($allChartData['foldersperuser'])) {
        $totalFolders = 0;
        foreach ($allChartData['foldersperuser'] as $userData) {
            $totalFolders += (int)($userData['total'] ?? 0);
        }
        $globalStats['folderCount'] = $totalFolders; // Will show 0 if sum is 0
    } else {
        $globalStats['folderCount'] = 'N/A';
        // error_log("Dashboard Controller: Could not determine folder count from 'foldersperuser' data or data is empty.");
    }

    // Disk Space Used
    $diskSpaceFound = false;
    if ($settings) { // Ensure $settings is an object or array-like
        $storageValue = null;
        if (is_object($settings) && isset($settings->storageUsed) && is_numeric($settings->storageUsed)) {
           $storageValue = $settings->storageUsed;
        } elseif (is_object($settings) && isset($settings->_storageUsed) && is_numeric($settings->_storageUsed)) { // Check legacy property
           $storageValue = $settings->_storageUsed;
        } elseif (is_array($settings) && isset($settings['storageUsed']) && is_numeric($settings['storageUsed'])) {
           $storageValue = $settings['storageUsed'];
        } elseif (is_array($settings) && isset($settings['_storageUsed']) && is_numeric($settings['_storageUsed'])) {
            $storageValue = $settings['_storageUsed'];
        }


        if (!is_null($storageValue) && class_exists('SeedDMS_Core_File') && method_exists('SeedDMS_Core_File', 'format_filesize')) {
            $globalStats['diskSpaceUsed'] = SeedDMS_Core_File::format_filesize($storageValue);
            $diskSpaceFound = true;
        }
    }

    if (!$diskSpaceFound && isset($allChartData['sizeperuser']) && is_array($allChartData['sizeperuser']) && !empty($allChartData['sizeperuser'])) {
        $totalSize = 0;
        foreach ($allChartData['sizeperuser'] as $userData) {
            $totalSize += (float)($userData['total'] ?? 0);
        }
        if (class_exists('SeedDMS_Core_File') && method_exists('SeedDMS_Core_File', 'format_filesize')) {
            $globalStats['diskSpaceUsed'] = SeedDMS_Core_File::format_filesize($totalSize);
        } else {
            $globalStats['diskSpaceUsed'] = $totalSize . ' Bytes (formatter missing)'; // Fallback if formatter class/method not found
        }
    } elseif(!$diskSpaceFound) {
        $globalStats['diskSpaceUsed'] = 'N/A';
        // error_log("Dashboard Controller: Could not determine disk space used from settings or 'sizeperuser' data.");
    }

} else { // $dms is not a valid object
    $globalStats['userCount'] = 'N/A';
    $globalStats['documentCount'] = 'N/A';
    $globalStats['folderCount'] = 'N/A';
    $globalStats['diskSpaceUsed'] = 'N/A';
    // error_log("Dashboard Controller: \$dms object is not a valid SeedDMS_Core_DMS instance for global stats.");
}
$viewparams['globalStats'] = $globalStats;
// --- End Fetch Global Stats ---
// --- MODIFIED SECTION END ---


// Debugging output (optional)
// echo "<!-- CONTROLLER - Global Stats: " . htmlspecialchars(print_r($globalStats, true)) . " -->";
// echo "<!-- CONTROLLER - All Chart Data: " . htmlspecialchars(print_r($allChartData, true)) . " -->";

$view = UI::factory($theme, 'Dashboard', $viewparams);

if ($view) {
    // ... (access check and view invocation) ...
    if (isset($viewparams['accessobject']) && !$viewparams['accessobject']->check_view_access($view, $_GET)) {
        UI::exitError(getMLText("dashboard", [], "Dashboard"), getMLText("access_denied"));
    }
    $view($_GET);
    exit;
} else {
    error_log("Error: UI::factory could not create SeedDMS_View_Dashboard instance.");
    echo "Error: Dashboard view could not be loaded. Check logs.";
    exit;
}