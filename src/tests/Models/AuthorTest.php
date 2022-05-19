<?php 
    use Models\Author;
    use PHPUnit\Framework\TestCase;

    final class AuthorTest extends TestCase
    {    
        private static Author $author;
        private static array $list_result, $list_with_single_value_result, $single_result;

        public static function setUpBeforeClass() : void
        {
            self::$author = new Author();
            self::$list_result = [
                "authors" => [
                    [
                        "id" => "1",
                        "name" => "author_1",
                        "nationality" => "brittish"
                    ],
                    [
                        "id" => "2",
                        "name" => "author_2",
                        "nationality" => "brazilian"
                    ]
                ]
            ];

            self::$list_with_single_value_result = [
                "authors" => [
                    [
                        "id" => "1",
                        "name" => "author_1",
                        "nationality" => "brittish"
                    ],
                ]
            ];

            self::$single_result = [
                "id" => "1",
                "name" => "author_1",
                "nationality" => "brittish"
            ];
        }

        public function tearDown() : void
        {
            self::$author -> set_response([]);
        }

        public function testAuthorListing() {
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            $author = new Author();
            $author -> list();
        }

        public function testGetAuthorById() {
            $expected = json_encode(self::$single_result);
            $this -> expectOutputString($expected);
            $author = new Author();
            $author -> get_by_id(1);
        }

        public function testSearchAuthorBySingleNationality(){
            $expected = json_encode(self::$list_with_single_value_result);
            $this -> expectOutputString($expected);
            $author = new Author();
            $author -> search(["brittish"], "nationality");
        }

        public function testSearchAuthorBySingleName(){
            $expected = json_encode(self::$list_with_single_value_result);
            $this -> expectOutputString($expected);
            $author = new Author();
            $author -> search(["author_1"], "name");
        }

        public function testSearchAuthorsByMultipleNames(){
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            $author = new Author();
            $author -> search(["author_1", "author_2"], "name");
        }

        public function testSearchAuthorsByMultipleNationalities(){
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            $author = new Author();
            $author -> search(["brittish", "brazilian"], "nationality");
        }

        public function testAddANewAuthor(){
            $expected = json_encode(["message" => "Author added successfully!"]);
            $this -> expectOutputString($expected);
            $author = new Author();
            $conn = $author -> get_connection();
            $id = $author -> add("name, nationality", "'author_3', 'spanish'");

            $count = $conn -> query("SELECT COUNT(id) FROM authors", PDO::FETCH_COLUMN, 0) -> fetch();
            $this -> assertEquals(3,  $count);

            return $id;
        }

         /**
         * @depends testAddANewAuthor
         */
        public function testUpdateAnAuthor(int $id){
            $expected = json_encode(["message" => "Author #{$id} updated successfully!"]);
            $this -> expectOutputString($expected);
            $author = new Author();
            $conn = $author -> get_connection();
            $author -> update("name = \"author_4\", nationality = \"russian\"", $id);

            $updated_author = $conn -> query("SELECT * FROM authors WHERE id = {$id}", PDO::FETCH_ASSOC) -> fetch();
            $expected_update_author = [
                "id" => $id,
                "name" => "author_4",
                "nationality" => "russian",
            ];

            $this -> assertEquals($expected_update_author,  $updated_author);
        }

        /**
         * @depends testAddANewAuthor
         */
        public function testDeleteAnAuthor(int $id){
            $expected = json_encode(["message" => "Author #{$id} deleted successfully!"]);
            $this -> expectOutputString($expected);
            $author = new Author();
            $conn = $author -> get_connection();
            $author -> delete($id);
            $count = $conn -> query("SELECT COUNT(id) FROM authors", PDO::FETCH_COLUMN, 0) -> fetch();
            $this -> assertEquals(2,  $count);
        }
    }
?>