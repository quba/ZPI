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
            $path = $request->getPathInfo();
            $router = $this->get('router');
            $routeParameters = $router->match($path);
            $route = $routeParameters['_conf'];
            // skąd mogę pobrać pattern dla danej routy? Takie wpisanie na sztywno /users mi się nie podoba.
            if($request->getRequestUri() == '/' . $route . '/users')
                return $this->redirect($this->generateUrl ('users_manage', array('sortby' => 'id', 'direction' => 'desc', 'limit' => 20)));    
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
        
        if(!isset($params['limit']))
            $params['limit'] = 10;
        
        $qb = $qb->setMaxResults($params['limit']);
        
        if(!isset($params['page']))
            $params['page'] = 1;
        
        $qb = $qb->setFirstResult(($params['page']-1) * $params['limit']);
        
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
        
        return $this->render('ZpiUserManagementBundle:UserManagement:userlist.html.twig', array('users' => $users, 'params' => $url, 'pagination' => $this->generatePagination($request->getRequestUri(), $params['page'], $params['limit'])));

    }
    
    //chyba mogę sobie definiować funkcje pomocnicze tutaj?
    public function generatePagination($targetpage = '/', $page = 1, $limit = 10)
    {	
        $em = $this->getDoctrine()->getEntityManager();
        
        $total_pages = $em->createQuery(
                            'SELECT COUNT(u.id) as total FROM ZpiUserBundle:User u WHERE u.type <> :coauthor')
                             ->setParameter('coauthor', USER::TYPE_COAUTHOR)
                             ->getOneOrNullResult();
        
	$total_pages = $total_pages['total'];
	
	$stages = 3;
	if($page)
            $start = ($page - 1) * $limit; 
        else
            $start = 0;		
	
	
	// Initial page num setup
	if ($page == 0)
            $page = 1;
        
	$prev = $page - 1;	
	$next = $page + 1;							
	$lastpage = ceil($total_pages/$limit);		
	$LastPagem1 = $lastpage - 1;					
	
	
	$paginate = '';
        
        if(strpos($targetpage, '?') === false)
            $pageslug = '?page=';
        elseif(strpos($targetpage, 'page=') === false)
            $pageslug = '&page=';
        else
        {
            $pageslug = '&page=';
            $targetpage = preg_replace('/&page=\d*/i', '', $targetpage);
        }    
        
	if($lastpage > 1)
	{	
            $paginate .= "<div class='paginate'>";
            // Previous
            if ($page > 1)
		$paginate.= "<a href='$targetpage&page=$prev'>previous</a>";
            else
		$paginate.= "<span class='disabled'>previous</span>";
		
            // Pages	
            if ($lastpage < 7 + ($stages * 2))	// Not enough pages to breaking it up
            {	
		for ($counter = 1; $counter <= $lastpage; $counter++)
		{
                    if ($counter == $page)
			$paginate.= "<span class='current'>$counter</span>";
                    else
			$paginate.= "<a href='$targetpage&page=$counter'>$counter</a>";
                }					
			
            }
            elseif($lastpage > 5 + ($stages * 2))	// Enough pages to hide a few?
            {
		//Beginning only hide later pages
		if($page < 1 + ($stages * 2))		
		{
                    for ($counter = 1; $counter < 4 + ($stages * 2); $counter++)
                    {
			if ($counter == $page)
                            $paginate.= "<span class='current'>$counter</span>";
			else
                            $paginate.= "<a href='$targetpage&page=$counter'>$counter</a>";
                    }					
                    $paginate.= "...";
                    $paginate.= "<a href='$targetpage&page=$LastPagem1'>$LastPagem1</a>";
                    $paginate.= "<a href='$targetpage&page=$lastpage'>$lastpage</a>";		
                }
                // Middle hide some front and some back
                elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2))
                {
                    $paginate.= "<a href='$targetpage&page=1'>1</a>";
                    $paginate.= "<a href='$targetpage&page=2'>2</a>";
                    $paginate.= "...";

                    for ($counter = $page - $stages; $counter <= $page + $stages; $counter++)
                    {
                        if ($counter == $page)
                            $paginate.= "<span class='current'>$counter</span>";
                        else
                            $paginate.= "<a href='$targetpage&page=$counter'>$counter</a>";
                    }					

                        $paginate.= "...";
                        $paginate.= "<a href='$targetpage&page=$LastPagem1'>$LastPagem1</a>";
                        $paginate.= "<a href='$targetpage&page=$lastpage'>$lastpage</a>";		
                }
                // End only hide early pages
                else
                {
                    $paginate.= "<a href='$targetpage&page=1'>1</a>";
                    $paginate.= "<a href='$targetpage&page=2'>2</a>";
                    $paginate.= "...";

                    for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                            $paginate.= "<span class='current'>$counter</span>";
                        else
                            $paginate.= "<a href='$targetpage&page=$counter'>$counter</a>";					
                    }
                }
            }
					
		// Next
		if ($page < $counter - 1)
                    $paginate.= "<a href='$targetpage&page=$next'>next</a>";
		else
                    $paginate.= "<span class='disabled'>next</span>";
			
			
		$paginate.= "</div>";		
	
	
        }
        
        $request = $this->getRequest();
        
        if($request->isXmlHttpRequest())
        {
            $response = new Response(json_encode(
                    array('pagintation' => $paginate)
                    ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
         
         // pagination
         return $paginate;
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
