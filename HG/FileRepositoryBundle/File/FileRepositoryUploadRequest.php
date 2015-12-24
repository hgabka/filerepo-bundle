<?php

namespace HG\FileRepositoryBundle\File;

use HG\FileRepositoryBundle\Model\FileRepositoryFileManagerInterface;
use HG\FileRepositoryBundle\Entity\HGFile;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileRepositoryUploadRequest implements FileRepositoryUploadRequestInterface
{
  protected $entity;
  protected $field;
  protected $uploadedFile;
  protected $delete;
  protected $fileManager;
  protected $type;
  protected $originalFile;
  protected $processed;
  protected $subDirectory;
  protected $fileClass;

  public function __construct(FileRepositoryFileManagerInterface $manager, $fileClass)
  {
    $this->fileManager = $manager;
    $this->processed = false;
    $this->fileClass = $fileClass;
  }

  public function setEntity($entity)
  {
    $this->entity = $entity;

    return $this;
  }

  public function getEntity()
  {
    return $this->entity;
  }

  public function hasValidEntity()
  {
    if (empty($this->entity))
    {
      return 'Hianyzik az entity';
    }

    if (empty($this->field))
    {
      return 'Hianyzik az mezonev';
    }

    $meta = $this->fileManager->getEntityManager()->getClassMetadata(get_class($this->entity));
    $mappings = $meta->getAssociationMappings();

    if (!isset($mappings[$this->field]) || $mappings[$this->field]['targetEntity'] !== $this->fileClass)
    {
      return sprintf('A %s class-nak nincs %s mezője', get_class($this->entity), $this->field);
    }

    return true;
  }

  public function setField($field)
  {
    $this->field = $field;

    return $this;
  }

  public function setUploadedFile(UploadedFile $uploadedFile = null)
  {
    $this->uploadedFile = $uploadedFile;

    return $this;
  }

  public function removeUploadedFile()
  {
    $this->uploadedFile = null;
  }

  public function getUploadedFile()
  {
    return $this->uploadedFile;
  }

  public function setDeleteRequest($delete)
  {
    $this->delete = (bool)$delete;

    return $this;
  }

  public function hasDeleteRequest()
  {
    return $this->delete;
  }

  public function hasUploadRequest()
  {
    return !is_null($this->uploadedFile);
  }

  public function getType()
  {
    return $this->type;
  }

  public function setType($type)
  {
    $this->type = $type;

    return $this;
  }

  public function setOriginalFile($file = null)
  {
    if (!is_null($file) && !$file instanceof $this->fileClass)
    {
      throw new InvalidArgumentException('Nem megfelelo file objektum');
    }

    $this->originalFile = $file;

    return $this;
  }

  public function getOriginalFile()
  {
    return $this->originalFile;
  }

  public function process($withFlush = false)
  {
    if ($this->processed)
    {
      return null;
    }

    $this->processed = true;

    if ($this->hasDeleteRequest() || $this->hasUploadRequest())
    {
      if (!is_null($this->originalFile))
      {
        $this->fileManager->remove($this->originalFile, $withFlush);
      }

      if (!$this->hasUploadRequest())
      {
        return null;
      }
    }

    if ($this->hasUploadRequest())
    {

      return $this->fileManager->getFromUploadedFile($this->uploadedFile, $this->type, $this->subDirectory, $withFlush);
    }

    return $this->getOriginalFile();
  }

  public function check()
  {
    if (($result = $this->hasValidEntity()) !== true)
    {
      throw new InvalidArgumentException($result);
    }

    return true;
  }

  protected function isProcessed()
  {
    return $this->processed;
  }

  public function getField()
  {
    return $this->field;
  }

  public function setSubDirectory($subDirectory)
  {
    $this->subDirectory = $subDirectory;

    return $this;
  }

  public function getSubDirectory()
  {
    return $this->subDirectory;
  }

  public function getOriginalFileId()
  {
    return is_object($this->originalFile) ? $this->originalFile->getFilId() : null;
  }
}
