<?php

namespace Novuscom\CMFBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Doctrine\Common\Collections\ArrayCollection;
use Monolog\Logger;
use Novuscom\CMFBundle\Entity\ElementSection;
use Novuscom\CMFBundle\Services\Utils;

class Route
{

	public function getRoute($routeName)
	{
		$availableApiRoutes = [];
		foreach ($this->Router->getRouteCollection()->all() as $name => $route) {
			$route = $route->compile();
			if (strpos($name, "api_") !== 0) {
				$availableApiRoutes[$name] = ["vars" => $route->getVariables()];
			}
		}
		return $availableApiRoutes[$routeName];
	}

	public function getUrl($routeCode, $object)
	{
		$url = false;
		$routeInfo = $this->getRoute($routeCode);
		if (in_array('PAGE', $routeInfo['vars']))
			return false;
		$routeParams = array();
		if (in_array('CODE', $routeInfo['vars'])) {
			if (get_class($object) == 'Novuscom\CMFBundle\Entity\Element') {
				$routeParams['CODE'] = $object->getCode();
			}
			if (get_class($object) == 'Novuscom\CMFBundle\Entity\Section') {
				$code = $this->container->get('Section')->getFullCode($object);
				$routeParams['CODE'] = $code;
			}
		}
		if (in_array('SECTION_CODE', $routeInfo['vars'])) {
			//$this->Utils->msg(get_class($object));
			if (get_class($object) == 'Novuscom\CMFBundle\Entity\Element') {
				$sections = $object->getSection();
				$section = $sections[0]->getSection();
				$code = $this->container->get('Section')->getFullCode($section);
				$routeParams['SECTION_CODE'] = $code;
			}
		}
		if (in_array('ID', $routeInfo['vars']))
			$routeParams['ID'] = $object->getId();
		if ($routeParams) {
			//$this->Utils->msg($object->getName());
			//$this->Utils->msg($routeParams);
			$url = $this->Router->generate($routeCode, $routeParams);
		}
		return $url;
	}

	private $container;
	private $Utils;
	private $Router;
	private $logger;
	private $em;

	public function __construct(
		\Doctrine\ORM\EntityManager $em,
		Logger $logger,
		ContainerInterface $container,
		Utils $Utils,
		Router $Router)
	{
		$this->em = $em;
		$this->container = $container;
		$this->Utils = $Utils;
		$this->Router = $Router;
	}
}