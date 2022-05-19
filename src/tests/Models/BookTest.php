<?php 
    use Models\Book;
    use PHPUnit\Framework\TestCase;

    final class BookTest extends TestCase
    {    
        private static Book $book;
        private static array $list_result, $list_with_single_value_result, $single_result;

        public static function setUpBeforeClass() : void
        {
            self::$book = new Book();
            self::$book -> set_table_name("books");
            self::$list_result = [
                "books" => [
                    [
                        "id" => "3",
                        "title" => "book_1",
                        "author" => [
                            "id" => "1",
                            "name" => "author_1",
                            "nationality" => "brittish"
                        ],
                        "pages" => "213",
                        "editor" => "editor_1"
                    ],
                    [
                        "id" => "4",
                        "title" => "book_2",
                        "author" => [
                            "id" => "1",
                            "name" => "author_1",
                            "nationality" => "brittish"
                        ],
                        "pages" => "405",
                        "editor" => "editor_1"
                    ]
                ]
            ];

            self::$list_with_single_value_result = [
                "books" => [
                    [
                        "id" => "3",
                        "title" => "book_1",
                        "author" => [
                            "id" => "1",
                            "name" => "author_1",
                            "nationality" => "brittish"
                        ],
                        "pages" => "213",
                        "editor" => "editor_1"
                    ],
                ]
            ];

            self::$single_result = [
                "id" => "3",
                "title" => "book_1",
                "author" => [
                    "id" => "1",
                    "name" => "author_1",
                    "nationality" => "brittish"
                ],
                "pages" => "213",
                "editor" => "editor_1"
            ];
        }

        public function tearDown() : void
        {
            self::$book -> set_response([]);
        }

        public function testBookListing() {
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            
            self::$book -> list();
        }

        public function testGetBookById() {
            $expected = json_encode(self::$single_result);
            $this -> expectOutputString($expected);
            
            self::$book -> get_by_id(3);
        }

        public function testSearchBookByTitle() {
            $expected = json_encode(self::$single_result);
            $this -> expectOutputString($expected);
            
            self::$book -> searchByTitle("book_1");
        }

        public function testSearchBooksByAuthor() {
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            
            self::$book -> searchByAuthor("name", "author_1");
        }

        public function testSearchBooksByPagesWithSingleValue() {
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            
            self::$book -> searchByPages([200]);
        }

        public function testSearchBooksByPagesWithMaxAndMinValue() {
            $expected = json_encode(self::$list_with_single_value_result);
            $this -> expectOutputString($expected);
            
            self::$book -> searchByPages([200, 300]);
        }

        public function testSearchBooksByEditor() {
            $expected = json_encode(self::$list_result);
            $this -> expectOutputString($expected);
            
            self::$book -> searchByEditor("editor_1");
        }

        public function testAddANewBook(){
            $expected = json_encode(["message" => "Book added successfully!"]);
            $this -> expectOutputString($expected);
            
            $conn = self::$book -> get_connection();
            $id = self::$book -> add("title, author", "'book_3', 1");

            $count = $conn -> query("SELECT COUNT(id) FROM books", PDO::FETCH_COLUMN, 0) -> fetch();
            $this -> assertEquals(3,  $count);

            return $id;
        }

         /**
         * @depends testAddANewBook
         */
        public function testUpdateABook(int $id){
            $expected = json_encode(["message" => "Book #{$id} updated successfully!"]);
            $this -> expectOutputString($expected);
            
            $conn = self::$book -> get_connection();
            self::$book -> update("title = \"book_4\", author = 2, pages = 100", $id);

            $updated_book = $conn -> query("SELECT * FROM books WHERE id = {$id}", PDO::FETCH_ASSOC) -> fetch();
            $expected_update_book = [
                "id" => $id,
                "title" => "book_4",
                "author" => 2,
                "pages" => 100,
                "editor" => null
            ];

            $this -> assertEquals($expected_update_book,  $updated_book);
        }

        /**
         * @depends testAddANewBook
         */
        public function testDeleteABook(int $id){
            $expected = json_encode(["message" => "Book #{$id} deleted successfully!"]);
            $this -> expectOutputString($expected);
            
            $conn = self::$book -> get_connection();
            self::$book -> delete($id);
            $count = $conn -> query("SELECT COUNT(id) FROM books", PDO::FETCH_COLUMN, 0) -> fetch();
            $this -> assertEquals(2,  $count);
        }
    }
?>