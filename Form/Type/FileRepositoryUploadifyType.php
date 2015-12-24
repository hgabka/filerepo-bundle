<?php

namespace HG\FileRepositoryBundle\Form\Type;

use HG\UtilsBundle\Form\Type\UploadifyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class FileRepositoryUploadifyType extends UploadifyType
{
  protected $fileManager;

  public function __construct($fileManager)
  {
    $this->fileManager = $fileManager;
  }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
       $resolver->setRequired(array('repository_type'));

        $resolver->setDefaults(array(
            'route' => 'hg_file_repository_uploadify',
            'route_params' => array(),
            'size' => 'null',
            'js_upload_complete_callback' => '',
            'render_controller' => 'HGFileRepositoryBundle:Default:uploadifyRender',
            'file_types' => '*.*',
            'html' => null,
            'btn_label' => 'btn_widget_upload',
            'debug' => false,
            'subdir' => 'null',
            'upload_form_type' => 'uploadify_upload'
            ));

      $resolver->setAllowedValues(array(
        'repository_type' => array_keys($this->fileManager->getTypes()),
        )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
      $options['route_params'] = array_merge($options['route_params'], array('subdir' => $options['subdir'], 'type' => $options['repository_type']));

      parent::buildView($view, $form, $options);
    }

  public function getName()
  {
      return 'file_uploadify';
  }
}
