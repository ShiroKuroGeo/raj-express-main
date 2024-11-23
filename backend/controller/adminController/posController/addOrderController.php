<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';

$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $adminId = 1;
    $status = 'walk-in';
    $statusPaymentInCounter = 'over-the-counter';
    $orderReference = 'cusord24' . uniqid();

    foreach ($data['orders'] as $order) {

        $payment = "INSERT INTO `payments`(`user_id`, `payment_method`, `payment_total`, `payment_status`) VALUES (:user_id, :payment_method, :payment_total, :payment_status)";
        $paymentStmt = $db->prepare($payment);
        $paymentStmt->bindParam(":user_id", $adminId);
        $paymentStmt->bindParam(":payment_method", $status);
        $paymentStmt->bindParam(":payment_total", $order['price']);
        $paymentStmt->bindParam(":payment_status", $statusPaymentInCounter);
        $paymentStmt->execute();
        $paymentId = $db->lastInsertId();

        $orderSql = "INSERT INTO `orders`(`user_id`, `product_id`, `address_id`, `payment_id`, `customer_reference`, `order_qty`, `extra`, `status`) VALUES (:user_id, :product_id, :address_id, :payment_id, :cusref, :quantity, :extra, :stat)";
        $orderStmt = $db->prepare($orderSql);
        $orderStmt->bindParam(":product_id", $order['product_id']);
        $orderStmt->bindParam(":payment_id", $paymentId);
        $orderStmt->bindParam(":quantity", $order['quantity']);
        $orderStmt->bindParam(":cusref", $orderReference);
        $orderStmt->bindParam(":address_id", $adminId);
        $orderStmt->bindParam(":user_id", $adminId);
        $orderStmt->bindParam(":stat", $statusPaymentInCounter);
        $orderStmt->bindParam(":extra", $order['extra']);
        $result = $orderStmt->execute();
        $orderId = $db->lastInsertId();

        if ($result) {
            $orderItemSql = "INSERT INTO `order_items`(`order_id`, `product_id`, `product_name`, `quantity`, `price`, `status`, `payment_status`) VALUES (:order_id, :product_id, :product_name, :quantity, :price, :payment_status, :stat)";
            $orderItemStmt = $db->prepare($orderItemSql);
            $orderItemStmt->bindParam(":order_id", $orderId);
            $orderItemStmt->bindParam(":product_id", $order['product_id']);
            $orderItemStmt->bindParam(":product_name", $order['product_name']);
            $orderItemStmt->bindParam(":quantity", $order['quantity']);
            $orderItemStmt->bindParam(":price", $order['price']);
            $orderItemStmt->bindParam(":payment_status", $order['payment_status']);
            $orderItemStmt->bindParam(":stat", $order['status']);
            $orderItemStmt->execute();

        } else {
            $set->sendJsonResponse(["error" => "Failed to create payment record."], 409);
        }
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
