<?php 
    use Models\Database;
    use PHPUnit\Framework\TestCase;

    const DB_USER = "root";
    const DB_PASSWORD = "root";
    const DB_DSN = "mysql:dbname=phpunit;host=db";

    final class DatabaseTest extends TestCase
    {   
        private static Database $db;

        public static function setUpBeforeClass() : void
        {
            self::$db = new Database();
        }        

        public function testDatabaseInstatiantion() {
            $this -> assertInstanceOf(Database::class, self::$db);
        }

        public function testConnectionSucess(){
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
            $conn = self::$db -> get_connection();
            $this -> assertEquals($pdo, $conn);
            $this -> assertGreaterThanOrEqual(1, $conn -> query("SELECT CONNECTION_ID()", PDO::FETCH_COLUMN, 0) -> fetch());
        }
    }
?>