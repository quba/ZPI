<?php

namespace Zpi\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends BaseController
{
    public function emailValAction()
    {

        # Is the request an ajax one?
        if ($this->container->get('request')->isXmlHttpRequest())
        {
            # Lets get the email parameter's value
            $title = $this->container->get('request')->request->get('email');
           #if the email is correct
            if(empty($email))
            {
                $response = new Response(json_encode(array('reply' => 'E-mail nie może być pusty')));
               $response->headers->set('Content-Type', 'application/json');
               return $response;
            }
            else
            {
                $response = new Response(json_encode(array('reply' => 'OK!')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }#endelse

        }# endif this is an ajax request
        $response = new Response(json_encode(array('reply' => 'asdf')));
               $response->headers->set('Content-Type', 'application/json');
               return $response;
    } 
}