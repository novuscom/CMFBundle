<?php

namespace Novuscom\CMFBundle\Entity;

/**
 * OrderEntity
 */
class Order
{
    /**
     * @var integer
     */
    private $id;


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
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Novuscom\CMFUserBundle\Entity\User
     */
    private $user;

    /**
     * @var \Novuscom\CMFBundle\Entity\StatusOrder
     */
    private $status;


    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Order
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Order
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Order
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
     * Set name
     *
     * @param string $name
     *
     * @return Order
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
     * Set user
     *
     * @param \Novuscom\CMFUserBundle\Entity\User $user
     *
     * @return Order
     */
    public function setUser(\Novuscom\CMFUserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Novuscom\CMFUserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set status
     *
     * @param \Novuscom\CMFBundle\Entity\StatusOrder $status
     *
     * @return Order
     */
    public function setStatus(\Novuscom\CMFBundle\Entity\StatusOrder $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Novuscom\CMFBundle\Entity\StatusOrder
     */
    public function getStatus()
    {
        return $this->status;
    }
}
