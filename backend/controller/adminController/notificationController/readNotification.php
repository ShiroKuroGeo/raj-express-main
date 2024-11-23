<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();
try {
    $msgQuery = "UPDATE `notifications` SET `isRead`= 1 WHERE `user_id` = 1";
    $orderStmt = $db->prepare($msgQuery);
    $result = $orderStmt->execute();

    if($result){
        $set->sendJsonResponse([
            "success" => true,
            "newOrder" => 1
        ]);
    }else{
        $set->sendJsonResponse(["error" => "Notication Failed!"], 400);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
