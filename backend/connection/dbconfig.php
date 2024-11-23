<?php
class Database {
    private $host = "localhost";
    private $db_name = "raj-express";
    private $username = "root";
    private $password ="";
    public $conn;

    public function getDb() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Database connection successful");
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw $exception; // Re-throw the exception to be caught in the main script
        }
        return $this->conn;
    }

    public function getState() {
        return $this->conn !== null;
    }
    public function getErrMsg() {
        return "Unable to connect to the database.";
    }
}



