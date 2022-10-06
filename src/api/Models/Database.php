<?php
    namespace Models;
    use PDO, PDOStatement, PDOException;
    
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

        public function init() {
            $users_sql = "CREATE TABLE IF NOT EXISTS users (
                id INT(255) AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );";

            $authors_sql = "CREATE TABLE IF NOT EXISTS authors (
                id INT(255) AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                nationality VARCHAR(255),
                PRIMARY KEY (id)
            );";

            try {
                $this -> get_connection() -> exec($users_sql);
                $this -> get_connection() -> exec($authors_sql);
            } catch (PDOException | Exception $err) {
                http_response_code(500);
                echo json_encode(["message" => "Error creating tables", "error" => $err -> getMessage()]);
                die();
            }
           
        }
    }

?>