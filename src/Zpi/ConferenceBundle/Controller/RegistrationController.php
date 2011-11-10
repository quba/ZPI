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

class RegistrationController extends Controller
{
    public function newAction(Request $request)
    {	
        $user = $this->get('security.context')->getToken()->getUser();
        $conference = $request->getSession()->get('conference');
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
        $form = $this->createFormBuilder($registration)->getForm();
           
	if($request->getMethod() == 'POST')
	{
            $form->bindRequest($request);
			
            if($form->isValid())
            {  
                $registration->setParticipant($user);
                $registration->setConference($conference);
                $registration->setType(Registration::TYPE_LIMITED_PARTICIPATION); // zmieniamy przy dodaniu pracy bądź cedowaniu
                $em->persist($registration);
		$em->flush();                
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
        
    public function showAction($id)
    {
        
        $registration = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Registration')
					->find($id);
        $conference = $registration->getConference();
        $papers = $registration->getPapers();
	    					
		
	if(!$conference)
        {
        }
	else
        {
            $startDate = date('Y-m-d', $conference->getStartDate()->getTimestamp());
            $endDate = date('Y-m-d', $conference->getEndDate()->getTimestamp());
            $deadline = date('Y-m-d', $conference->getDeadline()->getTimestamp());
            $arrivalDate = ''; //date('Y-m-d', $registration->getStartDate()->getTimestamp());
            $leaveDate = ''; //date('Y-m-d', $registration->getEndDate()->getTimestamp());
                        
            return $this->render('ZpiConferenceBundle:Registration:show.html.twig', 
								 array('conference' => $conference,
								 	   'startDate' => $startDate,
								 	   'endDate' => $endDate,
								 	   'deadline' => $deadline,
								 	   'papers' => $papers,
                                       'arrivalDate' => $arrivalDate,
                                       'leaveDate' => $leaveDate,
                                       'reg_id' => $registration->getId(),
                                       'reg_type' => $registration->getType(),
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
    
    public function paperDeleteAction($id, $paper_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $registration = $this->getDoctrine()
                    ->getRepository('ZpiConferenceBundle:Registration')->find($id);
        $paper = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper')->find($paper_id);
        $registration->getPapers()->removeElement($paper);
        if(count($registration->getPapers()) == 0)
                $registration->setType(0);
        $em->flush();
        
        return $this->redirect($this->generateUrl('registration_show', 
                                        array('id' => $registration->getId(),
                                              )));
    }
    
    // TODO sprawdzenie deadline'u confirmation of participation
    public function confirmAction(Request $request)
    {       
        $conference = $this->getRequest()->getSession()->get('conference');
        $translator = $this->get('translator');
        
        $result = $this->getDoctrine()
                ->getEntityManager()
                ->createQuery('SELECT r FROM ZpiConferenceBundle:Registration r
                    WHERE r.conference = :conference AND r.participant = :user')
                ->setParameters(array('conference'=>$conference, 
                    'user' =>$this->container->get('security.context')->getToken()->getUser()))
                ->getResult();
        
        // Jezeli uzytkownik nie jest zarejestrowany, to przekierowanie na 
        // strone rejestracji
        if(!$result)
        {
            return $this->redirect($this->generateUrl('registration_new'));
        }
        
        $registration = $result[0];
        
        // TODO odpowiednia strona informacyjna
        if($registration->getConfirmed() == 1)
            throw $this->createNotFoundException($translator->trans('reg.err.alreadyconfirmed'));
        
        
        
        // Obliczenie ceny za zarejestrowane (i zaakceptowane) prace
        
        // zarejestrowane papery => cena za druk kazdego z nich
        $papers_prices;
        
        // suma cen za wszystkie papery do druku
        $papers_price_sum = 0;   
       
        // TODO Pobranie zaakceptowanych do druku - zapytanie SQL
        $documents;
               
        
        foreach($registration->getPapers() as $paper)
        {
            // Wstępna wersja. Później ten foreach będzie się wykonywał dla kolekcji dokumentów
            // które mają prawo do druku - zaakceptowane i posiadające minimalną ilość stron
            // pobranych zapytaniem z bazy danych
            foreach($paper->getDocuments() as $document)
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
        }
        
        foreach($papers_prices as $key => $value)
        {
            $papers_price_sum += $value;
        }
        
           
        $now = new \DateTime('now');
        
        $registration->setStartDate($now);
        $registration->setEndDate($now);
        
        $form = $this->createFormBuilder($registration)
                ->add('startDate', 'date', array('label' => 'reg.form.arr', 
				  'input'=>'datetime', 'widget' => 	'choice', 
				  'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 						date('Y', strtotime('+2 years')), 
				    date('Y', strtotime('+3 years')))))	
                ->add('endDate', 'date', array('label' => 'reg.form.leave', 
			      'input'=>'datetime', 'widget' => 'choice', 
			      'years' => array(date('Y'), date('Y', strtotime('+1 years')), 					 				       date('Y', strtotime('+2 years')), 
			       date('Y', strtotime('+3 years')))))
                ->getForm();
        if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			if ($form->isValid())
			{	               
                $registration->setConfirmed(true);
                $registration->setTotalPayment($papers_price_sum);
				$this->getDoctrine()->getEntityManager()->flush();					             
		        $this->get('session')->setFlash('notice', 
		        		$translator->trans('reg.confirm.success'));			
				
                return $this->redirect($this->generateUrl('homepage', 
                                        array('_conf' => $conference->getPrefix())));
					
			}
		}
        
        
        return $this->render('ZpiConferenceBundle:Registration:confirm.html.twig', 
                array('conference' => $conference, 
                    'registration' => $registration,
                    'papers_prices'=>$papers_prices,
                    'papers_price_sum'=>$papers_price_sum,
                    'form' => $form->createView()));
    }
}
