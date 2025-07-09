<?php
// filenamecheck.php
// Show all data from tbldocumentcontent for debugging

$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
include_once("inc/inc.Settings.php");
include_once("inc/inc.Utils.php");
include_once("inc/inc.DBInit.php");

if (!isset($dms) || !is_object($dms)) {
    die('DMS object not initialized. Check inc.DBInit.php and database connection.');
}

$db = $dms->getDB();
if (!$db) {
    die('Database connection failed.');
}

$query = "SELECT * FROM tbldocumentcontent ORDER BY documentid, version";
$result = $db->getResultArray($query);

// Debug: print raw result
echo '<pre>' . htmlspecialchars(print_r($result, true)) . '</pre>';

if ($result === false) {
    echo '<b>Query failed or returned false.</b><br>';
    echo '<b>Query:</b> ' . htmlspecialchars($query) . '<br>';
    exit;
}

if (!is_array($result) || empty($result)) {
    echo '<b>No document content entries found.</b><br>';
    echo '<b>Raw result:</b><pre>' . htmlspecialchars(print_r($result, true)) . '</pre>';
    exit;
}

echo '<table border="1">';
// Table header
$header = array_keys($result[0]);
echo '<tr>';
foreach ($header as $col) {
    echo '<th>' . htmlspecialchars($col) . '</th>';
}
echo '</tr>';
// Table rows
foreach ($result as $row) {
    echo '<tr>';
    foreach ($row as $val) {
        echo '<td>' . htmlspecialchars($val) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';
