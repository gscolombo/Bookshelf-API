<?php 
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: http://localhost:3000");

    $preflight = $_SERVER["REQUEST_METHOD"] === "OPTIONS";
    if ($preflight) {
        header("{$_SERVER["SERVER_PROTOCOL"]} 200 OK");
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-PubKey");
        header("Access-Control-Max-Age: 3600");
        die();
    }

    const ROOT_PATH = __DIR__ . "/../";

    require_once ROOT_PATH . "inc/config.php";
    require_once ROOT_PATH . "inc/autoload.php";
    require "./vendor/autoload.php";
?>