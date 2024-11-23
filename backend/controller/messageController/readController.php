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

    $msgQuery = "SELECT * FROM `messages` WHERE `sender_id` = :user_id OR `receiver_id` = :user_id;";
    $messageStmt = $db->prepare($msgQuery);
    $messageStmt->bindParam(":user_id",$authHeader);
    $result = $messageStmt->execute();

    if ($messageStmt->rowCount() > 0) {
        $message = $messageStmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "messages" => $message,
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Message not found"], 404);
    }

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
