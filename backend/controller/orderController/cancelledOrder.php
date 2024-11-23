<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "UPDATE `orders` SET `status`='cancelled' WHERE `customer_reference` = :cusref";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":cusref", $data['cusref']);
    $result = $stmt->execute();

    if ($result) {
        $set->sendJsonResponse(["success" => "Order Cancelled!"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Order found"], 404);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
