<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    
    $query = "UPDATE `users` SET `status` = :stat WHERE `user_id` = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':stat', $data['status']);
    $stmt->bindParam(':user_id', $data['user_id']);
    $result = $stmt->execute();

    if ($result) {
        $set->sendJsonResponse(["Success" => "Users is now " . $data['status']], 200);
    } else {
        $set->sendJsonResponse(["error" => "Users not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
