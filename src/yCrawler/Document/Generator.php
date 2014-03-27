<?php

namespace yCrawler\Document;

use yCrawler\Document;
use yCrawler\Parser;

class Generator
{
    const URL_PATTERN = '~https?://(?![^" ]*(?:jpg|png|gif))[^" ]+~m';

    protected $startup = [];
    protected $urlPatterns = [self::URL_PATTERN];

    public function getDocuments($file, Parser $parser)
    {
        $documents = [];
        $urls = $this->findUrls($file);
        foreach($urls as $url => $pass) {
            $documents[] = new Document($url, $parser);
        }

        return $documents;
    }

    public function setPatterns(array $patterns)
    {
        $this->urlPatterns = $patterns;
    }


    protected function findUrls($file)
    {
        $urls = [];
        $content = file_get_contents($file);
        foreach ($this->urlPatterns as $regexp) {
            if (preg_match_all($regexp, $content, $matches)) {
                $urls = array_flip($matches[0]);
            }
        }

        return $urls;
    }

    private function createDefaultURLPatterns()
    {
        $tmp = [];
        foreach ($this->startup as $url) {
            $tmp[] = $this->createURLPatternBasedOnURL($url);
        }

        $this->urlPatterns = array_unique($tmp);
    }

    private function createURLPatternBasedOnURL($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        $domainWithEscapedDots = str_replace('.', '\.', $domain);

        return sprintf(self::URL_PATTERN_BASED_ON_DOMAIN, $domainWithEscapedDots);
    }
}