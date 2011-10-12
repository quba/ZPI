<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;





class PageController extends Controller
{
    
    public function indexAction()
    {
    		

    	$subpages = $this->getDoctrine()
    		->getRepository('ZpiPageBundle:SubPage')
    		->findAll();
    	//$twig = new Twig_Environment($loader);
    	//$twig->addGlobal('subpages', $subpages);
    	
        return $this->render('ZpiPageBundle:Page:index.html.twig', array('subpages' => $subpages));
    }
}
