<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Novuscom\Bundle\CMFBundle\Entity\SiteBlock;
use Doctrine\ORM\Mapping as ORM;

/**
 * Block
 */
class Block
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
	 * @var integer
	 */
	private $group_id;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $SiteBlock;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $property;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $element;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $section;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $routes;

	/**
	 * @var \Novuscom\Bundle\CMFBundle\Entity\BlockGroup
	 */
	private $group;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->SiteBlock = new \Doctrine\Common\Collections\ArrayCollection();
		$this->property = new \Doctrine\Common\Collections\ArrayCollection();
		$this->element = new \Doctrine\Common\Collections\ArrayCollection();
		$this->section = new \Doctrine\Common\Collections\ArrayCollection();
		$this->routes = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @return Block
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
	 * @return Block
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
	 * Set group_id
	 *
	 * @param integer $groupId
	 * @return Block
	 */
	public function setGroupId($groupId)
	{
		$this->group_id = $groupId;

		return $this;
	}

	/**
	 * Get group_id
	 *
	 * @return integer
	 */
	public function getGroupId()
	{
		return $this->group_id;
	}

	/**
	 * Add SiteBlock
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\SiteBlock $siteBlock
	 * @return Block
	 */
	public function addSiteBlock(\Novuscom\Bundle\CMFBundle\Entity\SiteBlock $siteBlock)
	{
		$this->SiteBlock[] = $siteBlock;

		return $this;
	}

	/**
	 * Remove SiteBlock
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\SiteBlock $siteBlock
	 */
	public function removeSiteBlock(\Novuscom\Bundle\CMFBundle\Entity\SiteBlock $siteBlock)
	{
		$this->SiteBlock->removeElement($siteBlock);
	}

	/**
	 * Get SiteBlock
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSiteBlock()
	{
		return $this->SiteBlock;
	}

	/**
	 * Add property
	 *
	 * @param $property
	 * @return Block
	 */
	public function addProperty($property)
	{
		$property->setBlock($this);
		$this->property->add($property);
		return $this;
	}

	/**
	 * Remove property
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Property $property
	 */
	public function removeProperty(\Novuscom\Bundle\CMFBundle\Entity\Property $property)
	{
		$this->property->removeElement($property);
	}

	/**
	 * Get property
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Add element
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Element $element
	 * @return Block
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
	 * Add section
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $section
	 * @return Block
	 */
	public function addSection(\Novuscom\Bundle\CMFBundle\Entity\Section $section)
	{
		$this->section[] = $section;

		return $this;
	}

	/**
	 * Remove section
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Section $section
	 */
	public function removeSection(\Novuscom\Bundle\CMFBundle\Entity\Section $section)
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
	 * Add routes
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Route $routes
	 * @return Block
	 */
	public function addRoute(\Novuscom\Bundle\CMFBundle\Entity\Route $routes)
	{
		$this->routes[] = $routes;

		return $this;
	}

	/**
	 * Remove routes
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Route $routes
	 */
	public function removeRoute(\Novuscom\Bundle\CMFBundle\Entity\Route $routes)
	{
		$this->routes->removeElement($routes);
	}

	/**
	 * Get routes
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Set group
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\BlockGroup $group
	 * @return Block
	 */
	public function setGroup(\Novuscom\Bundle\CMFBundle\Entity\BlockGroup $group = null)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Get group
	 *
	 * @return \Novuscom\Bundle\CMFBundle\Entity\BlockGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $menu;


	/**
	 * Add menu
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Menu $menu
	 * @return Block
	 */
	public function addMenu(\Novuscom\Bundle\CMFBundle\Entity\Menu $menu)
	{
		$this->menu[] = $menu;

		return $this;
	}

	/**
	 * Remove menu
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Menu $menu
	 */
	public function removeMenu(\Novuscom\Bundle\CMFBundle\Entity\Menu $menu)
	{
		$this->menu->removeElement($menu);
	}

	/**
	 * Get menu
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getMenu()
	{
		return $this->menu;
	}

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $sites;


	/**
	 * Add sites
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Site $sites
	 * @return Block
	 */
	public function addSite(\Novuscom\Bundle\CMFBundle\Entity\Site $sites)
	{
		$this->sites[] = $sites;

		return $this;
	}

	/**
	 * Remove sites
	 *
	 * @param \Novuscom\Bundle\CMFBundle\Entity\Site $sites
	 */
	public function removeSite(\Novuscom\Bundle\CMFBundle\Entity\Site $sites)
	{
		$this->sites->removeElement($sites);
	}

	/**
	 * Get sites
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSites()
	{
		return $this->sites;
	}

	/**
	 * @var array
	 */
	private $params;

	/**
	 * Set params
	 *
	 * @param array $params
	 *
	 * @return Test
	 */
	public function setParams($params)
	{
		$this->params = $params;

		return $this;
	}

	/**
	 * Get params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	private $paramsArray = array();

	public function setParamsArray()
	{
		$array = json_decode($this->params, true);
		$this->paramsArray = $array;
		return $array;
	}

	public function getParamsArray()
	{
		return $this->paramsArray;
	}

	public function getParam($key)
	{
		if (!$this->paramsArray)
			$this->setParamsArray();
		if (array_key_exists($key, $this->paramsArray))
			return $this->paramsArray[$key];
		return null;
	}
}