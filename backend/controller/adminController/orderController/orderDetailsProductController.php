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
    
    $query = "SELECT prod.product_id, prod.product_name, prod.product_price, prod.product_image, ord.order_qty as qty FROM `orders` as ord INNER JOIN `products` AS prod ON ord.product_id = prod.product_id WHERE ord.customer_reference = :order_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $set->sendJsonResponse([
            "success" => true,
            "orderDetails" => $orderDetails,
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Order Details not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
