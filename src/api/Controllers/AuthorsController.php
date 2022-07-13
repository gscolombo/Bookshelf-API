<?php 
    namespace Controllers;

    class AuthorsController extends BaseController {
        public function __construct(...$args) {
            parent::__construct(...$args);
        }

        private function set_search(Array $query) {
            if (count($query) > 1) {
                http_response_code(400);
                $this -> res = ["message" => "Invalid query parameters. Search only for name or nationality"];
                echo json_encode($this -> res);
            } else {
                $key = key($query[0]);
                $query = [$key => $query[0][$key]];
                $query["values"] = explode("|", $query[key($query)]);

                $this -> resource -> search($query["values"], $key);
            }

        }
    }
?>