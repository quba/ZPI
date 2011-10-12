<?php

namespace Zpi\UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UserManagementController extends Controller
{
    
    public function listAction()
    {
        return $this->render('ZpiUserManagementBundle:UserManagement:userlist.html.twig');
    }
}
