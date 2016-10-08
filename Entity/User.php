<?php
namespace Novuscom\CMFBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @var string
     */
    private $phone;



    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $surname;

    /**
     * @var string
     */
    private $second_name;

    /**
     * @var string
     */
    private $post;

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * Set surname
     *
     * @param string $surname
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    
        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set second_name
     *
     * @param string $secondName
     * @return User
     */
    public function setSecondName($secondName)
    {
        $this->second_name = $secondName;
    
        return $this;
    }

    /**
     * Get second_name
     *
     * @return string 
     */
    public function getSecondName()
    {
        return $this->second_name;
    }

    /**
     * Set post
     *
     * @param string $post
     * @return User
     */
    public function setPost($post)
    {
        $this->post = $post;
    
        return $this;
    }

    /**
     * Get post
     *
     * @return string 
     */
    public function getPost()
    {
        return $this->post;
    }















    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sites;








    /**
     * Add sites
     *
     * @param \Novuscom\CMFBundle\Entity\Site $sites
     * @return User
     */
    public function addSite(\Novuscom\CMFBundle\Entity\Site $sites)
    {
        $this->sites[] = $sites;
    
        return $this;
    }

    /**
     * Remove sites
     *
     * @param \Novuscom\CMFBundle\Entity\Site $sites
     */
    public function removeSite(\Novuscom\CMFBundle\Entity\Site $sites)
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

    public function getSitesId()
    {
        $result = array();
        foreach ($this->getSites() as $entity) {
            $result[] = $entity->getId();
        }
        return $result;
    }
}