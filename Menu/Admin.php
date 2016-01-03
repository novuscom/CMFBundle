<?php
namespace Novuscom\CMFBundle\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Novuscom\CMFBundle\Event\ConfigureMenuEvent;

class Admin extends ContainerAware
{

	public function MainMenu(FactoryInterface $factory, array $options)
	{
		$em = $this->container->get('doctrine.orm.entity_manager');


		$request = $this->container->get('request');
		$routeName = $request->get('_route');
		$routeParams = $request->get('_route_params');

		//echo '<pre>' . print_r($routeName, true) . '</pre>';
		//echo '<pre>' . print_r($routeParams, true) . '</pre>';

		$siteIdExist = array_key_exists('site_id', $routeParams);


		$User = $this->container->get('User');
		$sites = $User->getUserSites();


		/*
		 * Создаем меню
		 */
		$menu = $factory->createItem('root');
		$menu->setChildrenAttribute('class', 'sidebar-menu');

		$menu->addChild('Пользователи', array(
			'route' => 'admin_user'
		));

		$pagesMenu = $menu->addChild('Страницы', array(
			'route' => 'cmf_admin_site_list'
		))->setCurrent(true);
		$pagesMenu->setAttribute('class', 'treeview'); // для li
		if ($routeName == 'cmf_admin_site_pages') {
			$pagesMenu->setAttribute('class', 'active');
			//$pagesMenu->setCurrent(true);
		}
		$pagesMenu->setChildrenAttribute('class', 'treeview-menu');// для ul

		$blocksList = $menu->addChild('Инфоблоки', array(
			'route' => 'admin_block'
		));
		if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
			$menu->addChild('Группы пользователей', array(
				'route' => 'admin_group'
			));
			$menu->addChild('Сайты', array(
				'route' => 'cmf_admin_site_list'
			));

			$menu->addChild('Маршруты', array(
				'route' => 'admin_route'
			));

			$menu->addChild('Заказы', array(
				'route' => 'admin_order'
			));
			$menu->addChild('Поисковые запросы', array(
				'route' => 'admin_searchquery'
			));
		}

		$blocks = $em->getRepository('NovuscomCMFBundle:Block')->findBySites($sites);
		$blocksList->setAttribute('class', 'treeview'); // для li
		if ($routeName == 'admin_block' || $routeName == 'admin_block_show') {
			$blocksList->setAttribute('class', 'active');
		}
		$blocksList->setChildrenAttribute('class', 'treeview-menu');// для ul
		$adminBlockShow = ($routeName == 'admin_block_show');
		foreach ($blocks as $e) {
			$blockItem = $blocksList->addChild($e->getName(), array(
				'route' => 'admin_block_show',
				'routeParameters' => array('id' => $e->getId())
			));
			if ($adminBlockShow && $e->getId() == $routeParams['id']) {
				$blockItem->setAttribute('class', 'active');
			}
		}

		$menuBlock = $menu->addChild('Меню', array(
			'route' => 'cmf_admin_site_list'
		));
		$menuBlock->setAttribute('class', 'treeview'); // для li
		$menuBlock->setChildrenAttribute('class', 'treeview-menu');// для ul

		foreach ($sites as $e) {
			$pageItem = $pagesMenu->addChild($e['name'], array(
				'route' => 'cmf_admin_site_pages',
				'routeParameters' => array('site_id' => $e['id'])
			));
			if ($siteIdExist && $e['id'] == $routeParams['site_id']) {
				$pageItem->setAttribute('class', 'active');
			}
			$menuBlockItem = $menuBlock->addChild($e['name'], array(
				'route' => 'admin_menu',
				'routeParameters' => array('site_id' => $e['id'])
			));
		}

		$menu->addChild('Система', array(
			'route' => 'admin_system'
		));


		/*
		 * Создаем событие
		 */
		$this->container->get('event_dispatcher')->dispatch(
			ConfigureMenuEvent::CONFIGURE,
			new ConfigureMenuEvent($factory, $menu)
		);
		return $menu;
	}

}