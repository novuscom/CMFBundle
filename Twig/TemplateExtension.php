<?php

namespace Novuscom\Bundle\CMFBundle\Twig;

use Knp\Menu\Loader\NodeLoader;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Monolog\Logger;

use Novuscom\Bundle\CMFBundle\Services\File;
use Novuscom\Bundle\CMFBundle\Services\Utils;


class TemplateExtension extends \Twig_Extension
{

	private $container;
	private $logger;
	protected $urlGenerator;
	protected $doctrine;

	public function __construct(ContainerInterface $container, Logger $logger)
	{
		$this->container = $container;
		$this->logger = $logger;
		$this->doctrine = $container->get('doctrine.orm.entity_manager');
		$this->urlGenerator = $container->get('router');
	}


	public function getFunctions()
	{
		return array(
			'sklon' => new \Twig_Function_Method($this, 'sklonFilter'),
			'typograf' => new \Twig_Function_Method($this, 'typograf'),
			'ElementsList' => new \Twig_Function_Method($this, 'getElementsList'),
			'resize_image' => new \Twig_Function_Method($this, 'ResizeImage'),
			'cmf_menu' => new \Twig_Function_Method($this, 'render'),
			'sections_menu' => new \Twig_Function_Method($this, 'SectionsMenu'),
			'str_repeat' => new \Twig_Function_Method($this, 'StrRepeat'),
			'msg' => new \Twig_Function_Method($this, 'msg'),
			'is_main_page' => new \Twig_Function_Method($this, 'IsMainPage'),
			'IsMainPage' => new \Twig_Function_Method($this, 'IsMainPage'),
			'mainPage' => new \Twig_Function_Method($this, 'IsMainPage'),
			'isMainPage' => new \Twig_Function_Method($this, 'IsMainPage'),
			'ElementsSections' => new \Twig_Function_Method($this, 'ElementsSections'),
		);
	}

	public function ElementsSections($elementsArray)
	{
		$this->logger->info('twig getElementsList' . print_r($elementsArray, true));
		$elementsId = array();
		foreach ($elementsArray as $e) {
			$elementsId[] = $e['id'];
		}
		//echo '<pre>' . print_r($elementsId, true) . '</pre>'; exit;

		$Section = $this->container->get('Section');
		$sections = $Section->ElementsSections($elementsId);
		return $sections;
	}

	public function getGlobals()
	{
		return array(
			'utils' => new Utils($this->logger),
		);
	}


	private function generateMenuFromTree($tree, $menu, $routeCode, $currentCode)
	{
		foreach ($tree as $item) {
			//echo '<pre>' . print_r($item, true) . '</pre>';
			$url = $this->urlGenerator->generate($routeCode, array('CODE' => $item['full_code']));
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

	public function SectionsMenu($options)
	{

		/*
		 * Настроки кеширования
		 */
		$env = $this->container->getParameter("kernel.environment");
		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();
		$namespace = 'menu_section_' . $env . '_' . $options['BLOCK_ID'];
		$cacheDriver->setNamespace($namespace);


		// засекаем время
		$time_start = microtime(1);

		/*
		 * Добавляем текущую страницу в id кеша
		 */
		$request = $this->container->get('request_stack');
		$routeParams = $request->get('_route_params');
		$currentCode = false;
		if (array_key_exists('CODE', $routeParams))
			$currentCode = trim($routeParams['CODE'], '/');
		$options['@currentCode'] = $currentCode;

		// id кеша
		$cacheId = json_encode($options);

		if ($fooString = $cacheDriver->fetch($cacheId)) {
			//if (false) {
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
		echo $result;
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
		//echo $time.' мс';
	}

	public function IsMainPage()
	{
		$request = $this->container->get('request_stack')->getCurrentRequest();
		$routeName = $request->get('_route');
		return ($routeName == 'cmf_page_main');
	}

	public function msg($obj)
	{
		echo '<pre>' . print_r($obj, true) . '</pre>';
	}

	public function StrRepeat($input, $multiplier)
	{
		return str_repeat($input, $multiplier);
	}

	public function render($options)
	{
		$Site = $this->container->get('Site');
		$currentSite = $Site->getCurrentSite();
		$time_start = microtime(1);
		$request = $this->container->get('request_stack')->getCurrentRequest();
		//echo '<pre>' . print_r($request, true) . '</pre>';
		$env = $this->container->getParameter("kernel.environment");
		$routeParams = $request->get('_route_params');
		$options['@request_uri'] = $_SERVER['REQUEST_URI'];
		$cacheId = json_encode($options);
		//$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/menu/');
		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();
		$namespace = 'menu_' . $currentSite['code'] . '_' . $env . '_' . $options['id'];
		$this->logger->info('Menu ' . $options['id'] . ' NameSpace: ' . $namespace);
		$cacheDriver->setNamespace($namespace);
		//if ($fooString = $cacheDriver->fetch($cacheId) /*and $env=='prod'*/) {
		if (false) {
			$result = unserialize($fooString);
		} else {
			$array = $this->getArray($options['id']);
			$result = $this->getMenu($options, $array);
			$cacheDriver->save($cacheId, serialize($result));
		}
		echo $result;
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
		//echo $time.' мс';
	}

	private function getArray($id)
	{
		$repo = $this->doctrine->getRepository('NovuscomCMFBundle:Item');
		$query = $this->doctrine
			->createQueryBuilder()
			->select('node')
			->from('NovuscomCMFBundle:Item', 'node')
			->orderBy('node.root, node.lft', 'ASC')
			->where('node.menu = :menu_id')
			->setParameter('menu_id', $id)
			->getQuery();
		$options = array(
			'decorate' => false
		);
		$tree = $repo->buildTree($query->getArrayResult(), $options);
		return $tree;
		echo '<pre>' . print_r($tree, true) . '</pre>';
		exit;
		$entities = $this->doctrine->getRepository('NovuscomCMFBundle:Item')->findBy(
			array(
				'menu' => $id
			),
			array(
				'lft' => 'asc',
				'sort' => 'asc'
			)
		);
		$items_array = array();
		foreach ($entities as $e) {
			if (preg_match('/^(http|https|ftp):\/\//', $e->getUrl()))
				$url = $e->getUrl();
			else
				$url = $this->urlGenerator->generate('cmf_page_frontend', array('name' => $e->getUrl()));
			$itemArray = array(
				'name' => $e->getName(),
				'url' => $url,
				'lvl' => $e->getLvl(),
				'lft' => $e->getLft(),
				'rgt' => $e->getRgt(),
				'root' => $e->getRoot(),
				'id' => $e->getId(),
				'parent' => null,
			);
			if ($e->getParent()) {
				$itemArray['parent'] = $e->getParent()->getId();
			}
			$items_array[$e->getId()] = $itemArray;
		}
		$this->setMenuByParents($items_array);
		return $items_array;
	}

	private function getMenu($options, $array, $menu = false, $currentItem = false)
	{
		//$array = $this->menuByParents['root'];
		$factory = new MenuFactory();
		if ($menu == false)
			$menu = $factory->createItem('root');
		//echo '<pre>' . print_r($array, true) . '</pre>';
		//exit;
		foreach ($array as $e) {
			if (preg_match('/^(http|https|ftp):\/\//', $e['url']))
				$url = $e->getUrl();
			else
				$url = $this->urlGenerator->generate('cmf_page_frontend', array('name' => $e['url']));
			$item = $menu->addChild($e['name'], array('uri' => $url, 'attributes' => array(
				//'data-url' => $e['url'],
				//'data-uri' => $_SERVER['REQUEST_URI'],
			)));
			if ($_SERVER['REQUEST_URI'] == '' . $e['url'] . '') {
				//echo '<pre>' . print_r($_SERVER['REQUEST_URI'], true) . '</pre>';
				//echo '<pre>' . print_r($e['url'], true) . '</pre>';
				$item->setCurrent(true);
			}
			if ($e['__children']) {
				$currentItem = $e;
				$this->getMenu($options, $e['__children'], $item, $currentItem);
			}
		}
		if (!array_key_exists('template', $options)) {
			$options['template'] = 'default.html.twig';
		}
		$menuRenderer = $this->getMenuRenderer();
		$result = $menuRenderer->render($menu, array(
			'template' => $options['template'], 'currentAsLink' => false));
		return $result;
	}

	private function getMenuRenderer()
	{
		$Site = $this->container->get('Site');
		$currentSite = $Site->getCurrentSite();
		$request = $this->container->get('request_stack')->getCurrentRequest();
		$routeName = $request->get('_route');
		$routeParams = $request->get('_route_params');
		$twigLoader = new \Twig_Loader_Filesystem(array(
			__DIR__ . '/../../../../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views',
			__DIR__ . '/../../../../templates/' . $currentSite['code'] . '/Menu',
		));
		$twig = new \Twig_Environment($twigLoader);
		$itemMatcher = new Matcher();
		$menuRenderer = new \Knp\Menu\Renderer\TwigRenderer($twig, 'knp_menu.html.twig', $itemMatcher);
		return $menuRenderer;
	}


	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('site', array($this, 'siteFilter')),
		);
	}

	public function ResizeImage($path, $filter, $size)
	{
		$file = new File($this->container);
		$result = $file->ResizeImage($path, $filter, $size);
		return $result;
	}

	/**
	 * Типограф для сайта "универсальный". Для большинства полей
	 * @param string $text Входящий текст, который надо оттипографить
	 * @return string Оттипографленный текст
	 */
	public function typograf($text)
	{
		$stringTags = 'strong|span';
		$new_text = stripslashes($text);
		/**
		 * &quot; заменяем на "
		 */
		$new_text = preg_replace("/&quot;/u", "\"", $new_text);
		/**
		 * Ставит тире вместо дефиса
		 */
		$new_text = preg_replace("/(\s)(-)(\s)/u", "&#160;&#8212;\\3", $new_text);
		$new_text = preg_replace('/alt=""/', 'alt="0"', $new_text);
		/**
		 * Выделение всех кавычек
		 */
		$new_text = preg_replace("/(\".+?\")/u", "[\\0]", $new_text);
		/**
		 * Удаление квадратных скобок из тегов
		 */
		$new_text = preg_replace("/=\"\](.+?)\[\"/", "=\"\\1\"", $new_text);
		$new_text = preg_replace("/=\[\"(.+?)\"\]/", "=\"\\1\"", $new_text);
		/**
		 * Левая кавычка первого уровня
		 */
		$new_text = preg_replace("/(\[\")([^\.\s])/u", "&#171;\\2", $new_text);
		/**
		 * Правая кавычка первого уровня
		 */
		$new_text = preg_replace("/([^\s])(\"\])/u", "\\1&#187;", $new_text);
		/**
		 * Правая кавычка второго уровня
		 */
		$new_text = preg_replace("/([^\.\s])(\[\")/u", "\\1&#147;", $new_text);
		/**
		 * Левая кавычка второго уровня
		 */
		$new_text = preg_replace("/(\"\])([^\.\s])/u", "&#132;\\2", $new_text);
		$new_text = preg_replace('/alt="0"/', 'alt=""', $new_text);
		/**
		 * Убираем пробел перед закрытием строкового тега
		 */
		$new_text = preg_replace("/(\s|&nbsp;)+\<\/(" . $stringTags . ")\>(\w)?/u", "</\\2> \\3", $new_text);
		/**
		 * Неразрывный пробел после предлогов и союзов
		 */
		$new_text = preg_replace("/(\s)([а-я]{1,2})(\s)/u", "\\1\\2&#160;", $new_text);
		/**
		 * Тире вместо дефиса, короткого тире и т. д.
		 */
		$new_text = preg_replace("/(\s)(-|&ndash;)(\s)/u", "&#160;&#8212;\\3", $new_text);
		/**
		 * Диапозон чисел
		 */
		$new_text = preg_replace("/([0-9])\s*-\s*([0-9])/u", "\\1&#8211;\\2", $new_text);

		/**
		 *
		 */
		$new_text = str_replace("&nbsp;-&nbsp;", "&nbsp;&mdash; ", $new_text);

		return $new_text;
	}

	public function sklonFilter($n, $forms)
	{
		return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
	}


	public function getElementsList($params)
	{
		$this->logger->info('twig getElementsList' . print_r($params, true));
		$defaultParams = array(
			'FIELDS' => array(),
			'PROPERTIES' => array(),
			'FILTER' => array(),
			'LIMIT' => false,
			'ORDER' => array(),
		);
		$params = array_merge($defaultParams, $params);
		$ElementsList = $this->container->get('ElementsList');
		$ElementsList->setBlockId($params['BLOCK_ID']);
		$ElementsList->setSelect($params['FIELDS']);
		$ElementsList->selectProperties($params['PROPERTIES']);
		$ElementsList->setFilter($params['FILTER']);
		$ElementsList->setLimit($params['LIMIT']);
		$ElementsList->setOrder($params['ORDER']);
		$elements = $ElementsList->getResult();
		return $elements;
	}

	public function siteFilter($url)
	{
		$result = $url;
		preg_match('/^https?:\/\/([^\/]*)/', $url, $matches);
		if ($matches[1]) {
			$result = $matches[1];
		}
		return $result;
	}

	public function getName()
	{
		return 'cmf_extension';
	}
}