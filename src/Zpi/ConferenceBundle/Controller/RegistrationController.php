<?php

namespace Zpi\ConferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\ConferenceBundle\Entity\Registration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
	public function newAction(Request $request)
	{
		$registration = new Registration();		             
        $securityContext = $this->container->get('security.context');
	    $user = $securityContext->getToken()->getUser();     
        
		$form = $this->createFormBuilder($registration)
			->add('conference', 'entity', array('class' => 'ZpiConferenceBundle:Conference'))
			->add('startDate', 'date', array('label' => 'Przyjazd', 'input'=>'datetime', 'widget' => 						'choice',))	
			->add('endDate', 'date', array('label' => 'Wyjazd', 'input'=>'datetime', 'widget' => 						'choice',))
			->add('type', 'integer', array('label' => 'Typ rejestracji'))
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
		        $this->get('session')->setFlash('notice', 'You have successfully registered to a conference!');
			
				return $this->redirect($this->generateUrl('homepage'));
			}
		}
			
		return $this->render('ZpiConferenceBundle:Registration:new.html.twig', array(
			'form' => $form->createView()));
	}
}
