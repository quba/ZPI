<?php    
namespace Zpi\PaperBundle\Controller;

use Zpi\ConferenceBundle\Entity\Conference;

use Zpi\PaperBundle\Entity\Comment;

use Zpi\PaperBundle\Entity\UserPaper;

use Zpi\UserBundle\Entity\User;

use Zpi\PaperBundle\Form\Type\ReviewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\PaperBundle\Entity\Review;

/**
 * Kontroler dla klasy Review.
 * @author lyzkov
 */
class ReviewController extends Controller
{
    
    /**
     * Tworzy bądź edytuje recenzję dla danego dokumentu.
     * @param Request $request
     * @param unknown_type $doc_id
     * @author lyzkov
     */
    //TODO Walidacja formularza.
    //TODO Dopracowanie widoku.
    //TODO Wprowadzić dwa typy statusu - normal i technical
    public function newAction(Request $request, $doc_id, $review_id = null)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route['name'] = $routeParameters['_route'];
        unset($routeParameters['_route']);
        $route['params'] = $routeParameters;
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Document');
        $qb = $repository->createQueryBuilder('d')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registration', 'r')
            ->innerJoin('r.conference', 'c')
            ->innerJoin('p.users', 'up')
                ->where('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id)
                ->andWhere('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId())
            ;
        
        $document = null;
        $reviewType = null;
        
        switch ($route['name'])
        {
            case 'review_edit':
            case 'review_new':
                $query = $qb->andWhere('up.editor = TRUE')
                    ->getQuery()->setMaxResults(1);
                $document = $query->getOneOrNullResult();
                $reviewType = Review::TYPE_NORMAL;
                break;
            case 'tech_review_edit':
            case 'tech_review_new':
                $query = $qb->andWhere('up.techEditor = TRUE')
                    ->getQuery()->setMaxResults(1);
                $document = $query->getOneOrNullResult();
                $reviewType = Review::TYPE_TECHNICAL;
                break;
            default:
                throw $this->createNotFoundException(
                    $translator->trans('exception.route_not_found: %route%', array(
            			'%route%' => $route['name'])));
        }
        
        // TODO Treść błędu??
        if (is_null($document))
        {
            throw $this->createNotFoundException(
                $translator->trans('exception.permission_denied'));
        }
        
        $pap_id = $document->getPaper()->getId();
        $em = $this->getDoctrine()->getEntityManager();
        $lastId = $em->createQuery('SELECT count(d) FROM ZpiPaperBundle:Document d WHERE d.paper = :pap_id')
                        ->setParameters(array('pap_id' => $pap_id))
                    ->getOneOrNullResult();
        
        if ($lastId[1] != $document->getVersion())
        {
            throw $this->createNotFoundException(
                $translator->trans('review.new.exception.not_last_version: %doc_id%', array(
            		'%doc_id%' => $doc_id)));
        }
        
        $reviews = $document->getReviews();
        $review = null;
        foreach ($reviews as $rev)
        {
            if ($rev->getId() == $review_id)
            {
                if ($rev->getApproved() == Review::APPROVED)
                {
                    throw $this->createNotFoundException($translator->trans(
                        'review.edit.exception.review_approved'));
                }
                $review = $rev;
                break;
            }
            // Sprawdź czy nie recenzent nie napisał już recenzji
            if ($rev->getEditor()->getId() == $user->getId() && $rev->getType() == $reviewType)
            {
                throw $this->createNotFoundException(
                    $translator->trans('review.new.exception.review_duplicate'));
            }
        }
        
        if (is_null($review))
        {
            $review = new Review();
        }
        $review->setType($reviewType);
        
        $form = $this->createForm(new ReviewType(), $review);
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
        
            if ($form->isValid())
            {
                $review->setEditor($user);
                $review->setDocument($document);
                $review->setDate(new \DateTime());
                $review->setApproved(Review::NOT_APPROVED);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($review);
                $em->persist($document);
                $em->flush();
                $this->get('session')->setFlash('notice',
                    $translator->trans('review.new.success'));
        
                return $this->redirect($this->generateUrl('review_show', array(
                	'doc_id' => $doc_id)));
            }
        }
        
        return $this->render('ZpiPaperBundle:Review:new.html.twig', array(
        	'form' => $form->createView(),
        	'doc_id' => $doc_id,
        	'route' => $route));
    }
    
    public function deleteAction(Request $request, $review_id)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        if (($method = $request->getMethod()) != 'POST')
        {
            throw $this->createNotFoundException($translator->trans(
            	'unsupported_method: %method%', array(
                	'%metod%' => $method)));
        }
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Review');
        $qb = $repository->createQueryBuilder('r')
            ->innerJoin('r.document', 'd')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registration', 'reg')
            ->innerJoin('reg.conference', 'c')
            ->innerJoin('p.users', 'up')
                ->where('r.id = :review_id')
                    ->setParameter('review_id', $review_id)
                ->andWhere('r.approved = FALSE')
                ->andWhere('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId())
                ->andWhere('up.editor = TRUE OR up.techEditor = TRUE')
        ;
        
        $review = $qb->getQuery()->getOneOrNullResult();
        
        if (is_null($review))
        {
            throw $this->createNotFoundException($translator->trans(
                'review.exception.not_found'));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($review);
        $em->flush();
        
        if($this->getRequest()->isXmlHttpRequest())
        {
            $response = new Response(json_encode(array('reply' => true)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        $lastRoute = $session->get('last_route');
        return $this->redirect($lastRoute['name'], $lastRoute['params']);
    }
    
    /**
     * Wyświetla wszystkie recenzje dotyczące danego dokumentu, a także komentarze dla recenzentów.
     * @param Request $request
     * @param unknown_type $doc_id
     * @author lyzkov
     */
    //TODO Optymalizacja zapytań.
    //TODO Poprawienie widoku.
    //TODO Poprawienie ról?
    public function showAction(Request $request, $doc_id)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        
        // Zapytanie zwracające papier o danym id powiązany z użytkownikiem i konferencją.
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Document');
        $queryBuilder = $repository->createQueryBuilder('d')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registration', 'reg')
            ->innerJoin('reg.conference', 'c')
                ->where('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id)
                ->andWhere('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
            ;
        
        $reviews = null;
        $document = null;
        $twigParams = array();
        
        $roles = array();
        $roles[] = null;
        
        //TODO Nieładny sposób sprawdzania roli: hasRole().
        //TODO Te ify się za bardzo rozrosły :/
        $isFetched = false;
        if ($user->hasRole(User::ROLE_EDITOR))
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.editor = TRUE')
                ->getQuery();
            $document = $query->getOneOrNullResult();
        
            // Znajduje dokument o podanym id
//             foreach ($documents as $doc)
//             {
//                 echo $doc->getVersion() . '<br />';
//                 if ($doc->getId() == $doc_id)
//                 {
//                     $document = $doc;
//                     break;
//                 }
//             }
            
            if (!is_null($document))
            {
                $twigParams['user_id'] = $user->getId();
                $roles[] = User::ROLE_EDITOR;
                $isFetched = true;
            }
        }
        if ($user->hasRole(User::ROLE_TECH_EDITOR))
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.techEditor = TRUE')
                ->getQuery();
//             $tmpDoc = array();
            $document = $query->getOneOrNullResult();
        
            // Znajduje dokument o podanym id
//             foreach ($tmpDoc as $doc)
//             {
//                 if ($doc->getId() == $doc_id)
//                 {
//                     $document = $doc;
//                     break;
//                 }
//             }
            
            if (!is_null($document))
            {
                $twigParams['user_id'] = $user->getId();
                $roles[] = User::ROLE_TECH_EDITOR;
                $isFetched = true;
            }
        }
        if ($user->hasRole(User::ROLE_ORGANIZER) && !$isFetched)
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('c.organizers', 'u')
                    ->andWhere('u.id = :user_id')
                        ->setParameter('user_id', $user->getId())
                ->getQuery();
            $document = $query->getOneOrNullResult();
        
            // Znajduje dokument o podanym id
//             foreach ($documents as $doc)
//             {
//                 if ($doc->getId() == $doc_id)
//                 {
//                     $document = $doc;
//                     break;
//                 }
//             }
            
            if (!is_null($document))
            {
                $roles[] = User::ROLE_ORGANIZER;
                $isFetched = true;
            }
        }
        if ($user->hasRole(User::ROLE_USER) && !$isFetched)
        {
            $query = $queryBuilder
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.author = :existing')
                        ->setParameter('existing', UserPaper::TYPE_AUTHOR_EXISTING)
                ->getQuery();
            $document = $query->getOneOrNullResult();
        
            // Znajduje dokument o podanym id
//             foreach ($documents as $doc)
//             {
//                 if ($doc->getId() == $doc_id)
//                 {
//                     $document = $doc;
//                     break;
//                 }
//             }
            
            if (!is_null($document))
            {
                $roles[] = User::ROLE_USER;
                $isFetched = true;
            }
        }
        
        // Jeśli nie znalazł to wywal Not Found
        if (!$isFetched)
        {
            throw $this->createNotFoundException(
                $translator->trans('review.exception.not_found: %id%',
                    array('%id%' => $doc_id)));
        }
        
        
        $pap_id = $document->getPaper()->getId();
        $em = $this->getDoctrine()->getEntityManager();
        $lastId = $em
                ->createQuery('SELECT count(d) FROM ZpiPaperBundle:Document d
                    WHERE d.paper = :pap_id')
                ->setParameters(array('pap_id' => $pap_id))
                ->getOneOrNullResult();
        
        // Sprawdza czy dokument jest ostatnią wersją pracy
        $isLast = $lastId[1] == $document->getVersion();
        
        
        $reviews = $document->getReviews();
        
        // Podział recenzji na normalne i techniczne.
        $techReviews = array();
        for ($i = 0; $i < count($reviews); $i++)
        {
            if ($reviews[$i]->getType() == Review::TYPE_TECHNICAL)
            {
                $techReviews[] = $reviews[$i];
                unset($reviews[$i]);
            }
        }
        
        $twigParams = array_merge($twigParams, array(
            'reviews' => $reviews,
            'tech_reviews' => $techReviews,
            'document' => $document,
            'roles' => $roles,
            'is_last' => $isLast,
            'user_id' => $user->getId(),
            'conference' => $conference));
        
        return $this->render('ZpiPaperBundle:Review:show.html.twig', $twigParams);
    }
    
    /**
     * Wyświetla wybraną recenzję wraz z komentarzami.
     * @param Request $request
     * @param unknown_type $doc_id
     * @param unknown_type $review_id
     * @author lyzkov, quba
     */
    //TODO Optymalizacja zapytań!!! - nie chce mi sie jakos tego mocno analizowac, ale nie mozna od razu pobrac tym wielkim
    // zapytaniem review? Jesli pobierze jakis rekord, to mam prawa, jesli nie to nie mam?
    //TODO Stężenie rozmaitych hacków i tricków jest tutaj zdecydowanie zbyt duże
    public function commentAction(Request $request, $doc_id, $review_id = null)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        
        // Zapytanie zwracające papier o danym id powiązany z użytkownikiem i konferencją.
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Document');
        $queryBuilder = $repository->createQueryBuilder('d')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registration', 'reg')
            ->innerJoin('reg.conference', 'c')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id)
            ;
        
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route['name'] = $routeParameters['_route'];
        unset($routeParameters['_route']);
        $route['params'] = $routeParameters;
        
        //TODO Bardzo brzydki hack
        if (is_null($review_id) && $request->getMethod() == 'POST')
        {
            $route['name'] = 'review_show';
        }
            
        if ($route['name'] == 'review_comment')
        {
            // Sprawdź czy w konferencji skonfigurowano obsługę tego typu komentarzy
            if (!$conference->isCommentsType(Conference::COMMENTS_TYPE_REVIEW))
            {
                throw $this->createNotFoundException(
                    $translator->trans('comment.exception.wrong_type: %type%',
                        array('%type%' => Conference::COMMENTS_TYPE_REVIEW)));
            }
            $queryBuilder
                ->innerJoin('d.reviews', 'r')
                    ->andWhere('r.id = :review_id')
                    ->setParameter('review_id', $review_id)
                ;
        }
        elseif ($route['name'] == 'review_comment')
        {
            // Sprawdź czy w konferencji skonfigurowano obsługę tego typu komentarzy
            if (!$conference->isCommentsType(Conference::COMMENTS_TYPE_DOCUMENT))
            {
                throw $this->createNotFoundException(
                    $translator->trans('comment.exception.wrong_type: %type%',
                        array('%type%' => Conference::COMMENTS_TYPE_DOCUMENT)));
            }
        }
        
        $document = null;
        $review = null;
        $isFetched = false;
        $roles = array();
        
        if ($user->hasRole(User::ROLE_EDITOR))
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.editor = TRUE')
                ->getQuery();
            $document = $query->getOneOrNullResult();
            if (!is_null($document))
            {
                $roles[] = User::ROLE_EDITOR;
                $isFetched = true;
            }
        }
        if ($user->hasRole(User::ROLE_TECH_EDITOR) && !$isFetched)
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.techEditor = TRUE')
                ->getQuery();
            $document = $query->getOneOrNullResult();
            if (!is_null($document))
            {
                $role = User::ROLE_TECH_EDITOR;
                $isFetched = true;
            }
        }
        if ($user->hasRole(User::ROLE_ORGANIZER) && !$isFetched)
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('c.organizers', 'u')
                    ->andWhere('u.id = :user_id')
                        ->setParameter('user_id', $user->getId())
                ->getQuery();
            $document = $query->getOneOrNullResult();
            if (!is_null($document))
            {
                $roles[] = User::ROLE_ORGANIZER;
                $isFetched = true;
            }
        }
        //TODO Nie wiem czy tu powinno być 404, zasób jest na serwerze ale użytkownik nie ma prawa dostępu
        if (!$isFetched)
        {
            throw $this->createNotFoundException(
                $translator->trans('review.exception.not_found: %doc_id%, %review_id%',
                    array('%review_id%' => $review_id, '%doc_id%' => $doc_id)));
        }
        
        $review = null;
        $target = null;
        
        switch ($route['name'])
        {
            case 'review_comment':
                $review = $this->getDoctrine()->getRepository('ZpiPaperBundle:Review')
                    ->find($review_id);
                $target = $review;
                break;
            case 'review_show':
                $target = $document;
                break;
            default:
                throw $this->createNotFoundException(
                    $translator->trans('exception.route_not_found: %route%', array('%route%' => $route['name'])));
        }
        
        // Nie będę tworzył form typa - wątpie żęby ten formularz się jeszcze gdzieś przydał.
        $comment = new Comment();
        $form = $this->createFormBuilder($comment)
            ->add('content')
            ->getForm();
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
        
            if ($form->isValid())
            {
                if ($route['name'] == 'review_comment')
                {
                    $comment->setReview($review);
                }
                else
                {
                    $comment->setDocument($document);
                }
                $comment->setUser($user);
                $comment->setDate(new \DateTime());
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($comment);
                $em->flush();
                
                if($this->getRequest()->isXmlHttpRequest())
                {
                    $editForm = $this->createFormBuilder($comment)
                        ->add('content')
                        ->getForm();
                    $comment->setEditForm($editForm->createView());
                    
                    $response = new Response(json_encode(array(
                                    'reply' => true,
                                    'html' => $this->get('templating')->render('ZpiPaperBundle:Review:comment_body.html.twig', array(
                    					'comment' => $comment)))));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                else
                {
                    $this->get('session')->setFlash('notice',
                        $translator->trans('reviewcomment.new.success'));
                    return $this->redirect($this->generateUrl($route['name'], $route['params']));
                }
            }
        }
        
        foreach($target->getComments() as $comment)
        {
            $editForm = $this->createFormBuilder($comment)
                ->add('content')
                ->getForm();
            $comment->setEditForm($editForm->createView());
        }
        
        switch ($route['name'])
        {
            case 'review_comment':
                return $this->render('ZpiPaperBundle:Review:comment.html.twig', array(
                	'target' => $review,
                    'roles' => $roles,
                    'form' => $form->createView(),
                    'route' => $route));
            case 'review_show':
                $template = $this->get('twig')->loadTemplate('ZpiPaperBundle:Review:comment.html.twig');
                //TODO Blok z javascriptem renderuje się w <body> - na razie nie widze innego rozwiązania (includowanie w szablonie javascriptu?)
                return new Response($template->renderBlock('js', array()) . $template->renderBlock('body', array(
                    'target' => $document,
                    'roles' => $roles,
                    'form' => $form->createView(),
                    'route' => $route)));
        }
    }
    
    public function commentEditAction(Request $request, $comment_id)
    {
        if($request->getMethod() == 'POST')
        {
            $translator = $this->get('translator');
            // sprawdzenie czy to moj komentarz
            $user = $this->get('security.context')->getToken()->getUser();
            $comment= $this->getDoctrine()->getRepository('ZpiPaperBundle:Comment')
                ->findOneBy(array('user' => $user->getId(), 'id' => $comment_id));

            if(is_null($comment))
                throw $this->createNotFoundException('comment.not.exist');

            $form = $this->createFormBuilder($comment)
                ->add('content')
                ->getForm();


            $form->bindRequest($request);

            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->flush();

                if($this->getRequest()->isXmlHttpRequest())
                {
                    $editForm = $this->createFormBuilder($comment)
                        ->add('content')
                        ->getForm();
                    $comment->setEditForm($editForm->createView());
                    
                    $response = new Response(json_encode(array(
                                    'reply' => true,
                                    'html' => $this->get('templating')->render('ZpiPaperBundle:Review:comment_body.html.twig', array('comment' => $comment)))));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                else
                    return $this->redirect($this->generateUrl('homepage')); //'review_comment', array('doc_id' => $document->getId(), 'review_id' => $review->getId())));
            }
        }
        else
            throw $this->createNotFoundException('comment.not.exist');
    }
    
    public function commentDeleteAction(Request $request, $comment_id)
    {
        $translator = $this->get('translator');
        // sprawdzenie czy to moj komentarz
        $user = $this->get('security.context')->getToken()->getUser();
        $comment= $this->getDoctrine()->getRepository('ZpiPaperBundle:Comment')
            ->findOneBy(array('user' => $user->getId(), 'id' => $comment_id));

        if(is_null($comment))
            throw $this->createNotFoundException('comment.not.exist');

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($comment);
        $em->flush();
        
        if($this->getRequest()->isXmlHttpRequest())
        {
            $response = new Response(json_encode(array('reply' => true)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else
        return $this->redirect($this->generateUrl('homepage'));
    }
    
    /**
     * Zatwierdza recenzje - recenzja staje się widoczna dla użytkownika
     * @param Request $request
     * @param unknown_type $doc_id
     * @param unknown_type $review_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function approveAction(Request $request, $doc_id, $review_id = null)
    {
        $translator = $this->get('translator');
        
        if (($method = $request->getMethod()) != 'POST')
        {
            throw $this->createNotFoundException($translator->trans('unsupported_method: %method%', array(
                '%metod%' => $method)));
        }
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        $user = $this->get('security.context')->getToken()->getUser();
        
        // sprawdzenie czy użytkownik może zatwierdzać
        //TODO Na razie tylko organizator - w przyszłości może recenzenci
        if (is_null($conference) || !$user->getConferences()->contains($conference))
        {
            throw $this->createNotFoundException(
                $translator->trans('conf.exception.not_found: %prefix%', array(
                    '%prefix%' => $conference->getPrefix())));
        }
        
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Review');
        $qb = $repository->createQueryBuilder('r')
                    ->innerJoin('r.document', 'd')
                    ->innerJoin('d.paper', 'p')
                    ->innerJoin('p.registration', 'reg')
                    ->innerJoin('reg.conference', 'c')
                        ->where('d.id = :doc_id')
                            ->setParameter('doc_id', $doc_id)
                        ->andWhere('c.id = :conf_id')
                            ->setParameter('conf_id', $conference->getId())
                ;
                
//         if (!is_null($review_id))
//         {
//             $qb
//                 ->andWhere('r.id = :review_id')
//                     ->setParameter('review_id', $review_id)
//                 ;
//         }
        
        $reviews = $qb->getQuery()->getResult();

        if(empty($reviews))
        {
            throw $this->createNotFoundException($translator->trans('review.not_found: %review_id%', array(
                '%review_id%' => $review_id)));
        }
        
        $document = $reviews[0]->getDocument();
        $status = $document->getStatusNormal();
        $statusTech = $document->getStatusTech();
        $isApproved = true;

        $em = $this->getDoctrine()->getEntityManager();
        foreach ($reviews as $review)
        {
            if (is_null($review_id) || $review_id == $review->getId())
            {
                $review->setApproved(Review::APPROVED);
                $em->persist($review);
                $newStatus = $review->getMark();
                if ($review->getType() == Review::TYPE_NORMAL)
                    $status = $newStatus < $status ? $newStatus : $status;
                else
                    $statusTech = $newStatus < $statusTech ? $newStatus : $statusTech;
            }
            $isApproved = $review->getApproved() ? $isApproved : false;
        }
        
        $document->setStatusNormal($status);
        $document->setStatusTech($statusTech);
        $document->setApproved($isApproved);
        $em->persist($document);
        $paper = $document->getPaper();
        $lastVersion = $em
            ->createQuery('SELECT count(d) FROM ZpiPaperBundle:Document d WHERE d.paper = :pap_id')
                ->setParameters(array('pap_id' => $paper->getId()))
            ->getOneOrNullResult()
        ;
        // Ustawia status pracy tylko dla ostatniego dokumentu
        if ($document->getVersion() == $lastVersion[1])
        {
            $paper->setStatusNormal($status);
            $paper->setStatusTech($statusTech);
            $paper->setApproved($isApproved);
            $em->persist($paper);
        }
        
        $em->flush();
        
        if($this->getRequest()->isXmlHttpRequest())
        {
            $response = new Response(json_encode(array('reply' => true)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else
        {
            $lastRoute = $session->get('last_route');
            return $this->redirect($lastRoute['name'], $lastRoute['params']);
        }
    }
    
}
