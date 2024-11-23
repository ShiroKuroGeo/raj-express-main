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
        `ao_name` = :aoname,
        `ao_price` = :aoprice,
        `ao_status` = :aostatus,
        `updated_at` = CURRENT_TIMESTAMP
        WHERE `addOn_id` = :addOn_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":addOn_id", $data['addOn_id']);
    $stmt->bindParam(":aoname", $data['aoname']);
    $stmt->bindParam(":aoprice", $data['aoprice']);
    $stmt->bindParam(":aostatus", $data['aostatus']);

    if ($stmt->execute()) {
        $set->sendJsonResponse(["success" => "Successfully updated Add On"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to update Add On"], 400);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
