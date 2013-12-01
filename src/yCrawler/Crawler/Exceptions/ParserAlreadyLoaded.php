<?php

namespace yCrawler\Crawler\Exceptions;

use RuntimeException;

class ParserAlreadyLoaded extends RuntimeException
{
    public function __construct(Parser $parser)
    {
        parent::__construct(sprintf(
            'A parser of "%s" class already loaded.', $parser->getName()
        ));
    }
}
