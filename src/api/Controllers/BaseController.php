<?php
    namespace Controllers;
    use Controllers\{BooksController, AuthorsController, UsersController};
    use Models\{Book, Author, User};

    class BaseController {
        protected string $method, $client_token, $public_key;
        protected array $uri, $params, $query, $post_data;
        public object $resource;

        protected function __construct(string $method, array $uri, $query, array $post_data, string $client_token, string $public_key) {            
            $this -> method = $method;
            $this -> uri = $uri;
            $this -> post_data = $post_data;
            $this -> params = array_slice($this -> uri, 3);
            $this -> client_token = $client_token;
            $this -> public_key = $public_key;
            $this -> set_resource($uri[2]);
            if (!is_null($query)) {
                $this -> set_query($query);
            }
        }

        protected function return_res(){
            switch($this -> method) {
                case "GET":
                    $this -> get();
                    break;
                case "POST":
                    $this -> post();
                    break;
                case "PUT":
                    $this -> put();
                    break;
                case "DELETE":
                    $this -> delete();
                    break;
            }
        }

        public static function set_controller($resource_name, $auth, ...$args) {
            switch($resource_name) {
                case "books":
                    if ($auth) {
                        $books = new BooksController(...$args);
                        $id = User::authorize($books -> client_token, $books -> public_key) -> data -> user_id;
                        $books -> resource -> set_table_name("user_{$id}_books");
                        $books -> return_res();
                    } else {
                        http_response_code(401);
                        echo json_encode(["message" => "Access denied."]);
                    }
                    break;
                case "authors":
                    $authors = new AuthorsController(...$args);
                    $authors -> return_res();
                    break;
                case "users":
                    $users = new UsersController(...$args);
                    $users -> set_response();
                    break;
            }
        }

        private function set_resource(string $name) {
            switch($name) {
                case "books":
                    $this -> resource = new Book();
                    break;
                case "authors":
                    $this -> resource = new Author();
                    break;
                case "users":
                    $this -> resource = new User();
                    break;
            }
        }

        private function set_query(string $query) {
            $subqueries = explode("&", $query);
            $query_arr = [];

            foreach($subqueries as $pair) {
                $pair_parts = explode("=", $pair);
                $query_arr[] = [$pair_parts[0] => str_replace("+", " ", $pair_parts[1])];
            }

            $this -> query = $query_arr;
        }

        protected function get(){
            if (empty($this -> params)) {
                $this -> resource -> list();
            } else {
                if (is_numeric($this -> params[0])) {
                    $this -> resource -> get_by_id((int)$this -> params[0]);
                } else {
                    if ($this -> params[0] == "search") {
                    $this -> set_search($this -> query);
                    }
                }
            }
        }

        protected function post(){
            if (count($this -> params) === 0) {
                $fields = implode(", ", array_keys($this -> post_data));
                $values = array_values($this -> post_data);
                $values = array_map(function($value) {
                    if (!is_numeric($value)) {
                        $value = "\"{$value}\"";
                        return $value;
                    } else {
                        return $value;
                    }
                }, $values);
                
                $values = implode(", ", $values);
                $this -> resource -> add($fields, $values);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid post request. Only request body is necessary to add a resource"]);
            }
            
        }

        protected function put(){
            $set = "";
            foreach($this -> post_data as $field => $value) {
                $end = end($this -> post_data) === $value ? "" : ", ";
                $value = !is_numeric($value) ? "\"{$value}\"" : $value;
                $set .= $field . " = " . $value . $end;
            }

            if (count($this -> params) > 0 && is_numeric($this -> params[0])) {
                $id = (int)$this -> params[0];
                $this -> resource -> update($set, $id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid put request. A valid integer ID must be specified in the URL"]);
            }
        }

        protected function delete() {
            if (count($this -> params) > 0 && is_numeric($this -> params[0])) {
                $id = (int)$this -> params[0];
                $this -> resource -> delete($id);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid delete request. A valid integer ID must be specified in the URL"]);
            }
        }

    }
?>