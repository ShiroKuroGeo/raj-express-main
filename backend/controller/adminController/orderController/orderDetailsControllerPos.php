<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : 8;

try {
    $query = "SELECT 
    ord.customer_reference AS cusref, 
    COUNT(ord.order_id) AS total_orders,
    GROUP_CONCAT(prod.product_name SEPARATOR ', ') AS product_names,
    ord.extra, 
    ord.status, 
    ord.order_qty as qty, 
    ord.user_id, 
    pay.payment_id, 
    MIN(ord.created_at) AS first_order_date, 
    address.personName AS addressContactPerson, 
    address.phoneNumber AS addressContactNumber, 
    address.deliveryAddress, 
    address.latitude, 
    address.longitude, 
    address.streetNumber, 
    address.landmark, 
    pay.payment_method,
    ordItem.price,
    SUM(pay.payment_total) AS total_payment, 
    pay.payment_status 
    FROM `order_items` as ordItem
    INNER JOIN `orders` AS ord ON ordItem.order_id = ord.order_id
    INNER JOIN `products` AS prod ON ord.product_id = prod.product_id 
    INNER JOIN `addresses` AS address ON ord.address_id = address.address_id 
    INNER JOIN `payments` AS pay ON ord.payment_id = pay.payment_id 
    INNER JOIN `users` AS us ON ord.user_id = us.user_id 
    WHERE ord.customer_reference = :order_id 
    GROUP BY ord.customer_reference;";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            'cusref' => $orderDetails['cusref'],
            'user_id' => $orderDetails['user_id'],
            'payment_id' => $orderDetails['payment_id'],
            'total_orders' => $orderDetails['total_orders'],
            'product_names' => $orderDetails['product_names'],
            'extra' => $orderDetails['extra'],
            'qty' => $orderDetails['qty'],
            'status' => $orderDetails['status'],
            'first_order_date' => $orderDetails['first_order_date'],
            'addressContactPerson' => $orderDetails['addressContactPerson'],
            'addressContactNumber' => $orderDetails['addressContactNumber'],
            'deliveryAddress' => $orderDetails['deliveryAddress'],
            'streetNumber' => $orderDetails['streetNumber'],
            'landmark' => $orderDetails['landmark'],
            'payment_method' => $orderDetails['payment_method'],
            'total_payment' => $orderDetails['total_payment'],
            'payment_status' => $orderDetails['payment_status'],
            'latitude' => $orderDetails['latitude'],
            'longitude' => $orderDetails['longitude'],
        ]);

    } else {
        $set->sendJsonResponse([
            "success" => false,
            "message" => "No orders found for the provided reference.",
        ]);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
