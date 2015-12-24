<?php

namespace HG\FileRepositoryBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FileRepositoryFormSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $type;
    private $field;

    public function __construct($manager, $type, $field)
    {
      $this->manager = $manager;
      $this->type = $type;
      $this->field = $field;
    }

    public static function getSubscribedEvents()
    {
      return array(
          FormEvents::SUBMIT => 'submit',
        );
    }

    public function submit(FormEvent $event)
    {
      $request = $event->getData();
      $form = $event->getForm();

      $parent = $form->getParent() ? $form->getParent()->getData() : null;

      $request->setOriginalFile($form->getData());

      if (is_object($parent))
      {
        $request->setEntity($parent)
                ->setField($this->field);

        $this->manager->addUploadRequest($request);
      }
    }

}