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
			->add('title', 'text', array('label' => 'subpage.form.title'))
			->add('content', 'textarea', array('label' => 'subpage.form.content'))
			->getForm();
			
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			if ($form->isValid())
			{				
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($subpage);
				$em->flush();
                                $session = $this->getRequest()->getSession();
                                $session->setFlash('notice', 'Congratulations, your action succeeded!');
			
				return $this->redirect($this->generateUrl('subpage_show',
					 array('canonical' => $subpage->getCanonical())));
			}
		}
                
		return $this->render('ZpiPageBundle:SubPage:new.html.twig', array(
			'form' => $form->createView(),));
	}
	
	public function showAction($canonical)
	{
		$query = $this->getDoctrine()->getEntityManager()->createQuery(
		'SELECT sp FROM ZpiPageBundle:SubPage sp 
		 WHERE sp.canonical = :canonical'
		 )->setParameter('canonical', $canonical);
		$subpage = $query->getSingleResult();
		
		if(!$subpage)
		{
			throw $this->createNotFoundException('No subpage found for id '.$id);
		}
		else
		{
			return $this->render('ZpiPageBundle:SubPage:show.html.twig', array(
				'title' => $subpage->getTitle(), 'content' => $subpage->getContent(), 
				'canonical' => $subpage->getCanonical()));
		}
	}
	
	public function deleteAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$subpage = $em->getRepository('ZpiPageBundle:SubPage')->find($id);
		$em->remove($subpage);
		$em->flush();
		
		return $this->redirect($this->generateUrl('homepage'));
	}
	
	public function updateAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$subpage = $em->getRepository('ZpiPageBundle:SubPage')->find($id);
		
		$form = $this->createFormBuilder($subpage)			
			->add('title', 'text', array('label' => 'subpage.form.title'))
			->add('content', 'textarea', array('label' => 'subpage.form.content'))
			->getForm();
			
		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);
			
			if ($form->isValid())
			{							
				$em->flush();
			
				return $this->redirect($this->generateUrl('subpage_show',
					 array('canonical' => $subpage->getCanonical())));
			}
		}
			
		return $this->render('ZpiPageBundle:SubPage:update.html.twig', array(
			'form' => $form->createView(), 'subpage' => $subpage,));
		
	}
        
    public function subPageMenuAction()
    {
        $subpages = $this->getDoctrine()
                ->getRepository('ZpiPageBundle:SubPage')
                ->findAll();
        return $this->render('ZpiPageBundle:SubPage:subPagesMenu.html.twig', array('subpages' => $subpages));
    }
 
}
