<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);


// DB NOT REQUIRED FOR OPTION A
$username = $_SESSION['username'] ?? "";

// GET PROJECT NAME FROM URL
$project_name = $_GET['project'] ?? "";
if (!$project_name) {
    die("<h1 style='color:red;'>❌ No project name provided</h1>");
}

// CLEAN FOLDER NAME
$cleanName = strtolower(trim($project_name));
$cleanName = str_replace(" ", "-", $cleanName);

// PROJECT FOLDER
$projectFolder = "projects/$username/$cleanName";

// INDEX.HTML PATH
$indexFile = "$projectFolder/index.html";

if (!file_exists($indexFile)) {
    die("<h1 style='color:red;'>❌ index.html NOT FOUND</h1><p>$indexFile</p>");
}

// LOAD HTML
$website_html = file_get_contents($indexFile);

?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="projects/logo.png">
    <title>Snapsite Editor - <?= $project_name ?></title>
    <link rel="stylesheet" href="editor.css">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">

    <div class="left-title">
        <img src="../images/logo.png" class="top-logo">
        <span class="editor-title"> Snapsite  /     <?= $project_name ?></span>
    </div>

    <div class="top-controls">
        <button id="desktopView" class="view-btn">🖥</button>
        <button id="tabletView" class="view-btn">📱</button>
        <button id="mobileView" class="view-btn">📞</button>

        <button id="downloadBtn" class="action-btn down">Download</button>
        <button id="publishBtn" class="action-btn pub">Publish</button>

        <a href="../home.php"><button class="exit-btn">Exit</button></a>
    </div>
</div>

<!-- BODY -->
<div class="editor-body">

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <h2 class="panel-title">snapsite AI Assistant</h2>
        <div class="chat-box" id="chatBox"></div>

        <div class="ai-input-row">
            <input type="text" id="aiUserInput" placeholder="Ask snapsite AI...">
            <button id="aiSendBtn" class="send-btn">➤</button>
        </div>
    </div>

    <!-- RIGHT PREVIEW -->
    <div class="right-panel">
        <div class="preview-frame">
            <iframe id="previewFrame"></iframe>
        </div>
    </div>

</div>

<script>
// LOAD PROJECT HTML
let html = <?= json_encode($website_html) ?>;

// ABSOLUTE BASE URL FOR CSS/JS
let baseURL = "http://localhost/snapsite/builder/<?= $projectFolder ?>";

// Insert <base> tag to fix CSS & JS paths
html = html.replace("<head>", `<head><base href="${baseURL}/">`);

// Load into iframe
window.onload = () => {
    const frame = document.getElementById("previewFrame");
    frame.contentDocument.open();
    frame.contentDocument.write(html);
    frame.contentDocument.close();
};

// Make project name available for save/publish functions

const projectName = <?= json_encode($project_name) ?>;
const username    = <?= json_encode($username) ?>; // from $_SESSION['username']
</script>


<script src="editor.js"></script>

</body>
</html>
