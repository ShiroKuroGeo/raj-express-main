<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "DELETE FROM `carts` WHERE `cart_id` = :cart_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":cart_id", $data['cart_id']);

    if ($stmt->execute()) {
        $set->sendJsonResponse(["success" => "Removed Successfully!"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to add product to cart"], 400);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
