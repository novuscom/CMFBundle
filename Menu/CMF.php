<?php
namespace Novuscom\CMFBundle\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

class CMF extends ContainerAware
{

    public function BaseMenu(FactoryInterface $factory, array $options)
    {

        $em = $this->container->get('doctrine.orm.entity_manager');
        $entities = $em->getRepository('CMFMenuBundle:Item')->findBy(array('menu'=>$options['MENU_ID']));

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'dropdown-menu');


        if ($entities) {
            foreach ($entities as $e) {
                $menu->addChild($e->getName(), array(
                    'route' => 'cmf_admin_site_pages',
                    'routeParameters' => array('site_id' => $e->getId())
                ));
            }
        }


        return $menu;
    }

}