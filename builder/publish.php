<?php
session_start();
header("Content-Type: application/json");

$project  = $_POST['project'] ?? "";
$username = $_SESSION['username'] ?? "";
$html     = $_POST['html'] ?? "";

if (!$project || !$username) {
    echo json_encode([
        "status"  => "error",
        "message" => "MISSING_DATA"
    ]);
    exit;
}

// Clean project name
$cleanName = strtolower(trim($project));
$cleanName = str_replace(" ", "-", $cleanName);

// --------------------------------------------------------
// SOURCE: /snapsite/builder/projects/USERNAME/PROJECT
// --------------------------------------------------------
$sourceDir = __DIR__ . "/projects/$username/$cleanName";

if (!is_dir($sourceDir)) {
    echo json_encode([
        "status"  => "error",
        "message" => "SOURCE_PROJECT_NOT_FOUND: $sourceDir"
    ]);
    exit;
}

// Replace index.html with latest HTML from editor
if (!empty($html)) {
    file_put_contents($sourceDir . "/index.html", $html);
}

// --------------------------------------------------------
// TARGET: /snapsite/published/PROJECT
// --------------------------------------------------------
$rootDir   = realpath(__DIR__ . "/..");  // Goes from /builder → /snapsite
$targetDir = $rootDir . "/published/$cleanName";

function copyFolder($src, $dst)
{
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }

    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $srcPath = "$src/$item";
        $dstPath = "$dst/$item";

        if (is_dir($srcPath)) {
            copyFolder($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }

    return true;
}

// Copy to published folder
copyFolder($sourceDir, $targetDir);

// --------------------------------------------------------
// CORRECT LIVE URL
// --------------------------------------------------------
$liveUrl = "http://localhost/snapsite/builder/projects/$username/$project/index.html";

echo json_encode([
    "status" => "ok",
    "url"    => $liveUrl
]);
exit;
