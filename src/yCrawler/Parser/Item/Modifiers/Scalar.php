<?php
namespace yCrawler\Parser\Item\Modifiers;
final class Scalar {
    const BOOLEAN_POSITIVE = true;
    const BOOLEAN_NEGATIVE = false;

    public static function boolean($sign = self::BOOLEAN_POSITIVE) { 
        return function(array &$results) use ($sign) {
            foreach($results as &$result) {
                if ( (boolean)$result['value'] ) $result['value'] = (boolean)$sign;
                else $result['value'] =! (boolean)$sign;
            }
        };
    }

    public static function int($regexp = '/[^0-9,.]/') {
        return function(array &$results) use ($regexp) {
            foreach($results as &$result) {
                $result['value'] = (int)preg_replace($regexp, '', $result['value']);
            }
        };
    }

    public static function float($regexp = '/[^0-9,.]/', $decimalSep = ',') {
        return function(array &$results) use ($regexp, $decimalSep) {
            foreach($results as &$result) {
                $result['value'] = (float)str_replace(
                    $decimalSep,
                    '.', 
                    preg_replace($regexp, '', $result['value'])
                );
            }
        };
    }

    public static function join($glue = '') {
        return function(array &$results) use ($glue) {
            $output = array();
            foreach($results as &$result) $output[] = $result['value'];

            $results = Array(
                Array('value' => implode($glue, $output))
            );
        };

        if ( !$value ) return false;
        $output=Array();
        foreach($value as &$data) $output[] = $data['value'];
        $value = Array(Array(
            'value' => implode($glue, $output)
        ));
        return true;
    }
}