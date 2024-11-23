<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {

    $msgQuery = "INSERT INTO `expenses`(`category_name`, `description`, `amount`, `date`) VALUES ( :categoryName, :descrip, :amount, :dates)";
    $orderStmt = $db->prepare($msgQuery);
    $orderStmt->bindParam(":categoryName", $data['categoryName']);
    $orderStmt->bindParam(":descrip", $data['descrip']);
    $orderStmt->bindParam(":amount", $data['amount']);
    $orderStmt->bindParam(":dates", $data['dates']);
    $result = $orderStmt->execute();

    if($result){
        $set->sendJsonResponse(["success" => "Message Sent!"], 200);
    }else{
        $set->sendJsonResponse(["error" => "Message Failed!"], 400);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
