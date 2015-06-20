<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Element
 */
class Element
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
    private $preview_text;

    /**
     * @var string
     */
    private $detail_text;

    /**
     * @var \DateTime
     */
    private $last_modified;

    /**
     * @var \Novuscom\CMFBundle\Entity\File
     */
    private $PreviewPicture;

    /**
     * @var \Novuscom\CMFBundle\Entity\File
     */
    private $DetailPicture;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ElementProperty;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ElementPropertyF;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $section;

    /**
     * @var \Novuscom\CMFBundle\Entity\Block
     */
    private $block;

    private $properties;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ElementProperty = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ElementPropertyF = new \Doctrine\Common\Collections\ArrayCollection();
        $this->section = new \Doctrine\Common\Collections\ArrayCollection();
        $this->properties = array();
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
     * @return Element
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
     * @return Element
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
     * Set preview_text
     *
     * @param string $previewText
     * @return Element
     */
    public function setPreviewText($previewText)
    {
        $this->preview_text = $previewText;

        return $this;
    }

    /**
     * Get preview_text
     *
     * @return string
     */
    public function getPreviewText()
    {
        return $this->preview_text;
    }

    /**
     * Set detail_text
     *
     * @param string $detailText
     * @return Element
     */
    public function setDetailText($detailText)
    {
        $this->detail_text = $detailText;

        return $this;
    }

    /**
     * Get detail_text
     *
     * @return string
     */
    public function getDetailText()
    {
        return $this->detail_text;
    }

    /**
     * Set last_modified
     *
     * @param \DateTime $lastModified
     * @return Element
     */
    public function setLastModified($lastModified)
    {
        $this->last_modified = $lastModified;

        return $this;
    }

    /**
     * Get last_modified
     *
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->last_modified;
    }

    /**
     * Set PreviewPicture
     *
     * @param \Novuscom\CMFBundle\Entity\File $previewPicture
     * @return Element
     */
    public function setPreviewPicture(\Novuscom\CMFBundle\Entity\File $previewPicture = null)
    {
        $this->PreviewPicture = $previewPicture;

        return $this;
    }

    /**
     * Get PreviewPicture
     *
     * @return \Novuscom\CMFBundle\Entity\File
     */
    public function getPreviewPicture()
    {
        return $this->PreviewPicture;
    }

    /**
     * Set DetailPicture
     *
     * @param \Novuscom\CMFBundle\Entity\File $detailPicture
     * @return Element
     */
    public function setDetailPicture(\Novuscom\CMFBundle\Entity\File $detailPicture = null)
    {
        $this->DetailPicture = $detailPicture;

        return $this;
    }

    /**
     * Get DetailPicture
     *
     * @return \Novuscom\CMFBundle\Entity\File
     */
    public function getDetailPicture()
    {
        return $this->DetailPicture;
    }

    /**
     * Add ElementProperty
     *
     * @param \Novuscom\CMFBundle\Entity\ElementProperty $elementProperty
     * @return Element
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
     * @return Element
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
     * Add section
     *
     * @param \Novuscom\CMFBundle\Entity\ElementSection $section
     * @return Element
     */
    public function addSection(\Novuscom\CMFBundle\Entity\ElementSection $section)
    {
        $this->section[] = $section;

        return $this;
    }

    /**
     * Remove section
     *
     * @param \Novuscom\CMFBundle\Entity\ElementSection $section
     */
    public function removeSection(\Novuscom\CMFBundle\Entity\ElementSection $section)
    {
        $this->section->removeElement($section);
    }

    /**
     * Get section
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set block
     *
     * @param \Novuscom\CMFBundle\Entity\Block $block
     * @return Element
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
     * @var integer
     */
    private $sort;


    /**
     * Set sort
     *
     * @param integer $sort
     * @return Element
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort
     *
     * @return integer
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ElementPropertyDT;


    /**
     * Add ElementPropertyDT
     *
     * @param \Novuscom\CMFBundle\Entity\ElementPropertyDT $elementPropertyDT
     * @return Element
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
    public function addProperty($property_code, $property){
        $this->properties[$property_code] = $property;
    }
    public function getProperties(){
        return $this->properties;
    }
    /**
     * @var boolean
     */
    private $active;


    /**
     * Set active
     *
     * @param boolean $active
     * @return Element
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var string
     */
    private $description;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return Element
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Element
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Element
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
     * @var string
     */
    private $header;


    /**
     * Set header
     *
     * @param string $header
     *
     * @return Element
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }
}
