<?php

namespace Zpi\UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\UserManagementBundle\Form\Type\UserEditFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zpi\UserBundle\Entity\User;

class UserManagementController extends Controller
{
    
    public function listAction(Request $request)
    {
        /* 
         * to zabezpieczenie kontrolera tak dla zobaczenia, że się tak da
         * generalnie tę rzecz jak i różne inne akcje z zarządzaniem userami
         * łatwo można zabezpieczyć po pasku adresu w security.yml
         */
        if(false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN'))
        {
            //throw new AccessDeniedException(); // na razie bez ograniczeń
        }
      
        if(!$request->isXmlHttpRequest())
        {
            // skąd mogę pobrać pattern dla danej routy? Takie wpisanie na sztywno /users mi się nie podoba.
            if($request->getRequestUri() == '/' . $request->getSession()->get('conference')->getPrefix() . '/users')
                return $this->redirect($this->generateUrl ('users_manage', array('sortby' => 'id', 'direction' => 'desc')));    
        }  

        $em = $this->getDoctrine()->getEntityManager();
        
        $params =array();
        $test = '';
        if($request->isXmlHttpRequest())
        {
            // bardzo przydatna pętla do testowania ajaxa na localhoście. :)
            //for($i = 0; $i<5000000; $i++) $cos = 'cos';
            $params = $request->request->all();
            $test = print_r($params, true);
        }
        else
        {
            $params = $request->query->all();
        }
        
        
        $qb = $em->createQueryBuilder()
                ->select('u')
                ->from('ZpiUserBundle:User', 'u')
                ->where('u.type <> :coauthor')
                    ->setParameter('coauthor', User::TYPE_COAUTHOR);
        if(isset($params['limit']))
            $qb = $qb->setMaxResults($params['limit']);
        
        if(isset($params['sortby']))
            $qb = $qb->orderBy('u.' . $params['sortby'], (isset($params['direction']) && $params['direction'] == 'desc') ? 'desc' : 'asc');
        
        // jakies customowe zmiany tutaj

        $users = $qb->getQuery()->execute();
        

       
        
        if(isset($params['direction']) && $params['direction'] == 'asc') 
            $params['direction'] = 'desc';
        else
            $params['direction'] = 'asc';
            
            
            
        $url = '';        
        unset($params['sortby']);
        
        foreach($params as $key => $value)
            $url .= '&' . $key . '=' . $value;
        
        //echo $urlajax;
        if($request->isXmlHttpRequest())
        {
            $response = new Response(json_encode(
                    array('users' => $this->get('templating')->render('ZpiUserManagementBundle:UserManagement:userlist_body.html.twig', array('users' => $users)),
                          'params' => $url, 'test' => $test)
                    ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        return $this->render('ZpiUserManagementBundle:UserManagement:userlist.html.twig', array('users' => $users, 'params' => $url));

    }
    
    public function editAction(Request $request, $id)
    {
        if(!$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) 
        {
            //throw new AccessDeniedException();
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
