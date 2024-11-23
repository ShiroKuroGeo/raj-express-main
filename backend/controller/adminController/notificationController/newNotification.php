<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    // $userid = 1;
    // $isread = 0;
    $msgQuery = "SELECT * FROM `notifications` WHERE `user_id` = 1 AND `isRead` = 0";
    $orderStmt = $db->prepare($msgQuery);
    // $orderStmt->bindParam(":user_id", $userid);
    // $orderStmt->bindParam(":isread", $isread);
    $orderStmt->execute();

    if($orderStmt->rowCount() > 0){
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
