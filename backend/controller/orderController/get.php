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

    $query = "SELECT ord.customer_reference AS reference, SUM(pay.payment_total) AS totalPayment, GROUP_CONCAT(pro.product_name) AS product_names, GROUP_CONCAT(pro.product_image) AS product_images, ord.status AS orderStatus, ord.extra, ord.order_id as id, pay.payment_method, pay.payment_status FROM `orders` AS ord INNER JOIN `users` AS us ON ord.user_id = us.user_id INNER JOIN `products` AS pro ON ord.product_id = pro.product_id INNER JOIN `addresses` AS addr ON ord.address_id = addr.address_id INNER JOIN `payments` AS pay ON ord.payment_id = pay.payment_id WHERE ord.user_id = :user_id AND ord.status = 'pending' GROUP BY ord.customer_reference";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "ordersItems" => $orders,
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Cart not found"], 404);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
