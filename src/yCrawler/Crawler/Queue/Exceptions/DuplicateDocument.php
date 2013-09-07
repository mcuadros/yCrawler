<?php
namespace yCrawler\Crawler\Queue\Exceptions;

use yCrawler\Document;
use RuntimeException;

class DuplicateDocument extends RuntimeException
{
    const MESSAGE = 'Another documetn with the URL %s is already in the queue';

    function setDocument(Document $document)
    {
        
    }   
}