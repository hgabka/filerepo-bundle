<?php
  
namespace HG\FileRepositoryBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileRepositoryFileManagerInterface
{
  public function getFileObject($id);
  
  public function remove($file);
  
  public function getFromUploadedFile(UploadedFile $resource, $type, $subDir = null, $withFlush = false);
}
