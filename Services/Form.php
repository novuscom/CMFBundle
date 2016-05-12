<?php

namespace Novuscom\CMFBundle\Services;

use Monolog\Logger;
use Novuscom\CMFBundle\Services\Section as SectionService;

class Form
{

	public function getElementProperties($formField){
		$propArray = array();
		foreach ($formField as $p) {
			if ($p->getData()) {
				$propArray[$p->getName()] = $p->getData();
			}
		}
		return $propArray;
	}

	private $em;
	private $Element;
	private $logger;
	private $Section;

	public function __construct(\Doctrine\ORM\EntityManager $em, $Element, Logger $logger, SectionService $Section)
	{
		$this->em = $em;
		$this->Element = $Element;
		$this->logger = $logger;
		$this->Section = $Section;
	}
}