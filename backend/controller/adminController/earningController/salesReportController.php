<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';

$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

try {
    // Base query
    $query = "SELECT COUNT(ord.order_id) AS totalOrder, SUM(pay.payment_total) as totalAmount, SUM(ord.order_qty) AS totalQTY FROM `orders` as ord INNER JOIN `payments` as pay ON ord.payment_id = pay.payment_id WHERE ord.status = 'delivered'";

    if ($startDate && $endDate) {
        $query .= " AND `created_at` BETWEEN :startDate AND :endDate";
    }

    $stmt = $db->prepare($query);

    if ($startDate && $endDate) {
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
    }

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $orders = $stmt->fetch(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "totalOrder" => $orders['totalOrder'],
            "totalQTY" => $orders['totalQTY'],
            "totalAmount" => $orders['totalAmount'],
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Orders not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}

