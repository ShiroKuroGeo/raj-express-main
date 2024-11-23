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

// Handle GET request to fetch products or recent orders
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_recent_orders') {
        // Fetch recent orders from the database
        $query = "SELECT order_number, order_total, payment_method, created_at
                  FROM orders
                  ORDER BY created_at DESC
                  LIMIT 10";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'orders' => $orders]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to fetch recent orders: ' . $e->getMessage()]);
        }
    } else {
        // Existing code to fetch products
        $query = "SELECT p.product_id as id, p.category_id, p.product_name as name, p.product_description, p.product_price as price,
                       p.product_status as available, CONCAT('http://localhost/raj-express/backend/uploads/', p.product_image) as image, c.category_name as category
                FROM products p
                JOIN categories c ON p.category_id = c.category_id";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert available status to boolean and price to number
            foreach ($products as &$product) {
                $product['available'] = $product['available'] === 'Available';
                $product['price'] = floatval($product['price']);
            }

            // Format response
            echo json_encode(['products' => $products]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch products: ' . $e->getMessage()]);
        }
    }
    exit;
}

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
    $itemQuery = "INSERT INTO order_items (order_id, product_id, product_name, product_quantity, product_price) VALUES (?, ?, ?, ?, ?)";
    $itemStmt = $pdo->prepare($itemQuery);

    foreach ($data['items'] as $item) {
        $itemStmt->execute([
            $orderId,
            $item['product_id'],
            $item['name'], // Assuming 'name' is sent from the frontend
            $item['quantity'],
            $item['price']
        ]);
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
  if (isset($_GET['action'])) {
      switch ($_GET['action']) {
          case 'get_order_details':
              if (isset($_GET['order_number'])) {
                  $orderNumber = $_GET['order_number'];
                  error_log("Fetching details for order number: $orderNumber");

                  try {
                      // Fetch order items
                      $stmt = $pdo->prepare("SELECT product_name, product_quantity, product_price
                                             FROM order_items
                                             WHERE order_number = ?");
                      $stmt->execute([$orderNumber]);
                      $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                      // Fetch order details
                      $stmt = $pdo->prepare("SELECT order_id, order_total, payment_method, created_at FROM orders WHERE order_number = ?");
                      $stmt->execute([$orderNumber]);
                      $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

                      // Calculate total items
                      $totalItems = array_sum(array_column($products, 'product_quantity'));

                      // Format products for response
                      $formattedProducts = array_map(function($product) {
                          return [
                              'name' => $product['product_name'],
                              'quantity' => intval($product['product_quantity']),
                              'price' => floatval($product['product_price'])
                          ];
                      }, $products);

                      $response = [
                          'success' => true,
                          'products' => $formattedProducts,
                          'total_items' => $totalItems,
                          'order_number' => $orderDetails['order_number'] ?? 'N/A',
                          'order_total' => floatval($orderDetails['order_total'] ?? 0),
                          'payment_method' => $orderDetails['payment_method'] ?? 'N/A',
                          'created_at' => $orderDetails['created_at'] ?? 'N/A'
                      ];

                      // Before sending the response
                      error_log("Sending response: " . json_encode($response));

                      echo json_encode($response);
                  } catch (PDOException $e) {
                      error_log("Database error: " . $e->getMessage());
                      echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                  }
              } else {
                  error_log("Order number is missing");
                  echo json_encode(['success' => false, 'message' => 'Order number is missing']);
              }
              break;

          // ... other cases ...
      }
  } else {
      error_log("Action is missing");
      echo json_encode(['success' => false, 'message' => 'Action is missing']);
  }
  exit;
}




