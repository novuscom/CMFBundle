<?php

namespace Novuscom\CMFBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\CMFBundle\Entity\ElementSection;

class SectionClass
{

	public function getPropertyValues($propertyId, $sectionId = false)
	{
		$em = $this->em;
		$builder = $em
			->createQueryBuilder()
			->select('n.id, n.value, n.description')
			->addSelect('IDENTITY(n.section) as section_id')
			->addSelect('IDENTITY(n.property) as property_id')
			->from('NovuscomCMFBundle:SectionProperty', 'n', 'n.id')
			->where('n.property IN (:property_id)')
			->setParameter('property_id', $propertyId);
		$builder->orderBy('n.id', 'ASC');
		$query = $builder->getQuery();
		$result = $query->getArrayResult();
		$res = array();
		foreach ($result as $r) {
			$res[$r['section_id']][$r['property_id']] = array(
				'value' => $r['value'],
				'id' => $r['id'],
				'description' => $r['description'],
			);
		}
		return $res;
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
			->setParameter('block_id', $block_id)
			->orderBy('n.name', 'ASC');
		$query = $builder->getQuery();
		$result = $query->getArrayResult();
		return $result;
	}

	private function SetSectionsCodeTree(&$sectionsArray, $parent_full_code = '')
	{
		usort($sectionsArray, function ($a, $b) {
			return $a['sort'] - $b['sort'];
		});
		//echo '<pre>' . print_r($sectionsArray, true) . '</pre>';
		foreach ($sectionsArray as &$section) {
			if ($parent_full_code)
				$section['full_code'] = $parent_full_code . '/' . $section['code'];
			else
				$section['full_code'] = $section['code'];
			$section['fullCode'] = $section['full_code'];
			if (!empty($section['__children'])) {
				$this->SetSectionsCodeTree($section['__children'], $section['full_code']);
			}
		}
		//echo '<pre>' . print_r($sectionsArray, true) . '</pre>';
		return $sectionsArray;
	}

	public function SectionsList($filter, $parentFullCode = '')
	{
		//echo '<pre>' . print_r('SectionsList', true) . '</pre>';
		$em = $this->em;
		$repo = $em->getRepository('NovuscomCMFBundle:Section');
		$builder = $em
			->createQueryBuilder()
			->select('n.name, n.code, n.preview_text, n.lvl, n.lft, n.rgt, n.id, n.sort')
			->addSelect('IDENTITY(n.PreviewPicture) as preview_picture')
			->from('NovuscomCMFBundle:Section', 'n')
			->where('n.block=:block_id')
			->setParameter('block_id', $filter['block_id'])
			->orderBy('n.root, n.lft', 'ASC');
		if (array_key_exists('section_id', $filter)) {
			$builder->andWhere('n.parent=:section_id');
			$builder->setParameter('section_id', $filter['section_id']);
		}
		$query = $builder->getQuery();
		$result = $query->getArrayResult();
		//echo '<pre>' . print_r(count($result), true) . '</pre>';
		$properties = $this->getProperties($filter['block_id']);
		$propertyValues = $this->getPropertyValues(array_keys($properties));

		//echo '<pre>' . print_r($propertyValues, true) . '</pre>';

		$propertiesByCode = array();
		foreach ($properties as &$prop) {
			//$prop['value'] = $propertyValues[$prop['id']];
			$propertiesByCode[$prop['code']] = $prop;
		}

		foreach ($result as &$r) {
			$pB = array();
			foreach ($propertiesByCode as $key => $pc) {
				$pc['value'] = null;
				if (isset($propertyValues[$r['id']][$pc['id']])) {
					//echo '<pre>' . print_r($r['id'].'-'.$pc['id'], true) . '</pre>';
					$pc['value'] = $propertyValues[$r['id']][$pc['id']]['value'];
				}
				else {
					$pc['value'] = null;
				}
				$pB[$key] = $pc;
			}
			//echo '<pre>' . print_r($pB, true) . '</pre>';
			$r['properties'] = $pB;
		}

		//echo '<pre>' . print_r($result, true) . '</pre>';

		//echo '<pre>' . print_r($properties, true) . '</pre>';
		//echo '<pre>' . print_r($propertiesByCode, true) . '</pre>';

		//exit;
		//echo '<pre>' . print_r($result, true) . '</pre>';
		//exit;
		$options = array('decorate' => false);
		$tree = $repo->buildTree($result, $options);
		//echo '<pre>' . print_r(count($tree), true) . '</pre>';
		return $this->SetSectionsCodeTree($tree, $parentFullCode);
	}

	public function GetTreeArray($sections)
	{
		$result = array();

		$by_id = array();
		foreach ($sections as $s) {
			$by_id[$s->getId()] = $s;
		}


		return $result;
	}

	/**
	 * Используется для компонента Section и Crumbs
	 * необходимо кешировать
	 * @param $section_code_path
	 * @param $block_id
	 * @param array $params
	 * @return bool
	 */
	public function GetSectionByPath($section_code_path, $block_id, $params = array())
	{
		$result = false;
		$er = $this->em->getRepository('NovuscomCMFBundle:Section');
		$block_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Block', $block_id);
		$codeArray = $this->getPathArray($section_code_path);
		$maxLevel = count($codeArray);
		$filter_params = array(
			'block' => $block_reference,
			'code' => $codeArray[0],
			'lvl' => 0
		);
		if (is_array($params) && array_key_exists('root_level', $params) && is_numeric($params['root_level'])) {
			$filter_params['lvl'] = $params['root_level'];
		}
		$root = $er->findOneBy($filter_params);
		if ($root) {
			if ($maxLevel > 1) {
				$entities_by_last_code = $er->createQueryBuilder('p')
					->where("p.block=:block")
					->andWhere("p.lft>:left")
					->andWhere("p.rgt<:right")
					->andWhere("p.code=:code")
					->setParameters(array(
						'block' => $block_reference,
						'left' => $root->getLft(),
						'right' => $root->getRgt(),
						'code' => $codeArray[$maxLevel - 1]
					))
					->orderBy('p.lft', 'ASC')
					->getQuery()
					->getResult();
				$pages_by_id = array();
				$paths = array();
				$paths_array = array();
				foreach ($entities_by_last_code as $p) {
					$pages_by_id[$p->getId()] = $p;
					$path = $er->getPath($p);
					$path_id = array();
					foreach ($path as $pa) {
						$path_id[] = $pa->getCode();
						$paths_array[$p->getId()][] = $pa;
					}
					$paths[$p->getId()] = $path_id;
				}
				$page_id = false;
				foreach ($paths as $id => $array) {
					//array_shift($array);
					if ($array == $codeArray) {
						$page_id = $id;
						break;
					}
				}
				if (!$page_id) {
					//throw $this->createNotFoundException('Страница не найдена (путь не найден)');
					//$this->setExceptionText('Страница не найдена (путь не найден)');
				} else
					$result = $pages_by_id[$page_id];
			} else {
				$result = $root;
			}
		}
		return $result;
	}

	private $em;

	private function getSectionByCodeArray($section_code_path)
	{

	}

	private function getPathArray($path)
	{
		$result = explode('/', $path);
		$result = array_filter($result);
		return $result;
	}

	public function __construct(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
	}
}