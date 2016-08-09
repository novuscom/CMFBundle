<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;
use Knp\Menu\Loader\NodeLoader;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;


class Menu
{

	private function getMenuRenderer()
	{
		$Site = $this->container->get('Site');
		$currentSite = $Site->getCurrentSite();
		$request = $this->container->get('request_stack')->getCurrentRequest();
		$routeName = $request->get('_route');
		$routeParams = $request->get('_route_params');
		$rootDir = $this->container->get('kernel')->getRootDir();
		$twigLoader = new \Twig_Loader_Filesystem(array(
			$rootDir . '/../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views',
			$rootDir . '/../templates/' . $currentSite['code'] . '/Menu',
		));
		$twig = new \Twig_Environment($twigLoader);
		$itemMatcher = new Matcher();
		$menuRenderer = new \Knp\Menu\Renderer\TwigRenderer($twig, 'knp_menu.html.twig', $itemMatcher);
		return $menuRenderer;
	}

	private function generateMenuFromTree($tree, $menu, $routeCode, $currentCode)
	{
		foreach ($tree as $item) {
			//echo '<pre>' . print_r($item, true) . '</pre>';
			$url = $this->getUrlForItem($routeCode, $item);
			$menuItem = $menu->addChild($item['name'], array('uri' => $url));
			$menuItem->setAttribute('data-id', $item['id']);
			if ($item['full_code'] == $currentCode) {
				$menuItem->setAttribute('data-current', 'true');
				$menuItem->setCurrent(true);
			}
			if (!empty($item['__children']))
				$this->generateMenuFromTree($item['__children'], $menuItem, $routeCode, $currentCode);
		}
	}


	private function getUrlForItem($routeCode, $item)
	{
		//echo '<pre>' . print_r($routeCode, true) . '</pre>';
		//echo '<pre>' . print_r($item, true) . '</pre>';
		$this->setRouteInfo($routeCode);
		$url = '#';
		if (!empty($this->routeProperties)) {
			$urlParams = array();
			$allParams = true;
			foreach ($this->routeProperties as $rp) {
				if ($item['properties'][$rp]['value'])
					$urlParams['PROPERTY_' . $rp] = $item['properties'][$rp]['value'];
				else {
					$allParams = false;
					break;
				}
			}
			if ($allParams)
				$url = $this->urlGenerator->generate($routeCode, $urlParams);
		} else {
			$url = $this->urlGenerator->generate($routeCode, array('CODE' => $item['full_code']));
		}
		return $url;
	}

	private $routeProperties;

	private $routeInfo;

	private function setRouteInfo($routeName)
	{
		$route = $this->container->get('route');
		$r = $route->getRoute($routeName);
		$props = array();
		foreach ($r['vars'] as $var) {
			if (preg_match('/^PROPERTY_(.+?)$/', $var, $matches)) {
				$props[] = $matches[1];
			}
		}
		$this->routeInfo = $r;
		$this->routeProperties = $props;
	}

	public function getSectionsMenu($options)
	{

		/*
		 * Настроки кеширования
		 */
		$env = $this->container->getParameter("kernel.environment");
		$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
		$namespace = 'menu_section_' . $env . '_' . $options['BLOCK_ID'];
		$cacheDriver->setNamespace($namespace);


		// засекаем время
		$time_start = microtime(1);

		/*
		 * Добавляем текущую страницу в id кеша
		 */
		$request = $this->container->get('request_stack');
		$routeParams = $request->getCurrentRequest()->get('_route_params');


		$currentCode = false;
		if (array_key_exists('CODE', $routeParams))
			$currentCode = trim($routeParams['CODE'], '/');
		$options['@currentCode'] = $currentCode;


		//echo '<pre>' . print_r($options, true) . '</pre>';

		// id кеша
		$cacheId = json_encode($options);

		//if ($fooString = $cacheDriver->fetch($cacheId)) {
		if (false) {
			/*
			 * Выдаем результат из кеша
			 */
			$result = unserialize($fooString);
		} else {
			/*
			 * Генерируем результат
			 */
			$SectionClass = $this->container->get('SectionClass');
			$tree = $SectionClass->SectionsList(array(
				'block_id' => $options['BLOCK_ID'],
			));
			//echo '<pre>' . print_r('tree on SectionMenu', true) . '</pre>';
			//echo '<pre>' . print_r($tree, true) . '</pre>';

			$factory = new MenuFactory();
			$menu = $factory->createItem('root');
			//echo '<pre>' . print_r($routeName, true) . '</pre>';
			//echo '<pre>' . print_r($routeParams, true) . '</pre>';
			$this->generateMenuFromTree($tree, $menu, $options['route'], $currentCode);
			$menuRenderer = $this->getMenuRenderer();
			$result = $menuRenderer->render($menu, array(
				'template' => $options['template'], 'currentAsLink' => false));
			/*
			 * Сохраняем результат в кеш
			 */
			$cacheDriver->save($cacheId, serialize($result));
		}
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
		return html_entity_decode($result);
		//echo $time.' мс';
	}

	private $logger;
	private $em;
	private $container;

	public function __construct(Logger $logger, EntityManager $em, ContainerInterface $container)
	{
		$this->logger = $logger;
		$this->em = $em;
		$this->urlGenerator = $container->get('router');
		$this->container = $container;
	}
}