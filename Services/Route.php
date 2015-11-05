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
		$routeInfo = $this->getRoute($routeCode);
		$routeParams = array();
		if (in_array('CODE', $routeInfo['vars']))
			$routeParams['CODE'] = $object->getCode();
		if (in_array('ID', $routeInfo['vars']))
			$routeParams['ID'] = $object->getId();
		$url = $this->Router->generate($routeCode, $routeParams);
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