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
    
    $query = "SELECT * FROM `users` WHERE `user_id` = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([ "success" => true, "users" => $users ]);
    } else {
        $set->sendJsonResponse([ "error" => "User not found" ], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
