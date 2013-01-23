<?php
namespace yCrawler;

class Output_Base {
    protected $_jobs;
    protected $_logLevel = Output::INFO;

    public function log($string, $level = Output::WARNING) {
        throw new Exception('Output driver without log funciton.');
    }

    public function getLogLevel() { return $this->_logLevel;  }
    public function setLogLevel($level) {
        return $this->_logLevel = $level;
    }

    
    public function linkJobs(&$jobs) {
        $this->_jobs = &$jobs;
    }
}