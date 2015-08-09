<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\CMFBundle\Entity\ElementSection;

class Cart
{

    public function GetItems($id)
    {
        $items = array();

        return $items;
    }

    public function GetById($id)
    {
        if (is_numeric($id) == false)
            return false;
        $entity = $this->em->getRepository('NovuscomCMFBundle:Cart')->find($id);
        return $entity;
    }

    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }
}