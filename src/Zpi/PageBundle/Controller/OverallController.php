<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/*
 * Zakładam, że to będzie kontroler skonfigurowany jako serwis po to, by móc tutaj wrzucać jakieś globalne funkcje.
 * Jestem pewien, że można to zrobić bardziej elegancko.
 */


class OverallController extends Controller
{
    public function conf($em, $router, $request)
    {    
        $prefix = $request->attributes->get('_conf');
        $conference = $em->getRepository('ZpiConferenceBundle:Conference')
                ->findOneBy(array('prefix' => $prefix));
	if(empty($conference))
            throw $this->createNotFoundException('conference.notfound');
        $router->getContext()->setParameter('_conf', $prefix);
        return $conference;
    }
}
