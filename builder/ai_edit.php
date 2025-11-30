<?php
session_start();
header("Content-Type: application/json");

// 1) PUT YOUR OPENAI API KEY HERE

$OPENAI_API_KEY = getenv("OPENAI_API_KEY");

// 2) BASIC DEBUG (optional but very useful)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 3) READ INPUT FROM JS
$userMessage = trim($_POST['message'] ?? "");
$project     = $_POST['project'] ?? "";
$username    = $_SESSION['username'] ?? "";

if (!$userMessage) {
    echo json_encode([
        "reply" => "Please type something.",
        "html"  => null
    ]);
    exit;
}

if (!$project || !$username) {
    echo json_encode([
        "reply" => "Missing project or user session.",
        "html"  => null
    ]);
    exit;
}

// 4) PROJECT FILE PATHS
$clean = strtolower(str_replace(" ", "-", $project));
$folder = "projects/$username/$clean";

$indexFile  = "$folder/index.html";
$cssFile    = "$folder/style.css";
$jsFile     = "$folder/script.js";

$html = file_exists($indexFile) ? file_get_contents($indexFile) : "";
$css  = file_exists($cssFile)   ? file_get_contents($cssFile)   : "";
$js   = file_exists($jsFile)    ? file_get_contents($jsFile)    : "";

// 5) SYSTEM PROMPT – TELL AI WHAT TO DO
$system = "
You are an AI website designer for snapsite.

Task:
- You get the current website HTML, CSS, and JS.
- You get a user instruction.
- You must generate a FULL NEW website that matches the user's request
  (lovable, modern, dark theme, restaurant, etc).

Return ONLY valid JSON in this format:

{
  \"reply\": \"short message to show to user\",
  \"html\": \"FULL HTML CODE\",
  \"css\": \"FULL CSS CODE\",
  \"js\": \"FULL JS CODE\"
}

Rules:
- Always return complete <html>...</html>.
- Keep code as short and simple as possible.
- Do not include markdown or ```json.
";

// 6) BUILD OPENAI REQUEST
$payload = [
    "model" => "gpt-4o-mini",
    "response_format" => ["type" => "json_object"],
    "messages" => [
        ["role" => "system", "content" => $system],
        ["role" => "user", "content" => "Current HTML:\n" . $html],
        ["role" => "user", "content" => "Current CSS:\n" . $css],
        ["role" => "user", "content" => "Current JS:\n" . $js],
        ["role" => "user", "content" => "User request:\n" . $userMessage]
    ],
    "max_tokens" => 6000 // avoid crazy long outputs
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: " . "Bearer " . $apiKey
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
curl_close($ch);

// 7) LOG RAW RESPONSE (so you can inspect errors)
file_put_contents(_DIR_ . "/ai_log.txt", $response);

// 8) PARSE OPENAI RESPONSE
$data = json_decode($response, true);

// CASE: OpenAI returned error (no choices)
if (!isset($data["choices"][0]["message"]["content"])) {
    // Try to capture error message
    $errorMsg = $data["error"]["message"] ?? "Unknown AI error.";
    echo json_encode([
        "reply" => "OpenAI error: " . $errorMsg,
        "html"  => $html    // fallback: return old HTML so editor still works
    ]);
    exit;
}

// CONTENT IS JSON (because we used json_object)
$json = json_decode($data["choices"][0]["message"]["content"], true);

// CASE: JSON decode failed
if (!$json || !isset($json["html"])) {
    echo json_encode([
        "reply" => "AI returned invalid JSON. Using previous version.",
        "html"  => $html // fallback to old HTML
    ]);
    exit;
}

// 9) SAVE NEW FILES
file_put_contents($indexFile, $json["html"]);
file_put_contents($cssFile,  $json["css"]);
file_put_contents($jsFile,   $json["js"]);

// 10) RETURN TO JS – ALWAYS PROVIDE html
echo json_encode([
    "reply" => $json["reply"],
    "html"  => $json["html"]
]);
exit;