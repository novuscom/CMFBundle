<?php

namespace Novuscom\Bundle\CMFBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Novuscom\Bundle\CMFBundle\Services\Utils;

class SectionCodeValidator extends ConstraintValidator
{
	public function validate($object, Constraint $constraint)
	{
		$repo = $this->em->getRepository('NovuscomCMFBundle:Section');
		$parent = $object->getParent();
		$currentRequest = $this->request->getCurrentRequest();
		if (!$parent) {
			//echo '<pre>' . print_r($currentRequest->get('_route_params'), true) . '</pre>';
			$parentId = $object->getParentId();
			//echo '<pre>' . print_r($parentId, true) . '</pre>';
			if ($parentId)
				$parent = $repo->find($parentId);
		}
		//echo '<pre>' . print_r($object->getParentId(), true) . '</pre>'; exit;
		$neighborsCode = array();
		if ($parent) {
			//echo '<pre>' . print_r('Parent YES! ', true) . '</pre>';
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
			//->setParameter('id', $object->getId())
			if ($object->getId()) $query->andWhere($qb->expr()->notIn('node.id', $object->getId()));
			if ($level) {
				$query->andWhere('node.lvl = :level');
				$query->setParameter('level', $level);
			};
			$query = $query->getQuery();
			$sql = $query->getSql();
			$parameters = $query->getParameters();
			//echo '<pre>' . print_r($sql, true) . '</pre>';
			//echo '<pre>' . print_r($parameters, true) . '</pre>';
			foreach ($query->getResult() as $r) {
				//echo '<pre>' . print_r($r, true) . '</pre>';
				$neighborsCode[$r['id']] = $r['code'];
			}
		} else {
			//echo '<pre>' . print_r('No parent!', true) . '</pre>';
			foreach ($repo->getRootNodes() as $index => $rootNode) {
				$neighborsCode[$rootNode->getId()] = strtolower($rootNode->getCode());
			};
		}

		$search = array_search(strtolower($object->getCode()), $neighborsCode);
		//echo '<pre>Соседи:' . "\n" . print_r($neighborsCode, true) . '</pre>';
		//echo '<pre>Код нового раздела: ' . "\n" . print_r($object->getCode(), true) . '</pre>';
		//echo '<pre>' . print_r($object->getId(), true) . '</pre>';
		//echo '<pre>' . print_r($search, true) . '</pre>';
		//exit;
		if ($search && $search != $object->getId()) {
			$this->context->buildViolation($constraint->message)
				->atPath('code')
				->addViolation();
		}
	}

	private $em;
	private $request;

	public function __construct(EntityManager $em, RequestStack $request)
	{
		$this->em = $em;
		$this->request = $request;
	}

}