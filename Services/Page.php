<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Novuscom\CMFBundle\Services\Site;
use Monolog\Logger;

class Page
{
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

	private function getCacheId($page_id)
	{
		$cacheId = 'page_' . $page_id;
		return $cacheId;
	}

	private function getCacheDriver()
	{
		//$redis = new Redis();
		//$redis->connect('127.0.0.1', 6379);
		$site = $this->getSite();
		//$env = $this->get('kernel')->getEnvironment();
		$env = 'test';
		//$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $site['id'] . '/pages/');
		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();
		//$cacheDriver = new \Doctrine\Common\Cache\RedisCache();
		$cacheDriver->setNamespace('Pages_' . $env);
		return $cacheDriver;
	}

	public function GetById($id)
	{
		$em = $this->entityManager;
		$cacheDriver = $this->getCacheDriver();
		$cacheId = $this->getCacheId($id);
		$repo = $em->getRepository('NovuscomCMFBundle:Page');
		$page = $repo->find($id);

		return $page;
	}

	private $site;

	private function getSite()
	{
		if (!$result = $this->site)
			$result = $this->Site->getCurrentSite();
		return $result;
	}

	public function getRoot()
	{
		$this->logger->debug('Получение главной страницы сайта');
		$site = $this->getSite();
		$root = $this->repo->findOneBy(array(
			'site' => $site['id'],
			'lvl' => 0
		));
		return $root;
	}

	public function findPage($name)
	{
		$this->logger->debug('Поиск страницы по имени');
		$page = false;
		$em = $this->entityManager;
		$er = $this->repo;
		$site = $this->getSite();

		if ($name) {
			$codeArray = explode('/', preg_replace('/^\/?(.+?)\/?$/', '\\1', $name));
			$maxLevel = count($codeArray);
			/*if (preg_match('/^(\/)(.+?)(\/)$/', $name)) {
				//throw $this->createNotFoundException('Слэш в начале');
				$this->setExceptionText('Слэш в начале');
			};*/
			$root = $this->getRoot();

			if (!$root) {
				return false;
				//throw $this->createNotFoundException('Страница не найдена');
				//$this->setExceptionText('Страница не найдена');
			}
			if ($maxLevel > 0) {
				$pages_by_last_code = $er->createQueryBuilder('p')
					->where("p.site=:site")
					->andWhere("p.lft>:left")
					->andWhere("p.rgt<:right")
					->andWhere("p.url=:url")
					->setParameters(array(
						'site' => $em->getReference('Novuscom\CMFBundle\Entity\Site', $site['id']),
						'left' => $root->getLft(),
						'right' => $root->getRgt(),
						'url' => $codeArray[$maxLevel - 1]
					))
					->orderBy('p.lft', 'ASC')
					->getQuery()
					->getResult();
				$pages_by_id = array();
				$paths = array();
				$paths_array = array();
				foreach ($pages_by_last_code as $p) {
					$pages_by_id[$p->getId()] = $p;
					$path = $er->getPath($p);
					$path_id = array();
					foreach ($path as $pa) {
						$path_id[] = $pa->getUrl();
						$paths_array[$p->getId()][] = $pa;
					}
					$paths[$p->getId()] = $path_id;
				}
				$page_id = false;
				foreach ($paths as $id => $array) {
					array_shift($array);
					if ($array == $codeArray) {
						$page_id = $id;
						break;
					}
				}
				if (!$page_id) {
					//throw $this->createNotFoundException('Страница не найдена (путь не найден)');
					//$this->setExceptionText('Страница не найдена (путь не найден)');
				} else
					$page = $pages_by_id[$page_id];


			} else {
				$page = $root;
			}
		} else {
			$page = $em->getRepository('NovuscomCMFBundle:Page')->findOneBy(array(
				'site' => $site['id'],
				'lvl' => 0
			));
		}
		return $page;
	}

	private $entityManager;
	private $Site;
	private $logger;
	private $repo;

	public function __construct(EntityManager $entityManager, Logger $logger, Site $site)
	{
		$this->entityManager = $entityManager;
		$this->Site = $site;
		$this->logger = $logger;
		$this->repo = $entityManager->getRepository('NovuscomCMFBundle:Page');
	}
}