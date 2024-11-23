<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

try {
    $database = new Database();
    $db = $database->getDb();

    $uploadDir = '../../uploads/';
    $filename = uniqid() . '-' . basename($_FILES['profile_picture']['name']);
    $uploadFile = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
        $response['status'] = 'success';

        $query = "UPDATE `users` SET `profile_img` = :profile_img WHERE `user_id` = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":user_id", $authHeader);
        $stmt->bindParam(":profile_img", $filename);
        $result = $stmt->execute();

        if($result){
            $set->sendJsonResponse(["success" => "Image Uploaded!"], 200);
        }else{
            $set->sendJsonResponse(["notFound" => "File image not found!"], 409);
        }

    } else {
        $set->sendJsonResponse(["error" => "No file uploaded or upload error."], 400);
    }
    

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}

?>
