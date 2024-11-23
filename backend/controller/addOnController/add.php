<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php";

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "INSERT INTO `addons`(
        `ao_name`,
        `ao_price`,
        `ao_status`,
        `created_at`,
        `updated_at`
    ) VALUES (
        :aoname,
        :aoprice,
        :aostatus,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":aoname", $data['aoname']);
    $stmt->bindParam(":aoprice", $data['aoprice']);
    $stmt->bindParam(":aostatus", $data['aostatus']);

    if ($stmt->execute()) {
        $set->sendJsonResponse(["success" => "Successfully added to Add Ons"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to add to Add Ons"], 400);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
