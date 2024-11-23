<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../connection/dbconfig.php"; 

$userId = $authHeader;

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
}

$response = [];

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "SELECT first_name, last_name, email, profile_img, contact_number FROM users WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $userId);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        sendResponse([
            "success" => true,
            "first_name" => $user['first_name'],
            "last_name" => $user['last_name'],
            "profile_img" => $user['profile_img'],
            "phoneNumber" => $user['contact_number'],
            "email" => $user['email']
        ]);
    } else {
        sendResponse(["error" => "User not found"], 404);
    }
} catch (PDOException $e) {
    sendResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
