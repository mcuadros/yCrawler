<?php
namespace yCrawler;
use Pimple;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use yCrawler\Cache\Driver\APC;
use yCrawler\Cache\Driver\File;

class Crawler extends Pimple {
    public function __construct()
    {
        $this->registerConfig();
        $this->registerLogger();
        $this->registerCache();
    }

    public function registerConfig() {
        $this['config'] = $this->share(function() {
            return new Config();  
        });
    }

    public function registerLogger() {
        $this['logger'] = $this->share(function() {
            $logger = new Logger('crawler');
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
            $logger->addInfo('My logger is now ready');

            return $logger;  
        });
    }

    public function registerCache() {
        $this['cache.memory'] = $this->share(function() {
            return new APC($this);
        });

        $this['cache.persistent'] = $this->share(function() {
            return new File($this);
        });   
    }
}