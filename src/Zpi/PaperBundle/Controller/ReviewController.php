<?php    
namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\UserPaper;

use Zpi\UserBundle\Entity\User;

use Zpi\PaperBundle\Form\Type\ReviewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\PaperBundle\Entity\Review;
use Zpi\PaperBundle\Entity\ReviewComment;

/**
 * Kontroler dla klasy Review.
 * @author lyzkov
 */
class ReviewController extends Controller
{
    
    /**
     * Tworzy nową recenzję dla danego dokumentu.
     * @param Request $request
     * @param unknown_type $doc_id
     * @author lyzkov
     */
    //TODO Walidacja formularza.
    //TODO Dopracowanie widoku.
    //TODO Zabezpieczenie kontrolera. i ograniczenia na dodawanie recenzji.
    //TODO Rozróżnienie recenzji technicznej i zwykłej. Na podstawie routy? Na podstawie ról i wybór w przypadku ROLE_EDITOR i ROLE_TECH_EDITOR?
    public function newAction(Request $request, $doc_id)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route = $routeParameters['_route'];
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        //TODO Wypierdzielić tą zmienną sesyjną
        $status = $session->get('status');
        
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Document');
        $qb = $repository->createQueryBuilder('d')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registrations', 'r')
            ->innerJoin('r.conference', 'c')
            ->innerJoin('p.users', 'up')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id)
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId())
            ;
        
        $document = null;
        $reviewType = null;
        
        switch ($route)
        {
            case 'review_new':
                $query = $qb->andWhere('up.editor = TRUE')->getQuery();
                $document = $query->getOneOrNullResult();
                $reviewType = Review::TYPE_NORMAL;
                break;
            case 'tech_review_new':
                $query = $qb->andWhere('up.techEditor = TRUE')->getQuery();
                $document = $query->getOneOrNullResult();
                $reviewType = Review::TYPE_TECHNICAL;
                break;
            default:
                throw $this->createNotFoundException(
                    $translator->trans('exception.route_not_found: %route%', array('%route%' => $route)));
        }
        
        $review = new Review();
        $review->setType($reviewType);
        
        $form = $this->createForm(new ReviewType(), $review);
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
        
            if ($form->isValid())
            {
                $document->setStatus($status > $review->getMark() ? $review->getMark() : $status);
                $review->setEditor($user);
                $review->setDocument($document);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($review);
                $em->persist($document);
                $em->flush();
                $this->get('session')->setFlash('notice',
                    $translator->trans('review.new.success'));
        
                return $this->redirect($this->generateUrl('review_show', array('doc_id' => $doc_id)));
            }
        }
        
        $session->set('status', $status);
        
        return $this->render('ZpiPaperBundle:Review:new.html.twig',
            array('form' => $form->createView(), 'doc_id' => $doc_id, 'submit_path' => $route));
    }
    
    /**
     * Wyświetla wszystkie recenzje dotyczące danego dokumentu.
     * @param Request $request
     * @param unknown_type $doc_id
     * @author lyzkov
     */
    //TODO Optymalizacja zapytań!!!
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
//             ->innerJoin('r.document', 'd')
//             ->innerJoin('d.reviews', 'r')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registrations', 'reg')
            ->innerJoin('reg.conference', 'c')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id);
        
        $reviews = null;
        $document = null;
        $twigName = 'ZpiPaperBundle:Review:show.html.twig';
        $roles = array();
        $roles[] = User::ROLE_USER;
        
        //TODO Nieładny sposób sprawdzania roli: hasRole().
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
            if (!is_null($document))
            {
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
            $document = $query->getOneOrNullResult();
            if (!is_null($document))
            {
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
            if (is_null($document))
            {
                throw $this->createNotFoundException(
                    $translator->trans('review.exception.not_found: %id%',
                        array('%id%' => $doc_id)));
            }
        }
        
        $reviews = $document->getReviews();
        
        //TODO Nie powinno być przekazywanie statusu w sesji. Będzie najwyżej dużo zapytań do bazy.
        $status = Review::MARK_ACCEPTED;
        foreach ($reviews as $review)
        {
            $status = $status > $review->getMark() ? $review->getMark() : $status;
        }
        $status = $session->set('status', $status);
        
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
        
        return $this->render($twigName, array(
        	'reviews' => $reviews,
        	'tech_reviews' => $techReviews,
        	'document' => $document,
            'roles' => $roles));
    }
    
    /**
     * Wyświetla wybraną recenzję wraz z komentarzami.
     * @param Request $request
     * @param unknown_type $doc_id
     * @param unknown_type $review_id
     * @author lyzkov
     */
    //TODO Wyświetlanie komentarzy w twigu.
    //TODO Optymalizacja zapytań!!! - nie chce mi sie jakos tego mocno analizowac, ale nie mozna od razu pobrac tym wielkim
    // zapytaniem review? Jesli pobierze jakis rekord, to mam prawa, jesli nie to nie mam?
    public function commentAction(Request $request, $doc_id, $review_id)
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
//             ->innerJoin('r.document', 'd')
            ->innerJoin('d.reviews', 'r')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registrations', 'reg')
            ->innerJoin('reg.conference', 'c')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id)
                ->andWhere('r.id = :review_id')
                    ->setParameter('review_id', $review_id);
        
        $document = null;
        $review = null;
        $isFetched = false;
        $role = 0;
        
        if ($user->hasRole(User::ROLE_EDITOR) || $user->hasRole(User::ROLE_TECH_EDITOR))
        {
            $qb = clone $queryBuilder;
            $query = $qb
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.editor = TRUE OR up.techEditor = TRUE')
                ->getQuery();
            $document = $query->getOneOrNullResult();
            if (!is_null($document))
            {
                $role = 2;
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
                $role = 1;
                $isFetched = true;
            }
            else //TODO 404? Może być przypadek gdy nie ma takiej recenzji/dokumentu,
                // a może być też tak, że user nie organizuje danej konferencji.
            {
                throw $this->createNotFoundException(
                    $translator->trans('review.exception.not_found: %doc_id%, %review_id%',
                        array('%review_id%' => $review_id, '%doc_id%' => $doc_id)));
            }
        }
        //TODO Nie wiem czy tu powinno być 404, zasób jest na serwerze ale użytkownik nie ma prawa dostępu
        if (!$isFetched)
        {
            throw $this->createNotFoundException(
                $translator->trans('review.exception.not_found: %doc_id%, %review_id%',
                    array('%review_id%' => $review_id, '%doc_id%' => $doc_id)));
        }
        
        $review = $this->getDoctrine()->getRepository('ZpiPaperBundle:Review')
            ->find($review_id);
        
        // Nie będę tworzył form typa - wątpie żęby ten formularz się jeszcze gdzieś przydał.
        $comment = new ReviewComment();
        $form = $this->createFormBuilder($comment)
            ->add('content')
            ->getForm();
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
        
            if ($form->isValid())
            {
                $comment->setReview($review);
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
                                    'html' => $this->get('templating')->render('ZpiPaperBundle:Review:comment_body.html.twig', array('comment' => $comment)))));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                else
                {
                    $this->get('session')->setFlash('notice',
                        $translator->trans('reviewcomment.new.success'));

                    return $this->redirect($this->generateUrl('review_comment', array('doc_id' => $document->getId(), 'review_id' => $review->getId())));
                }
            }
        }
        
        foreach($review->getComments() as $comment)
        {
            $editForm = $this->createFormBuilder($comment)
            ->add('content')
            ->getForm();
            $comment->setEditForm($editForm->createView());
        }
        
        return $this->render('ZpiPaperBundle:Review:comment.html.twig', array(
        	'document' => $document,
        	'review' => $review,
            'role' => $role,
            'form' => $form->createView()));
    }
    
    public function commentEditAction(Request $request, $comment_id)
    {
        if($request->getMethod() == 'POST')
        {
            $translator = $this->get('translator');
            // sprawdzenie czy to moj komentarz
            $user = $this->get('security.context')->getToken()->getUser();
            $comment= $this->getDoctrine()->getRepository('ZpiPaperBundle:ReviewComment')
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
        $comment= $this->getDoctrine()->getRepository('ZpiPaperBundle:ReviewComment')
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
}