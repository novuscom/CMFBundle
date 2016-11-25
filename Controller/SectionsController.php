<?php

namespace Novuscom\CMFBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Util\TokenGenerator;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use LSS\Array2XML;
use Novuscom\CMFBundle\Entity\Block;
use Novuscom\CMFBundle\Entity\Order;
use Novuscom\CMFBundle\Entity\Product;
use Novuscom\CMFBundle\Entity\SearchQuery;
use Novuscom\CMFBundle\Entity\Site;
use Novuscom\CMFBundle\Entity\SiteBlock;
use Novuscom\CMFBundle\Event\UserEvent as CMFUserEvent;
use Novuscom\CMFBundle\Event\UserSubscriber;
use Novuscom\CMFBundle\Form\BlockType;
use Novuscom\CMFBundle\Form\LoginType;
use Novuscom\CMFBundle\Form\OrderType;
use Novuscom\CMFBundle\Form\RegisterType;
use Novuscom\CMFBundle\Services\Section as Section;
use Novuscom\CMFBundle\Services\Utils;
use Novuscom\CMFBundle\UserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Constraints\DateTime;


class SectionsController extends Controller
{

	private $fullCode;

	private function getFullCode()
	{
		return $this->fullCode;
	}

	private function setFullCode($SECTION_CODE)
	{
		$fullCode = trim($SECTION_CODE, '/');
		$this->fullCode = $fullCode;
	}

	private $parentFullCode = false;

	private function setParentFullCode()
	{
		$explode = explode('/', $this->getFullCode());
		array_pop($explode);
		if ($explode) {
			$parentFullCode = trim(implode($explode, '/'), '/');
			$this->parentFullCode = $parentFullCode;
		}

	}

	private function getParentFullCode()
	{
		return $this->parentFullCode;
	}

	private function setSections($blockId)
	{
		$SectionClass = $this->get('SectionClass');
		$filter = array(
			'block_id' => $blockId,
		);
		if ($this->getCurrentSection()) {
			$filter['section_id'] = $this->getCurrentSection()->getId();
		}
		$sections = $SectionClass->SectionsList($filter, $this->getParentFullCode());
		$this->sections = $sections;
		return $sections;
	}

	private $sections;

	private function getSections()
	{
		return $this->sections;
	}

	private function getPageById($id)
	{
		$page_repository = $this->getDoctrine()->getManager()->getRepository('NovuscomCMFBundle:Page');
		$pageEntity = $page_repository->find($id);
		return $pageEntity;
	}

	private function getPropertyCodes()
	{
		$block = $this->getDoctrine()->getManager()->getReference('Novuscom\CMFBundle\Entity\Block', $this->getParams('BLOCK_ID'));
		$properties = $block->getProperty();
		$propCodes = array();
		foreach ($properties as $p) {
			$propCodes[] = $p->getCode();
		}
		return $propCodes;
	}


	private function setElements($blockId)
	{

		$ElementsList = $this->get('ElementsList');
		$ElementsList->setBlockId($blockId);
		if ($this->getSections()) {
			foreach (array_keys($this->getSections()) as $sectionId) {
				$ElementsList->setSectionId($sectionId);
			}
		}
		if ($this->getCurrentSection()) {
			$ElementsList->setSectionId($this->getCurrentSection()->getId());
		}

		$propCodes = $this->getPropertyCodes();
		$ElementsList->selectProperties($propCodes);
		$ElementsList->setSelect(array('code', 'last_modified', 'preview_picture', 'preview_text'));
		$ElementsList->setOrder(array('sort' => 'asc', 'name' => 'asc', 'id' => 'desc'));
		$params = $this->getParams();
		if ($params && array_key_exists('params', $params) && array_key_exists('INCLUDE_SUB_SECTIONS', $params['params']))
			$ElementsList->setIncludeSubSections($params['params']['INCLUDE_SUB_SECTIONS']);
		$elements = $ElementsList->getResult();
		return $elements;
	}

	private $params;

	private function setParams($params)
	{
		$this->params = $params;
	}

	private function getParams($code = false)
	{
		if (!$code)
			return $this->params;
		else
			return $this->params[$code];
	}

	private $currentSecton;

	private function getCurrentSection()
	{
		return $this->currentSecton;
	}

	private function setCurrentSection()
	{
		$routeOptions = array();
		if (array_key_exists('params', $this->getParams()) === true)
			$routeOptions = $this->getParams('params');
		$SectionClass = $this->get('SectionClass');
		$section = $SectionClass->GetSectionByPath($this->getFullCode(), $this->getParams('BLOCK_ID'), $routeOptions);
		$this->currentSecton = $section;
		return $section;
	}

	private function setSectionsElements(&$sections, $elements)
	{
		foreach ($elements as $e) {
			$sections[$e['parent_section']]['elements'][$e['id']] = $e;
		}
		return $sections;
	}

	public function ItemAction($params, $SECTION_CODE = false, Request $request, $PAGE = 1)
	{
		$logger = $this->get('logger');
		$logger->debug('ItemAction');
		$this->setParams($params);
		$this->setFullCode($SECTION_CODE);
		$this->setParentFullCode();
		$this->setCurrentSection();


		$templateService = $this->get('novuscom.cmf.templating');
		$page = $this->getPageById($params['page_id']);
		$response_data = array(
			'title' => $page->getTitle(),
			'header' => $page->getHeader(),
			'page' => $page,
		);

		if ($this->getCurrentSection()) {
			$response_data['header'] = $this->getCurrentSection()->getName();
		}

		$sections = $this->setSections($params['BLOCK_ID']);
		//$sections = array();
		$elements = $this->setElements($params['BLOCK_ID']);
		//$elements = array();
		$sectionsElements = $this->setSectionsElements($sections, $elements);
		$response_data['sectionsElements'] = $sectionsElements;
		$response_data['elements'] = $elements;
		$response_data['section'] = $this->getCurrentSection();

		$path = $templateService->getPath('Section', $params['template_code']);
		$response = $this->render($path, $response_data);

		return $response;
	}

	private $paginationRedirect = false;

	private function getPagination($elements, $PAGE, $routeParams, $site, $sectionFullCode = false)
	{
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
			$elements,
			$PAGE,
			16
		);
		$pagination_route = preg_replace('/^(.+?)(_pagination)*$/', '\\1_pagination', $routeParams['template_code']);
		$pagination->setUsedRoute($pagination_route);
		if (!empty($sectionFullCode)) {
			//echo '<pre>' . print_r($sectionFullCode, true) . '</pre>';
			$pagination->setParam('SECTION_CODE', $sectionFullCode);
		}
		$pagination->setParam('params', null); // очищаем params - непонятно откуда берется на первой странице для других страниц
		$pagination->setTemplate('@templates/' . $site['code'] . '/Pagination/' . $routeParams['template_code'] . '.html.twig');
		if ($PAGE > 1 && count($pagination) < 1) {
			throw $this->createNotFoundException('Не найдено элементов на странице ' . $PAGE);
		}
		if (preg_match('/^(.+?)(_pagination)+$/', $routeParams['template_code'], $matches) && $PAGE == 1) {
			//$this->msg($matches);
			if ($sectionFullCode)
				$url = $this->get('router')->generate($matches[1], array('SECTION_CODE' => $sectionFullCode));
			else
				$url = $this->get('router')->generate($matches[1]);
			//$this->msg($url);
			$this->paginationRedirect = $url;
		}
		return $pagination;
	}

}