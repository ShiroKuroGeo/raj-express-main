<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    
    $query = "SELECT ord.customer_reference as cusref, pos.quantity, pos.price, pos.status, prod.product_name, prod.product_image, pos.pos_id, pos.created_at FROM `order_items` as pos INNER JOIN `products` as prod INNER JOIN `orders` as ord ON pos.product_id = prod.product_id AND pos.order_id = ord.order_id GROUP BY ord.customer_reference ORDER BY ord.created_at desc;";
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
