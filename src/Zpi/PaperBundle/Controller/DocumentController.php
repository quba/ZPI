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
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $conference = $this->getRequest()->getSession()->get('conference');
        $trans = $this->get('translator');
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
        $queryBuilder = $repository->createQueryBuilder('p')
            ->innerJoin('p.registration', 'r')
            ->innerJoin('r.conference', 'c')
            ->innerJoin('p.users', 'up')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId())
                ->andWhere('p.id = :paper_id')
                    ->setParameter('paper_id', $id);
        
        $query = $queryBuilder->andWhere('up.author = :auth')
             ->setParameters(array('auth' => UserPaper::TYPE_AUTHOR_EXISTING))
             ->getQuery();
        $paper = $query->getOneOrNullResult();
        
        //$paper = $em->getRepository('ZpiPaperBundle:Paper')->find($id); // z żalem w sercu zamieniam to na tego potwora powyżej // @quba
        
        if(empty($paper))
            throw $this->createNotFoundException($trans->trans('paper.upload.notallowed'));
        
        $currDate = new \DateTime();
        $lastDoc = $paper->getLastDocument();
        $registration = $this->getDoctrine()->getEntityManager()->createQuery(
                'SELECT r FROM ZpiConferenceBundle:Registration r
                 WHERE r.conference = :conf AND r.participant = :user')
                ->setParameters(array(
                    'conf' => $conference->getId(),
                    'user' => $user->getId()))
                ->getOneOrNullResult();
        if ($currDate > $registration->getSubmissionDeadline() && !isset($lastDoc))
        {
            throw $this->createNotFoundException($trans->trans(
            	'document.exception.submission_after_deadline'));
        }
        else if(isset($lastDoc) && $lastDoc->getStatus() == Review::MARK_NO_MARK)
        {
            throw $this->createNotFoundException($trans->trans(
            	'document.exception.submission_before_review'));
        }
        else if(isset($lastDoc) && $currDate > $registration->getCamerareadyDeadline())
        {
            throw $this->createNotFoundException($trans->trans(
            	'document.exception.submission_after_deadline'));
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
        
        $user = $this->get('security.context')->getToken()->getUser();
        $document = $this->getDoctrine()->getEntityManager()->getRepository('ZpiPaperBundle:Document')
						->find($id);
        
        if(empty($document))
        {
            throw $this->createNotFoundException('Nie ma takiego pliku.');
        }
        
        $user = $this->get('security.context')->getToken()->getUser();
        $conference = $this->getRequest()->getSession()->get('conference');
        $translator = $this->get('translator');
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
        $queryBuilder = $repository->createQueryBuilder('p')
            ->innerJoin('p.registration', 'r')
            ->innerJoin('r.conference', 'c')
            ->innerJoin('c.organizers', 'u')
            ->innerJoin('p.users', 'up')
                ->where('c.id = :conf_id')
                    ->setParameter('conf_id', $conference->getId())
                ->andWhere('up.user = :user_id')
                    ->setParameter('user_id', $user->getId())
                ->andWhere('p.id = :paper_id')
                    ->setParameter('paper_id', $document->getPaper()->getId());
        
        $query = $queryBuilder->andWhere('up.author = :auth OR up.editor = :edit OR up.techEditor = :techedit OR u.id = :uid')
             ->setParameters(array('auth' => UserPaper::TYPE_AUTHOR_EXISTING,
                                   'edit' => 1,
                                   'techedit' => 1,
                                   'uid' => $user->getId()))
             ->getQuery();
        $paper = $query->getOneOrNullResult();
        
        //$paper = $em->getRepository('ZpiPaperBundle:Paper')->find($id); // z żalem w sercu zamieniam to na tego potwora powyżej // @quba
        
        if(empty($paper))
            throw $this->createNotFoundException($translator->trans('doc.err.notfound'));
        
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
