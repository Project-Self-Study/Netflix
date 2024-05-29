<?php
require_once 'config.php';

class Database {
    private $connection;

    public function __construct() {
        $this->connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function getConnection() {
        return $this->connection;
    }
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function close() {
        $this->connection->close();
    }
}