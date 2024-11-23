<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '1';

include "../../connection/dbconfig.php"; 

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
            "user_id" => $user['user_id'],
            "first_name" => $user['first_name'],
            "last_name" => $user['last_name'],
            "address" => $user['address'],
            "contact_number" => $user['contact_number'],
            "email" => $user['email'],
            "password_hash" => $user['password_hash'],
            "user_type" => $user['user_type'],
            "profile_img" => $user['profile_img'],
            "status" => $user['status'],
            "date_deletion" => $user['date_deletion'],
            "created_at" => $user['created_at'],
            "updated_at" => $user['updated_at'],
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Address not found"], 404);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
