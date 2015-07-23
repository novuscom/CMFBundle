<?php

namespace Novuscom\CMFBundle\Controller;


use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Novuscom\CMFBundle\Entity\SiteBlock;
use Novuscom\CMFBundle\Entity\Site;
use Novuscom\CMFBundle\Entity\Block;
use Novuscom\CMFBundle\Form\BlockType;
use \Doctrine\Common\Collections\ArrayCollection;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Novuscom\CMFBundle\Services\Section as Section;

/**
 * Block controller.
 *
 */
class ComponentController extends Controller
{
    private function routeToControllerName($routename)
    {
        $routes = $this->get('router')->getRouteCollection();
        return $routes->get($routename)->getDefaults()['_controller'];
    }

    public function CrumbsAction($params, Request $request)
    {
        $time_start = microtime(1);
        $logger = $this->get('logger');
        $route_params = $request->get('_route_params');
        $routeName = $request->get('_route');
        $pageRoute = ($routeName == 'cmf_page_frontend');
        if (!isset($route_params['params']) && !$pageRoute) {
            $logger->notice('параметры маршрута не известны и это не маршрут для статических страниц, возвращаем пустой результат (' . print_r($route_params, true) . ')');
            return new Response();
        }
        $logger->info('параметры маршрута известны или это маршрут для статических страниц');
        $env = $this->get('kernel')->getEnvironment();
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $cacheDriver->setNamespace('CrumbsAction_' . $env);
        $cacheId = json_encode(array($params, $route_params));
        $existParams = (array_key_exists('params', $route_params));
        if ($fooString = $cacheDriver->fetch($cacheId)) {
            //echo '<pre>' . print_r('крошки закешированы', true) . '</pre>';
            $logger->info('крошки есть в кеше');
            $response = unserialize($fooString);
        } else {
            $logger->info('крошек нет в кеше');
            $em = $this->getDoctrine()->getManager();
            $crumbs = array();
            $codes_array = array();
            /*
             * Хлебные крошки для страниц
             */
            $repo = $em->getRepository('NovuscomCMFBundle:Page');

            if ($existParams) {
                $page = $repo->find($route_params['params']['page_id']);
            } elseif ($pageRoute && $route_params['name']) {
                $Page = $this->get('Page');
                $page = $Page->findPage($route_params['name']);
            }

            $path = $repo->getPath($page);
            foreach ($path as $p) {
                if ($p->getLvl() == 0) {
                    $crumbs[] = array(
                        'url' => $this->generateUrl('cmf_page_main'),
                        'name' => $p->getName(),
                    );
                } else {
                    $codes_array[] = $p->getUrl();
                    $crumbs[] = array(
                        'url' => $this->generateUrl('cmf_page_frontend', array('name' => implode('/', $codes_array))),
                        'name' => $p->getName(),
                    );
                }
            }

            /*
             * Хлебные крошки для раздела
             */
            if ($existParams && $route_params['params']['controller_code'] == 'section') {
                $logger->info('Создаем хлебные крошки для раздела');
                $crumbs = $this->getCrumbsForSection($route_params['CODE'], $route_params, $crumbs, $codes_array);
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
                $crumbs[] = array(
                    'url' => $this->generateUrl('cmf_page_frontend', array('name' => implode('/', $codes_array))),
                    'name' => $element->getName(),
                );
            }


            /*
             * Выдаем результат
             */
            $response_data = array(
                'items' => $crumbs
            );
            $response = $this->render('@templates/' . $params['template_directory'] . '/Crumbs/' . $params['template_code'] . '.html.twig', $response_data);

            $cacheDriver->save($cacheId, serialize($response));
        }
        $time_end = microtime(1);
        $time = number_format((($time_end - $time_start) * 1000), 2);
        //echo $time.' мс';
        return $response;
    }

    private $sectionByPath;

    private function getCrumbsForSection($SECTION_PATH, $route_params, $crumbs, $codes_array)
    {
        $em = $this->getDoctrine()->getManager();
        $section_repo = $em->getRepository('NovuscomCMFBundle:Section');
        $logger = $this->get('logger');
        $SectionClass = $this->get('SectionClass');
        $section = $SectionClass->GetSectionByPath($SECTION_PATH, $route_params['params']['BLOCK_ID'], $route_params['params']['params']);
        $this->sectionByPath = $section;
        if ($section) {
            $logger->info('Нашли раздел');
            $path = $section_repo->getPath($section);
            foreach ($path as $p) {
                $codes_array[] = $p->getCode();
                $crumbs[] = array(
                    'url' => $this->generateUrl('cmf_page_frontend', array('name' => implode('/', $codes_array))),
                    'name' => $p->getName(),
                );
            }
        }
        return $crumbs;
    }

    private function getEnv()
    {
        $env = $this->get('kernel')->getEnvironment();
        return $env;
    }

    private function getCahceDir()
    {
        $env = $this->getEnv();
        return $this->get('kernel')->getRootDir() . '/cache/' . $env . '/sys/SectionAction/';
    }

    public function SectionAction($params, $CODE, Request $request, $PAGE = 1)
    {
        $logger = $this->get('logger');
        $logger->info('SectionAction');
        $logger->info(print_r($params, true));
        $env = $this->getEnv();
        $route_name = $request->get('_route');
        $route_params = $request->get('_route_params');
        $cacheDir = $this->getCahceDir();
        $logger->info('Директория кеша = ' . $cacheDir);
        //$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($cacheDir);
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $fullCode = trim($CODE, '/');
        $cacheDriver->setNamespace('SectionAction_' . $env . '_' . $params['BLOCK_ID'] . '_' . $fullCode);
        $cacheId = $fullCode . '[page=' . $PAGE . ']';
        $logger->info('Cache id = ' . print_r($cacheId, true));
        if (false) {
            //if ($fooString = $cacheDriver->fetch($cacheId)) {
            $logger->info('Информацию берем из кеша');
            $response = unserialize($fooString);
        } else {
            if ($this->checkConstruction()) {
                return $this->constructionResponse();
            };
            $em = $this->getDoctrine()->getManager();

            /*
             * Страница
             */
            $page_class = $this->get('Page');
            $page = $page_class->GetById($params['page_id']);


            /*
             * Раздел
             */
            $SectionClass = $this->get('SectionClass');
            $section = $SectionClass->GetSectionByPath($CODE, $params['BLOCK_ID'], $params['params']);
            if (!$section) {
                $logger->notice('Раздел не найден по пути ' . $CODE . '');
                throw $this->createNotFoundException('Раздел не найден по пути ' . $CODE);
            }
            $section->setFullCode($fullCode);


            /*
             * Подразделы
             */
            $sections = $SectionClass->SectionsList(array(
                'block_id' => $params['BLOCK_ID'],
                'section_id' => $section->getId()
            ), $parentFullCode = trim($CODE, '/'));


            /*
             * Элементы
             */
            $ElementsList = $this->get('ElementsList');
            $ElementsList->setBlockId($params['BLOCK_ID']);
            $ElementsList->setSectionId($section->getId());
            $ElementsList->setSelect(array('code', 'last_modified', 'preview_picture'));
            $ElementsList->selectProperties(array('address', 'shirota', 'price'));
            $ElementsList->setOrder(array('name', 'asc'));
            //echo '<pre>' . print_r($params, true) . '</pre>';
            if ($params && array_key_exists('params', $params) && array_key_exists('INCLUDE_SUB_SECTIONS', $params['params']))
                $ElementsList->setIncludeSubSections($params['params']['INCLUDE_SUB_SECTIONS']);
            $elements = $ElementsList->getResult();


            /*
             * Обработка элементов перед выдачей
             */
            $elementRoute = (array_key_exists('ELEMENT_ROUTE', $params['params']));
            foreach ($elements as &$e) {
                $e['url'] = false;
                if ($elementRoute)
                    $e['url'] = $this->generateUrl($params['params']['ELEMENT_ROUTE'], array(
                        'SECTION_CODE' => $e['parent_section_full_code'],
                        'CODE' => $e['code']
                    ));
            }

            // TODO Здесь надо сделать редирект с первой страницы на раздел
            /*$url = $this->generateUrl('cmf_page_frontend', array(
                'name' => $parentFullCode,
            ));*/
            //echo '<pre>' . print_r($url, true) . '</pre>';

            /*
             * Пагинация
             */
            //echo '<pre>' . print_r($route_params, true) . '</pre>';
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $elements,
                $PAGE/*page number*/,
                12/*limit per page*/
            );

            $pagination->setUsedRoute('stroyshop_catalog_pagination');
            $pagination->setParam('CODE', $fullCode);
            $pagination->setTemplate('@templates/' . $params['params']['template_directory'] . '/Pagination/' . $params['template_code'] . '.html.twig');
            //$pagination->setParam('PAGE', $PAGE);
            if ($PAGE > 1 && count($pagination) < 1) {
                throw $this->createNotFoundException('Не найдено элементов на странице ' . $PAGE);
            }
            //echo '<pre>' . print_r(count($pagination), true) . '</pre>';


            /*
             * Массив данных
             */
            $response_data = array(
                'page' => $page,
                'section' => $section,
                'elements' => $elements,
                'sections' => $sections,
                'title' => $section->getTitle(),
                'description' => $section->getDescription(),
                'keywords' => $section->getKeywords(),
                'header' => $section->getName(),
                'pagination' => $pagination,
            );

            if (!$response_data['title'])
                $response_data['title'] = $section->getName();
            if (!$response_data['description'])
                $response_data['description'] = $section->getName();
            if (!$response_data['keywords'])
                $response_data['keywords'] = $section->getName();


            /*
             * Ответ
             */
            $response = $this->render('@templates/' . $params['params']['template_directory'] . '/Section/' . $params['template_code'] . '.html.twig', $response_data);

            $cacheDriver->save($cacheId, serialize($response));
        }


        return $response;
    }


    public function ElementAction(
        $params = false,
        $CODE = false,
        $ID = false,
        $SECTION_CODE = false,
        Request $request
    )
    {
        $logger = $this->get('logger');
        $logger->notice('Начал работу контроллер ElementAction');

        $env = $this->get('kernel')->getEnvironment();
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        /*
         * Кэш в зависиомсти от окружения нужен для того чтобы правильные ссылки кешировались
         */
        $cacheDriver->setNamespace('ElementAction_' . $env . '_' . $params['BLOCK_ID']);
        $cacheId = json_encode(array($params, $CODE, $ID));
        //if (false) {
        if ($fooString = $cacheDriver->fetch($cacheId)) {
            //echo '<pre>' . print_r('кешированные данные', true) . '</pre>';
            $cacheData = unserialize($fooString);
        } else {

            /**
             * Проверяем закрыт ли сайт на обслуживание
             */
            if ($this->checkConstruction()) {
                return $this->constructionResponse();
            };

            /**
             * Параметры
             */
            $block_id = $params['BLOCK_ID'];

            /**
             * Переменные
             */
            $em = $this->getDoctrine()->getManager();
            $page_repository = $em->getRepository('NovuscomCMFBundle:Page');

            /**
             * Информация о текущем сайте
             */
            $site = $this->getAlias()->getSite();
            //echo '<pre>' . print_r($site->getId(), true) . '</pre>';

            /**
             * Получаем информацию об инфоблоке
             */
            $block = $em->getRepository('NovuscomCMFBundle:Block')->findOneBy(
                array(
                    'id' => $block_id,
                )
            );

            /**
             * Получаем ид инфоблоков которые принадлежат текущему сайту
             */
            $sites_blocks_id = array();

            foreach ($site->getBlocks() as $SiteBlock) {
                $sites_blocks_id[] = $SiteBlock->getId();
            }
            //$sites_blocks_id = array_unique($sites_blocks_id);
            //echo '<pre>' . print_r($sites_blocks_id, true) . '</pre>';
            //echo '<pre>' . print_r($site->getId(), true) . '</pre>';

            /**
             * Если инфоблок не принадлежит данному сайту - выдаем ошибку
             */
            if (!in_array($block->getId(), $sites_blocks_id)) {
                throw $this->createNotFoundException('Инфоблок не принадлежит данному сайту');
            }

            /*
             * Получаем раздел
             */
            $section = false;
            $elementsId = array();
            if ($SECTION_CODE) {
                $SectionClass = $this->get('SectionClass');
                $section = $SectionClass->GetSectionByPath($SECTION_CODE, $block_id, $params['params']);
                $ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array('section' => $section));
                foreach ($ElementSection as $es) {
                    $elementsId[] = $es->getElement()->getId();
                }
                if (!$elementsId) {
                    $logger->notice('Не найдены элементы в разделе [' . $SECTION_CODE . '] [' . $section->getId() . '] [' . $section->getName() . ']');
                    throw $this->createNotFoundException('Элемент не найден');
                }
            }

            /*
             * Получаем информацию об элементе
             */
            $filter = array();
            if ($CODE) {
                $filter['code'] = $CODE;
            }
            if ($ID) {
                $filter['id'] = $ID;
            }
            $filter['block'] = $block_id;
            if ($elementsId) {
                $filter['id'] = $elementsId;
            }
            $element = $em->getRepository('NovuscomCMFBundle:Element')->findOneBy(
                $filter
            );
            if (!$element) {
                $logger->notice('Элемент не найден по фильтру: <pre>' . print_r($filter, true) . '</pre>');
                throw $this->createNotFoundException('Элемент не найден');
            }


            /**
             * Получение информации о страницах
             */
            $page = false;
            if (array_key_exists('page_id', $params))
                $page = $page_repository->find($params['page_id']);


            /**
             * Свойства элемента
             */
            $properties_codes = array();
            $properties_by_code = array();
            $ElementProperties = $em->getRepository('NovuscomCMFBundle:Property')->findBy(
                array(
                    'block' => $block,
                )
            );
            $properties_by_type = array();
            foreach ($ElementProperties as $ep) {
                $properties_codes[$ep->getId()] = $ep->getCode();
                $property_info = array(
                    'name' => $ep->getName(),
                    'code' => $ep->getCode(),
                    'id' => $ep->getId(),
                    'type' => $ep->getType(),
                    'params' => json_decode($ep->getInfo(), true),
                    'value' => null,
                );
                $properties_by_code[$ep->getCode()] = $property_info;
                $properties_by_type[$ep->getType()][$ep->getId()] = $property_info;
            }

            /**
             * Значения свойств элемента
             */
            $PropertyValues = $em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(
                array(
                    'element' => $element,
                )
            );
            $values_by_code = array();
            foreach ($PropertyValues as $pv) {
                $code = $properties_codes[$pv->getProperty()->getId()];
                $values_by_code[$code][] = $pv->getValue();
            }
            $logger->info('<pre>' . print_r($values_by_code, true) . '</pre>');
            foreach ($values_by_code as $code => $n) {
                $prop = $properties_by_code[$code];
                if (is_array($prop['params']) && array_key_exists('MULTIPLE', $prop['params']) && $prop['params']['MULTIPLE']) {
                    $value = $n;
                } else {
                    $value = $n[0];
                }
                $properties_by_code[$code]['value'] = $value;
            }


            /**
             * Значения свойства типа "файл"
             */
            $PropertyValuesF = $em->getRepository('NovuscomCMFBundle:ElementPropertyF')->findBy(
                array(
                    'element' => $element,
                )
            );
            $values_by_property = array();
            foreach ($PropertyValuesF as $pv) {
                //echo '<pre>' . print_r($pv->getFile()->getName(), true) . '</pre>';
                $values_by_property[$pv->getProperty()->getId()]['id'][] = $pv->getFile()->getId();
                $values_by_property[$pv->getProperty()->getId()]['name'][] = $pv->getFile()->getName();
                $values_by_property[$pv->getProperty()->getId()]['src'][] = $pv->getFile()->getImagePath();
            }
            foreach ($values_by_property as $property_id => $value) {
                $code = $properties_codes[$property_id];
                $properties_by_code[$code] = $value;
            }

            /**
             * Значения свойств типа "список"
             */
            $list = array();
            if (array_key_exists('LIST', $properties_by_type)) {
                $PropertyList = $em->getRepository('NovuscomCMFBundle:PropertyList')->findBy(
                    array(
                        'property' => array_keys($properties_by_type['LIST']),
                    )
                );
                foreach ($PropertyList as $pl) {
                    $list[$pl->getId()] = array(
                        'value' => $pl->getValue(),
                        'code' => $pl->getCode(),
                        'id' => $pl->getId(),
                    );
                }
            }

            $list = array();
            if (array_key_exists('LIST', $properties_by_type)) {
                $PropertyList = $em->getRepository('NovuscomCMFBundle:PropertyList')->findBy(
                    array(
                        'property' => array_keys($properties_by_type['LIST']),
                    )
                );
                foreach ($PropertyList as $pl) {
                    $list[$pl->getId()] = array(
                        'value' => $pl->getValue(),
                        'code' => $pl->getCode(),
                        'id' => $pl->getId(),
                    );
                }
            }

            //echo '<pre>' . print_r($properties_by_code, true) . '</pre>';

            /**
             * Собираем все свойства
             */
            //echo '<pre>' . print_r($properties_by_code, true) . '</pre>';
            foreach ($properties_by_code as $code => $n) {
                if (array_key_exists('type', $n)) {
                    if ($n['type'] == 'LIST' && array_key_exists($n['value'], $list)) {
                        $lv = $list[$n['value']];
                        $n['value'] = $lv['value'];
                        $n['code'] = $lv['code'];
                        $n['id'] = $lv['id'];
                    } else {

                    }
                }
                $element->addProperty($code, $n);
            }


            /**
             * Элемент
             */
            $entity_element = $element;
            $element = array(
                'id' => $entity_element->getId(),
                'name' => $entity_element->getName(),
                'properties' => $entity_element->getProperties(),
                'detailText' => $entity_element->getDetailText(),
                'previewText' => $entity_element->getPreviewText(),
                'lastModified' => $entity_element->getLastModified(),
                'previewPicture' => array(),
                'detailPicture' => array(),
            );
            if ($previewPicture = $entity_element->getPreviewPicture()) {
                $element['previewPicture']['path'] = $previewPicture->getImagePath();
                $element['previewPicture']['name'] = $previewPicture->getName();
                $element['previewPicture']['description'] = $previewPicture->getDescription();
            }
            if ($entity_element->getDetailPicture()) {
                $element['detailPicture']['path'] = $entity_element->getDetailPicture()->getImagePath();
                $element['detailPicture']['name'] = $entity_element->getDetailPicture()->getName();
            }

            //echo '<pre>' . print_r($element, true) . '</pre>';

            $response_data = array(
                'element' => $element,
                'title' => $entity_element->getTitle(),
                'header' => $entity_element->getHeader(),
                'description' => $entity_element->getDescription(),
                'keywords' => $entity_element->getKeywords(),
                'section' => $section
            );
            if (!$response_data['title'])
                $response_data['title'] = $entity_element->getName();
            if (!$response_data['header'])
                $response_data['header'] = $entity_element->getName();
            if (!$response_data['description'])
                $response_data['description'] = $entity_element->getName();
            if (!$response_data['keywords'])
                $response_data['keywords'] = $entity_element->getName();
            if ($page) {
                $response_data['page'] = $page;
            }
            $template_code = $params['template_code'];
            $template_dir = $params['params']['template_directory'];
            $response = $this->render('@templates/' . $template_dir . '/Element/' . $template_code . '.html.twig', $response_data);
            $cacheData = array(
                'response' => $response,
                'lastModified' => $element['lastModified'],
            );
            $cacheDriver->save($cacheId, serialize($cacheData));
            //echo '<pre>' . print_r('НЕ кешированные данные', true) . '</pre>';
        }
        //$cacheData['response']->setLastModified($cacheData['lastModified']);
        if ($env == 'prod') {
            //$cacheData['response']->setETag(md5($cacheData['response']->getContent()));
            //$cacheData['response']->setPublic(); // make sure the response is public/cacheable
            //$cacheData['response']->isNotModified($request);
            //$cacheData['response']->setSharedMaxAge(600);
            //$cacheData['response']->setMaxAge(600);
        }


        return $cacheData['response'];
    }



    /*    private function getCrumbs($page_id, $section = false)
        {
            $em = $this->getDoctrine()->getManager();
            $crumbs = $this->get("apy_breadcrumb_trail");
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
            if ($section) {
                $crumbs->add($section->getName(), 'cmf_page_frontend', array('name' => $section->getCode()));
            }
            return $crumbs;
        }*/

    /**
     * Список разделов инфоблока
     * @param array $params Параметры компонента
     * @param Request $request
     * @return mixed
     */
    public function SectionsListAction($params, Request $request)
    {
        if ($this->checkConstruction()) {
            return $this->constructionResponse();
        };

        /**
         * Переменные
         */
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $page_repository = $em->getRepository('NovuscomCMFBundle:Page');
        $host = $request->headers->get('host');
        $cacheId = json_encode($params);
        $BLOCK_ID = $params['BLOCK_ID'];
        $section_id = null;
        if (array_key_exists('SECTION_ID', $params)) {
            $section_id = $params['SECTION_ID'];
        }
        $template_code = 'default';
        if (array_key_exists('template_code', $params)) {
            $template_code = $params['template_code'];
        }
        $env = $this->get('kernel')->getEnvironment();
        //echo '<pre>' . print_r($params, true) . '</pre>';

        /**
         * Кэш
         */
        //$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $host . '/components/SectionsList/');
        //$cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        if (false) {
            //if ($fooString = $cacheDriver->fetch($cacheId)) {
            //echo '<pre>' . print_r('Кешированные данные', true) . '</pre>';
            $render = unserialize($fooString);
        } else {

            $SectionClass = $this->get('SectionClass');

            /**
             * Получение необходимой информации
             */
            $page = $page_repository->find($params['page_id']);


            //$block = $em->getRepository('NovuscomCMFBundle:Block')->find($BLOCK_ID);


            /*
             * Разделы
             */
            $sections = $SectionClass->SectionsList(array(
                'block_id' => $BLOCK_ID,
            ));

            //echo '<pre>' . print_r($sections, true) . '</pre>';
            //exit;

            /*
             *
             */
            $files_id = array();
            foreach ($sections as $e) {
                $files_id[] = $e['preview_picture'];
            }
            $files_id = array_filter(array_unique($files_id));
            if ($files_id) {
                $repo = $em->createQueryBuilder('n');
                $repo = $repo->from('NovuscomCMFBundle:File', 'n', 'n.id');
                $repo = $repo->select('n.id, n.name, n.size, n.description, n.type');
                $repo = $repo->andWhere('n.id IN(:files_id)');
                $repo = $repo->setParameter('files_id', $files_id);
                $repo = $repo->getQuery();
                $sql = $repo->getSql();
                $preview_pictures = $repo->getResult();
            }
            foreach ($sections as $key => $e) {
                if ($e['preview_picture'] && array_key_exists($e['preview_picture'], $preview_pictures)) {
                    $array = $preview_pictures[$e['preview_picture']];
                    $array['src'] = 'upload/images/' . $array['name'];
                    $array['path'] = $array['src'];
                    $sections[$key]['preview_picture'] = $array;
                }
            }

            //echo '<pre>' . print_r($sections, true) . '</pre>';
            //exit;

            /**
             * Данные попадающие в шаблон
             */
            $response_data = array(
                'sections' => $sections,
            );
            $response_data['page'] = $page;

            //echo '<pre>' . print_r($sections, true) . '</pre>';

            $render = $this->render('@templates/' . $params['params']['template_directory'] . '/SectionsList/' . $template_code . '.html.twig', $response_data, $response);

            //$cacheDriver->save($cacheId, serialize($render));
        }
        return $render;
    }


    private function constructionResponse()
    {
        $response = $this->forward('NovuscomCMFBundle:Default:closed', array());
        return $response;
    }

    private function checkConstruction()
    {
        $alias = $this->getAlias();
        $site = $alias->getSite();
        $securityContext = $this->container->get('security.context');
        $result = ($site->getClosed() && !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'));
        return $result;
    }

    public function ElementsListAction($params, Request $request)
    {
        if ($this->checkConstruction()) {
            return $this->constructionResponse();
        };

        /**
         * Переменные
         */
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $page_repository = $em->getRepository('NovuscomCMFBundle:Page');
        $host = $request->headers->get('host');
        $cacheId = json_encode($params);
        $section_id = null;
        if (array_key_exists('section_id', $params)) {
            $section_id = $params['section_id'];
        }
        $template_code = 'default';
        if (array_key_exists('template_code', $params)) {
            $template_code = $params['template_code'];
        }
        $env = $this->get('kernel')->getEnvironment();
        $exist_page_id = array_key_exists('page_id', $params);
        if (!$exist_page_id) {
            $params['page_id'] = false;
        }
        if (!array_key_exists('LIMIT', $params)) {
            $params['LIMIT'] = null;
        }
        if (!array_key_exists('OPTIONS', $params)) {
            $params['OPTIONS'] = null;
        }

        /**
         * Кэш
         */
        //$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $host . '/components/ElementsList/');
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $cacheDriver->setNamespace('ElementsListAction_' . $env . '_' . $params['BLOCK_ID']);
        //if (false) {
        if ($fooString = $cacheDriver->fetch($cacheId)) {
            //echo '<pre>' . print_r('есть такое в кеше', true) . '</pre>';
            $render = unserialize($fooString);
        } else {
            $response_data = array();

            //echo '<pre>' . print_r($params, true) . '</pre>';

            /**
             * Элементы
             */
            $ElementsList = $this->get('ElementsList');
            $ElementsList->setBlockId($params['BLOCK_ID']);
            $ElementsList->setSelect(array('code', 'last_modified', 'preview_picture', 'preview_text'));
            // TODO Здесь в сервисе ElementList - выбирать все свойства
            $ElementsList->selectProperties(array('address', 'shirota', 'anounce', 'long_name', 'date'));
            $ElementsList->setFilter(array('active' => true));
            $ElementsList->setLimit($params['LIMIT']);
            $ElementsList->setOrder(array('name', 'asc'));
            $elements = $ElementsList->getResult();


            /**
             * Данные попадающие в шаблон
             */

            $response_data['elements'] = $elements;
            $response_data['options'] = $params['OPTIONS'];
            $response_data['page'] = $page_repository->find($params['page_id']);

            $render = $this->render('@templates/' . $params['params']['template_directory'] . '/ElementsList/' . $template_code . '.html.twig', $response_data, $response);

            $cacheDriver->save($cacheId, serialize($render));
        }
        return $render;
    }


    private function getAlias()
    {
        $request = Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $host = $request->headers->get('host');
        $alias = $em->getRepository('NovuscomCMFBundle:Alias')->findOneBy(array('name' => $host));
        return $alias;
    }
}
