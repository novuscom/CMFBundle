<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Novuscom\CMFBundle\Services\Utils;

/**
 * File
 */
class File
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
	 * @var integer
	 */
	private $size;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $property;


	public function __construct($file = false)
	{
		if ($file) {
			$this->file = $file;
			$this->getSize();
			$this->getName();
			$this->getType();
			$this->getName();
		}

		$this->property = new \Doctrine\Common\Collections\ArrayCollection();
	}

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
	 * @return File
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	private function getRandCode()
	{
		return md5(time());
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName()
	{
		if (empty($this->name) && !empty($this->getFile())) {
			$ext = $this->getFile()->guessExtension();
			if (!$ext)
				$ext = $this->getFile()->getClientOriginalExtension();
			$fileName = $this->getRandCode().'.'.$ext;
			$this->setName($fileName);
		}
		return $this->name;
	}

	/**
	 * Set size
	 *
	 * @param integer $size
	 * @return File
	 */
	public function setSize($size)
	{
		$this->size = $size;

		return $this;
	}

	/**
	 * Get size
	 *
	 * @return integer
	 */
	public function getSize()
	{
		if (empty($this->size) && !empty($this->getFile())) {
			$size = $this->getFile()->getClientSize();
			$this->setSize($size);
		}
		return $this->size;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 * @return File
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
	 * Set type
	 *
	 * @param string $type
	 * @return File
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType()
	{
		if (empty($this->type) && !empty($this->getFile())) {
			$this->setType($this->getFile()->getClientMimeType());
		}
		return $this->type;
	}

	/**
	 * Add property
	 *
	 * @param \Novuscom\CMFBundle\Entity\ElementPropertyF $property
	 * @return File
	 */
	public function addProperty(\Novuscom\CMFBundle\Entity\ElementPropertyF $property)
	{
		$this->property[] = $property;

		return $this;
	}

	/**
	 * Remove property
	 *
	 * @param \Novuscom\CMFBundle\Entity\ElementPropertyF $property
	 */
	public function removeProperty(\Novuscom\CMFBundle\Entity\ElementPropertyF $property)
	{
		$this->property->removeElement($property);
	}

	/**
	 * Get property
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getProperty()
	{
		return $this->property;
	}

	public function getImagePath()
	{
		return '/upload/images/' . $this->getName();
	}

	private $file;

	public function getFile(){
		return $this->file;
	}

}