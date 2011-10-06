<?php

namespace Zpi\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationController extends BaseController
{
    public function emailValAction()
    {

        # Is the request an ajax one?
        if ($this->container->get('request')->isXmlHttpRequest())
        {
            # Lets get the email parameter's value
            $email = $this->container->get('request')->request->get('email');
           #if the email is correct
            $emailConstraint = new Email();
            // all constraint "options" can be set this way
            $emailConstraint->message = 'Invalid email address';

            // use the validator to validate the value
            $errorList = $this->container->get('validator')->validateValue($email, $emailConstraint);

            if(empty($email))
            {
                $response = new Response(json_encode(array('reply' => 'E-mail cannot be empty')));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if(count($errorList) > 0)
            {
                $response = new Response(json_encode(array('reply' => $errorList[0]->getMessage())));
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
        $response = new Response(json_encode(array('reply' => 'Nothing')));
               $response->headers->set('Content-Type', 'application/json');
               return $response;
    } 
}