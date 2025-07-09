<?php
//    SeedDMS - Download Audit Log for a Document (Excel/CSV)
//    Copyright (C) 2024
//    This script allows a user to download the audit log for a specific document as an Excel-compatible CSV file.
function decrypt($encrypted_combined_base64, $key)
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

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.LogInit.php");
include("../inc/inc.Language.php");
include("../inc/inc.Init.php");
include("../inc/inc.Extension.php");
include("../inc/inc.DBInit.php");
include("../inc/inc.ClassUI.php");
include("../inc/inc.Authentication.php");

// Only allow if user can view the document's audit log
if (!isset($_POST['documentid']) || !is_numeric($_POST['documentid']) || intval($_POST['documentid']) < 1) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid document ID.';
    exit;
}
$documentId = intval($_POST['documentid']);
$document = $dms->getDocument($documentId);
if (!is_object($document)) {
    header('HTTP/1.1 404 Not Found');
    echo 'Document not found.';
    exit;
}
$accessop = new SeedDMS_AccessOperation($dms, $user, $settings);
if ($document->getAccessMode($user) < M_READ || !$accessop->check_view_access($document, 'auditlog')) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied.';
    exit;
}

// Set headers for Excel-compatible download (CSV with .xls extension)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="audit_log_doc' . $documentId . '_' . date('Ymd_His') . '.csv"');


$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Excel/CSV header
fputcsv($output, ['Date/Time', 'User', 'Old Value', 'New Value']);

// Use auditlogs from POST if present, otherwise query the database
if (isset($_POST['auditlogs']) && !empty($_POST['auditlogs'])) {
    $encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';
    $auditLogs = json_decode($_POST['auditlogs'], true);
    if (is_array($auditLogs) && count($auditLogs) > 0) {
        foreach ($auditLogs as $row) {
            $decrypted_user = decrypt($row['user'] ?? '', $encryption_key);
            $decrypted_old = decrypt($row['old_value'] ?? '', $encryption_key);
            $decrypted_new = decrypt($row['new_value'] ?? '', $encryption_key);

            // Only needed if created_at is also encrypted
            // $decrypted_date = decrypt($row['created_at'] ?? '', $encryption_key);

            $raw_date = $row['created_at'] ?? '';
            $timestamp = strtotime($raw_date);
            $formatted_date = $timestamp ? date('Y-m-d H:i:s', $timestamp) : '';
            $excel_friendly_date = "'" . $formatted_date;

            fputcsv($output, [
                $excel_friendly_date,
                $decrypted_user,
                $decrypted_old,
                $decrypted_new
            ]);
        }

    } else {
        fputcsv($output, ['No audit log entries found for this document.', '', '', '']);
    }
} else {
    $db = $dms->getDB();
    $query = "SELECT created_at, user, old_value, new_value FROM audit_logs WHERE document_id = $documentId ORDER BY created_at DESC";
    $result = $db->getResult($query);
    if ($result && is_array($result) && count($result) > 0) {
        foreach ($result as $row) {
            fputcsv($output, [
                $row['created_at'] ?? '',
                $row['user'] ?? '',
                $row['old_value'] ?? '',
                $row['new_value'] ?? ''
            ]);
        }
    } else {
        // Output a row indicating no data found
        fputcsv($output, ['No audit log entries found for this document.', '', '', '']);
    }
}
fclose($output);
exit;
