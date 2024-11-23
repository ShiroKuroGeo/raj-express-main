<?php

class controller {
    public function setCorsOrigin(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 3600");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    public function setInputData(){
        $input = file_get_contents("php://input");
        return json_decode($input, true);
    }

    public function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header("Content-Type: application/json");

        $json = json_encode($data);

        if ($json === false) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to encode JSON"]);
        } else {
            echo $json;
        }
        exit;
    }
}




