<?php

namespace HG\FileRepositoryBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use HG\FileRepositoryBundle\File\FileRepositoryUploadManager as UploadManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class FileRepositoryModelTransformer implements DataTransformerInterface
{
    private $manager;
    private $type;
    private $uploadRequestType;
    private $subDirectory;

    /**
     * @param ObjectManager $om
     */
    public function __construct(UploadManager $manager, $type, $subDirectory, $uploadRequestType)
    {
      $this->manager = $manager;
      $this->type = $type;
      $this->uploadRequestType = $uploadRequestType;
      $this->subDirectory = $subDirectory;
    }

    /**
     * A widget-hez tartozó HGFile objektumból csinál egy .FileRepositoryUploadRequestInterface objektumot
     *
     * @param  HGFile|null $data
     *
     * @return FileRepositoryUploadRequestInterface
     */
    public function transform($data)
    {
       $request = $this->manager->createUploadRequest($this->uploadRequestType, $this->type, $this->subDirectory);
       $request->setOriginalFile($data);

       return $request;
    }

    /**
     * NormData -> Modeldata. Ha nem objektumhoz kötött a widget, akkor egy
     * FileRepositoryUploadRequestInterface objektumot ad (lehet rá hívni a process()-t a feltöltéshez, ami visszaadja az új filet
     * Egyébként null-t ad, ha volt feltöltés, vagy törlés, az eredeti fájlt, ha egyik sem volt
     * ( a mentést majd a manager végzi, az fog értéket adni)
     *
     * @param  string FileRepositoryUploadRequestInterface
     *
     * @return FileRepositoryUploadRequestInterface|HGFile|null
     *
     */
    public function reverseTransform($uploadRequest)
    {
      if (true !== $uploadRequest->hasValidEntity())
      {
        return $uploadRequest;
      }

      if ($uploadRequest->hasDeleteRequest() || $uploadRequest->hasUploadRequest())
      {
          return null;
      }

      return $uploadRequest->getOriginalFile();
    }
}
