<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {

    $msgQuery = "INSERT INTO `notifications`(`user_id`, `customer_ref`, `content`) VALUES (:user_id, :customer_ref, :content)";
    $orderStmt = $db->prepare($msgQuery);
    $orderStmt->bindParam(":user_id", $data['user_id']);
    $orderStmt->bindParam(":customer_ref", $data['customer_ref']);
    $orderStmt->bindParam(":content", $data['content']);
    $result = $orderStmt->execute();

    if($result){
        $set->sendJsonResponse(["success" => "Notication Sent!"], 200);
    }else{
        $set->sendJsonResponse(["error" => "Notication Failed!"], 400);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
