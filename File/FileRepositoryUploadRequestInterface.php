<?php

namespace HG\FileRepositoryBundle\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;


interface FileRepositoryUploadRequestInterface
{
  public function process($withFlush = false);

  public function setEntity($entity);

  public function getEntity();
  
  public function hasValidEntity();

  public function setUploadedFile(UploadedFile $uploadedFile = null);

  public function removeUploadedFile();

  public function getUploadedFile();

  public function setField($field);

  public function getField();

  public function setSubDirectory($subDirectory);
  
  public function getSubDirectory();
  
  public function getType();

  public function setType($type);
  
  public function setDeleteRequest($delete);

  public function hasUploadRequest();

  public function hasDeleteRequest();

  public function getOriginalFile();
  
  public function getOriginalFileId();

  public function setOriginalFile($file = null);

  public function check();
}
