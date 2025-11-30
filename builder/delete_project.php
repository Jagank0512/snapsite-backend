<?php
session_start();

$username = $_SESSION['username'];
$project = $_POST['project'] ?? "";

if (!$username || !$project) {
    echo "INVALID";
    exit;
}

$clean = strtolower(trim($project));
$path = "projects/$username/$clean";

// Delete folder recursively
function deleteFolder($dir) {
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $full = "$dir/$file";
        is_dir($full) ? deleteFolder($full) : unlink($full);
    }
    return rmdir($dir);
}

if (deleteFolder($path)) {
    // delete from DB
    $conn = new mysqli("localhost","root","Jagan@143","snapsite");
    $conn->query("DELETE FROM projects WHERE project_name='$project' AND user_id=".$_SESSION['user_id']);
    echo "DELETED";
} else {
    echo "ERROR";
}
