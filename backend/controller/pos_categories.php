<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
header(' header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Origin: http://localhost:9000");');

    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include dbconfig.php to get the database connection
include "../connection/dbconfig.php"; // Adjust the path as necessary

$db = new Database();
$pdo = $db->getDb(); // Get the PDO connection

// Helper function to send response
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
}

file_put_contents('php://stderr', print_r(apache_request_headers(), TRUE));
file_put_contents('php://stderr', print_r(headers_list(), TRUE));

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Fetch categories including status
        $stmt = $pdo->query("SELECT category_id, category_name, category_status FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['categories' => $categories]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add a new category
        $data = json_decode(file_get_contents("php://input"), true);
        $category_name = $data['category_name'] ?? null;
        $category_status = $data['category_status'] ?? 'Active'; // Default to 'Active' if not provided

        if ($category_name) {
            $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_status) VALUES (:category_name, :category_status)");
            $stmt->execute([':category_name' => $category_name, ':category_status' => $category_status]);
            sendResponse(["success" => true, "message" => "Category added successfully."]);
        } else {
            sendResponse(["success" => false, "error" => "Category name is required."], 400);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update an existing category
        $data = json_decode(file_get_contents("php://input"), true);
        $category_id = $data['category_id'] ?? null;
        $category_name = $data['category_name'] ?? null;
        $category_status = $data['category_status'] ?? null;

        if ($category_id && ($category_name !== null || $category_status !== null)) {
            $updateFields = [];
            $params = [':category_id' => $category_id];

            if ($category_name !== null) {
                $updateFields[] = "category_name = :category_name";
                $params[':category_name'] = $category_name;
            }
            if ($category_status !== null) {
                $updateFields[] = "category_status = :category_status";
                $params[':category_status'] = $category_status;
            }

            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";

            $sql = "UPDATE categories SET " . implode(", ", $updateFields) . " WHERE category_id = :category_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            sendResponse(["success" => true, "message" => "Category updated successfully."]);
        } else {
            sendResponse(["success" => false, "error" => "Category ID and at least one field to update are required."], 400);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete a category
        $data = json_decode(file_get_contents("php://input"), true);
        $category_id = $data['category_id'] ?? null;

        if ($category_id) {
            $stmt = $pdo->prepare("UPDATE `categories` SET `category_status`= 'inactive' WHERE category_id = :category_id");
            $stmt->execute([':category_id' => $category_id]);
            sendResponse(["success" => true, "message" => "Category is updated due to soft deleted and successfully."]);
        } else {
            sendResponse(["success" => false, "error" => "Category ID is required."], 400);
        }

    } else {
        sendResponse(["success" => false, "error" => "Invalid request method"], 405);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
