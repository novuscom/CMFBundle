<?php

namespace Novuscom\CMFBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Novuscom\CMFBundle\Services\Site;


class ExtraLoader implements LoaderInterface
{
	private $loaded = false;

	public function load($resource, $type = null)
	{
		if (true === $this->loaded) {
			throw new \RuntimeException('Do not add the "extra" loader twice');
		}

		$routes = new RouteCollection();

		$Site = $this->Site;

		$base_routes = $this->em->getRepository('NovuscomCMFBundle:Route')->findBy(
			array(
				'active' => true
			),
			array(
				'sort' => 'asc'
			)
		);


		$logger = $this->logger;
		foreach ($base_routes as $r) {
			//echo '<pre>' . print_r($r->getParams(), true) . '</pre>';
			$routeSite = $Site->getSiteEntityById($r->getSite()->getId());
			$aliasesArray = array();
			foreach ($routeSite->getAliases() as $a) {
				$aliasesArray[] = $a->getName();
			}

			//print_r($aliasesArray);

			switch ($r->getController()) {
				case 'NovuscomCMFBundle:Component:Section':
					$controller_code = 'section';
					break;
				case 'NovuscomCMFBundle:Component:SectionsList':
					$controller_code = 'sections_list';
					break;
				case 'NovuscomCMFBundle:Component:Element':
					$controller_code = 'element';
					break;
				case 'NovuscomCMFBundle:Component:ElementsList':
					$controller_code = 'elements_list';
					break;
				default:
					$controller_code = 'page';
					break;
			}

			$params = array();
			$params['template_code'] = $r->getCode();
			$params['code'] = $r->getCode();
			$params['controller_code'] = $controller_code;
			if ($r->getBlock()) {
				$params['BLOCK_ID'] = $r->getBlock()->getId();
			}
			if ($r->getPage()) {
				$params['page_id'] = $r->getPage()->getId();
			}
			if ($r->getParams()) {
				$route_params = json_decode($r->getParams(), true);
				$params['params'] = $route_params;
			} else {
				$route_params = array();
			}

			//$logger->info('<pre>'.print_r($r->getCode(), true).'</pre>');
			$logger->info('Route params: ' . print_r($route_params, true) . '</pre>');
			$defaults = array(
				'_controller' => $r->getController(),
				'params' => $params,
			);
			//$logger->error('<pre>'.print_r($r->getParams(), true).'</pre>');
			//$logger->error('<pre>'.print_r($route_params, true).'</pre>');
			//$logger->error('An error occurred');
			//$route_params['method'] = 'GET';
			$requirements = array();
			if (is_array($route_params) && array_key_exists('requirements', $route_params) && is_array($route_params['requirements'])) {
				$requirements = array_merge($requirements, $route_params['requirements']);
			}
			if ($r->getId() == 26) {
				//echo '<pre>' . print_r($r->getParams(), true) . '</pre>';
				//echo '<pre>' . print_r('{"requirements":{"BLOCK_ID":"\\\d+"}}', true) . '</pre>';
				//echo '<pre>' . print_r(json_decode('{"requirements":{"BLOCK_ID":"\\\d+"}}', true), true) . '</pre>';
			}
			$method = 'GET';
			if (is_array($route_params) && array_key_exists('method', $route_params)) {
				$method = $route_params['method'];
			}
			$requirements['domains'] = implode($aliasesArray, '|');
			$route = new Route(
				$r->getTemplate(),
				$defaults,
				$requirements,
				array(),
				"{domains}",
				array(),
				$method
			);
			$routes->add($r->getCode(), $route);
		}


		return $routes;
	}

	public function supports($resource, $type = null)
	{
		return 'extra' === $type;
	}

	public function getResolver()
	{
		// needed, but can be blank, unless you want to load other resources
		// and if you do, using the Loader base class is easier (see below)
	}

	public function setResolver(LoaderResolverInterface $resolver)
	{
		// same as above
	}

	private $em;
	private $logger;
	private $Site;

	public function __construct(EntityManager $em, $logger, Site $Site)
	{
		$this->em = $em;
		$this->logger = $logger;
		$this->Site = $Site;
	}
}