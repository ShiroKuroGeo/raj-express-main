<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    $queryToday = "SELECT SUM(price) AS total_price FROM order_items WHERE DATE(created_at) = CURDATE()";
    $stmt = $db->prepare($queryToday);
    $stmt->execute();
    $today = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'] ?? 0;

    $queryYesterday = "SELECT SUM(price) AS total_price FROM order_items WHERE DATE(created_at) = CURDATE() - INTERVAL 1 DAY";
    $stmt = $db->prepare($queryYesterday);
    $stmt->execute();
    $yesterday = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'] ?? 0;

    $queryThisWeek = "SELECT SUM(price) AS total_price FROM order_items WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
    $stmt = $db->prepare($queryThisWeek);
    $stmt->execute();
    $thisWeek = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'] ?? 0;

    $queryThisMonth = "SELECT SUM(price) AS total_price FROM order_items WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
    $stmt = $db->prepare($queryThisMonth);
    $stmt->execute();
    $thisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'] ?? 0;

    $queryThisYear = "SELECT SUM(price) AS total_price FROM order_items WHERE YEAR(created_at) = YEAR(CURDATE())";
    $stmt = $db->prepare($queryThisYear);
    $stmt->execute();
    $thisYear = $stmt->fetch(PDO::FETCH_ASSOC)['total_price'] ?? 0;

    $set->sendJsonResponse([
        "success" => true,
        'today' => $today,
        'yesterday' => $yesterday,
        'thisWeek' => $thisWeek,
        'thisMonth' => $thisMonth,
        'thisYear' => $thisYear,
    ]);

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
