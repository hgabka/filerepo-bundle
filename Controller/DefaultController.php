<?php

namespace HG\FileRepositoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
  /**
  * Letölti az adott id-jű filet
  *
  * @param int $id
  * @param string $filename
  */
  public function downloadAction($id, $filename = null)
  {
    return $this->get('hg_file_repository.filemanager')->download($id, $filename);
  }

  public function uploadifyAction(Request $request, $name, $type, $formType, $subdir = 'null', $controller = 'null')
  {
    $form = $this->createForm($formType, null, array('widget_name' => $name));
    
    $form->handleRequest($request);
    
    if (!$form->isValid())
    {
      return new Response(json_encode(array('valid' => false, 'msgs' => $form[$name]->getErrorsAsString())));
    }
     
    $file = $form[$name]->getData();
    
    $manager = $this->get('hg_file_repository.filemanager');
    $id = $manager->saveFromUploadedFile($file, $type, empty($subdir) || $subdir == 'null' ? null : $subdir);

    if (!empty($controller) && $controller !== 'null')
    {
      return $this->forward($controller, array('file_id' => $id, 'file' => $file, 'name' => $name));
    }

    return new Response(json_encode(array('valid' => false, 'msgs' => 'Ervenytelen response')));
  }

  public function uploadifyRenderAction($file_id, $file, $name)
  {
     return new Response(json_encode(array('valid' => true, 'html' => $this->renderView('HGFileRepositoryBundle:Default:uploadifyRender.html.twig', array('file_id' => $file_id)))));
   }
}
