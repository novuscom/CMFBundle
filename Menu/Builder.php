<?php
namespace Novuscom\CMFBundle\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Novuscom\CMFBundle\Event\ConfigureMenuEvent;

class Builder extends ContainerAware
{
    
    public function BaseMenu(FactoryInterface $factory, array $options)
    {


        $em = $this->container->get('doctrine.orm.entity_manager');


        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');


        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $routeParams = $request->get('_route_params');
        //echo '<pre>'.print_r($params, true).'</pre>';
        //echo '<pre>'.print_r($routeName, true).'</pre>';


        $menuArray = array(
            'Главная' => array(
                'route' => 'cmf_page_main',
                'name' => 'Главная'
            ),
            'О салоне' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'about')
            ),
            'Новости' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'news')
            ),
            'Продукция и услуги' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'products')
            ),
            'Галерея' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'gallery')
            ),
            'Контактная информация' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'contacts')
            )

        );

        foreach ($menuArray as $key => $ma) {
            $item = $menu->addChild($key, $ma);
            if (array_key_exists('routeParameters', $ma) && $routeParams['name'] == $ma['routeParameters']['name'] . '/') {
                $item->setCurrent(true);
            }
        }
        return $menu;
    }

    public function PagesMenu(FactoryInterface $factory, array $options)
    {


        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:Site')->findAll();

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');


        $usr = $this->container->get('security.context')->getToken()->getUser();
        $group = $usr->getGroups()->map(function ($entity) {
            return $entity->getId();
        })->toArray();
        //echo '<pre>' . print_r($group, true) . '</pre>';
        if ($entities) {
            foreach ($entities as $e) {
                //echo '<pre>' . print_r($e->getId(), true) . '</pre>';
                /*if (in_array(3, $group) && $e->getId()==11) {

                }
                else {
                    continue;
                }*/
                $menu->addChild($e->getName(), array(
                    'route' => 'cmf_admin_site_pages',
                    'routeParameters' => array('site_id' => $e->getId())
                ));
            }
        }
        return $menu;
    }

    /**
     * Ссылки на меню сайтов
     * @param FactoryInterface $factory
     * @param array $options
     * @return mixed
     */
    public function MenuMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:Site')->findAll();
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');
        if ($entities) {


            $usr = $this->container->get('security.context')->getToken()->getUser();
            $group = $usr->getGroups()->map(function ($entity) {
                return $entity->getId();
            })->toArray();

            foreach ($entities as $e) {

                /* if ((in_array(3, $group) && $e->getId()==11)==false){
                     continue;
                 }*/

                $menu->addChild($e->getName(), array(
                    'route' => 'admin_menu',
                    'routeParameters' => array('site_id' => $e->getId())
                ));
            }
        }
        return $menu;
    }

    public function BlocksGroupsTreeMenu(FactoryInterface $factory, array $options)
    {

        //echo '<pre>' . print_r('BlocksGroupsTreeMenu()', true) . '</pre>';
        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:BlockGroup')->findAll();
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');
        $m = array();
        foreach ($entities as $e) {
            $m['code'] = $menu->addChild($e->getName(), array(
                'route' => 'admin_blockgroup_show',
                'routeParameters' => array('id' => $e->getId())
            ));
            //echo '<pre>' . print_r($e->getName(), true) . '</pre>';

        }
        return $menu;
    }

    public function BlocksGroupsMenu(FactoryInterface $factory, array $options)
    {


        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:BlockGroup')->findAll();
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'treeview-menu');
        foreach ($entities as $e) {
            $menu->addChild($e->getName(), array(
                'route' => 'admin_blockgroup_show',
                'routeParameters' => array('id' => $e->getId())
            ));
        }
        return $menu;
    }


    public function SitesMenu(FactoryInterface $factory, array $options)
    {


        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:Site')->findAll();

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');

        $usr = $this->container->get('security.context')->getToken()->getUser();
        $group = $usr->getGroups()->map(function ($entity) {
            return $entity->getId();
        })->toArray();


        if ($entities) {
            foreach ($entities as $e) {
                $menu->addChild($e->getName(), array(
                    'route' => 'cmf_admin_site_show',
                    'routeParameters' => array('id' => $e->getId())
                ));
            }
        }


        return $menu;
    }

    public function BlocksMenu(FactoryInterface $factory, array $options)
    {


        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:Block')->findAll();

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo '<pre>' . print_r($routeName, true) . '</pre>';
        if ($routeName == 'admin_block_show' || $routeName == 'admin_block_show_section') {

            $menu->setChildrenAttribute('style', 'display:block;');
        }

        $usr = $this->container->get('security.context')->getToken()->getUser();
        $group = $usr->getGroups()->map(function ($entity) {
            return $entity->getId();
        })->toArray();

        if ($entities) {
            foreach ($entities as $e) {
                /*if ((in_array(3, $group) && $e->getId()==29)==false){
                    continue;
                }*/
                //echo '<pre>' . print_r($e->getName(), true) . '</pre>';
                $menu->addChild($e->getName(), array(
                    'route' => 'admin_block_show',
                    'routeParameters' => array('id' => $e->getId())
                ));
            }
        }


        return $menu;
    }

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        //$entities = $em->getRepository('NovuscomCMFBundle:Block')->findAll();

        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('NovuscomCMFBundle:Site')->findAll();
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav bs-sidenav');
        $sites = $menu->addChild('Сайты', array(
            'route' => 'cmf_admin_site_list'
        ));

        /*$menu->addChild('Страницы', array(
            'route' => 'admin_page',
        ));*/

        foreach ($entities as $e) {
            $site = $sites->addChild($e->getName(), array(
                'route' => 'cmf_admin_site_show',
                'routeParameters' => array('id' => $e->getId())
            ));
            $site->addChild('Страницы', array(
                'route' => 'cmf_admin_site_pages',
                'routeParameters' => array('site_id' => $e->getId())
            ));
            $site->addChild('Справочник организаций', array(
                'route' => 'vpg_admin_sprav',
                'routeParameters' => array('site_id' => $e->getId())
            ));
            /*
             $site->addChild('Список инфоблокв вместо этого пункта', array(
                 'route' => 'cmf_admin_site_pages',
                 'routeParameters' => array('site_id' => $e->getId())
             ));*/
        }
        $sites = $menu->addChild('Инфоблоки', array(
            'route' => 'admin_block'
        ));
        $sites = $menu->addChild('Свойства инфоблоков', array(
            'route' => 'admin_property'
        ));
        $sites = $menu->addChild('Элементы инфоблоков', array(
            'route' => 'admin_element'
        ));
        $sites = $menu->addChild('Разделы инфоблоков', array(
            'route' => 'admin_section_'
        ));
        $sites = $menu->addChild('Значения свойств', array(
            'route' => 'admin_elementproperty'
        ));
        $sites = $menu->addChild('Группы инфоблоков', array(
            'route' => 'admin_blockgroup'
        ));

        return $menu;
    }

    private $crumbs = array();

    public function addCrumb($name, $routeName)
    {
        $this->crumbs[] = array($name, $routeName);
    }

    public function crumbs(FactoryInterface $factory, array $options)
    {

        $em = $this->container->get('doctrine.orm.entity_manager');

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild('CMF', array(
            'route' => 'cmf_admin_homepage'
        ));
        //echo '<pre>'.print_r($this->crumbs, true).'</pre>';
        foreach ($this->crumbs as $crumb) {
            $menu->addChild($crumb[0], array(
                'route' => $crumb[1]
            ));
        }

        //$pathInfo = $this->container->get('request')->getPathInfo();
        //$pathInfo = preg_replace('/^\/(.*?)\/$/', '\\1', $pathInfo);
        //$pathArray = explode('/', $pathInfo);

        //$request = $this->container->get('request');
        //$routeName = $request->get('_route');
        //$params = $request->get('_route_params');
        //echo '<pre>'.print_r($params, true).'</pre>';
        //echo '<pre>'.print_r($routeName, true).'</pre>';
        //echo '<pre>'.print_r($pathArray, true).'</pre>';
        return $menu;
    }
}