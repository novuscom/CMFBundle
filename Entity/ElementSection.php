<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementSection
 */
class ElementSection
{
    /**
     * @var integer
     */
    private $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var \Novuscom\CMFBundle\Entity\Element
     */
    private $element;

    /**
     * @var \Novuscom\CMFBundle\Entity\Section
     */
    private $section;


    /**
     * Set element
     *
     * @param \Novuscom\CMFBundle\Entity\Element $element
     * @return ElementSection
     */
    public function setElement(\Novuscom\CMFBundle\Entity\Element $element = null)
    {
        $this->element = $element;
    
        return $this;
    }

    /**
     * Get element
     *
     * @return \Novuscom\CMFBundle\Entity\Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set section
     *
     * @param \Novuscom\CMFBundle\Entity\Section $section
     * @return ElementSection
     */
    public function setSection(\Novuscom\CMFBundle\Entity\Section $section = null)
    {
        $this->section = $section;
    
        return $this;
    }

    /**
     * Get section
     *
     * @return \Novuscom\CMFBundle\Entity\Section
     */
    public function getSection()
    {
        return $this->section;
    }
}