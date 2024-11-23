<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {

    $expensesSQL = "SELECT 
    (SELECT IFNULL(SUM(amount), 0) FROM expenses WHERE DATE(date) = CURDATE()) AS total_today,
    (SELECT IFNULL(SUM(amount), 0) FROM expenses WHERE YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())) AS total_this_month,
    (SELECT IFNULL(SUM(amount), 0) FROM expenses WHERE YEAR(date) = YEAR(CURDATE())) AS total_this_year;";
    $expensesStmt = $db->prepare($expensesSQL);
    $result = $expensesStmt->execute();

    $total = $expensesStmt->fetch(PDO::FETCH_ASSOC);
    $set->sendJsonResponse([
        "success" => true,
        "today" => $total['total_today'],
        "month" => $total['total_this_month'],
        "year" => $total['total_this_year']
    ]);

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
