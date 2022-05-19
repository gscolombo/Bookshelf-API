<?php 
    namespace Models;
    require "./vendor/autoload.php";

    use PDO, PDOStatement, OpenSSLAsymmetricKey;
    use Firebase\JWT\{JWT, Key, SignatureInvalidException, ExpiredException};
    
    class User extends Database
    {
        private int $id;

        public function getID()
        {
            return $this -> id;
        }

        public function setJWT(int $id, OpenSSLAsymmetricKey $key) 
        {
            $this -> id = $id;
            $payload = [
                "iss" => "Bookshelf API",
                "iat" => time(),
                "exp" => time() + 3600,
                "data" => ["user_id" => $this -> id]
            ];
            
            $jwt = JWT::encode($payload, $key, 'RS256');
            $this -> token = $jwt;
            return ["jwt" => $jwt, "exp" => $payload["exp"]];
        }

        static function authorize(string $jwt, string $key) {
            try {
                $key = str_replace("\\n", "\n", $key);
                return JWT::decode($jwt, new Key($key, 'RS256'));
            } catch (SignatureInvalidException | ExpiredException | Exception $err) {
                http_response_code(401);
                echo json_encode(["message" => "Forbidden access to resource", "error" => $err -> getMessage()]);
                die();
            }
        }

        public function validate(string $password, $private_key, $public_key, string $user = "")
        {   
            $user_email = str_contains($user, "@");
            $sql = $user_email ? 
            "SELECT id, password FROM users WHERE email = :email" :
            "SELECT id, password FROM users WHERE name = :name";
            
            $stmt = $this -> connection -> prepare($sql);
            $user_email ? $stmt -> execute(["email" => $user]) : $stmt -> execute(["name" => $user]);

            $rowNumber = $stmt -> rowCount();
            if ($rowNumber > 0) {
                $row = $stmt -> fetch(PDO::FETCH_ASSOC);
                $id = $row["id"];
                $hash_password = $row["password"];
                if (password_verify($password, $hash_password)) {
                    $arr = $this -> setJWT($id, $private_key);
                    echo json_encode([
                        "message" => "Successful login!",
                        "jwt" => $arr["jwt"],
                        "public_key" => $public_key,
                        "expireAt" => $arr["exp"],
                    ]);
                } else {
                    echo json_encode([
                        "message" => "Login failed!",
                        "error" => "Incorrect password"
                    ]);
                }
            } else {
                echo json_encode([
                    "message" => "Login failed!",
                    "error" => "User not found"
                ]);
            }
        }

        public function subscribe(string $name, string $email, string $password, $private_key, $public_key)
        {   
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this -> connection -> prepare($sql);
            $stmt -> bindParam(":name", $name);
            $stmt -> bindParam(":email", $email);
            $stmt -> bindParam(":password", $hash_password);

            if ($stmt -> execute()) {
                $user_id = intval($this -> connection -> lastInsertId());
                $arr = $this -> setJWT($user_id, $private_key);
                $this -> createTable($user_id);
                echo json_encode([
                    "message" => "User registered successfully!", 
                    "jwt" => $arr["jwt"],
                    "public_key" => $public_key,
                    "expireAt" => $arr["exp"]
                ]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "User registration failed!"]);
            }
        }

        public function unsubscribe(int $id)
        {
            $query1 = "DELETE FROM users WHERE id = {$id}";
            $query2 = "DROP TABLE user_{$id}_books";
            try {
                $this -> connection -> exec($query1);
                $this -> connection -> exec($query2);
            } catch(PDOException $err) {
                echo json_encode(["message" => "Error deleting user ->" . $err -> getMessage()]);
            }
        }

        public function createTable(int $id) {
            $query = "CREATE TABLE IF NOT EXISTS user_{$id}_books (
                        id INT(255) AUTO_INCREMENT,
                        title VARCHAR(255) NOT NULL,
                        author INT(255) NOT NULL,
                        pages INT(255),
                        editor VARCHAR(255),
                        PRIMARY KEY(id),
                        FOREIGN KEY (author) REFERENCES authors(id)
                        );";
            try {
                $this -> connection -> exec($query);
            } catch(PDOException $err) {
                echo json_encode(["message" => "Error creating table ->" . $err -> getMessage()]);
            }
        }
    }
?>