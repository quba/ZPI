<?php

namespace Zpi\PaperBundle\Controller;

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
        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('fileName')
            ->add('file')
            ->add('pagescount')
            ->getForm();

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                
                $paper = $this->getDoctrine()->getEntityManager()->getRepository('ZpiPaperBundle:Paper')
						->find($id);
                $document->setPaper($paper);
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
        $document = $this->getDoctrine()->getEntityManager()->getRepository('ZpiPaperBundle:Document')
						->find($id);
        
        $ext = explode('.', $document->getPath());
        $response =  new Response();
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $document->getFileName() . '.' . $ext[1] . '"');
        $response->send();
        ob_clean();
        flush();
        readfile($document->getAbsolutePath());
        return $response;
        
    }
}
