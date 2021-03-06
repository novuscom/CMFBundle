<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Element
 */
class FrontElement
{
    private $info;

    public function setInfo(\Novuscom\CMFBundle\Entity\Element $element)
    {
        $this->info = $element;
        return $this;
    }

    public function getInfo()
    {
        return $this->info;
    }

    private $propertyFiles;

    public function addPropertyFile(\Novuscom\CMFBundle\Entity\File $propertyF)
    {
        $this->propertyFiles[] = $propertyF;
        return $this;
    }

    public function getPropertyFiles()
    {
        return $this->propertyFiles;
    }

    private $elements;

    public function addElement(\Novuscom\CMFBundle\Entity\Element $element)
    {
        $this->elements[] = $element;
        return $this;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function __construct()
    {
        $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
    }
}