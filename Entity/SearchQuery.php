<?php

namespace Novuscom\CMFBundle\Entity;

/**
 * SearchQuery
 */
class SearchQuery
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $query;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var integer
     */
    private $results;

    /**
     * @var \DateTime
     */
    private $time;


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
     * Set query
     *
     * @param string $query
     *
     * @return SearchQuery
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return SearchQuery
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set results
     *
     * @param integer $results
     *
     * @return SearchQuery
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Get results
     *
     * @return integer
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     *
     * @return SearchQuery
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }
}

