<?php	
namespace Zpi\ConferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\ConferenceBundle\Entity\Conference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 *
 * @author lyzkov
 */
class ConferenceController extends Controller  {
	public function newAction(Request $request) {
		$conference = new Conference();
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();
		
		$form = $this->createFormBuilder($conference)
			->add('name', 'text')
			->add('startDate', 'date')
			->add('endDate', 'date')
			->add('deadline', 'date')
			->add('minPageSize', 'integer')
			->add('address', 'textarea')
			->add('description', 'textarea')
			->getForm();
		
		if($request->getMethod() == 'POST') {
			$form->bindRequest($request);
			
			if ($form->isValid()) {
				$conference->setStatus($conference::STATUS_OPEN);
				$user->addConference($conference);
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($conference);
				$em->flush();
				$this->get('session')->setFlash('notice',
					'You have succesfully created new conference!');
				
				return $this->redirect($this->generateUrl('homepage'));
			}
		}
		
		return $this->render('ZpiConferenceBundle:Conference:new.html.twig',
			array('form' => $form->createView()));
	}
	public function conferenceMenuAction(){
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();
			
		$conferences = $user->getConferences();
					 
		if(count($conferences) == 0){
			return new Response('Nie zarejestrowales sie do zadnej konferencji ', 200, 
						  array('Content-Type' => 'text/html'));
		}
		else{
			return $this->render('ZpiConferenceBundle:Conference:conferencesMenu.html.twig',
					 array('conferences' => $conferences));
		}
	}
	
	public function showAction($id){
		$conference = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Conference')
					->find($id);
					
		$startDate = date('Y-m-d', $conference->getStartDate()->getTimestamp());
		$endDate = date('Y-m-d', $conference->getEndDate()->getTimestamp());
		$deadline = date('Y-m-d', $conference->getDeadline()->getTimestamp());
		if(!$conference){
		
		}
		else{
			return $this->render('ZpiConferenceBundle:Conference:show.html.twig', 
								 array('conference' => $conference,
								 	   'startDate' => $startDate,
								 	   'endDate' => $endDate,
								 	   'deadline' => $deadline));
		}
	}
}
