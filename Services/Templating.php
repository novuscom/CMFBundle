<?php

namespace Novuscom\CMFBundle\Services;

use Monolog\Logger;
use Novuscom\CMFBundle\Services\Site;
use Symfony\Bundle\TwigBundle\TwigEngine;

class Templating
{

	public function getPath($type, $templateCode = false){
		if (!trim($templateCode))
			$templateCode = 'default';
		$site = $this->siteService->getCurrentSite();
		$template = '@templates/' . $site['code'] . '/'.$type.'/' . $templateCode . '.html.twig';
		if ($this->engine->exists($template) == false) {
			$template = 'NovuscomCMFBundle:DefaultTemplate/'.$type.':' . $templateCode . '.html.twig';
		}
		return $template;
	}

	private $logger;
	private $siteService;
	private $engine;

	public function __construct(Logger $logger, Site $siteService, TwigEngine $engine)
	{
		$this->logger = $logger;
		$this->siteService = $siteService;
		$this->engine = $engine;
	}
}