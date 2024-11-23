<?php
include '../../connection/dbconfig.php';
include '../controller.php';

$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();

try {
    $data = json_decode(file_get_contents("php://input"));

    $currentPassword = isset($data->currentPassword) ? $data->currentPassword : '';
    $newPassword = isset($data->newPassword) ? $data->newPassword : '';

    if (empty($currentPassword) || empty($newPassword)) {
        $set->sendJsonResponse(["error" => "Both current and new passwords are required"], 400);
        exit;
    }

    $query = "SELECT `password_hash` FROM `users` WHERE `user_id` = :userId";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':userId', $data->userId); 
    $stmt->execute();
    $storedPasswordHash = $stmt->fetchColumn();

    if (!$storedPasswordHash) {
        $set->sendJsonResponse(["error" => "User not found"], 404);
        exit;
    }

    if (!password_verify($currentPassword, $storedPasswordHash)) {
        $set->sendJsonResponse(["error" => "Current password is incorrect"], 400);
        exit;
    }

    $newPassHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateQuery = "UPDATE `users` SET `password_hash` = :newPassword WHERE `user_id` = :userId";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':newPassword', $newPassHash);
    $updateStmt->bindParam(':userId', $data->userId); 
    $updateStmt->execute();

    $set->sendJsonResponse(["Success" => "Password updated successfully"], 200);

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
