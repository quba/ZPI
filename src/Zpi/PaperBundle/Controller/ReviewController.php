<?php    
namespace Zpi\PaperBundle\Controller;

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
     * Tworzy nową recenzję dla danego dokumentu.
     * @param Request $request
     * @param unknown_type $doc_id
     * @author lyzkov
     * TODO Walidacja formularza.
     * TODO Dopracowanie widoku.
     * TODO Zabezpieczenie kontrolera. i ograniczenia na dodawanie recenzji.
     * TODO Rozróżnienie recenzji technicznej i zwykłej. Na podstawie routy? Na podstawie ról i wybór w przypadku ROLE_EDITOR i ROLE_TECH_EDITOR?
     */
    public function newAction(Request $request, $doc_id)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        //TODO Co jeśli nie przejdziemy do newAction z showAction? Czy potrzebne zapytanie?
        $session = $request->getSession();
        $conference = $session->get('conference');
        $status = $session->get('status');
        //TODO Zabezpieczyć kontroler. Będzie potężne zapytanie.
        
        $review = new Review();
        
        $form = $this->createForm(new ReviewType(), $review);
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
        
            if ($form->isValid())
            {
                $document = $this->getDoctrine()->getRepository('ZpiPaperBundle:Document')
                    ->find($doc_id);
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
            array('form' => $form->createView(), 'doc_id' => $doc_id));
    }
    
    /**
     * Wyświetla wszystkie recenzje dotyczące danego dokumentu.
     * @param Request $request
     * @param unknown_type $doc_id
     * @author lyzkov
     * TODO Wyświetlanie komentarzy/dyskusji do recenzji.
     */
    public function showAction(Request $request, $doc_id)
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
        
        // Zapytanie zwracające papier o danym id powiązany z użytkownikiem i konferencją.
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Review');
        $queryBuilder = $repository->createQueryBuilder('r')
            ->innerJoin('r.document', 'd')
            ->innerJoin('d.paper', 'p')
            ->innerJoin('p.registrations', 'reg')
            ->innerJoin('reg.conference', 'c')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('d.id = :doc_id')
                    ->setParameter('doc_id', $doc_id);
        
        $reviews = null;
        $twigName = 'ZpiPaperBundle:Review:show.html.twig';
        
        //TODO Nieładny sposób sprawdzania roli: hasRole().
        if ($user->hasRole(User::ROLE_EDITOR) || $user->hasRole(User::ROLE_TECH_EDITOR))
        {
            $query = $queryBuilder
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.editor = 1 OR up.techEditor = 1')
                ->getQuery();
            $reviews = $query->getResult();
            $twigName = 'ZpiPaperBundle:Review:show_editor.html.twig';
        }
        elseif ($user->hasRole(User::ROLE_ORGANIZER))
        {
            $query = $queryBuilder
                ->getQuery();
            $reviews = $query->getResult();
            $twigName = 'ZpiPaperBundle:Review:show_organizer.html.twig';
        }
        else
        {
            $query = $queryBuilder
                ->innerJoin('p.users', 'up')
                    ->andWhere('up.user = :user_id')
                        ->setParameter('user_id', $user->getId())
                    ->andWhere('up.author = 1')
                ->getQuery();
            $reviews = $query->getResult();
        }
        
        //TODO Na razie błąd 404.
        if (is_null($reviews))
        {
            throw $this->createNotFoundException(
                $translator->trans('paper.exception.paper_not_found: %id%',
                    array('%id%' => $id)));
        }
        
        //TODO Trochę nieoptymalne ale nie widzę na razie
        // innej opcji przy pustej kolekcji $reviews
        $document = $this->getDoctrine()->getRepository('ZpiPaperBundle:Document')->find($doc_id);
        
        $status = 2;
        foreach ($reviews as $review)
        {
            $status = $status > $review->getMark() ? $review->getMark() : $status;
        }
        $status = $session->set('status', $status);
        
        $techReviews = array();
        
        for ($i = 0; $i < count($reviews); $i++)
        {
            if ($reviews[$i]->getType() == Review::TYPE_TECHNICAL)
            {
                $techReviews[] = $reviews[$i];
                unset($reviews[$i]);
            }
        }
        
        return $this->render($twigName, array('reviews' => $reviews, 'tech_reviews' => $techReviews, 'document' => $document));
    }
    
}