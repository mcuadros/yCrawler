<?php
namespace yCrawler\Parser\Item\Modifiers;
final class DateTime {
    public static function strtotime() { 
        return function(array &$results) {
            foreach($results as &$result) {
                $result['value'] = strtotime($result['value']);
            }
        };
    }

    public static function diff($milliseconds = false) { 
        return function(array &$results) use ($milliseconds) {
            foreach($results as &$result) {
                if ( $milliseconds ) $seconds = (int)($result['value']/1000);
                else $seconds = (int)$result['value'];

                $result['value'] = time() + $seconds;
            }
        };
    }
}