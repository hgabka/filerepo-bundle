<?php

namespace HG\FileRepositoryBundle\Twig;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use HG\FileRepositoryBundle\Model\HGFileManager as FileManager;

class HGFileRepositoryExtension extends \Twig_Extension
{
  private $manager;
  private $router;

  public function __construct(FileManager $manager, $router)
  {
     $this->manager = $manager;
     $this->router = $router;
  }

    public function getGlobals()
    {
        return array(
            'hg_filemanager' => $this->manager,
        );
    }
  public function getFunctions()
  {
      return array(
          'file_repository_asset' => new \Twig_Function_Method($this, 'showFile', array(
              'is_safe' => array('html')
          )),
          
          'file_repository_path' => new \Twig_Function_Method($this, 'filePath', array(
              'is_safe' => array('html')
          )),
          
          'file_repository_url' => new \Twig_Function_Method($this, 'fileUrl', array(
              'is_safe' => array('html')
          )),
          
          'file_repository_link' => new \Twig_Function_Method($this, 'fileLink', array(
              'is_safe' => array('html')
          )),
      );
  }

  public function showFile($id)
  {
    return $this->manager->show($id);
  }
  
  public function filePath($id, $filename = '')
  {
    return $this->router->generate('hg_file_repository_download', array('id' => $id, 'filename' => $filename));
  }
  
  public function fileUrl($id, $filename = '')
  {
    return $this->router->generate('hg_file_repository_download', array('id' => $id, 'filename' => $filename), true);
  }
  
  public function fileLink($id, $name = '', $filename = '', $attributes = array(), $absolute = false)
  {
    $attStr = '';
    foreach ($attributes as $attr => $value)
    {
      $attStr.=' '.$attr.'="'.$value.'"';
    }
    $link = $this->router->generate('hg_file_repository_download', array('id' => $id, 'filename' => $filename), $absolute);
    
    if (empty($name))
    {
      $name = $link;
    }
    
    return '<a href="'.$link.'"'.$attStr.'>'.$name.'</a>';
  }
  
  public function getName()
  {
    return 'hg_file_repository';
  }


}