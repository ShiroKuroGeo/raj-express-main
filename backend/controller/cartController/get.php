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
    $query = "SELECT cart.cart_id, cart.extra, cart.product_id, cart.user_id, cart.quantity, cart.status, cart.created_at, cart.updated_at, CONCAT(user.first_name, ' ', user.last_name) AS fullname, user.contact_number, user.email, product.product_name, product.product_price, product.product_image, AVG(rating.feedback) AS average_rating FROM `carts` AS cart INNER JOIN `users` AS user ON cart.user_id = user.user_id INNER JOIN `products` AS product ON cart.product_id = product.product_id LEFT JOIN `ratings` AS rating ON product.product_id = rating.product_id WHERE cart.user_id = :user_id AND cart.status = 'pending' GROUP BY cart.cart_id;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "cartItems" => $cart,
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Cart not found"], 404);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
