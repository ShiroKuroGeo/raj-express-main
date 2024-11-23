<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    
    $query = "SELECT rat.rating_id, rat.feedback, rat.fb_description, us.first_name, us.last_name, us.contact_number, pro.product_name, pro.product_image as productImage FROM `ratings` as rat INNER JOIN `users` as us INNER JOIN `products` as pro ON rat.user_id = us.user_id AND rat.product_id = pro.product_id;";
    $stmt = $db->prepare($query);
    $result = $stmt->execute();

    if ($result) {
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "reviews" => $reviews
        ]);
    } else {
        $set->sendJsonResponse(["error" => "Users not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
