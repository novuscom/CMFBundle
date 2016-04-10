<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PropertyList
 */
class PropertyList
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
    private $code;

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
     * Set value
     *
     * @param string $value
     * @return PropertyList
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
     * Set code
     *
     * @param string $code
     * @return PropertyList
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set property
     *
     * @param \Novuscom\CMFBundle\Entity\Property $property
     * @return PropertyList
     */
    public function setProperty(\Novuscom\CMFBundle\Entity\Property $property)
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