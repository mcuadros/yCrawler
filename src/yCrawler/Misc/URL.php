<?php

namespace yCrawler\Misc;

class URL
{
    public static $imageExtensions = Array('jpg','jpeg','png','gif');

    public static function isImage($filename)
    {
        if (!$filename) { return false; }

        $extensions=implode('|',self::$imageExtensions);
        //casa con imágenes seguidas de "?" o "#" o "&"
        return preg_match('~\.(?:'.$extensions.')(?:$|\?|&|\#)~i',$filename) == 1;
    }

    public static function fromStyle($style)
    {
        if ( preg_match('~url\s*\([\'\"\s]*([^\'\"]+?)[\'\"\s]*\)~', $style, $url) ) {
            return $url[1];
        }

        return false;
    }

    public static function absolutize($url, $origin)
    {
        //Clean the fragment in the url
        if ( $fragment = parse_url($url,PHP_URL_FRAGMENT) ) {
            $url = str_replace('#' . $fragment, '', $url);
        }

        if ( strlen($url) == 0 ) return false;

        if ( substr($url,0,4) == 'http' ) {
        //si es 'HTTP' ...
            return $url;
        } elseif (substr($url,0,2) == '//' ) {
        //si es '//'...
            return parse_url($origin, PHP_URL_SCHEME) . ':' . $url;
        } else {
        //si se necesita resolver la ruta...

            //TODO: Controlar todo tipo de eventos
            if (stripos($url, ' ') !== false ||
                stripos($url, '@') !== false ||
                stripos($url, 'mailto:') !== false ||
                stripos($url, 'javascript:') !== false
            ) { return false; }

            //No anchor index
            if ($url[0] == '#') { return false; }

            //Path relativo con ..
            if ( substr($url,0,2) == '..' ) {
                $tmp = explode('/',$origin);
                foreach ( explode('/', $url) as $dir) {
                    if ($dir == '..') {
                        unset($tmp[count($tmp)-1]);
                    } else {
                        $output[] = $dir;
                    }
                }

                return implode('/', $tmp) . '/' . implode('/', $output);

            //Path absoluto
            } elseif ($url[0] == '/') {
                return parse_url($origin, PHP_URL_SCHEME) .'://' . parse_url($origin, PHP_URL_HOST) . $url;
            //Path relativo
            } else {
                return parse_url($origin, PHP_URL_SCHEME) .'://' . parse_url($origin, PHP_URL_HOST) . '/' . $url;
            }
        }
    }

    public static function validate($url)
    {
        //TODO: code for 'validate($url)'
        if (!$url) return false;
        return true;
    }

    public static function fix($url)
    {
        if ( !$parts = parse_url($url) ) return false;

        $return = '';
        if ( array_key_exists('scheme', $parts) ) $return .= $parts['scheme'] . '://';
        if ( array_key_exists('user', $parts) && array_key_exists('pass', $parts) ) $return .= $parts['user'] . ':' . $parts['pass'] . '@';
        if ( array_key_exists('host', $parts) ) $return .= $parts['host'];
        if ( array_key_exists('port', $parts) ) $return .= ':' . $parts['port'];
        if ( array_key_exists('path', $parts) ) $return .= join('/', array_map('rawurlencode', explode('/', $parts['path'])));
        if ( array_key_exists('query', $parts) ) $return .= '?' . $parts['query'];
        if ( array_key_exists('fragment', $parts) ) $return .= '#' . $parts['fragment'];
        return $return;
    }

}
