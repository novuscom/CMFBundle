<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;

class Crumbs
{

	private function getCacheDriver()
	{
		$env = $this->container->get('kernel')->getEnvironment();
		//$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/Sites/');
		$cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
		$cacheDriverName = $this->container->getParameter('cache_driver');
		if ($cacheDriverName=='apcu') {
			$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
		}
		//echo '<pre>'.print_r($cacheDriverName, true).'</pre>';
		//$cacheDriver->setNamespace('Pages_' . $env);
		return $cacheDriver;
	}

	public function getForSite($params)
	{

		$time_start = microtime(1);
		$logger = $this->logger;
		$logger->debug('Получение хлебных крощек');
		$request = $this->container->get('request_stack')->getMasterRequest();
		$route_params = $request->attributes->get('_route_params');
		$routeName = $request->attributes->get('_route');
		$pageRoute = ($routeName == 'cmf_page_frontend' || $routeName == 'page');
		if (false) {
			//if (!isset($route_params['params']) && !$pageRoute) {
			$logger->notice('параметры маршрута не известны и это не маршрут для статических страниц, возвращаем пустой результат (' . print_r($route_params, true) . ')');
			return new Response();
		}
		$logger->info('параметры маршрута известны или это маршрут для статических страниц');
		$env = $this->container->get('kernel')->getEnvironment();
		$cacheDriver = $this->getCacheDriver();
		$cacheDriver->setNamespace('CrumbsAction_' . $env);
		$cacheId = json_encode(array($params, $route_params));
		//Utils::msg($cacheId);
		$existParams = (array_key_exists('params', $route_params));
		$Site = $this->container->get('Site');
		$currentSite = $Site->getCurrentSite();
		$crumbs = array();
		//echo '<pre>' . print_r($crumbs, true) . '</pre>';
		//echo '<pre>' . print_r('крошки', true) . '</pre>'; exit;
		//if (false) {
		if ($fooString = $cacheDriver->fetch($cacheId)) {
			//echo '<pre>' . print_r('крошки закешированы', true) . '</pre>';
			$logger->debug('крошки есть в кеше');
			$response = unserialize($fooString);
		} else {
			$logger->debug('крошек нет в кеше');
			$em = $this->em;
			$codes_array = array();
			/*
			 * Хлебные крошки для страниц
			 */
			$repo = $em->getRepository('NovuscomCMFBundle:Page');
			$Page = $this->container->get('Page');
			if ($existParams) {
				$page = $repo->find($route_params['params']['page_id']);
			} elseif ($pageRoute && $route_params['name']) {
				$page = $Page->findPage($route_params['name']);
			} else {
				$page = $Page->getRoot();
			}
			$path = $repo->getPath($page);
			foreach ($path as $p) {
				if ($p->getLvl() == 0) {
					$crumbs[] = array(
						'url' => $this->container->get('router')->generate('cmf_page_main'),
						'name' => $p->getName(),
					);
				} else {
					$codes_array[] = $p->getUrl();
					$crumbs[] = array(
						'url' => $this->container->get('router')->generate('page', array('url' => implode('/', $codes_array))),
						'name' => $p->getName(),
					);
				}
			}

			/*
			 * Хлебные крошки для раздела
			 */
			$section_codes_array = $codes_array;
			if ($existParams && $route_params['params']['controller_code'] == 'section' && isset($route_params['SECTION_CODE'])) {
				$logger->info('Создаем хлебные крошки для раздела');
				$crumbs = $this->getCrumbsForSection($route_params['SECTION_CODE'], $route_params, $crumbs, $codes_array);
			}

			/*
			 * Хлебные крошки для элемента
			 */
			if ($existParams && $route_params['params']['controller_code'] == 'element') {
				if (array_key_exists('SECTION_CODE', $route_params))
					$crumbs = $this->getCrumbsForSection($route_params['SECTION_CODE'], $route_params, $crumbs, $codes_array);

				$filter = array();

				if (array_key_exists('CODE', $route_params)) {
					$filter['code'] = $route_params['CODE'];
				}
				if (array_key_exists('ID', $route_params)) {
					$filter['id'] = $route_params['ID'];
				}
				$elementsId = array();
				if ($this->sectionByPath) {
					$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array('section' => $this->sectionByPath));
					foreach ($ElementSection as $es) {
						$elementsId[] = $es->getElement()->getId();
					}
				}
				if ($elementsId) {
					$filter['id'] = $elementsId;
				}
				$element = $em->getRepository('NovuscomCMFBundle:Element')->findOneBy($filter);
				$codes_array[] = $element->getCode();
				$crumbItem = array(
					//'url' => $this->generateUrl($routeName, array('name' => implode('/', $codes_array))),
					'name' => $element->getName(),
				);
				if (array_key_exists('ID', $route_params)) {
					$crumbItem['url'] = $this->container->get('router')->generate($routeName, array('SECTION_CODE' => implode('/', $section_codes_array), 'ID' => $element->getId()));
				} else if (array_key_exists('CODE', $route_params)) {
					$crumbItem['url'] = $this->container->get('router')->generate($routeName, array('SECTION_CODE' => implode('/', $section_codes_array), 'CODE' => $element->getCode()));
				}
				$crumbs[] = $crumbItem;


			}
			//echo '<pre>' . print_r($crumbs, true) . '</pre>';
			if (!isset($params['template_code']) || !$params['template_code'])
				$params['template_code'] = 'default';

			/*
			 * Выдаем результат
			 */
			$response_data = array(
				'items' => $crumbs
			);
			$template = '@templates/' . $currentSite['code'] . '/Crumbs/' . $params['template_code'] . '.html.twig';
			if ($this->container->get('templating')->exists($template) == false) {
				$template = 'NovuscomCMFBundle:DefaultTemplate/Crumbs:default.html.twig';
			}
			$response = $this->container->get('templating')->render($template, $response_data);
			$cacheDriver->save($cacheId, serialize($response));
		}
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
		//echo $time.' мс';
		return $response;
	}

	private function getCrumbsForSection($SECTION_PATH, $route_params, $crumbs, $codes_array)
	{
		$em = $this->em;
		$section_repo = $em->getRepository('NovuscomCMFBundle:Section');
		$logger = $this->logger;
		$SectionClass = $this->container->get('SectionClass');
		$params_params = array();
		if (array_key_exists('params', $route_params['params']))
			$params_params = $route_params['params']['params'];
		$section = $SectionClass->GetSectionByPath(
			$SECTION_PATH, $route_params['params']['BLOCK_ID'],
			$params_params
		);
		$this->sectionByPath = $section;
		if ($section) {
			$logger->info('Нашли раздел');
			$path = $section_repo->getPath($section);
			foreach ($path as $p) {
				$codes_array[] = $p->getCode();
				$crumbs[] = array(
					'url' => $this->container->get('router')->generate('cmf_page_frontend', array('name' => implode('/', $codes_array))),
					'name' => $p->getName(),
				);
			}
		}
		return $crumbs;
	}

	private $sectionByPath;

	public function getCrumbs($page_id)
	{
		$em = $this->em;
		$crumbs = $this->apy_breadcrumb_trail;
		$repo = $em->getRepository('NovuscomCMFBundle:Page');
		$page = $repo->find($page_id);
		$path = $repo->getPath($page);
		$codes_array = array();
		foreach ($path as $p) {
			if ($p->getLvl() == 0) {
				$crumbs->add($p->getName(), 'cmf_page_main');
			} else {
				$codes_array[] = $p->getUrl();
				$crumbs->add($p->getName(), 'cmf_page_frontend', array('name' => implode('/', $codes_array)));
			}
		}
		return $crumbs;
	}


	private $em;
	private $logger;
	private $apy_breadcrumb_trail;
	private $container;

	public function __construct(\Doctrine\ORM\EntityManager $em, Logger $logger, $apyCrumbs, ContainerInterface $container)
	{
		$this->em = $em;
		$this->logger = $logger;
		$this->apy_breadcrumb_trail = $apyCrumbs;
		$this->container = $container;
	}
}