<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Site
 */
class Site
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
    private $robots_txt;

    /**
     * @var boolean
     */
    private $closed;

    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $emails;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $aliases;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $routes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $menu;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $pages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $blocks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aliases = new \Doctrine\Common\Collections\ArrayCollection();
        $this->routes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->menu = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
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
     *
     * @return Site
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
     * Set robotsTxt
     *
     * @param string $robotsTxt
     *
     * @return Site
     */
    public function setRobotsTxt($robotsTxt)
    {
        $this->robots_txt = $robotsTxt;

        return $this;
    }

    /**
     * Get robotsTxt
     *
     * @return string
     */
    public function getRobotsTxt()
    {
        return $this->robots_txt;
    }

    /**
     * Set closed
     *
     * @param boolean $closed
     *
     * @return Site
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed
     *
     * @return boolean
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Site
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
     * Set emails
     *
     * @param array $emails
     *
     * @return Site
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Get emails
     *
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add alias
     *
     * @param array $alias
     *
     * @return Site
     */
    public function addAlias($alias)
    {
	    $alias->setSite($this);
	    $this->aliases->add($alias);
        return $this;
    }

    /**
     * Remove alias
     *
     * @param \Novuscom\CMFBundle\Entity\Alias $alias
     */
    public function removeAlias(\Novuscom\CMFBundle\Entity\Alias $alias)
    {
        $this->aliases->removeElement($alias);
    }

    /**
     * Get aliases
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Add route
     *
     * @param \Novuscom\CMFBundle\Entity\Route $route
     *
     * @return Site
     */
    public function addRoute(\Novuscom\CMFBundle\Entity\Route $route)
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * Remove route
     *
     * @param \Novuscom\CMFBundle\Entity\Route $route
     */
    public function removeRoute(\Novuscom\CMFBundle\Entity\Route $route)
    {
        $this->routes->removeElement($route);
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
     * Add menu
     *
     * @param \Novuscom\CMFBundle\Entity\Menu $menu
     *
     * @return Site
     */
    public function addMenu(\Novuscom\CMFBundle\Entity\Menu $menu)
    {
        $this->menu[] = $menu;

        return $this;
    }

    /**
     * Remove menu
     *
     * @param \Novuscom\CMFBundle\Entity\Menu $menu
     */
    public function removeMenu(\Novuscom\CMFBundle\Entity\Menu $menu)
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
     * Add page
     *
     * @param \Novuscom\CMFBundle\Entity\Page $page
     *
     * @return Site
     */
    public function addPage(\Novuscom\CMFBundle\Entity\Page $page)
    {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * Remove page
     *
     * @param \Novuscom\CMFBundle\Entity\Page $page
     */
    public function removePage(\Novuscom\CMFBundle\Entity\Page $page)
    {
        $this->pages->removeElement($page);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Add block
     *
     * @param \Novuscom\CMFBundle\Entity\Block $block
     *
     * @return Site
     */
    public function addBlock(\Novuscom\CMFBundle\Entity\Block $block)
    {
        $this->blocks[] = $block;

        return $this;
    }

    /**
     * Remove block
     *
     * @param \Novuscom\CMFBundle\Entity\Block $block
     */
    public function removeBlock(\Novuscom\CMFBundle\Entity\Block $block)
    {
        $this->blocks->removeElement($block);
    }

    /**
     * Get blocks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}

