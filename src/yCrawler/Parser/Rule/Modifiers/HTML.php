<?php

namespace yCrawler\Parser\Rule\Modifiers;

use yCrawler\Misc\URL;
use yCrawler\Document;

final class HTML
{
    public static function markup()
    {
        return function(array &$results) {
            foreach ($results as &$result) {
                $result['value'] = $result['dom']->saveXML($result['node']);
            }
        };
    }

    public static function br2nl($tags = array('<br>','<br/>','<br />'))
    {
        return function(array &$results) use ($tags) {
            foreach ($results as &$result) {
                $result['value'] = strip_tags(
                    str_ireplace($tags, PHP_EOL, $result['dom']->saveXML($result['node']))
                );
            }
        };
    }

    public static function image()
    {
        return function(array &$results, Document $document) {
            foreach ($results as &$result) {
                $img = $result['node']->getAttribute('src');
                if (!URL::isImage($img)) $img = $result['node']->getAttribute('href');
                if (!URL::isImage($img)) {
                    $img = $result['node']->getAttribute('style');
                    $img = URL::fromStyle($img);
                }

                if (!URL::isImage($img)) break;
                $result['value'] = URL::absolutize($img, $document->getURL());
            }
        };
    }
}
