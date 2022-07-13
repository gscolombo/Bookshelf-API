<?php 
    namespace Models;
    use PDO, PDOStatement;

    class Author extends Database 
    {
        private array $res;
        
        public function set_response(array $data){
            $this -> res = $data;
        }
        
        private function send_single_res($author, string $message) {
            if ($author && is_array($author)) {
                $this -> res = $author;
            } else {
                $this -> res = ["message" => $message];
            }
            echo json_encode($this -> res);
        }
        
        private function send_list_res($authors, string $message) {
            if ($authors -> rowCount() > 0 && $authors instanceof PDOStatement) {
                while ($author = $authors -> fetch()) {
                    $this -> res["authors"][] = $author;
                }
            } else {
                $this -> res = ["message" => $message];
            }
            echo json_encode($this -> res);
        }

        public function list() {
            $query = "SELECT * FROM authors";
            $authors = $this -> connection -> query($query, PDO::FETCH_ASSOC);
            $this -> send_list_res($authors, "No authors were found");
        }

        public function get_by_id(int $id) {
            $query = "SELECT * FROM authors WHERE id = {$id}";
            $author = $this -> connection -> query($query, PDO::FETCH_ASSOC) -> fetch();
            $this -> send_single_res($author, "Author #{$id} not found");
        }

        public function search(Array $values, string $key) {
            $conditions = "{$key} = ";
            if (count($values) > 1) {
                foreach($values as $value) {
                    end($values) === $value ?
                    $conditions .= "\"{$value}\"" :
                    $conditions .= "\"{$value}\"" . " OR {$key} = ";
                }
            } else {
                $values[0] = "\"{$values[0]}\"";
                $conditions .= $values[0];
            }

            $query = "SELECT * FROM authors WHERE {$conditions}";
            $authors = $this -> connection -> query($query, PDO::FETCH_ASSOC);
            $this -> send_list_res($authors, "No authors that meet the search criteria were found");
        }

        public function add(string $fields, string $values) {      
            try {
                $query = "INSERT INTO authors ($fields) VALUES ($values);";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Author added successfully!"]);
                return intval($this -> connection -> lastInsertId());
            } catch(PDOException $err) {
                echo json_encode(["message" => "Author couldn't be added.", "error" => $err -> getMessage()]);
            } 
        }

        public function update(string $changes, int $id) {
            try {
                $query = "UPDATE authors SET {$changes} WHERE id = {$id}";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Author #{$id} updated successfully!"]);
            } catch(PDOException $err) {
                echo json_encode(["message" => "Author couldn't be updated.", "error" => $err -> getMessage()]);
            } 
        }

        public function delete(int $id) {
            try {
                $query = "DELETE FROM authors WHERE id = {$id}";
                $this -> connection -> exec($query);
                echo json_encode(["message" => "Author #{$id} deleted successfully!"]);
            } catch(PDOException $err) {
                echo json_encode(["message" => "Author couldn't be deleted", "error" => $err -> getMessage()]);
            }
        }
    }
?>