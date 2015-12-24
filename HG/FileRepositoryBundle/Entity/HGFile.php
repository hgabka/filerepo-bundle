<?php

namespace HG\FileRepositoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HG\FileRepositoryBundle\File\RepositoryUploadedFileInterface;

/**
 * HGFile
 */
class HGFile
{
    /**
     * @var integer
     */
    private $fil_id;

    /**
     * @var string
     */
    private $fil_type;

    /**
     * @var string
     */
    private $fil_original_filename;

    /**
     * @var string
     */
    private $fil_filename;

    /**
     * @var string
     */
    private $fil_mime_type;

    /**
     * @var string
     */
    private $fil_subdirectory;

    /**
     * @var integer
     */
    private $fil_size;


    /**
     * Get fil_id
     *
     * @return integer 
     */
    public function getFilId()
    {
        return $this->fil_id;
    }

    /**
     * Set fil_type
     *
     * @param string $filType
     * @return HGFile
     */
    public function setFilType($filType)
    {
        $this->fil_type = $filType;
    
        return $this;
    }

    /**
     * Get fil_type
     *
     * @return string 
     */
    public function getFilType()
    {
        return $this->fil_type;
    }

    /**
     * Set fil_original_filename
     *
     * @param string $filOriginalFilename
     * @return HGFile
     */
    public function setFilOriginalFilename($filOriginalFilename)
    {
        $this->fil_original_filename = $filOriginalFilename;
    
        return $this;
    }

    /**
     * Get fil_original_filename
     *
     * @return string 
     */
    public function getFilOriginalFilename()
    {
        return $this->fil_original_filename;
    }

    /**
     * Set fil_filename
     *
     * @param string $filFilename
     * @return HGFile
     */
    public function setFilFilename($filFilename)
    {
        $this->fil_filename = $filFilename;
    
        return $this;
    }

    /**
     * Get fil_filename
     *
     * @return string 
     */
    public function getFilFilename()
    {
        return $this->fil_filename;
    }

    /**
     * Set fil_mime_type
     *
     * @param string $filMimeType
     * @return HGFile
     */
    public function setFilMimeType($filMimeType)
    {
        $this->fil_mime_type = $filMimeType;
    
        return $this;
    }

    /**
     * Get fil_mime_type
     *
     * @return string 
     */
    public function getFilMimeType()
    {
        return $this->fil_mime_type;
    }

    /**
     * Set fil_subdirectory
     *
     * @param string $filSubdirectory
     * @return HGFile
     */
    public function setFilSubdirectory($filSubdirectory)
    {
        $this->fil_subdirectory = $filSubdirectory;
    
        return $this;
    }

    /**
     * Get fil_subdirectory
     *
     * @return string 
     */
    public function getFilSubdirectory()
    {
        return $this->fil_subdirectory;
    }

    /**
     * Set fil_size
     *
     * @param integer $filSize
     * @return HGFile
     */
    public function setFilSize($filSize)
    {
        $this->fil_size = $filSize;
    
        return $this;
    }

    /**
     * Get fil_size
     *
     * @return integer 
     */
    public function getFilSize()
    {
        return $this->fil_size;
    }
    
}