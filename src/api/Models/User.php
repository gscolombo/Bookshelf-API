<?php 
    namespace Models;
    
    use PDO, PDOStatement, PDOException, OpenSSLAsymmetricKey;
    use Firebase\JWT\{JWT, Key, SignatureInvalidException, ExpiredException};
    
    class User extends Database
    {
        private int $id;

        public function getID()
        {
            return $this -> id;
        }

        public function setJWT(int $id, string $name, OpenSSLAsymmetricKey $key) 
        {
            $this -> id = $id;
            $payload = [
                "iss" => "Bookshelf API",
                "iat" => time(),
                "exp" => time() + 3600,
                "data" => ["user_id" => $this -> id, "user_name" => $name]
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
            "SELECT id, password, name FROM users WHERE email = :email" :
            "SELECT id, password, name FROM users WHERE name = :name";
            
            $stmt = $this -> connection -> prepare($sql);
            $user_email ? $stmt -> execute(["email" => $user]) : $stmt -> execute(["name" => $user]);

            $rowNumber = $stmt -> rowCount();
            if ($rowNumber > 0) {
                $row = $stmt -> fetch(PDO::FETCH_ASSOC);
                $id = $row["id"];
                $hash_password = $row["password"];
                $user_name = $row["name"];
                if (password_verify($password, $hash_password)) {
                    $arr = $this -> setJWT($id, $user_name, $private_key);
                    echo json_encode([
                        "message" => "Successful login!",
                        "userName" => $user_name,
                        "jwt" => $arr["jwt"],
                        "publicKey" => $public_key,
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
            $email_check_sql = "SELECT COUNT(id) FROM users WHERE email = :email";
            $email_check_stmt = $this -> connection -> prepare($email_check_sql);
            $email_check_stmt -> execute(["email" => $email]);
            $has_email = $email_check_stmt -> fetchColumn() > 0;

            if ($has_email) {
                http_response_code(400);
                echo json_encode(["message" => "User already registered"]);
                die();
            }

            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this -> connection -> prepare($sql);
            $stmt -> bindParam(":name", $name);
            $stmt -> bindParam(":email", $email);
            $stmt -> bindParam(":password", $hash_password);

            if ($stmt -> execute()) {
                $user_id = intval($this -> connection -> lastInsertId());
                $arr = $this -> setJWT($user_id, $name, $private_key);
                $this -> createTables($user_id);
                echo json_encode([
                    "message" => "User registered successfully!", 
                    "userName" => $name,
                    "jwt" => $arr["jwt"],
                    "publicKey" => $public_key,
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

        public function createTables(int $id) {
            $user_books_table_query = "CREATE TABLE IF NOT EXISTS user_{$id}_books (
                        id INT(255) AUTO_INCREMENT,
                        title VARCHAR(255) NOT NULL,
                        author INT(255) NOT NULL,
                        pages INT(255),
                        editor VARCHAR(255),
                        shelf INT(255),
                        PRIMARY KEY(id),
                        FOREIGN KEY (author) REFERENCES authors(id),
                        FOREIGN KEY (shelf) REFERENCES user_{$id}_shelves(id)
                        );";

            $user_shelves_table_query = "CREATE TABLE IF NOT EXISTS user_{$id}_shelves (
                        id INT(255) AUTO_INCREMENT,
                        name VARCHAR(50) NOT NULL,
                        PRIMARY KEY(id)
                        );";
            try {
                $this -> connection -> exec($user_shelves_table_query);
                $this -> connection -> exec($user_books_table_query);
            } catch(PDOException $err) {
                http_response_code(500);
                $query = "DELETE FROM users WHERE id = ${id}";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Error creating table ->" . $err -> getMessage()]);
                die();
            }
        }

        public function retrieveUserFromEmail(string $email) {
            $sql = "SELECT id, name FROM users WHERE email = :email";
            $stmt = $this -> connection -> prepare($sql);
            $stmt -> bindParam(":email", $email);

            if ($stmt -> execute()) {
                if ($stmt -> rowCount() > 0) {
                    return $stmt -> fetch(PDO::FETCH_ASSOC);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "User not found"]);
                    die();
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Error finding user"]);
            }
        }

        public function savePassword(string $password, int $id) {
            $check_id_sql = "SELECT id FROM users WHERE id = :id";
            $check_id_stmt = $this -> connection -> prepare($check_id_sql);
            
            $check_id_stmt -> execute(["id" => $id]);
            $user_exists = $check_id_stmt -> rowCount() > 0;

            if ($user_exists) {
                $sql = "UPDATE users SET password = :password WHERE id = :id";
                $stmt = $this -> connection -> prepare($sql);
    
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt -> bindParam(":id", $id);
                $stmt -> bindParam(":password", $hash_password);
    
                if ($stmt -> execute()) {
                    echo json_encode(["message" => "Password updated sucessfully"]);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Password could not be updated"]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["message" => "User not found"]);
            }
        }
    }
?>