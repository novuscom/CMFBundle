<?php

namespace Novuscom\CMFBundle\Services;

use Monolog\Handler\Curl\Util;
use Novuscom\CMFBundle\Entity\ElementPropertyF;
use Novuscom\CMFBundle\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Router;
use Doctrine\Common\Collections\ArrayCollection;
use Monolog\Logger;
use Novuscom\CMFBundle\Entity\ElementSection;
use Novuscom\CMFBundle\Entity\Section;
use Novuscom\CMFBundle\Entity\ElementProperty;
use Novuscom\CMFBundle\Entity\ElementPropertySection;
use Novuscom\CMFBundle\Entity\ElementPropertyDT;
use Novuscom\CMFBundle\Services\Utils;


class Element
{

	public function SetPropertyValues($element, array $properties = array())
	{
		if (!$properties)
			return null;
		$currentValues = $this->getPropertiesValues($element, array_keys($properties));
		$currentSectionValues = $this->getPropertiesSectionValues($element, array_keys($properties));

		$removed = array();
		$currentById = array();
		foreach ($currentValues as $prop) {
			if (array_key_exists($prop['property_id'], $properties)) {
				$newValue = $properties[$prop['property_id']];
				if (is_array($newValue)) {
					if (in_array($prop['value'], $newValue) == false) {
						$removed[] = $prop;
					}
					$currentById[$prop['property_id']][] = $prop['value'];
				} else {
					$currentById[$prop['property_id']] = $prop['value'];
				}
			}
		}
		$added = array();

		$Section = new Section();

		$currentSectionV = array();
		foreach ($currentSectionValues as $info) {

			$ref = $this->em->getReference('Novuscom\CMFBundle\Entity\ElementPropertySection', $info['id']);
			$prop = $properties[$info['property_id']];
			if ($info['section_id'] != $prop->getId()) {
				//Utils::msg('изменяем запись');
				//Utils::msg($info);
				$ref->setSection($prop);
				$this->em->persist($prop);
				$currentSectionV[] = $prop->getId();
			} else {
				$currentSectionV[] = $info['section_id'];
			}

		}

		//Utils::msg($currentSectionV);
		//exit;

		foreach ($properties as $key => $value) {
			$propertyReference = $this->em->getReference('Novuscom\CMFBundle\Entity\Property', $key);
			if (is_array($value)) {
				if (isset($currentById[$key]))
					$currentVal = $currentById[$key];
				else $currentVal = array();
				$diff = array_diff($value, $currentVal);
				foreach ($diff as $d) {
					$added[] = array(
						'value' => $d,
						'description' => null,
						'element_id' => $element->getId(),
						'property_id' => $key,
					);
				}
			}
			//Utils::msg(gettype($value));
			//TODO Надо пернести ниже?
			if (is_object($value)) {
				if ($value instanceof $Section) {
					Utils::msg($value->getId());
					Utils::msg($currentSectionV);
					//exit;
					if (in_array($value->getId(), $currentSectionV) == false) {
						$ElementPropertySection = new ElementPropertySection();
						$ElementPropertySection->setProperty($propertyReference);
						$ElementPropertySection->setElement($element);
						$ElementPropertySection->setSection($value);
						$this->em->persist($ElementPropertySection);
					}
				} else if ($value instanceof \DateTime) {
					//Utils::msg($value);
					$ElementPropertyDT = new ElementPropertyDT();
					$ElementPropertyDT->setElement($element);
					$ElementPropertyDT->setProperty($propertyReference);
					$ElementPropertyDT->setValue($value);
					$this->em->persist($ElementPropertyDT);
				} else {
					Utils::msg('Class: ' . get_class($value));
				}
			}
			/*if (is_array($value)) {
				foreach ($value as $v) {
					Utils::msg($v);
				}
			}*/


		}
		//Utils::msg('-------added--------');
		//Utils::msg($added);
		//Utils::msg('-------removed--------');
		//Utils::msg($removed);

		//exit;

		foreach ($added as $addArray) {
			$propertyReference = $this->em->getReference('Novuscom\CMFBundle\Entity\Property', $addArray['property_id']);
			$value = $addArray['value'];
			if (isset($value['file']) && $value['file'] instanceof UploadedFile) {

				//$serviceFile =

				$elementFile = new ElementPropertyF();
				//Utils::msg($value);
				//exit;

				$nFile = new File($value['file']);


				$elementFile->setDescription($value['description']);
				$elementFile->setElement($element);
				$elementFile->setProperty($propertyReference);
				$elementFile->setFile($nFile);
				//Utils::msg('аплоадим файл');
				//exit;
				$this->em->persist($nFile);
				$this->em->persist($elementFile);
				$this->file->uploadFile($nFile);
			} else {
				$ep = new ElementProperty();
				$ep->setDescription($addArray['description']);
				$ep->setElement($element);
				$ep->setProperty($propertyReference);
				$ep->setValue($value);
				$this->em->persist($ep);
			}
		}

		foreach ($removed as $removeArray) {
			$ref = $this->em->getReference('Novuscom\CMFBundle\Entity\ElementProperty', $removeArray['id']);
			$this->em->remove($ref);
		}
		//exit;
		$this->em->flush();
		//exit;
	}

	public function GetById($id)
	{
		if (is_numeric($id) == false)
			return false;
		$entity = $this->em->getRepository('NovuscomCMFBundle:Element')->find($id);
		return $entity;
	}

	public function getElementSections($element_id)
	{
		$result = array();
		$element_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Block', $element_id);
		$ElementSection = $this->em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array(
			'element' => $element_reference
		));
		$sections_id = array();
		foreach ($ElementSection as $es) {
			$sections_id[] = $es->getSection()->getId();
		}
		if ($sections_id) {
			$result = $this->em->getRepository('NovuscomCMFBundle:Section')->findBy(array(
				'id' => $sections_id
			));
		}
		return $result;
	}

	public function getBySection($section)
	{
		$result = array();
		$es = $this->em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array(
			'section' => $section
		));
		$elements_id = array();
		foreach ($es as $e) {
			$elements_id[] = $e->getElement()->getId();
		}
		if ($elements_id) {
			$result = $this->em->getRepository('NovuscomCMFBundle:Element')->findBy(array(
				'id' => $elements_id
			));
		}

		return $result;
	}

	protected $em;
	private $ElementsSections = array();

	/**
	 * @param array $filter Массив со значениями фильтрации
	 * @param bool $entities Какой результтат возвращаем - массив или сущность
	 * @param mixed $prop_codes Массив кодов свойств, значения которых необходимо получить
	 * @return mixed
	 */
	public function GetList($filter, $entities = false, $prop_codes = array())
	{
		$em = $this->em;
		$result = array();
		//echo '<pre>' . print_r('Ищем элементы по фильтру:', true) . '</pre>';
		//echo '<pre>' . print_r($filter, true) . '</pre>';
		//exit;

		$block_reference = $em->getReference('Novuscom\CMFBundle\Entity\Block', $filter['BLOCK_ID']);
		$properties_filter = array(
			'block' => $block_reference,
		);


		if ($filter['PROPERTIES']) {
			$properties_filter['code'] = array_keys($filter['PROPERTIES']);
		}
		if ($prop_codes) {
			if (isset($properties_filter['code'])) {
				$properties_filter['code'] = array_merge($properties_filter['code'], $prop_codes);
			} else {
				$properties_filter['code'] = $prop_codes;
			}
		}
		//echo '<pre>' . print_r($properties_filter, true) . '</pre>';

		$properties_by_code = array();
		$properties = $em->getRepository('NovuscomCMFBundle:Property')->findBy($properties_filter);
		$property_types = array();
		foreach ($properties as $p) {
			//echo '<pre>' . print_r($p->getCode(), true) . '</pre>';
			$property_types[] = $p->getType();
			$properties_by_code[$p->getCode()] = $p;
		}
		$property_types = array_unique($property_types);
		//echo '<pre>' . print_r($property_types, true) . '</pre>';

		$values_filter = array();
		foreach ($filter['PROPERTIES'] as $k => $f) {
			$property_id = $properties_by_code[$k]->getId();
			$values_filter[] = array('id' => $property_id, 'value' => $f);
		}

		//echo '<pre>' . print_r($values_filter, true) . '</pre>';
		//echo '<pre>' . print_r(count($values_filter), true) . '</pre>';
		$values_filter_count = count($values_filter);


		$elements = new ArrayCollection();
		$values = array();

		if ($filter['PROPERTIES']) {
			$connection = $em->getConnection();
			$query = 'SELECT *, COUNT(element_id) AS element_counter FROM ElementProperty WHERE (property_id = :prop_id AND value=:value)';

			foreach ($values_filter as $k => $p) {
				if ($k == 0) continue;
				//$qb->orWhere('n.property = ' . $p['id'] . ' AND n.value = ' . $p['value']);
				$query .= ' OR (property_id = ' . $p['id'] . ' AND value=' . $p['value'] . ')';
			}
			//echo '<pre>' . print_r($prop_codes, true) . '</pre>';
			$property_id = array();
			foreach ($prop_codes as $code) {
				$property_id[] = $properties_by_code[$code]->getId();
			}
			//echo '<pre>' . print_r($property_id, true) . '</pre>';
			$query .= ' GROUP BY(element_id) HAVING(element_counter=' . $values_filter_count . ')';
			$statement = $connection->prepare($query);
			$statement->bindValue('prop_id', $values_filter[0]['id']);
			$statement->bindValue('value', $values_filter[0]['value']);

			foreach ($values_filter as $p) {
				//$qb->orWhere('n.property = ' . $p['id'] . ' AND n.value = ' . $p['value']);
			}
			$statement->execute();
			$values = $statement->fetchAll();

			if ($values) {
				$elements_id = array();
				foreach ($values as $v) {
					//echo '<pre>' . print_r($v->getId(), true) . '</pre>';
					//$elements_id[] = $v->getElement()->getId();
					$elements_id[] = $v['element_id'];
				}


				/**
				 * Получение элементов
				 */
				$elements_id = array_unique($elements_id);
				$elements_repo = $em->getRepository('NovuscomCMFBundle:Element');
				$elements_repo = $elements_repo->createQueryBuilder('n');
				$elements_repo = $elements_repo->where('n.block=' . $filter['BLOCK_ID']);
				if ($elements_id) {
					$elements_repo = $elements_repo->andWhere('n.id IN(' . implode(',', $elements_id) . ')');
				}
				if (array_key_exists('ID', $filter) && is_numeric($filter['ID'])) {
					$elements_repo = $elements_repo->andWhere('n.id=' . $filter['ID']);
				}
				$elements_repo = $elements_repo->getQuery();
				$sql = $elements_repo->getSql();
				$elements = $elements_repo->getResult();
				$values_by_ep = $em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(
					array(
						'element' => $elements,
					)
				);
				foreach ($values_by_ep as $vp) {
					$values[] = array(
						'id' => $vp->getId(),
						'property_id' => $vp->getProperty()->getId(),
						'value' => $vp->getValue(),
						'element_id' => $vp->getElement()->getId(),
						'description' => $vp->getDescription(),
					);
				}
				/**
				 * Получаем даты
				 */
				if (in_array('D', $property_types)) {
					$values_dates = $em->getRepository('NovuscomCMFBundle:ElementPropertyDT')->findBy(
						array(
							'element' => $elements,
						)
					);
					foreach ($values_dates as $vp) {
						$values[] = array(
							'id' => $vp->getId(),
							'property_id' => $vp->getProperty()->getId(),
							'value' => $vp->getValue(),
							'element_id' => $vp->getElement()->getId(),
							'description' => $vp->getDescription(),
						);
					}
				}
			}


		}


		if ($entities) {
			$result = $elements;
		} else {
			$result = $this->getElementsListArray($elements, $properties, $values);
		}

		//echo '<pre>' . print_r($result, true) . '</pre>';

		return $result;
	}

	public function getElementArray($element)
	{
		$result = array(
			'id' => $element->getId(),
			'name' => $element->getName(),
			'code' => $element->getCode(),
			'preview_text' => $element->getPreviewText(),
			'detail_text' => $element->getDetailText(),
			'last_modified' => $element->getLastModified(),
			'sort' => $element->getSort(),
		);
		return $result;
	}

	private function getElementsListArray($elements, $properties, $property_values)
	{
		$result = array();

		//echo '<pre>' . print_r(count($properties), true) . '</pre>';
		//exit;
		$property_codes = array();
		$codes = array();
		foreach ($properties as $prop) {
			$property_codes[$prop->getId()] = array(
				'code' => $prop->getCode(),
				'type' => $prop->getType(),
			);
			$codes[$prop->getCode()] = array(
				'id' => $prop->getId(),
				'type' => $prop->getType(),
				'value' => '',
			);
		}
		//echo '<pre>' . print_r($property_codes, true) . '</pre>';
		//echo '<pre>' . print_r(count($elements), true) . '</pre>';
		foreach ($elements as $element) {
			$result[$element->getId()] = array(
				'id' => $element->getId(),
				'name' => $element->getName(),
				'code' => $element->getCode(),
				'preview_text' => $element->getPreviewText(),
				'detail_text' => $element->getDetailText(),
				'last_modified' => $element->getLastModified(),
				'sort' => $element->getSort(),
				'properties' => $codes,
			);
		}

		//echo '<pre>' . print_r(count($property_values), true) . '</pre>';

		//echo '<pre>' . print_r($property_values, true) . '</pre>';
		//exit;
		foreach ($property_values as $val) {
			//$code = $property_codes[$val->getProperty()->getId()]['code'];
			if (array_key_exists($val['property_id'], $property_codes)) {
				$code = $property_codes[$val['property_id']]['code'];
				//if (array_key_exists($val->getElement()->getId(), $result)) {
				if (array_key_exists($val['element_id'], $result)) {
					$result[$val['element_id']]['properties'][$code]['value'] = $val['value'];
				}
			}
		}
		//echo '<pre>' . print_r($result, true) . '</pre>';
		//exit;
		return $result;
	}

	public function updateSections($element, $newSections, $oldSections = false)
	{
		if (is_object($element)) {

			if (!$oldSections) {
				$oldSections = $this->getSections($element);
			}

			/*
			 * Готовим массив разделов, в которые надо добавить элемент
			 */
			$addSections = new ArrayCollection();
			foreach ($newSections as $n) {
				if (!$oldSections->contains($n)) {
					$addSections->add($n);
				}
			}
			$countAddSections = count($addSections);

			/*
			 * Добавляем элемент в разделы
			 */
			if ($countAddSections > 0) {
				foreach ($addSections as $obj) {
					$ElementSection = new ElementSection();
					$ElementSection->setElement($element);
					$ElementSection->setSection($obj);
					$this->em->persist($ElementSection);
				}
			}

			/*
			 * Готовим массив ИД разделов из которых элемент должен быть удален
			 */
			$deleteSectionsId = array();
			foreach ($oldSections as $o) {
				// Раздел к которому был привязан элемент отсутствует в списке разделов к которым нужно привязать элемент
				//echo 'Удаляем из разделов: <pre>[' . print_r($o->getName(), true) . ']</pre>';
				if (!$newSections->contains($o)) {
					$deleteSectionsId[] = $o->getId();
				}
			}
			$countDeleteSections = count($deleteSectionsId);

			/*
			 * Удаляем элемент из разделов
			 */
			//echo 'ИД разделов, из котрых элемент должен быть удален: <pre>' . print_r($deleteSectionsId, true) . '</pre>';
			$eSection = $element->getSection();
			$countESection = count($eSection);
			foreach ($eSection as $obj) {
				//$obj->getSection();
				//echo '<pre>' . print_r($obj->getSection(), true) . '</pre>';
				if ($obj->getSection()) {
					if (in_array($obj->getSection()->getId(), $deleteSectionsId)) {
						//echo '<pre>' . print_r('Удаляем '.$obj->getId(), true) . '</pre>';
						$this->em->remove($obj);
					}
				} else {
					//echo '<pre>' . print_r('Элемент прикреплен к разделу NULL', true) . '</pre>';
					if (
						($countAddSections > 0 && $countESection > 0)
						|| ($countAddSections == 0 && $countDeleteSections == 0 && $countESection > 1)
					) {
						$this->em->remove($obj);
					}
				}

			}

			/*
			 * Создаем нулевую запись, если элемент не прикрепелен к разделам
			 */
			if ($countAddSections == 0 && ($countESection == $countDeleteSections)) {
				$ElementSection = new ElementSection();
				$ElementSection->setElement($element);
				$ElementSection->setSection(null);
				$this->em->persist($ElementSection);
			}

		} else {
			throw new \Exception('$element must be Element object');
		}
	}

	public function getSections($element = false)
	{
		$ElementsSections = $this->getElementsSections();
		if (is_numeric($element)) {
			//$element = array($element);
			//throw new \Exception('$element must be Element object');
			$elementId = $element;
			$element = $this->em->getReference('NovuscomCMFBundle:Element', $elementId);
		} else if (is_array($element)) {
			//$entity = $this->em->getRepository('NovuscomCMFBundle:Element')->findBy(array('id' => $element));
			//$this->setSections($entity);
			throw new \Exception('$element must be Element object');
		} else if (is_object($element)) {
			$elementId = $element->getId();
		} else {
			throw new \Exception('$element must be Element object');
		}
		if (array_key_exists($elementId, $ElementsSections)) {
			$sections = $ElementsSections[$elementId];
		} else {
			$this->setSectionsByEntity($element);
			$sections = $this->ElementsSections[$elementId];
		}

		return $sections;
	}

	public function setEM(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
	}

	private function getElementsSections()
	{
		return $this->ElementsSections;
	}

	private function setSectionsByEntity($entity)
	{
		//echo '<pre>' . print_r('setSectionsByEntity(' . $entity->getName() . ')', true) . '</pre>';

		$this->ElementsSections[$entity->getId()] = new ArrayCollection();
		$entitySections = $entity->getSection();

		$sectionsId = array();
		// TODO Здесь нормальный сделать один запрос а не для каждого элемента запрашивать разделы
		foreach ($entitySections as $o) {
			if ($o->getSection()) {
				$sectionsId[] = $o->getSection()->getId();
			} else {

			}
		}
		if ($sectionsId) {
			$sectionsById = $this->em->getRepository('NovuscomCMFBundle:Section')->findBy(array('id' => $sectionsId));
			foreach ($sectionsById as $s) {
				//$sections->add($s);
				//echo '<pre>' . print_r($s->getName(), true) . '</pre>';
				$this->ElementsSections[$entity->getId()]->add($s);
			}
		}

	}

	public function getProperties($code_array, $block_id)
	{
		$em = $this->em;
		$query = $em->createQueryBuilder('n')
			->from('NovuscomCMFBundle:Property', 'n', 'n.id')
			->select('n.code, n.type, n.name')
			->addSelect('n.info, n.id')
			->where('n.block = :block_id')
			->andWhere('n.code IN(:code)')
			->setParameter('block_id', $block_id)
			->setParameter('code', $code_array)
			->getQuery();
		$sql = $query->getSql();
		$properties = $query->getResult();
		return $properties;
	}


	public function getFullCode($entity)
	{
		$sections = $this->getSections($entity);
		$routes = $this->routeService->getCurrentSiteRoutes();

		foreach ($routes as $r) {
			Utils::msg($r->getCode());
		}
		foreach ($sections as $s) {

			Utils::msg($this->sectionService->getFullCode($s->getId()));
		}
	}


	/*
	 * Получает значения свойств элемента типа "Раздел"
	 */
	public function getPropertiesSectionValues($element, $properties_id)
	{
		$em = $this->em;
		$query = $em->createQueryBuilder('n')
			->from('NovuscomCMFBundle:ElementPropertySection', 'n', 'n.id')
			->select('n.id, n.description')
			->addSelect('IDENTITY(n.element) as element_id, IDENTITY(n.property) as property_id, IDENTITY(n.section) as section_id');
		if ($properties_id) {
			$query->andWhere('n.property IN(:property_id)')->setParameter('property_id', $properties_id);
		}
		if ($element) {
			$query = $query->andWhere('n.element IN(:element_id)');
			$query = $query->setParameter('element_id', $element);
		}
		$query = $query->getQuery();
		$sql = $query->getSql();
		$result = $query->getResult();
		return $result;
	}

	public function getPropertiesValues($elements_id, $properties_id)
	{
		$em = $this->em;

		//$elements_id = array();

		$query = $em->createQueryBuilder('n')
			->from('NovuscomCMFBundle:ElementProperty', 'n', 'n.id')
			->select('n.id, n.value, n.description')
			->addSelect('IDENTITY(n.element) as element_id, IDENTITY(n.property) as property_id');
		if ($properties_id) {
			$query->andWhere('n.property IN(:property_id)')->setParameter('property_id', $properties_id);
		}
		if ($elements_id) {
			$query = $query->andWhere('n.element IN(:element_id)');
			$query = $query->setParameter('element_id', $elements_id);
		}
		$query = $query->getQuery();
		$sql = $query->getSql();
		$result = $query->getResult();
		//echo '<pre>' . print_r($sql, true) . '</pre>';
		return $result;
	}

	private $container;
	private $Utils;
	private $Router;
	private $file;
	private $sectionService;
	private $routeService;

	public function __construct(
		\Doctrine\ORM\EntityManager $em,
		Logger $logger,
		ContainerInterface $container,
		Utils $Utils,
		Router $Router,
		\Novuscom\CMFBundle\Services\File $file,
		\Novuscom\CMFBundle\Services\Section $sectionService,
		Route $routeService
	)
	{
		$this->file = $file;
		$this->em = $em;
		$this->container = $container;
		$this->Utils = $Utils;
		$this->Router = $Router;
		$this->sectionService = $sectionService;
		$this->routeService = $routeService;
	}
}