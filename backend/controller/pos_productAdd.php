<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

include "../connection/dbconfig.php"; // Adjust the path as necessary

$database = new Database();
$pdo = $database->getDb(); // Get the database connection

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $product_name = filter_input(INPUT_POST, 'product_name', FILTER_SANITIZE_STRING);
    $product_description = filter_input(INPUT_POST, 'product_description', FILTER_SANITIZE_STRING);
    $product_price = filter_input(INPUT_POST, 'product_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $product_status = filter_input(INPUT_POST, 'product_status', FILTER_SANITIZE_STRING);

    // Log the received category ID
    error_log('Received category ID: ' . $category_id);

    // Validate category ID
    $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();

    // Log the number of rows returned
    error_log('Number of rows returned: ' . $stmt->rowCount());

    if ($stmt->rowCount() == 0) {
        $response['message'] = 'Invalid category ID provided.';
        echo json_encode($response);
        exit;
    }

    // Handle product image upload
    $product_image = null;
    $upload_dir = '../uploads/';
    $allowed_types = array('jpg', 'png');

    if (!empty($_FILES['product_image']['name'])) {
        $file_name = basename($_FILES['product_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_path = $upload_dir . uniqid('img_', true) . '.' . $file_ext; // Unique image name

        if (!in_array($file_ext, $allowed_types)) {
            $response['message'] = 'Only JPG and PNG files are allowed.';
            echo json_encode($response);
            exit;
        }

        if ($_FILES['product_image']['size'] > 5000000) {
            $response['message'] = 'File size should not exceed 5MB.';
            echo json_encode($response);
            exit;
        }

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $file_path)) {
            $product_image = $file_path;
        } else {
            $response['message'] = 'Failed to upload the image.';
            echo json_encode($response);
            exit;
        }
    }

    // Insert product into the database
    $sql = "INSERT INTO products (category_id, product_name, product_description, product_price, product_status, product_image, created_at, updated_at)
            VALUES (:category_id, :product_name, :product_description, :product_price, :product_status, :product_image, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_name', $product_name, PDO::PARAM_STR);
    $stmt->bindParam(':product_description', $product_description, PDO::PARAM_STR);
    $stmt->bindParam(':product_price', $product_price, PDO::PARAM_STR);
    $stmt->bindParam(':product_status', $product_status, PDO::PARAM_STR);
    $stmt->bindParam(':product_image', $product_image, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Product added successfully.';
    } else {
        $response['message'] = 'Failed to add product.';
    }

    echo json_encode($response);
} else {
    http_response_code(405); // Method not allowed
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
}

