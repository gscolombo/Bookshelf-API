<?php 
    namespace Models;
    use PDO, PDOStatement, PDOException;

    class Book extends Database 
    {
        private string $table_name;
        private array $res;

        public function set_table_name($name) {
            $this -> table_name = $name;
        }

        public function set_response(array $data){
            $this -> res = $data;
        }

        private function join_author($id) {
            $author_query = "SELECT * FROM authors WHERE id = {$id}";
            return $this -> connection -> query($author_query, PDO::FETCH_ASSOC) -> fetch();
        }

        private function send_single_res($book, string $message) {
            if ($book && is_array($book)) {
                $book["author"] = $this -> join_author($book["author"]);
                $this -> res = $book;
            } else {
                $this -> res = ["message" => $message];
            }
            echo json_encode($this -> res);
        }
        
        private function send_list_res($books, string $message) {
            if ($books -> rowCount() > 0 && $books instanceof PDOStatement) {
                while ($book = $books -> fetch()) {
                    $book["author"] = $this -> join_author($book["author"]);
                    $this -> res["books"][] = $book;
                }
            } else {
                $this -> res = ["message" => $message];
            }
            echo json_encode($this -> res);
        }

        public function list() {
            $query = "SELECT * FROM {$this -> table_name}";
            $books = $this -> connection -> query($query, PDO::FETCH_ASSOC);
            $this -> send_list_res($books, "No books were found");
        }

        public function get_by_id(int $id) {
            $query = "SELECT * FROM {$this -> table_name} WHERE id = {$id}";
            $book = $this -> connection -> query($query, PDO::FETCH_ASSOC) -> fetch();
            $this -> send_single_res($book, "Book #{$id} not found.");
        }

        public function searchByTitle(string $title) {
            $query = "SELECT * FROM {$this -> table_name} WHERE title = '{$title}'";
            $book = $this -> connection -> query($query, PDO::FETCH_ASSOC) -> fetch();
            $this -> send_single_res($book, "Book not found");
        }

        public function searchByAuthor(string $field, string $value) {
            $field = str_replace("author_", "", $field);
            $author_id_query = "SELECT id FROM authors WHERE {$field} = '{$value}'";
            $author_id = $this -> connection -> query($author_id_query, PDO::FETCH_COLUMN, 0) -> fetch();

            if ($author_id) {
                $books_query = "SELECT * FROM {$this -> table_name} WHERE author = {$author_id}";
                $books = $this -> connection -> query($books_query, PDO::FETCH_ASSOC);
                $this -> send_list_res($books, "No books from {$value} where found");
            } else {
                echo json_encode(["message" => "Authors that meet the criteria are unregistered in database"]);
            }
            
        }

        public function searchByPages(array $values) {
            $min = $values[0];
            $max = isset($values[1]) ? $values[1] : false;

            if ($max) {
                $query = "SELECT * FROM {$this -> table_name} WHERE pages >= ${min} AND pages <= {$max}";
            } else {
                $query = "SELECT * FROM {$this -> table_name} WHERE pages >= ${min}";
            }

            $books = $this -> connection -> query($query, PDO::FETCH_ASSOC);
            $this -> send_list_res($books, "No books that meet the criteria were found");
        }

        public function searchByEditor(string $editor) {
            $query = "SELECT * FROM {$this -> table_name} WHERE editor = '{$editor}'";
            $books = $this -> connection -> query($query, PDO::FETCH_ASSOC);
            $this -> send_list_res($books, "No books were found from {$editor}");
        }

        public function add(string $fields, string $values) {      
            try {
                $query = "INSERT INTO {$this -> table_name} ($fields) VALUES ($values);";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Book added successfully!"]);
                return intval($this -> connection -> lastInsertId());
            } catch(PDOException $err) {
                http_response_code(400);
                echo json_encode(["message" => "Book couldn't be added.", "error" => $err -> getMessage()]);
            } 
        }

        public function update(string $changes, int $id) {
            try {
                $query = "UPDATE {$this -> table_name} SET {$changes} WHERE id = {$id}";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Book #{$id} updated successfully!"]);
            } catch(PDOException $err) {
                http_response_code(400);
                echo json_encode(["message" => "Book couldn't be updated.", "error" => $err -> getMessage()]);
            } 
        }

        public function delete(int $id) {
            try {
                $query = "DELETE FROM {$this -> table_name} WHERE id = {$id}";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Book #{$id} deleted successfully!"]);
            } catch(PDOException $err) {
                http_response_code(400);
                echo json_encode(["message" => "Book couldn't be deleted", "error" => $err -> getMessage()]);
            }
        }
    }

?>