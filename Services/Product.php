<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\CMFBundle\Entity\ElementSection;

class Product
{

    public function GetByElementId($element_id)
    {
        if (is_numeric($element_id) == false)
            return false;
        $element_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Element', $element_id);
        $entity = $this->em->getRepository('NovuscomCMFBundle:Product')->findBy(array(
            'element' => $element_reference
        ));
        return $entity;
    }

    public function GetById($id)
    {
        if (is_numeric($id) == false)
            return false;
        $entity = $this->em->getRepository('NovuscomCMFBundle:Product')->find($id);
        return $entity;
    }

    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }
}