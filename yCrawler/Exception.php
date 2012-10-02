<?php
namespace yCrawler;

class Exception extends \Exception {
    private $_previous = null;

    public function __construct($msg = '', $code = 0, Exception $previous = null) {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            parent::__construct($msg, (int) $code);
            $this->_previous = $previous;
        } else {
            parent::__construct($msg, (int) $code, $previous);
        }
    }


    public function __call($method, array $args) {
        if ( 'getprevious' == strtolower($method) ) {
            return $this->_getPrevious();
        }
        return null;
    }

    public function __toString() {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if (null !== ($e = $this->getPrevious())) {
                return $e->__toString()
                       . "\n\nNext "
                       . parent::__toString();
            }
        }

        return parent::__toString();
    }

    protected function _getPrevious() {
        return $this->_previous;
    }
}
