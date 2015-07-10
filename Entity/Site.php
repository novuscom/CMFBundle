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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $aliases;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $pages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $SiteBlock;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aliases = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->SiteBlock = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add aliases
     *
     * @param \Novuscom\CMFBundle\Entity\Alias $aliases
     * @return Site
     */
    public function addAlias(\Novuscom\CMFBundle\Entity\Alias $aliases)
    {
        $aliases->setSite($this);
        $this->aliases[] = $aliases;
    
        return $this;
    }

    /**
     * Remove aliases
     *
     * @param \Novuscom\CMFBundle\Entity\Alias $aliases
     */
    public function removeAlias(\Novuscom\CMFBundle\Entity\Alias $aliases)
    {
        $this->aliases->removeElement($aliases);
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
     * Add pages
     *
     * @param \Novuscom\CMFBundle\Entity\Page $pages
     * @return Site
     */
    public function addPage(\Novuscom\CMFBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;
    
        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Novuscom\CMFBundle\Entity\Page $pages
     */
    public function removePage(\Novuscom\CMFBundle\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
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
     * Add SiteBlock
     *
     * @param \Novuscom\CMFBundle\Entity\SiteBlock $siteBlock
     * @return Site
     */
    public function addSiteBlock(\Novuscom\CMFBundle\Entity\SiteBlock $siteBlock)
    {
        $this->SiteBlock[] = $siteBlock;
    
        return $this;
    }

    /**
     * Remove SiteBlock
     *
     * @param \Novuscom\CMFBundle\Entity\SiteBlock $siteBlock
     */
    public function removeSiteBlock(\Novuscom\CMFBundle\Entity\SiteBlock $siteBlock)
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
     * @var string
     */
    private $robots_txt;


    /**
     * Set robots_txt
     *
     * @param string $robotsTxt
     * @return Site
     */
    public function setRobotsTxt($robotsTxt)
    {
        $this->robots_txt = $robotsTxt;
    
        return $this;
    }

    /**
     * Get robots_txt
     *
     * @return string 
     */
    public function getRobotsTxt()
    {
        return $this->robots_txt;
    }

    /**
     * Add aliases
     *
     * @param \Novuscom\CMFBundle\Entity\Alias $aliases
     * @return Site
     */
    public function addAliase(\Novuscom\CMFBundle\Entity\Alias $aliases)
    {
        $this->aliases[] = $aliases;
    
        return $this;
    }

    /**
     * Remove aliases
     *
     * @param \Novuscom\CMFBundle\Entity\Alias $aliases
     */
    public function removeAliase(\Novuscom\CMFBundle\Entity\Alias $aliases)
    {
        $this->aliases->removeElement($aliases);
    }
    /**
     * @var boolean
     */
    private $closed;


    /**
     * Set closed
     *
     * @param boolean $closed
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $menu;


    /**
     * Add menu
     *
     * @param \Novuscom\CMFBundle\Entity\Menu $menu
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $routes;


    /**
     * Add routes
     *
     * @param \Novuscom\CMFBundle\Entity\Route $routes
     * @return Site
     */
    public function addRoute(\Novuscom\CMFBundle\Entity\Route $routes)
    {
        $this->routes[] = $routes;
    
        return $this;
    }

    /**
     * Remove routes
     *
     * @param \Novuscom\CMFBundle\Entity\Route $routes
     */
    public function removeRoute(\Novuscom\CMFBundle\Entity\Route $routes)
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;


    /**
     * Add groups
     *
     * @param \Novuscom\CMFUserBundle\Entity\Group $groups
     * @return Site
     */
    public function addGroup(\Novuscom\CMFUserBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;
    
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Novuscom\CMFUserBundle\Entity\Group $groups
     */
    public function removeGroup(\Novuscom\CMFUserBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }
    /**
     * @var string
     */
    private $code;


    /**
     * Set code
     *
     * @param string $code
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $blocks;


    /**
     * Add blocks
     *
     * @param \Novuscom\CMFBundle\Entity\Block $blocks
     * @return Site
     */
    public function addBlock(\Novuscom\CMFBundle\Entity\Block $blocks)
    {
        $this->blocks[] = $blocks;
    
        return $this;
    }

    /**
     * Remove blocks
     *
     * @param \Novuscom\CMFBundle\Entity\Block $blocks
     */
    public function removeBlock(\Novuscom\CMFBundle\Entity\Block $blocks)
    {
        $this->blocks->removeElement($blocks);
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