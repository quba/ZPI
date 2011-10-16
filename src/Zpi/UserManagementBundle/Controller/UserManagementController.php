<?php

namespace Zpi\UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zpi\UserManagementBundle\Form\Type\UserEditFormType;
use \Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserManagementController extends Controller
{
    
    public function listAction()
    {
        if(!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) 
        {
            throw new AccessDeniedException();
        }
        $um = $this->get('fos_user.user_manager');
        $users = $um->findUsers();
        return $this->render('ZpiUserManagementBundle:UserManagement:userlist.html.twig', array('users' => $users));
    }
    
    public function editAction(Request $request, $id)
    {
        if(!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) 
        {
            throw new AccessDeniedException();
        }
        
        $um = $this->get('fos_user.user_manager');
        $user = $um->findUserBy(array('id' => $id));
        $form = $this->createForm(new UserEditFormType('Zpi\UserBundle\Entity\User'), $user);
     
        if ($request->getMethod() == 'POST')
	{
		$form->bindRequest($request);
			
		if ($form->isValid())
		{		
                        //$user->addRole('ROLE_REVIEWER'); // so simple as it looks like ;) domyślną rolą jest role user (puste pole w bazie)
			$um->updateUser($user);
			$session = $this->getRequest()->getSession();
                        $session->setFlash('notice', 'Congratulations, your action succeeded!');
			return $this->redirect($this->generateUrl('users_manage'));
		}
	}
        
        return $this->render('ZpiUserManagementBundle:UserManagement:edituser.html.twig', array('form' => $form->createView(), 'user' => $user));
    }
}
