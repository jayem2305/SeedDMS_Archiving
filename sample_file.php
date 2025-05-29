<?php
// Encryption key and method
$encryption_key = 'b8c75fa53c0c7a18a84adb6ca815bd94';  // The key must be 32 bytes for AES-256
$encryption_method = 'AES-256-CBC';  // Specify the encryption method

// Function to decrypt the data
// Decrypt the name (including handling for hex to binary conversion for ciphertext)
function decrypt_name($encrypted_combined_base64, $key)
{
    // Decode the base64 string
    $data = base64_decode($encrypted_combined_base64);
    if ($data === false) {
        echo "Decryption failed: Base64 decoding failed.<br>";
        return '[INVALID DATA]';
    }

    // Ensure the data is large enough to contain both IV and Ciphertext (IV must be 16 bytes)
    if (strlen($data) < 16) {
        echo "Decryption failed: Data is too short for valid IV and ciphertext.<br>";
        return '[INVALID DATA]';
    }

    // Extract the IV (first 16 bytes) and Ciphertext (remaining bytes)
    $iv = substr($data, 0, 16);  // First 16 bytes are the IV
    $ciphertext = substr($data, 16);  // The rest is the ciphertext

    // Perform decryption with AES-256-CBC and the specified IV
    $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    // Check for decryption failure
    if ($decrypted === false) {
        echo "Decryption failed: " . openssl_error_string() . "<br>";
        return '[DECRYPTION FAILED]';
    }

    return $decrypted;
}

// Sample database query to test decryption
$host = "localhost";
$username = "root";
$password = "";
$database = "seeddms_dbdoc";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM tbldocuments";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Raw Encrypted Data: " . $row["name"] . "<br>";

        // Correct the function call to pass the correct parameters
        $decrypted_name = decrypt_name($row["name"], $encryption_key);

        echo "ID: " . $row["id"] . "<br>";
        echo "Date: " . $row["date"] . "<br>";
        echo "Decrypted Name: " . $decrypted_name . "<br><hr>";
    }
} else {
    echo "No records found.";
}

$conn->close();
?>