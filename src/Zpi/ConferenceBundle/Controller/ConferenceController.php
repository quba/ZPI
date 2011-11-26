<?php    
namespace Zpi\ConferenceBundle\Controller;

use Zpi\ConferenceBundle\Form\Type\AssignEditorsType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\ConferenceBundle\Entity\Conference;
use Zpi\ConferenceBundle\Form\Type\ConferenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\UserBundle\Entity\User;
use Zpi\PaperBundle\Entity\UserPaper;
use Zpi\ConferenceBundle\Entity\Mail;

/**
 * Kontroler dla klasy Conference.
 * @author lyzkov
 */
class ConferenceController extends Controller
{
    /**
     * Dodawanie nowej konferencji.
     * @param Request $request
     * @author lyzkov
     */
    public function newAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $translator = $this->get('translator');
        
        $conference = new Conference();
        $now = new \DateTime('now');
        $conference->setStartDate($now);
        $conference->setEndDate($now);
        $conference->setAbstractDeadline($now);
        $conference->setConfirmationDeadline($now);
        $conference->setCorrectedPaperDeadline($now);
        $conference->setPaperDeadline($now);
        $conference->setBookingstartDate($now);
        $conference->setBookingendDate($now);
        
        $form = $this->createForm(new ConferenceType(), $conference);
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
            
            if ($form->isValid()) {
                $conference->setStatus(Conference::STATUS_OPEN);
                $user->addConference($conference);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($conference);
                $em->flush();
                $this->get('session')->setFlash('notice',
                    $translator->trans('conf.new.success'));
                
                return $this->redirect($this->generateUrl('conference_manage', array('_conf' => $conference->getPrefix())));
            }
        }
        
        return $this->render('ZpiConferenceBundle:Conference:new.html.twig',
            array('form' => $form->createView()));
    }
    
    
    /**
     * Edycja konferencji.
     * @param Request $request
     * @param unknown_type $id
     * @author lyzkov
     */
    public function editAction(Request $request)
    {
        //TODO Autoryzacja użytkownika.
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $translator = $this->get('translator');
        
        $conference = $request->getSession()->get('conference');
        
        if (!$conference || !$user->getConferences()->contains($conference))
        {
         //   throw $this->createNotFoundException(
        //        $translator->trans('conf.exception.conference_not_found'));
        }
        
        $id = $conference->getId();
        
        // Jeśli konferencja ma satus: zamknięty, to zwróć błąd 404.
        // TODO Nie jestem pewien czy tego nie trzeba będzie inaczej rozwiązać.
        if ($conference->getStatus() == Conference::STATUS_CLOSED)
        {
            return $this->createNotFoundException(
                $translator->trans('conf.exception.closed: %id%', array('%id%' => $id)));
        }
        
        $form = $this->createForm(new ConferenceType(), $conference);
        
        $repository = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Registration');
        $registration = $repository->find($id);
        
        if (!is_null($registration))
        {
            $form->remove('startDate');
            $form->remove('endDate');
            $form->remove('deadline');
        }
        
        if($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
            
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($conference);
                $em->flush();
                $this->get('session')->setFlash('notice',
                        $translator->trans('conf.edit.success'));
            
                return $this->redirect($this->generateUrl('conference_manage'));
            }
        }
        
        return $this->render('ZpiConferenceBundle:Conference:edit.html.twig',
            array('form' => $form->createView(), 'id' => $id));
    }
    
    /**
     * Wyświetla listę organizowanych konferencji.
     * TODO Co z listą wszystkich otwartych konferencji?
     * @author lyzkov
     */
    public function listAction()
    {
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $conferences = $user->getConferences();
        
        return $this->render('ZpiConferenceBundle:Conference:list.html.twig',
            array('conferences' => $conferences));
    }
    
    /**
     * Panel zarządzania konferencją.
     * @param unknown_type $id
     * @author lyzkov
     */
    public function manageAction(Request $request)
    {
        //TODO Autoryzacja użytkownika.
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $translator = $this->get('translator');
        
        $conference = $request->getSession()->get('conference');
        
        if (is_null($conference) || !$user->getConferences()->contains($conference))
        {
            throw $this->createNotFoundException(
                $translator->trans('conf.exception.conference_not_found'));
        }
        
        $id = $conference->getId();
        
        // Jeśli konferencja ma satus: zamknięty, to zwróć błąd 404.
        // TODO Nie jestem pewien czy tego nie trzeba będzie inaczej rozwiązać.
                // może nie tyle 404 (bo ona istnieje tak naprawdę), co zwykły response z info, że zamknięta i przekierowaniem) @quba
        if ($conference->getStatus() == Conference::STATUS_CLOSED)
        {
           throw $this->createNotFoundException(
                $translator->trans('conf.exception.closed: %id%', array('%id%' => $id)));
        }
        
        $registrations = $conference->getRegistrations();
        
        return $this->render('ZpiConferenceBundle:Conference:manage.html.twig',
            array('conference' => $conference,
                'registrations' => $registrations));
    }
    
    /**
     * Przypisuje recenzentów do pracy.
     * @param unknown_type $paper_id
     * @author lyzkov
     */
    public function assignEditorsAction(Request $request, $paper_id)
    {
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        $translator = $this->get('translator');
        
        //TODO Autoryzacja użytkownika.
        
        $conference = $request->getSession()->get('conference');
        
        if (!$conference || !$user->getConferences()->contains($conference))
        {
        //   throw $this->createNotFoundException(
        //        $translator->trans('conf.exception.conference_not_found'));
        }
        
        $id = $conference->getId();
        
        // Jeśli konferencja ma satus: zamknięty, to zwróć błąd 404.
        // TODO Nie jestem pewien czy tego nie trzeba będzie inaczej rozwiązać.
        if ($conference->getStatus() == Conference::STATUS_CLOSED)
        {
            return $this->createNotFoundException(
                $translator->trans('conf.exception.conference_closed: %id%',
                    array('%id%' => $id)));
        }
        
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
        $query = $repository->createQueryBuilder('p')
                    ->innerJoin('p.registrations', 'r')
                    ->innerJoin('r.conference', 'c')
                        ->where('c.id = :conf_id')
                            ->setParameter('conf_id', $id)
                        ->andWhere('p.id = :paper_id')
                            ->setParameter('paper_id', $paper_id)
                    ->getQuery();
        $paper = $query->getOneOrNullResult();
        
        if (!$paper)
        {
            throw $this->createNotFoundException(
                $translator->trans('conf.exception.paper_not_found: %id%', array('%id' => $paper_id)));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('ZpiUserBundle:User');
        $editors = $repository->findAllByRoles(array(User::ROLE_EDITOR));
        $techEditors = $repository->findAllByRoles(array(User::ROLE_TECH_EDITOR));
        
        //TODO Wyświetlanie na liście formularza użytkowników z rolami edytorów
//         $qb = $this->getDoctrine()->getRepository('ZpiUserBundle:User')
//             ->createQueryBuilder('u');
        
        $form = $this->createForm(new AssignEditorsType(), $paper);
        
        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);
        
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($paper);
                $em->flush();
                
                $this->get('session')->setFlash('notice', $translator->trans('conf.edit.success'));
                
                return $this->redirect($this->generateUrl('conference_manage'));
            }
        }
        
        return $this->render('ZpiConferenceBundle:Conference:assign_editors.html.twig',
                            array('editors' => $editors, 'techEditors' => $techEditors,
                                'form' => $form->createView(), 'paper' => $paper));
    }
    
    /**
     * Ustawia terminy deadline dla pracy.
     * @param unknown_type $paper_id
     * @author lyzkov
     */
    public function deadlineAction($paper_id)
        {
                return new Response('Page under construction...');
    }

    public function mailAction(Request $request)
    {
        $translator = $this->get('translator');
        $task = new Mail();
        $task->setTitle('Temat');
        $task->setContent('Konferencja odwolana');

        $form = $this->createFormBuilder($task)
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->getForm();

            if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) 
            {
            $mailer = $this->get('messager');
            $temat = $task->getTitle();
            $content= $task->getContent();
//            $conference = $request->getSession()->get('conference');
//            $to = $conference->getRegistration();
//            foreach ($to as $row) {
//            $row=$this->getParticipant()->getEmail();
//             }
//          jakaś moja nieudana pruba mejlingu dla wszyskich uczestników 
            $to[0]= 'zpimailer@gmail.com';
            $mailer->sendMail($temat, 'zpimailer@gmail.com', $to,
            'ZpiConferenceBundle:Conference:mail_to_all.txt.twig', array('content' => $content));
            $this->get('session')->setFlash('notice',
            $translator->trans('mail.new.succes'));

            return $this->redirect($this->generateUrl('homepage'));
                }
            }
            return $this->render('ZpiConferenceBundle:Conference:new_mail.html.twig', array(
            'form' => $form->createView()));
            }
			
public function mailContentAction(Request $request)
    {
       $translator = $this->get('translator');
        $conference = $request->getSession()->get('conference');
        $conference->setConfirmationMailContent('');
        $conference->setRegistrationMailContent('');

        $form = $this->createFormBuilder($conference)
            ->add('ConfirmationMailContent', 'textarea')
            ->add('RegistrationMailContent', 'textarea')
            ->getForm();

            if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

                        if ($form->isValid())
            {
            $content1=$conference->getConfirmationMailContent();
            $content2=$conference->getRegistrationMailContent();
            $this->get('session')->setFlash('notice',
            $translator->trans('mail.content.succes'));
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($conference);
            $em->flush();

            return $this->redirect($this->generateUrl('homepage'));
                }

            }
            return $this->render('ZpiConferenceBundle:Conference:mail_content.html.twig', array(
            'form' => $form->createView()));
            }
            
            public function papersListAction()
            {
                
            }
    
    
}
