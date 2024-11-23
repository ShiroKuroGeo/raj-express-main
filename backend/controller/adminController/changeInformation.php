<?php
include '../../connection/dbconfig.php';
include '../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $query = "UPDATE `users` SET `first_name` = :first_name, `last_name` = :last_name, `email` = :email, `contact_number` = :contact_number, `address` = :addr WHERE `user_id` = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':first_name', $data['first_name']);
    $stmt->bindParam(':last_name', $data['last_name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':contact_number', $data['contact_number']);
    $stmt->bindParam(':addr', $data['address']);
    $result = $stmt->execute();

    if ($result) {
        $set->sendJsonResponse(["Success" => "Admin is updated!"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Users not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
