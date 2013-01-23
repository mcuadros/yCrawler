<?php
namespace yCrawler;

class Request extends Base {
    const STATUS_NONE = 0;
    const STATUS_FAILED = 1;
    const STATUS_RETRY = 2;
    const STATUS_DONE = 3;
    const STATUS_CACHED = 4;
    
    private $_id;
    private $_url;
    private $_host;
    private $_scheme;

    private $_post;

    private $_cookie ;
    private $_userAgent;
    private $_sslCertificate;
    private $_maxRetries;
    private $_cache;
    private $_connectionTimeout;
    private $_timeout;
    private $_interface;
    private $_utf8;
    private $_headers;

    private $_responseCode;
    private $_response;

    private $_status = self::STATUS_NONE;
    private $_retries = 0;


    private $_curl = false;

    public function __construct($url) {
        $this->_maxRetries = Config::get('max_retries');
        $this->_sslCertificate = Config::get('ssl_certificate');
        $this->_userAgent = Config::get('user_agent');
        $this->_cookie = Config::get('cookie');
        $this->_cache = Config::get('request_cache');
        $this->_headers = Config::get('headers');
        

        $this->_timeout = Config::get('max_execution_time');
        $this->_connectionTimeout = Config::get('connection_timeout');
        $this->_interface = Config::get('interface');
        $this->_utf8 = Config::get('utf8');

        $this->setUrl($url);
    }

    public function setUrl($url) {
        if ( !$this->_cleanUrl = Misc_URL::fix($url) ) {
            throw new Exception('Unable to set URL "'.$originalUrl.'" non-valid url.');
        }

        $this->_url = $url;
        $this->_id = md5($this->_url);
        $this->_host = parse_url(strtolower($this->_url), PHP_URL_HOST);
        $this->_scheme = parse_url(strtolower($this->_url), PHP_URL_SCHEME);
        
        Output::log('setUrl: "' . $url .'"', Output::DEBUG);
        return $this->_url;
    }

    public function setCache($ttl) {
        $this->_cache = (int)$ttl;
        Output::log('setCache: "' . $this->_cache . '"', Output::DEBUG);
        return $this->_cache;
    }

    public function setPost($post) {
        $this->_post = http_build_query($post);
        Output::log('setPost: ' . strlen($this->_post) .' byte(s)', Output::DEBUG);
        return $this->_post;
    }

    public function setCookie($cookie) {
        $this->_cookie = $cookie;
        Output::log('setCookie: "' . $this->_cookie . '"', Output::DEBUG);
        return $this->_cookie;
    }

    public function setResponse($response,$renewCache=true) {
        Output::log('setResponse: ' . strlen($response) .' byte(s)', Output::DEBUG);
        
        //Sólo se guarda en caché, si se desea
        if($renewCache){
            Cache::driver('File')->set($this->getId(), $response, $this->_cache);
        }

        if ( !$response ) $this->_response = false;
        
        if ( $this->_utf8 ) { 
            $this->_response = Misc_Encoding::toUTF8($response);
        } else {
            $this->_response = $response;
        }

        return $response;
    }

    public function setHeaders($headers) {
        Output::log('setHeaders: ' . strlen($headers) .' byte(s)', Output::DEBUG);
        if ( !$headers ) $this->_headers = false;
        return $this->_headers = $headers;
    }

    public function setResponseCode($code) {
        //Of course 200 HTTP code, is OK
        if ( $code == 200 ) $this->setStatus(self::STATUS_DONE);
        //Under 100, is a CURL error, maybe a temporal error, we will try again.
        else if ( $code < 100 ) $this->setStatus(self::STATUS_RETRY);
        //Any other code 4xx or 5xx will be marked as failed
        else $this->setStatus(self::STATUS_FAILED);

        Output::log('setResponseCode: ' . $code, Output::DEBUG);
        return $this->_responseCode = $code;
    }

    public function setStatus($status = self::STATUS_DONE) {
        $this->data('set', 'status', $this->getConstant($status));
        return $this->_status = $status;
    }

    public function getId() { return $this->_id; }
    public function getUrl() { return $this->_url; }
    public function getPost() { return $this->_post; }
    public function getCookie() { return $this->_cookie; }
    public function getUserAgent() { return $this->_userAgent; }
    public function getHost() { return $this->_host; }
    public function getScheme() { return $this->_scheme; }
    public function getResponseCode() { return $this->_responseCode; }
    public function getStatus() { return $this->_status; }
    public function getCache() { return $this->_cache; }
    public function getHeaders() { return $this->_headers; }
    public function &getResponse() { return $this->_response; }

    public function newRetry() {
        if ( ++$this->_retries > $this->_maxRetries ) return false;
        $this->setStatus(self::STATUS_NONE);
        return $this->_retries;
    }

    public function call() {
        if ( $this->_cache != 0 && $cache = Cache::driver('File')->get($this->getId()) ) {
            $this->setResponseCode(200);
            $this->setResponse($cache,FALSE); //Una respuesta cacheada, no debe ser "cacheada de nuevo"
            $this->setStatus(self::STATUS_CACHED);

            Output::log('Request recovered from cache.', Output::DEBUG);
            return $cache;
        }

        $start = microtime(true);

        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($this->_curl, CURLOPT_URL, $this->_cleanUrl);
        curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout);
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, $this->_timeout);
        
        if ( $this->_headers ) {
            curl_setopt($this->_curl, CURLOPT_HEADER, true);
            curl_setopt($this->_curl, CURLOPT_FAILONERROR, false);
        } else {
            curl_setopt($this->_curl, CURLOPT_FAILONERROR, true);
        }

        if  ( $this->_cookie ) {
            curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_cookie );
            curl_setopt($this->_curl, CURLOPT_COOKIEFILE,$this->_cookie );     
        }
        
        if ( $this->_interface ) {
            curl_setopt($this->_curl, CURLOPT_INTERFACE,$this->_interface );  
        }

        if ( $this->_sslCertificate ) {
            curl_setopt($this->_curl, CURLOPT_CAINFO, $this->_sslCertificate);
        }
        
        if ( $this->_post ) { 
            curl_setopt ($this->_curl, CURLOPT_POSTFIELDS, $this->_post);
            curl_setopt ($this->_curl, CURLOPT_POST, 1);
        }

        $response = curl_exec($this->_curl); 
        if ( $this->_headers ) {
            $headerSize = curl_getinfo($this->_curl, CURLINFO_HEADER_SIZE);
            $this->setHeaders(substr($response, 0, $headerSize));
            $this->setResponse(substr($response, $headerSize));
        } else {
            $this->setResponse($response);  
        }

        $elapsed = microtime(true) - $start;
        $this->data('set', 'elapsed', $elapsed);

        $errorNo = curl_errno($this->_curl);
        $httpCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);

        if( !$errorNo && $httpCode == 200 ) {
            $this->setResponseCode(curl_getinfo($this->_curl, CURLINFO_HTTP_CODE));
            Output::log('Request in ' . $elapsed . ' second(s)', Output::DEBUG);
            return true;
        } else {
            if ( $httpCode ) {
                $this->setResponseCode($httpCode);
                Output::log('HTTP error: "' . $this->_responseCode . ' "' . $this->_url . '"', Output::DEBUG);
                return false;
            } else {
                $this->setResponseCode($errorNo);
                Output::log('HTTP error: "' . $this->_responseCode . ' message: ' . curl_error($this->_curl) . '"', Output::DEBUG);
                return false;
            }
        }
    }

}

