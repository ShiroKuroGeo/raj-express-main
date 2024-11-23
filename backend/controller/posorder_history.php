<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight (OPTIONS) request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

header('Content-Type: application/json');

include "../connection/dbconfig.php"; // Adjust the path as necessary

$database = new Database();
$pdo = $database->getDb(); // Get the database connection

// Handle POST request to add a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  try {
      $pdo->beginTransaction();

      // Insert order into orders table
      $orderQuery = "INSERT INTO orders (order_number, order_total, payment_method) VALUES (?, ?, ?)";
      $orderStmt = $pdo->prepare($orderQuery);
      $orderStmt->execute([$data['order_number'], $data['order_total'], $data['payment_method']]);

      $orderId = $pdo->lastInsertId();

      // Insert order items
      $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
      $itemStmt = $pdo->prepare($itemQuery);

      foreach ($data['items'] as $item) {
          $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
      }

      $pdo->commit();
      echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
  } catch (PDOException $e) {
      $pdo->rollBack();
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
  }
  exit;
}

// Handle GET request for fetching order details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_recent_orders') {
        // ... existing code for fetching recent orders ...
    } elseif (isset($_GET['action']) && $_GET['action'] === 'get_order_details' && isset($_GET['order_id'])) {
        $orderId = $_GET['order_id'];

        // Fetch order items
        $stmt = $pdo->prepare("SELECT oi.product_id, p.product_name, oi.quantity, oi.price
                               FROM order_items oi
                               JOIN products p ON oi.product_id = p.product_id
                               WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'items' => $items]);
        exit;
    }
}
