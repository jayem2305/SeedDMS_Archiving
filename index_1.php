<?php
require_once 'inc/inc.Init.php'; // Adjust path based on where your file is


$temp_dir = 'scanner_temp/';
$files = array_diff(scandir($temp_dir), array('.', '..'));

// Fetch SeedDMS folders from the DB
$folders = [];
$result = $db->query("SELECT id, name FROM folders ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $folders[] = $row;
}
?>

<form action="save_scanned.php" method="POST">
    <label for="folder">Select Folder:</label>
    <select name="folder_id" required>
        <option value="">-- Choose Folder --</option>
        <?php foreach ($folders as $folder): ?>
            <option value="<?= $folder['id'] ?>"><?= htmlspecialchars($folder['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <ul>
        <?php foreach ($files as $file): ?>
            <li>
                <input type="checkbox" name="files[]" value="<?= htmlspecialchars($file) ?>" checked>
                <?= htmlspecialchars($file) ?>
                <img src="<?= $temp_dir . $file ?>" width="100">
            </li>
        <?php endforeach; ?>
    </ul>
    <button type="submit">Save to Folder</button>
</form>