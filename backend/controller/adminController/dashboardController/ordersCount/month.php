<?php
include '../../../../connection/dbconfig.php';
include '../../../controller.php';

$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

function getTotalByStatus($db, $status) {
    $query = "SELECT count(ord.order_id) as total 
                FROM `orders` as ord 
                INNER JOIN `payments` as pay ON ord.payment_id = pay.payment_id 
                WHERE MONTH(ord.created_at) = MONTH(CURDATE()) 
                AND YEAR(ord.created_at) = YEAR(CURDATE()) 
                AND ord.status = :status";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

try {
    $pending = getTotalByStatus($db, 'pending');
    $delivered = getTotalByStatus($db, 'delivered');
    $cancelled = getTotalByStatus($db, 'cancelled');
    
    $orderSeries = [(float)$pending, (float)$delivered, (float)$cancelled]; 
    
    $set->sendJsonResponse([
        "success" => true,
        'orderSeries' => $orderSeries, 
    ]);

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
?>
