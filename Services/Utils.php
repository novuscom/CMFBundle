<?php

namespace Novuscom\CMFBundle\Services;

use Monolog\Logger;
use Novuscom\CMFBundle\Services\Site;

class Utils
{
	CONST VERSION = '0.0.7.9';

	public static function msg($object){
		echo '<pre>'.print_r($object, true).'</pre>';
	}

	public static function getUniqueHash($text=''){
		return md5(microtime().$text);
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
