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
    private $cleanUrl;

    private $responseCode;
    private $responseHeaders;
    private $response;

    private $status = self::STATUS_NONE;
    private $retries = 0;
    private $curl;

    private $utf8;
    private $cache;
    private $cookie;
    private $headers;
    private $userAgent;
    private $connectionTimeout;
    private $maxExecutionTime;
    private $sslCertificate;
    
    public function __construct($crawler, $url = null) {
        parent::__construct($crawler);
        $this->headers = $this->config('headers');
        $this->userAgent = $this->config('user_agent');
        $this->connectionTimeout = $this->config('connection_timeout');
        $this->maxExecutionTime = $this->config('max_execution_time');
        $this->sslCertificate = $this->config('ssl_certificate');

        $this->cache = $this->config('request_cache');
        $this->cookie = $this->config('cookie');
        $this->interface = $this->config('interface');
        $this->utf8 = $this->config('utf8');

        if ( $url ) $this->setUrl($url);
    }

    public function setUrl($url) {
        if ( !$this->cleanUrl = URL::fix($url) ) {
            throw new Exception('Unable to set URL "'.$originalUrl.'" non-valid url.');
        }

        $this->url = $url;
        $this->id = md5($this->url);
        $this->host = parse_url(strtolower($this->url), PHP_URL_HOST);
        $this->scheme = parse_url(strtolower($this->url), PHP_URL_SCHEME);        
        return $this->url;
    }
 
    public function setPost($post) {
        $this->post = http_build_query($post);
        return $this->post;
    }

    public function setUserAgent($ua) {
        return $this->userAgent = $ua;
    }

    public function setHeaders($headers) {
        return $this->headers = $headers;
    }

    public function setCookie($cookie) {
        return $this->cookie = $cookie;
    }

    public function setSSLCertificate($file) {
        if ( !file_exists($file) ) {
            throw new \InvalidArgumentException(sprintf('No se pudo encontrar el fichero "%s"', $file));
        }

        return $this->sslCertificate = $file;
    }

    public function setConnectionTimeout($seconds) {
        return $this->connectionTimeout = $seconds;
    }

    public function setMaxExecutionTime($seconds) {
        return $this->maxExecutionTime = $seconds;
    }

    public function setOutputInterface($ip) {
        return $this->outputInterface = $ip;
    }

    public function setUTF8($boolean) {
        return $this->utf8 = $boolean;
    }

    public function setCache($ttl) {
        return $this->cache = $ttl;
    }

    public function getId() { return $this->id; }
    public function getHost() { return $this->host; }
    public function getScheme() { return $this->scheme; }
    public function getUrl() { return $this->url; }
    public function getPost() { return $this->post; }
    public function getUserAgent() { return $this->userAgent; }
    public function getHeaders() { return $this->headers; }
    public function getCookie() { return $this->cookie; }
    public function getSSLCertificate() { return $this->sslCertificate; }
    public function getConnectionTimeout() { return $this->connectionTimeout; }
    public function getOutputInterface() { return $this->outputInterface; }
    public function getUTF8() { return $this->utf8; }
    public function getResponse() { return $this->response; }
    public function getResponseCode() { return $this->responseCode; }
    public function getResponseHeaders() { return $this->responseHeaders; }
    public function getStatus() { return $this->status; }
    public function getExecutionTime() { return $this->elapsed; }

    public function newRetry() {
        if ( ++$this->retries > $this->config('maxretries') ) return false;
        $this->setStatus(self::STATUS_NONE);
        return $this->retries;
    }

    public function call() {
        if ( $cache = $this->getFromCache() ) return $cache;

        $start = microtime(true);

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($this->curl, CURLOPT_URL, $this->cleanUrl);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->maxExecutionTime);
        
        if ( $this->headers ) {
            curl_setopt($this->curl, CURLOPT_HEADER, true);
            curl_setopt($this->curl, CURLOPT_FAILONERROR, false);
        } else {
            curl_setopt($this->curl, CURLOPT_FAILONERROR, true);
        }

        if  ( $this->cookie ) {
            curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookie);     
        }
        
        if ( $this->interface ) {
            curl_setopt($this->curl, CURLOPT_INTERFACE,$this->interface);  
        }

        if ( $this->sslCertificate ) {
            curl_setopt($this->curl, CURLOPT_CAINFO, $this->sslCertificate);
        }
        
        if ( $this->post ) { 
            curl_setopt ($this->curl, CURLOPT_POSTFIELDS, $this->post);
            curl_setopt ($this->curl, CURLOPT_POST, 1);
        }

        $response = curl_exec($this->curl); 
        if ( $this->headers ) {
            $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
            $this->setResponseHeaders(substr($response, 0, $headerSize));
            $this->setResponse(substr($response, $headerSize));
        } else {
            $this->setResponse($response);  
        }

        $this->setExecutionTime(microtime(true) - $start);

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
        
        if ( $this->utf8 ) { 
            $this->response = Encoding::toUTF8($response);
        } else {
            $this->response = $response;
        }

        return $response;
    }

    private function setResponseHeaders($string) {
        return $this->responseHeaders = $string;
    }

    private function setStatus($status = self::STATUS_DONE) {
        return $this->status = $status;
    }

    private function setExecutionTime($time) {
        $this->elapsed = $time;
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

    private function setToCache($response) {
        if ( (int)$this->cache == 0 ) return false;
        return $this->writeCache($this->getId(), $response, $this->cache, true);
    }

    private function getFromCache() {
        if ( !$this->cache ) return false;
        if ( !$cache = $this->readCache($this->getId(), true) ) return false;
        $this->setResponseCode(200);
        $this->setResponse($cache, true); 
        $this->setStatus(self::STATUS_CACHED);
        return true;
    }
}

