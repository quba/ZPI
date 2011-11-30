<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function indexAction()
    {
        $conferences = '';
        $conf = $this->getRequest()->getSession()->get('conference');
        if(empty($conf)) // strona główna systemu
        {    
            $conferences = $this->getDoctrine()->getEntityManager()->getRepository('ZpiConferenceBundle:Conference')
                    ->findAll();  
        }
        return $this->render('ZpiPageBundle:Page:index.html.twig', array('conferences' => $conferences));
    }
    
    public function mainAction()
    {
        return $this->redirect($this->generateUrl('homepage', array('_conf' => 'comas')));
    }
    
    public function changeLangAction($lang)
    {
        $session = $this->get('session');
        $session->setLocale($lang);
        $last_route = $session->get('last_route', array('name' => 'homepage'));
        return ($this->redirect($this->generateUrl($last_route['name'], $last_route['params'])));
    }
}
