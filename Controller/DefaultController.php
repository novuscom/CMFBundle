<?php

namespace Novuscom\CMFBundle\Controller;


use Novuscom\CMFBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Liip\ImagineBundle\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;


class DefaultController extends Controller
{

	private function getPageByUrl($url)
	{
		//echo '<pre>' . print_r('getPageByUrl()', true) . '</pre>';
		$request = $this->container->get('request');
		$params = $request->get('_route_params');
		//echo '<pre>' . print_r($params, true) . '</pre>';
		//echo '<pre>' . print_r($routeName = $request->get('_route'), true) . '</pre>';
		//echo '<pre>' . print_r($this->container->get('request')->getPathInfo(), true) . '</pre>';
		$em = $this->getDoctrine()->getManager();
		$site = $this->getSite();

		$name = preg_replace('/^\/(.+?)\/$/', '\\1', $url);
		//$codeArray = array('/');
		//$ca = explode('/', $name);
		$codeArray = explode('/', $name);
		//$codeArray = array_merge($codeArray, $ca);
		//$codeArray = array_slice($codeArray, 1, -1, true);
		$maxLevel = count($codeArray);
		//print_r($codeArray);
		//echo '<br/>';
		$er = $em->getRepository('NovuscomCMFBundle:Page');

		$root = $er->findOneBy(array(
			'site' => $site,
			'url' => $codeArray[0],
			'lvl' => 1
		));


		if (!$root) {
			throw $this->createNotFoundException('Страница не найдена');
		}

		if ($maxLevel > 1) {

			$pages = $er->createQueryBuilder('p')
				->where("p.site=:site")
				->andWhere("p.lft>:left")
				->andWhere("p.rgt<:right")
				->andWhere("p.lvl<=:level")
				->andWhere("p.url IN(:url)")
				//->andWhere("p.url=:url")
				->setParameters(array(
					'site' => $site,
					'left' => $root->getLft(),
					'right' => $root->getRgt(),
					'level' => $maxLevel,
					'url' => $codeArray
				))
				//->setMaxResults(1)
				->getQuery()
				->getResult();
			$pagesCount = count($pages);

			//echo '<pre>' . print_r($pagesCount, true) . '</pre>';

			$selectedCodes = array(
				1 => $root->getUrl()
			);
			foreach ($pages as $i => $p) {
				$selectedCodes[] = $p->getUrl();

			}
			$n = $pagesCount - 1;
			$page = $pages[$n];
		} else {
			$page = $root;
		}
		//echo '<pre>dasdadad ' . print_r($page->getName(), true) . '</pre>';
		return $page;
	}


	public function robotsAction(Request $request)
	{
		$host = $request->headers->get('host');
		$cacheId = 'robots_txt';
		$env = $this->get('kernel')->getEnvironment();
		$cache = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $host . '/etc/');
		//$cache = new \Doctrine\Common\Cache\ApcCache();
		if ($fooString = $cache->fetch($cacheId)) {
			$response = unserialize($fooString);
		} else {
			$site = $this->getSite();
			if (!$site) {
				$response = $this->render('CMFTemplateBundle:Status:404.html.twig');
				$response->setStatusCode(404);
				return $response;
			}
			$response = new Response();
			$response->headers->set('Content-Type', 'text/plain');
			$response->sendHeaders();
			$response->setContent($site['robots_txt']);
			$cache->save($cacheId, serialize($response));
		}

		return $response;

	}


	public function closedAction()
	{
		$site = $this->getSite();
		$response = $this->render('CMFTemplateBundle:Closed:site_' . $site['id'] . '.html.twig');
		$response->setStatusCode(403);
		return $response;
	}

	private $site;

	private function getSite()
	{
		if (!$this->site)
			$this->setSite();
		return $this->site;
	}

	private function setSite()
	{
		$Site = $this->get('Site');
		$this->site = $Site->getCurrentSite();
	}

	public function indexAction($name = false, Request $request)
	{
		$time_start = microtime(1);

		$logger = $this->get('logger');
		$logger->info('indexAction page controller start');
		/**
		 * Проверяем закрыт сайт или нет
		 */

		$site = $this->getSite();
		if (!$site) {
			$msg = 'Сайт не найден по хосту ' . $request->getHost();
			$logger->alert($msg);
			throw $this->createNotFoundException($msg);
		}
		$securityContext = $this->container->get('security.context');
		if ($site['closed'] && !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			$response = $this->forward('NovuscomCMFBundle:Default:closed', array());
		} else {


			$cacheId = $site['id'] . '/' . $name;
			$cacheDriver = $this->getCacheDriver();
			$cached = false;
			if ($cached) {
				$cacheVariant = false;
				if ($cacheVariant) {
					//if (false) {
					if ($fooString = $cacheDriver->fetch($cacheId)) {
						$logger->info('страница найдена в кеше по id=' . $cacheId);
						$response = unserialize($fooString);
					} else {
						$logger->info('страница НЕ найдена в кеше по id=' . $cacheId);
						$Page = $this->get('Page');
						$page = $Page->findPage($name);
						if (!$page) {
							$logger->notice('Не найдена страница', array('name' => $name));
							$template = $this->getTemplate($site['code'], '404');
							$response = $this->render($template);
							$response->setStatusCode(404);
							return $response;
						}
						$page = $this->getPageArray($page);
						$pageTemplate = $page->getTemplate();
						$response = $this->render($pageTemplate,
							array(
								'page' => $page,
								'title' => $page->getTitle(),
								'header' => $page->getHeader(),
								'keywords' => $page->getKeywords(),
								'description' => $page->getDescription()
							)
						);
						$cacheDriver->save($cacheId, serialize($response));
					}
				} else {
					if ($fooString = $cacheDriver->fetch($cacheId)) {
						$logger->info('страница найдена в кеше по id=' . $cacheId);
						$page = unserialize($fooString);
					} else {
						$logger->info('Страница в кеше не найдена '.$cacheId);
						$Page = $this->get('Page');
						$page = $Page->findPage($name);
						if (!$page) {
							$logger->notice('Не найдена страница', array('name' => $name));
							$template = $this->getTemplate($site['code'], '404');
							$response = $this->render($template);
							$response->setStatusCode(404);
							return $response;
						}
						$page = $this->getPageArray($page);
						$cacheDriver->save($cacheId, serialize($page));
					}
				}
			}
			else {
				$Page = $this->get('Page');
				$page = $Page->findPage($name);
				if (!$page) {
					$template = $this->getTemplate($site['code'], '404');
					$response = $this->render($template);
					$response->setStatusCode(404);
					return $response;
				}
			}
			$page = $this->getPageArray($page);
			$pageTemplate = $page->getTemplate();
			$response = $this->render($pageTemplate,
				array(
					'page' => $page,
					'title' => $page->getTitle(),
					'header' => $page->getHeader(),
					'keywords' => $page->getKeywords(),
					'description' => $page->getDescription()
				)
			);


		}
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
		//if ($site['id'] == 14)
		//echo '<!--indexAction() ' . $time . ' мс-->';
		return $response;
	}

	private function getTemplate($templateDir, $templateCode)
	{
		return $template = '@templates/' . $templateDir . '/Pages/' . $templateCode . '.html.twig';
	}

	private function getPageArray($page)
	{
		$site = $this->getSite();
		$twig = new \Twig_Environment(new \Twig_Loader_String());
		$twig->addExtension(new \Symfony\Bridge\Twig\Extension\HttpKernelExtension($this->get('fragment.handler')));

		$rendered = '';

		$rendered = $twig->render(
			$page->getContent(),
			array()
		);
		$page->setContent($rendered);
		$template = $this->getTemplate($site['code'], 'default');


		if (!$templateName = $page->getTemplate()) {
			//echo '<pre>' . print_r('не было шаблона', true) . '</pre>';
			$page->setTemplate($template);
		} else {
			$page->setTemplate($this->getTemplate($site['code'], $templateName));
		}

		/**
		 * Рендерим контент страницы
		 */
		$twig = new \Twig_Environment(new \Twig_Loader_String());
		$twig->addExtension(new \Symfony\Bridge\Twig\Extension\HttpKernelExtension($this->get('fragment.handler')));
		$rendered = $twig->render(
			$page->getContent(),
			array(
				//'_route_params' => $routeParams,
				//'_get_params'=>$this->getRequest()->query->all();
				//'_post_params'=>$this->getRequest()->request->all();
			)
		);

		//echo '<pre>' . print_r($page->getTitle(), true) . '</pre>'; exit;

		$new_page = new Page();
		$new_page->setContent($rendered);
		$new_page->setController($page->getController());
		$new_page->setDescription($page->getDescription());
		$new_page->setHeader($page->getHeader());
		$new_page->setName($page->getName());
		$new_page->setUrl($page->getUrl());
		$new_page->setTitle($page->getTitle());
		$new_page->setTemplate($page->getTemplate());
		$new_page->setRoot($page->getRoot());

		return $new_page;
	}


	private function getCacheDriver()
	{
		//$redis = new Redis();
		//$redis->connect('127.0.0.1', 6379);
		$site = $this->getSite();
		$env = $this->get('kernel')->getEnvironment();
		//$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $site['id'] . '/pages/');
		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();
		//$cacheDriver = new \Doctrine\Common\Cache\RedisCache();
		$cacheDriver->setNamespace('Pages_' . $env);
		return $cacheDriver;
	}

	private function getCacheId($page_id)
	{
		$cacheId = 'page_' . $page_id;
		return $cacheId;
	}

	private function GetById($id)
	{
		$em = $this->getDoctrine()->getManager();
		$cacheDriver = $this->getCacheDriver();
		$cacheId = $this->getCacheId($id);
		$repo = $em->getRepository('NovuscomCMFBundle:Page');
		if ($fooString = $cacheDriver->fetch($cacheId)) {
			$page = unserialize($fooString);
		} else {
			$page = $repo->find($id);
			$page = $this->getPageArray($page);
			$cacheDriver->save($cacheId, serialize($page));
		}
		return $page;
	}
}