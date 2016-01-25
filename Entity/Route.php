<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Route
 */
class Route
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
    private $template;

    /**
     * @var string
     */
    private $block;

    /**
     * @var integer
     */
    private $blockId;

    /**
     * @var integer
     */
    private $pageId;

    /**
     * @var string
     */
    private $controller;


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
     * @return Route
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
     * @return Route
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
     * Set template
     *
     * @param string $template
     * @return Route
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return string 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set block
     *
     * @param string $block
     * @return Route
     */
    public function setBlock($block)
    {
        $this->block = $block;
    
        return $this;
    }

    /**
     * Get block
     *
     * @return string 
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set blockId
     *
     * @param integer $blockId
     * @return Route
     */
    public function setBlockId($blockId)
    {
        $this->blockId = $blockId;
    
        return $this;
    }

    /**
     * Get blockId
     *
     * @return integer 
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * Set pageId
     *
     * @param integer $pageId
     * @return Route
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    
        return $this;
    }

    /**
     * Get pageId
     *
     * @return integer 
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return Route
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    
        return $this;
    }

    /**
     * Get controller
     *
     * @return string 
     */
    public function getController()
    {
        return $this->controller;
    }
    /**
     * @var \Novuscom\Bundle\CMFBundle\Entity\Page
     */
    private $page;


    /**
     * Set page
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Page $page
     * @return Route
     */
    public function setPage(\Novuscom\Bundle\CMFBundle\Entity\Page $page = null)
    {
        $this->page = $page;
    
        return $this;
    }

    /**
     * Get page
     *
     * @return \Novuscom\Bundle\CMFBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
        
    /**
     * @var \Novuscom\Bundle\CMFBundle\Entity\Site
     */
    private $site;


    /**
     * Set site
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Site $site
     * @return Route
     */
    public function setSite(\Novuscom\Bundle\CMFBundle\Entity\Site $site = null)
    {
        $this->site = $site;
    
        return $this;
    }

    /**
     * Get site
     *
     * @return \Novuscom\Bundle\CMFBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }
    /**
     * @var array
     */
    private $params;


    /**
     * Set params
     *
     * @param array $params
     * @return Route
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
    /**
     * @var boolean
     */
    private $active;


    /**
     * Set active
     *
     * @param boolean $active
     * @return Route
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
     * @var integer
     */
    private $sort;


    /**
     * Set sort
     *
     * @param integer $sort
     * @return Route
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
}