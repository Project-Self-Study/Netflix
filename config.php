<?php

    class DB {
        private $connect = null; // The database connection
        private static $instance = null; // Static instance for the singleton pattern
    
        private function __construct() {
            $servername = 'wheatley.cs.up.ac.za';
            $username = 'u22506773';
            $password = 'MAIUBAXLVHG5R7XOPBG2RAU65YCINHDB';
            $dbName = 'u22506773';

            $this->connect = new mysqli($servername, $username, $password, $dbName);

            if ($this->connect->connect_error) {
                die("Connection failed: " . $this->connect->connect_error);
            } 
            else {
                // You might not want to echo here; it can cause issues in production
                // echo "Successful connection\n";
            }
        }

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new DB();
            }
            return self::$instance;
        }
        
        public function getConnection() {
            return $this->connect;
        }
        
        public function __destruct() {
            if ($this->connect !== null) {
                $this->connect->close();
            }
        }
    }
?>
