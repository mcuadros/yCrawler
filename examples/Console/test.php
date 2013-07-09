<?php
namespace Voddly;
use yCrawler\Output;
use yCrawler\Config;
use yCrawler\Document;
use yCrawler\Misc_CLI;
use yCrawler\Crawler_Base;

require_once 'autoload.php';

class test extends Misc_CLI
{
    protected $_appName = 'yCrawler';
    protected $_appVersion = '2.0alpha';
    protected $_appAuthor = 'Yunait Unidos para Ahorrar S.L. <contacto@yunait.com>';

    protected $_appArgs  = Array(
        Array('verbose', 'v', 'info', 'Nivel de verbose'),
        Array('output', 'o', 'console', 'Salida del log'),
        Array('config', 'c', 'test.ini', 'Fichero de configuración'),
        Array('settings', null, false, "Parametros de configuración on-the-fly, formato: setting=value\n"),

        Array('url', null, false, 'Realiza una prueba de parseo para la url dada'),
    );

    protected $_appFlags = Array(
        Array('crawler', null, false, 'Realiza una prueba de parseo para la url dada'),
        Array('info', null, false, 'Muestra la configuración actual.')
    );

    public function __construct()
    {
        parent::__construct();
        $this->configureOutput();
        $this->configureConfig();

        if ( $this->getValue('info') ) $this->flagInfo();
        else if ( $this->getValue('url') ) $this->makeTest();
        else if ( $this->getValue('crawler') ) $this->makeCrawler();
    }

    private function configureConfig()
    {
        if ( !file_exists($this->getValue('config')) ) $this->error('config file not found.', true);
        Config::loadConfig($this->getValue('config'));

        if ( $settings = $this->getValue('settings') ) {
            parse_str(str_replace(' ', '&', $settings), $params);
            foreach($params as $setting => $value) Config::set($setting, $value);
        }
    }

    private function configureOutput()
    {
        Output::loadDriver('yCrawler\Output_Driver_' . $this->getValue('output'));

        $r = new \ReflectionClass('yCrawler\Output');
        if ( !$level = $r->getConstant(strtoupper($this->getValue('verbose'))) ) {
            $this->error('invalid verbose argument, valid values: ' . implode(', ', array_keys($r->getConstants())), true);
        }
        Output::setLogLevel($level);
    }

    private function flagInfo()
    {
        $this->table('Config:', Config::getConfig(),1);
    }

    private function makeTest()
    {
        $_parser = new Config_GooglePlay;
        $doc = new Document($this->getValue('url'), $_parser);
        $doc->parse();

        $this->table('Info', json_decode((string) $doc, true), 1);
        $this->table('Values:', $doc->getValues(), 2);
        $this->table('Links:', $doc->getLinks(), 1);
    }

    private function makeCrawler()
    {
        $_crawler = new Crawler_Base();
        $_crawler->registerParser(__NAMESPACE__.'\Config_GooglePlay');
        $_crawler->run();
    }
}

$test = new CLI;
