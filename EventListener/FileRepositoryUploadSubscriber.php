<?php

// src/Acme/SearchBundle/EventListener/SearchIndexer.php
namespace HG\FileRepositoryBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

class FileRepositoryUploadSubscriber implements EventSubscriber
{

  private $container;
    public function __construct($container)
    {
      $this->container = $container;
    }

    public function getSubscribedEvents()
    {
      $events = array(
          'preFlush',
      );
      
      if (true === $this->container->getParameter('hg_file_repository.auto_delete_relations'))
      {
        $events[] = 'preRemove';
      }
      
      return $events;
    }


    public function preFlush(PreFlushEventArgs $args)
    {
       $uploadManager = $this->container->get('hg_file_repository.upload_manager');
       $entities = $uploadManager->getEntities();

       if (empty($entities))
       {
         return;
       }

       $em = $args->getEntityManager();

       foreach ($entities as $entity)
       {
         if ($em->contains($entity))
         {
           $uploadManager->processRequestsForEntity($entity);
           $meta = $em->getClassMetadata(get_class($entity));
           $em->getUnitOfWork()->computeChangeSet($meta, $entity);
         }
       }
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
      $fileManager = $this->container->get('hg_file_repository.filemanager');
      $em = $args->getEntityManager();
      $entity = $args->getEntity();
      $meta = $em->getClassMetadata(get_class($entity));
      
      foreach ($meta->getAssociationMappings() as $name => $data)
      {
        if ($data['targetEntity'] !== 'HG\FileRepositoryBundle\Entity\HGFile')
        {
          continue;
        }
        
        $fileManager->remove($entity->{'get'.ucfirst($name)}());
      }
    }
    

}