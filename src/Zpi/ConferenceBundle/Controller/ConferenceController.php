<?php	
namespace Zpi\ConferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\ConferenceBundle\Entity\Conference;
use Zpi\ConferenceBundle\Form\Type\ConferenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 *
 * @author lyzkov
 */
class ConferenceController extends Controller
{
	/**
	 * Dodawanie nowej konferencji.
	 * @param Request $request
	 * @author lyzkov
	 */
	public function newAction(Request $request)
	{
		$translator = $this->get('translator');
		$conference = new Conference();
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();

		$form = $this->createForm(new ConferenceType(), $conference);
		
		if($request->getMethod() == 'POST') {
			$form->bindRequest($request);
			
			if ($form->isValid()) {
				$conference->setStatus(Conference::STATUS_OPEN);
				$user->addConference($conference);
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($conference);
				$em->flush();
				$this->get('session')->setFlash('notice',
				$translator->trans('conf.new.success'));
				
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
	
	/**
	 * Edycja konferencji.
	 * @param Request $request
	 * @param unknown_type $id
	 * @author lyzkov
	 */
	public function editAction(Request $request, $id)
	{
		$translator = $this->get('translator');
		$conference = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Conference')
						->find($id);
		
		if (!$conference) {
			throw $this->createNotFoundException(
				$translator->trans('conf.not_found: %id%', array('%id%' => $id)));
		}
		
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();
		
		$form = $this->createForm(new ConferenceType(), $conference);
		
		//TODO Generuje brzydkie zapytanie. Należy zrobić prostszego selecta.
		if (count($conference->getRegistrations()) != 0)
		{
			$form->remove('startDate');
			$form->remove('endDate');
			$form->remove('deadline');
		}
		
		if($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			if ($form->isValid())
			{
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($conference);
				$em->flush();
				$this->get('session')->setFlash('notice',
						$translator->trans('conf.edit.success'));
			
				return $this->redirect($this->generateUrl('homepage'));
			}
		}
		
		return $this->render('ZpiConferenceBundle:Conference:edit.html.twig',
			array('form' => $form->createView(),
				'id' => $id));
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
