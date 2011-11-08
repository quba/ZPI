<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;





class PageController extends Controller
{
    
    public function indexAction()
    {    	
        return $this->render('ZpiPageBundle:Page:index.html.twig');
    }
    
    public function mainAction()
    {
        $conferences = $this->getDoctrine()->getEntityManager()->getRepository('ZpiConferenceBundle:Conference')
                ->findAll();
        return $this->render('ZpiPageBundle:Page:main.html.twig', array('conferences' => $conferences));
    }
    
    
}
