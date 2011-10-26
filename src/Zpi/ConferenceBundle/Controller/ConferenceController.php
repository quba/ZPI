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
	function newAction(Request $request) {
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
}