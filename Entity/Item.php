<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Item
 */
class Item
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
	private $url;

	/**
	 * @var integer
	 */
	private $sort;

	/**
	 * @var \Novuscom\Bundle\CMFBundle\Entity\Menu
	 */
	private $menu;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $children;

	/**
	 * @var \Novuscom\Bundle\CMFBundle\Entity\Item
	 */
	private $parent;

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
	 * Constructor
	 */
	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @return Item
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
	 * Set url
	 *
	 * @param string $url
	 * @return Item
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Get url
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Set sort
	 *
	 * @param integer $sort
	 * @return Item
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
	 * Set menu
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Menu $menu
	 * @return Item
	 */
	public function setMenu(\Novuscom\Bundle\CMFBundle\Entity\Menu $menu = null)
	{
		$this->menu = $menu;

		return $this;
	}

	/**
	 * Get menu
	 *
	 * @return \Novuscom\Bundle\CMFBundle\Entity\Menu
	 */
	public function getMenu()
	{
		return $this->menu;
	}
	/**
	 * Set lft
	 *
	 * @param integer $lft
	 * @return Page
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
	 * @return Page
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
	 * @return Page
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

	/**
	 * Set root
	 *
	 * @param integer $root
	 * @return Page
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
	 * Set parent
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Item $parent
	 * @return Page
	 */
	public function setParent(\Novuscom\Bundle\CMFBundle\Entity\Item $parent = null)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Get parent
	 *
	 * @return \Novuscom\Bundle\CMFBundle\Entity\Item
	 */
	public function getParent()
	{
		return $this->parent;
	}
}