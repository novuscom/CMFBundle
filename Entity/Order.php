<?php

namespace Novuscom\Bundle\CMFBundle\Entity;

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
     * @var \Novuscom\Bundle\CMFBundle\Entity\StatusOrder
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
     * @param \Novuscom\Bundle\CMFBundle\Entity\StatusOrder $status
     *
     * @return Order
     */
    public function setStatus(\Novuscom\Bundle\CMFBundle\Entity\StatusOrder $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Novuscom\Bundle\CMFBundle\Entity\StatusOrder
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $product;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->product = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add product
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Product $product
     *
     * @return Order
     */
    public function addProduct(\Novuscom\Bundle\CMFBundle\Entity\Product $product)
    {
        $this->product[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \Novuscom\Bundle\CMFBundle\Entity\Product $product
     */
    public function removeProduct(\Novuscom\Bundle\CMFBundle\Entity\Product $product)
    {
        $this->product->removeElement($product);
    }

    /**
     * Get product
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProduct()
    {
        return $this->product;
    }
}
