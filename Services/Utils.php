<?php

namespace Novuscom\CMFBundle\Services;

use Monolog\Logger;

class Utils
{
	CONST VERSION = '0.0.5.10';

	public function getVersion(){
		return self::VERSION;
	}

	private $logger;

	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}
}