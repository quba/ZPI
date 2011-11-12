<?php    
namespace Zpi\ConferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zpi\ConferenceBundle\Entity\Conference;
use Zpi\ConferenceBundle\Form\Type\ConferenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zpi\UserBundle\Entity\User;

/**
 *
 *
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

        //TODO Autoryzacja użytkownika
        
        $translator = $this->get('translator');
        
        $conference = new Conference();
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        $form = $this->createForm(new ConferenceType(), $conference);
        
        if($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            
            if ($form->isValid()) {
                $conference->setStatus(Conference::STATUS_OPEN);
                $user->addConference($conference);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($conference);
                $em->flush();
                $this->get('session')->setFlash('notice',
                    $translator->trans('conf.new.success'));
                
                return $this->redirect($this->generateUrl('homepage'));
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
    public function editAction(Request $request, $id)
    {
        
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        //TODO Zapezpieczenie przed edytowaniem nie swojej konferencji.
        $conference = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Conference')
                        ->find($id);
        
        if (!$conference)
        {
            throw $this->createNotFoundException(
                $translator->trans('conf.exception.not_found: %id%', array('%id%' => $id)));
        }
        
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
            
                return $this->redirect($this->generateUrl('homepage'));
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
    public function manageAction($id)
    {
        //TODO Autoryzacja użytkownika.
        
        $translator = $this->get('translator');
        
        //TODO Zabezpieczenie przed maniupulowaniem nie swoją konferencją.
        $conference = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Conference')
                        ->find($id);
        
        if (!$conference)
        {
            throw $this->createNotFoundException(
                $translator->trans('conf.exception.not_found: %id%', array('%id%' => $id)));
        }
        
        // Jeśli konferencja ma satus: zamknięty, to zwróć błąd 404.
        // TODO Nie jestem pewien czy tego nie trzeba będzie inaczej rozwiązać.
                // może nie tyle 404 (bo ona istnieje tak naprawdę), co zwykły response z info, że zamknięta i przekierowaniem) @quba
        if ($conference->getStatus() == Conference::STATUS_CLOSED)
        {
            return $this->createNotFoundException(
                $translator->trans('conf.exception.closed: %id%', array('%id%' => $id)));
        }
        
        $registrations = $conference->getRegistrations();
        $repository = $this->getDoctrine()->getRepository('ZpiPaperBundle:Paper');
        $query = $repository->createQueryBuilder('p')
            ->innerJoin('p.registrations', 'r')
            ->innerJoin('r.conference', 'c')
            ->where('c.id = :conf_id')
            ->setParameter('conf_id', $conference->getId())
            ->getQuery();
        $papers = $query->getResult();
        
        return $this->render('ZpiConferenceBundle:Conference:manage.html.twig',
            array('conference' => $conference,
                'registrations' => $registrations,
                'papers' => $papers));
    }
    
    /**
     * Przypisuje recenzentów do pracy.
     * @param unknown_type $paper_id
     * @author lyzkov
     */
    public function assignEditorsAction($id, $paper_id)
    {
        $translator = $this->get('translator');
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();
        
        //TODO Autoryzacja użytkownika.
        
        //TODO Zabezpieczenie przed maniupulowaniem nie swoją konferencją.
        $conference = $this->getDoctrine()->getRepository('ZpiConferenceBundle:Conference')
            ->find($id);
        
        if (!$conference)
        {
            throw $this->createNotFoundException(
            $translator->trans('conf.exception.conference_not_found: %id%', array('%id%' => $id)));
        }
        
        // Jeśli konferencja ma satus: zamknięty, to zwróć błąd 404.
        // TODO Nie jestem pewien czy tego nie trzeba będzie inaczej rozwiązać.
        if ($conference->getStatus() == Conference::STATUS_CLOSED)
        {
            return $this->createNotFoundException(
            $translator->trans('conf.exception.conference_closed: %id%', array('%id%' => $id)));
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
        
        //TODO Formularz dodawania autorów.
        
        //TODO Przerobić widok.
        return $this->render('ZpiConferenceBundle:Conference:assign_editors.html.twig',
                            array('editors' => $editors, 'techEditors' => $techEditors));
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
}
