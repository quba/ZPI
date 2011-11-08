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
        //TODO: zabezpieczenie dla zalogowanych (chyba najlepiej po URL-u)
        $em = $this->getDoctrine()->getEntityManager();
        $conference = $this->get('overall')->conf($em, $this->get('router'), $this->getRequest());
        $user = $this->get('security.context')->getToken()->getUser();
        $registration = new Registration();
        
        $form = $this->createFormBuilder($registration)->getForm();
        // rano dorzucę informacje o konferencji, sprawdzanie zalogowania oraz tego, czy już nie byłem zarejestrowany.             
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
			
                //return $this->redirect($this->generateUrl('registration_show', array('id' => $registration->getId())));
					
            }
	}			
	return $this->render('ZpiConferenceBundle:Registration:new.html.twig', array('form' => $form->createView()));
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
            $arrivalDate = date('Y-m-d', $registration->getStartDate()->getTimestamp());
            $leaveDate = date('Y-m-d', $registration->getEndDate()->getTimestamp());
                        
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
    
    public function confirmAction($id)
    {
    
    }
}
