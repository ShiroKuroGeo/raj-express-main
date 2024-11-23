<?php
include '../../../../connection/dbconfig.php';
include '../../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $query = "SELECT SUM(pay.payment_total) AS total FROM `orders` as ord INNER JOIN `payments` as pay ON ord.payment_id = pay.payment_id WHERE MONTH(ord.created_at) = MONTH(CURRENT_DATE());";
    $stmt = $db->prepare($query);
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
