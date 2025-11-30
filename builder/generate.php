<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["username"]) || !isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

$username = $_SESSION["username"];
$user_id  = $_SESSION["user_id"];

require "config.php"; // includes $conn and $OPENAI_API_KEY

$prompt  = trim($_POST["prompt"]);
$project = trim($_POST["project_name"]);

// Clean project folder name
$cleanProject = strtolower(str_replace(" ", "-", $project));

$userDir    = "projects/$username/";
$projectDir = "projects/$username/$cleanProject/";

if (!file_exists($userDir)) mkdir($userDir, 0777, true);
if (!file_exists($projectDir)) mkdir($projectDir, 0777, true);

// =============================
// AI Request
// =============================
$openai_url = "https://api.openai.com/v1/chat/completions";

$system_prompt = "
You generate websites. Output strictly in this format:

===HTML===
(HTML but always include: <link rel='stylesheet' href='style.css'> and <script src='script.js'>)

===CSS===
(CSS)

===JS===
(JS)
";

$payload = json_encode([
    "model" => "gpt-4.1-mini",
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => $prompt]
    ]
]);

$headers = [
    "Content-Type: application/json",
    "Authorization: " . "Bearer $OPENAI_API_KEY"
];

$ch = curl_init($openai_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data["choices"][0]["message"]["content"])) {
    echo json_encode(["status" => "error", "message" => "AI returned no content"]);
    exit();
}

$output = $data["choices"][0]["message"]["content"];

function block($txt, $start, $end="") {
    $s = strpos($txt, $start);
    if ($s === false) return "";
    $s += strlen($start);
    if ($end === "") return trim(substr($txt, $s));
    $e = strpos($txt, $end, $s);
    if ($e === false) return "";
    return trim(substr($txt, $s, $e - $s));
}

$html = block($output, "===HTML===", "===CSS===");
$css  = block($output, "===CSS===", "===JS===");
$js   = block($output, "===JS===");

$html = str_replace("styles.css", "style.css", $html);
$html = str_replace("scripts.js", "script.js", $html);

if ($js === "") $js = "// js auto-created";

// Save files
file_put_contents($projectDir."index.html", $html);
file_put_contents($projectDir."style.css", $css);
file_put_contents($projectDir."script.js", $js);

// ========================================================
// SAVE PROJECT TO DATABASE  (IMPORTANT FIX)
// ========================================================

// Check if project already exists
$check = $conn->prepare("SELECT id FROM projects WHERE user_id=? AND project_name=?");
$check->bind_param("is", $user_id, $project);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    // Insert new project
    $stmt = $conn->prepare("
        INSERT INTO projects (user_id, project_name) 
        VALUES (?, ?)
    ");
    $stmt->bind_param("is", $user_id, $project);
    $stmt->execute();
}

// Return editor page
echo json_encode([
    "status" => "success",
    "editor_url" => "/snapsite/builder/editor.php?project=$cleanProject"
]);

?>
