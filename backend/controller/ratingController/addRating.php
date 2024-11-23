<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();
    
    $query = "INSERT INTO `ratings`(`user_id`, `product_id`, `feedback`, `fb_description`) VALUES (:user_id, :product_id, :feedback, :fb_description)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $data['user_id']);
    $stmt->bindParam(":product_id", $data['pid']);
    $stmt->bindParam(":feedback", $data['feedback']);
    $stmt->bindParam(":fb_description", $data['fb_description']);
    $result = $stmt->execute();

    if($result){
        $set->sendJsonResponse(["success" => "Successfully Rate!"], 200);
    }else{
        $set->sendJsonResponse(["error" => "Rate not created!!"], 400);
    }
    
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
