<?php
namespace yCrawler;
use yCrawler\Misc\URL;
use ForceUTF8\Encoding;

class Request extends Base {
    const STATUS_NONE = 0;
    const STATUS_FAILED = 1;
    const STATUS_RETRY = 2;
    const STATUS_DONE = 3;
    const STATUS_CACHED = 4;
    
    private $id;
    private $url;
    private $host;
    private $scheme;
    private $post;

    private $responseCode;
    private $responseHeaders;
    private $response;

    private $status = self::STATUS_NONE;
    private $retries = 0;
    private $curl;

    private $cache;
    private $cookie;
    
    public function __construct($crawler, $url = null) {
        parent::__construct($crawler);
        $this->cache = $this->config('request_cache');
        $this->cookie = $this->config('cookie');
        if ( $url ) $this->setUrl($url);
    }

    public function setUrl($url) {
        if ( !$this->_cleanUrl = URL::fix($url) ) {
            throw new Exception('Unable to set URL "'.$originalUrl.'" non-valid url.');
        }

        $this->url = $url;
        $this->id = md5($this->url);
        $this->host = parse_url(strtolower($this->url), PHP_URL_HOST);
        $this->scheme = parse_url(strtolower($this->url), PHP_URL_SCHEME);        
        return $this->url;
    }

    public function setCache($ttl) {
        return $this->cache = (int)$ttl;
    }

    public function setPost($post) {
        return $this->post = http_build_query($post);
    }

    public function setCookie($cookie) {
        return $this->cookie = $cookie;
    }

    public function getId() { return $this->id; }
    public function getUrl() { return $this->url; }
    public function getPost() { return $this->post; }
    public function getHost() { return $this->host; }
    public function getScheme() { return $this->scheme; }
    public function getResponseCode() { return $this->responseCode; }
    public function getResponseHeaders() { return $this->responseHeaders; }
    public function &getResponse() { return $this->response; }
    public function getStatus() { return $this->status; }
    public function getCache() { return $this->cache; }

    public function newRetry() {
        if ( ++$this->retries > $this->config('maxretries') ) return false;
        $this->setStatus(self::STATUS_NONE);
        return $this->retries;
    }

    public function call() {
        if ( $cache = $this->getFromCache() ) return $cache;

        $start = microtime(true);

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->config('user_agent'));
        curl_setopt($this->curl, CURLOPT_URL, $this->_cleanUrl);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->config('connection_timeout'));
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->config('max_execution_time'));
        
        if ( $this->config('headers') ) {
            curl_setopt($this->curl, CURLOPT_HEADER, true);
            curl_setopt($this->curl, CURLOPT_FAILONERROR, false);
        } else {
            curl_setopt($this->curl, CURLOPT_FAILONERROR, true);
        }

        if  ( $this->cookie ) {
            curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie );
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);     
        }
        
        if ( $this->config('interface') ) {
            curl_setopt($this->curl, CURLOPT_INTERFACE,$this->config('interface') );  
        }

        if ( $this->config('ssl_certificate') ) {
            curl_setopt($this->curl, CURLOPT_CAINFO, $this->config('ssl_certificate'));
        }
        
        if ( $this->post ) { 
            curl_setopt ($this->curl, CURLOPT_POSTFIELDS, $this->post);
            curl_setopt ($this->curl, CURLOPT_POST, 1);
        }

        $response = curl_exec($this->curl); 
        if ( $this->config('headers') ) {
            $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
            $this->setResponseHeaders(substr($response, 0, $headerSize));
            $this->setResponse(substr($response, $headerSize));
        } else {
            $this->setResponse($response);  
        }

        $elapsed = microtime(true) - $start;
        $this->data('set', 'elapsed', $elapsed);

        $errorNo = curl_errno($this->curl);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if( !$errorNo && $httpCode == 200 ) {
            $this->setResponseCode(curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
            return true;
        } else {
            if ( $httpCode ) {
                $this->setResponseCode($httpCode);
                return false;
            } else {
                $this->setResponseCode($errorNo);
                return false;
            }
        }
    }

    private function setResponse($response, $noCache = false) {
        if ( !$noCache ) $this->setToCache($response);
        if ( !$response ) $this->response = false;

        if ( $this->config('utf8') ) { 
            $this->response = Encoding::toUTF8($response);
        } else {
            $this->response = $response;
        }

        return $response;
    }

    private function setResponseHeaders($headers) {
        return $this->responseHeaders = $headers;
    }

    private function setResponseCode($code) {
        //Of course 200 HTTP code, is OK
        if ( $code == 200 ) $this->setStatus(self::STATUS_DONE);
        //Under 100, is a CURL error, maybe a temporal error, we will try again.
        else if ( $code < 100 ) $this->setStatus(self::STATUS_RETRY);
        //Any other code 4xx or 5xx will be marked as failed
        else $this->setStatus(self::STATUS_FAILED);

        return $this->responseCode = $code;
    }

    private function setStatus($status = self::STATUS_DONE) {
        return $this->status = $status;
    }

    private function setToCache($response) {
        if ( 0 != $ttl = (int)$this->config('request_cache') ) return false;
        return $this->writeCache($this->getId(), $response, $ttl , true);
    }

    private function getFromCache() {
        if ( !$this->config('request_cache') ) return false;
        if ( !$cache = $this->readCache($this->getId(), true) ) return false;
        $this->setResponseCode(200);
        $this->setResponse($cache, true); 
        $this->setStatus(self::STATUS_CACHED);
        return true;
    }
}

