<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

try {
    
    $query = "SELECT * FROM `users` WHERE `user_id` = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $users = $stmt->fetch(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "user_id" => $users['user_id'],
            "first_name" => $users['first_name'],
            "last_name" => $users['last_name'],
            "address" => $users['address'],
            "contact_number" => $users['contact_number'],
            "email" => $users['email'],
            "profile_img" => $users['profile_img'],
            "status" => $users['status'],
            "created_at" => $users['created_at'],
            "date_deletion" => $users['date_deletion'],
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Users not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
