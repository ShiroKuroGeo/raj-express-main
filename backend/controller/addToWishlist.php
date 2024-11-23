<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

include "../connection/dbconfig.php"; 

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header("Content-Type: application/json");
    
    $json = json_encode($data);
    
    if ($json === false) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to encode JSON"]);
    } else {
        echo $json;
    }
    exit;
}

try {
    $database = new Database();
    $db = $database->getDb();

    // Checking for existing cart item
    $checkWish = "SELECT * FROM `wishlists` WHERE `product_id` = :product_id AND `user_id` = :user_id";
    $wishlistStmt = $db->prepare($checkWish);
    $wishlistStmt->bindParam(":product_id", $data['product_id']);
    $wishlistStmt->bindParam(":user_id", $data['user_id']);
    $wishlistStmt->execute();

    if ($wishlistStmt->rowCount() > 0) {
        sendJsonResponse(["success" => 'You already have this product'], 200);
    }

    // Insert new cart item
    $query = "INSERT INTO `wishlists`(`user_id`, `product_id`) VALUES (:user_id, :product_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":product_id", $data['product_id']);
    $stmt->bindParam(":user_id", $data['user_id']);
    if ($stmt->execute()) {
        sendJsonResponse(["success" => "Product successfully added to your wishlist"], 200);
    } else {
        sendJsonResponse(["error" => "Failed to add product to your wishlist"], 400);
    }
} catch (PDOException $e) {
    sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
