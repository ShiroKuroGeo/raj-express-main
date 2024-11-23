<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    
    $query = "SELECT ord.order_id, ord.order_qty as qty, ord.customer_reference as cusref, ord.extra, ord.status, ord.created_at, prod.product_name, prod.product_image, address.personName as addressContactPerson, address.phoneNumber as addressContactNumber, address.deliveryAddress, address.streetNumber, address.landmark, pay.payment_method, pay.payment_total, pay.payment_status FROM `orders` as ord INNER JOIN `products` as prod INNER JOIN `addresses` as address INNER JOIN `payments` as pay INNER JOIN `users` as us ON ord.user_id = us.user_id AND ord.product_id = prod.product_id AND ord.address_id = address.address_id AND ord.payment_id = pay.payment_id WHERE ord.status = 'returned' GROUP BY ord.customer_reference ORDER BY ord.customer_reference;";
    $stmt = $db->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "orders" => $orders
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Orders not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
