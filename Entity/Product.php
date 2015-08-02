<?php

namespace Novuscom\CMFBundle\Entity;

/**
 * Product
 */
class Product
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
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $url;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $route;

    /**
     * @var float
     */
    private $weight;


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
     * @return Product
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
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     *
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Product
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Product
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
     * Set route
     *
     * @param string $route
     *
     * @return Product
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set weight
     *
     * @param float $weight
     *
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }
    /**
     * @var \Novuscom\CMFBundle\Entity\Element
     */
    private $element;

    /**
     * @var \Novuscom\CMFBundle\Entity\Cart
     */
    private $cart;


    /**
     * Set element
     *
     * @param \Novuscom\CMFBundle\Entity\Element $element
     *
     * @return Product
     */
    public function setElement(\Novuscom\CMFBundle\Entity\Element $element = null)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Get element
     *
     * @return \Novuscom\CMFBundle\Entity\Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set cart
     *
     * @param \Novuscom\CMFBundle\Entity\Cart $cart
     *
     * @return Product
     */
    public function setCart(\Novuscom\CMFBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \Novuscom\CMFBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }
}
