<?php
include '../controller/controller.php';

$set = new controller();

$set->setCorsOrigin();

try {
    include_once '../connection/dbconfig.php';

    error_log("dbconfig.php included successfully");

    $input = file_get_contents("php://input");
    error_log("Received raw input: " . $input);

    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        $set->sendJsonResponse(["error" => "Invalid JSON input: " . json_last_error_msg()], 400);
    }

    error_log("Decoded data: " . print_r($data, true));

    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'password', 'user_type', 'status'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $set->sendJsonResponse(["error" => "$field is required"], 400);
        }
    }

    // Create database connection
    $database = new Database();
    $db = $database->getDb();

    // Check if email already exists
    $check_email_query = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
    $check_stmt = $db->prepare($check_email_query);
    $check_stmt->bindParam(":email", $data['email']);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        $set->sendJsonResponse(["error" => "Email already exists"], 400);
    }

    // Prepare the SQL query for insertion
    $query = "INSERT INTO users (first_name, last_name, address, contact_number, email, password_hash, user_type, status)
          VALUES (:first_name, :last_name, :address, :contact_number, :email, :password_hash, :user_type, :status)";

    $stmt = $db->prepare($query);

    // Hash the password
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Bind the parameters
    $stmt->bindParam(":first_name", $data['first_name']);
    $stmt->bindParam(":last_name", $data['last_name']);
    $stmt->bindParam(":address", $data['address']);
    $stmt->bindParam(":contact_number", $data['contact_number']);
    $stmt->bindParam(":email", $data['email']);
    $stmt->bindParam(":password_hash", $password_hash);
    $stmt->bindParam(":user_type", $data['user_type']);
    $stmt->bindParam(":status", $data['status']);

    // Execute the query
    if ($stmt->execute()) {
        $set->sendJsonResponse(["message" => "User registered successfully"], 201);
    } else {
        $set->sendJsonResponse(["error" => "Unable to register user"], 500);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $set->sendJsonResponse(["error" => "A database error occurred: " . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $set->sendJsonResponse(["error" => "An unexpected error occurred: " . $e->getMessage()], 500);
}
