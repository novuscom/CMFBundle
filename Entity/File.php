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
			$this->getOriginalName();
		}
		$this->setTime(new \DateTime());
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
		return md5(microtime());
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
			$fileName = $this->getRandCode() . '.' . $ext;
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
		$res = null;
		if ($this->getName())
			$res = '/upload/etc/' . $this->getName();
		return $res;
	}

	public function getPath()
	{
		$res = null;
		if ($this->getName())
			$res = '/upload/etc/' . $this->getName();
		return $res;
	}

	private $file;

	public function getFile()
	{
		return $this->file;
	}

	private $time;

	/**
	 * @return mixed
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * @param mixed $time
	 */
	public function setTime(\DateTime $time)
	{
		$this->time = $time;
	}

	private $original_name;

	/**
	 * @return mixed
	 */
	public function getOriginalName()
	{
		if (empty($this->original_name) && !empty($this->getFile())) {
			$originalName = $this->getFile()->getClientOriginalName();
			$this->setOriginalName($originalName);
		}
		return $this->original_name;
	}

	/**
	 * @param mixed $originalName
	 */
	public function setOriginalName($originalName)
	{
		$this->original_name = $originalName;
	}

}