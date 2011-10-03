<?php

namespace Zpi\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/hellou/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name . ' asdf LOL');
    }
}
