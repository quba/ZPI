<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Paper;
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
                $user->addPaper($paper); // tak, tylko tyle. W ten sposób po flushu uzupełni się też tabela relacji. 
                                         // tak, byłem pewien, że zamiast poszukać, spojrzysz jak ja to zrobiłem.
                                         // pamiętaj też o setterze dla tej kolekcji w klasie z manytomany: 
                                         // php app/console doctrine:generate:entities Zpi/TwojaPaczka stworzy tego settera
                
            	$em = $this->getDoctrine()->getEntityManager();
		$em->persist($paper);
		$em->flush();
                
                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, your action succeeded!');
		
		return $this->redirect($this->generateUrl('homepage'));
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
	$paper = $user->getPapers()->get($id);        
	return $this->render('ZpiPaperBundle:Paper:details.html.twig', array('paper' => $paper));
    }
}
