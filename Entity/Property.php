<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Property
 */
class Property
{
   
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $info;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ElementProperty;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ElementPropertyF;

    /**
     * @var \Novuscom\Bundle\CMFBundle\Entity\Block
     */
    private $block;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ElementProperty = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ElementPropertyF = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set name
     *
     * @param string $name
     * @return Property
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Property
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
     * Set type
     *
     * @param string $type
     * @return Property
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set info
     *
     * @param array $info
     * @return Property
     */
    public function setInfo($info)
    {
        $this->info = $info;
    
        return $this;
    }

    /**
     * Get info
     *
     * @return array 
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Add ElementProperty
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\ElementProperty $elementProperty
     * @return Property
     */
    public function addElementProperty(\Novuscom\Bundle\CMFBundle\Entity\ElementProperty $elementProperty)
    {
        $this->ElementProperty[] = $elementProperty;
    
        return $this;
    }

    /**
     * Remove ElementProperty
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\ElementProperty $elementProperty
     */
    public function removeElementProperty(\Novuscom\Bundle\CMFBundle\Entity\ElementProperty $elementProperty)
    {
        $this->ElementProperty->removeElement($elementProperty);
    }

    /**
     * Get ElementProperty
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getElementProperty()
    {
        return $this->ElementProperty;
    }

    /**
     * Add ElementPropertyF
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\ElementPropertyF $elementPropertyF
     * @return Property
     */
    public function addElementPropertyF(\Novuscom\Bundle\CMFBundle\Entity\ElementPropertyF $elementPropertyF)
    {
        $this->ElementPropertyF[] = $elementPropertyF;
    
        return $this;
    }

    /**
     * Remove ElementPropertyF
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\ElementPropertyF $elementPropertyF
     */
    public function removeElementPropertyF(\Novuscom\Bundle\CMFBundle\Entity\ElementPropertyF $elementPropertyF)
    {
        $this->ElementPropertyF->removeElement($elementPropertyF);
    }

    /**
     * Get ElementPropertyF
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getElementPropertyF()
    {
        return $this->ElementPropertyF;
    }

    /**
     * Set block
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Block $block
     * @return Property
     */
    public function setBlock(\Novuscom\Bundle\CMFBundle\Entity\Block $block = null)
    {
        $this->block = $block;
    
        return $this;
    }

    /**
     * Get block
     *
     * @return \Novuscom\Bundle\CMFBundle\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ElementPropertyDT;


    /**
     * Add ElementPropertyDT
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT
     * @return Property
     */
    public function addElementPropertyDT(\Novuscom\Bundle\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT)
    {
        $this->ElementPropertyDT[] = $elementPropertyDT;
    
        return $this;
    }

    /**
     * Remove ElementPropertyDT
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT
     */
    public function removeElementPropertyDT(\Novuscom\Bundle\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT)
    {
        $this->ElementPropertyDT->removeElement($elementPropertyDT);
    }

    /**
     * Get ElementPropertyDT
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getElementPropertyDT()
    {
        return $this->ElementPropertyDT;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $PropertyList;


    /**
     * Add PropertyList
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\PropertyList $propertyList
     * @return Property
     */
    public function addPropertyList(\Novuscom\Bundle\CMFBundle\Entity\PropertyList $propertyList)
    {
        $this->PropertyList[] = $propertyList;
    
        return $this;
    }

    /**
     * Remove PropertyList
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\PropertyList $propertyList
     */
    public function removePropertyList(\Novuscom\Bundle\CMFBundle\Entity\PropertyList $propertyList)
    {
        $this->PropertyList->removeElement($propertyList);
    }

    /**
     * Get PropertyList
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPropertyList()
    {
        return $this->PropertyList;
    }
}