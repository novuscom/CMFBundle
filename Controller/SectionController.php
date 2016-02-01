<?php

namespace Novuscom\Bundle\CMFBundle\Controller;

use Novuscom\Bundle\CMFBundle\Entity\FrontSection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\Bundle\CMFBundle\Entity\File;
use Novuscom\Bundle\CMFBundle\Entity\Section;
use Novuscom\Bundle\CMFBundle\Entity\Page;
use Novuscom\Bundle\CMFBundle\Form\SectionType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Section controller.
 *
 */
class SectionController extends Controller
{

	public function blockSectionAction($BLOCK_CODE, $SECTION_CODE_PATH, $siteId, $pageId)
	{
		//echo '<pre>' . print_r('blockSectionAction()', true) . '</pre>';
		$em = $this->getDoctrine()->getManager();
		//echo '<pre>' . print_r($BLOCK_CODE, true) . '</pre>';
		//echo '<pre>' . print_r($SECTION_CODE_PATH, true) . '</pre>';


		$path = preg_replace('/^\/?(.+?)\/?$/', '\\1', $SECTION_CODE_PATH);
		$codeArray = explode('/', $path);
		$maxLevel = count($codeArray) - 1;
		//echo '<pre>' . print_r($BLOCK_CODE, true) . '</pre>';
		//echo '<pre>' . print_r($codeArray, true) . '</pre>';


		$block = $em->getRepository('NovuscomCMFBundle:Block')->findOneBy(array('code' => $BLOCK_CODE));


		$rootPage = $em->getRepository('NovuscomCMFBundle:Page')->findOneBy(array(
			'site' => $siteId,
			'lvl' => 0
		));
		$page = $em->getRepository('NovuscomCMFBundle:Page')->find($pageId);
		$crumbs = $this->get("apy_breadcrumb_trail");
		$crumbs->add($rootPage->getName(), 'cmf_page_main');
		$crumbs->add($page->getName(), 'cmf_page_frontend', array("name" => $page->getUrl()));
		$crumbs->add($block->getName(), 'voenkom_block', array('BLOCK_CODE' => $BLOCK_CODE));


		$er = $em->getRepository('NovuscomCMFBundle:Section');
		$rootSection = $er->findOneBy(array('lvl' => 0, 'block' => $block, 'code' => $codeArray[0]));

		if (!$rootSection) {
			//echo '<pre>' . print_r('!$rootSection', true) . '</pre>';
			//echo '<pre>' . print_r('макс. уровень: '.$maxLevel, true) . '</pre>';
			//$elementController = new \Novuscom\Bundle\CMFBundle\Controller\ElementController();
			//$elementController = $this->get('cmf.block.element.controller');
			//$elementController->elementPageAction($codeArray[0], $block, null);
			if ($maxLevel == 0) {
				$response = $this->forward('NovuscomCMFBundle:Element:elementPage', array(
					'ELEMENT_CODE' => $codeArray[0],
					'BLOCK_ID' => $block->getId(),
					'pageId' => $page->getId(),
					'block' => $block,
					'section' => null,

				));
				return $response;
			} else {
				throw $this->createNotFoundException('Не совпадают уровни в корне инфоблока');
			}

			//throw $this->createNotFoundException('Не найден корневой раздел с кодом '.$codeArray[0].'. Ищем элемент с таким кодом без раздела (в корне) инфоблока '.$block->getName());
		}

		$crumbs->add($rootSection->getName(), 'voenkom_block_section', array("SECTION_CODE_PATH" => $rootSection->getCode(), 'BLOCK_CODE' => $BLOCK_CODE));

		//echo '<pre>' . print_r('Корневой раздел: '.$rootSection->getName().', '.$rootSection->getId(), true) . '</pre><hr/>';

		//echo '<pre>' . print_r('Максимальный уровень: '.$maxLevel, true) . '</pre>';

		/*
		$sections = $er->createQueryBuilder('s')
			->where("s.block=:block")
			->andWhere("s.code IN (:code)")
			->andWhere("s.lft>:left")
			->andWhere("s.rgt<:right")
			->andWhere("s.lvl<=:level")
			->andWhere("s.root<=:root")
			->setParameters(array(
				'block' => $block,
				'code' => $codeArray,
				'left' => $rootSection->getLft(),
				'right' => $rootSection->getRgt(),
				'level' => $maxLevel,
				'root'=>$rootSection->getId(),
			))
			->orderBy('s.lft', 'ASC')
			->getQuery()
			->getResult();
		*/

		$sections = $er->getChildren(
			$rootSection,
			null,
			'lvl',
			'asc'
		);

		/*
		echo '<hr/><p>Потомки:</p>';
		foreach ($sections as $c) {
			echo '<pre>' . print_r('уровень: '.$c->getLvl().', ид: '.$c->getId().',  код: '.$c->getCode().', родитель: '.$c->getParent()->getId().', '.$c->getName(), true) . '</pre>';
		}
		echo '<hr/>';
		*/

		$count = count($sections);
		if ($count > 0) {

			//echo '<pre>' . print_r('Найдено разделов: '.$count, true) . '</pre>';

			$need = array();


			$htmlTree = $er->childrenHierarchy(
				$rootSection, // starting from root nodes
				false, //true: load all children, false: only direct
				array(
					'decorate' => true,
					'rootOpen' => '<ul>',
					'rootClose' => '</ul>',
					'childOpen' => '<li>',
					'childClose' => '</li>',
					'nodeDecorator' => function ($node) {
						return '<a href="/page/' . $node['code'] . '">' . $node['code'] . '</a> ' . $node['id'];
					}
				)
			);
			//echo $htmlTree;


			/*
			echo '<hr/><p>Найденные разделы: </p>';
			foreach ($sections as $k=>$s) {
				echo '<pre>' . print_r($s->getName(), true) . '</pre>';
			}
			*/


			$prevParent = $rootSection->getId();
			$needMaxLevel = 0;
			$needCodes = array();
			foreach ($sections as $k => $s) {
				$parent = $s->getParent()->getId();
				//echo '<pre>' . print_r($k.': '. $s->getName(), true) . '</pre>';
				//echo '<pre>' . print_r('Ид родителя: '.$prevParent, true) . '</pre>';
				//echo '<pre>' . print_r('Ид родителя текущего: '.$parent, true) . '</pre>';
				//echo '<hr/>';
				$level = $s->getLvl();
				if (isset($codeArray[$level]) && $codeArray[$level] == $s->getCode() && $prevParent == $parent) {
					//$need[] = $s->getName();
					$need[] = $s;
					$needCodes[] = $s->getCode();
					$prevParent = $s->getId();
					if ($s->getLvl() > $needMaxLevel) {
						$needMaxLevel = $s->getLvl();
					}
				}
			}

			//echo '<pre>' . print_r($rootSection->getCode(), true) . '</pre>';
			$path = $rootSection->getCode();
			foreach ($need as $n) {
				$path = $path . '/' . $n->getCode();
				$crumbs->add($n->getName(), 'voenkom_block_section', array("SECTION_CODE_PATH" => $path, 'BLOCK_CODE' => $BLOCK_CODE));

				//echo '<pre>' . print_r($path, true) . '</pre>';
			}

			array_shift($codeArray);
			$diff = array_values(array_diff($codeArray, $needCodes));
			$countDiff = count($diff);

			if (!empty($need)) {
				end($need);
				$need = current($need);
				if ($need->getLvl() != $maxLevel) {
					if ($countDiff == 1) {
						//echo '<pre>Ищем элемент с кодом: ' . print_r($diff[0], true) . ' в разделе '.$need->getName().'</pre>';
						$response = $this->forward('NovuscomCMFBundle:Element:elementPage', array(
							'ELEMENT_CODE' => $diff[0],
							'block' => $block,
							'section' => $need,

						));
						return $response;
					} else {
						//echo '<pre>Ищем элементы по пути: ' . print_r($diff, true) . '</pre>';
					}
					throw $this->createNotFoundException('Не совпадают уровни 1');

				}
			} else {
				$need = $rootSection;
				//echo '<pre>' . print_r($need->getName().', уровень: '.$need->getLvl().', макс. уровень: '.$maxLevel, true) . '</pre>';
				if ($need->getLvl() != $maxLevel) {
					if ($countDiff == 1) {
						echo '<pre>Ищем элемент с кодом: ' . print_r($diff[0], true) . ' в разделе ' . $need->getName() . '</pre>';
					} else {
						//echo '<pre>Ищем элементы по пути: ' . print_r($diff, true) . '</pre>';
					}
					throw $this->createNotFoundException('Не совпадают уровни 2');
				}
			}


		} else {
			//echo '<pre>' . print_r('Найдено разделов: '.$count, true) . '</pre>';
			//echo '<pre>' . print_r('Уровень корня: '.$rootSection->getLvl(), true) . '</pre>';
			if ($maxLevel > 0) {
				array_shift($codeArray);
				$diff = array_values(array_diff($codeArray, array($rootSection->getCode())));
				$countDiff = count($diff);
				//echo '<pre>' . print_r($diff, true) . '</pre>';
				if ($countDiff == 1) {
					$response = $this->forward('NovuscomCMFBundle:Element:elementPage', array(
						'ELEMENT_CODE' => $diff[0],
						'block' => $block,
						'section' => $rootSection,
					));
					return $response;

				} else {
					throw $this->createNotFoundException('Не найдены разделы');

				}
				//throw $this->createNotFoundException('Не найдены разделы');
			}
			$need = $rootSection;
		}


		//echo '<pre>' . print_r($need->getName(), true) . '</pre>';

		/*$page = new Page();
		$page->setName($need->getName());
		$page->setTitle($need->getName());*/
		/*$em = $this->getDoctrine()->getManager();

		$block = $em->getRepository('NovuscomCMFBundle:Block')->findOneBy(array('code'=>$code));


		$page->setTitle($block->getName());
		$page->setName($block->getName());

		$sections = $em->getRepository('NovuscomCMFBundle:Section')->findBy(array('block'=>$block));*/
		/*
		foreach ($sections as $section) {
			echo '<pre>' . print_r($section->getName(), true) . '</pre>';
		}
		*/

		$subSections = $er->createQueryBuilder('s')
			->where("s.block=:block")
			->andWhere("s.parent=:parent")
			->setParameters(array(
				'block' => $block,
				'parent' => $need,
			))
			->orderBy('s.name', 'ASC')
			->getQuery()
			->getResult();


		//$crumbs->add($need->getName());
		//echo '<pre>' . print_r('blockSectionAction', true) . '</pre>';

		$this->setSectionsElements(array($need));

		/*foreach($need->getElements() as $e) {
			echo '<pre>' . print_r($e->getName(), true) . '</pre>';
		}*/

		return $this->render('CMFTemplateBundle:BlockSections:section.html.twig', array(
			/*'block' => $block,

			'sections'=>$sections,*/
			'section' => $need,
			'page' => $page,
			'block' => $block,
			'sections' => $subSections
		));
	}

	private function setSectionsElements($sections)
	{
		$em = $this->getDoctrine()->getManager();
		$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBySection($sections);
		$elementsId = array();
		$resultArray = array();
		foreach ($ElementSection as $es) {
			$eId = $es->getElement()->getId();
			$sId = $es->getSection()->getId();
			$elementsId[] = $eId;
			$resultArray[$sId]['ELEMENTS_ID'][] = $eId;
		}
		$elements = array();
		if ($elementsId) {
			$elements = $em->getRepository('NovuscomCMFBundle:Element')->findBy(array('id' => $elementsId));
		}
		$elementsById = array();
		$preview_id = array();
		foreach ($elements as $e) {
			//echo '<pre>' . print_r($e->getName(), true) . '</pre>';
			if ($e->getPreviewPicture()) {
				$preview_id[] = $e->getPreviewPicture()->getId();
			}
			$elementsById[$e->getId()] = $e;
		}

		if ($preview_id) {
			$em->getRepository('NovuscomCMFBundle:File')->findBy(array('id' => $preview_id));
		}


		//echo '<pre>' . print_r($resultArray, true) . '</pre>';

		//echo '<pre>' . print_r($elementsById, true) . '</pre>';

		foreach ($sections as $s) {
			if (array_key_exists($s->getId(), $resultArray)) {
				$info = $resultArray[$s->getId()];
				//echo '<pre>' . print_r($info, true) . '</pre>';
				foreach ($info['ELEMENTS_ID'] as $eId) {
					$element = $elementsById[$eId];
					$s->addSectionElement($element);
					//echo '<pre>' . print_r($element, true) . '</pre>';
				}
			}

		}
		return $sections;
	}

	public function sections_listAction($block_id = false, $section_id = null, $template_code = "default", $get_elements = null, $params)
	{
		//$cache = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/sys/components/sections_list/');
		$cache = new \Doctrine\Common\Cache\ApcCache();
		$cacheId = md5(json_encode(array('block_id' => $block_id, 'section_id' => $section_id, 'template_code' => $template_code, 'get_elements' => $get_elements)));
		//echo '<pre>' . print_r($cache->fetch($cacheId), true) . '</pre>';
		if ($fooString = $cache->fetch($cacheId)) {
			//echo '<pre>' . print_r('возвращаем закешированные данные', true) . '</pre>';
			$render = unserialize($fooString);
		} else {
			//echo '<pre>' . print_r('возвращаем не закешированные данные', true) . '</pre>';
			$em = $this->getDoctrine()->getManager();
			//echo '<pre>' . print_r($block_id, true) . '</pre>';
			if (is_numeric($block_id)) {
				//$sections = $em->getRepository('NovuscomCMFBundle:Section')->findBy(array('block' => $block_id, 'lvl' => 0));
				//echo '<pre>' . print_r($block_id, true) . '</pre>';
				$repository = $em->getRepository('NovuscomCMFBundle:Section');
				$query = $repository->createQueryBuilder('p')
					->where('p.block = :block_id')
					->setParameters(array('block_id' => $block_id))
					->orderBy('p.sort', 'ASC')
					->getQuery();
				$sections = $query->getResult();
				/*foreach($sections as $s) {
					echo '<pre>' . print_r($s->getName(), true) . '</pre>';
				}*/
			} else {
				//echo '<pre>' . print_r('notfound', true) . '</pre>';
				throw $this->createNotFoundException('');
			}
			if (is_int($section_id)) {
				$repository = $em->getRepository('NovuscomCMFBundle:Section');
				$query = $repository->createQueryBuilder('p')
					->where('p.id = :section_id')
					->setParameter('section_id', $section_id)
					->orderBy('p.sort', 'ASC')
					->getQuery();
				$sections = $query->getResult();
			}

			$this->setSectionsElements($sections);


			$response = new Response();
			$data = array();
			$data['sections'] = $sections;
			//echo '<pre>' . print_r($template_code, true) . '</pre>';
			$render = $this->render('@templates/' . $params['params']['template_directory'] . '/SectionsList/' . $template_code . '.html.twig', $data, $response);
		}

		return $render;

	}

	public function blockSectionsAction($BLOCK_CODE, $pageNumber, $pageId, $siteId)
	{

		//echo '<pre>' . print_r($BLOCK_CODE, true) . '</pre>';


		$em = $this->getDoctrine()->getManager();

		$page = $em->getRepository('NovuscomCMFBundle:Page')->find($pageId);
		$rootPage = $em->getRepository('NovuscomCMFBundle:Page')->findOneBy(array(
			'site' => $siteId,
			'lvl' => 0
		));

		$block = $em->getRepository('NovuscomCMFBundle:Block')->findOneBy(array('code' => $BLOCK_CODE));
		if (!$block) {
			//throw $this->createNotFoundException('Инфоблок с кодом ['.$BLOCK_CODE.'] не найден.');
			return $response = $this->forward('NovuscomCMFBundle:Element:elementPage', array(
				'ELEMENT_CODE' => $BLOCK_CODE,
				//'BLOCK_ID'=>$block->getId(),
				'pageId' => $page->getId(),
				'block' => $block,
				'section' => null,
				'siteId' => $siteId,

			));
		}

		/*$page = new Page();
		$page->setTitle($block->getName());
		$page->setName($block->getName());*/
		$sections = $em->getRepository('NovuscomCMFBundle:Section')->findBy(array('block' => $block, 'lvl' => 0));

		$elementRepo = $em->getRepository('NovuscomCMFBundle:Element');


		/*
		 * Получаем ID элементов находящихся в данном инфоблоке
		 */
		$query = $em->createQuery("SELECT o.id FROM NovuscomCMFBundle:Element o WHERE o.block = :block_id");
		$query->setParameters(array(
			'block_id' => $block->getId(),
		));
		$objects = $query->getScalarResult();
		$objectsId = array_map('current', $objects);

		/*
		 * Получаем ИД элементов без разделов
		 */
		$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array(
			'section' => null,
			'element' => $objectsId
		));
		$root_elements_id = array();
		foreach ($ElementSection as $es) {
			//echo '<pre>' . print_r($es->getid(), true) . '</pre>';
			$root_elements_id[] = $es->getElement()->getId();
		}


		//echo '<pre>' . print_r($root_elements_id, true) . '</pre>';


		$elementsQueryBuilder = $elementRepo->createQueryBuilder('e')
			->where("e.id IN(:root_id)")
			//->where("e.block=:block")
			//->andWhere("e.section IS NULL")
			->setParameters(array(
				//'block' => $block,
				//'section'=>false,
				'root_id' => $root_elements_id
			))
			->orderBy('e.name', 'ASC');
		$elementsQuery = $elementsQueryBuilder->getQuery();
		$elements = $elementsQuery->getResult();


		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
			$elementsQuery,
			$pageNumber, // $this->get('request')->query->get('page', 1)/*page number*/,
			1/*limit per page*/
		);
		$pagination->setUsedRoute('voenkom_block_page');

		/*foreach ($elements as $e) {
			echo '<pre>' . print_r($e->getName(), true) . '</pre>';
		}*/

		//$elements = $elementRepo->findBy(array('block'=>$block, 'section'=>null));

		//$elements->getQuery();

		/*
		foreach ($sections as $section) {
			echo '<pre>' . print_r($section->getName(), true) . '</pre>';
		}
		*/

		$crumbs = $this->get("apy_breadcrumb_trail");
		$crumbs->add($rootPage->getName(), 'cmf_page_main');
		$crumbs->add($page->getName(), 'cmf_page_frontend', array("name" => $page->getUrl()));
		$crumbs->add($block->getName());

		//$page->setName($block->getName());

		//$crumbs->add($page->getName(), 'cmf_page_frontend', array("name" => $page->getUrl()));

		return $this->render('CMFTemplateBundle:BlockSections:list.html.twig', array(
			'block' => $block,
			'page' => $page->setName($block->getName()),
			'sections' => $sections,
			'elements' => $elements,
			'pagination' => $pagination
		));
	}

	/**
	 * Lists all Section entities.
	 *
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$entities = $em->getRepository('NovuscomCMFBundle:Section')->findAll();

		return $this->render('NovuscomCMFBundle:Section:index.html.twig', array(
			'entities' => $entities,
		));
	}

	/**
	 * Creates a new Section entity.
	 *
	 */
	public function createAction(Request $request)
	{
		$entity = new Section();
		$form = $this->createCreateForm($entity);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$request = $this->container->get('request');
			$params = $request->get('_route_params');
			$block = $em->getRepository('NovuscomCMFBundle:Block')->find($params['id']);
			$redirectUrl = $this->generateUrl('admin_block_show', array('id' => $params['id']));
			if (array_key_exists('section_id', $params)) {
				$parentSection = $em->getRepository('NovuscomCMFBundle:Section')->find($params['section_id']);
				$entity->setParent($parentSection);
				$redirectUrl = $this->generateUrl('admin_block_show_section', array('id' => $params['id'], 'section_id' => $params['section_id']));
			}
			$entity->setBlock($block);
			$em->persist($entity);
			$em->flush();


			return $this->redirect($redirectUrl);
		}

		return $this->render('NovuscomCMFBundle:Section:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Creates a form to create a Section entity.
	 *
	 * @param Section $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createCreateForm(Section $entity)
	{
		$request = $this->container->get('request_stack')->getCurrentRequest();
		$params = $request->get('_route_params');

		$action = $this->generateUrl('admin_section__create', array('id' => $params['id']));

		if (array_key_exists('section_id', $params)) {
			$action = $this->generateUrl('admin_section__create_in_section', array(
				'id' => $params['id'],
				'section_id' => $params['section_id']
			));
		}

		//echo '<pre>' . print_r($params, true) . '</pre>'; exit;
		$form = $this->createForm(SectionType::class, $entity, array(
			'action' => $action,
			'method' => 'POST',
		));

		return $form;
	}

	/**
	 * Displays a form to create a new Section entity.
	 *
	 */
	public function newAction($id, $section_id = false)
	{
		$em = $this->getDoctrine()->getManager();
		$block = $em->getRepository('NovuscomCMFBundle:Block')->find($id);
		if (!$block) {
			throw $this->createNotFoundException('Unable to find Block entity.');
		}
		if ($section_id) {
			$entity = $em->getRepository('NovuscomCMFBundle:Section')->find($section_id);
			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Section entity.');
			}
			$this->setCrumbs($block, $entity, 'Создание раздела');
		}
		$this->setCrumbs($block, false, 'Создание раздела');

		$entity = new Section();
		$form = $this->createCreateForm($entity);

		return $this->render('NovuscomCMFBundle:Section:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Finds and displays a Section entity.
	 *
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Section')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Section entity.');
		}

		$deleteForm = $this->createDeleteForm($id);

		return $this->render('NovuscomCMFBundle:Section:show.html.twig', array(
			'entity' => $entity,
			'delete_form' => $deleteForm->createView(),));
	}

	private function setCrumbs($block, $entity = false, $last = '')
	{
		$Section = $this->get('Section');
		$crumbs = $this->get("apy_breadcrumb_trail");
		$crumbs->add('CMS', 'cmf_admin_homepage');
		$crumbs->add($block->getName(), 'admin_block_show', array('id' => $block->getId()));
		if ($entity) {
			$path = $Section->getPath($entity);
			foreach ($path as $key => $p) {
				$crumbs->add($p->getName(), 'admin_block_show_section', array(
					'id' => $block->getId(),
					'section_id' => $p->getId()
				));
			}
		}
		if ($last)
			$crumbs->add($last);
	}

	/**
	 * Displays a form to edit an existing Section entity.
	 *
	 */
	public function editAction($id, $section_id)
	{
		$em = $this->getDoctrine()->getManager();
		$block = $em->getRepository('NovuscomCMFBundle:Block')->find($id);
		if (!$block) {
			throw $this->createNotFoundException('Unable to find Block entity.');
		}
		$entity = $em->getRepository('NovuscomCMFBundle:Section')->find($section_id);
		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Section entity.');
		}

		$this->setCrumbs($block, $entity, 'Изменение раздела');

		$editForm = $this->createEditForm($entity, $block);
		$deleteForm = $this->createDeleteForm($section_id, $id);

		return $this->render('NovuscomCMFBundle:Section:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
			'block' => $block
		));
	}


	private function createEditForm(Section $entity, $block)
	{
		if (!$block) {
			throw $this->createNotFoundException('Не передана информация инфоблоке');
		}
		//echo '<pre>' . print_r($block->getName(), true) . '</pre>';
		$form = $this->createForm(new SectionType(), $entity, array(
			'action' => $this->generateUrl('admin_section__update', array(
					'section_id' => $entity->getId(),
					'id' => $block->getId())
			),
			'method' => 'PUT',
		));

		return $form;
	}


	/**
	 * Edits an existing Section entity.
	 *
	 */
	public function updateAction(Request $request, $id, $section_id)
	{
		$em = $this->getDoctrine()->getManager();

		$block = $em->getRepository('NovuscomCMFBundle:Block')->find($id);
		if (!$block) {
			throw $this->createNotFoundException('Unable to find Block entity.');
		}
		$entity = $em->getRepository('NovuscomCMFBundle:Section')->find($section_id);


		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Section entity.');
		}

		$deleteForm = $this->createDeleteForm($section_id, $id);
		//echo '<pre>' . print_r('updateAction', true) . '</pre>';
		$editForm = $this->createEditForm($entity, $block);
		$editForm->handleRequest($request);

		if ($editForm->isValid()) {

			/*
			 * Превью пикча
			 */
			$file = $editForm['preview_picture']->getData();
			if ($file) {
				//echo '<pre>' . print_r($file, true) . '</pre>';
				$this->deletePreviewPicture($entity);
				$this->createPreviewPicture($entity, $file);
			} else {
				//echo '<pre>' . print_r('Нет превью пикчи', true) . '</pre>';
			}
			//$this->downloadFile($editForm['preview_picture_src']->getData(), $entity, 'preview');

			$em->flush();
			$Section = $this->get('Section');
			$Section->clearCacheSection($block->getId(), $section_id);
			return $this->redirect($this->generateUrl('admin_section__edit', array('id' => $id, 'section_id' => $section_id)));
		}

		return $this->render('NovuscomCMFBundle:Section:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	private function deletePreviewPicture(\Novuscom\Bundle\CMFBundle\Entity\Section $section)
	{
		$em = $this->getDoctrine()->getManager();
		$previewPicture = $section->getPreviewPicture();
		if ($previewPicture) {
			$section->setPreviewPicture(null);
			$em->persist($section);
			$em->remove($previewPicture);
			$fileName = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/' . $previewPicture->getName();
			$em->flush();
			unlink($fileName);
		}
	}


	private function createPreviewPicture(\Novuscom\Bundle\CMFBundle\Entity\Section $entity, $file, $description = '')
	{
		if ($file) {
			$em = $this->getDoctrine()->getManager();
			$extension = $file->guessExtension();
			$dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/images/';
			if (!$extension) {
				$extension = 'bin';
			}
			$newName = md5(time()) . '.' . $extension;
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
	 * Deletes a Section entity.
	 *
	 */
	public function deleteAction(Request $request, $block_id, $section_id)
	{

		$form = $this->createDeleteForm($section_id, $block_id);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$entity = $em->getRepository('NovuscomCMFBundle:Section')->find($section_id);


			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Section entity.');
			}

			$em->remove($entity);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('admin_block_show', array('id' => $block_id)));
	}

	private function createDeleteForm($id, $block_id)
	{
		/*$form = $this->get('form.factory')
			->createNamedBuilder('delete_section', 'form', null, array(
				'constraints' => false,
			))
			->add('submit', 'submit', array('label' => 'Удалить', 'attr' => array(
				'class' => 'btn btn-danger',
			)))
			->getForm();*/
		//return $form;
		return $this->createFormBuilder()
			->setAction($this->generateUrl('admin_section__delete', array('section_id' => $id, 'block_id' => $block_id)))
			->setMethod('DELETE')
			->add('submit', 'submit', array('label' => 'Удалить', 'attr' => array(
				'class' => 'btn btn-danger',
				'data-delete' => ''
			)))
			->getForm();

	}
}
