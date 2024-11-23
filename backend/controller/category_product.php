<?php
header("Access-Control-Allow-Origin: http://localhost:9000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
include "../connection/dbconfig.php"; // Adjust the path as necessary

$database = new Database();
$pdo = $database->getDb(); // Get the database connection

// Helper function to send response
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
}

// Handle GET, POST, PUT, DELETE methods
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check if category filter is provided
        if (isset($_GET['category_id'])) {
            $category_id = $_GET['category_id'];

            // Fetch products by category
            $stmt = $pdo->prepare("
                SELECT p.product_id, p.category_id, p.product_name, p.product_description, p.product_price,
                       p.product_status, p.product_image as product_image, c.category_name as category
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE c.category_id = ?
            ");
            $stmt->execute([$category_id]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($products) {
                sendResponse(['products' => $products]);
            } else {
                sendResponse(['message' => 'No products found for this category'], 404);
            }
        } else {
            // Fetch all products if no category filter is provided
            $stmt = $pdo->query("
                SELECT p.product_id, p.category_id, p.product_name, p.product_description, p.product_price,
                       p.product_status, CONCAT('http://localhost/raj-express/backend/uploads/', p.product_image) as product_image, c.category_name as category
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
            ");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse($products);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle adding categories or products based on input data
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['category_name'])) {
            // Add a new category
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_status) VALUES (?, ?)");
            $stmt->execute([$data['category_name'], $data['category_status'] ?? 'Active']);
            sendResponse(["success" => true, "message" => "Category added successfully."]);
        } else {
            // Add or update a product
            $category_id = $data['category_id'] ?? null;
            $product_name = $data['product_name'] ?? null;
            $product_price = $data['product_price'] ?? null;

            // Basic validation
            if (!$category_id || !$product_name || !$product_price) {
                sendResponse(["error" => "Category ID, Product name, and Product price are required."], 400);
                exit();
            }

            if (isset($data['product_id'])) {
                // Update product
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, product_name = ?, product_description = ?, product_price = ?, product_status = ? WHERE product_id = ?");
                $stmt->execute([$category_id, $product_name, $data['product_description'], $product_price, $data['product_status'], $data['product_id']]);
                sendResponse(["success" => true, "message" => "Product updated successfully."]);
            } else {
                // Add new product
                $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, product_description, product_price, product_status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $product_name, $data['product_description'], $product_price, $data['product_status']]);
                sendResponse(["success" => true, "message" => "Product added successfully."]);
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Handle delete for categories or products
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['category_id'])) {
            // Delete category
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$data['category_id']]);
            sendResponse(["success" => true, "message" => "Category deleted successfully."]);
        } elseif (isset($data['product_id'])) {
            // Delete product
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->execute([$data['product_id']]);
            sendResponse(["success" => true, "message" => "Product deleted successfully."]);
        } else {
            sendResponse(["error" => "Category ID or Product ID required for deletion."], 400);
        }
    } else {
        sendResponse(["error" => "Method not allowed."], 405);
    }
} catch (PDOException $e) {
    sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
