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
}
