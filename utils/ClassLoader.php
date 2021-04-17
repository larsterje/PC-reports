<?php

spl_autoload_register(function ($className) {
    if (strstr($className, '\\') != false) {
        $parts = explode('\\', $className);
        
        $path = implode('/', array_splice($parts, 1));

        require_once dirname(__FILE__) . '/../' . $path . '.php';
    }
});