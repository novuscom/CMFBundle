<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Alias
 */
class Alias
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
     * @var \Novuscom\Bundle\CMFBundle\Entity\Site
     */
    private $site;


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
     * @return Alias
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
     * Set site
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Site $site
     * @return Alias
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
}