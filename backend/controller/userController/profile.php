<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "SELECT * FROM `users` WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "id" => $user['user_id'],
            "fn" => $user['first_name'],
            "ln" => $user['last_name'],
            "number" => $user['contact_number'],
            "mail" => $user['email'],
            "profilePicture" => $user['profile_img'],
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Address not found"], 404);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
