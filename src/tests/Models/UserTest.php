<?php 
    use Models\User;
    use Controllers\BaseController;
    use Firebase\JWT\{JWT, Key, SignatureInvalidException};
    use PHPUnit\Framework\TestCase;
    use PDO;

    final class UserTest extends TestCase
    {
        private static User $user;
        private static PDO $conn;
        private static string $name, $email, $password, $public_key, $token;
        private static OpenSSLAsymmetricKey $private_key;

        public static function setUpBeforeClass() : void
        {
            self::$user = new User();
            self::$conn = self::$user -> get_connection();
            self::$name = "New User";
            self::$email = "newuser@mail.com";
            self::$password = "1234";
            self::$private_key = openssl_pkey_new([
                "digest_alg" => "sha256", 
                "private_key_bits" => 4096,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            ]);
            self::$public_key = openssl_pkey_get_details(self::$private_key)["key"];
        }

        public function testUserRegistration()
        {
            self::$user -> subscribe(self::$name, self::$email, self::$password, self::$private_key, self::$public_key);
            $this -> expectOutputString($this -> getActualOutput());
            $stdout = json_decode($this -> getActualOutput(), true);
            $this -> assertEquals("User registered successfully!", $stdout["message"]);
            $this -> assertEquals(self::$public_key, $stdout["public_key"]);
            $this -> assertLessThanOrEqual(time() + 3600, $stdout["expireAt"]);
            $this -> assertStringStartsWith("eyJ", $stdout["jwt"]);
            $this -> assertEquals(1, self::$conn -> query("SELECT COUNT(id) FROM users", PDO::FETCH_COLUMN, 0) -> fetch());
            $this -> assertEquals(self::$user -> getID(), self::$conn -> query("SELECT id FROM users", PDO::FETCH_COLUMN, 0) -> fetch());

            return self::$user -> getID();
        }

        public function testUserValidationWithNameGiven()
        {   
            $login_private_key = openssl_pkey_new(["digest_alg" => "sha256", "private_key_bits" => 4096]);
            $login_public_key = openssl_pkey_get_details($login_private_key)["key"];
            self::$user -> validate(self::$password, $login_private_key, $login_public_key, self::$name);
            $this -> expectOutputString($this -> getActualOutput());
            $stdout = json_decode($this -> getActualOutput(), true);

            $this -> assertEquals("Successful login!", $stdout["message"]);
            $this -> assertEquals($login_public_key, $stdout["public_key"]);
            $this -> assertLessThanOrEqual(time() + 3600, $stdout["expireAt"]);
            $this -> assertStringStartsWith("eyJ", $stdout["jwt"]);
            $this -> assertEquals(1, self::$conn -> query("SELECT COUNT(id) FROM users", PDO::FETCH_COLUMN, 0) -> fetch());
            $this -> assertEquals(self::$user -> getID(), self::$conn -> query("SELECT id FROM users", PDO::FETCH_COLUMN, 0) -> fetch());

            return $stdout;
        }

        public function testUserValidationWithEmailGiven()
        {   
            $login_private_key = openssl_pkey_new(["digest_alg" => "sha256", "private_key_bits" => 4096]);
            $login_public_key = openssl_pkey_get_details($login_private_key)["key"];
            self::$user -> validate(self::$password, $login_private_key, $login_public_key, self::$email);
            $this -> expectOutputString($this -> getActualOutput());
            $stdout = json_decode($this -> getActualOutput(), true);

            $this -> assertEquals("Successful login!", $stdout["message"]);
            $this -> assertEquals($login_public_key, $stdout["public_key"]);
            $this -> assertLessThanOrEqual(time() + 3600, $stdout["expireAt"]);
            $this -> assertStringStartsWith("eyJ", $stdout["jwt"]);
            $this -> assertEquals(1, self::$conn -> query("SELECT COUNT(id) FROM users", PDO::FETCH_COLUMN, 0) -> fetch());
            $this -> assertEquals(self::$user -> getID(), self::$conn -> query("SELECT id FROM users", PDO::FETCH_COLUMN, 0) -> fetch());

            return $stdout;
        }

        /**
         * @depends testUserValidationWithNameGiven
         * @depends testUserValidationWithEmailGiven
         */
        public function testUserAuthentication(array $data_from_name_login, array $data_from_email_login)
        {   
            $expected_decoded_jwt = 
            [
                "iss" => "Bookshelf API",
                "iat" => $data_from_name_login["expireAt"] - 3600,
                "exp" => $data_from_name_login["expireAt"],
                "data" => ["user_id" => self::$user -> getID()]

            ];
            $decoded_jwt_name_login = (array) self::$user -> authorize($data_from_name_login["jwt"], $data_from_name_login["public_key"]);
            $decoded_jwt_name_login["data"] = (array) $decoded_jwt_name_login["data"];
            $this -> assertEquals($expected_decoded_jwt["iss"], $decoded_jwt_name_login["iss"]);
            $this -> assertGreaterThanOrEqual($expected_decoded_jwt["iat"], $decoded_jwt_name_login["iat"]);
            $this -> assertGreaterThanOrEqual($expected_decoded_jwt["exp"], $decoded_jwt_name_login["exp"]);
            $this -> assertEquals($expected_decoded_jwt["data"], $decoded_jwt_name_login["data"]);

            $decoded_jwt_email_login = (array) self::$user -> authorize($data_from_email_login["jwt"], $data_from_email_login["public_key"]);
            $decoded_jwt_email_login["data"] = (array) $decoded_jwt_email_login["data"];
            $this -> assertEquals($expected_decoded_jwt["iss"], $decoded_jwt_email_login["iss"]);
            $this -> assertGreaterThanOrEqual($expected_decoded_jwt["iat"], $decoded_jwt_email_login["iat"]);
            $this -> assertGreaterThanOrEqual($expected_decoded_jwt["exp"], $decoded_jwt_email_login["exp"]);
            $this -> assertEquals($expected_decoded_jwt["data"], $decoded_jwt_email_login["data"]);
        }

        public function testAccessDeniedWithInvalidName() 
        {
            $login_private_key = openssl_pkey_new(["digest_alg" => "sha256", "private_key_bits" => 4096]);
            $login_public_key = openssl_pkey_get_details($login_private_key)["key"];
            self::$user -> validate(self::$password, $login_private_key, $login_public_key, "Bob");
            $this -> expectOutputString(json_encode([ "message" => "Login failed!", "error" => "User not found"]));
        }

        public function testAccessDeniedWithInvalidEmail() 
        {
            $login_private_key = openssl_pkey_new(["digest_alg" => "sha256", "private_key_bits" => 4096]);
            $login_public_key = openssl_pkey_get_details($login_private_key)["key"];
            self::$user -> validate(self::$password, $login_private_key, $login_public_key, "bob@mail.com");
            $this -> expectOutputString(json_encode([ "message" => "Login failed!", "error" => "User not found"]));
        }

        public function testAccessDeniedWithInvalidPassword() 
        {
            $login_private_key = openssl_pkey_new(["digest_alg" => "sha256", "private_key_bits" => 4096]);
            $login_public_key = openssl_pkey_get_details($login_private_key)["key"];
            self::$user -> validate("5678", $login_private_key, $login_public_key, self::$name);
            $this -> expectOutputString(json_encode([ "message" => "Login failed!", "error" => "Incorrect password"]));
        }

        /**
         * @depends testUserRegistration
         */
        public function testUserDeletion(int $id)
        {
            self::$user -> unsubscribe($id);
            $this -> assertEquals(0, self::$conn -> query("SELECT COUNT(id) FROM users", PDO::FETCH_COLUMN, 0) -> fetch());  

            $query = "CALL sys.table_exists('phpunit', 'user_{$id}_books', @exists); SELECT @exists;";
            $this -> assertEquals("", self::$conn -> query($query, PDO::FETCH_COLUMN, 0) -> fetch());
        }
    }
?>