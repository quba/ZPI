<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\UserPaper;
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
	$user = $this->get('security.context')->getToken()->getUser();
	/*$papers = $user->getAuthorPapers(); 
         * funkcja ta niestety pobiera tylko dane z tabeli users_papers i jest to prawidłowe działanie, bo mamy tam one to 
         * many i nie przeskoczymy przez tę tabelę łatwo (żeby pobrać title i inne z papers). Próbowałem tworzyć tzw. proxy
         * metodę w UserPaper (która zwracała getPaper()->getTitle()), jednak to rozwiązanie nie jest wg mnie zbyt wydajne.
         * Owszem działa, ale np. pobranie wszystkich nazw paperów danego usera (standardowe getAuthorPapers()) skutkuje
         * najpierw pobraniem wszystkich rekordów danego usera o typie 0 z tabeli users_papers (OK), jednak potem gdy chcemy
         * odwołać się do tytułu papera (nie ma go w users_papers), korzysta ze stworzonej przeze mnie proxy metody tej klasy
         * o nazwie getTitle(). Wszystko jest spoko, jednak daje nam to dla każdego elementu usera z users_papers dodatkowe
         * zapytanie, które pobiera ten tytuł. Rozwiązanie schludne i wygodne, jednak przydatne tylko przy pobieraniu jednego
         * rekordu (choć i tutaj bym się zastanawiał, bo mamy 2 zapytania, a można to zrobić jednym inner joinem).
         * Dyskusja na ten temat oraz coś o proxy metodzie dla tej asocjacji: 
         * http://stackoverflow.com/questions/3542243/doctrine2-best-way-to-handle-many-to-many-with-extra-columns-in-reference-table
         */
        
        $em = $this->getDoctrine()->getEntityManager();
        $papers = $em->createQuery('SELECT p, up FROM ZpiPaperBundle:UserPaper up INNER JOIN up.paper p where up.user = :uid')
                ->setParameter('uid', $user->getId())
                ->execute();
        
	return $this->render('ZpiPaperBundle:Paper:show.html.twig', array('papers' => $papers));
    }
    
    public function detailsAction($id)
    {
	$user = $this->get('security.context')->getToken()->getUser();
	//$paper = $user->getPapers()->get($id);
        
        $paper = $this->getDoctrine()->getEntityManager()->createQuery(
            'SELECT p, up FROM ZpiPaperBundle:UserPaper up INNER JOIN up.paper p
                WHERE up.paper = :id AND up.user = :uid'
            )->setParameter('id', $id)
             ->setParameter('uid', $user->getId()) // nie chcemy, żeby inni userzy widzieli narze pejpery
             ->getSingleResult();
        /* nie mam już nerwów, żeby z jakichś querybuilderów korzystać. Takie zapytanie jest najbardziej optymalne,
           a jak ktoś znajdzie, jak je wywołać jakoś bezpośrednio pobierając np. $user->getPaper($id) to ma ode mnie
           browara i dodatniego plusa. */
	
	if(!$paper)
	{
            throw $this->createNotFoundException('Not Found, You mad?!');
	}
                
	return $this->render('ZpiPaperBundle:Paper:details.html.twig', array('paper' => $paper));
    }
}
