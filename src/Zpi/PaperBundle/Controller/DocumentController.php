<?php

namespace Zpi\PaperBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Zpi\PaperBundle\Entity\Review;

use Zpi\PaperBundle\Entity\Paper;
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\UserBundle\Entity\User;
use Zpi\PaperBundle\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class DocumentController extends Controller
{
    
    /**
     * Umożliwia submisję pracy.
     * @param unknown_type $id
     */
    public function uploadAction(Request $request, $id)
    {
        //TODO: sprawdzenie praw do uploadu dla paperu o danym ID
        
        $session = $request->getSession();
        $conference = $session->get('conference');
        $trans = $this->get('translator');
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $paper = $em->getRepository('ZpiPaperBundle:Paper')
            ->find($id);
        $currDate = new \DateTime();
        $lastDoc = $paper->getLastDocument();
        if (isset($lastDoc) && $lastDoc->getStatus() == Review::MARK_NO_MARK &&
                $currDate > $conference->getPaperDeadline())
        {
            throw $this->createNotFoundException($trans->trans(
            	'document.exception.submission_before_review'));
        }
        
        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('file')
            ->add('pagescount')
            ->add('comment')
            ->getForm();

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $curr_ver = $lastDoc ? $lastDoc->getVersion() : 0;
                
                $document->setUser($user);
                $document->setVersion(++$curr_ver);
                $document->setPaper($paper);
                $document->setUploadDate(new \DateTime('now'));
                $document->setStatus(Review::MARK_NO_MARK);
                $paper->setStatus(Review::MARK_NO_MARK);
                $em->persist($document);
                $em->persist($paper);
                if(substr($document->getPath(), -4) != '.zip') // takie dodatkowe zabezpieczenie, jeszcze się doda regexp dla inputa
                        throw $this->createNotFoundException('Dozwolone sa tylko paczki zip.');
                $em->flush();

                $session = $this->getRequest()->getSession();
                $session->setFlash('notice', 'Congratulations, upload succeeded!');
                return $this->redirect($this->generateUrl('paper_details', array('id' => $id)));
            }
        }

        return $this->render('ZpiPaperBundle:Document:upload.html.twig', array('form' => $form->createView(), 'id' => $id));
    }
    
    /**
     * Umożliwia pobranie pracy na dysk.
     * @param unknown_type $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
