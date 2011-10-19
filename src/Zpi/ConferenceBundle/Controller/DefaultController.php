<?php

namespace Zpi\ConferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('ZpiConferenceBundle:Default:index.html.twig', array('name' => $name));
    }
}
