<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PageController extends Controller
{
    /**
     * Funkcja zarządzająca stroną główną - dla widoku całego systemu dla Admina
     * @return type 
     */
    public function indexAction()
    { 
        $conferences = '';
        $conf = $this->getRequest()->getSession()->get('conference');
        if(empty($conf)) // strona główna systemu
        {    
            $conferences = $this->getDoctrine()->getEntityManager()->getRepository('ZpiConferenceBundle:Conference')
                    ->findAll();  
        }
        return $this->render('ZpiPageBundle:Page:index.html.twig', array('conferences' => $conferences));
    }
    
    /**
     * Funkcja przekierowująca na stronę główną systemu - dla Admina.
     * @return type 
     */
    public function mainAction()
    {
        return $this->redirect($this->generateUrl('homepage', array('_conf' => 'comas')));
    }
    
    /**
     * Funkcja zmieniająca język strony.
     * @param type $lang
     * @return type 
     */
    public function changeLangAction($lang)
    {
        $session = $this->get('session');
        $session->setLocale($lang);
        $last_route = $session->get('last_route', array('name' => 'homepage'));
        return ($this->redirect($this->generateUrl($last_route['name'], $last_route['params'])));
    }
}
