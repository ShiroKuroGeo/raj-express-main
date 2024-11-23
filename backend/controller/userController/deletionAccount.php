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

    // Request delete should be 14 days before the deletion process is complete
    // No data will be remove or delete, instead the user status is archive
    
    $query = "UPDATE `users` SET `date_deletion` = :delDate WHERE `user_id` = :user_id";
    $stmt = $db->prepare($query);
    $setDate = strtotime("+14 days", time());
    $date_deletion = date("Y-m-d H:i:s", $setDate);
    $stmt->bindParam(":delDate", $date_deletion);
    $stmt->bindParam(":user_id", $authHeader);  
    $result = $stmt->execute();

    if($result){
        $set->sendJsonResponse(["success" => "Your account will be deleted in " .$date_deletion], 200);
    }else{
        $set->sendJsonResponse(["error" => "Information not Updated!"], 400);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
