<?php 
    spl_autoload_register(function($class_name) {
        $path = str_replace("\\", DIRECTORY_SEPARATOR, ROOT_PATH . $class_name . ".php");
        if (file_exists($path)) {
            require_once $path;
        }
    });
?>