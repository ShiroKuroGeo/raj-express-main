<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $monthlySales = [];

    for ($month = 1; $month <= 12; $month++) {
        $queryMonthly = "SELECT SUM(pay.payment_total) as total_price 
                         FROM `orders` as ord 
                         INNER JOIN `payments` as pay ON ord.payment_id = pay.payment_id 
                         WHERE ord.status = 'delivered' 
                         AND MONTH(ord.created_at) = :month 
                         AND YEAR(ord.created_at) = YEAR(CURDATE())";
                         
        $stmt = $db->prepare($queryMonthly);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->execute();
        
        $totalPrice = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'];
        $monthlySales[] = is_numeric($totalPrice) ? (float)$totalPrice : 0;
    }

    $set->sendJsonResponse([
        "success" => true,
        'name' => 'delivered',
        'monthlySales' => $monthlySales,
    ]);

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
?>
