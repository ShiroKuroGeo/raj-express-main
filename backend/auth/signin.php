<?php
include '../controller/controller.php';

$set = new controller();

$set->setCorsOrigin();

try {
    include_once '../connection/dbconfig.php';

    error_log("dbconfig.php included successfully");

    $data = $set->setInputData();

    // Get the posted data
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;


    // Validate input
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required.']);
        exit;
    }

    // Create database connection
    $database = new database();
    $db = $database->getDb();

    // Prepare the SQL query
    $query = "SELECT user_id, first_name, last_name, email, password_hash, user_type, status FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                http_response_code(403);
                echo json_encode(['error' => 'Account is not active']);
                exit;
            }

            // Login successful
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'user_id' => $user['user_id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'user_type' => $user['user_type'],
                'status' => $user['status']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'User not found']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    sendJsonResponse(["error" => "A database error occurred"], 500);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    sendJsonResponse(["error" => "An unexpected error occurred"], 500);
}
