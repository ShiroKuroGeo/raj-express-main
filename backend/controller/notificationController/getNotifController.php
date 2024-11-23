<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

$database = new Database();
$db = $database->getDb();

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

try {

    $msgQuery = "SELECT * FROM `notifications` WHERE `user_id` = :user_id ORDER BY `created_at` DESC";
    $notifStmt = $db->prepare($msgQuery);
    $notifStmt->bindParam(":user_id",$authHeader);
    $result = $notifStmt->execute();

    if ($notifStmt->rowCount() > 0) {
        $notifs = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "notifs" => $notifs,
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Notifications not found"], 404);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
