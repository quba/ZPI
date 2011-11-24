<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\PaperBundle\Form\Type\NewPaperType;
use Zpi\PaperBundle\Form\Type\EditPaperType;
use Zpi\PaperBundle\Form\Type\ChangePapersPaymentType;
use Zpi\PaperBundle\Entity\Review;


/**
 * Kontroler dla klasy Paper.
 * @author quba, lyzkov
 *
 */
class PaperController extends Controller
{   
    // Dla akcji new oraz edycji:
    // TODO: errory fajnie jakby się przy formularzach odpowiednich wyświetlały.
    // TODO: Ograniczenie do X autorów (bodajże 6 to max), jeszcze trzeba odpytać maf-a.
    // TODO: Zapytania do paperów muszą jeszcze joinować reg, zeby sprawdzic czy tycza sie dobrej konfy.
    // TODO: Nadpisać domyślne mapowanie FOSUserBundle, żeby pole email mogło być nullable.
    // TODO: Podpiąć jakąś sensowną validację
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
        
        // pobranie  wszystkich paperow aby sprawdzic, czy typ platnosci przynajmniej
        // jednego z nich jest FULL @Gecaj
        $papers = $registration->getPapers();
        $fullExists = false;
        foreach($papers as $paper)
        {
            if($paper->getPaymentType() == Paper::PAYMENT_TYPE_FULL)
                $fullExists = true;
        }
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
                $paper->setStatus(Review::MARK_NO_MARK);
                
                
                // Jezeli zadna praca nie ma typu platnosci full to ustawienie takowego
                // jezeli jakas ma to wowczas typ platnosci = extrapages
                if(!$fullExists)
                    $paper->setPaymentType (Paper::PAYMENT_TYPE_FULL);
                else
                    $paper->setPaymentType (Paper::PAYMENT_TYPE_EXTRAPAGES);
                     
                
                //$tmp = $paper->getAuthors();
                //$tmp2 = $paper->getAuthorsExisting();
                //$paper->delAuthors();
                //$paper->delAuthorsFromEmail();
                
                foreach ($paper->getAuthors() as $at)
                {
                    if(!empty($at['name']) && !empty($at['surname']))
                    {
                        $author = new User();
                        if(is_null($at['email']))
                            $author->setEmail(rand(1, 10000));
                        else
                        {
                            $emailCheck = $em->createQuery(
                            'SELECT u FROM ZpiUserBundle:User u
                                WHERE u.emailCanonical = :email'
                            )->setParameter('email', $at['email'])
                             ->getOneOrNullResult();
                            if(!is_null($emailCheck))
                                throw $this->createNotFoundException('Taki mail już istnieje, dodaj współautora używając opcji existing.');
                      
                            $author->setEmail($at['email']);
                            // wysylamy maila z linkiem uwzględniającym $author->getConfirmationToken();
                        }
                        $author->setType(USER::TYPE_COAUTHOR);
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
                            $message = \Swift_Message::newInstance()
                                ->setSubject('Zostałeś dodany jako współautor pracy ' . $paper->getTitle())
                                ->setFrom('zpimailer@gmail.com')
                               ->setTo($author->getEmail())
                           //   nie działa     
                           //     ->setTo('zpimailer@gmail.com')
                                ->setBody($this->renderView('ZpiPaperBundle:Paper:notify_author.txt.twig', array('username' => $author->getEmail(), 'title' => $paper->getTitle()) ));
                            $this->get('mailer')->send($message);
                            
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

                return $this->redirect($this->generateUrl('paper_details', array('id' => $paper->getId())));          
            }
        }    
        return $this->render('ZpiPaperBundle:Paper:new.html.twig', array('form' => $form->createView(), 'debug' => $debug));
    }
    
    public function editAction(Request $request, $id)
    {
        $debug = '';
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

        $form = $this->createForm(new EditPaperType(), $paper);
 
        if($request->getMethod() == 'POST')
	{         
            $form->bindRequest($request);

            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->get('security.context')->getToken()->getUser();
                
                $donttouch = array(); // będzie rewrite kodu jeszcze wiec nie sugerować sie nazwa
                
                // sprawdzamy, czy usunięto albo zmieniono jakichś autorów (tych z imienia i nazwiska)
                foreach($authors as $au)
                {
                    $delete = true;
                    foreach($paper->getAuthors() as $at)
                    {
                        // obiekty, ktore pobralem z bazy i przypisalem do formularza sa traktowane jako obiekt, natomiast
                        // obiekty wpisane bezposrednio na nowo, sa traktowane jako tablice, stąd to rozróżnienie.
                        if(is_array($at))
                        {
                            $name = $at['name'];
                            $surname = $at['surname'];
                        }
                        else
                        {
                            $name = $at->getName();
                            $surname = $at->getSurname();
                        }
                            
                        if($name == $au->getName() && $surname == $au->getSurname())
                        {
                            $delete = false;
                            $donttouch[] = $at;
                        }
                    }
                    
                    if($delete)
                    {                        
                        $up = $em->getRepository('ZpiPaperBundle:UserPaper')
                                 ->findOneBy(array(
                                     'user' => $au->getId(),
                                     'paper'=> $paper->getId()
                                 ));
                        
                        // usuwamy powiązanie z paperem i samego autora
                        $em->remove($up);
                        $em->remove($au);
                    }
                }
                
                $authorsNames = array(); // bufor do sprawdzania, czy nie podajemy 2 razy tych samych danych
                
                foreach ($paper->getAuthors() as $at)
                {
                    if(is_array($at))
                    {
                        $name = $at['name'];
                        $surname = $at['surname'];
                        $email = $at['email'];
                    }
                    else
                    {
                        $name = $at->getName();
                        $surname = $at->getSurname();
                        $email = $at->getEmail();
                    }
                    
                    if(in_array(array($name, $surname), $authorsNames))
                    {
                        throw $this->createNotFoundException('Dobra, ale po co dodajesz jednego zioma 2 razy?');
                    }
                    
                    $authorsNames[] = array($name, $surname);
                    
                    if(in_array($at, $donttouch))
                    {
                            continue;
                    }
                    
                    if(!is_null($name) && !is_null($surname))
                    {
                        $author = new User();
                        if(is_null($email))
                            $author->setEmail(rand(1, 10000));
                        else
                        {
                            $emailCheck = $em->createQuery(
                            'SELECT u FROM ZpiUserBundle:User u
                                WHERE u.emailCanonical = :email'
                            )->setParameter('email', $email)
                             ->getOneOrNullResult();
                            if(!is_null($emailCheck))
                                throw $this->createNotFoundException('Taki mail już istnieje, dodaj współautora używając opcji existing.');
                      
                            $author->setEmail($email);
                            // wysylamy maila z linkiem uwzględniającym $author->getConfirmationToken();
                        }
                        $author->setType(USER::TYPE_COAUTHOR);
                        $author->setAlgorithm('');
                        $author->setPassword('');
                        $author->setName($name);
                        $author->setSurname($surname);
                        $paper->addAuthor($author);
                    }
                }
                
                
                
                
                $donttouch = array(); // zerujemy tę tablicę, będzie rewrite kodu jeszcze, więc spoko :D
                
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
                         $up = $em->getRepository('ZpiPaperBundle:UserPaper')
                                 ->findOneBy(array(
                                     'user' => $ae->getId(),
                                     'paper'=> $paper->getId()
                                 ));

                        $em->remove($up);
                    }
                }
                
                $authorsEmails = array(); // taki bufor do sprawdzania, czy nie podajemy 2 razy tej samej osoby
                
                // dodanie nowych współautorów po emailu
                foreach ($paper->getAuthorsExisting() as $at)
                {
                    if(is_array($at))
                        $email = $at['email'];
                    else
                        $email = $at->getEmail();
                    
                    if(in_array($email, $authorsEmails))
                    {
                        throw $this->createNotFoundException('Dobra, ale po co dodajesz jednego zioma 2 razy?');
                    }
                    
                    $authorsEmails[] = $email;
                    if(in_array($at, $donttouch))
                            continue;

                    if(!is_null($email))
                    {
                        if($email == $user->getEmailCanonical())
                        {
                            throw $this->createNotFoundException('Nie musisz dodawać siebie samego, to się stanie z automatu');
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
                            $message = \Swift_Message::newInstance()
                                ->setSubject('Zostałeś dodany jako współautor pracy ' . $paper->getTitle())
                                ->setFrom('zpimailer@gmail.com')
                               ->setTo($author->getEmail())
                           //   nie działa     
                           //     ->setTo('zpimailer@gmail.com')
                                ->setBody($this->renderView('ZpiPaperBundle:Paper:notify_author.txt.twig', array('username' => $author->getEmail(), 'title' => $paper->getTitle()) ));
                            $this->get('mailer')->send($message);
                            $paper->addAuthorExisting($author); // nie ma wyjątków, można jechać z koksem
                        }            
                    } // nie ma else, puste pola po prostu ignorujemy
                } 
                         // tak, też bym sobie życzył pracować na funkcjach helperach, a nie zapytaniach
                         // ale na razie nie mamy na to czasów ani nerwów. Potem się doda User repository
                         // i np. funkcję findUserByEmail ;)
                
                $em->flush();

                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, your action succeeded!');
                $cos = $paper->getAuthorsExisting();
                //$debug .= $cos[0];
                //$debug .= print_r($_POST, true);
                return $this->redirect($this->generateUrl('paper_details', array('id' => $paper->getId())));          
            }
        }    
        return $this->render('ZpiPaperBundle:Paper:edit.html.twig', array('form' => $form->createView(), 'debug' => $debug, 'paper' => $paper));
    }
    
    /**
     * Wyświetla listę papierów.
     * @param Request $request
     * @author quba, lyzkov, Gecaj
     * TODO Dodanie informacji o wersji i o statusie ostatniej recenzji.
     * TODO Sprawdzenie czy działa dla wszystkich requestów (w zależności od routy i od roli użytkownika)
     */
    public function listAction(Request $request)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route = $routeParameters['_route'];
        
        
        $conference = $request->getSession()->get('conference');
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
        $qb = $repository->createQueryBuilder('p')
            ->innerJoin('p.registrations', 'r')
            ->innerJoin('r.conference', 'c')
            ->innerJoin('p.users', 'up')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId());
        
        // W zależności od tego z jakiej rout'y weszliśmy pobierzemy
        // inną kolekcję papierów (autorstwa/do recenzji/do zarządzania). :) @lyzkov
        // podział prac na niezaakceptowane/ nieprzeslane /oczekujace na ocene
        // jedna z ocen nizsza od ACCEPTED
        $nonaccepted_papers = array();

        // oczekujace na ocene
        $waiting_papers = array();

        // nie przesłane prace
        $nonsubmitted_papers = array();
                
        // zaakceptowane
        $accepted_papers = array();
        switch ($route)
        {
            case 'papers_list': //TODO Zabezpieczyć akcje dla niezgłoszonych uczestników
                $query = $qb
                        ->andWhere('up.author = :author')
                            ->setParameter('author', UserPaper::TYPE_AUTHOR_EXISTING)
                    ->getQuery();
	            $papers = $query->getResult();                
                
                
                foreach($papers as $paper)
                {
                    $hasDocument = false;

                    foreach($paper->getDocuments() as $document)
                    {
                        $hasDocument = true;
                        // najgorsza ocena jest wiazaca
                        $worst_technical_mark = Review::MARK_ACCEPTED;
                        $worst_normal_mark = Review::MARK_ACCEPTED;

                        // czy istnieje przynajmniej jedna ocena kazdego typu
                        $exist_technical = false;
                        $exist_normal = false;

                        foreach($document->getReviews() as $review)
                        {
                            if(!$exist_normal && $review->getType() == 0)
                                    $exist_normal = true;
                            else if(!$exist_technical && $review->getType() == 1)
                                    $exist_technical = true;

                            if($review->getType() == REVIEW::TYPE_NORMAL && $review->getMark() < $worst_normal_mark)
                            {

                                $worst_normal_mark = $review->getMark();
                            }
                            else if($review->getType() == Review::TYPE_TECHNICAL && $review->getMark() < $worst_technical_mark)
                            {

                                $worst_technical_mark = $review->getMark();
                            }
                        }

                        // jezeli choc jednego typu oceny dokument nie posiada
                        // dodawany do oczekujacych na ocene
                        if(!($exist_normal && $exist_technical))
                        {
                            $waiting_papers[] = $paper;
                        }  
                        // jezeli obydwie najnizsze oceny sa 'accepted' papery moga byc drukowane - liczenie cen
                        else if($worst_normal_mark == Review::MARK_ACCEPTED && $worst_technical_mark == Review::MARK_ACCEPTED)
                        {
                            if($document->getPagesCount() >= $conference->getMinPageSize())
                            {
                                $accepted_papers[] = $paper;
                            }
                        }
                        // w przeciwnym wypadku paper nie jest zaakceptowany do druku
                        else
                        {
                            $nonaccepted_papers[] = $paper;
                        }

                    }
                    // Jeżeli nie przesłał żadnego dokumenty a zarejestrował abstrakt
                    if(!$hasDocument)
                    {

                        $nonsubmitted_papers[] = $paper;
                    }
                }
	            return $this->render('ZpiPaperBundle:Paper:list.html.twig', array(
	            	'nonaccepted_papers' => $nonaccepted_papers,
                    'nonsubmitted_papers' => $nonsubmitted_papers,
                    'waiting_papers' => $waiting_papers,
                    'accepted_papers' => $accepted_papers,
                    'papers' => array()));
            case 'conference_manage':
                $query = $qb->getQuery();
                $papers = $query->getResult();
                return $this->render('ZpiConferenceBundle:Conference:list_papers.html.twig', array(
                    'nonaccepted_papers' => $nonaccepted_papers,
                    'nonsubmitted_papers' => $nonsubmitted_papers,
                    'waiting_papers' => $waiting_papers,
                    'accepted_papers' => $accepted_papers,
                	'papers' => $papers));
            case 'reviews_list':
                $query = $qb->andWhere('up.editor = TRUE OR up.techEditor = TRUE')->getQuery();
                $papers = $query->getResult();
                return $this->render('ZpiPaperBundle:Review:list.html.twig', array(
                	'papers' => $papers, 'path_details'));
            default:
                throw $this->createNotFoundException(
                    $translator->trans('exception.route_not_found: %route%', array('%route%' => $route)));
        }
    }
    
    /**
     * Wyświetla szczegóły papieru.
     * @param Request $request
     * @param unknown_type $id
     * @author lyzkov
     * TODO Dodanie informacji o wersji, daty, uploadera i komentarza.
     */
    public function detailsAction(Request $request, $id)
    {
        $securityContext = $this->get('security.context');
        $user = $securityContext->getToken()->getUser();
    
        //TODO Autoryzacja użytkownika.
    
        $translator = $this->get('translator');
    
        $path = $request->getPathInfo();
        $router = $this->get('router');
        $routeParameters = $router->match($path);
        $route = $routeParameters['_route'];
        
        $conference = $request->getSession()->get('conference');
        
        // Zapytanie zwracające papier o danym id powiązany z użytkownikiem i konferencją
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
        $queryBuilder = $repository->createQueryBuilder('p')
            ->innerJoin('p.registrations', 'r')
            ->innerJoin('r.conference', 'c')
            ->innerJoin('p.users', 'up')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId())
                ->andWhere('p.id = :paper_id')
                    ->setParameter('paper_id', $id);
        
        $paper = null;
        $twigName = 'ZpiPaperBundle:Paper:details.html.twig';
        
        //TODO Dodać stałe dla author, editor i techEditor w zapytaniach.
        switch ($route)
        {
            case 'paper_details':
                $query = $queryBuilder->andWhere('up.author = :auth')
                    ->setParameter('auth', UserPaper::TYPE_AUTHOR_EXISTING)
                    ->getQuery();
                $paper = $query->getOneOrNullResult();
                $twigName = 'ZpiPaperBundle:Paper:details_upload.html.twig';
                break;
            case 'review_details':
                $query = $queryBuilder->andWhere('up.editor = TRUE OR up.techEditor = TRUE')
                    ->getQuery();
                $paper = $query->getOneOrNullResult();
                break;
            case 'conference_manage_paper_details':
                $query = $queryBuilder->getQuery();
                $paper = $query->getOneOrNullResult();
                break;
            default:
                throw $this->createNotFoundException(
                    $translator->trans('exception.route_not_found'));
        }
        
        //TODO Na razie błąd 404. // eee.. bo tutaj akurat ma być błąd 404 - taka jest jego specyfika // @quba
        if (is_null($paper))
        {
            throw $this->createNotFoundException(
                $translator->trans('paper.exception.paper_not_found: %id%',
                    array('%id%' => $id)));
        }
        
        return $this->render($twigName, array('paper' => $paper));
    }
    public function changePaymentTypeAction(Request $request)
    {
        //$paper = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper')->find($paper_id);
        $conference = $this->getRequest()->getSession()->get('conference');
        $translator = $this->get('translator');
        
        // Pobranie rejestracji dla danego uzytkownika
        
        $registration = $this->getDoctrine()
                ->getRepository('ZpiConferenceBundle:Registration')
                ->createQueryBuilder('r')                
                ->where('r.conference = :conf_id')
                ->setParameter('conf_id', $conference->getId())
                ->andWhere('r.participant = :user_id')
                ->setParameter('user_id', 
                        $user = $this->get('security.context')->getToken()->getUser()->getId())
                ->getQuery()
                ->getOneOrNullResult();
        
        // Pobranie paperow, za ktore jest zobowiazany zaplacic
        $papers = $registration->getPapers();
        $form = $this->createForm(new ChangePapersPaymentType(), $registration);
        
        
        
   
        if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);			
			if ($form->isValid())
			{		
                $this->getDoctrine()->getEntityManager()->flush();
                $this->get('session')->setFlash('notice', 
		        		$translator->trans('paper.success.change_paymenttype'));	
				
                return $this->redirect($this->generateUrl('papers_list', 
                                        array('_conf' => $conference->getPrefix())));
					
			}
		}
        
        return $this->render('ZpiPaperBundle:Paper:changePaymentType.html.twig', array(
            'form' => $form->createView(),'papers' => $papers, 'registration' => $registration));
        
    }
}
