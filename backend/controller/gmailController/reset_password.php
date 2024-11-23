<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

if (empty($data['newPassword']) || empty($data['token'])) {
    $set->sendJsonResponse(["error" => "Missing required parameters"], 400);
    exit;
}

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "SELECT * FROM `users` WHERE `reset_token` = :reset_token";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":reset_token", $data['token']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $query = "UPDATE `users` SET `password_hash` = :password_hash, `reset_token` = null WHERE `reset_token` = :reset_token";
        $stmt = $db->prepare($query);
        $password_hash = password_hash($data['newPassword'], PASSWORD_DEFAULT);
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":reset_token", $data['token']);

        if ($stmt->execute()) {
            $set->sendJsonResponse(["success" => "Successfully changed password"], 200);
        } else {
            $set->sendJsonResponse(["error" => "Failed to reset password"], 400);
        }
    } else {
        $set->sendJsonResponse(["error" => "Invalid or expired token"], 400);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
?>
