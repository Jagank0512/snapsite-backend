<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* === DB SETUP === */
$db_host = "localhost";
$db_user = "root";
$db_pass = "Jagan@143";
$db_name = "snapsite";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("DB_ERROR");
}

/* === PHPMAILER === */
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";
require "PHPMailer/src/Exception.php";

function sendOTP($email, $otp){
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        $mail->Host="smtp.gmail.com";
        $mail->SMTPAuth=true;
        $mail->Username="jagank0512@gmail.com";
        $mail->Password="cjbjeunhfjcwazdv";  // your app password
        $mail->SMTPSecure="tls";
        $mail->Port=587;

 $mail->setFrom("jagank0512@gmail.com", "Snapsite");
        $mail->addAddress($email);

        // === Embed Logo ===
        // Change path to your logo
        $mail->AddEmbeddedImage("images/logo.png", "snaplogo", "logo.png");

        $mail->isHTML(true);
        $mail->Subject = "Snapsite - Your OTP Code";

        // ==== Beautiful Email HTML Template ====
        $mail->Body = "
        <div style='font-family:Arial; padding:20px;'>
            <div style='text-align:center;'>
                <img src='cid:snaplogo' width='80' style='margin-bottom:15px;' />
                <h2 style='color:#4A00FF;'>Snapsite Verification</h2>
            </div>

            <p>Hello,</p>
            <p style='font-size:16px;'>
                Your One-Time Password (OTP) for verification is:
            </p>

            <div style='
                background:#4A00FF;
                color:white;
                padding:12px 0;
                font-size:28px;
                font-weight:bold;
                text-align:center;
                border-radius:10px;
                letter-spacing:4px;
                width:200px;
                margin:auto;
            '>
                $otp
            </div>

            <p style='margin-top:20px; font-size:14px; color:#555;'>
                This OTP is valid for <b>5 minutes.</b><br>
                Do not share it with anyone.
            </p>

            <br>
            <p style='font-size:13px; color:#777; text-align:center;'>
                © " . date('Y') . " Snapsite. All rights reserved.
            </p>
        </div>
        ";

        return $mail->send();

    } catch (Exception $e) {
        return false;
    }
}


/* === Get action from JS === */
$action = $_POST["action"] ?? "";



/* =====================================================
   REGISTER: SEND OTP
===================================================== */
if($action === "sendRegOTP"){
    $u = $_POST["username"];
    $e = $_POST["email"];

    // Check username
    $q = $conn->prepare("SELECT id FROM users WHERE username=?");
    $q->bind_param("s", $u);
    $q->execute();
    $q->store_result();
    if($q->num_rows > 0){
        echo "USERNAME_EXISTS";
        exit;
    }

    // Check email
    $q2 = $conn->prepare("SELECT id FROM users WHERE email=?");
    $q2->bind_param("s", $e);
    $q2->execute();
    $q2->store_result();
    if($q2->num_rows > 0){
        echo "EMAIL_EXISTS";
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION["reg_otp"] = $otp;
    $_SESSION["reg_email"] = $e;
    $_SESSION["reg_user"] = $u;

    sendOTP($e, $otp);
    echo "OK";
    exit;
}



/* =====================================================
   REGISTER: SUBMIT FINAL
===================================================== */
if($action === "register"){
    $u = $_POST["username"];
    $e = $_POST["email"];
    $p = $_POST["password"];
    $otp = $_POST["otp"];

    if($otp != ($_SESSION["reg_otp"] ?? "")){
        echo "INVALID_OTP";
        exit;
    }

    $hash = password_hash($p, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users(username,email,password) VALUES(?,?,?)");
    $stmt->bind_param("sss", $u, $e, $hash);

    if($stmt->execute()){
        echo "OK";
    } else {
        echo "ERROR_DB";
    }
    exit;
}



/* =====================================================
   LOGIN
===================================================== */
if($action === "login"){
    $u = $_POST["username"];
    $p = $_POST["password"];

    // Fetch id, username, email, password
    $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $u, $u);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){
        echo "USERNAME_WRONG";
        exit;
    }

    $row = $result->fetch_assoc();

    // Validate password
    if(password_verify($p, $row["password"])){

        // STORE SESSION
        $_SESSION["username"] = $row["username"];
        $_SESSION["email"]    = $row["email"];   // ❤️ FIXED
        $_SESSION["user_id"]  = $row["id"];

        echo "OK";
    } else {
        echo "PASSWORD_WRONG";
    }
    exit;
}



/* =====================================================
   FORGOT PASSWORD — SEND OTP
===================================================== */
if($action === "sendForgotOTP"){
    $email = $_POST["email"];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows == 0){
        echo "EMAIL_NOT_FOUND";
        exit;
    }

    $otp = rand(100000,999999);
    $_SESSION["forgot_otp"] = $otp;
    $_SESSION["forgot_email"] = $email;

    sendOTP($email,$otp);
    echo "OK";
    exit;
}



/* =====================================================
   FORGOT PASSWORD — VERIFY OTP
===================================================== */
if($action === "verifyForgotOTP"){
    $otp = $_POST["otp"];

    if($otp == ($_SESSION["forgot_otp"] ?? "")){
        echo "OK";
    } else {
        echo "INVALID_OTP";
    }
    exit;
}



/* =====================================================
   RESET PASSWORD
===================================================== */
if($action === "resetPassword"){
    $new = $_POST["password"];
    $email = $_SESSION["forgot_email"] ?? "";

    $hash = password_hash($new, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $hash, $email);

    if($stmt->execute()){
        echo "OK";
    } else {
        echo "ERROR_DB";
    }
    exit;
}


echo "NO_ACTION";
?>
