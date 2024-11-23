<?php
include '../controller.php';

$set = new Controller();
$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php";

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "UPDATE `addons` SET
        `deleted_at` = CURRENT_TIMESTAMP,
        `updated_at` = CURRENT_TIMESTAMP
        WHERE `addOn_id` = :addOn_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":addOn_id", $data['addOn_id']);

    if ($stmt->execute()) {
        $set->sendJsonResponse(["success" => "Successfully deleted Add On"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to delete Add On"], 400);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
