<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\PaperBundle\Form\Type\NewPaperType;


class PaperController extends Controller
{
    // TODO: errory fajnie jakby się przy formularzach odpowiednich wyświetlały
    public function newAction(Request $request)
    {
        $debug = 'debug';
        $translator = $this->get('translator');
        $user = $this->get('security.context')->getToken()->getUser();
        $conference = $request->getSession()->get('conference');
        $em = $this->getDoctrine()->getEntityManager();
        $registration = $em
            ->createQuery('SELECT r FROM ZpiConferenceBundle:Registration r WHERE r.participant = :user AND r.conference = :conf')
            ->setParameters(array(
                'user' => $user->getId(),
                'conf' => $conference->getId()
            ))->getOneOrNullResult();
        if(empty($registration))
            throw $this->createNotFoundException($translator->trans('pap.err.notregistered'));
        
        $paper = new Paper();
        $form = $this->createForm(new NewPaperType(), $paper);

        if ($request->getMethod() == 'POST')
	{          
            $form->bindRequest($request);

            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->get('security.context')->getToken()->getUser();
                $paper->setOwner($user);
                
                //$tmp = $paper->getAuthors();
                //$tmp2 = $paper->getAuthorsExisting();
                //$paper->delAuthors();
                //$paper->delAuthorsFromEmail();
                
                foreach ($paper->getAuthors() as $at)
                {
                    if(!empty($at['name']) && !empty($at['surname']))
                    {
                        $author = new User();
                        $author->setEmail(rand(1, 10000));
                        $author->setAlgorithm('');
                        $author->setPassword('');
                        $author->setName($at['name']);
                        $author->setSurname($at['surname']);
                        $paper->addAuthor($author);
                    }
                    else
                        throw $this->createNotFoundException('Jak chcesz dodać autora, to podaj jego dane.');
                }
                
                $authorsEmails = array(); // taki bufor do sprawdzania, czy nie podajemy 2 razy tej samej osoby
                
                foreach ($paper->getAuthorsExisting() as $at)
                {
                    if(!empty($at['email']))
                    {
                        if($at['email'] == $user->getEmailCanonical())
                        {
                            throw $this->createNotFoundException('Nie musisz dodawać siebie samego, to się stanie z automatu');
                        }
                        
                        if(in_array($at['email'], $authorsEmails))
                        {
                            throw $this->createNotFoundException('Dobra, ale po co dodajesz jednego zioma 2 razy?');
                        }
                        
                        $author = $em->createQuery(
                            'SELECT u FROM ZpiUserBundle:User u
                                WHERE u.emailCanonical = :email'
                            )->setParameter('email', $at['email'])
                             ->getOneOrNullResult();
                        if(empty($author))
                        {
                            throw $this->createNotFoundException('Nie ma takiego autora zią?!'); // na razie tak, pozniej sie zmieni
                        }
                        else // okej mamy zioma, teraz wypada sprawdzić, czy już nie ma przydzielonej tej pracy
                        {
                            /*
                            $up = $em->createQuery(
                            'SELECT up FROM ZpiPaperBundle:UserPaper up
                                WHERE up.user = :id AND up.paper = :paper'
                            )->setParameter('id', $author->getId())
                             ->setParameter('paper', $paper->getId())
                             ->execute();
                            $debug = $paper->getId();
                            to jest zabawa na edycję. Teraz mamy nowy paper to na pewno nie ma go przydzielonego nikt
                            poza osobami, które dodałem w obecnym formularzu, tak więc to zaraz sprawdzimy
                            */

                            $paper->addAuthorExisting($author); // nie ma wyjątków, można jechać z koksem
                        }            
                    }
                    $authorsEmails[] = $at['email'];
                }
                         // tak, też bym sobie życzył pracować na funkcjach helperach, a nie zapytaniach
                         // ale na razie nie mamy na to czasów ani nerwów. Potem się doda User repository
                         // i np. funkcję findUserByEmail ;)
                
                $paper->addAuthorExisting($user); // wszystko ok, dodajmy wiec tego papera aktualnie zalogowanemu
                $registration->addPaper($paper);
                $em->persist($paper);
                $em->flush();
                $cos = $form->getData();
                $debug .= print_r($paper->getAuthors(), true) . '<br /><br />' . print_r($paper->getAuthorsExisting(), true);

                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, your action succeeded!');

                //return $this->redirect($this->generateUrl('papers_show'));          
            }
        }    
        return $this->render('ZpiPaperBundle:Paper:new.html.twig', array('form' => $form->createView(), 'debug' => $debug));
    }
    
    public function editAction(Request $request, $id)
    {
        $debug = 'debug';
        $translator = $this->get('translator');
        $user = $this->get('security.context')->getToken()->getUser();
        $conference = $request->getSession()->get('conference');
        $em = $this->getDoctrine()->getEntityManager();
        $registration = $em
            ->createQuery('SELECT r FROM ZpiConferenceBundle:Registration r WHERE r.participant = :user AND r.conference = :conf')
            ->setParameters(array(
                'user' => $user->getId(),
                'conf' => $conference->getId()
            ))->getOneOrNullResult();
        if(empty($registration))
            throw $this->createNotFoundException($translator->trans('pap.err.notregistered'));
        
        
        $paper = $em->getRepository('ZpiPaperBundle:Paper')->find($id);
        
        if(empty($paper))
            throw $this->createNotFoundException($translator->trans('pap.err.notfound'));
        
        $authors = $em
            ->createQuery('SELECT u, up FROM ZpiUserBundle:User u INNER JOIN u.papers up 
                WHERE up.paper = :paper AND up.author=1')
            ->setParameters(array(
                'paper' => $paper->getId()
            ))->execute();
        $paper->setAuthors($authors);
        
        $authorsExisting = $em
            ->createQuery('SELECT u, up FROM ZpiUserBundle:User u INNER JOIN u.papers up 
                WHERE up.paper = :paper AND up.author=2 AND u.emailCanonical <> :emailCanonical')
            ->setParameters(array(
                'paper' => $paper->getId(),
                'emailCanonical' => $user->getEmailCanonical()
            ))->execute();
        $paper->setAuthorsExisting($authorsExisting);

        $form = $this->createForm(new NewPaperType(), $paper);
 
        if($request->getMethod() == 'POST')
	{         
            $form->bindRequest($request);

            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->get('security.context')->getToken()->getUser();
                
                foreach ($paper->getAuthors() as $at)
                {
                    if(!empty($at['name']) || !empty($at['surname']))
                    {
                        $author = new User();
                        $author->setEmail(rand(1, 1000));
                        $author->setAlgorithm('');
                        $author->setPassword('');
                        $author->setName($at['name']);
                        $author->setSurname($at['surname']);
                        $paper->addAuthor($author);
                    }
                }
                
                $authorsEmails = array(); // taki bufor do sprawdzania, czy nie podajemy 2 razy tej samej osoby
                
                foreach ($paper->getAuthorsExisting()as $at)
                {
                    if(!empty($at['email']))
                    {
                        if($at['email'] == $user->getEmailCanonical())
                        {
                            throw $this->createNotFoundException('Nie musisz dodawać siebie samego, to się stanie z automatu');
                        }
                        
                        if(in_array($at['email'], $authorsEmails))
                        {
                            throw $this->createNotFoundException('Dobra, ale po co dodajesz jednego zioma 2 razy?');
                        }
                        
                        $author = $em->createQuery(
                            'SELECT u FROM ZpiUserBundle:User u
                                WHERE u.emailCanonical = :email'
                            )->setParameter('email', $at['email'])
                             ->getOneOrNullResult();
                        if(empty($author))
                        {
                            throw $this->createNotFoundException('Nie ma takiego autora zią?!'); // na razie tak, pozniej sie zmieni
                        }
                        else // okej mamy zioma, teraz wypada sprawdzić, czy już nie ma przydzielonej tej pracy
                        {
                            /*
                            $up = $em->createQuery(
                            'SELECT up FROM ZpiPaperBundle:UserPaper up
                                WHERE up.user = :id AND up.paper = :paper'
                            )->setParameter('id', $author->getId())
                             ->setParameter('paper', $paper->getId())
                             ->execute();
                            $debug = $paper->getId();
                            to jest zabawa na edycję. Teraz mamy nowy paper to na pewno nie ma go przydzielonego nikt
                            poza osobami, które dodałem w obecnym formularzu, tak więc to zaraz sprawdzimy
                            
                            */
                            $paper->addAuthor($author); // nie ma wyjątków, można jechać z koksem
                        }            
                    }
                    $authorsEmails[] = $at['email'];
                } 
                         // tak, też bym sobie życzył pracować na funkcjach helperach, a nie zapytaniach
                         // ale na razie nie mamy na to czasów ani nerwów. Potem się doda User repository
                         // i np. funkcję findUserByEmail ;)
                
                $em->persist($paper);
                //$em->flush();
                $cos = $form->getData();
                $debug .= print_r($paper->getAuthors(), true) . '<br /><br />' . print_r($paper->getAuthorsExisting(), true);

                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, your action succeeded!');

                //return $this->redirect($this->generateUrl('papers_show'));          
            }
        }    
        return $this->render('ZpiPaperBundle:Paper:edit.html.twig', array('form' => $form->createView(), 'debug' => $debug, 'paper' => $paper));
    }
    
    /**
     * Wyświetla listę papierów.
     * @param Request $request
     * @author quba, lyzkov
     */
    public function listAction(Request $request)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $translator = $this->get('translator');
        
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route = $routeParameters['_route'];
        
//         print_r($routeParameters);
        
        $papers = array();
        
        // W zależności od tego z jakiej rout'y weszliśmy pobierzemy
        // inną kolekcję papierów (autorstwa/do recenzji/do zarządzania). :) @lyzkov
        switch ($route)
        {
            case 'papers_list':
	            $papers = $user->getAuthorsPapers();
	            return $this->render('ZpiPaperBundle:Paper:list.html.twig', array('papers' => $papers));
            case 'conference_manage':
                $conference = $request->getSession()->get('conference');
                $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
                $query = $repository->createQueryBuilder('p')
                            ->innerJoin('p.registrations', 'r')
                            ->innerJoin('r.conference', 'c')
                            ->where('c.id = :conf_id')
                            ->setParameter('conf_id', $conference->getId())
                ->getQuery();
                $papers = $query->getResult();
                
//                 $twig = $this->get('twig');
//                 $template = $twig->loadTemplate('ZpiConferenceBundle:Conference:list_papers.html.twig');
// 	            return $response = new Response($template->renderBlock('body', array('papers' => $papers)));
                
                return $this->render('ZpiConferenceBundle:Conference:list_papers.html.twig', array('papers' => $papers));
            case 'reviews_list':
                $papersToReview = $user->getEditorsPapers();
                $papersToTechReview = $user->getTechEditorsPapers();
                return $this->render('ZpiPaperBundle:Review:list.html.twig',
                    array('papersToReview' => $papersToReview,
                        'papersToTechReview' => $papersToTechReview));
        }
    }
    
    public function detailsAction($id)
    {
	$user = $this->get('security.context')->getToken()->getUser();
        
        $paper = $this->getDoctrine()->getEntityManager()->createQuery(
            'SELECT p, up FROM ZpiPaperBundle:UserPaper up INNER JOIN up.paper p
                WHERE up.paper = :id AND up.user = :uid'
            )->setParameter('id', $id)
             ->setParameter('uid', $user->getId()) // nie chcemy, żeby inni userzy widzieli narze pejpery
             ->getSingleResult();
        /* nie mam już nerwów, żeby z jakichś querybuilderów korzystać. Takie zapytanie jest najbardziej optymalne,
           a jak ktoś znajdzie, jak je wywołać jakoś bezpośrednio pobierając np. $user->getPaper($id) to ma ode mnie
           browara i dodatniego plusa. */
	
	if(!$paper)
	{
            throw $this->createNotFoundException('Not Found, You mad?!');
	}
        
        $documents = $this->getDoctrine()->getEntityManager()->getRepository('ZpiPaperBundle:Document')
						->findBy(array('paper' => $id));
                
	return $this->render('ZpiPaperBundle:Paper:details.html.twig', array('paper' => $paper, 'documents' => $documents));
    }
}
