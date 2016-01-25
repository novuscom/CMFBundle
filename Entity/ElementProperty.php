<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ElementProperty
 */
class ElementProperty
{
    
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Novuscom\Bundle\CMFBundle\Entity\Element
     */
    private $element;

    /**
     * @var \Novuscom\Bundle\CMFBundle\Entity\Property
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
     * Set value
     *
     * @param string $value
     * @return ElementProperty
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ElementProperty
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
     * Set element
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Element $element
     * @return ElementProperty
     */
    public function setElement(\Novuscom\Bundle\CMFBundle\Entity\Element $element = null)
    {
        $this->element = $element;
    
        return $this;
    }

    /**
     * Get element
     *
     * @return \Novuscom\Bundle\CMFBundle\Entity\Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set property
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Property $property
     * @return ElementProperty
     */
    public function setProperty(\Novuscom\Bundle\CMFBundle\Entity\Property $property = null)
    {
        $this->property = $property;
    
        return $this;
    }

    /**
     * Get property
     *
     * @return \Novuscom\Bundle\CMFBundle\Entity\Property
     */
    public function getProperty()
    {
        return $this->property;
    }
}