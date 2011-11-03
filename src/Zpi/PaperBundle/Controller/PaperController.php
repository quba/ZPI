<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zpi\PaperBundle\Form\Type\NewPaperType;


class PaperController extends Controller
{
    
    public function newAction(Request $request)
    {
        $paper = new Paper();
        $form = $this->createForm(new NewPaperType(), $paper);

        if ($request->getMethod() == 'POST')
	{
            $form->bindRequest($request);

            if ($form->isValid())
            {
                $user = $this->get('security.context')->getToken()->getUser();
                $paper->setOwner($user);
                $user->addAuthorPaper($paper);
                
            	$em = $this->getDoctrine()->getEntityManager();
		$em->persist($paper);
		$em->flush();
                
                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, your action succeeded!');
		
		return $this->redirect($this->generateUrl('papers_show'));
            }
	}
        
        return $this->render('ZpiPaperBundle:Paper:new.html.twig', array('form' => $form->createView()));
    }
    
    public function showAction()
    {
	//$user = $this->get('security.context')->getToken()->getUser();
		
	return $this->render('ZpiPaperBundle:Paper:show.html.twig');
    }
    
    public function detailsAction($id)
    {
	$user = $this->get('security.context')->getToken()->getUser();
	//$paper = $user->getPapers()->get($id);
        
        $query = $this->getDoctrine()->getEntityManager()->createQuery(
            'SELECT p FROM ZpiPaperBundle:Paper p INNER JOIN p.users u 
                WHERE p.id = :id AND u.id = :uid'
            )->setParameter('id', $id)
            ->setParameter('uid', $user->getId());
	
        $paper = $query->getSingleResult();
	
	if(!$paper)
	{
            throw $this->createNotFoundException('Not Found, You mad?!');
	}
                
	return $this->render('ZpiPaperBundle:Paper:details.html.twig', array('paper' => $paper));
    }
}
