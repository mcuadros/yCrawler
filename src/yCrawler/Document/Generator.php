<?php

namespace yCrawler\Document;

use yCrawler\Document;
use yCrawler\Parser;

class Generator
{
    const URL_PATTERN = '~^https?://.+~m';

    protected $parser;
    protected $file;
    protected $urls = [];
    protected $startup = [];
    protected $urlPatterns = [self::URL_PATTERN];

    public function __construct(Parser $parser, $file)
    {
        $this->parser = $parser;
        $this->file = $file;
    }

    public function getDocuments()
    {
        $documents = [];
        $this->findUrls();
        foreach($this->urls as $url => $pass) {
            $documents[] = new Document($url, $this->parser);
        }

        return $documents;
    }

    public function setPatterns(array $patterns)
    {
        $this->urlPatterns = $patterns;
    }


    protected function findUrls()
    {
        $content = file_get_contents($this->file);
        foreach ($this->urlPatterns as $regexp) {
            if (preg_match_all($regexp, $content, $matches)) {
                $this->urls = array_flip($matches[0]);
            }
        }
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