<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * SiteBlock
 */
class SiteBlock
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Novuscom\CMFBundle\Entity\Site
     */
    private $site;

    /**
     * @var \Novuscom\CMFBundle\Entity\Block
     */
    private $block;


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
     * Set site
     *
     * @param \Novuscom\CMFBundle\Entity\Site $site
     * @return SiteBlock
     */
    public function setSite(\Novuscom\CMFBundle\Entity\Site $site = null)
    {
        $this->site = $site;
    
        return $this;
    }

    /**
     * Get site
     *
     * @return \Novuscom\CMFBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set block
     *
     * @param \Novuscom\CMFBundle\Entity\Block $block
     * @return SiteBlock
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
}