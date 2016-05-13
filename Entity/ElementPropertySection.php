<?php

namespace Novuscom\CMFBundle\Entity;

/**
 * ElementPropertySection
 */
class ElementPropertySection
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $propertyId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ElementPropertySection
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set propertyId
     *
     * @param integer $propertyId
     *
     * @return ElementPropertySection
     */
    public function setPropertyId($propertyId)
    {
        $this->propertyId = $propertyId;

        return $this;
    }

    /**
     * Get propertyId
     *
     * @return int
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }
    /**
     * @var \Novuscom\CMFBundle\Entity\Element
     */
    private $element;
    /**
     * Set element
     *
     * @param \Novuscom\CMFBundle\Entity\Element $element
     * @return ElementPropertyDT
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
     * @var \Novuscom\CMFBundle\Entity\Property
     */
    private $property;


    /**
     * Set property
     *
     * @param \Novuscom\CMFBundle\Entity\Property $property
     * @return ElementPropertyDT
     */
    public function setProperty(\Novuscom\CMFBundle\Entity\Property $property = null)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property
     *
     * @return \Novuscom\CMFBundle\Entity\Property
     */
    public function getProperty()
    {
        return $this->property;
    }


    /**
     * @var \Novuscom\CMFBundle\Entity\Section
     */
    private $section;


    /**
     * Set section
     *
     * @param \Novuscom\CMFBundle\Entity\Section $section
     * @return ElementPropertySection
     */
    public function setSection(\Novuscom\CMFBundle\Entity\Section $section)
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

