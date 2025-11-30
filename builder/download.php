<?php
session_start();

// Validate
$project  = $_GET['project'] ?? "";
$username = $_SESSION['username'] ?? "";

if (!$project || !$username) {
    die("MISSING_PROJECT");
}

$clean = strtolower(trim($project));
$clean = str_replace(" ", "-", $clean);

$projectDir = "projects/$username/$clean";

if (!is_dir($projectDir)) {
    die("PROJECT_FOLDER_NOT_FOUND");
}

// Create TAR file name
$tarFile = "download_" . $clean . ".tar";
$gzFile  = $tarFile . ".gz";

// Delete old files if exist
if (file_exists($tarFile)) unlink($tarFile);
if (file_exists($gzFile)) unlink($gzFile);

// CREATE TAR using PharData
try {
    $phar = new PharData($tarFile);
    $phar->buildFromDirectory($projectDir);

    // Now compress TAR → TAR.GZ
    $phar->compress(Phar::GZ);

    // Remove non-compressed TAR (keep only .tar.gz)
    unlink($tarFile);

} catch (Exception $e) {
    die("ERROR_CREATING_ARCHIVE: " . $e->getMessage());
}

// FORCE DOWNLOAD
header("Content-Type: application/gzip");
header("Content-Disposition: attachment; filename=\"$clean.tar.gz\"");
header("Content-Length: " . filesize($gzFile));

// Output the file
readfile($gzFile);

// Delete after download
unlink($gzFile);

exit;
