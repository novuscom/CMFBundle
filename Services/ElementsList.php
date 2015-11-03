<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\CMFBundle\Entity\ElementSection;
use Doctrine\DBAL\Types\BooleanType;
use Novuscom\CMFBundle\Services\Section as SectionService;
use Monolog\Logger;

class ElementsList
{

	private $limit = false;

	public function getLimit()
	{
		return $this->limit;
	}

	public function setLimit($limit)
	{
		$this->limit = $limit;
	}

	private $filter = array();

	public function setFilter($filter)
	{
		$this->filter = $filter;
	}

	public function getFilter()
	{
		return $this->filter;
	}

	private $sections = array();

	public function setSections($sections){
		$this->sections = $sections;
	}
	public function setSectionsId($id)
	{
		if (is_array($id)) {
			foreach ($id as $i) {
				$this->setSectionId($id);
			}
		}
		if (is_numeric($id)) {
			$this->setSectionId($id);
		}
	}
	private $includeSubSections;

	public function setIncludeSubSections($includeSubSections)
	{
		$this->includeSubSections = $includeSubSections;
	}

	public function getIncludeSubSections()
	{
		return $this->includeSubSections;
	}

	public function setSectionId($id)
	{
		$this->sections[] = $id;
	}

	public function getSectionsId()
	{
		return $this->sections;
	}

	private $block_id = false;

	public function setBlockId($block_id)
	{
		$this->block_id = $block_id;
	}

	public function getBlockId()
	{
		return $this->block_id;
	}

	private $order = array();

	public function setOrder(array $order)
	{
		$this->order = $order;
	}

	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @var array
	 */
	private $select = array();

	public function setSelect($code_array)
	{
		$this->select = $code_array;
	}

	public function getSelect()
	{
		return $this->select;
	}

	private $selectProperties = array();

	public function selectProperties($code_array)
	{
		$this->selectProperties = $code_array;
	}

	public function getSelectProperties()
	{
		return $this->selectProperties;
	}

	public function getResult()
	{

		$get_preview_picture = in_array('preview_picture', $this->getSelect());

		$em = $this->em;
		$logger = $this->logger;
		$section_elements_id = false;
		if ($this->getSectionsId()) {
			$sectionRepo = $em->getRepository('NovuscomCMFBundle:Section');
			$Sections = $sectionRepo->findBy(array('id' => $this->getSectionsId()));
			$sections_id = array();
			foreach ($Sections as $s) {
				$sections_id[] = $s->getId();
			}

			if ($this->getIncludeSubSections()) {
				/*
				 * Выбираем элементы включая подразделы
				 */
				$logger->info('Выбираем элементы включая подразделы');
				$subSectionsId = array();
				foreach ($Sections as $s) {
					$path = $this->Section->getChildren($s);
					foreach ($path as $p) {
						$subSectionsId[] = $p->getId();
					}
				}
				$logger->info('Подразделы в которых надо выбрать элементы ' . print_r($subSectionsId, true));
				$sections_id = array_merge($sections_id, $subSectionsId);
			}

			$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array('section' => $sections_id));
			$section_elements_id = array();
			foreach ($ElementSection as $es) {
				$section_elements_id[$es->getElement()->getId()] = $es->getSection()->getId();
			}
			if (empty($section_elements_id)) {
				$logger->info('Нет элементов в разделах ' . implode(', ', $this->getSectionsId()) . '. Возвращается пустой массив.');
				return array();
			}
		}
		$elements_repo = $em->createQueryBuilder('n');
		$elements_repo->from('NovuscomCMFBundle:Element', 'n', 'n.id');
		$elements_repo->select('n.name');
		if ($get_preview_picture) {
			$elements_repo->addSelect('IDENTITY(n.PreviewPicture) as preview_picture');
		}
		$elements_repo->addSelect('n.id');
		foreach ($this->getSelect() as $s) {
			if (in_array($s, $this->getFieldNames())) {
				$elements_repo->addSelect('n.' . $s);
			}
		}
		if ($this->getBlockId()) {
			$elements_repo->where('n.block=:block_id');
			$elements_repo->setParameter('block_id', $this->getBlockId());
		}
		if ($this->getSectionsId()) {
			$elements_repo->andWhere('n.id IN(:sections_id)');
			$elements_repo->setParameter('sections_id', array_keys($section_elements_id));
		}
		foreach ($this->getFilter() as $key => $val) {
			if (in_array($key, $this->getFieldNames())) {
				$elements_repo->andWhere('n.' . $key . '=:' . $key);
				$elements_repo->setParameter($key, $val);
			}
		}
		$order = $this->getOrder();
		if ($order) {
			$elements_repo->orderBy('n.sort', 'asc');
			$elements_repo->addOrderBy('n.' . $order[0], $order[1]);
		}
		if ($this->getLimit() > 0) {

			$elements_repo->setMaxResults($this->getLimit());
		}
		$query = $elements_repo->getQuery();
		$sql = $query->getSql();
		$this->sql = $sql;
		$elements = $query->getResult();
		
		/**
		 * Получение preview picture
		 */
		if ($get_preview_picture) {
			$files_id = array();
			foreach ($elements as $e) {
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
			foreach ($elements as $key => $e) {
				if ($e['preview_picture'] && array_key_exists($e['preview_picture'], $preview_pictures)) {
					$array = $preview_pictures[$e['preview_picture']];
					$array['src'] = 'upload/images/' . $array['name'];
					$array['path'] = $array['src'];
					$elements[$key]['preview_picture'] = $array;
				}
			}
		}

		if ($this->selectProperties) {
			$properties = $this->Element->getProperties($this->getSelectProperties(), $this->getBlockId());
			//echo '<pre>' . print_r($properties, true) . '</pre>';
			$values = $this->Element->getPropertiesValues(array_keys($elements), array_keys($properties));
			//echo '<pre>' . print_r($values, true) . '</pre>';
			$values_by_element = array();
			foreach ($values as $v) {
				$values_by_element[$v['element_id']][$v['property_id']] = $v;
			}

			$properties_by_code = array();
			$prop_codes = array();
			foreach ($properties as $p) {
				$prop_codes[$p['code']] = $p['id'];
				$p['value'] = false;
				$properties_by_code[$p['code']] = $p;
			}

			foreach ($elements as $element_id => $e) {
				$e_props = array();
				foreach ($properties_by_code as $p) {
					if (isset($values_by_element[$element_id][$p['id']])) {
						$p['value'] = $values_by_element[$element_id][$p['id']]['value'];
					}
					$e_props[$p['code']] = $p;
				}
				$elements[$element_id]['properties'] = $e_props;
			}
		}

		if ($section_elements_id) {
			$sectionsFullCode = $this->Section->getFullCode($section_elements_id);
			//echo '<pre>' . print_r($sectionsFullCode, true) . '</pre>';
			foreach ($elements as &$elementInfo) {
				$elementInfo['parent_section'] = $section_elements_id[$elementInfo['id']];
				$elementInfo['parent_section_full_code'] = $sectionsFullCode[$elementInfo['parent_section']];
			}
		}
		return $elements;
	}

	private $sql;

	public function getSql()
	{
		return $this->sql;
	}

	private $fieldNames;

	private function getFieldNames()
	{
		return $this->fieldNames;
	}

	private $em;
	private $Element;
	private $logger;
	private $Section;

	public function __construct(\Doctrine\ORM\EntityManager $em, $Element, Logger $logger, SectionService $Section)
	{
		$this->em = $em;
		$this->Element = $Element;
		$this->logger = $logger;
		$this->fieldNames = $em->getClassMetadata('NovuscomCMFBundle:Element')->getFieldNames();
		$this->Section = $Section;
		//echo '<pre>' . print_r($em->getClassMetadata('NovuscomCMFBundle:Element')->getAssociationNames(), true) . '</pre>';
	}
}