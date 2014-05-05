<?php

namespace yCrawler\Parser\Rule\Modifiers;

use yCrawler\Document;

final class HTML
{
    public static function markup()
    {
        return function (array &$results) {
            foreach ($results as &$result) {
                $result['value'] = $result['raw'];
            }

            return $results;
        };
    }

    public static function br2nl($tags = array('<br>','<br/>','<br />'))
    {
        return function (array &$results) use ($tags) {
            foreach ($results as &$result) {
                $result['value'] = strip_tags(str_ireplace($tags, PHP_EOL, $result['raw']));
            }

            return $results;
        };
    }

    public static function image()
    {
        return function (array &$results, \yCrawler\Document $document) {
            foreach ($results as &$result) {
                $img = $result['node']->getAttribute('src');
                if (!\yCrawler\Misc\URL::isImage($img)) {
                    $img = $result['node']->getAttribute('href');
                }
                if (!\yCrawler\Misc\URL::isImage($img)) {
                    $img = $result['node']->getAttribute('style');
                    $img = \yCrawler\Misc\URL::fromStyle($img);
                }

                if (!\yCrawler\Misc\URL::isImage($img)) {
                    break;
                }
                $result['value'] = \yCrawler\Misc\URL::absolutize($img, $document->getURL());
            }

            return $results;
        };
    }
}
