<?php

namespace Novuscom\Bundle\CMFBundle\Services;

use Monolog\Logger;

class Utils
{
	CONST VERSION = '0.0.6.9';

	public function msg($object){
		echo '<pre>'.print_r($object, true).'</pre>';
	}

	public function getVersion(){
		return self::VERSION;
	}

	private $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}
}
