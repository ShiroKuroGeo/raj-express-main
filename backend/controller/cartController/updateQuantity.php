<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    if (empty($data['quantity']) || empty($data['id'])) {
        $set->sendJsonResponse(["error" => "Invalid input data"], 400);
        exit;
    }

    $database = new Database();
    $db = $database->getDb();
    $query = "UPDATE `carts` SET `quantity`= :qty WHERE `cart_id` = :cart_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":qty", $data['quantity']);
    $stmt->bindParam(":cart_id", $data['id']);
    $result = $stmt->execute();

    if ($result) {   
        $set->sendJsonResponse(["success" => 200], 200);
    } else {
        $set->sendJsonResponse(["error" => "Cart not found"], 404);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
