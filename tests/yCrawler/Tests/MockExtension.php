<?php
namespace StringForge\Tests;
use StringForge\Extension;
use StringForge\StringForge;

class MockExtension implements Extension {
    public function register(StringForge $forge) {
        $forge->register('exampleFunction', [$this, 'exampleFunction']);
        $forge->register('exampleFunctionWithArgs', [$this, 'exampleFunctionWithArgs']);

    }

    public function exampleFunction($string) {
        return strtoupper($string);
    }

    public function exampleFunctionWithArgs($string, $one, $two) {
        return $string . '-' . $one . '-' . $two;
    }
}