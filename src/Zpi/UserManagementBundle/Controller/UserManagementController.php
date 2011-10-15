<?php

namespace Zpi\UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class UserManagementController extends Controller
{
    
    public function listAction()
    {
        $um = $this->get('fos_user.user_manager');
        $users = $um->findUsers();
        return $this->render('ZpiUserManagementBundle:UserManagement:userlist.html.twig', array('users' => $users));
    }
    
    public function editAction(Request $request, $id)
    {
        $um = $this->get('fos_user.user_manager');
        $user = $um->findUserBy(array('id' => $id));
        $form = $this->createFormBuilder($user)
        ->add('email', 'email')
        ->add('plainPassword', 'repeated', array('type' => 'password'))
        ->add('title', 'choice', array('choices' => array(0 => 'register.title.mr', 1 => 'register.title.ms', 2 => 'register.title.bsc', 3 => 'register.title.msc', 4 => 'register.title.phd', 5 => 'register.title.prof')))
        ->add('name')
        ->add('surname')
        ->add('type', 'choice', array('choices' => array(0, 1), 'expanded' => true))
        ->add('institution')
        ->add('nipvat')
        ->add('address')
        ->add('city')
        ->add('postalcode')
        ->add('country')
        ->add('phone')
        ->getForm();
        
        if ($request->getMethod() == 'POST')
	{
		$form->bindRequest($request);
			
		if ($form->isValid())
		{			
			$um->updateUser($user);
			$session = $this->getRequest()->getSession();
                        $session->setFlash('notice', 'Congratulations, your action succeeded!');
			return $this->redirect($this->generateUrl('users_manage'));
		}
	}
        
        return $this->render('ZpiUserManagementBundle:UserManagement:edituser.html.twig', array('form' => $form->createView(), 'user' => $user));
    }
}
