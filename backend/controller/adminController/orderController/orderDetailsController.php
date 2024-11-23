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
	ord.*,
    pay.payment_method,
    pay.payment_status,
    pay.payment_total,
    address.latitude,
    address.longitude
    FROM `orders` as ord 
INNER JOIN `payments` as pay ON ord.payment_id = pay.payment_id 
INNER JOIN `addresses` as address ON ord.address_id = address.address_id
WHERE ord.customer_reference = :order_id;";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            'orders' => $orderDetails,
            // 'total_orders' => $orderDetails['total_orders'],
            // 'product_id' => $orderDetails['product_id'],
            // 'product_names' => $orderDetails['product_names'],
            // 'extra' => $orderDetails['extra'],
            // 'status' => $orderDetails['status'],
            // 'first_order_date' => $orderDetails['first_order_date'],
            // 'addressContactPerson' => $orderDetails['addressContactPerson'],
            // 'addressContactNumber' => $orderDetails['addressContactNumber'],
            // 'deliveryAddress' => $orderDetails['deliveryAddress'],
            // 'streetNumber' => $orderDetails['streetNumber'],
            // 'landmark' => $orderDetails['landmark'],
            // 'payment_method' => $orderDetails['payment_method'],
            // 'total_payment' => $orderDetails['total_payment'],
            // 'payment_status' => $orderDetails['payment_status'],
            // 'latitude' => $orderDetails['latitude'],
            // 'longitude' => $orderDetails['longitude'],
            // 'product_id' => $orderDetails['product_id'],
            // 'address_id' => $orderDetails['address_id'],
            // 'payment_id' => $orderDetails['payment_id'],
            // 'user_id' => $orderDetails['user_id'],
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
