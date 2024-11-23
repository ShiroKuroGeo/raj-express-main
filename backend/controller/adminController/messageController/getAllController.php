<?php
include '../../../connection/dbconfig.php';
include '../../controller.php';
$database = new Database();
$set = new controller();
$db = $database->getDb();
$set->setCorsOrigin();
$data = $set->setInputData();

try {
    
    $query = "SELECT CASE WHEN mess.sender_id != 1 THEN mess.sender_id WHEN mess.receiver_id != 1 THEN mess.receiver_id END AS user_id, us.first_name, us.last_name, us.contact_number, us.profile_img, MAX(mess.content) AS content, MAX(mess.read_status) AS read_status FROM `messages` AS mess INNER JOIN `users` AS us ON (us.user_id = mess.sender_id OR us.user_id = mess.receiver_id) WHERE mess.sender_id = 1 OR mess.receiver_id = 1 GROUP BY user_id, us.first_name, us.last_name, us.contact_number;";
    $stmt = $db->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $set->sendJsonResponse([
            "success" => true,
            "users" => $users
        ]);
    } else {
        $set->sendJsonResponse(["error" => "User not found"], 404);
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
