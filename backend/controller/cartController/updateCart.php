<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "UPDATE `carts` SET `quantity`= :quantity, `extra`= :extra WHERE `cart_id` = :cart_id";
    $stmt = $db->prepare($query);
    $extraJson = json_encode($data['extra']);
    $stmt->bindParam(":quantity", $data['quantity']);
    $stmt->bindParam(":cart_id", $data['cart_id']);
    $stmt->bindParam(":extra", $extraJson);

    if ($stmt->execute()) {
        $set->sendJsonResponse(["success" => "Updated Successfully!"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to add product to cart"], 400);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
