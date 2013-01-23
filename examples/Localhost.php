<?php
namespace Config;
use yCrawler;

class Localhost extends yCrawler\Parser_Base {
	public function initialize() {
		$this->setURLPattern('/localhost/');
		$this->setStartupURL('http://localhost/codes/200.php');
	}
}