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
		$mailContent=$conference->getRegistrationMailContent();
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
                'var1' => $name,
				'var2' => $mailContent
                );
                $mailer->sendMail('Registration', 'zpimailer@gmail.com', $user->getEmail(), 'ZpiConferenceBundle:Conference:mail.txt.twig',array('parameters' => $parameters));
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
    
    
    public function showConfirmationAction()
    {
        return $this->render('ZpiConferenceBundle:Registration:showConfirmation.html.twig'); 
    }
    
    public function confirmAction(Request $request)
    {       
        $conference = $this->getRequest()->getSession()->get('conference');  
        $em = $this->getDoctrine()->getEntityManager();   
        $registration = $em
                ->createQuery('SELECT r FROM ZpiConferenceBundle:Registration r
                    WHERE r.conference = :conference AND r.participant = :user')
                ->setParameters(array('conference'=>$conference, 
                    'user' =>$this->container->get('security.context')->getToken()->getUser()))
                ->getOneOrNullResult();
        $translator = $this->get('translator');
        $now = new \DateTime('now');
        
        // Sprawdzenie, czy nie minął już deadline na potwierdzenie rejestracji
        // TODO podstrony informacyjne
        
        // Jeżeli rejestracja jest już potwierdzona, to wyświetlenie tylko informacji o potwierdzeniu
        if(!$registration->getConfirmed())
            return $this->redirect($this->generateUrl('participation_show'));
        if($now > $conference->getConfirmationDeadline())
            throw $this->createNotFoundException($translator->trans('reg.confirm.too_late')); 
        // TODO odpowiednia strona informacyjna
        
             
        
        
        // Jezeli uzytkownik nie jest zarejestrowany, to przekierowanie na 
        // strone rejestracji
        if(!$registration)
        {
            $this->get('session')->setFlash('notice', 
                $translator->trans('reg.confirm.register_first'));	
            return $this->redirect($this->generateUrl('registration_new'));
        }        
        // Jeżeli jeszcze nie potwierdził swojej rejestracji to informacja
        if(!($registration->getConfirmed()))
        {
            $this->get('session')->setFlash('notice', 
                $translator->trans('reg.confirm.not_confirmed'));
        }           

        $papers = $registration->getPapers();        
        /*
         * Formularz dat oraz wyboru książki i kita
         */
         
        // Jeżeli data nie ustawiona wcześniej to domyślne ustawienie na daty
        // początku rezerwacji i jej końca przez konferencję
        
        if($registration->getStartDate() == null)
            $registration->setStartDate($conference->getStartDate());
        if($registration->getEndDate() == null)
        {
            $defaultEnd = new \DateTime(date('Y-m-d', $conference->getEndDate()->getTimestamp()));
            
            $registration->setEndDate($defaultEnd->add(new \DateInterval('P1D')));
        }
                    
        $form = $this->createFormBuilder($registration)
                ->add('declared', 'checkbox', array('label' => 'reg.form.declaration'))
                ->add('papers', 'collection', array(
                'type' => new ChangePaperPaymentType(),
                ))
                ->add('startDate', 'datetime', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'single_text' ,'date_format'=>'d-m-Y'))               
                ->add('endDate', 'datetime', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'single_text' ,'date_format'=>'d-m-Y'))
                ->add('bookQuantity', 'choice', array('label' => 'reg.form.conf_book_quantity',
                    'choices' => array(0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6')))
                ->add('enableKit', 'checkbox', array('label' => 'reg.form.conf_kit'))
                ->add('notes', 'textarea',
				array('label' => 'reg.form.notes'))
                ->add('_token', 'csrf')                        
                ->getForm();
        
        if ($request->getMethod() == 'POST')
        {
            //echo '<pre>'; echo var_dump($this->getRequest()->request->all()); echo '</pre>';

            //echo 'DUPAAA: ' . strtotime($dupa['startDate']);
            
            //$request->request->get('form')['startDate'] =  new \DateTime(date('Y-m-d', strtotime($dupa['startDate'])));
            //$dupa['endDate'] = new \DateTime(date('Y-m-d', strtotime($dupa['endDate'])));
            $form->bindRequest($request);
            //$registration->setStartDate(new \DateTime(date('Y-m-d', strtotime($dupa['startDate']))));
            //$registration->setEndDate(new \DateTime(date('Y-m-d', strtotime($dupa['endDate']))));
            

            if ($form->isValid())
            {	             
            
                $registration->setConfirmed(true);
                
                $total_payment = 0;
                
                // Dodanie ceny za papery
                if($registration->getType() == 0)
                {                    
                    foreach($papers as $paper)
                    {
                        // funkcja ta sama sprawdza czy jest zaakceptowany czy nie
                        $total_payment += $paper->getPaperPrice(); 
                    }
                    
                    /*
                     * Ręczne ustawienie na true, ponieważ pole to jest pominięte w formularzu
                     * dla full participation i ustawia się na 0, a full participation
                     * zawsze zawiera kita
                     */
                    $registration->setEnableKit(true);
                }
                                
                if($registration->getBookQuantity() > 0)
                {
                    $registration->setEnableBook(1);
                    $total_payment += ($conference->getConferencebookPrice())*($registration->getBookQuantity());                    
                }
                else
                    $registration->setEnableBook(0);
                
                // Tylko limited płaci dodatkowo za kit. Full ma wliczony w conference fee.
                if($registration->getEnableKit() && $registration->getType() == 1)
                    $total_payment += $conference->getConferencekitPrice ();
                
                $total_payment += $registration->getBookingPrice();
                $registration->setTotalPayment($total_payment);
                $em->flush();
                $mailer = $this->get('messager');
                $user = $this->get('security.context')->getToken()->getUser();
                $name= $conference->getName();
                $parameters = array(
                'name' => $name,
                'price' => $total_payment,
                'content'=> $conference->getConfirmationMailContent()
                );
                $mailer->sendMail('Confirmation', 'zpimailer@gmail.com', $user->getEmail(), 'ZpiConferenceBundle:Conference:confirm_mail.txt.twig',
                array('parameters' => $parameters));
                $this->get('session')->setFlash('notice', 
                $translator->trans('reg.confirm.success'));			
				
                //return $this->redirect($this->generateUrl('homepage', 
                //array('_conf' => $conference->getPrefix())));
    		
            }
            //else
                //echo '<pre>'; var_dump($this->getRequest()->request->all()); echo '</pre>';
        }
        
        //$conference = new Conference();
        
        return $this->render('ZpiConferenceBundle:Registration:confirm.html.twig', 
                array('conference' => $conference, 
                    'registration' => $registration, 
                    'papers' => $papers,
                    'form' => $form->createView()));
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
