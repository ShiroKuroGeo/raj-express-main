<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

try {
    
    $query = "SELECT * FROM `addresses` WHERE `user_id` = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $address = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "addressess" => $address
        ]);
    } else {
        $set->sendJsonResponse(["success" => "No address added yet!"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
