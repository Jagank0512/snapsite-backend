<?php
// Replace with your OpenAI key
$OPENAI_API_KEY = getenv("OPENAI_API_KEY");


$db_host = "localhost";
$db_user = "root";
$db_pass = "Jagan@143";
$db_name = "snapsite";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("DB FAILED: " . $conn->connect_error);
}
?>
