<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $msgQuery = "SELECT * FROM `notifications` WHERE `user_id` = 1 AND `isRead` = 0";
    $orderStmt = $db->prepare($msgQuery);
    $orderStmt->execute();

    if($orderStmt->rowCount() > 0){
        $notification = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "notifs" => $notification
        ]);
    }else{
        $set->sendJsonResponse(["error" => "Notication Failed!"], 400);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
