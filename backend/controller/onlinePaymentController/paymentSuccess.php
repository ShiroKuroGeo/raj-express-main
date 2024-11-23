<?php
include '../controller.php';

$set = new controller();
$set->setCorsOrigin();
$data = $set->setInputData();

include "../../connection/dbconfig.php";

$database = new Database();
$db = $database->getDb();

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

try {
    $database = new Database();
    $db = $database->getDb();

    // Split the Authorization header (which contains the comma-separated payment ids) into an array
    $paymentIds = explode(',', $authHeader);  // This will give you an array like [48, 49]

    // Loop through the array of payment IDs and update each payment status
    $allPaymentsUpdated = true;
    foreach ($paymentIds as $paymentId) {
        $payment = "UPDATE `payments` SET `payment_status` = 'Paid on Online' WHERE `payment_id` = :paymentid";
        $paymentStmt = $db->prepare($payment);
        $paymentStmt->bindParam(':paymentid', $paymentId);
        $result = $paymentStmt->execute();

        if (!$result) {
            $allPaymentsUpdated = false;
            break;  // Stop the loop if any update fails
        }
    }

    if ($allPaymentsUpdated) {
        $set->sendJsonResponse(["success" => "Payments Successfully Updated"], 200);
    } else {
        $set->sendJsonResponse(["failed" => "Failed to update one or more payments"], 401);
    }
} catch (\Throwable $th) {
    $set->sendJsonResponse(["error" => "Error: " . $th->getMessage()], 500);
}
