<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zpi\PaperBundle\Form\Type\NewPaperType;


class PaperController extends Controller
{
    // TODO: errory fajnie jakby się przy formularzach odpowiednich wyświetlały
    // TODO: Ograniczenie do X autorów (bodajże 6 to max), jeszcze trzeba odpytać maf-a.
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
                    /*
                    if(is_null($at->getName()) || is_null($at->getSurname()))
                    {
                        $em->remove($at);
                    }
                        */
                }
                
                $donttouch = array();
                // sprawdzenie, czy czasem nie zostal usunięty jakiś autor po mailu
                foreach($authorsExisting as $ae)
                {
                    $delete = true;
                    foreach($paper->getAuthorsExisting() as $at)
                    {
                        if(is_array($at))
                            $email = $at['email'];
                        else
                            $email = $at->getEmail();
                            
                        if($email == $ae->getEmail())
                        {
                            $delete = false;
                            $donttouch[] = $at;
                        }
                    }
                    
                    if($delete)
                    {
                        //usuwamy powiązanie tego autora z paperem
                        echo 'usuwam ' . $ae->getEmail() . '<br />';
                        /*
                        $up = $em->createQuery(
                            'SELECT up FROM ZpiPaperBundle:UserPaper up
                                WHERE up.user = :id AND up.paper = :paper'
                            )->setParameter('id', $ae->getId())
                             ->setParameter('paper', $paper->getId())
                             ->execute();
                         */
                         $up = $em->getRepository('ZpiPaperBundle:UserPaper')
                                 ->findOneBy(array(
                                     'user' => $ae->getId(),
                                     'paper'=> $paper->getId()
                                 ));
                        //echo $up->getPaper()->getAbstract();
                        $em->remove($up);
                        //$em->
                        //$paper->getAuthorsExisting()->remove($ae);
                    }
                }
                
                $authorsEmails = array(); // taki bufor do sprawdzania, czy nie podajemy 2 razy tej samej osoby
                
                // dodanie nowych współautorów po emailu
                foreach ($paper->getAuthorsExisting() as $at)
                {
                    if(in_array($at, $donttouch))
                            continue;
                    
                    if(is_array($at))
                        $email = $at['email'];
                    else
                        $email = $at->getEmail();
                    
                    if(!is_null($email))
                    {
                        if($email == $user->getEmailCanonical())
                        {
                            throw $this->createNotFoundException('Nie musisz dodawać siebie samego, to się stanie z automatu');
                        }
                        
                        if(in_array($email, $authorsEmails))
                        {
                            throw $this->createNotFoundException('Dobra, ale po co dodajesz jednego zioma 2 razy?');
                        }

                        $author = $em->createQuery(
                            'SELECT u FROM ZpiUserBundle:User u
                                WHERE u.emailCanonical = :email'
                            )->setParameter('email', $email)
                             ->getOneOrNullResult();
                        if(empty($author))
                        {
                            throw $this->createNotFoundException('Nie ma takiego autora zią?!'); // na razie tak, pozniej sie zmieni
                        }
                        else // okej mamy zioma, teraz wypada sprawdzić, czy już nie ma przydzielonej tej pracy
                        {
                            /*
                            sprawdzanie czy wpis juz istnieje w bazie nie ma sensu, bo jesli istnieje, to wyswietli sie na liscie,
                            a dodanie kolejnego takiego samego bedzie obsłużone tak jak przy dodawaniu nowego - errorem.
                            
                            $up = $em->createQuery(
                            'SELECT up FROM ZpiPaperBundle:UserPaper up
                                WHERE up.user = :id AND up.paper = :paper'
                            )->setParameter('id', $author->getId())
                             ->setParameter('paper', $paper->getId())
                             ->execute();
                            $debug = $paper->getId();

                            
                            */
                            $paper->addAuthorExisting($author); // nie ma wyjątków, można jechać z koksem
                        }            
                    }
                    $authorsEmails[] = $email;
                } 
                         // tak, też bym sobie życzył pracować na funkcjach helperach, a nie zapytaniach
                         // ale na razie nie mamy na to czasów ani nerwów. Potem się doda User repository
                         // i np. funkcję findUserByEmail ;)
                
                $em->flush();

                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, your action succeeded!');

                //return $this->redirect($this->generateUrl('papers_show'));          
            }
        }    
        return $this->render('ZpiPaperBundle:Paper:edit.html.twig', array('form' => $form->createView(), 'debug' => $debug, 'paper' => $paper));
    }
    
    public function showAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
	/*$papers = $user->getAuthorPapers(); 
         * funkcja ta niestety pobiera tylko dane z tabeli users_papers i jest to prawidłowe działanie, bo mamy tam one to 
         * many i nie przeskoczymy przez tę tabelę łatwo (żeby pobrać title i inne z papers). Próbowałem tworzyć tzw. proxy
         * metodę w UserPaper (która zwracała getPaper()->getTitle()), jednak to rozwiązanie nie jest wg mnie zbyt wydajne.
         * Owszem działa, ale np. pobranie wszystkich nazw paperów danego usera (standardowe getAuthorPapers()) skutkuje
         * najpierw pobraniem wszystkich rekordów danego usera o typie 0 z tabeli users_papers (OK), jednak potem gdy chcemy
         * odwołać się do tytułu papera (nie ma go w users_papers), korzysta ze stworzonej przeze mnie proxy metody tej klasy
         * o nazwie getTitle(). Wszystko jest spoko, jednak daje nam to dla każdego elementu usera z users_papers dodatkowe
         * zapytanie, które pobiera ten tytuł. Rozwiązanie schludne i wygodne, jednak przydatne tylko przy pobieraniu jednego
         * rekordu (choć i tutaj bym się zastanawiał, bo mamy 2 zapytania, a można to zrobić jednym inner joinem).
         * Dyskusja na ten temat oraz coś o proxy metodzie dla tej asocjacji: 
         * http://stackoverflow.com/questions/3542243/doctrine2-best-way-to-handle-many-to-many-with-extra-columns-in-reference-table
         */
        
        $papers = $this->getDoctrine()->getEntityManager()
                ->createQuery('SELECT p, up FROM ZpiPaperBundle:UserPaper up INNER JOIN up.paper p where up.user = :uid')
                ->setParameter('uid', $user->getId())
                ->execute();
        
	return $this->render('ZpiPaperBundle:Paper:show.html.twig', array('papers' => $papers));
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
