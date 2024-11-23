
<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "INSERT INTO `addresses`(`user_id`, `personName`, `phoneNumber`, `latitude`, `longitude`, `deliveryAddress`, `streetNumber`, `landmark`) VALUES ( :user_id, :personName, :phoneNumber, :latitude, :longitude, :deliveryAddress, :streetNumber, :landmark)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(":user_id", $data['user_id']);
    $stmt->bindParam(":personName", $data['personName']);
    $stmt->bindParam(":phoneNumber", $data['phoneNumber']);
    $stmt->bindParam(":latitude", $data['latitude']);
    $stmt->bindParam(":longitude",  $data['longitude']);
    $stmt->bindParam(":deliveryAddress",  $data['deliveryAddress']);
    $stmt->bindParam(":streetNumber",  $data['streetNumber']);
    $stmt->bindParam(":landmark",  $data['landmark']);

    if ($stmt->execute()) {
        $set->sendJsonResponse(["success" => "Successfully add address!"], 200);
    } else {
        $set->sendJsonResponse(["error" => "Failed to add address"], 400);
    }
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
