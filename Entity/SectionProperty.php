<?php

namespace Novuscom\CMFBundle\Entity;

/**
 * SectionProperty
 */
class SectionProperty
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \Novuscom\CMFBundle\Entity\Element
     */
    private $element;

	/**
	 * @var \Novuscom\CMFBundle\Entity\Property
	 */
	private $property;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return SectionProperty
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return SectionProperty
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

	/**
	 * Set element
	 *
	 * @param \Novuscom\CMFBundle\Entity\Element $element
	 * @return ElementProperty
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
	 * Set property
	 *
	 * @param \Novuscom\CMFBundle\Entity\Property $property
	 * @return ElementProperty
	 */
	public function setProperty(\Novuscom\CMFBundle\Entity\Property $property = null)
	{
		$this->property = $property;

		return $this;
	}

	/**
	 * Get property
	 *
	 * @return \Novuscom\CMFBundle\Entity\Property
	 */
	public function getProperty()
	{
		return $this->property;
	}
	/**
	 * @var \Novuscom\CMFBundle\Entity\Section
	 */
	private $section;

	/**
	 * Set element
	 *
	 * @param \Novuscom\CMFBundle\Entity\Section $section
	 * @return ElementProperty
	 */
	public function setSection(\Novuscom\CMFBundle\Entity\Section $section = null)
	{
		$this->section = $section;

		return $this;
	}

	/**
	 * Get element
	 *
	 * @return \Novuscom\CMFBundle\Entity\Section
	 */
	public function getSection()
	{
		return $this->section;
	}
}
