<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Section
 */
class Section
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
	 * @return Section
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
	 * @return Section
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


	private $fullCode;

	/**
	 * Set code
	 *
	 * @param string $code
	 * @return Section
	 */
	public function setFullCode($code)
	{
		$this->fullCode = $code;

		return $this;
	}

	/**
	 * Get code
	 *
	 * @return string
	 */
	public function getFullCode()
	{
		return $this->fullCode;
	}

	/**
	 * @var integer
	 */
	private $lft;

	/**
	 * @var integer
	 */
	private $rgt;

	/**
	 * @var integer
	 */
	private $lvl;

	/**
	 * @var integer
	 */
	private $root;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $children;

	/**
	 * @var \Novuscom\Bundle\CMFBundle\Entity\Section
	 */
	private $parent;

	/**
	 * @var \Novuscom\Bundle\CMFBundle\Entity\Block
	 */
	private $block;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Set lft
	 *
	 * @param integer $lft
	 * @return Section
	 */
	public function setLft($lft)
	{
		$this->lft = $lft;

		return $this;
	}

	/**
	 * Get lft
	 *
	 * @return integer
	 */
	public function getLft()
	{
		return $this->lft;
	}

	/**
	 * Set rgt
	 *
	 * @param integer $rgt
	 * @return Section
	 */
	public function setRgt($rgt)
	{
		$this->rgt = $rgt;

		return $this;
	}

	/**
	 * Get rgt
	 *
	 * @return integer
	 */
	public function getRgt()
	{
		return $this->rgt;
	}

	/**
	 * Set lvl
	 *
	 * @param integer $lvl
	 * @return Section
	 */
	public function setLvl($lvl)
	{
		$this->lvl = $lvl;

		return $this;
	}

	/**
	 * Get lvl
	 *
	 * @return integer
	 */
	public function getLvl()
	{
		return $this->lvl;
	}

	public function getLevel(){
		return ($this->lvl+1);
	}

	/**
	 * Set root
	 *
	 * @param integer $root
	 * @return Section
	 */
	public function setRoot($root)
	{
		$this->root = $root;

		return $this;
	}

	/**
	 * Get root
	 *
	 * @return integer
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Add children
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $children
	 * @return Section
	 */
	public function addChildren(\Novuscom\Bundle\CMFBundle\Entity\Section $children)
	{
		$this->children[] = $children;

		return $this;
	}

	/**
	 * Remove children
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $children
	 */
	public function removeChildren(\Novuscom\Bundle\CMFBundle\Entity\Section $children)
	{
		$this->children->removeElement($children);
	}

	/**
	 * Get children
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Set parent
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $parent
	 * @return Section
	 */
	public function setParent(\Novuscom\Bundle\CMFBundle\Entity\Section $parent = null)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Get parent
	 *
	 * @return \Novuscom\Bundle\CMFBundle\Entity\Section
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Set block
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Block $block
	 * @return Section
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


	private $blockId;

	public function setBlockId($id)
	{
		$this->blockId = $id;
	}

	public function getBlockId()
	{
		if ($this->blockId)
			return $this->blockId;
		if ($this->getBlock())
			return $this->getBlock()->getId();
		return null;
	}

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $element;


	/**
	 * Add element
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Element $element
	 * @return Section
	 */
	public function addElement(\Novuscom\Bundle\CMFBundle\Entity\Element $element)
	{
		$this->element[] = $element;

		return $this;
	}

	/**
	 * Remove element
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Element $element
	 */
	public function removeElement(\Novuscom\Bundle\CMFBundle\Entity\Element $element)
	{
		$this->element->removeElement($element);
	}

	/**
	 * Get element
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getElement()
	{
		return $this->element;
	}


	/**
	 * @var integer
	 */
	private $parentId;


	/**
	 * Set parentId
	 *
	 * @param integer $parentId
	 * @return Section
	 */
	public function setParentId($parentId)
	{
		$this->parentId = $parentId;

		return $this;
	}

	/**
	 * Get parentId
	 *
	 * @return integer
	 */
	public function getParentId()
	{
		if ($this->parentId)
			return $this->parentId;
		if ($this->getParent())
			return $this->getParent()->getId();
		return null;
	}

	private $SectionElements;

	public function getElements()
	{
		//echo '<pre>' . print_r('getElements()', true) . '</pre>';
		return $this->SectionElements;
	}

	public function addSectionElement(\Novuscom\Bundle\CMFBundle\Entity\Element $element)
	{
		$this->SectionElements[] = $element;
		return $this;
	}

	/**
	 * @var integer
	 */
	private $sort;


	/**
	 * Set sort
	 *
	 * @param integer $sort
	 * @return Section
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

	private $codePath;


	public function setCodePath($codePath)
	{
		$this->codePath = $codePath;

		return $this;
	}

	public function getCodePath()
	{
		return $this->codePath;
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
	 * @var string
	 */
	private $preview_text;

	/**
	 * @var string
	 */
	private $detail_text;


	/**
	 * Set title
	 *
	 * @param string $title
	 * @return Section
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
	 * @return Section
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
	 * @return Section
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
	 * Set preview_text
	 *
	 * @param string $previewText
	 * @return Section
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
	 * @return Section
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
	 * @var \Novuscom\Bundle\CMFBundle\Entity\File
	 */
	private $PreviewPicture;

	/**
	 * @var \Novuscom\Bundle\CMFBundle\Entity\File
	 */
	private $DetailPicture;


	/**
	 * Set previewPicture
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\File $previewPicture
	 *
	 * @return Section
	 */
	public function setPreviewPicture(\Novuscom\Bundle\CMFBundle\Entity\File $previewPicture = null)
	{
		$this->PreviewPicture = $previewPicture;

		return $this;
	}

	/**
	 * Get previewPicture
	 *
	 * @return \Novuscom\Bundle\CMFBundle\Entity\File
	 */
	public function getPreviewPicture()
	{
		return $this->PreviewPicture;
	}

	/**
	 * Set detailPicture
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\File $detailPicture
	 *
	 * @return Section
	 */
	public function setDetailPicture(\Novuscom\Bundle\CMFBundle\Entity\File $detailPicture = null)
	{
		$this->DetailPicture = $detailPicture;

		return $this;
	}

	/**
	 * Get detailPicture
	 *
	 * @return \Novuscom\Bundle\CMFBundle\Entity\File
	 */
	public function getDetailPicture()
	{
		return $this->DetailPicture;
	}

	/**
	 * Add child
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $child
	 *
	 * @return Section
	 */
	public function addChild(\Novuscom\Bundle\CMFBundle\Entity\Section $child)
	{
		$this->children[] = $child;

		return $this;
	}

	/**
	 * Remove child
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $child
	 */
	public function removeChild(\Novuscom\Bundle\CMFBundle\Entity\Section $child)
	{
		$this->children->removeElement($child);
	}

	public function getIndentedTitle()
	{
		return str_repeat("—", $this->lvl) . ' ' . $this->name;
	}
}
