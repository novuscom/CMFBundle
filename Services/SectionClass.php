<?php

namespace Novuscom\Bundle\CMFBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\Bundle\CMFBundle\Entity\ElementSection;

class SectionClass
{

	private function SetSectionsCodeTree(&$sectionsArray, $parent_full_code = '')
	{
		usort($sectionsArray, function($a, $b){
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
		return $sectionsArray;
	}

	public function SectionsList($filter, $parentFullCode = '')
	{
		$em = $this->em;
		$repo = $em->getRepository('NovuscomCMFBundle:Section');
		$builder = $em
			->createQueryBuilder()
			->select('n.name, n.code, n.preview_text, n.lvl, n.lft, n.rgt, n.id, n.sort')
			->addSelect('IDENTITY(n.PreviewPicture) as preview_picture')
			->from('NovuscomCMFBundle:Section', 'n')
			->where('n.block=:block_id')
			->setParameter('block_id', $filter['block_id'])
			->orderBy('n.root, n.lft', 'ASC')
		;
		if (array_key_exists('section_id', $filter)) {
			$builder->andWhere('n.parent=:section_id');
			$builder->setParameter('section_id', $filter['section_id']);
		}
		$query = $builder->getQuery();
        $result = $query->getArrayResult();
        //echo '<pre>' . print_r(count($result), true) . '</pre>';
        //echo '<pre>' . print_r($result, true) . '</pre>'; exit;
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

	public function GetSectionByPath($section_code_path, $block_id, $params = array())
	{
		$result = false;
		$er = $this->em->getRepository('NovuscomCMFBundle:Section');
		$block_reference = $this->em->getReference('Novuscom\Bundle\CMFBundle\Entity\Block', $block_id);
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
				//echo '<pre>' . print_r($maxLevel, true) . '</pre>';
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
				$entities_count = count($entities_by_last_code);
				if ($entities_count > 0) {
					//echo '<pre>' . print_r($entities_count, true) . '</pre>';
					$selectedCodes = array(
						1 => $root->getCode()
					);
					foreach ($entities_by_last_code as $i => $p) {
						$selectedCodes[] = $p->getCode();
					}
					//echo '<pre>' . print_r($selectedCodes, true) . '</pre>';
					$n = $entities_count - 1;
					$result = $entities_by_last_code[$n];
				}
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