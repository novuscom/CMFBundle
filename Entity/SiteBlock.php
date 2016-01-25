<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

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
     * @var \Novuscom\Bundle\CMFBundle\Entity\Site
     */
    private $site;

    /**
     * @var \Novuscom\Bundle\CMFBundle\Entity\Block
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
     * @param \Novuscom\Bundle\CMFBundle\Entity\Site $site
     * @return SiteBlock
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
     * Set block
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Block $block
     * @return SiteBlock
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
}