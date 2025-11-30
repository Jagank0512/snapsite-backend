<?php
session_start();
$conn = new mysqli("localhost","root","Jagan@143","snapsite");

$username = $_SESSION['username'];

$conn->query("DELETE FROM users WHERE username='$username'");

$photo = "uploads/".$username.".jpg";
if (file_exists($photo)) unlink($photo);

session_destroy();

echo "DELETED";
