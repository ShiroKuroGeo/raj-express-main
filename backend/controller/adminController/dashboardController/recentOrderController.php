<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $query = "SELECT us.first_name, us.last_name, ord.order_qty, ord.customer_reference, prod.product_name FROM `orders` AS ord INNER JOIN `users` AS us INNER JOIN `products` AS prod ON ord.user_id = us.user_id AND ord.product_id = prod.product_id ORDER BY ord.created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $total = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "recentsOrder" => $total
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Orders not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
