<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\PageBundle\Entity\SubPage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubPageController extends Controller
{
	public function newAction(Request $request)
	{
		$subpage = new SubPage();
		//$subpage->setPageTitle('');
		//$subpage->setPageContent('');
		
		$form = $this->createFormBuilder($subpage)
			->add('page_title', 'text', array('label' => 'subpage.form.title'))
			->add('page_content', 'textarea', array('label' => 'subpage.form.content'))
			->getForm();
			
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			if ($form->isValid())
			{
				$subpage->setPageCanonical($subpage->getPageTitle());
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($subpage);
				$em->flush();
				
				return $this->redirect($this->generateUrl('subpage_show',
					 array('id' => $subpage->getId())));
			}
		}
		return $this->render('ZpiPageBundle:SubPage:new.html.twig', array(
			'form' => $form->createView(),));
	}
	
	public function showAction($id)
	{
		$subpage = $this->getDoctrine()->getRepository('ZpiPageBundle:SubPage')->find($id);
		
		if(!$subpage)
		{
			throw $this->createNotFoundException('No subpage found for id '.$id);
		}
		else
		{
			return $this->render('ZpiPageBundle:SubPage:show.html.twig', array(
				'title' => $subpage->getPageTitle(), 'content' => $subpage->getPageContent(),));
		}
	}
        
        public function subPageMenuAction()
        {
            $subpages = $this->getDoctrine()
                    ->getRepository('ZpiPageBundle:SubPage')
                    ->findAll();

            return $this->render('ZpiPageBundle:SubPage:subPagesMenu.html.twig', array('subpages' => $subpages));
        }
 
}
