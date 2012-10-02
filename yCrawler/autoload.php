<?php
namespace yCrawler;

spl_autoload_register(function($className){
    $tmp = explode('\\', $className);
    $tmp[count($tmp)-1] = str_replace('_', '/', $tmp[count($tmp)-1]);

    require_once implode('/', $tmp) . '.php';
});

Errors::init();