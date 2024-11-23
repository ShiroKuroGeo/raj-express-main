<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();
$headers = apache_request_headers();
$start = isset($headers['start']) ? $headers['start'] : '';
$end = isset($headers['end']) ? $headers['end'] : '';

try {
    $startDate = $start ? $start : date('Y-m-d');
    $endDate = $end ? $end : date('Y-m-d');
    $query = "SELECT SUM(pay.payment_total) AS total FROM `orders` as ord INNER JOIN `payments` as pay ON ord.payment_id = pay.payment_id WHERE ord.status = 'cancelled' AND DATE(ord.created_at) BETWEEN :startDate AND :endDate";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "total" => $total
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Orders not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
