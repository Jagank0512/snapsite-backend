<?php
session_start();

$username = $_SESSION['username'] ?? '';

if (!$username || !isset($_FILES['profile_image'])) {
    echo "ERROR_NO_USER";
    exit;
}

if (!is_dir("uploads")) mkdir("uploads");

$target = "uploads/" . $username . ".jpg";
move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target);

echo "OK";
