<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {

    $msgQuery = "INSERT INTO `messages`(`sender_id`, `receiver_id`, `content`) VALUES (:sender_id, :receiver_id, :content)";
    $orderStmt = $db->prepare($msgQuery);
    $orderStmt->bindParam(":sender_id", $data['sender_id']);
    $orderStmt->bindParam(":receiver_id", $data['receiver_id']);
    $orderStmt->bindParam(":content", $data['content']);
    $result = $orderStmt->execute();

    if($result){
        $set->sendJsonResponse(["success" => "Message Sent!"], 200);
    }else{
        $set->sendJsonResponse(["error" => "Message Failed!"], 400);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
