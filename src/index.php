<?php 
    require_once "./inc/bootstrap.php";
    use Controllers\BaseController;

    // Request parameters handling
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode("/", $uri);
    $resource_name = $uri[1];

    // Query parameters handling
    $query_str = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

    // Post data handling
    $data = json_decode(file_get_contents("php://input"), true);

    // Authorization token handling
    $request_headers = apache_request_headers();
    $auth = array_key_exists("Authorization", $request_headers) && 
            array_key_exists("X-PubKey", $request_headers) &&
            preg_match("/Bearer/", $request_headers["Authorization"]);

    $client_token = "";
    $public_key = "";
    if ($auth) {
        $client_token = str_replace("Bearer ", "", $request_headers["Authorization"]);
        $public_key = $request_headers["X-PubKey"];
    }

    var_dump($uri);
    $args = [$method, $uri, $query_str, $data, $client_token, $public_key];
    BaseController::set_controller($resource_name, $auth, ...$args);
?>