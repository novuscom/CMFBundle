<?php

namespace Novuscom\CMFBundle\Entity;

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
     * @var \Novuscom\CMFBundle\Entity\Block
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
     * @param \Novuscom\CMFBundle\Entity\ElementProperty $elementProperty
     * @return Property
     */
    public function addElementProperty(\Novuscom\CMFBundle\Entity\ElementProperty $elementProperty)
    {
        $this->ElementProperty[] = $elementProperty;
    
        return $this;
    }

    /**
     * Remove ElementProperty
     *
     * @param \Novuscom\CMFBundle\Entity\ElementProperty $elementProperty
     */
    public function removeElementProperty(\Novuscom\CMFBundle\Entity\ElementProperty $elementProperty)
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
     * @param \Novuscom\CMFBundle\Entity\ElementPropertyF $elementPropertyF
     * @return Property
     */
    public function addElementPropertyF(\Novuscom\CMFBundle\Entity\ElementPropertyF $elementPropertyF)
    {
        $this->ElementPropertyF[] = $elementPropertyF;
    
        return $this;
    }

    /**
     * Remove ElementPropertyF
     *
     * @param \Novuscom\CMFBundle\Entity\ElementPropertyF $elementPropertyF
     */
    public function removeElementPropertyF(\Novuscom\CMFBundle\Entity\ElementPropertyF $elementPropertyF)
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
     * @param \Novuscom\CMFBundle\Entity\Block $block
     * @return Property
     */
    public function setBlock(\Novuscom\CMFBundle\Entity\Block $block = null)
    {
        $this->block = $block;
    
        return $this;
    }

    /**
     * Get block
     *
     * @return \Novuscom\CMFBundle\Entity\Block
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
     * @param \Novuscom\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT
     * @return Property
     */
    public function addElementPropertyDT(\Novuscom\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT)
    {
        $this->ElementPropertyDT[] = $elementPropertyDT;
    
        return $this;
    }

    /**
     * Remove ElementPropertyDT
     *
     * @param \Novuscom\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT
     */
    public function removeElementPropertyDT(\Novuscom\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT)
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
    private $ElementPropertySection;


    /**
     * Add ElementPropertySection
     *
     * @param \Novuscom\CMFBundle\Entity\ElementPropertySection $elementPropertySection
     * @return Property
     */
    public function addElementPropertySection(\Novuscom\CMFBundle\Entity\ElementPropertySection $elementPropertySection)
    {
        $this->ElementPropertySection[] = $elementPropertySection;

        return $this;
    }

    /**
     * Remove ElementPropertySection
     *
     * @param \Novuscom\CMFBundle\Entity\ElementPropertySection $elementPropertySection
     */
    public function removeElementPropertySection(\Novuscom\CMFBundle\Entity\ElementPropertySection $elementPropertySection)
    {
        $this->ElementPropertySection->removeElement($elementPropertySection);
    }

    /**
     * Get ElementPropertySection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getElementPropertySection()
    {
        return $this->ElementPropertySection;
    }



	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $SectionProperty;


	/**
	 * Add ElementPropertySection
	 *
	 * @param \Novuscom\CMFBundle\Entity\SectionProperty $SectionProperty
	 * @return Property
	 */
	public function addSectionProperty(\Novuscom\CMFBundle\Entity\SectionProperty $SectionProperty)
	{
		$this->SectionProperty[] = $SectionProperty;

		return $this;
	}

	/**
	 * Remove SectionProperty
	 *
	 * @param \Novuscom\CMFBundle\Entity\SectionProperty $SectionProperty
	 */
	public function removeSectionProperty(\Novuscom\CMFBundle\Entity\SectionProperty $SectionProperty)
	{
		$this->SectionProperty->removeElement($SectionProperty);
	}

	/**
	 * Get ElementPropertySection
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSectionProperty()
	{
		return $this->SectionProperty;
	}



    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $PropertyList;


    /**
     * Add PropertyList
     *
     * @param \Novuscom\CMFBundle\Entity\PropertyList $propertyList
     * @return Property
     */
    public function addPropertyList(\Novuscom\CMFBundle\Entity\PropertyList $propertyList)
    {
        $this->PropertyList[] = $propertyList;
    
        return $this;
    }

    /**
     * Remove PropertyList
     *
     * @param \Novuscom\CMFBundle\Entity\PropertyList $propertyList
     */
    public function removePropertyList(\Novuscom\CMFBundle\Entity\PropertyList $propertyList)
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
    /**
     * @var boolean
     */
    private $isForSection;


    /**
     * Set isForSection
     *
     * @param boolean $isForSection
     *
     * @return Property
     */
    public function setIsForSection($isForSection)
    {
        $this->isForSection = $isForSection;

        return $this;
    }

    /**
     * Get isForSection
     *
     * @return boolean
     */
    public function getIsForSection()
    {
        return $this->isForSection;
    }
}
