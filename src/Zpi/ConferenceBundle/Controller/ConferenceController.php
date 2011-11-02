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
		$translator = $this->get('translator');
		$conference = new Conference();
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();

		$form = $this->createFormBuilder($conference)
			->add('name', 'text',
				array(
					'label'	=>	'conf.form.name'))
			->add('startDate', 'date',
				array(
					'label'	=>	'conf.form.start',
					'years'	=>	range(
									date('Y'),
									date('Y', strtotime('+2 years')))))
			->add('endDate', 'date',
				array(
					'label'	=>	'conf.form.end',
					'years'	=>	range(
									date('Y'),
									date('Y', strtotime('+2 years')))))
			->add('deadline', 'date',
				array(
					'label'	=>	'conf.form.deadline',
					'years'	=>	range(
									date('Y', strtotime('-1 years')),
									date('Y', strtotime('+2 years')))))
			->add('minPageSize', 'integer',
				array(
					'label'	=>	'conf.form.min_page_size'))
			->add('address', 'text',
				array(
					'label'	=>	'conf.form.address'))
			->add('city', 'text',
				array(
					'label'	=>	'conf.form.city'))
			->add('postalCode', 'text',
				array(
					'label'	=>	'conf.form.postal_code'))
			->add('description', 'textarea',
				array(
					'label'	=>	'conf.form.description'))
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
					$translator->trans('conf.new.success'));
				
				return $this->redirect($this->generateUrl('homepage'));
			}
		}
		
		return $this->render('ZpiConferenceBundle:Conference:new.html.twig',
			array('form' => $form->createView()));
	}
	
	function editAction(Request $request) {
		
	}
}