<?php

namespace Zpi\UserManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\UserManagementBundle\Form\Type\UserEditFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zpi\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;

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
      
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route = $routeParameters['_conf'];
        
        // od razu ustawiamy w pasku adresu domyślne parametry tabelki (żeby użytkownik od razu wiedział o co chodzi)
        if(!$request->isXmlHttpRequest())
        {
            // skąd mogę pobrać pattern dla danej routy? Takie wpisanie na sztywno /users mi się nie podoba.
            if($request->getRequestUri() == '/' . $route . '/users')
                return $this->redirect($this->generateUrl ('users_manage', array('sortby' => 'id', 'direction' => 'desc', 'limit' => 20)));    
        }  

        $em = $this->getDoctrine()->getEntityManager();
        
        $params =array();

        // bierzemy parametry w zależności od typu żądania
        if($request->isXmlHttpRequest())
        {
            // bardzo przydatna pętla do testowania ajaxa na localhoście. :)
            //for($i = 0; $i<5000000; $i++) $cos = 'cos';
            $params = $request->request->all();
        }
        else
        {
            $params = $request->query->all();
        }
        
        // nasze zapytanie - poniżej je dookreślamy (w przypadku braku wartości - domyślnymi)
        $qb = $em->createQueryBuilder()
                ->select('u')
                ->from('ZpiUserBundle:User', 'u')
                ->where('u.type <> :coauthor')
                    ->setParameter('coauthor', User::TYPE_COAUTHOR);
        
        if(!isset($params['limit']))
            $params['limit'] = 20;
        
        $qb = $qb->setMaxResults($params['limit']);
        
        if(!isset($params['page']))
            $params['page'] = 1;
        
        $qb = $qb->setFirstResult(($params['page']-1) * $params['limit']);
        
        if(isset($params['sortby']))
            $qb = $qb->orderBy('u.' . $params['sortby'], (isset($params['direction']) && $params['direction'] == 'desc') ? 'desc' : 'asc');

        // zapytanie gotowe, można pobrać userów
        $users = $qb->getQuery()->execute();
        
        // pobieramy obecny url na potrzeby paginacji
        $paginateurl = '';
        
        foreach($params as $key => $value)
            $paginateurl .= '&' . $key . '=' . $value;
        
        $paginateurl[0] = '?';
        
        // ustawiamy nowy url
        if(isset($params['direction']) && $params['direction'] == 'asc') 
            $params['direction'] = 'desc';
        else
            $params['direction'] = 'asc';

        $url = '';
        
        // żeby nie dublować &sortby=x
        unset($params['sortby']);
        
        foreach($params as $key => $value)
            $url .= '&' . $key . '=' . $value;
        
        // gdy puste ustawienia kolumn, wrzucamy jakieś domyślne
        if($request->cookies->has('columns'))
        {
            $showedColumns = explode('|', $request->cookies->get('columns'));
        }
        else
        {
            $showedColumns = array('id', 'email', 'name', 'surname', 'address', 'city', 'country', 'edit');
            //$response = new Response();
            //$response->headers->setCookie(new Cookie('columns', implode('|', $showedColumns), time() + (3600 * 24 * 30)));
            //$response->send();
            // ustawianie cookiesow przez symfony cos kuleje (moze przez nasz przedrostek?), bo nie mozna ich potem czytac przez js.
            setCookie('columns', implode('|', $showedColumns), time() + (3600 * 24 * 30));
        }    
        // rozróżnienie widoku normalnego, ajax oraz do druku
        if($request->isXmlHttpRequest())
        {
            $response = new Response(json_encode(
                    array('users' => $this->get('templating')->render('ZpiUserManagementBundle:UserManagement:userlist_body.html.twig', array('users' => $users, 'showedColumns' => $showedColumns)),
                          'params' => $url,
                          'pagination' => $this->generatePagination($request->getRequestUri() . $paginateurl, $params['page'], $params['limit']),
                        )
                    ));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        return $this->render('ZpiUserManagementBundle:UserManagement:userlist.html.twig', 
                    array(
                        'users' => $users,
                        'params' => $url,
                        'pagination' => $this->generatePagination($request->getRequestUri(), $params['page'], $params['limit']),
                        'showedColumns' => $showedColumns
                        ));

    }
    
    //chyba mogę sobie definiować funkcje pomocnicze tutaj?
    private function generatePagination($targetpage = '/', $page = 1, $limit = 10)
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
            // Previous
            if ($page > 1)
		$paginate.= "<a href=\"$targetpage&page=$prev\">previous</a>";
            else
		$paginate.= "<span class=\"disabled\">previous</span>";
		
            // Pages	
            if ($lastpage < 7 + ($stages * 2))	// Not enough pages to breaking it up
            {	
		for ($counter = 1; $counter <= $lastpage; $counter++)
		{
                    if ($counter == $page)
			$paginate.= "<span class=\"current\">$counter</span>";
                    else
			$paginate.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
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
                            $paginate.= "<span class=\"current\">$counter</span>";
			else
                            $paginate.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
                    }					
                    $paginate.= "...";
                    $paginate.= "<a href=\"$targetpage&page=$LastPagem1\">$LastPagem1</a>";
                    $paginate.= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";		
                }
                // Middle hide some front and some back
                elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2))
                {
                    $paginate.= "<a href=\"$targetpage&page=1\">1</a>";
                    $paginate.= "<a href=\"$targetpage&page=2\">2</a>";
                    $paginate.= "...";

                    for ($counter = $page - $stages; $counter <= $page + $stages; $counter++)
                    {
                        if ($counter == $page)
                            $paginate.= "<span class=\"current\">$counter</span>";
                        else
                            $paginate.= "<a href=\"$targetpage&page=$counter\">$counter</a>";
                    }					

                        $paginate.= "...";
                        $paginate.= "<a href=\"$targetpage&page=$LastPagem1\">$LastPagem1</a>";
                        $paginate.= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";		
                }
                // End only hide early pages
                else
                {
                    $paginate.= "<a href=\"$targetpage&page=1\">1</a>";
                    $paginate.= "<a href=\"$targetpage&page=2\">2</a>";
                    $paginate.= "...";

                    for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                            $paginate.= "<span class=\"current\">$counter</span>";
                        else
                            $paginate.= "<a href=\"$targetpage&page=$counter\">$counter</a>";					
                    }
                }
            }
					
		// Next
		if ($page < $counter - 1)
                    $paginate.= "<a href=\"$targetpage&page=$next\">next</a>";
		else
                    $paginate.= '<span class="disabled">next</span>';

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
