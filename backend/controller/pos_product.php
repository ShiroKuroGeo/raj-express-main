<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: http://localhost:9000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight (OPTIONS) request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

include "../connection/dbconfig.php"; // Adjust the path as necessary

$database = new Database();
$pdo = $database->getDb(); // Get the database connection

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT p.product_id, p.category_id, p.product_name, p.product_description, p.product_price,
                   p.product_status, CONCAT('http://localhost/raj-express/backend/uploads/', p.product_image) as product_image, c.category_name as category
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $addon_id = filter_input(INPUT_POST, 'addon_id', FILTER_SANITIZE_NUMBER_INT);
    $product_name = filter_input(INPUT_POST, 'product_name', FILTER_SANITIZE_STRING);
    $product_description = filter_input(INPUT_POST, 'product_description', FILTER_SANITIZE_STRING);
    $product_price = filter_input(INPUT_POST, 'product_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $product_status = filter_input(INPUT_POST, 'product_status', FILTER_SANITIZE_STRING);

    // Log sanitized data
    error_log("Sanitized data: " . json_encode([
        'category_id' => $category_id,
        'addon_id' => $addon_id,
        'product_name' => $product_name,
        'product_description' => $product_description,
        'product_price' => $product_price,
        'product_status' => $product_status
    ]));

    // Validate required fields
    $errors = [];
    if (empty($category_id)) $errors[] = "Category ID is required";
    if (empty($product_name)) $errors[] = "Product name is required";
    if (empty($product_price)) $errors[] = "Product price is required";

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields: " . implode(", ", $errors)]);
        exit();
    }

    $data = $_POST;
    $isUpdate = isset($data['product_id']) && !empty($data['product_id']);

    try {
        // Check if category_id exists and is not empty
        if (!isset($data['category_id']) || $data['category_id'] === '') {
          error_log('Category ID is missing or empty. POST data: ' . print_r($data, true));
            throw new Exception("Category ID is required and cannot be empty");
        }

        // Verify if the category exists
        $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $stmt->execute([$data['category_id']]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Invalid category ID: " . $data['category_id']);
        }

        if ($isUpdate) {
            // Update existing product
            $stmt = $pdo->prepare("UPDATE products SET category_id = ?, addon_id = ?, product_name = ?, product_description = ?, product_price = ?, product_status = ? WHERE product_id = ?");
            $result = $stmt->execute([$data['category_id'], $data['addon_id'], $data['product_name'], $data['product_description'], $data['product_price'], $data['product_status'], $data['product_id']]);
        } else {
            // Add new product
            $stmt = $pdo->prepare("INSERT INTO products (category_id, addon_id ,product_name, product_description, product_price, product_status) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$data['category_id'], $data['addon_id'], $data['product_name'], $data['product_description'], $data['product_price'], $data['product_status']]);
        }

        if (!$result) {
            throw new Exception(json_encode($stmt->errorInfo()));
        }

        // Handle image upload if present
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            $fileName = uniqid() . '_' . $_FILES['product_image']['name'];
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadFile)) {
                $productId = $isUpdate ? $data['product_id'] : $pdo->lastInsertId();
                $stmt = $pdo->prepare("UPDATE products SET product_image = ? WHERE product_id = ?");
                $stmt->execute([$fileName, $productId]);
            }
        }

        echo json_encode(['success' => true, 'message' => $isUpdate ? 'Product updated successfully' : 'Product added successfully']);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        error_log('Exception occurred: ' . $e->getMessage());
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Handle PUT request
    $input = file_get_contents('php://input');
    parse_str($input, $_PUT);
    // Process $_PUT data
}

error_log(print_r($_SERVER, true));
error_log(print_r(file_get_contents('php://input'), true));

// At the end of the file, add:
error_log(print_r($_POST, true));
error_log(print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'delete') {
        $product_id = $data['product_id'];

        $stmt = $pdo->prepare("DELETE FROM `products` WHERE `product_id` = ?");
        $result = $stmt->execute([$product_id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $product_id]);
        }
        exit;
    }
}
