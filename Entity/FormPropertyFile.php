<?php

namespace Novuscom\CMFBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File
 */
class FormPropertyFile
{




    /**
     * @var string
     */
    private $name;



    /**
     * @var string
     */
    private $description;








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
     * @var integer
     */
    private $replace_file_id;

    public function getReplaceFileId(){
        return $this->replace_file_id;
    }

    public function setReplaceFileId($replace_file_id){
        $this->replace_file_id = $replace_file_id;
    }

    /**
     * @var integer
     */
    private $file;

    public function getFile(){
        return $this->file;
    }

    public function setFile(\Symfony\Component\HttpFoundation\File\UploadedFile $file=null){
        $this->file = $file;
    }

}
