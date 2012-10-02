<?php
namespace yCrawler;

spl_autoload_register(function($className){
    $path = str_replace(Array('_', '\\'), '/', $className);
    require_once $path . '.php';
});

Errors::init();