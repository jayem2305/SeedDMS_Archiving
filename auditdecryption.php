<?php
// Decrypt audit log fields utility
function decrypt_auditlog_field($encrypted_base64, $key) {
    $data = base64_decode($encrypted_base64);
    if ($data === false || strlen($data) < 16) {
        return '[INVALID DATA]';
    }
    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);
    $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted === false ? '[DECRYPTION FAILED]' : $decrypted;
}

// Decrypt and show the entire audit log
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

$query = "SELECT document_id, created_at, user, old_value, new_value FROM audit_logs ORDER BY created_at DESC";
$result = $db->getResultArray($query);

if ($result === false) {
    echo '<b>Query failed or returned false.</b><br>';
    echo '<b>Query:</b> ' . htmlspecialchars($query) . '<br>';
    exit;
}

if (!is_array($result) || empty($result)) {
    echo '<b>No audit log entries found.</b><br>';
    echo '<b>Raw result:</b><pre>' . htmlspecialchars(print_r($result, true)) . '</pre>';
    exit;
}

$count = count($result);
echo "<b>Loaded $count audit log entries.</b><br>";
echo '<table border="1"><tr><th>Document ID</th><th>Date/Time</th><th>User</th><th>Old Value</th><th>New Value</th></tr>';
foreach ($result as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['document_id']) . '</td>';
    echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
    echo '<td>' . htmlspecialchars(decrypt_auditlog_field($row['user'], $encryption_key)) . '</td>';
    echo '<td>' . nl2br(htmlspecialchars(decrypt_auditlog_field($row['old_value'], $encryption_key))) . '</td>';
    echo '<td>' . nl2br(htmlspecialchars(decrypt_auditlog_field($row['new_value'], $encryption_key))) . '</td>';
    echo '</tr>';
}
echo '</table>';
?>