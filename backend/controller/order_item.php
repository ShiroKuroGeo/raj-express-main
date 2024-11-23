<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include dbconfig.php to get the database connection
include "../connection/dbconfig.php"; // Adjust the path as necessary

$db = new Database();
$pdo = $db->getDb(); // Get the PDO connection

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS request for CORS preflight
if ($method === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

try {
    switch($method) {
        case 'GET':
            $sql = "SELECT * FROM order_items";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $data['order_id'],
                ':product_id' => $data['product_id'],
                ':quantity' => $data['quantity'],
                ':price' => $data['price']
            ]);
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $sql = "UPDATE order_items SET order_id = :order_id, product_id = :product_id, quantity = :quantity, price = :price WHERE orderitems_id = :orderitems_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $data['order_id'],
                ':product_id' => $data['product_id'],
                ':quantity' => $data['quantity'],
                ':price' => $data['price'],
                ':orderitems_id' => $data['orderitems_id']
            ]);
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception("No ID provided for deletion");
            }
            $sql = "DELETE FROM order_items WHERE orderitems_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception("Unsupported HTTP method");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
