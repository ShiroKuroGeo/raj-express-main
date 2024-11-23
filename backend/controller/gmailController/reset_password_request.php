<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php';
include '../controller.php';
include "../../connection/dbconfig.php"; 
$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

$email = $data['email'];

function checkUserInDatabase($email) {
    $database = new Database();
    $db = $database->getDb();

    $query = "SELECT * FROM `users` WHERE `email` = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

function storeResetTokenInDatabase($email, $token) {
    $database = new Database();
    $db = $database->getDb();

    $query = "UPDATE `users` SET `reset_token` = :token WHERE `email` = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":token", $token);
    $stmt->execute();

    return true;
}

function sendResetEmail($email, $token) {
    $resetLink = "http://localhost:9000/#/change-password/" . $token;

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rajexpress514@gmail.com';
        $mail->Password   = 'ajgdwstrdqmvfgqw';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('rajexpress514@gmail.com', 'Raj Express');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Click the link to reset your password: <a href='" . $resetLink . "'>Reset Password</a>";

        if ($mail->send()) {
            sendJsonResponse(["success" => "Password reset email sent"], 200);
        } else {
            sendJsonResponse(["error" => "Failed to send reset email"], 500);
        }
    } catch (Exception $e) {
        echo "Mailer Error: " . $e->getMessage();
    }
}

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header("Content-Type: application/json");

    $json = json_encode($data);

    if ($json === false) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to encode JSON"]);
    } else {
        echo $json;
    }
    exit;
}

if (checkUserInDatabase($email)) {
    $token = bin2hex(random_bytes(50)); 

    storeResetTokenInDatabase($email, $token);

    sendResetEmail($email, $token);
} else {
    sendJsonResponse(['success' => false, 'message' => 'Email not found'], 404);
}
?>
