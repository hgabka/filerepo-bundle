<?php

namespace HG\FileRepositoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use HG\FileRepositoryBundle\Model\HGFileManager as FileManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use HG\FileRepositoryBundle\Form\EventListener\FileRepositoryFormSubscriber;
use Symfony\Component\Form\ReversedTransformer;
use HG\FileRepositoryBundle\Entity\HGFile;
use HG\FileRepositoryBundle\Form\DataTransformer\FileRepositoryViewTransformer;
use HG\FileRepositoryBundle\Form\DataTransformer\FileRepositoryModelTransformer;


class FileRepositoryType extends AbstractType
{
    protected $fileManager;
    protected $uploadManager;
    protected $uploadRequesttype;

    public function __construct($fileManager, $uploadManager, $uploadRequestType)
    {
      $this->fileManager = $fileManager;
      $this->uploadManager = $uploadManager;
      $this->uploadRequestType = $uploadRequestType;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
       $resolver->setRequired(array('repository_type'));
       
       $resolver->setDefaults(array(
          'file_widget_options' => array(),
          'delete_link_class' => 'file-repository-delete',
          'delete_link_type' => 'button',
          'delete_link_text' => 'Törlés',
          'download_link_class' => 'file-repository-download',
          'download_link_text' => '%filename% letöltése',
          'download_filename' => null,
          'data_class' => null,
          'field' => null,
          'subdirectory' => null,
          'template' => '%filename%<br />%download_link%&nbsp;%delete_link%',
          'download_route' => 'hg_file_repository_download',
      ));

      $resolver->setAllowedValues(array(
        'repository_type' => array_keys($this->fileManager->getTypes()),
        'delete_link_type' => array('anchor', 'button'),
        )
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $fileOptions = $options['file_widget_options'];

      $field = empty($options['field']) ? $builder->getName() : $options['field'];

      $builder->add('id', 'hidden');
      $builder->add('delete', 'hidden', array('data' => 0));
      $builder->add('file', 'file', $fileOptions);

      $builder->addViewTransformer(new FileRepositoryViewTransformer($this->uploadManager, $options['repository_type'], $options['subdirectory'], $this->uploadRequestType));
      $builder->addModelTransformer(new FileRepositoryModelTransformer($this->uploadManager, $options['repository_type'], $options['subdirectory'], $this->uploadRequestType));

      $builder->addEventSubscriber(new FileRepositoryFormSubscriber($this->uploadManager, $options['repository_type'], $field));

    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'file_repository';
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
      $filename = $options['download_filename']  ? : (!is_null($form->getData()) ? $form->getData()->getFilOriginalFilename() : '');
      
      $view->vars['download_filename'] = $filename;
      $view->vars['delete_link_class'] = $options['delete_link_class'];
      $view->vars['delete_link_type'] = $options['delete_link_type'];
      $view->vars['delete_link_text'] = $options['delete_link_text'];
      $view->vars['download_link_class'] = $options['download_link_class'];
      $view->vars['download_link_text'] = $options['download_link_text'];
      $view->vars['template'] = $options['template'];
      $view->vars['download_route'] = $options['download_route'];
    }

}