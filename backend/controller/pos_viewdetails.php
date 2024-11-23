<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    include "../connection/dbconfig.php"; // Adjust the path as necessary

    $database = new Database();
    $pdo = $database->getDb(); // Get the database connection

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_order_details') {
        if (isset($_GET['order_number'])) {
            $orderNumber = $_GET['order_number'];
            error_log("Fetching details for order number: $orderNumber");

            // Fetch order details
            $orderQuery = "SELECT o.order_id, o.order_number, o.created_at, o.payment_method,
                           (SELECT SUM(oi.product_quantity) FROM order_items oi WHERE oi.order_id = o.order_id) as total_items,
                           o.order_total
                    FROM orders o
                    WHERE o.order_number = ?";
            $orderStmt = $pdo->prepare($orderQuery);
            $orderStmt->execute([$orderNumber]);
            $orderDetails = $orderStmt->fetch(PDO::FETCH_ASSOC);

            if ($orderDetails) {
                // Fetch order items
                $itemsQuery = "SELECT oi.product_name, oi.product_quantity as quantity, oi.product_price as price
                               FROM order_items oi
                               WHERE oi.order_id = ?";
                $itemsStmt = $pdo->prepare($itemsQuery);
                $itemsStmt->execute([$orderDetails['order_id']]);
                $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                $response = [
                    'order_number' => $orderDetails['order_number'],
                    'created_at' => $orderDetails['created_at'],
                    'payment_method' => $orderDetails['payment_method'],
                    'total_items' => intval($orderDetails['total_items']),
                    'order_total' => floatval($orderDetails['order_total']),
                    'products' => array_map(function($item) {
                        return [
                            'product_name' => $item['product_name'],
                            'quantity' => intval($item['quantity']),
                            'price' => floatval($item['price'])
                        ];
                    }, $orderItems)
                ];

                echo json_encode($response);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Order number is missing']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
