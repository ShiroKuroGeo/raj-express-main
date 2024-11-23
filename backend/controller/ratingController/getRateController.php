<?php
include '../controller.php';

$set = new controller();
$set->setCorsOrigin();
$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();
    
    $query = "SELECT * FROM `ratings`";
    $stmt = $db->prepare($query);
    $stmt->execute();
    // $feedbacks = [];
    // $totalFeedback = 0;
    // $totalNumberOfRate = 0;

    if ($stmt->rowCount() > 0) {
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // foreach ($ratings as $rating) {
        //     $feedbacks[] = (int)$rating['feedback'];
        //     $totalFeedback += (int)$rating['feedback'];
        // }
        
        // $totalNumberOfRate = count($ratings); 
        // $averageRating = $totalNumberOfRate > 0 ? round($totalFeedback / $totalNumberOfRate, 1) : 0;
        
        $set->sendJsonResponse([
            "success" => true,
            "ratings" => $ratings,
        ]);
    } else {
        $set->sendJsonResponse(["error" => "No ratings found for this product"], 404);
    }
    
} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
