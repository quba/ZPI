<?php

namespace Zpi\ConferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\ConferenceBundle\Entity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\ConferenceBundle\Form\Type\RegistrationFormType;
use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\Review;

class RegistrationController extends Controller
{
    public function sendMail($user, $name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($name)
            ->setFrom('zpimailer@gmail.com')
           ->setTo($user->getEmail() )
       //   nie działa     
       //     ->setTo('zpimailer@gmail.com')
            ->setBody($this->renderView('ZpiConferenceBundle:Conference:mail.txt.twig', array('name' => $name) ));
        $this->get('mailer')->send($message);
    }

    public function newAction(Request $request)
    {	
        $user = $this->get('security.context')->getToken()->getUser();
        $conference = $request->getSession()->get('conference');
        $name= $conference->getName();
        $translator = $this->get('translator');
        
        $em = $this->getDoctrine()->getEntityManager();
        $registration = $em
            ->createQuery('SELECT r.id FROM ZpiConferenceBundle:Registration r WHERE r.participant = :user AND r.conference = :conf')
            ->setParameters(array(
                'user' => $user->getId(),
                'conf' => $conference->getId()
            ))->getOneOrNullResult();
        if(!empty($registration))
            throw $this->createNotFoundException($translator->trans('reg.err.alreadyregistered')); 
        // TODO: umówić się jak mają wyglądać infopage. Globalna funkcja zwracająca response? Ten wyjątek nie wygląda pięknie.
        
//         $conference = new Conference();
        $registration = new Registration();
        $registration->setConference($conference);
        $registration->setParticipant($user);
        // odgórne ustawienie deadline'u submisji dla tej rejestracji, na ten z konferencji
        $registration->setSubmissionDeadline($conference->getPaperDeadline());
        // odgórne ustawienie deadline'u poprawnej pracy dla tej rejestracji, na ten z konferencji
        $registration->setCamerareadyDeadline($conference->getCorrectedPaperDeadline());
        $registration->setType(Registration::TYPE_LIMITED_PARTICIPATION); // zmieniamy przy dodaniu pracy bądź cedowaniu
        $form = $this->createFormBuilder($registration)->getForm();
           
	if($request->getMethod() == 'POST')
	{
            $form->bindRequest($request);

            if($form->isValid())
            {  	
                $em->persist($registration);
                $em->flush();
                $this->sendMail($user, $name);
                $this->get('session')->setFlash('notice', $this->get('translator')->trans('reg.reg_success'));
                return $this->redirect($this->generateUrl('registration_show', array('id' => $registration->getId())));
			
            }
	}			
	return $this->render('ZpiConferenceBundle:Registration:new.html.twig', 
                                array('form' => $form->createView(), 'conference' => $conference));
    }
    
    public function new2Action(Request $request)
	{
		$translator = $this->get('translator');
		$this->get('session')->setFlash('notice', 
		        $translator->trans('reg.info'));
		$now = new \DateTime('now');
		$registration = new Registration();	
		$registration->setStartDate($now);		
		$registration->setEndDate($now);
			             
        $securityContext = $this->container->get('security.context'); // unikajmy definiowania zmiennych jak ich potem nie uzyjemy
	    $user = $securityContext->getToken()->getUser();
	    
        /* Pomimo szczerych chęci, nie udało się dodać pól do utworzonego
         *  w tej klasie formularza... @Gecaj
         */
        //$form = $this->createForm(new RegistrationFormType(), $registration);
                
        
		$form = $this->createFormBuilder($registration)                        
			->add('conference', 'entity', array('label' => 'reg.form.conf',
					'class' => 'ZpiConferenceBundle:Conference',
					'query_builder'=> $this->getDoctrine()
					->getRepository('ZpiConferenceBundle:Conference')
					->createQueryBuilder('c')
					->where('c.deadline > :current')
					->setParameter('current', date('Y-m-d'))))
			->add('startDate', 'date', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'choice', 
				  'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 						date('Y', strtotime('+2 years')), 
				    date('Y', strtotime('+3 years')))))	
			->add('endDate', 'date', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'choice', 
			      'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 				       date('Y', strtotime('+2 years')), 
			       date('Y', strtotime('+3 years')))))
			->add('type', 'choice', array('label' => 'reg.form.type', 'choices'=>
					array(0 => 'Limited participation', 1 => 'Full participation'),
					'expanded' => true, ))
			->add('papers', 'entity', array('label' => 'reg.form.papers',
				  'multiple' => true,
				  'class' => 'ZpiPaperBundle:Paper',				  
				  'query_builder'=> $this->getDoctrine()
					->getRepository('ZpiPaperBundle:Paper')
					->createQueryBuilder('p')
					->where('p.owner = :currentUser')
					->setParameter('currentUser', $user->getId())))
			->getForm();
                     
		
			
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			if ($form->isValid())
			{					
				$registration->setParticipant($user);
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($registration);
				$em->flush();                
		        $this->get('session')->setFlash('notice', 
		        		$translator->trans('reg.reg_success'));
			
				//return $this->redirect($this->generateUrl('conference_list')); 
                                return $this->redirect($this->generateUrl('registration_show', 
                                        array('id' => $registration->getId())));
					
			}
		}
			
		return $this->render('ZpiConferenceBundle:Registration:new.html.twig', array(
			'form' => $form->createView()));
	}
        
    public function showAction($id = null) // moze podglad po ID dla moderatora, a z routa /show dla ownera rejestracji?
    {
        $translator = $this->get('translator');
        $conference = $this->getRequest()->getSession()->get('conference');
        $user = $this->get('security.context')->getToken()->getUser();
        
        if(empty($id)) // korzystamy z faktu, ze jeden user ma tylko jedna rejestracje na konkretna konferencje
        {
            $em = $this->getDoctrine()->getEntityManager();
            $registration = $em
                ->createQuery('SELECT r FROM ZpiConferenceBundle:Registration r WHERE r.participant = :user AND r.conference = :conf')
                ->setParameters(array(
                    'user' => $user->getId(),
                    'conf' => $conference->getId()
                ))->getOneOrNullResult();
        }
        else
        {
            $registration = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Registration')
					->find($id);
        }
        
        $papers = $registration->getPapers();
	    					
		
	if(!$registration)
        {
            throw $this->createNotFoundException($translator->trans('reg.none'));
        }
        else
        {
            $startDate = date('Y-m-d', $conference->getStartDate()->getTimestamp());
            $endDate = date('Y-m-d', $conference->getEndDate()->getTimestamp());
            $deadline = date('Y-m-d', $conference->getConfirmationDeadline()->getTimestamp());
            $arrivalDate = (!is_null($registration->getStartDate())) ? date('Y-m-d', $registration->getStartDate()->getTimestamp()) : '';
            $leaveDate = (!is_null($registration->getEndDate())) ? date('Y-m-d', $registration->getEndDate()->getTimestamp()) : '';
                        
            return $this->render('ZpiConferenceBundle:Registration:show.html.twig', 
								 array('conference' => $conference,								 	   
								 	   'deadline' => $deadline,
								 	   'papers' => $papers,                                       
                                       'registration' => $registration,
                                       ));
            
	}
    }
    
    public function editAction(Request $request, $id)
    {
        $registration = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Registration')
					->find($id);
		$translator = $this->get('translator');
        $em = $this->getDoctrine()->getEntityManager();
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
		        
        $form = $this->createFormBuilder($registration)		
			->add('startDate', 'date', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'choice', 
				  'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 						date('Y', strtotime('+2 years')), 
				    date('Y', strtotime('+3 years')))))	
			->add('endDate', 'date', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'choice', 
			      'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 				       date('Y', strtotime('+2 years')), 
			       date('Y', strtotime('+3 years')))))
			->add('type', 'choice', array('label' => 'reg.form.type', 'choices'=>
					array(0 => 'Limited participation', 1 => 'Full participation'),
					'expanded' => true, ))
            ->add('_token', 'csrf')
            ->add('papers', 'entity', array('label' => 'reg.form.papers',
				  'multiple' => true,
				  'class' => 'ZpiPaperBundle:Paper',				  
				  'query_builder'=> $this->getDoctrine()
					->getRepository('ZpiPaperBundle:Paper')
					->createQueryBuilder('p')
					->where('p.owner = :currentUser')
					->setParameter('currentUser', $user->getId())))                           
            ->getForm();
      
        if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
           			
			if ($form->isValid())
			{						
				$em->flush();                
		        $this->get('session')->setFlash('notice', 
		        		$translator->trans('reg.reg_success'));			
				
                return $this->redirect($this->generateUrl('registration_show', 
                                        array('id' => $registration->getId())));
					
			}
		}
			
		return $this->render('ZpiConferenceBundle:Registration:edit.html.twig', array(
			'form' => $form->createView(), 'id'=>$id));
    }
    
    public function listAction()
    {
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();
			
		$registrations = $user->getRegistrations();
					 
		if(count($registrations) == 0)
        {
			return $this->render('ZpiConferenceBundle:Registration:registrationsList.html.twig',
					 array('registrations' => $registrations));
		}
		else
        {
			return $this->render('ZpiConferenceBundle:Registration:registrationsList.html.twig',
					 array('registrations' => $registrations));
		}
	}
	
	
	public function deleteAction($id)
	{
		$registration = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Registration')
					->find($id);
                $conference = $registration->getConference();
		$translator = $this->get('translator');
		$em = $this->getDoctrine()->getEntityManager();
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();		
		$user->getConferences()->removeElement($conference);
		$conference->getRegistrations()->removeElement($registration);
		$em->remove($registration);		
		$em->flush();
		$this->get('session')->setFlash('notice', 
		        $translator->trans('reg.del_success'));
		        
		return $this->redirect($this->generateUrl('registration_list'));
	}
    
    public function paperDeleteAction($id, $paper_id) // tutaj chyba dodamy usuwanie paperu z papers, jego dokumentów oraz rekordu z users_papers // @quba
    {
        $em = $this->getDoctrine()->getEntityManager();
        $registration = $this->getDoctrine()
                    ->getRepository('ZpiConferenceBundle:Registration')->find($id);
        $paper = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper')->find($paper_id);
        $registration->getPapers()->removeElement($paper);
        if(count($registration->getPapers()) == 0)
                $registration->setType(Registration::TYPE_LIMITED_PARTICIPATION);
        $em->flush();
        
        return $this->redirect($this->generateUrl('registration_show', 
                                        array('id' => $registration->getId(),
                                              )));
    }
    
    // TODO sprawdzenie deadline'u confirmation of participation
    // Będzie gdzieś podana cena żarcia/noclegu i podsumowanie?
    // byc moze bedzie, trzeba sie skontaktowac z Frasiem jak to ma byc, 
    // on mowil jedynie o cenie za prace, sam wole nic nie kombinowac @Gecaj
    // Fajnie byłoby dodać tutaj też info o konferencji, bo daty nie ma jak podejrzeć szybko.
    public function confirmAction(Request $request)
    {       
        $conference = $this->getRequest()->getSession()->get('conference');  
        $translator = $this->get('translator');
        $now = new \DateTime('now');
        
        // Sprawdzenie, czy nie minął już deadline na potwierdzenie rejestracji
        // TODO podstrony informacyjne
        if($now > $conference->getConfirmationDeadline())
            throw $this->createNotFoundException($translator->trans('reg.confirm.too_late')); 
        
        $em = $this->getDoctrine()->getEntityManager();        
        $registration = $em
                ->createQuery('SELECT r FROM ZpiConferenceBundle:Registration r
                    WHERE r.conference = :conference AND r.participant = :user')
                ->setParameters(array('conference'=>$conference, 
                    'user' =>$this->container->get('security.context')->getToken()->getUser()))
                ->getOneOrNullResult();
        
        // Jezeli uzytkownik nie jest zarejestrowany, to przekierowanie na 
        // strone rejestracji
        if(!$registration)
        {
            return $this->redirect($this->generateUrl('registration_new'));
        }
                
        // TODO odpowiednia strona informacyjna
        if($registration->getConfirmed() == 1)
            throw $this->createNotFoundException($translator->trans('reg.err.alreadyconfirmed'));
        
        
        
        /*
         * Obliczenie ceny za zarejestrowane (i zaakceptowane) prace
         */
        
        
        
        // zarejestrowane papery => cena za druk kazdego z nich
        $papers_prices = array(); // trzeba to zainicjować - puste dla limited participation
        
        // suma cen za wszystkie papery do druku
        $papers_price_sum = 0;   
       
        // TODO Pobranie zaakceptowanych do druku - zapytanie SQL

        // muszą być dwie oceny pozytywne (mark 2) typu 0 i typu 1
        // jezeli jest 1 -  praca musi zostac poprawiona
        // jezeli jest 0 -  praca odrzucona
        // jezeli nie ma dwoch ocen to trzeba jeszcze poczekac na recenzje swojej pracy
        // dla kazdego dokumentu sprawdzam najnizsza ocene zarowno techniczna i normalna - ona jest wiazaca
        
        // jedna z ocen nizsza od 4
        $nonaccepted_papers = array();
        
        // oczekujace na ocene
        $waiting_papers = array(); 
              

        
        foreach($registration->getPapers() as $paper)
        {
            
            foreach($paper->getDocuments() as $document)
            {
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
                    
                    if($review->getType() == 0 && $review->getMark() < $worst_normal_mark)
                    {
                        
                        $worst_normal_mark = $review->getMark();
                    }
                    else if($review->getType() == 1 && $review->getMark() < $worst_technical_mark)
                    {
                        
                        $worst_technical_mark = $review->getMark();
                    }
                }
                
                // jezeli choc jednego typu oceny dokument nie posiada
                // dodawany do oczekujacych na ocene
                if(!($exist_normal && $exist_technical))
                {
                    $waiting_papers[] = $paper->getTitle();
                }  
                // jezeli obydwie najnizsze oceny sa 'accepted' papery moga byc drukowane - liczenie cen
                else if($worst_normal_mark == Review::MARK_ACCEPTED && $worst_technical_mark == Review::MARK_ACCEPTED)
                {
                    if($document->getPagesCount() >= $conference->getMinPageSize())
                    {
                        $extra_pages = $document->getPagesCount() - $conference->getMinPageSize(); 

                        // obliczenie ceny za druk danej pracy
                        $price = $conference->getPaperPrice() + $extra_pages*$conference->getExtrapagePrice();

                        // dodanie do tablicy prac, które mają prawo do druku wraz z cenami wydruku
                        $papers_prices[$paper->getTitle()] = $price;
                    }
                }
                // w przeciwnym wypadku paper nie jest zaakceptowany do druku
                else
                {
                    $nonaccepted_papers[] = $paper->getTitle();
                }
              
            }
        }
        

        foreach($papers_prices as $value) 
        {
            $papers_price_sum += $value;
        }
        
        /*
         * Formularz dat oraz wyboru książki i kita
         */
                   
        
        $registration->setStartDate($conference->getBookingstartDate());
        $registration->setEndDate($conference->getBookingendDate());
        
        $form = $this->createFormBuilder($registration)
                ->add('startDate', 'date', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'choice', 
				  'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 						date('Y', strtotime('+2 years')), 
				    date('Y', strtotime('+3 years')))))	
                ->add('endDate', 'date', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'choice', 
			      'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 				       date('Y', strtotime('+2 years')), 
			       date('Y', strtotime('+3 years')))))
                ->add('enableBook', 'checkbox', array('label' => 'reg.form.conf_book'))
                ->add('enableKit', 'checkbox', array('label' => 'reg.form.conf_kit'))
                ->add('notes', 'textarea',
				array('label' => 'reg.form.notes'))
                        
                ->getForm();
               
        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);

            if ($form->isValid())
            {	               
                $registration->setConfirmed(true);
                
                // wyliczenie całkowitej kwoty udziału w konferencji
                $total_payment = 0;
                $total_payment += $papers_price_sum;
                
                if($registration->getEnableBook())
                    $total_payment += $conference->getConferencebookPrice();
                if($registration->getEnableKit())
                    $total_payment += $conference->getConferencekitPrice ();
                
                // Jeżeli wymagana opłata za wszystkie dni konferencji to wyliczenie i dodanie jej
                if($conference->getDemandAlldayPayment())
                {                  
                    $diff = (date_timestamp_get($conference->getEndDate()) 
                            - date_timestamp_get($conference->getStartDate()))/(24*60*60);
                    $bookingPrice = $diff*$conference->getOnedayPrice();
                    $total_payment += $bookingPrice;
                }
                
                if($conference->getDemandAlldayPayment())
                {
                    $bookingDiff = 0;
                    $bookingBefore = intval((date_timestamp_get($conference->getStartDate()) 
                            - date_timestamp_get($registration->getStartDate()))/(24*60*60));
                    if($bookingBefore < 0)
                        $bookingBefore = 0;
                    $bookingAfter = intval((date_timestamp_get($registration->getEndDate()) 
                            - date_timestamp_get($conference->getEndDate()))/(24*60*60));
                    if($bookingAfter < 0)
                        $bookingAfter = 0;
                    $bookingDiff = $bookingBefore + $bookingAfter;
                    $price = $bookingDiff*$conference->getOnedayPrice();
                    $total_payment += $price;
                    
                }
                else
                {    
                    $bookingDiff = intval((date_timestamp_get($endDate) 
                            - date_timestamp_get($startDate))/(24*60*60));
                    $price = $bookingDiff*$conference->getOnedayPrice();
                    $total_payment += $price;
                }
                
                $registration->setTotalPayment($total_payment);
                $em->flush();				             
                $this->get('session')->setFlash('notice', 
                $translator->trans('reg.confirm.success'));			
				
                return $this->redirect($this->generateUrl('homepage', 
                        array('_conf' => $conference->getPrefix())));
					
            }
        }
        
        //$conference = new Conference();
        $dataDiff = (date_timestamp_get($conference->getEndDate()) - date_timestamp_get($conference->getStartDate()))/(24*60*60);
        return $this->render('ZpiConferenceBundle:Registration:confirm.html.twig', 
                array('conference' => $conference, 
                    'registration' => $registration,
                    'nonaccepted_papers' => $nonaccepted_papers,
                    'waiting_papers' => $waiting_papers,
                    'papers_prices'=> $papers_prices,
                    'papers_price_sum'=>$papers_price_sum,
                    'form' => $form->createView(),
                    'conference' => $conference,
                    'dataDiff' => $dataDiff));
    }
    
    public function dataDiffAction()
    {
        # Is the request an ajax one?
        if ($this->container->get('request')->isXmlHttpRequest())
        {
            $conference = $this->getRequest()->getSession()->get('conference'); 
            $arrivalMonth = $this->container->get('request')->request->get('arrivalMonth');
            $arrivalDay = $this->container->get('request')->request->get('arrivalDay');
            $arrivalYear = $this->container->get('request')->request->get('arrivalYear');
            $leaveMonth = $this->container->get('request')->request->get('leaveMonth');
            $leaveDay = $this->container->get('request')->request->get('leaveDay');
            $leaveYear = $this->container->get('request')->request->get('leaveYear');
            $startDate = new \DateTime();
            $endDate = new \DateTime();
            $startDate->setDate($arrivalYear, $arrivalMonth, $arrivalDay);
            $startDate->setTime(0, 0, 0);
            $endDate->setDate($leaveYear, $leaveMonth, $leaveDay);
            $endDate->setTime(0, 0, 0);
            //$conference = new Conference();
            $bookingPrice = 0;
            $allDay = 0;
            // jezeli wymagana oplata za wszystkie dni, to wyliczenie jej
            if($conference->getDemandAlldayPayment())
            {
                $allDay = 1;
                $diff = (date_timestamp_get($conference->getEndDate()) - date_timestamp_get($conference->getStartDate()))/(24*60*60);
                $bookingPrice = $diff*$conference->getOnedayPrice();
            }
            if($startDate < $conference->getBookingstartDate() 
                    || $startDate > $conference->getEndDate())
            {
                $response = new Response(json_encode(array('booking' => $bookingPrice, 'allday' => $allDay, 'dates' => 1,
                    'reply' => 'Arrival date should be between conference booking start and conference end date.')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if($endDate > $conference->getBookingendDate() || $endDate < $conference->getStartDate())
            {
                $response = new Response(json_encode(array('booking' => $bookingPrice, 'allday' => $allDay, 'dates' => 1,
                    'reply' => 'Leave date should be between conference start and booking end date.')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if($startDate > $endDate)
            {
                $response = new Response(json_encode(array('booking' => $bookingPrice, 'allday' => $allDay, 'dates' => 1,
                    'reply' => 'Arrival date shouldn\'t be after leave date')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }            
            else
            {
                if($allDay)
                {
                    $bookingDiff = 0;
                    $bookingBefore = intval((date_timestamp_get($conference->getStartDate()) 
                            - date_timestamp_get($startDate))/(24*60*60));
                    if($bookingBefore < 0)
                        $bookingBefore = 0;
                    $bookingAfter = intval((date_timestamp_get($endDate) 
                            - date_timestamp_get($conference->getEndDate()))/(24*60*60));
                    if($bookingAfter < 0)
                        $bookingAfter = 0;
                    $bookingDiff = $bookingBefore + $bookingAfter;
                    
                }
                else
                {    
                    $bookingDiff = intval((date_timestamp_get($endDate) 
                            - date_timestamp_get($startDate))/(24*60*60));
                }
                $price = $bookingDiff*$conference->getOnedayPrice();
                $response = new Response(json_encode(array('booking' => $bookingPrice, 'allday' => $allDay, 'dates' => 0, 'reply' => $price)));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            
        }
    }
}
