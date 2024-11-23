<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : 8;

try {
    
    $query = "SELECT addre.latitude, addre.longitude, addre.deliveryAddress, addre.streetNumber, addre.landmark FROM `orders` as ord INNER JOIN `addresses` AS addre ON ord.address_id = addre.address_id WHERE ord.customer_reference = :order_id GROUP BY ord.customer_reference;";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $authHeader);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $addressDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
        $set->sendJsonResponse([
            "success" => true,
            "latitude" => $addressDetails['latitude'],
            "longitude" => $addressDetails['longitude'],
            "deliveryAddress" => $addressDetails['deliveryAddress'],
            "streetNumber" => $addressDetails['streetNumber'],
            "landmark" => $addressDetails['landmark'],
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Order Details not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
