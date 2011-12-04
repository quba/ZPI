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
use Zpi\ConferenceBundle\Form\Type\SetAmountPaidType;
use Zpi\PaperBundle\Form\Type\SetRealDocumentPagesType;

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
            array('form' => $form->createView(), 'conference' => $conference));
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
           throw $this->createNotFoundException(
               $translator->trans('conf.exception.conference_not_found'));
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
            
                //return $this->redirect($this->generateUrl('conference_manage'));
            }
            unset($conference->file);
        }
        
        return $this->render('ZpiConferenceBundle:Conference:edit.html.twig',
            array('form' => $form->createView(), 'id' => $id, 'conference' => $conference));
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
                $translator->trans('conf.exception.not_found'));
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
         //  throw $this->createNotFoundException(
         //       $translator->trans('conf.exception.conference_not_found'));
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
                    ->innerJoin('p.registration', 'r')
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
        $task->setTitle('');
        $task->setContent('');

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
            
            public function papersPaymentsListAction()
            {
                $translator = $this->get('translator');
                $conference = $this->get('request')->getSession()->get('conference');
                $user = $this->get('security.context')->getToken()->getUser();
                
                // zmienna określająca czy jest organizatorem tej konferencji
                $valid_organizer = false;
                foreach($user->getConferences() as $conf)
                {
                    if($conf->getId() == $conference->getId())
                        $valid_organizer = true;
                }
                // TODO podstrona informacyjna z błędem
                if((false === $this->get('security.context')->isGranted('ROLE_ORGANIZER')) || !$valid_organizer)
                {
                    throw $this->createNotFoundException($translator->trans('conf.form.access_forbidden'));
                }
                
                $forms = array();
                $formsViews = array();
                $i = 0;
                $documents = array();
                $papers = $conference->getSubmittedPapers();
                foreach($papers as $paper)
                {
                    if($paper->isAccepted())
                    {   
                        $documents[] = $paper->getLastDocument();
                        $form = $this->get('form.factory')->createNamedBuilder(new SetRealDocumentPagesType(), 'paper' . $i, $documents[$i])                                                       
                                ->getForm();
                        $forms[] = $form;
                        $formsViews[] = $form->createView();
                        $i++;
                    }
                }
                
                if ($this->getRequest()->getMethod() == 'POST') {
                    
                    for($j = 0; $j < $i; $j++)
                    {
                        if($this->getRequest()->request->has('paper' . $j))
                        {
                            $forms[$j]->bindRequest($this->getRequest());                    
                            if ($forms[$j]->isValid()) { 
                                $registration = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Registration')
                                        ->createQueryBuilder('r')
                                        ->where('r.id = :payer')
                                        ->setParameter('payer', $papers[$j]->getRegistration())                                        
                                        ->getQuery()->getSingleResult();
                                $registration->setCorrectTotalPayment($registration->getTotalPayment() + 
                                        ($documents[$j]->getRealPagesCount() - $documents[$j]->getPagesCount())
                                        *$conference->getExtrapagePrice());
                                $em = $this->getDoctrine()->getEntityManager();                                
                                $em->flush();
                            }
                            return $this->redirect($this->generateUrl('conference_papers_payments_list'));
                                
                        }
                    }
                    //echo '<pre>'; var_dump($this->getRequest()->request->all()); echo '</pre>';
                    
                    
                }
                
                
                return $this->render('ZpiConferenceBundle:Conference:papers_payments_list.html.twig',
                        array('submitted_papers' => $conference->getSubmittedPapers(), 'forms' => $formsViews));
            }
            
            // TODO!
            public function registrationsListAction()
            {
                $translator = $this->get('translator');
                $conference = $this->get('request')->getSession()->get('conference');
                $user = $this->get('security.context')->getToken()->getUser();
                
                // zmienna określająca czy jest organizatorem tej konferencji
                $valid_organizer = false;
                foreach($user->getConferences() as $conf)
                {
                    if($conf->getId() == $conference->getId())
                        $valid_organizer = true;
                }
                // TODO podstrona informacyjna z błędem
                if((false === $this->get('security.context')->isGranted('ROLE_ORGANIZER')) || !$valid_organizer)
                {
                    throw $this->createNotFoundException($translator->trans('conf.form.access_forbidden'));
                }
                
                // Formularze dotyczące wprowadzonej opłaty
                $forms = array();
                $formsViews = array();
                $i = 0;               
                $registrations = $conference->getRegistrations();
                /* Nie chcę wnikać w sposób rozwiązanie tego problemu, ale jeśli już tutaj pobrałeś rejestracjie, to po co
                 * robisz to samo w twigu? Wystarczyło stworzyć sobie odpowiedni obiekt i potem go do forumarza przekazać, 
                 * a nie całą konferencję. Na tej podstronie powinno w sumie 6 zapytań sql, a jest 10. Będzie tyle dodatkowych 
                 * zapytań, ile jest rejestracji (ja nie mam ich wiele). Doctrinowy lazy loading nie sprawdza się dla tego 
                 * typu list. On jest przeznaczony do prostych operacji. Lista urośnie do 100 prac i będziesz miał tutaj 
                 * 105 zapytań sql zamiast 6. A wystarczy proste zapytanie selecta z rejestracji, gdzie jest odpowiedni 
                 * conf_id oraz confirmed = 1. // @quba
                 */
                foreach($registrations as $registration)
                {
                    if($registration->getConfirmed())
                    {   
                        
                        $form = $this->get('form.factory')->createNamedBuilder(new SetAmountPaidType(), 
                                'registration' . $i, $registrations[$i])                                                       
                                ->getForm();
                        $forms[] = $form;
                        $formsViews[] = $form->createView();
                        $i++;
                    }
                }
                
                // Obsługa powyższych formularzy
                if ($this->getRequest()->getMethod() == 'POST') {
                    
                    for($j = 0; $j < $i; $j++)
                    {
                        if($this->getRequest()->request->has('registration' . $j))
                        {
                            $forms[$j]->bindRequest($this->getRequest());                    
                            if ($forms[$j]->isValid()) {                                
                                $this->getDoctrine()->getEntityManager()->flush();                               
                            }
                            //return $this->redirect($this->generateUrl('conference_registrations_list'));
                            // przepiękny redirect z tej samej podstrony na... tę samą // @quba    
                        }
                    }
                    //echo '<pre>'; var_dump($this->getRequest()->request->all()); echo '</pre>';
                    //na dole na toolbarze symfony masz taką zębatkę. Tam kliknij i masz wszystkie dane na temat requesta 
                    //w formie ładnej tabelki. // @quba
                    
                }
                
                return $this->render('ZpiConferenceBundle:Conference:registrations_list.html.twig',
                        array('conference' => $conference, 'forms' => $formsViews));
            }

               public function notificationAction(Request $request)
    {
        $mailer = $this->get('messager');
        $to[0]= 'zpimailer@gmail.com';
        $conference = $request->getSession()->get('conference');
        $registrations = $conference->getRegistrations();
        $mailer->sendMail('pusty', 'zpimailer@gmail.com', $to,
        'ZpiConferenceBundle:Conference:mail_to_all.txt.twig', array('content' => "treś"));
        return $this->redirect($this->generateUrl('conference_manage'));
    }
    
        public function paymentNotificationAction(Request $request)   {
        $translator = $this->get('translator');
        $this->get('session')->setFlash('notice',
        $translator->trans('mail.new.payment.succes'));
        return $this->redirect($this->generateUrl('conference_registrations_list'));
        }
}
