<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {

    $expensesSQL = "SELECT * FROM `expenses` ORDER BY `created_at` DESC";
    $expensesStmt = $db->prepare($expensesSQL);
    $result = $expensesStmt->execute();

    $total = $expensesStmt->fetchAll(PDO::FETCH_ASSOC);
    $set->sendJsonResponse([
        "success" => true,
        "total" => $total
    ]);

} catch (Exception $e) {
    $set->sendJsonResponse(["error" => "Error: " . $e->getMessage()], 500);
}
