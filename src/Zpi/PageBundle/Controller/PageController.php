<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PageController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('ZpiPageBundle:Page:index.html.twig');
    }
}
