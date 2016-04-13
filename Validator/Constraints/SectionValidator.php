<?php

namespace Novuscom\CMFBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Novuscom\CMFBundle\Services\Utils;

class SectionValidator extends ConstraintValidator
{
	public function validate($object, Constraint $constraint)
	{
		$this->object = $object;
		$this->constraint = $constraint;
		$this->validateCode();
		$this->validateLevel();
	}

	private function validateLevel()
	{
		if (!$this->object->getParentId())
			return true;
		$parentSection = $this->getRepo()->find($this->object->getParentId());
		$block = $this->getBlockRepo()->find($this->object->getBlockId());
		$limit = $block->getParam('SECTIONS_LEVEL_LIMIT');
		if ($limit and $parentSection->getLevel() >= $limit) {
			$this->context->buildViolation('Нельзя добавить раздел по причине ограничения вложенности (' . $limit . ')')
				->atPath('name')
				->addViolation();
		}
	}

	private function validateCode()
	{
		$object = $this->object;
		$constraint = $this->constraint;
		$repo = $this->getRepo();
		$parent = $object->getParent();
		if (!$parent) {
			$parentId = $object->getParentId();
			if ($parentId)
				$parent = $repo->find($parentId);
		}
		//echo '<pre>' . print_r($parentId, true) . '</pre>'; exit;
		$neighborsCode = array();
		if ($parent) {
			$level = $object->getLvl();
			if (!$level) {
				$level = ($parent->getLvl() + 1);
			}
			$qb = $this->em->createQueryBuilder();
			$query = $qb->select('node.code, node.id')
				->from('NovuscomCMFBundle:Section', 'node')
				->orderBy('node.root, node.lft', 'ASC')
				->where('node.root = :root_id')
				->setParameter('root_id', $parent->getId());
			if ($object->getId()) $query->andWhere($qb->expr()->notIn('node.id', $object->getId()));
			if ($level) {
				$query->andWhere('node.lvl = :level');
				$query->setParameter('level', $level);
			};
			$query = $query->getQuery();
			foreach ($query->getResult() as $r) {
				$neighborsCode[$r['id']] = $r['code'];
			}
		} else {
			foreach ($repo->getRootNodes() as $index => $rootNode) {
				$neighborsCode[$rootNode->getId()] = strtolower($rootNode->getCode());
			};
		}
		$search = array_search(strtolower($object->getCode()), $neighborsCode);
		if ($search && $search != $object->getId()) {
			$this->context->buildViolation($constraint->message)
				->atPath('code')
				->addViolation();
		}
	}

	private function getBlockRepo()
	{
		return $this->em->getRepository('NovuscomCMFBundle:Block');
	}

	private function getRepo()
	{
		return $this->em->getRepository('NovuscomCMFBundle:Section');
	}

	private $constraint;
	private $object;
	private $em;
	private $request;

	public function __construct(EntityManager $em, RequestStack $request)
	{
		$this->em = $em;
		$this->request = $request;
	}

}