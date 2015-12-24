<?php

namespace HG\FileRepositoryBundle\File;

use HG\FileRepositoryBundle\File\FileRepositoryUploadRequestInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use HG\FileRepositoryBundle\Model\FileRepositoryFileManagerInterface;



/**
* A file_repository widget működését segítő osztály
* Uploadrequest-eket tárol, melyek entity-k HGFile relation-jeit kezelik attól függően, hogy a widget-en mit állít a felhasználó
* A request-ek feltöltést és törlést kezelnek
* Automatizálja azt a folyamatot, amit az action-ben kellene elvégezni
* (a feltöltött fájl repository-ba mentése és id hozzárendelés, fájl törlése a repository-ból és null hozzárendelése)
*/
class FileRepositoryUploadManager
{
  protected $requests = array();
  protected $requestTypes = array();

  public function addUploadRequest(FileRepositoryUploadRequestInterface $request)
  {
    $request->check();
    $field = $request->getField();
    $entityHash = spl_object_hash($request->getEntity());

    if (empty($this->requests[$entityHash]))
    {
      $this->requests[$entityHash] = array();
    }

    if (array_key_exists($field, $this->requests[$entityHash]))
    {
      throw new InvalidArgumentException(sprintf('A %s entity %s mezejehez mar letezik feltoltesi keres', $entityHash, $field));
    }

    $this->requests[$entityHash][$field] = $request;
  }

  public function removeUploadRequest($entity, $field)
  {
    if (isset($this->requests[spl_object_hash($entity)][$field]))
    {
      unset($this->requests[spl_object_hash($entity)][$field]);
    }
  }

  public function removeUploadRequestsForEntity($entity)
  {
    if (isset($this->requests[spl_object_hash($entity)]))
    {
      unset($this->requests[spl_object_hash($entity)]);
    }
  }


  public function createUploadRequest($requestType, $type, $subDir = null)
  {
    if (!$this->isTypeRegistered($requestType))
    {
      throw new InvalidArgumentException(sprintf('A "%s" tipus nincs regisztralva', $requestType));
    }

    $class = $this->requestTypes[$requestType]['request_class'];
    $manager = $this->requestTypes[$requestType]['manager'];

    $request = new $class($manager, $this->requestTypes[$requestType]['file_class']);

    $request->setType($type)
            ->setSubDirectory($subDir);

    return $request;
  }


  public function processAll()
  {
    foreach ($this->requests as $entityHash => $requests)
    {
      foreach ($requests as $field => $request)
      {
        $setter = 'set'.ucfirst($field);
        $entity = $request->getEntity();

        $entity->{$setter}($request->process());
      }
    }

    $this->removeAllRequests();
  }

  public function removeAllRequests()
  {
    $this->requests = array();
  }

  public function processRequestsForEntity($entity)
  {
    if (isset($this->requests[spl_object_hash($entity)]))
    {
      foreach ($this->requests[spl_object_hash($entity)] as $field => $request)
      {
        $this->processRequest($entity, $field);
      }
    }
  }

  public function processRequest($entity, $field)
  {
    if (isset($this->requests[spl_object_hash($entity)][$field]))
    {
      $setter = 'set'.ucfirst($field);
      $entity->{$setter}($this->requests[spl_object_hash($entity)][$field]->process());

      unset($this->requests[spl_object_hash($entity)][$field]);
    }
  }

  public function getEntities()
  {
    $entities = array();
    foreach ($this->requests as $hash => $requests)
    {
      foreach ($requests as $field => $request)
      {
        $entities[$hash] = $request->getEntity();
      }
    }

    return $entities;
  }

  public function registerRequestType($name, $manager, $uploadRequestClass = 'HG\FileRepositoryBundle\File\FileRepositoryUploadRequest', $fileClass = 'HG\FileRepositoryBundle\Entity\HGFile' )
  {
    if (!$manager instanceof FileRepositoryFileManagerInterface)
    {
      throw new InvalidArgumentException('A manager nem FileRepositoryFileManagerInterface-ből származik');
    }

    if (!class_exists($uploadRequestClass))
    {
      throw new InvalidArgumentException(sprintf('A request class("%s") nem létezik', $uploadRequestClass));
    }

    if (!class_exists($fileClass))
    {
      throw new InvalidArgumentException(sprintf('A file class("%s") nem létezik', $fileClass));
    }

    $this->requestTypes[$name] = array(
      'manager'       => $manager,
      'request_class' => $uploadRequestClass,
      'file_class'    => $fileClass,
    );
  }

  public function isTypeRegistered($name)
  {
    return isset($this->requestTypes[$name]);
  }


}

