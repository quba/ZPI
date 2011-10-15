<?php

namespace Zpi\UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zpi\UserManagementBundle\Form\Type\UserEditFormType;


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
        $form = $this->createForm(new UserEditFormType('Zpi\UserBundle\Entity\User'), $user);

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
