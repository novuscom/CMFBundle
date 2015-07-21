<?php



namespace Novuscom\CMFBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyFile;

use Novuscom\CMFBundle\Entity\FormElement;
use Novuscom\CMFBundle\Entity\FormProperty;
use Novuscom\CMFBundle\Entity\Element;
use Novuscom\CMFBundle\Entity\ElementProperty;
use Novuscom\CMFBundle\Entity\ElementPropertyF;
use Novuscom\CMFBundle\Entity\ElementPropertyDT;
use Novuscom\CMFBundle\Entity\FrontElement;
use Novuscom\CMFBundle\Entity\ElementSection;
use Novuscom\CMFBundle\Form\ElementType;
use Novuscom\CMFBundle\Form\ElementPropertyType;
use Novuscom\CMFBundle\Entity\Page;
use Novuscom\CMFBundle\Entity\File;


/**
 * Element controller.
 *
 */
class ElementController extends Controller
{
    private function getParents($page, $site_id)
    {
        $em = $this->getDoctrine()->getManager();
        $er = $em->getRepository('NovuscomCMFBundle:Page');
        $parents = $er->createQueryBuilder('p')
            ->where("p.lft < :left")
            ->andWhere("p.rgt > :right")
            ->andWhere("p.site = :site_id")
            ->setParameters(array(
                'left' => $page->getLft(),
                'right' => $page->getRgt(),
                'site_id' => $site_id,
            ))
            ->getQuery()
            ->getResult();
        return $parents;
    }

    public function deletePreviewPictureAction($element_id)
    {

    }

    public function elementPageAction($ELEMENT_CODE, $block = false, $section = false, $siteId, $pageId, $templateCode = 'default')
    {
        //echo '<pre>' . print_r('elementPageAction()', true) . '</pre>';
        //echo '<pre>' . print_r($ELEMENT_CODE, true) . '</pre>';
        $em = $this->getDoctrine()->getManager();

        $filter = array(
            'code' => $ELEMENT_CODE,
        );

        $page = $em->getRepository('NovuscomCMFBundle:Page')->find($pageId);

        //echo '<pre>' . print_r($page->getName(), true) . '</pre>';


        /*if ($BLOCK_ID) {
            $block = $em->getRepository('NovuscomCMFBundle:Block')->find($BLOCK_ID);
        }*/

        //echo '<pre>' . print_r($siteId, true) . '</pre>';

        if ($block) {
            $filter['block'] = $block;
        }

        if ($section) {
            //$filter['section'] = $section;
        } else {
            //$filter['section'] = null;
        }

        //echo '<pre>Фильтр: ' . print_r($filter, true) . '</pre>';

        $element = $em->getRepository('NovuscomCMFBundle:Element')->findOneBy(
            $filter
        );
        if (!$element) {
            throw $this->createNotFoundException('Элемент не найден');
        }
        // TODO Здесь исправить хлебные крошки
        $crumbs = $this->get("apy_breadcrumb_trail");
        $parents = $this->getParents($page, $siteId);
        $routing_name = 'cmf_page_frontend';
        foreach ($parents as $p) {
            //echo '<pre>' . print_r($p->getName().' - '.$p->getId().' - '.$p->getUrl(), true) . '</pre>';

            if ($p->getLvl() == 0) {
                $routing_name = 'cmf_page_main';
            }
            $crumbs->add($p->getName(), $routing_name, array('name' => $p->getUrl()));
        }
        $crumbs->add($page->getName(), 'cmf_page_frontend', array('name' => $page->getUrl()));
        $crumbs->add($element->getName());


        /*$page = new Page();
        $page->setName($element->getName());
        $page->setTitle($element->getName());*/

        /*
        $elementProperties = array();
        $propId = array();
        $blockProperties = $block->getProperty();
        foreach ($blockProperties as $bp) {
            $propInfo = array(
                'NAME' => $bp->getName(),
                'CODE' => $bp->getCode(),
                'TYPE' => $bp->getType(),
                'ID' => $bp->getId(),
            );
            $propId[] = $bp->getId();
            $elementProperties[$bp->getId()] = $propInfo;
            //$elementProperties[$bp->getCode()][''] = $bp;
            //echo '<pre>' . print_r($bp->getName(), true) . '</pre>';
        }
        $propertyValues = array();
        if ($propId) {
            $er = $em->getRepository('NovuscomCMFBundle:ElementProperty');
            $propertyValues = $er->createQueryBuilder('s')
                ->andWhere("s.property IN (:id)")
                ->andWhere("s.element=:element")
                ->setParameters(array(
                    'id' => $propId,
                    'element' => $element
                ))
                ->getQuery()
                ->getResult();
        }

        $propertiesByCode = array();
        foreach ($elementProperties as $ep) {
            $propertiesByCode[$ep['CODE']] = $ep;
        }
        */
        //echo '<pre>' . print_r($elementProperties, true) . '</pre>';
        //echo '<pre>' . print_r($propertiesByCode, true) . '</pre>';


        return $this->render('CMFTemplateBundle:ElementPage:' . $templateCode . '.html.twig', array(
            'element' => $element,
            'page' => $page,
            //'block' => $block,
            //'properties' => $elementProperties,
            //'propertiesByCode' => $propertiesByCode,
        ));
    }

    public function elementAction($id, Request $request, $template_code = 'default')
    {
        $host = $request->headers->get('host');
        $cacheId = 'id=[' . $id . ']';
        $env = $this->get('kernel')->getEnvironment();
        //$cache = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/'.$env.'/sys/'.$host.'/element/');
        $cache = new \Doctrine\Common\Cache\ApcCache();
        if ($fooString = $cache->fetch($cacheId)) {
            //echo '<pre>' . print_r('есть кэш '.$cacheId, true) . '</pre>';
            $response = unserialize($fooString);
        } else {
            $element = new FrontElement();
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NovuscomCMFBundle:Element')->find($id);
            $element->setInfo($entity);
            $propertyFiles = $em->getRepository('NovuscomCMFBundle:ElementPropertyF')->findBy(array(
                'element' => $entity,
                'property_id' => 26,
            ));
            foreach ($propertyFiles as $pf) {
                //echo '<pre>' . print_r($pf->getFile(), true) . '</pre>';
                $element->addPropertyFile($pf->getFile());
            }
            //echo '<pre>' . print_r($entity->getName(), true) . '</pre>';
            if ($entity) {
                $templatePath = 'CMFTemplateBundle:Element:' . $template_code . '.html.twig';
                $response = $this->render($templatePath, array(
                    'element' => $element,
                ));
            } else {
                $response = new Response('<div>Элемент не найден</div>');
            }
            $cache->save($cacheId, serialize($response));
        }

        return $response;

    }

    public function elementsListAction($BLOCK_ID, $template_code, $section_id, $params, Request $request)
    {

        $host = $request->headers->get('host');
        $cacheId = 'Novuscom\CMFBundle\Controller\elementsListAction(BLOCK_ID=' . $BLOCK_ID . ')';
        $env = $this->get('kernel')->getEnvironment();
        //$cache = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $host . '/elements_list/');
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        if ($cacheDriver->contains($cacheId)) {
            //echo 'cache exists';
        } else {
            //echo 'cache does not exist';
        }
        if (false) {
            //if ($fooString = $cacheDriver->fetch($cacheId)) {
            //echo '<pre>' . print_r('ответ из кеша', true) . '</pre>';
            $render = unserialize($fooString);
        } else {


            /**
             * Переменные
             */
            $response = new Response();
            $em = $this->getDoctrine()->getManager();


            /**
             * Вычисление необходимой информации
             */
            $block = $em->getRepository('NovuscomCMFBundle:Block')->find($BLOCK_ID);
            //echo '<pre>' . print_r($block->getName(), true) . '</pre>';
            $ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array('section' => $section_id));
            $elements_id = array();
            foreach ($ElementSection as $es) {
                //echo '<pre>' . print_r($es->getSection()->getName(), true) . '</pre>';
                $elements_id[] = $es->getElement()->getId();
            }
            $elements = array();
            if ($elements_id) {
                //$elements = $em->getRepository('NovuscomCMFBundle:Element')->findBy(array('id' => $elements_id));
                $repository = $this->getDoctrine()
                    ->getRepository('NovuscomCMFBundle:Element');
                $query = $repository->createQueryBuilder('p')
                    ->where('p.id IN(:id)')
                    ->setParameter('id', $elements_id)
                    ->orderBy('p.id', 'DESC')
                    ->getQuery();
                $elements = $query->getResult();
            } else {
                //echo '<pre>' . print_r('elements_id не указан', true) . '</pre>';
                //$elements = $em->getRepository('NovuscomCMFBundle:Element')->findBy(array('block' => $block));
                $repository = $this->getDoctrine()
                    ->getRepository('NovuscomCMFBundle:Element');
                $query = $repository->createQueryBuilder('p')
                    ->where('p.block = :block')
                    ->setParameter('block', $block)
                    ->orderBy('p.id', 'DESC')
                    ->getQuery();
                $elements = $query->getResult();
            }


            /**
             * HTTP кэширование
             */
            /*$lastModifiedTimes = array();
            $timesArray = array();
            foreach ($elements as $e) {
                //echo '<pre>' . print_r($e->getLastModified(), true) . '</pre>';
                $date = $e->getLastModified();
                //$dateTime = new \DateTime('now');
                $timestamp = $date->getTimestamp();
                $lastModifiedTimes[] = $timestamp;
                $timesArray[$timestamp] = $date;
            }
            if ($timesArray) {
                //echo '<pre>' . print_r($lastModifiedTimes, true) . '</pre>';
                $max = max($lastModifiedTimes);
                $response->setLastModified($timesArray[$max]);
                $eTag = $max;
                $response->setETag($eTag);
            }
            $response->setSharedMaxAge(10);
            $response->setMaxAge(10);
            */


            $data = array(
                'block' => $block,
            );


            /**
             * Получаем previewPictures
             */
            $preview_id = array();
            foreach ($elements as $e) {
                if ($e->getPreviewPicture()) {
                    $preview_id[] = $e->getPreviewPicture()->getId();
                }
            }
            if ($preview_id) {
                $em->getRepository('NovuscomCMFBundle:File')->findBy(
                    array(
                        'id' => $preview_id
                    )
                );
            }

            $response->setMaxAge(60);
            $response->setSharedMaxAge(60);
            $render = $this->render('@templates/' . $params['template_directory'] . '/ElementsList/' . $template_code . '_.html.twig', array(
                'elements' => $elements
            ), $response);

            $cacheDriver->save($cacheId, serialize($render));
        }
        return $render;
    }

    /**
     * Lists all Element entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NovuscomCMFBundle:Element')->findAll();

        return $this->render('NovuscomCMFBundle:Element:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new Element entity.
     *
     */
    public function createAction(Request $request)
    {

        $entity = new Element();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);


        if ($form->isValid()) {



            $em = $this->getDoctrine()->getManager();
            $request = $this->container->get('request');
            $params = $request->get('_route_params');
            $block = $em->getRepository('NovuscomCMFBundle:Block')->find($params['id']);
            $section = $em->getRepository('NovuscomCMFBundle:Section')->find($params['section_id']);
            if ($section) {
                //$entity->setSection($section);
            }
            $entity->setBlock($block);


            /**
             * Свойства
             */
            if ($form->has('properties')) {

                foreach ($form->get('properties') as $p) {
                    //echo '<pre>' . print_r($p->getName(), true) . '</pre>';
                    //echo '<pre>' . print_r($p->getData(), true) . '</pre><hr/>';
                    $property = $em->getRepository('NovuscomCMFBundle:Property')->find($p->getName());
                    if (is_object($p->getData())) {

                        /**
                         * Добавляем дату/время
                         */
                        if (is_a($p->getData(), 'DateTime')) {
                            //echo '<pre>' . print_r('это дата/время', true) . '</pre>';
                            $ElementProperty = new ElementPropertyDT();
                            $ElementProperty->setElement($entity);
                            $ElementProperty->setProperty($property);
                            $ElementProperty->setValue($p->getData());
                            $em->persist($ElementProperty);
                        } else {
                            //echo '<pre>';
                            //print_r($p->getData());
                            //echo '</pre>';
                            //exit;
                        }
                    } else {
                        if (is_array($p->getData())) {
                            $property_value = $p->getData();
                            //$this->CreateUpdateFiles($p->getData(), $entity, $em);


                            foreach ($property_value as $pv) {
                                //echo '<pre>' . print_r($pv, true) . '</pre>';
                                //exit;
                                $file = new \Novuscom\CMFBundle\Entity\FormPropertyFile();

                                if ($pv instanceof $file) {

                                    //exit;
                                    //echo '<pre>' . print_r($pv->getName(), true) . '</pre>';
                                    //$this->createPreviewPicture()
                                    //$mediaController = new \CMF\MediaBundle\Controller\DefaultController();
                                    $newFile = $this->createFile($pv->getFile());


                                    //$em->flush();
                                    //$em->clear();

                                    if ($pv->getReplaceFileId()) {
                                        /**
                                         * Заменяем файл
                                         */

                                        /*
                                        foreach ($ElementProperty as $key => $ep) {
                                            echo '<pre>' . print_r('value: ' . $ep->getValue() . ', property:' . $ep->getProperty()->getId(), true) . '</pre>';
                                            if ($ep->getValue() == $pv->getReplaceFileId()) {
                                                $ep->setValue($newFile->getId());
                                                $em->persist($ep);
                                            }
                                            // удаляем зменяемый файл из базы
                                            $oldFile = $em->getRepository('CMFMediaBundle:File')->find($pv->getReplaceFileId());
                                            $em->remove($oldFile);
                                            // удаляем зменяемый файл из файловой системы
                                            $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $oldFile->getName();
                                            unlink($fileName);
                                        }*/
                                    } else {
                                        /*
                                         * Создаем значение свойства
                                         */
                                        $ElementPropertyF = new ElementPropertyF();
                                        $ElementPropertyF->setElement($entity);
                                        $ElementPropertyF->setDescription($pv->getDescription());
                                        $ElementPropertyF->setFile($newFile);
                                        $ElementPropertyF->setProperty($property);
                                        $newFile->addProperty($ElementPropertyF);
                                        $em->persist($newFile);
                                        $em->persist($ElementPropertyF);
                                        /*$ElementProperty = new ElementProperty();
                                        $ElementProperty->setValue($newFile->getId());
                                        $ElementProperty->setElement($entity);
                                        $ElementProperty->setProperty($property);
                                        //$ElementProperty->setDescription($pv->getDescription());
                                        $em->persist($ElementProperty);*/
                                    }

                                }

                            }
                            //exit;


                        } else {
                            //echo '<pre>стоп. ' . print_r($p->getData(), true) . '</pre>';
                            //exit;
                            /*$property = new ElementProperty();
                            $property->setElement($entity);
                            $property->setProperty($prop);
                            $property->setValue($p->getData());
                            $em->persist($property);*/
                        }

                    }

                }
            } else {
                //exit;
            }


            /**
             * Разделы
             */
            $sectionsCount = count($form->get('section')->getData());
            if ($sectionsCount > 0) {
                foreach ($form->get('section')->getData() as $section) {
                    $ElementSection = new ElementSection();
                    $ElementSection->setElement($entity);
                    $ElementSection->setSection($section);
                    $em->persist($ElementSection);
                    //echo '<pre>[' . print_r($section->getId(), true) . ']</pre>';
                }
            } else {
                /*
                 * для элемента не указаны разделы, добавляем пустой
                 */
                $ElementSection = new ElementSection();
                $ElementSection->setElement($entity);
                $ElementSection->setSection(null);
                $em->persist($ElementSection);
            }


            //exit;


            /**
             * Превью пикча
             */
            $this->createPreviewPicture($entity, $form['preview_picture']->getData(), $form['preview_picture_alt']->getData());

            /**
             * Детейл пикча
             */
            $this->createDetailPicture($entity, $form['detail_picture']->getData(), $form['detail_picture_alt']->getData());


            /**
             * Последнее изменение
             */

            $entity->setLastModified(new \DateTime('now'));


            $em->persist($entity);

            /**
             * Сохраниение информации в базу
             */
            $em->flush();


            /**
             * Очищаем кэш
             */
            $this->clearElementsListCache($request, $block->getId());

            /**
             * Редирект
             */
            $redirect_url = $this->generateUrl('admin_block_show', array('id' => $params['id']));
            if (array_key_exists('section_id', $params) && is_numeric($params['section_id'])) {
                $redirect_url = $this->generateUrl('admin_block_show_section', array('id' => $block->getId(), 'section_id' => $params['section_id']));
            }
            return $this->redirect($redirect_url);
        }


        return $this->render('NovuscomCMFBundle:Element:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }


    private function CreateUpdateFiles($property_value, $entity, $em)
    {
        //$em = $this->getDoctrine()->getManager();

        //exit;
        foreach ($property_value as $pv) {

            //exit;
            $file = new \Novuscom\CMFBundle\Entity\FormPropertyFile();

            if ($pv instanceof $file) {

                //exit;

                //$this->createPreviewPicture()
                //$mediaController = new \CMF\MediaBundle\Controller\DefaultController();
                $newFile = $this->createFile($pv->getFile());
                $em->persist($newFile);
                $em->flush($newFile);

                if ($pv->getReplaceFileId()) {
                    /**
                     * Заменяем файл
                     */

                    foreach ($ElementProperty as $key => $ep) {

                        if ($ep->getValue() == $pv->getReplaceFileId()) {
                            $ep->setValue($newFile->getId());
                            $em->persist($ep);
                        }
                        // удаляем зменяемый файл из базы
                        $oldFile = $em->getRepository('NovuscomCMFBundle:File')->find($pv->getReplaceFileId());
                        $em->remove($oldFile);
                        // удаляем зменяемый файл из файловой системы
                        $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $oldFile->getName();
                        unlink($fileName);
                    }
                } else {
                    /*
                     * Создаем значение свойства
                     */


                    $ElementProperty = new ElementProperty();
                    $ElementProperty->setValue($newFile->getId());
                    $ElementProperty->setElement($entity);
                    $ElementProperty->setProperty($property);
                    //$ElementProperty->setDescription($pv->getDescription());
                    $em->persist($ElementProperty);
                }

            }

        }
        exit;
    }

    private function createDetailPicture($entity, $file, $alt)
    {

        if ($file) {
            $em = $this->getDoctrine()->getManager();
            $extension = $file->guessExtension();
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/';
            if (!$extension) {
                $extension = 'bin';
            }
            $newName = $this->createRandCode() . '.' . $extension;
            $file->move($dir, $newName);
            /*
             * Создание и сохранение информации о файле
             */
            $File = new File();
            $File->setName($newName);
            $File->setType($file->getClientMimeType());
            $File->setSize($file->getClientSize());
            $File->setDescription($alt);
            $em->persist($File);
            $entity->setDetailPicture($File);
        }

    }


    private function createPreviewPicture($entity, $file, $description)
    {
        if ($file) {
            $em = $this->getDoctrine()->getManager();
            $extension = $file->guessExtension();
            $dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/';
            if (!$extension) {
                $extension = 'bin';
            }
            $newName = $this->createRandCode() . '.' . $extension;
            $file->move($dir, $newName);
            /*
             * Создание и сохранение информации о файле
             */
            $File = new File();
            $File->setName($newName);
            $File->setType($file->getClientMimeType());
            $File->setSize($file->getClientSize());
            $File->setDescription($description);
            $em->persist($File);
            $entity->setPreviewPicture($File);
        }

    }

    /**
     * Creates a form to create a Element entity.
     *
     * @param Element $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Element $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');
        $params = $request->get('_route_params');
        $block = $em->getRepository('NovuscomCMFBundle:Block')->find($params['id']);



        if ($params['section_id']) {
            $action = $this->generateUrl('admin_element_create_in_section',
                array(
                    'section_id' => $params['section_id'],
                    'id' => $params['id'],
                )
            );
        } else {
            $action = $this->generateUrl('admin_element_create_in_block',
                array(
                    'id' => $params['id'],
                )
            );
        }

        $form = $this->createForm(new ElementType(), $entity, array(
            'action' => $action,
            'method' => 'POST',
            'blockObject' => $block,
            'params' => array('_route_params' => $params),
            'em' => $em,
        ));


        $properties = $block->getProperty();
        $countProperties = count($properties);


        /*$EP = new ElementProperty();
        $propertiesForm = $this->createFormBuilder($EP);
        foreach ($properties as $p) {
            //echo '<pre>' . print_r($p->getName(), true) . '</pre>';
                $add = $propertiesForm->add('value', 'text');

        }*/
        //$propForm = $add->getForm();

        if ($countProperties > 0) {
            $propertyForm = new ElementPropertyType($properties, $em);
            $form->add('properties', $propertyForm, array('mapped' => false, 'label' => 'Свойства'));
        }

        //$form->add('properties', new ElementPropertyType($properties, $em));

        //$form->add('properties', 'collection', array('type' => new ElementPropertyType($properties, $em)));

        $form->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new Element entity.
     *
     */
    public function newAction($id, $section_id)
    {
        $em = $this->getDoctrine()->getManager();
        $block = $em->getRepository('NovuscomCMFBundle:Block')->find($id);
        $crumbs = $this->get("apy_breadcrumb_trail");
        $crumbs->add('ACMF', 'cmf_admin_homepage');
        $crumbs->add('Инфоблоки', 'admin_block');
        $crumbs->add($block->getName(), 'admin_block_show', array('id' => $block->getId()));
        if ($section_id) {
            $section = $em->getRepository('NovuscomCMFBundle:Section')->find($section_id);
            $parentsSection = $em->getRepository('NovuscomCMFBundle:Section')->createQueryBuilder('s')
                ->where("s.block=:block")
                ->andWhere("s.lft<:left")
                ->andWhere("s.rgt>:right")
                ->andWhere("s.lvl<:level")
                ->andWhere("s.root=:root")
                ->setParameters(array(
                    'block' => $block,
                    'left' => $section->getLft(),
                    'right' => $section->getRgt(),
                    'level' => $section->getLvl(),
                    'root' => $section->getRoot(),
                ))
                ->orderBy('s.lft', 'ASC')
                ->getQuery()
                ->getResult();
            foreach ($parentsSection as $p) {
                $crumbs->add($p->getName(), 'admin_block_show_section', array('id' => $block->getId(), 'section_id' => $p->getId()));
            }
            $crumbs->add($section->getName(), 'admin_block_show_section', array('id' => $block->getId(), 'section_id' => $section->getId()));
        }


        $entity = new Element();
        $form = $this->createCreateForm($entity);


        $crumbs->add('Создание элемента');
        return $this->render('NovuscomCMFBundle:Element:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Element entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Element')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Element entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Element:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),));
    }


    /**
     * Displays a form to edit an existing Element entity.
     *
     */
    public function editAction($id, $block_id, Request $request, $section_id = false)
    {
        /*ini_set('display_errors', true);
        error_reporting(E_ALL);*/
        $em = $this->getDoctrine()->getManager();
        $block = $em->getRepository('NovuscomCMFBundle:Block')->find($block_id);
        $entity = $em->getRepository('NovuscomCMFBundle:Element')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Element entity.');
        }



        //$path = $this->get('liip_imagine.cache.manager')->getBrowserPath('/upload/images/47066.jpeg', 'my_thumb');

        //$imagine = new Imagine\Gd\Imagine();
        $crumbs = $this->get("apy_breadcrumb_trail");
        $crumbs->add('ACMF', 'cmf_admin_homepage');
        $crumbs->add('Инфоблоки', 'admin_block');
        $crumbs->add($block->getName(), 'admin_block_show', array('id' => $block->getId()));

        $params = $request->get('_route_params');
        if (array_key_exists('section_id', $params)) {
            $section = $em->getRepository('NovuscomCMFBundle:Section')->find($params['section_id']);
            $parentsSection = $em->getRepository('NovuscomCMFBundle:Section')->createQueryBuilder('s')
                ->where("s.block=:block")
                ->andWhere("s.lft<:left")
                ->andWhere("s.rgt>:right")
                ->andWhere("s.lvl<:level")
                ->andWhere("s.root=:root")
                ->setParameters(array(
                    'block' => $block,
                    'left' => $section->getLft(),
                    'right' => $section->getRgt(),
                    'level' => $section->getLvl(),
                    'root' => $section->getRoot(),
                ))
                ->orderBy('s.lft', 'ASC')
                ->getQuery()
                ->getResult();
            foreach ($parentsSection as $p) {
                $crumbs->add($p->getName(), 'admin_block_show_section', array('id' => $block->getId(), 'section_id' => $p->getId()));
            }
            $crumbs->add($section->getName(), 'admin_block_show_section', array('id' => $block->getId(), 'section_id' => $section->getId()));
        }


        $crumbs->add($entity->getName());


        $editForm = $this->createEditForm($entity);

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Element:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'block' => $block,
        ));
    }

    /**
     * Creates a form to edit a Element entity.
     *
     * @param Element $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Element $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->container->get('request');
        $params = $request->get('_route_params');
        $block = $em->getRepository('NovuscomCMFBundle:Block')->find($params['block_id']);

        $epArray = array();

        /**
         * Пролучаем значения свойств типа "строка"
         */
        $ElementProperty = $em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(
            array(
                'element' => $entity,
            )
        );
        // TODO Здесь можно сделать попроще и попонятней, чтобы в форму сразу попадала коллекция
        foreach ($ElementProperty as $ep) {
            $epArray[$ep->getProperty()->getId()][] = $ep->getValue();
        }

        /**
         * Получаем значения свойств типа "дата/время"
         */
        $ElementPropertyDT = $em->getRepository('NovuscomCMFBundle:ElementPropertyDT')->findBy(
            array(
                'element' => $entity,
            )
        );
        foreach ($ElementPropertyDT as $ep) {
            $epArray[$ep->getProperty()->getId()][] = $ep->getValue();
        }

        /**
         * Получаем значения свойств типа "файл"
         */
        $ElementPropertyFile = $em->getRepository('NovuscomCMFBundle:ElementPropertyF')->findBy(
            array(
                'element' => $entity,
            )
        );

        $ElementPropertyFileId = array();
        foreach ($ElementPropertyFile as $epf) {
            $ElementPropertyFileId[$epf->getProperty()->getId()][] = $epf->getFile()->getId();
        }


        /**
         * Устанавливаем значения для формы
         */
        $data = array(
            'VALUES' => $epArray,
            'PROPERTY_FILE_VALUES' => $ElementPropertyFileId,
            'LIIP' => $this->get('liip_imagine.cache.manager'),

        );


        $propertyForm = new ElementPropertyType($block->getProperty(), $em, $data);

        //$formProperty = new FormProperty();
        //$formProperty->setValue('value of form property');

        /*$formElement = new FormElement();
        $formElement->setName($entity->getName());
        $formElement->setCode($entity->getCode());
        $formElement->setProperties($formProperty);*/

        $action_url = $this->generateUrl('admin_element_update', array('id' => $entity->getId(), 'block_id' => $params['block_id']));
        if (array_key_exists('section_id', $params)) {
            $action_url = $this->generateUrl('admin_element_update_in_section', array(
                'id' => $entity->getId(),
                'block_id' => $params['block_id'],
                'section_id' => $params['section_id']
            ));
        }
        $form = $this->createForm(new ElementType(), $entity, array(
            'action' => $action_url,
            'method' => 'PUT',
            'em' => $em,
            'blockObject' => $block
        ));


        $form->add('properties', $propertyForm,
            array(
                'mapped' => false,
                'label' => 'Свойства',

            ));


        $form->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn btn-info')));

        return $form;
    }


    /**
     * @param Element $entity Элемент, разделы котрого необходимо получить
     * @return array Возвращает массив содержащий коллекцию разделов и их айдишники (в том числе NULL)
     */
    private function getElementSections($entity)
    {
        $em = $this->getDoctrine()->getManager();
        $sectionsId = array();
        $entitySections = $entity->getSection();
        $sections = new ArrayCollection();
        $countEntitySections = count($entitySections);

        if ($countEntitySections > 0) {
            foreach ($entitySections as $o) {
                ///echo '<pre>' . print_r($o->getId(), true) . '</pre>';

                if ($o->getSection()) {
                    $sectionsId[] = $o->getSection()->getId();
                } else {
                    $sections->add(null);
                }
            }


            if ($sectionsId) {
                $sectionsById = $em->getRepository('NovuscomCMFBundle:Section')->findBy(array('id' => $sectionsId));
                foreach ($sectionsById as $s) {
                    $sections->add($s);
                }
            }

        }

        return $sections;
    }

    /**
     *
     * Обновляет разделы элемента
     *
     * @param Element $entity Элемент для которого надо изменить разделы
     * @param Doctrine\Common\Collections\ArrayCollection $newSections Новые разделы элемента
     * @return bool
     */

    private function sectionsUpdate($entity, $newSections)
    {
        $em = $this->getDoctrine()->getManager();
        $result = false;
        $originalSections = $this->getElementSections($entity);


        foreach ($originalSections as $section) {
            //if ($section)
            //    echo '<pre>' . print_r($section->getName(), true) . '</pre>';
        }


        foreach ($newSections as $section) {
            //if ($section)
            //    echo '<pre>' . print_r($section->getName(), true) . '</pre>';
        }


        /**
         * Готовим массив ИД разделов из которых элемент должен быть удален
         */
        $deleteSectionsId = array();
        foreach ($originalSections as $o) {
            // Раздел к которому был привязан элемент отсутствует в списке раздлов к которым нужно привязать элемент
            //echo '<pre>' . print_r($o->getName(), true) . '</pre>';
            if (!$newSections->contains($o)) {
                $deleteSectionsId[] = $o->getSection()->getId();
            }
        }

        /**
         * Готовим массив новых разделов к которым необходимо привязать элемент
         */
        $addSections = new ArrayCollection();
        foreach ($newSections as $n) {
            // Элемент не был привязан к данному разделу - добавляем его в списко новых
            if (!$originalSections->contains($n)) {
                $addSections->add($n);
            }
        }


        //echo '<pre>' . print_r('ИД удаляемых разделов', true) . '</pre>';
        //echo '<pre>' . print_r($deleteSectionsId, true) . '</pre>';

        /**
         * Удаляем элементы из разделов
         */
        /*
        if ($deleteSections) {
            // Проходим по всем привязкам к разделам
            foreach ($entity->getSection() as $s) {
                // Если раздел есть в списке удаляемых - удаляем
                if ($deleteSections->contains($s)) {
                    $em->remove($s);
                }
            }
        }
        */


        //exit;

        $deleteSectionsId = array();
        if ($originalSections->isEmpty()) {
        } else {
            /*
             * У элемета были разделы
             */
            //echo '<pre>' . print_r('Оригнальные разделы:', true) . '</pre>';
            foreach ($originalSections as $o) {
                if (!$newSections->contains($o)) {
                    /*
                     * Добавляем раздел в список удаляемых, т.к. он не содержится в новом списке разделов
                     */
                    $deleteSectionsId[] = $o->getId();
                }
            }
        }
        /*
         * Создаем массив новых разделов
         */
        $addSections = new ArrayCollection();
        foreach ($newSections as $n) {
            if (!$originalSections->contains($n)) {
                $addSections->add($n);
            }
        }

        /*
         * Удаляем разделы
         */
        if ($deleteSectionsId) {
            foreach ($entity->getSection() as $obj) {
                if (in_array($obj->getId(), $deleteSectionsId)) {
                    $em->remove($obj);
                }
            }
        }

        $countAddSections = count($addSections);
        if ($countAddSections > 0) {
            /*
             * Добавляем элемент в разделы
             */
            foreach ($addSections as $obj) {
                $ElementSection = new ElementSection();
                $ElementSection->setElement($entity);
                $ElementSection->setSection($obj);
                $em->persist($ElementSection);
            }
        }
        if ($countEntitySections == 0 && $countAddSections == 0) {
            /*
             * У элемента не было разделов вообще (не было связи с разделдами, даже NULL)
             * Новых раздлов у элемента также нет
             * Создаем связь с NULL разделом
             */
            $ElementSection = new ElementSection();
            $ElementSection->setElement($entity);
            $ElementSection->setSection(null);
            $em->persist($ElementSection);
        }

        return $result;
    }


    /**
     * Edits an existing Element entity.
     *
     */
    public function updateAction(Request $request, $id, $block_id, $section_id = false)
    {
        //echo '<pre>' . print_r('updateAction', true) . '</pre>';
        $em = $this->getDoctrine()->getManager();
        $Element = $this->get('Element');

        $entity = $em->getRepository('NovuscomCMFBundle:Element')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Element entity.');
        }


        /**
         * Получаем разделы к которым принадлежал элемент до отправки формы
         */
        $oldSections = $Element->getSections($entity);

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        //echo '<pre>' . print_r('Начинаем проверять форму', true) . '</pre>'; exit;
        if ($editForm->isValid()) {

            ///echo '<pre>' . print_r('Форма валидна', true) . '</pre>'; exit;

            $newSections = new ArrayCollection();
            if ($editForm->has('section')) {
                foreach ($editForm->get('section')->getData() as $s) {
                    $newSections->add($s);
                }
            }


            //mail('averichev@yandex.ru', '$data', print_r($editForm->get('properties'), true));


            //echo '<pre>' . print_r($editForm->getData(), true) . '</pre>';
            /*foreach ($editForm->get('properties') as $property) {
                echo '<pre>' . print_r($property->getName(), true) . '</pre>';
                echo '<pre>' . print_r($property->get('code')->getData(), true) . '</pre>';
            }*/

            if ($editForm->has('properties')) {
                //echo '<pre>' . print_r('У формы есть свойства', true) . '</pre>'; exit;

                /**
                 * Собираем массив свойств
                 */
                $propArray = array();
                foreach ($editForm->get('properties') as $p) {
                    if ($p->getData()) {
                        $propArray[$p->getName()] = $p->getData();
                        //echo '<pre>' . print_r($p->getData(), true) . '</pre>';
                    }
                }
                $keys = array_keys($propArray);
                //echo '<pre>' . print_r($keys, true) . '</pre>'; exit;

                /**
                 * Получаем свойства элемента
                 */
                $ElementProperty = $em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(
                    array(
                        'element' => $entity,
                    )
                );
                $countEP = count($ElementProperty);

                /**
                 * Тестовые свойства уже существуют - обрабатываем их
                 */
                if ($countEP > 0) {
                    $updatedId = array();
                    foreach ($ElementProperty as $key => $ep) {
                        //echo '<hr/><pre>' . print_r('$ep->getProperty()->getId() - ' . $ep->getProperty()->getId(), true) . '</pre>';
                        //echo '<pre>' . print_r('$ep->getValue() - '.$ep->getValue(), true) . '</pre><hr/>';
                        //echo '<pre>$key: ' . print_r($key, true) . '</pre>';
                        //echo '<pre>' . print_r($ep->getValue(), true) . '</pre>';

                        //echo '<pre>[id=' . print_r($ep->getId() . ', value=' . $ep->getValue() . ', property_id=' . $ep->getProperty()->getId(), true) . ']</pre>';
                        if (array_key_exists($ep->getProperty()->getId(), $propArray)) {
                            //echo '<pre>' . print_r('propArray[' . $ep->getProperty()->getId() . '] существует', true) . '</pre>';
                            $val = $propArray[$ep->getProperty()->getId()];
                            //echo '<pre>' . print_r($val, true) . '</pre>';
                            if (!is_object($val)) {
                                //echo '<pre>' . print_r('Не объект', true) . '</pre>';
                                //echo '<pre>' . print_r($val, true) . '</pre>';
                                if (is_array($val)) {
                                    //echo '<pre>' . print_r('Массив, $ep->getId()=' . $ep->getId(), true) . '</pre>';
                                    //echo '<pre>' . print_r($val, true) . '</pre>';

                                    /*foreach ($val as $keyVal=>$valueVal) {
                                        //echo '<pre>' . print_r('['.$ep->getProperty()->getId().']', true) . '</pre>';
                                        $file = new \CMF\MediaBundle\Entity\File();

                                        if ($valueVal instanceof $file) {

                                        }
                                        //echo '<pre>' . print_r($propArray[$ep->getProperty()->getId()], true) . '</pre>';

                                        //unset($propArray[$ep->getProperty()->getId()]);
                                        //break;
                                    }*/
                                    $updatedId[] = $ep->getId();
                                } else {
                                    //echo '<pre>' . print_r('Не массив', true) . '</pre>';
                                    //echo '<pre>' . print_r($val, true) . '</pre>';
                                    $ep->setValue($val);
                                    $updatedId[] = $ep->getId();
                                    //echo '<pre>' . print_r('Обновляем ' . $key, true) . '</pre>';
                                    //$updatedId[] = $key;
                                    $em->persist($ep);
                                    unset($propArray[$ep->getProperty()->getId()]);
                                }

                            } else {
                                //echo '<pre>' . print_r('Объект', true) . '</pre>';
                                //echo '<pre>' . print_r($val, true) . '</pre>';
                                foreach ($propArray[$ep->getProperty()->getId()] as $k => $v) {
                                    //echo '<pre>' . print_r($v->getValue(), true) . '</pre><hr/>';
                                    $ep->setValue($v->getValue());
                                    $updatedId[] = $ep->getId();
                                    //echo '<pre>' . print_r('Обновляем ' . $key, true) . '</pre>';
                                    //$updatedId[] = $key;
                                    $em->persist($ep);
                                    //$val->remove($k);
                                    $propArray[$ep->getProperty()->getId()]->remove($k);

                                    break;
                                }
                                //echo '<pre>' . print_r($val, true) . '</pre>';
                                if ($val->isEmpty()) {
                                    //echo '<pre>' . print_r('Удаляем коллекецию, т.к. она уже пустая', true) . '</pre>';
                                    unset($propArray[$ep->getProperty()->getId()]);
                                }

                            }

                        }
                    }

                    //exit;

                    //echo 'Обновленные свойства: <pre>' . print_r($updatedId, true) . '</pre>';
                    //echo '<pre>' . print_r($propArray, true) . '</pre>';

                    foreach ($ElementProperty as $key => $ep) {
                        if (!in_array($ep->getId(), $updatedId)) {
                            //if (!in_array($key, $updatedId)) {
                            //echo '<pre>' . print_r('Удаляем $key=' . $key . ', $ep->getId()=' . $ep->getId() . ', ' . $ep->getValue(), true) . '</pre>';
                            $em->remove($ep);
                        }
                    }

                    //exit;


                }
                // echo '<pre>' . print_r('Текстовые свойства обработаны', true) . '</pre>'; exit;


                /**
                 * Обрабатываем  дату/время
                 */
                $ElementPropertyDT = $em->getRepository('NovuscomCMFBundle:ElementPropertyDT')->findBy(
                    array(
                        'element' => $entity,
                    )
                );
                $updatedId = array();
                foreach ($ElementPropertyDT as $ep) {
                    if (array_key_exists($ep->getProperty()->getId(), $propArray)) {
                        $val = $propArray[$ep->getProperty()->getId()];
                        $ep->setValue($val);
                        $updatedId[] = $ep->getId();
                        $em->persist($ep);
                        unset($propArray[$ep->getProperty()->getId()]);
                        break;
                    }
                }
                foreach ($ElementPropertyDT as $ep) {
                    if (!in_array($ep->getId(), $updatedId)) {
                        $em->remove($ep);
                    }
                }


                //exit;

                if ($propArray) {
                    //echo '<pre>' . print_r('Свойства остались', true) . '</pre>';
                    //echo '<pre>' . print_r($propArray, true) . '</pre>';

                    foreach ($propArray as $property_id => $property_value) {
                        $property = false;
                        if (is_numeric($property_id)) {
                            $property = $em->getRepository('NovuscomCMFBundle:Property')->find($property_id);
                        }

                        //echo '<hr/>$property_value:<pre>' . print_r($property_value, true) . '</pre><hr/>';

                        if (!is_object($property_value)) {
                            //echo '<hr/>Это не объект:<pre>' . print_r($property_value, true) . '</pre><hr/>';
                            if (is_array($property_value)) {
                                //echo '<hr/>Это массив:<pre>' . print_r($property_value, true) . '</pre><hr/>';
                                foreach ($property_value as $pv) {
                                    //echo '<pre>' . print_r($pv, true) . '</pre>';

                                    $file = new \Novuscom\CMFBundle\Entity\FormPropertyFile();

                                    if ($pv instanceof $file) {

                                        //exit;
                                        //echo '<pre>' . print_r($pv->getName(), true) . '</pre>';
                                        //$this->createPreviewPicture()
                                        //$mediaController = new \CMF\MediaBundle\Controller\DefaultController();

                                        //$em->flush();
                                        //$em->clear();

                                        /**
                                         * Заменяем файл
                                         */
                                        $ElementPropertyF = $em->getRepository('NovuscomCMFBundle:ElementPropertyF')->findBy(
                                            array(
                                                'element' => $entity,
                                            )
                                        );

                                        //if ($pv->getDeleteFileId()){
                                        if (false) {
                                            $this->deleteElementFiles($entity, $pv->getDeleteFileId());
                                            foreach ($ElementPropertyF as $key => $ep) {
                                                if ($ep->getFile()->getId() == $pv->getDeleteFileId()) {
                                                    $em->remove($ep);
                                                }
                                            }
                                        } else {
                                            if ($pv->getReplaceFileId()) {

                                                //echo '<pre> $property_id=' . print_r($property_id, true) . '</pre>';


                                                //echo '<pre>' . print_r('Заменяем файл', true) . '</pre>';

                                                foreach ($ElementPropertyF as $key => $ep) {
                                                    //echo '<pre>' . print_r('fil_id: ' . $ep->getFile()->getId() . ', property:' . $ep->getProperty()->getId(), true) . '</pre>';
                                                    //echo '<pre>' . print_r('$pv->getReplaceFileId()=' . $pv->getReplaceFileId(), true) . '</pre>';
                                                    if ($ep->getFile()->getId() == $pv->getReplaceFileId()) {
                                                        $newFile = $this->createFile($pv->getFile());
                                                        $em->persist($newFile);
                                                        $ep->setFile($newFile);
                                                        $em->persist($ep);
                                                        $oldFile = $em->getRepository('NovuscomCMFBundle:File')->find($pv->getReplaceFileId());
                                                        $em->remove($oldFile);
                                                        $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $oldFile->getName();
                                                        if (is_file($fileName)) {
                                                            unlink($fileName);
                                                        }

                                                    }
                                                }
                                                //exit;
                                            } else {
                                                /*
                                                 * Создаем значение свойства
                                                 */
                                                $newFile = $this->createFile($pv->getFile());
                                                $em->persist($newFile);
                                                //echo '<pre>' . print_r('создаем новый файл', true) . '</pre>';
                                                //echo '<pre>' . print_r($pv, true) . '</pre>';
                                                $ElementPropertyF = new ElementPropertyF();
                                                $ElementPropertyF->setFile($newFile);
                                                $ElementPropertyF->setElement($entity);
                                                $ElementPropertyF->setProperty($property);
                                                $ElementPropertyF->setDescription($pv->getDescription());
                                                $em->persist($ElementPropertyF);
                                            }
                                        }

                                    }
                                    $ElementProperty = new ElementProperty();
                                    if ($pv instanceof $ElementProperty) {
                                        //echo '<pre>' . print_r('это свойство', true) . '</pre>';
                                        $ElementProperty->setValue($pv->getValue());
                                        $ElementProperty->setElement($entity);
                                        $ElementProperty->setProperty($property);
                                        $em->persist($ElementProperty);
                                    }
                                }
                            } else {
                                /**
                                 * Создание свойства
                                 */
                                $ElementProperty = new ElementProperty();
                                $ElementProperty->setValue($property_value);
                                $ElementProperty->setElement($entity);
                                $ElementProperty->setProperty($property);
                                $em->persist($ElementProperty);
                            }

                        } else {
                            /**
                             * Добавления свойства типа "дата/время"
                             */
                            if (is_a($property_value, 'DateTime')) {
                                //echo '<pre>' . print_r('это дата/время', true) . '</pre>';
                                $ElementProperty = new ElementPropertyDT();
                                $ElementProperty->setElement($entity);
                                $ElementProperty->setProperty($property);
                                $ElementProperty->setValue($property_value);
                                $em->persist($ElementProperty);
                            } else {
                                foreach ($propArray[$property_id] as $k => $v) {
                                    //echo '<pre>' . print_r($v, true) . '</pre>';
                                    $ElementProperty = new ElementProperty();
                                    $ElementProperty->setValue($v->getValue());
                                    $ElementProperty->setElement($entity);
                                    $ElementProperty->setProperty($property);
                                    $em->persist($ElementProperty);
                                    //$val->remove($k);
                                    $propArray[$property_id]->remove($k);
                                    //break;
                                }
                                if ($propArray[$property_id]->isEmpty()) {
                                    //echo '<pre>' . print_r('Удаляем коллекецию, т.к. она уже пустая', true) . '</pre>';
                                    unset($propArray[$property_id]);
                                }
                            }


                        }
                    }
                }


            }
            //echo '<pre>' . print_r($propArray, true) . '</pre>';
            //exit;
            //echo '<hr/>';


            /**
             * Обновление разделов
             */
            $Element->updateSections($entity, $newSections, $oldSections);


            /**
             * Превью пикча
             */
            $file = $editForm['preview_picture']->getData();
            if ($file) {
                //echo '<pre>' . print_r($file, true) . '</pre>';
                $this->deletePreviewPicture($entity);
                $this->createPreviewPicture($entity, $file, $editForm['preview_picture_alt']->getData());
            } else {
                //echo '<pre>' . print_r('Нет превью пикчи', true) . '</pre>';
            }
            $this->downloadFile($editForm['preview_picture_src']->getData(), $entity, 'preview');


            /*
             * Обновление описания картинки
             */
            if ($preview_picture = $entity->getPreviewPicture())
                $preview_picture->setDescription($editForm['preview_picture_alt']->getData());
            //$em->persist($preview_picture);


            /**
             * Детейл пикча
             */
            $file = $editForm['detail_picture']->getData();
            if ($file) {
                $this->deleteDetailPicture($entity);
                $this->createDetailPicture($entity, $file, $editForm['detail_picture_alt']->getData());
            } else {
                //echo '<pre>' . print_r('Нет детейл пикчи', true) . '</pre>';
            }
            $this->downloadFile($editForm['detail_picture_src']->getData(), $entity, 'detail');

            if ($detail_picture = $entity->getDetailPicture()) {
                //echo '<pre>' . print_r('есть detailPicture', true) . '</pre>';
                //exit;
                $detail_picture->setDescription($editForm['detail_picture_alt']->getData());
                $em->persist($detail_picture);;
            }



            $entity->setLastModified(new \DateTime('now'));




            //exit;
            $em->flush();


            /**
             * Очищаем кэш
             */
            $this->clearElementsListCache($request, $block_id);
            $this->clearElementCache($request, $block_id);

            /**
             * Редирект
             */
            $redirect_url = $this->generateUrl('admin_element_edit', array('id' => $id, 'block_id' => $block_id));
            if ($section_id) {
                $redirect_url = $this->generateUrl('admin_element_edit_in_section', array('id' => $id, 'block_id' => $block_id, 'section_id' => $section_id));
            }
            return $this->redirect($redirect_url);
        } else {
            //echo '<pre>' . print_r('форма не валидна', true) . '</pre>';
            //echo $editForm->getErrorsAsString();

        }


        return $this->render('NovuscomCMFBundle:Element:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    private function downloadFile($file_src, $entity, $type)
    {
        $result = false;
        if ($file_src) {
            $em = $this->getDoctrine()->getManager();
            $client = $this->get('Guzzle');
            $response = $client->get($file_src);
            $file_path = $this->getUploadDir() . $this->createRandCode() . '.jpg';
            $response->setResponseBody($file_path);
            $response->send();

            $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file_path);

            $File = new File();
            $File->setName(basename($file_path));
            $File->setType($mime);
            $File->setSize(filesize($file_path));
            $em->persist($File);
            if ($type == 'preview') {
                $this->deletePreviewPicture($entity);
                $entity->setPreviewPicture($File);
            }
            if ($type == 'detail') {
                $this->deleteDetailPicture($entity);
                $entity->setDetailPicture($File);
            }
            $result = true;
        }
        return $result;
    }

    private function clearElementCache($request, $block_id)
    {
        $env = $this->get('kernel')->getEnvironment();
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $nameSpace = 'ElementAction_' . $env . '_' . $block_id;
        $cacheDriver->setNamespace($nameSpace);
        $cacheDriver->deleteAll();
        $cacheDriver->setNamespace('CrumbsAction_' . $env);
        $cacheDriver->deleteAll();
    }

    private function clearElementsListCache($request, $block_id)
    {

        $env = $this->get('kernel')->getEnvironment();
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $cacheDriver->setNamespace('ElementsListAction_' . $env . '_' . $block_id);
        $cacheDriver->deleteAll();
    }

    private function getUploadDir()
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/upload/images/';
    }

    private function createRandCode()
    {
        //return rand(1, 9999999999);
        return md5(time());
    }

    private function createFile($file)
    {
        //echo '<pre>' . print_r('createFile()', true) . '</pre>';
        //exit;

        $extension = $file->guessExtension();
        $dir = $this->getUploadDir();
        if (!$extension) {
            $extension = 'bin';
        }
        $newName = $this->createRandCode() . '.' . $extension;
        $file->move($dir, $newName);
        /*
         * Создание и сохранение информации о файле
         */
        $File = new File();
        $File->setName($newName);
        $File->setType($file->getClientMimeType());
        $File->setSize($file->getClientSize());
        return $File;
    }

    private function deleteDetailPicture(\Novuscom\CMFBundle\Entity\Element $element)
    {
        $em = $this->getDoctrine()->getManager();
        $picture = $element->getDetailPicture();
        if ($picture) {
            $element->setDetailPicture(null);
            $em->persist($element);
            $em->remove($picture);
            $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $picture->getName();
            $em->flush();
            unlink($fileName);
        }
    }

    private function deletePreviewPicture(\Novuscom\CMFBundle\Entity\Element $element)
    {
        $em = $this->getDoctrine()->getManager();
        $previewPicture = $element->getPreviewPicture();
        if ($previewPicture) {
            $element->setPreviewPicture(null);
            $em->persist($element);
            $em->remove($previewPicture);
            $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $previewPicture->getName();
            $em->flush();
            unlink($fileName);
        }
    }

    private function deleteElementFiles($entity, $filesId = array())
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * Удаление файлов элемента (свойств)
         */
        /*
         * Находим свойства типа файл
         */
        $fileProperties = $em->getRepository('NovuscomCMFBundle:Property')->findBy(
            array(
                'type' => 'F',
            )
        );
        $filePropertiesId = array();
        foreach ($fileProperties as $fp) {
            $filePropertiesId[] = $fp->getId();
        }
        //echo '<pre>' . print_r($filePropertiesId, true) . '</pre>';


        if ($filePropertiesId) {
            /*
            * Находим значания свойств элемента типа файл
            */
            $ElementProperty = $em->getRepository('NovuscomCMFBundle:ElementPropertyF')->findBy(
                array(
                    'element' => $entity,
                    'property' => $filePropertiesId
                )
            );
            $filesId = array();
            foreach ($ElementProperty as $ep) {
                $filesId[] = $ep->getFile()->getId();
            }
            if ($filesId) {
                /*
                 * Получаем файлы которые надо удалить
                 */
                $files = $em->getRepository('NovuscomCMFBundle:File')->findBy(
                    array(
                        'id' => $filesId,
                    )
                );
                foreach ($files as $f) {
                    if (!$filesId || ($filesId && in_array($f->getId(), $filesId))) {
                        $fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $f->getName();
                        //echo '<pre>' . print_r($fileName, true) . '</pre>';
                        $em->remove($f);
                        unlink($fileName);
                    }
                }

            }
        }


    }

    /**
     * Deletes a Element entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        //echo '<pre>' . print_r('deleteAction();', true) . '</pre>';
        if ($form->isValid()) {
            //echo '<pre>' . print_r($child->getName(), true) . '</pre>';
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NovuscomCMFBundle:Element')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Element entity.');
            }

            /**
             * Удаление превью пикчи
             */
            $this->deletePreviewPicture($entity);


            /**
             * Удаление файлов элемента
             */
            $this->deleteElementFiles($entity);

            //exit;

            $elementSection = $entity->getSection();
            $elementBlock = $entity->getBlock();

            $url = $this->generateUrl('admin_block_show', array('id' => $elementBlock->getId()));

            //if ($elementSection) {
            if (false) {
                $url = $this->generateUrl('admin_block_show_section', array('id' => $elementBlock->getId(), 'section_id' => $elementSection->getId()));
            }

            $em->remove($entity);
            $em->flush();

        } else {
            //echo '<pre>' . print_r($form->getErrors(), true) . '</pre>';
        }

        return $this->redirect($url);


    }

    /**
     * Creates a form to delete a Element entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_element_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Удалить', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm();
    }
}
