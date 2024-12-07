<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "enviromens";

        $this->connection = new mysqli($servername, $username, $password, $dbname);
        if ($this->connection->connect_error) {
            throw new Exception("Connection failed: " . $this->connection->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
} 