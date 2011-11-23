<?php

namespace Zpi\PaperBundle\Controller;

use Zpi\PaperBundle\Entity\Review;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\UserBundle\Entity\User;
use Zpi\PaperBundle\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class DocumentController extends Controller
{
    
    public function uploadAction($id)
    {
        //TODO: sprawdzenie praw do uploadu dla paperu o danym ID
        $user = $this->get('security.context')->getToken()->getUser();
        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('file')
            ->add('pagescount')
            ->add('comment')
            ->getForm();

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                
                $paper = $this->getDoctrine()->getEntityManager()->getRepository('ZpiPaperBundle:Paper')
						->find($id);
                $curr_ver = $this->getDoctrine()->getEntityManager()
                                ->createQuery('SELECT max(d.version) maxver FROM ZpiPaperBundle:Document d WHERE d.paper = :paper')
                                ->setParameter('paper', $paper->getId())
                                ->getOneOrNullResult();
                $document->setUser($user);
                $document->setVersion(++$curr_ver['maxver']);
                $document->setPaper($paper);
                $document->setUploadDate(new \DateTime('now'));
                $document->setStatus(Review::MARK_NO_MARK);
                $em->persist($document);
                $em->flush();

                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, upload succeeded!');
                return $this->redirect($this->generateUrl('paper_details', array('id' => $id)));
            }
        }

        return $this->render('ZpiPaperBundle:Document:upload.html.twig', array('form' => $form->createView(), 'id' => $id));
    }
    
    public function downloadAction($id)
    {
        //TODO: sprawdzenie praw do downloadu dla paperu o danym ID
        //TODO: konwencja nazewnictwa ściąganych plików
        //TODO: Wersja pliku i ograniczenie do zipów przy uploadzie
        $user = $this->get('security.context')->getToken()->getUser();
        $document = $this->getDoctrine()->getEntityManager()->getRepository('ZpiPaperBundle:Document')
						->find($id);
        
        if(empty($document))
        {
            throw $this->createNotFoundException('Nie ma takiego pliku.');
        }
        
        $ext = explode('.', $document->getPath());
        $response =  new Response();
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $document->getId() . '_' . $user->getSurName() . '.zip"');
        $response->send();
        ob_clean();
        flush();
        readfile($document->getAbsolutePath());
        return $response;
        
    }
}
