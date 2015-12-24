<?php

namespace HG\FileRepositoryBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use HG\FileRepositoryBundle\File\FileRepositoryUploadManager as UploadManager;
use HG\FileRepositoryBundle\File\FileRepositoryUploadRequestInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class FileRepositoryViewTransformer implements DataTransformerInterface
{
    /**
     * @var FileRepositoryUploadManager
     */
    private $manager;
    private $type;
    private $uploadRequestType;
    private $subDirectory;

    /**
     * Konstruktor
     *
     * @param UploadManager $manager - az uploadmanager
     * @param string type - a Filemanager típusa (ld. ott)
     * @param string|null $subDirectory - a FileManager subdirectory
     * @param string $uploadRequestClass - milyen uploadrequest osztályt példányosítson
     *
     */
    public function __construct(UploadManager $manager, $type, $subDirectory, $uploadRequestType)
    {
      $this->manager = $manager;
      $this->type = $type;
      $this->uploadRequestType = $uploadRequestType;
      $this->subDirectory = $subDirectory;
    }

    /**
     * Az uploadRequest adataiból összeállítja a widget paramétereit.
     *
     * @param  FileRepositoryUploadRequestInterface
     *
     * @return array
     */
    public function transform($uploadRequest)
    {
      if (!$uploadRequest instanceof FileRepositoryUploadRequestInterface)
      {
        throw new TransformationFailedException('Expected an instance of FileRepositoryInterface.');
      }

      return array('id' => $uploadRequest->getOriginalFileId());
    }

    /**
     * A widget-ből bejövő tömb alapján elkészíti az uploadRequest-et és feltölti az adatokkal
     *
     * @param  array $data
     *
     * @return FileRepositoryUploadRequestInterface
     *
     */
    public function reverseTransform($data)
    {
      $request = $this->manager->createUploadRequest($this->uploadRequestType, $this->type, $this->subDirectory);

      $request->setUploadedFile(empty($data['file']) ? null : $data['file'])
              ->setDeleteRequest( empty($data['delete']) ? false : $data['delete']);

      return $request;
    }
}