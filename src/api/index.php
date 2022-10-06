<?php 
    require_once "./inc/bootstrap.php";
    use Controllers\BaseController;

    // Request parameters handling
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode("/", $uri);
    $resource_name = $uri[2];
    
    // Query parameters handling
    $query_str = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)?: "";

    // Post data handling
    $data = [];
    if ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
    }

    $auth = array_key_exists("jwt", $_COOKIE) && array_key_exists("public_key", $_COOKIE);
    $client_token = "";
    $public_key = "";

    if ($auth) {
        $client_token = $_COOKIE["jwt"];
        $public_key = $_COOKIE["public_key"];
    }

    $args = [$method, $uri, $query_str, $data, $client_token, $public_key];
    BaseController::set_controller($resource_name, $auth, ...$args);
?>