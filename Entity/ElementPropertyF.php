<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementPropertyF
 */
class ElementPropertyF
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $property_id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Novuscom\CMFBundle\Entity\File
     */
    private $file;

    /**
     * @var \Novuscom\CMFBundle\Entity\Element
     */
    private $element;

    /**
     * @var \Novuscom\CMFBundle\Entity\Property
     */
    private $property;


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
     * Set property_id
     *
     * @param integer $propertyId
     * @return ElementPropertyF
     */
    public function setPropertyId($propertyId)
    {
        $this->property_id = $propertyId;
    
        return $this;
    }

    /**
     * Get property_id
     *
     * @return integer 
     */
    public function getPropertyId()
    {
        return $this->property_id;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ElementPropertyF
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
     * Set file
     *
     * @param \Novuscom\CMFBundle\Entity\File $file
     * @return ElementPropertyF
     */
    public function setFile(\Novuscom\CMFBundle\Entity\File $file = null)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * @return \Novuscom\CMFBundle\Entity\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set element
     *
     * @param \Novuscom\CMFBundle\Entity\Element $element
     * @return ElementPropertyF
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
     * Set property
     *
     * @param \Novuscom\CMFBundle\Entity\Property $property
     * @return ElementPropertyF
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
}