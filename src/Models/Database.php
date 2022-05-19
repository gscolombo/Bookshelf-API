<?php
    namespace Models;
    use PDO, PDOStatement;
    
    class Database 
    {
        protected PDO $connection;

        public function __construct() {
            try {
                $this -> connection = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
            } catch (PDOException $err) {
                echo "Database connection failed - {$err -> getMessage()}";
                die();
            }
        }

        public function get_connection() {
            return $this -> connection;
        }
    }

?>