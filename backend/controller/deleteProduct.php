<?php
include 'controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    $deleteProductSQL = "DELETE FROM `products` WHERE `product_id` = :product_id";
    $deleteProductStmt = $db->prepare($deleteProductSQL);
    $deleteProductStmt->bindParam(":product_id", $data['product_id']);
    $result = $deleteProductStmt->execute();
    
    if ($result) {
        $set->sendJsonResponse(["success" => "Product successfully deleted!"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to delete product!"], 400);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
