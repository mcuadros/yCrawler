<?php
namespace yCrawler;

class Output_Driver_Console extends Output_Base {
	public function log($string, $level = Output::CUSTOM) {
		if ( $level >= $this->_logLevel ) {
			$tmp = Array(
				Array(Output::levelToString($level), 7),
				Array(date('H:i:s'), 8),
				Array(count($this->_jobs), 3),
				Array(getmypid(), 7)
			);
		
			$log = '';
			foreach($tmp as $data) {
				$log .= '[' . str_pad($data[0], $data[1]) . ']';
			}

			$string = str_replace("\n", str_pad("\n", strlen($log) + 1) , $string);
			echo $this->colorString($log, $level) . ' ' . $string . "\n";
		}		
	}


	private function colorString($string, $level) {
		$codes = Array();
		switch ($level) {
			case Output::INFO: $codes = Array(1,32); break;
			case Output::DEBUG: $codes = Array(1,36); break;
			case Output::NOTICE: $codes = Array(1,37); break;
			case Output::ERROR: $codes = Array(1, 1, 41); break;
			case Output::WARNING: $codes = Array(1, 1, 31); break;
		}

		return "\033[" . implode(';', $codes) . "m" . $string . "\033[0m";
	}



}