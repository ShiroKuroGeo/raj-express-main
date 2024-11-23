<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';

$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();

// Get startDate and endDate from GET parameters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

try {
    // Base query
    $query = "SELECT `customer_reference`, COUNT(ord.order_id) as totalOrder 
              FROM `orders` AS ord 
              WHERE `status` = 'cancelled'";

    if ($startDate && $endDate) {
        $query .= " AND `created_at` BETWEEN :startDate AND :endDate";
    }

    $query .= " GROUP BY `customer_reference` ORDER BY `customer_reference`";

    $stmt = $db->prepare($query);

    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        $set->sendJsonResponse([
            "success" => true,
            "orderWithStatus" => $orders
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Orders not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
