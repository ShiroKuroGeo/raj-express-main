<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

$database = new Database();
$db = $database->getDb();

try {

    $notifQuery = "INSERT INTO `notifications`(`user_id`, `customer_ref`, `content`) VALUES (:user_id, :customer_ref, :content)";
    $orderStmt = $db->prepare($notifQuery);
    $orderStmt->bindParam(":user_id", $data['user_id']);
    $orderStmt->bindParam(":customer_ref", $data['customer_ref']);
    $orderStmt->bindParam(":content", $data['content']);
    $result = $orderStmt->execute();

    if($result){
        $set->sendJsonResponse(["success" => "Notification Sent!"], 200);
    }else{
        $set->sendJsonResponse(["error" => "Notification Failed!"], 400);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
