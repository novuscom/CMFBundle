<?php

namespace Novuscom\CMFBundle\Twig;

use Knp\Menu\Loader\NodeLoader;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Monolog\Logger;
use Novuscom\CMFBundle\Services\File;
use Novuscom\CMFBundle\Services\Utils;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


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
			'menu' => new \Twig_Function_Method($this, 'render'),
			'sections_menu' => new \Twig_Function_Method($this, 'SectionsMenu'),
			'str_repeat' => new \Twig_Function_Method($this, 'StrRepeat'),
			'msg' => new \Twig_Function_Method($this, 'msg'),
			'is_main_page' => new \Twig_Function_Method($this, 'IsMainPage'),
			'IsMainPage' => new \Twig_Function_Method($this, 'IsMainPage'),
			'mainPage' => new \Twig_Function_Method($this, 'IsMainPage'),
			'isMainPage' => new \Twig_Function_Method($this, 'IsMainPage'),
			'ElementsSections' => new \Twig_Function_Method($this, 'ElementsSections'),
			'format_number' => new \Twig_Function_Method($this, 'NumFormat'),
			'crumbs' => new \Twig_Function_Method($this, 'Breadcrumbs'),
			'youtubeLinkInfo' => new \Twig_Function_Method($this, 'GetYoutubeLinkInfo'),
		);
	}

	/**
	 * Генерирует информацию о youtube ролике
	 * link - ссылка для fancybox и iframe, например
	 * code - код ролика (мало ли для чего пригодится)
	 * img - "скриншот" видео-ролика в высоком разрешении
	 * @param string $url Ссылка на ролик
	 * @return array
	 */
	public static function GetYoutubeLinkInfo($url)
	{
		$result = array();
		if (preg_match('/watch\?v=([^&]*)/ui', $url, $matches)) {
			$result['link'] = '//www.youtube.com/embed/' . $matches[1] . '?wmode=opaque';
			$result['code'] = $matches[1];
			$result['img'] = 'http://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
		};
		return $result;
	}

	public function BreadCrumbs(array $options = array())
	{
		$Crumbs = $this->container->get('Crumbs');
		$res = $Crumbs->getForSite($options);
		echo $res;
		//return $res;
	}

	public static function NumFormat($number, $decimals = 0)
	{
		$thousands_sep = '&nbsp;';
		if (phpversion() < '5.4') {
			$thousands_sep = ' ';
		}
		return html_entity_decode(number_format($number, $decimals, '.', $thousands_sep));
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
			$url = $this->urlGenerator->generate($routeCode, array('SECTION_CODE' => $item['full_code']));
			//$url = $this->urlGenerator->generate($routeCode, array('CODE' => $item['full_code']));
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
		$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
		$namespace = 'menu_section_' . $env . '_' . $options['BLOCK_ID'];
		$cacheDriver->setNamespace($namespace);


		// засекаем время
		$time_start = microtime(1);

		/*
		 * Добавляем текущую страницу в id кеша
		 */
		$request = $this->container->get('request_stack')->getMasterRequest();
		$routeParams = $request->attributes->get('_route_params');
		$currentCode = false;
		if (array_key_exists('SECTION_CODE', $routeParams))
			$currentCode = trim($routeParams['SECTION_CODE'], '/');
		//$currentCode = trim($routeParams['CODE'], '/');
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
		$env = $this->container->getParameter("kernel.environment");
		$options['@request_uri'] = $request->getPathInfo();
		$namespace = 'menu_' . $currentSite['code'] . '_' . $env . '_' . $options['id'];
		$this->logger->info('Menu ' . $options['id'] . ' NameSpace: ' . $namespace);
		$array = $this->getArray($options['id']);
		$result = $this->getMenu($options, $array);
		echo $result;
		$time_end = microtime(1);
		$time = number_format((($time_end - $time_start) * 1000), 2);
	}

	private function getArray($id)
	{
		$repo = $this->doctrine->getRepository('NovuscomCMFBundle:Item');
		$query = $this->doctrine
			->createQueryBuilder()
			->select('node')
			->from('NovuscomCMFBundle:Item', 'node')
			->orderBy('node.root, node.lft, node.sort', 'ASC')
			->where('node.menu = :menu_id')
			->setParameter('menu_id', $id)
			->getQuery();
		$options = array(
			'decorate' => false
		);
		$tree = $repo->buildTree($query->getArrayResult(), $options);
		return $tree;
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
			$e['name'] = html_entity_decode($this->typograf($e['name']));
			if (preg_match('/^(http|https|ftp):\/\//', $e['url']))
				$url = $e['url'];
			else if (preg_match('/\.(html|xml|php|htm)$/', $e['url']))
				$url = $this->urlGenerator->generate('cmf_page_frontend_clear', array('name' => $e['url']));
			else if ($e['url'] == '/')
				$url = $this->urlGenerator->generate('main');
			else
				$url = $this->urlGenerator->generate('cmf_page_frontend', array('name' => $e['url']));
			$item = $menu->addChild($e['name'], array('uri' => $url, 'attributes' => array(
				//'data-url' => $e['url'],
				//'data-uri' => $_SERVER['REQUEST_URI'],
			)));
			$pathInfo = $this->container->get('request_stack')->getCurrentRequest()->getRequestUri();
			//echo '<pre>' . print_r($pathInfo->getRequestUri(), true) . '</pre>';
			//echo '<pre>' . print_r($url, true) . '</pre>';
			if ($pathInfo == '' . $url . '') {

				$item->setCurrent(true);
			}

			/**
			 * Здесь делаем проверку параметров пункта меню, и если есть привязки - генерируем дерево по принципу getArray()
			 */


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
			$this->container->get('kernel')->getRootDir() . '/../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views',
			$this->container->get('kernel')->getRootDir() . '/../templates/' . $currentSite['code'] . '/Menu',
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
			'ID' => array(),
			'FILTER_PROPERTIES' => array(),
		);
		$params = array_merge($defaultParams, $params);
		$ElementsList = $this->container->get('ElementsList');
		$ElementsList->setBlockId($params['BLOCK_ID']);
		$ElementsList->setSelect($params['FIELDS']);
		$ElementsList->selectProperties($params['PROPERTIES']);
		$ElementsList->setFilter($params['FILTER']);
		$ElementsList->setLimit($params['LIMIT']);
		$ElementsList->setOrder($params['ORDER']);
		$ElementsList->setIdArray($params['ID']);
		$ElementsList->setFilterProperties($params['FILTER_PROPERTIES']);
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