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
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\PaperBundle\Form\Type\ChangePaperPaymentType;

class RegistrationController extends Controller
{
    public function sendMail($user, $name)
    {
//         $message = \Swift_Message::newInstance()
//             ->setSubject($name)
//             ->setFrom('zpimailer@gmail.com')
//            ->setTo($user->getEmail() )
//        //   nie działa     
//        //     ->setTo('zpimailer@gmail.com')
//             ->setBody($this->renderView('ZpiConferenceBundle:Conference:mail.txt.twig', array('name' => $name) ));
//         $this->get('mailer')->send($message);
// Szymon, tu masz pokazane jak korzystać z mojej nowej usługi przesyłania powiadomień. @lyzkov
        $mailer = $this->get('messager');
        $mailer->sendMail($name, 'zpimailer@gmail.com', $user->getEmail(), 'ZpiConferenceBundle:Conference:reg_mail.txt.twig', array('name' => $name));
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
          //      $this->sendMail($user, $name);
                $mailer = $this->get('messager');
                $parameters = array(
                'var1' => $name
                );
                $mailer->sendMail($name, 'zpimailer@gmail.com', $user->getEmail(), 'ZpiConferenceBundle:Conference:mail.txt.twig',array('parameters' => $parameters));
                $this->get('session')->setFlash('notice', $this->get('translator')->trans('reg.reg_success'));
                return $this->redirect($this->generateUrl('registration_user_show'));
			
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
        
        // tablica przechowująca papery wraz z ilością istniejących autorów
        $papers_authors = array();
        $papers = $registration->getPapers();
        foreach($papers as $paper)
        {
            $coauthors = 
                    $this->getDoctrine()
					->getRepository('ZpiUserBundle:User')
					->createQueryBuilder('u')
                    ->innerJoin('u.registrations', 'r')
                    ->innerJoin('r.conference', 'c')
                    ->innerJoin('u.papers', 'up')
                    ->where('r.conference = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                    ->andWhere('up.author = :author')
                    ->setParameter('author', UserPaper::TYPE_AUTHOR_EXISTING)
                    ->andWhere('up.paper = :paper_id')
                    ->setParameter('paper_id', $paper->getId())
                    ->getQuery()
                    ->getResult();
            
            
            $papers_authors[$paper->getTitle()] = count($coauthors);
        }
        
	    					
		
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
                                       'papers_authors' => $papers_authors,
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
        $conference = $this->getRequest()->getSession()->get('conference');
        
        if($registration->getStartDate() == null)
            $registration->setStartDate($conference->getStartDate());
        if($registration->getEndDate() == null)
            $registration->setEndDate($conference->getEndDate());
		        
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
            ->add('notes', 'textarea', array('label' => 'reg.form.notes'))
            ->add('_token', 'csrf')                         
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
			'form' => $form->createView(), 'id'=>$id, 'type' => $registration->getType()));
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
       
        
       
        // papery opłacane jakos full
        // zarejestrowane papery => cena za druk każdego z nich
        $papers_prices = array(); // trzeba to zainicjować - puste dla limited participation
        // ceny za extra pages
        $extrapages_prices = array();
        
        // ceny za papery opłacane jako extra pages
        $papers_extra_prices = array();
        // suma cen za wszystkie papery do druku
        $papers_price_sum = 0;   
        
        // Przynajmniej jedna zaakceptowana praca musi być opłacana jako full
        $exist_full_type = false;

        $papers = $registration->getPapers();
        // TODO nowe rozroznianie zaakceptowanych, przekazywanie obiektow do twiga
        // obliczanie cen w twigu
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
                    if(!$exist_normal && $review->getType() == Review::TYPE_NORMAL)
                            $exist_normal = true;
                    else if(!$exist_technical && $review->getType() == Review::TYPE_TECHNICAL)
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
                
                
                // jezeli obydwie najnizsze oceny sa 'accepted' papery moga byc drukowane - liczenie cen
                if(($exist_normal && $exist_technical) && $worst_normal_mark == Review::MARK_ACCEPTED && $worst_technical_mark == Review::MARK_ACCEPTED)
                {
                    if($document->getPagesCount() >= $conference->getMinPageSize())
                    {
                        if($paper->getPaymentType() == Paper::PAYMENT_TYPE_FULL)
                        {
                            $exist_full_type = true;
                            $extra_pages = $document->getPagesCount() - $conference->getMinPageSize(); 

                            $extra_pages_price = $extra_pages*$conference->getExtrapagePrice();
                            // obliczenie ceny za druk danej pracy
                            $price = $conference->getPaperPrice() + $extra_pages_price;

                            // dodanie do tablicy prac, które mają prawo do druku wraz z cenami wydruku
                            // płatność typu full
                            $papers_prices[$paper->getTitle()] = $price;

                            // dodanie do tablicy cen za extrapages
                            $extrapages_prices[$paper->getTitle()] = $extra_pages_price;
                        }
                        else 
                        {
                            $price = $document->getPagesCount()*$conference->getExtrapagePrice();
                            $papers_extra_prices[$paper->getTitle()] = $price;
                        }
                    }
                }
            
              
            }
            
        }
        
        if($exist_full_type)
        {
            foreach($papers_prices as $value) 
            {
                $papers_price_sum += $value;
            }
            foreach($papers_extra_prices as $value) 
            {
                $papers_price_sum += $value;
            }
        }
        
        /*
         * Formularz dat oraz wyboru książki i kita
         */
         
        // Jeżeli data nie ustawiona wcześniej to domyślne ustawienie na daty
        // początku rezerwacji i jej końca przez konferencję
        if($registration->getStartDate() == null)
            $registration->setStartDate($conference->getStartDate());
        if($registration->getEndDate() == null)
            $registration->setEndDate($conference->getEndDate());
                    
        $form = $this->createFormBuilder($registration)
                ->add('declared', 'checkbox', array('label' => 'reg.form.declaration'))
                ->add('papers', 'collection', array(
                'type' => new ChangePaperPaymentType(),
                ))
                ->add('startDate', 'date', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'single_text' ,'format'=>'d-m-Y'))               
                ->add('endDate', 'date', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'single_text' ,'format'=>'d-m-Y'))
                ->add('bookQuantity', 'choice', array('label' => 'reg.form.conf_book_quantity',
                    'choices' => array(0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6')))
                ->add('enableKit', 'checkbox', array('label' => 'reg.form.conf_kit'))
                ->add('notes', 'textarea',
				array('label' => 'reg.form.notes'))
                ->add('_token', 'csrf')                        
                ->getForm();
        // TODO nowe wyliczenie cen za papery 
        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);

            if ($form->isValid())
            {	               
                $registration->setConfirmed(true);
                
                // wyliczenie całkowitej kwoty udziału w konferencji
                $total_payment = 0;
                // tylko full płaci za prace
                
                if($registration->getType() == 0)
                {
                    $total_payment += $papers_price_sum;
                    
                    /*
                     * Ręczne ustawienie na true, ponieważ pole to jest pominięte w formularzu
                     * dla full participation i ustawia się na 0, a full participation
                     * zawsze zawiera kita :P
                     */
                    $registration->setEnableKit(true);
                }
                                
                if($registration->getEnableBook())
                    $total_payment += ($conference->getConferencebookPrice())*($registration->getBookQuantity());
                
                // Tylko limited płaci dodatkowo za kit. Full ma wliczony w conference fee.
                if($registration->getEnableKit() && $registration->getType() == 1)
                    $total_payment += $conference->getConferencekitPrice ();
                /*
                // Jeżeli wymagana opłata za wszystkie dni konferencji to wyliczenie i dodanie jej
                if($conference->getDemandAlldayPayment())
                {                  
                    $diff = (date_timestamp_get($conference->getEndDate()) 
                            - date_timestamp_get($conference->getStartDate()))/(24*60*60);
                    $bookingPrice = $diff*$conference->getOnedayPrice();
                    $total_payment += $bookingPrice;
                }
                 * 
                 */
                
                // dodanie do całkowitej ceny full/limited participation fee                
                if($registration->getType() == 0)
                    $total_payment += $conference->getFullParticipationPrice ();
                else
                    $total_payment += $conference->getLimitedParticipationPrice ();
                
                // naliczenie extra dni
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
                // obliczenie za każdy dzień z osobna jeżeli nie konferencja nie opłaca pobytu
                // w ustalonej cenie (full/limited participation fee)
                else
                {    
                    $bookingDiff = intval((date_timestamp_get($registration->getEndDate()) 
                            - date_timestamp_get($registration->getStartDate()))/(24*60*60));
                    $price = $bookingDiff*$conference->getOnedayPrice();
                    $total_payment += $price;
                }
                
                $registration->setTotalPayment($total_payment);
                $em->flush();
                $mailer = $this->get('messager');
                $user = $this->get('security.context')->getToken()->getUser();
                $name= $conference->getName();
                $parameters = array(
                'name' => $name,
                'price' => $total_payment
                );
                $mailer->sendMail('Confirmation', 'zpimailer@gmail.com', $user->getEmail(), 'ZpiConferenceBundle:Conference:confirm_mail.txt.twig',
                array('parameters' => $parameters));
                $this->get('session')->setFlash('notice', 
                $translator->trans('reg.confirm.success'));			
				
                return $this->redirect($this->generateUrl('homepage', 
                array('_conf' => $conference->getPrefix())));
    		
            }
        }
        
        //$conference = new Conference();
        
        return $this->render('ZpiConferenceBundle:Registration:confirm.html.twig', 
                array('conference' => $conference, 
                    'registration' => $registration, 
                    'papers' => $papers,
                    'papers_prices'=> $papers_prices,
                    'extrapages_prices' => $extrapages_prices,
                    'papers_extra_prices' => $papers_extra_prices,
                    'papers_price_sum'=>$papers_price_sum,
                    'form' => $form->createView()));
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
            if($conference->getDemandAlldayPayment())
                $allDay = 1;
            // jezeli wymagana oplata za wszystkie dni, to wyliczenie jej
            /*if($conference->getDemandAlldayPayment())
            {
                $allDay = 1;
                $diff = (date_timestamp_get($conference->getEndDate()) - date_timestamp_get($conference->getStartDate()))/(24*60*60);
                $bookingPrice = $diff*$conference->getOnedayPrice();
            }*/
            if($startDate < $conference->getBookingstartDate() 
                    || $startDate > $conference->getEndDate())
            {
                $response = new Response(json_encode(array('booking_price' => $bookingPrice, 'allday' => $allDay, 'dates' => 1,
                    'reply' => 'Arrival date should be between conference booking start and conference end date.')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if($endDate > $conference->getBookingendDate() || $endDate < $conference->getStartDate())
            {
                $response = new Response(json_encode(array('booking_price' => $bookingPrice, 'allday' => $allDay, 'dates' => 1,
                    'reply' => 'Leave date should be between conference start and booking end date.')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if($startDate > $endDate)
            {
                $response = new Response(json_encode(array('booking_price' => $bookingPrice, 'allday' => $allDay, 'dates' => 1,
                    'reply' => 'Arrival date shouldn\'t be after leave date')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }            
            else
            {
                // poprawne wyliczenie ceny za extra dni
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
                $response = new Response(json_encode(array('booking_price' => $price, 'allday' => $allDay, 'dates' => 0, 'reply' => '')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            
        }
    }
    
    
    public function changeOwnerAction($id, $paper_id)
    {        
        
        // pobranie rejestracji i paperu
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getEntityManager();
        
        $conference = $this->getRequest()->getSession()->get('conference');
        //$user = $this->get('security.context')->getToken()->getUser();
        $registration = $this->getDoctrine()
                    ->getRepository('ZpiConferenceBundle:Registration')->find($id);
        $paper = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper')->find($paper_id);
        
               
        $form = $this->createFormBuilder($paper)
                ->add('owner', 'entity', array('label' => 'reg.form.choose_owner',
					'class' => 'ZpiUserBundle:User',
                    'multiple' => false, 'expanded' => true,
					'query_builder'=> $this->getDoctrine()
					->getRepository('ZpiUserBundle:User')
					->createQueryBuilder('u')
                    ->innerJoin('u.registrations', 'r')
                    ->innerJoin('r.conference', 'c')
                    ->innerJoin('u.papers', 'up')
                    ->where('r.conference = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                    ->andWhere('up.author = :author')
                    ->setParameter('author', UserPaper::TYPE_AUTHOR_EXISTING)
                    ->andWhere('up.paper = :paper_id')
                    ->setParameter('paper_id', $paper->getId())))             
                ->getForm();
        
        if ($this->getRequest()->getMethod() == 'POST')
		{
			$form->bindRequest($this->getRequest());
           			
			if ($form->isValid())
			{			
                $registration->getPapers()->removeElement($paper);
                $newOwnerRegistration = $this->getDoctrine()
					->getRepository('ZpiConferenceBundle:Registration')
					->createQueryBuilder('r')
                    ->where('r.participant = :user_id')
                    ->setParameter('user_id', $paper->getOwner()->getId())
                    ->andWhere('r.conference = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                    ->getQuery()
                    ->getSingleResult();
                $newOwnerRegistration->addPaper($paper);
				$em->flush();                
		        $this->get('session')->setFlash('notice', 
		        		$translator->trans('reg.ownerchange_success'));			
				
                return $this->redirect($this->generateUrl('registration_show', 
                                        array('id' => $registration->getId())));
					
			}
		}
			
		return $this->render('ZpiConferenceBundle:Registration:changeOwner.html.twig', array(
			'form' => $form->createView(), 'id'=>$id, 'paper_id' => $paper_id));
        
    }
}
