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
    private $spages;
    // TODO pobieranie i ustawianie ID aktualnej konferencji
	public function newAction(Request $request)
	{
        
		$subpage = new SubPage();
		//$subpage->setPageTitle('');
		//$subpage->setPageContent('');
        $conference = $this->getRequest()->getSession()->get('conference');
        
        $subpage->setConference($conference);
		
		$form = $this->createFormBuilder($subpage)
			->add('title', 'text', array('label' => 'subpage.form.title'))
			->add('content', 'textarea', array('label' => 'subpage.form.content'))
			->add('position', 'choice', array('choices' => array('Top' => 'Top', 'Left' => 'Left'),
			'required' => true))
			->getForm();
			
		if ($request->getMethod() == 'POST')
		{			
			$form->bindRequest($request);			
			
			// jezeli nie ma tytulu nie podejmuj zadnych akcji
			if($subpage->getTitleCanonical() =='')
			{
				$this->get('session')->setFlash('notice', 'Please fill out the title.');
			}
			// w przeciwnym wypadku sprawdz czy zadana strona juz nie istnieje
			else
			{
				$repository = $this->getDoctrine()->getRepository('ZpiPageBundle:SubPage');
				$subpages = $repository->findAll();
				$subpage_exists = false;
			
				foreach($subpages as $spage)
				{			
					if($spage->getTitleCanonical() == $subpage->getTitleCanonical())
					{
						$subpage_exists = true;
					}
					
				}
				if($subpage_exists)
				{
					$this->get('session')->setFlash('notice', 'You cant have two subpages with the same 													name!');
				}					
				else if ($form->isValid())
				{				
					$em = $this->getDoctrine()->getEntityManager();
					$em->persist($subpage);
					$em->flush();                
		            $this->get('session')->setFlash('notice', 'You have successfully added a new subpage!');
			
					return $this->redirect($this->generateUrl('subpage_show',
						 array('titleCanonical' => $subpage->getTitleCanonical())));
				}
			}			
			
			
		}
                
		return $this->render('ZpiPageBundle:SubPage:new.html.twig', array(
			'form' => $form->createView(),));
	}
	
	public function showAction($titleCanonical)
	{
		$query = $this->getDoctrine()->getEntityManager()->createQuery(
		'SELECT sp FROM ZpiPageBundle:SubPage sp 
		 WHERE sp.title_canonical = :title_canonical'
		 )->setParameter('title_canonical', $titleCanonical);
		$subpage = $query->getSingleResult();
		
		if(!$subpage)
		{
			throw $this->createNotFoundException('No subpage found for title_canonical '.$titleCanonical);
		}
		else
		{
			return $this->render('ZpiPageBundle:SubPage:show.html.twig', array(
				'title' => $subpage->getTitle(), 'content' => $subpage->getContent(), 
				'titleCanonical' => $subpage->getTitleCanonical()));
		}
	}
	
	public function deleteAction($titleCanonical)
	{
        $em = $this->getDoctrine()->getEntityManager();
		$query = $em->createQuery(
		'SELECT sp FROM ZpiPageBundle:SubPage sp 
		 WHERE sp.title_canonical = :title_canonical'
		 )->setParameter('title_canonical', $titleCanonical);
		$subpage = $query->getSingleResult();	
		$em->remove($subpage);
		$em->flush();
		$this->get('session')->setFlash('notice', 'You have successfully deleted a subpage!');
		
		return $this->redirect($this->generateUrl('homepage'));
	}
	
	public function updateAction(Request $request, $titleCanonical)
	{
		$query = $this->getDoctrine()->getEntityManager()->createQuery(
		'SELECT sp FROM ZpiPageBundle:SubPage sp 
		 WHERE sp.title_canonical = :title_canonical'
		 )->setParameter('title_canonical', $titleCanonical);
		$subpage = $query->getSingleResult();
				
		
		$form = $this->createFormBuilder($subpage)			
			->add('title', 'text', array('label' => 'subpage.form.title'))
			->add('content', 'textarea', array('label' => 'subpage.form.content'))
			->add('position', 'choice', array('choices' => array('Top' => 'Top', 'Left' => 'Left'),
			'required' => true))
			->getForm();
			
		if ($request->getMethod() == 'POST')
		{
			$oldSubpageTitle = $subpage->getTitle();
			$form->bindRequest($request);
			
			// jezeli nie ma tytulu nie podejmuj zadnych akcji
			if($subpage->getTitleCanonical() =='')
			{
				$this->get('session')->setFlash('notice', 'Please fill out the title.');
				$subpage->setTitle($oldSubpageTitle);
			}
			// w pp przypadku sprawdzenie czy podstrona ze zmieniona nazwa nie istnieje juz w bazie danych
			else
			{
				// pobranie wszystkich pozostalych podstron		
				$query = $em->createQuery(
				'SELECT sp FROM ZpiPageBundle:SubPage sp 
				 WHERE sp.title_canonical != :title_canonical'
				 )->setParameter('title_canonical', $titleCanonical);	
				 	 
				$subpages = $query->getResult();		 
				$subpage_exists = false;
				foreach($subpages as $spage)
				{			
					if($spage->getTitleCanonical() == $subpage->getTitleCanonical())
					{
						$subpage_exists = true;
					}
					
				}			
				if($subpage_exists)
				{
					$this->get('session')->setFlash('notice', 'You cant have two subpages with the same 													name!');
					$subpage->setTitle($oldSubpageTitle);
				}			
				else if ($form->isValid())
				{							
					$em->flush();
					$this->get('session')->setFlash('notice', 'Subpage successfully updated!');
					return $this->redirect($this->generateUrl('subpage_show',
						 array('titleCanonical' => $subpage->getTitleCanonical())));
				}
				
			}
			
			
		}
			
		return $this->render('ZpiPageBundle:SubPage:update.html.twig', array(
			'form' => $form->createView(), 'subpage' => $subpage,));
		
	}
        
    // TODO pobieranie id konferencji, aby wiedziec, ktore wyswietlic
    public function subPageMenuTopAction()
    {
        
        $subpages = $this->getDoctrine()
                ->getEntityManager()
                ->createQuery('SELECT sp 
                    FROM ZpiPageBundle:SubPage sp
                    WHERE sp.conference = :conference')                
                ->setParameter('conference', $this->getRequest()->getSession()->get('conference'))
                ->getResult();        
        $this->getRequest()->getSession()->set('subpages', $subpages);
        return $this->render('ZpiPageBundle:SubPage:subPagesMenuTop.html.twig', array('subpages' => $subpages));
        
    }
    
    // TODO pobieranie id konferencji, aby wiedziec, ktore wyswietlic
    public function subPageMenuLeftAction()
    {
    	$subpages = $this->getRequest()->getSession()->get('subpages');
        return $this->render('ZpiPageBundle:SubPage:subPagesMenuLeft.html.twig', array('subpages' => $subpages));
    	
    }
    
    public function getSubpages()
    {
        return $this->spages;
    }
 
}
