<?php
namespace yCrawler\Request\Exceptions;
use RuntimeException;

class HTTPError extends RuntimeException {
    const MESSAGE = 'Received http error code %d';

    public function __construct($code)
    {
        parent::__construct(sprintf(self::MESSAGE, $code), $code);
    }
}