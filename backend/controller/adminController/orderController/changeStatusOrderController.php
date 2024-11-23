<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    if (!empty($data['data'])) {
        foreach ($data['data'] as $order) {
            $msgQuery = "UPDATE `orders` SET `status` = :stat WHERE customer_reference = :cusref";
            $orderStmt = $db->prepare($msgQuery);
            $orderStmt->bindParam(":cusref", $order['product_id']); 
            $orderStmt->bindParam(":stat", $order['status']);
            $result = $orderStmt->execute();
            
            if($result){
                $payQuery = "UPDATE `payments` SET `payment_status`= :paymentStatus WHERE `payment_id` = :payId ";
                $payStmt = $db->prepare($payQuery);
                $payStmt->bindParam(":paymentStatus", $order['payment_status']); 
                $payStmt->bindParam(":payId", $order['payment_id']);
                $payStmt->execute();
        
            }else{
                $set->sendJsonResponse(["error" => "Status Change Failed!"], 400);
            }
        }
        $set->sendJsonResponse(["succes" => "Order Changed Status"], 200);
    }else{
        $set->sendJsonResponse(["error" => "No Data"], 401);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
