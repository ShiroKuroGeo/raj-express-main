<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

try {
    $query = "SELECT SUM(pay.payment_total) AS total FROM `orders` AS ord INNER JOIN `payments` AS pay ON ord.payment_id = pay.payment_id WHERE ord.status = 'delivered'";

    if ($startDate && $endDate) {
        $query .= " AND ord.created_at BETWEEN :startDate AND :endDate";
    }

    $stmt = $db->prepare($query);

    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "total" => $total
        ]);
    } else {
        $set->sendJsonResponse(["error" => "No data found for the given date range"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}