<?php
namespace Novuscom\CMFBundle\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;

class Yar extends ContainerAware
{


    public function MainMenu(FactoryInterface $factory, array $options)
    {

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', '');
        $request = $this->container->get('request');
        $routeParams = $request->get('_route_params');
        //echo '<pre>' . print_r($routeParams, true) . '</pre>';
        $menuArray = array(
            'Главная' => array(
                'route' => 'cmf_page_main',
                'name' => 'Главная'
            ),
            'Достопримечательности' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'showplace')
            ),
            /*'Справочник организаций' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'org/cat')
            ),*/
            /*'Гостиницы' => array(
                'route' => 'cmf_page_frontend',
                'routeParameters' => array('name' => 'hotels')
            ),*/
        );
        foreach ($menuArray as $key => $ma) {
            $item = $menu->addChild($key, $ma);
            /*
            if (array_key_exists('routeParameters', $ma) and $routeParams['name']==$ma['routeParameters']['name'].'/'){
                $item->setCurrent(true);
            }
            */
        }
        /*$raz = $menu->addChild('Развлечения', array(
            'route' => 'cmf_page_frontend',
            'routeParameters' => array('name' => 'razvlecheniya'),
            'attributes' => array(
                'class' => 'dropdown',
                
            ),
        ));
        $raz->addChild('Кафе/Бары/Рестораны', array(
            'route' => 'cmf_page_frontend',
            'routeParameters' => array('name' => 'razvlecheniya/cafe-bar-restaurant')
        ));
        $raz->addChild('Ночные клубы', array(
            'route' => 'cmf_page_frontend',
            'routeParameters' => array('name' => 'razvlecheniya/night-clubs')
        ));
        $raz->addChild('Кинотеатры', array(
            'route' => 'cmf_page_frontend',
            'routeParameters' => array('name' => 'razvlecheniya/kinoteatry')
        ));*/
        //$raz->setLinkAttributes(array('class'=>'dropdown-toggle', 'data-toggle'=>'dropdown'));
        //$raz->setChildrenAttribute('class', 'dropdown-menu');
        return $menu;
    }


}