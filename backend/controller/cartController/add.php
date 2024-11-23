<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();
    
    $checkCart = "SELECT `product_id`, `user_id` FROM `carts` WHERE `status` = :status AND `product_id` = :product_id AND `user_id` = :user_id";
    $cartStmt = $db->prepare($checkCart);
    $pendingStatus = 'pending';
    $cartStmt->bindParam(":status", $pendingStatus);
    $cartStmt->bindParam(":product_id", $data['product_id']);
    $cartStmt->bindParam(":user_id", $data['user_id']);
    $cartStmt->execute();
    
    if ($cartStmt->rowCount() > 0) {
        $set->sendJsonResponse(["success" => "Product already in your cart!"], 200);
    }else{
        
        $query = "INSERT INTO `carts` (`product_id`, `user_id`, `quantity`, `status`, `extra`) VALUES (:product_id, :user_id, :quantity, :status, :extra)";
        $stmt = $db->prepare($query);
        $extraJson = json_encode($data['extra']);
        $stmt->bindParam(":product_id", $data['product_id']);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":quantity", $data['quantity']);
        $stmt->bindParam(":status", $pendingStatus);
        $stmt->bindParam(":extra", $extraJson);
    
        if ($stmt->execute()) {
            $set->sendJsonResponse(["success" => "Product successfully added to cart"], 200);
        } else {
            $set->sendJsonResponse(["error" => "Failed to add product to cart"], 400);
        }
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
