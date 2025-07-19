<?php
require_once 'db.php'; // Your SeedDMS DB connection
require_once 'inc/inc.Init.php'; // Load SeedDMS core

$temp_dir = 'scanner_temp/';
$folder_id = intval($_POST['folder_id']);
$files = $_POST['files'] ?? [];

if (!$folder_id || empty($files)) {
    die("Invalid folder or no files selected.");
}

$folder = $dms->getFolder($folder_id);
if (!$folder) {
    die("Folder not found in SeedDMS.");
}

foreach ($files as $filename) {
    $source = $temp_dir . basename($filename);
    if (!file_exists($source))
        continue;

    $docName = pathinfo($filename, PATHINFO_FILENAME);
    $mimetype = mime_content_type($source);
    $tmpfile = tempnam(sys_get_temp_dir(), 'scan');
    copy($source, $tmpfile);

    $res = $folder->addDocument($docName, '', $tmpfile, '', $mimetype);
    if ($res) {
        unlink($source); // Delete from temp after saving
    } else {
        echo "Failed to save: $filename<br>";
    }
}

echo "Documents saved successfully!";
?>