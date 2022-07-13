<?php 
    namespace Controllers;

    class BooksController extends BaseController 
    {   
        public function __construct(...$args){
            parent::__construct(...$args);
        }

        private function set_search(Array $query){
            $fields = [];
            $values = [];
            foreach($query as $pair) {
                $fields[] = key($pair);
                $values[] = $pair[key($pair)];
            }
            
            switch($fields[0]) {
                case "author_name":
                case "author_nationality": 
                    $this -> resource -> searchByAuthor($fields[0], $values[0]); break;
                case "title": $this -> resource -> searchByTitle($values[0]); break;
                case "pages": $this -> resource -> searchByPages($fields, $values); break;
                case "editor": $this -> resource -> searchByEditor($fields, $values); break;
            }
        }
    }
?>