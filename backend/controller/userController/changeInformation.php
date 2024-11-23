<?php
include '../controller.php';

$set = new controller();

$set->setCorsOrigin();

$data = $set->setInputData();

include "../../connection/dbconfig.php"; 

try {
    $database = new Database();
    $db = $database->getDb();

    if($data['password'] == null){
        $query = "UPDATE `users` SET `first_name` = :first_name, `last_name` = :last_name, `email` = :email, `contact_number` = :contact_number WHERE `user_id` = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":first_name", $data['firstName']);
        $stmt->bindParam(":last_name", $data['lastName']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":contact_number", $data['number']);
        $stmt->bindParam(":user_id", $data['user_id']);
        $result = $stmt->execute();

        if($result){
            $set->sendJsonResponse(["success" => "Successfully Updated!"], 200);
        }else{
            $set->sendJsonResponse(["error" => "Information not Updated!"], 400);
        }
    }else{
        $passHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $query = "UPDATE `users` SET `first_name` = :first_name, `last_name` = :last_name, `email` = :email, `contact_number` = :contact_number, `password_hash` = :password  WHERE `user_id` = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":first_name", $data['firstName']);
        $stmt->bindParam(":last_name", $data['lastName']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":contact_number", $data['number']);
        $stmt->bindParam(":password", $passHash);
        $stmt->bindParam(":user_id", $data['user_id']);
        $result = $stmt->execute();

        if($result){
            $set->sendJsonResponse(["success" => "Successfully Updated!"], 200);
        }else{
            $set->sendJsonResponse(["error" => "Information not Updated!"], 400);
        }
    }

} catch (PDOException $e) {
    $set->sendJsonResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
