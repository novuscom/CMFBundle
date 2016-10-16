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
		return $response;
	}


	public function closedAction()
	{
		$site = $this->getSite();
		$response = $this->render('NovuscomCMFBundle:Status:Closed.html.twig');
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
			$msg = 'Сайт не найден';
			//$logger->alert($msg);
			//throw $this->createNotFoundException($msg);
			$template = $this->getTemplate(false, '404');
			$response = $this->render($template, array('message' => $msg));
			$response->setStatusCode(404);
			return $response;
		}
		$securityContext = $this->container->get('security.authorization_checker');
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
							$response = $this->render($template, array('message' => 'Страница не найдена'));
							$response->setStatusCode(404);
							return $response;
						}
						$page = $this->getPageArray($page);
						$pageTemplate = $page->getTemplate(false, $page->getTemplate());
						$response = $this->render($pageTemplate,
							array(
								'page' => $page,
								'title' => $page->getTitle(),
								'header' => $page->getHeader(),
								'keywords' => $page->getKeywords(),
								'description' => $page->getDescription(),
								'site' => $site,
							)
						);
						$cacheDriver->save($cacheId, serialize($response));
					}
				} else {
					if ($fooString = $cacheDriver->fetch($cacheId)) {
						$logger->info('страница найдена в кеше по id=' . $cacheId);
						$page = unserialize($fooString);
					} else {
						$logger->info('Страница в кеше не найдена ' . $cacheId);
						$Page = $this->get('Page');
						$page = $Page->findPage($name);
						if (!$page) {
							$logger->notice('Не найдена страница', array('name' => $name));
							$template = $this->getTemplate($site['code'], '404');
							$response = $this->render($template, array('message' => 'Страница не найдена'));
							$response->setStatusCode(404);
							return $response;
						}
						$page = $this->getPageArray($page);
						$cacheDriver->save($cacheId, serialize($page));
					}
				}
			} else {
				$logger->debug('страница не найдена в кеше');
				$Page = $this->get('Page');
				$page = $Page->findPage($name);
				if (!$page) {
					$this->get('logger')->debug('главная странциа не найдена');
					$template = $this->getTemplate($site['code'], '404');
					$response = $this->render($template, array('message' => 'Страница не найдена'));
					$response->setStatusCode(404);
					return $response;
				}
			}

			$pageTemplate = $this->getTemplate(false, $page->getTemplate());
			$logger->debug('формирование ответа');
			$options = array(
				'page' => $page,
				'content' => $page->getContent(),
				'title' => $page->getTitle(),
				'header' => $page->getHeader(),
				'keywords' => $page->getKeywords(),
				'description' => $page->getDescription(),
				'site' => $site,
			);
			$logger->debug('создание ответа');
			$response = $this->render($pageTemplate, $options);


		}
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
		//if ($site['id'] == 14)
		//echo '<!--indexAction() ' . $time . ' мс-->';
		$logger->debug('возвращение ответа');
		return $response;
	}

	private function getTemplate($templateDir = false, $templateCode = false)
	{
		if (!trim($templateCode))
			$templateCode = 'default';
		$site = $this->getSite();
		$template = '@templates/' . $site['code'] . '/Pages/' . $templateCode . '.html.twig';
		if ($this->get('templating')->exists($template) == false) {
			$template = 'NovuscomCMFBundle:DefaultTemplate/Pages:' . $templateCode . '.html.twig';
		}
		return $template;
	}
}
