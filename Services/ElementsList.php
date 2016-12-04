<?php

namespace Novuscom\CMFBundle\Services;

use Monolog\Handler\Curl\Util;
use Monolog\Logger;
use Novuscom\CMFBundle\Services\Section as SectionService;
use Novuscom\CMFBundle\Services\SectionClass as SectionClass;

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

	public function setSections($sections)
	{
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
		if ($id)
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

	private $block;

	private function setBlock()
	{
		$repo = $this->em->getRepository('NovuscomCMFBundle:Block');
		$block = $repo->findBy(array('id' => $this->getBlockId()));
		$this->block = $block;
		return $block;
	}

	public function getBlock()
	{
		if (empty($this->block))
			$this->setBlock();
		return $this->block;
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

	private function setSelectProperties()
	{
		$properties = $this->getBlock()->getProperty();
		$propCodes = array();
		foreach ($properties as $p) {
			$propCodes[] = $p->getCode();
		}
		$this->selectProperties($propCodes);
	}

	public function getSelectProperties()
	{
		if (empty($this->selectProperties))
			$this->setSelectProperties();
		return $this->selectProperties;
	}

	private $notId;

	public function setNotId($notId)
	{
		$this->notId = $notId;
	}

	public function getNotId()
	{
		return $this->notId;
	}

	private $random = false;

	public function setRandom($random)
	{
		$this->random = $random;
	}

	public function getRandom()
	{
		return $this->random;
	}


	private $idArray = array();

	private function getIdArray()
	{
		return $this->idArray;
	}

	public function setIdArray($array = array())
	{
		$this->idArray = $array;
	}

	private $filterProperties;

	public function setFilterProperties($properties)
	{
		$this->filterProperties = $properties;
	}

	public function getFilterProperties()
	{
		return $this->filterProperties;
	}

	public function addToIdArray($id)
	{
		if (is_numeric($id)) {
			$this->idArray[] = $id;
		}
		if (is_array($id)) {
			$this->idArray = array_merge($id, $this->idArray);
		}
	}

	public function getResult()
	{
		$root = null;
		$this->logger->info('Получаем список элементов');

		$get_preview_picture = in_array('preview_picture', $this->getSelect());

		$em = $this->em;
		$logger = $this->logger;
		$section_elements_id = false;
		if ($this->getSectionsId()) {
			$root = false;
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
				$sectionId = $es->getSection()->getId();
				$elementId = $es->getElement()->getId();
				$section_elements_id[$elementId] = $sectionId;
			}

			/*$builder = $em
				->createQueryBuilder()
				->select('n.id')
				->addSelect('IDENTITY(n.section) as section_id')
				->addSelect('IDENTITY(n.element) as element_id')
				->from('NovuscomCMFBundle:ElementSection', 'n', 'n.id')
				->where('n.section IN (:sections)')
				->setParameter('sections', $sections_id);
			$builder->orderBy('n.id', 'ASC');
			$query = $builder->getQuery();
			$result = $query->getArrayResult();
			foreach ($result as $r) {
				$section_elements_id[$r['element_id']] = $r['section_id'];
			}*/


			if (empty($section_elements_id)) {
				$logger->info('Нет элементов в разделах ' . implode(', ', $this->getSectionsId()) . '. Возвращается пустой массив.');
				return array();
			}
		} else {
			$root = true;
			/*
			 * Не указаны разделы из которых надо брать элементы
			 */
			$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array(
				'section' => null,
			));
			$elementsId = array();
			if (count($ElementSection)) {
				foreach ($ElementSection as $es) {
					//if ($this->getBlockId() == $blockId)
					$elementsId[] = $es->getElement()->getId();
				}
			}
			//Utils::msg($elementsId);
			//$this->setIdArray($elementsId);
			if ($this->getIncludeSubSections() == false)
				if (empty($elementsId)) {
					$logger->info('Нет элементов без раздела ' . implode(', ', $this->getSectionsId()) . '. Возвращается пустой массив.');
					return array();
				}
		}
		$elements_repo = $em->createQueryBuilder('n');
		$elements_repo->from('NovuscomCMFBundle:Element', 'n', 'n.id');
		$elements_repo->select('n.name');

		/**
		 * Фильтрация по значениям свойств
		 */
		if ($this->getFilterProperties()) {
			// получаем типы свойств, чтобы знать в каких таблицах искать
			$filterProperties = $this->getFilterProperties();
			$properties = $this->getProperties($this->getBlockId());
			//Utils::msg($properties);
			$propTypes = array();
			$idValues = array();
			foreach ($properties as $p) {
				if (isset($filterProperties[$p['code']]))
					$idValues[$p['id']] = $filterProperties[$p['code']];
				$propTypes[] = $p['type'];
			}
			//echo '<pre>' . print_r($propTypes, true) . '</pre>';
			if (in_array('S', $propTypes) || in_array('E', $propTypes) || in_array('BOOLEAN', $propTypes)) {
				//Utils::msg($idValues);
				$propValues = $this->getPropertyValues(array_keys($properties), $idValues);
				//Utils::msg($propValues);
				$this->setIdArray(array_keys($propValues));
			}
			if (!$this->getIdArray()) {
				$logger->info('Не найдены элементы по указанному фильтру свойств <pre>' . print_r($this->getFilterProperties(), true) . '</pre>');
				return array();
			}
		}


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
		if ($this->getNotId()) {
			$elements_repo->andWhere('n.id NOT IN(:not_id)');
			$elements_repo->setParameter('not_id', $this->getNotId());
		}
		if ($this->getIdArray()) {
			//Utils::msg($this->getIdArray()); exit;
			$elements_repo->andWhere('n.id IN(:id_array)');
			$elements_repo->setParameter('id_array', $this->getIdArray());
		}

		$elements_repo->andWhere('n.active=true');


		$order = $this->getOrder();

		if ($order) {
			foreach ($order as $key => $val) {
				$elements_repo->addOrderBy('n.' . $key, $val);
			}
		} else {
			$elements_repo->orderBy('n.id', 'desc');
			$elements_repo->addOrderBy('n.sort', 'asc');
		}
		if ($this->getLimit() > 0) {
			$elements_repo->setMaxResults($this->getLimit());
		}
		if ($this->getRandom() !== false)
			$elements_repo->addSelect('RAND() as HIDDEN rand')->orderBy('rand');


		$query = $elements_repo->getQuery();
		$sql = $query->getSql();
		$this->sql = $sql;
		//echo '<pre>' . print_r($sql, true) . '</pre>';
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
					$array['src'] = 'upload/etc/' . $array['name'];
					$array['path'] = $array['src'];
					$elements[$key]['preview_picture'] = $array;
				}
			}
		}

		if ($this->getSelectProperties()) {
			//$properties = $this->Element->getProperties($this->getBlockId());
			$properties = $this->Element->getProperties(false, $this->getBlockId());
			//$properties = array();
			//echo '<pre>' . print_r($properties, true) . '</pre>';
			$values = $this->Element->getPropertiesValues(array_keys($elements), array_keys($properties));
			//$values = array();
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
			$this->sectionClass->SectionsList(array(
				'block_id' => $this->getBlockId()
			));
			$flatSections = $this->sectionClass->getSectionsFlat();
			foreach ($elements as &$elementInfo) {
				$elementInfo['parent_section'] = $section_elements_id[$elementInfo['id']];
				$elementInfo['parent_section_full_code'] = $flatSections[$elementInfo['parent_section']]['fullCode'];
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
	private $sectionClass;

	public function __construct(\Doctrine\ORM\EntityManager $em, $Element, Logger $logger, SectionService $Section, SectionClass $sectionClass)
	{
		$this->em = $em;
		$this->Element = $Element;
		$this->logger = $logger;
		$this->fieldNames = $em->getClassMetadata('NovuscomCMFBundle:Element')->getFieldNames();
		$this->Section = $Section;
		$this->sectionClass = $sectionClass;
		//echo '<pre>' . print_r($em->getClassMetadata('NovuscomCMFBundle:Element')->getAssociationNames(), true) . '</pre>';
	}

	public function getProperties($block_id)
	{
		$em = $this->em;
		$builder = $em
			->createQueryBuilder()
			->select('n.id, n.name, n.code, n.type, n.info, n.isForSection')
			->addSelect('IDENTITY(n.block) as block_id')
			->from('NovuscomCMFBundle:Property', 'n', 'n.id')
			->where('n.block=:block_id')
			->andWhere('n.isForSection=:isForSection')
			->setParameter('block_id', $block_id)
			->setParameter('isForSection', false)
			->orderBy('n.name', 'ASC');
		$query = $builder->getQuery();
		$result = $query->getArrayResult();
		return $result;
	}

	public function getPropertyValues($propertyId, array $values = array())
	{
		$em = $this->em;
		$builder = $em
			->createQueryBuilder()
			->select('n.id, n.value, n.description')
			->addSelect('IDENTITY(n.element) as element_id')
			->addSelect('IDENTITY(n.property) as property_id')
			->from('NovuscomCMFBundle:ElementProperty', 'n', 'n.id')
			->where('n.property IN (:property_id)')
			->setParameter('property_id', $propertyId);
		foreach ($values as $propertyId => $propertyValue) {

			$builder->add('having', $builder->expr()->orX(
				$builder->expr()->eq('n.value', $propertyValue)
			));
			//$builder->setParameter('value', $propertyId);
		}
		$builder->orderBy('n.id', 'ASC');
		$query = $builder->getQuery();
		$SQL = $query->getSQL();
		//Utils::msg($SQL);
		//exit;
		$result = $query->getArrayResult();
		$res = array();
		foreach ($result as $r) {
			$res[$r['element_id']][$r['property_id']] = array(
				'value' => $r['value'],
				'id' => $r['id'],
				'description' => $r['description'],
			);
		}
		return $res;
	}
}