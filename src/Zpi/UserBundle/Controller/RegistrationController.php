<?php

namespace Zpi\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RegistrationController extends BaseController
{
    public function registerAction()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $prefix = $this->container->get('request')->attributes->get('_conf');
        $conference = $em->getRepository('ZpiConferenceBundle:Conference')
                ->findOneBy(array('prefix' => $prefix));
        if(empty($conference))
            throw new NotFoundHttpException('conference.notfound');
        $this->container->get('router')->getContext()->setParameter('_conf', $prefix);
        
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();

            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                $route = 'fos_user_registration_check_email';
            } else {
                $this->authenticateUser($user);
                $route = 'fos_user_registration_confirmed';
            }

            $this->setFlash('fos_user_success', 'registration.flash.user_created');
            $url = $this->container->get('router')->generate($route);

            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
            'theme' => $this->container->getParameter('fos_user.template.theme'),
        ));
    }
    
    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:checkEmail.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction($token)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $this->container->get('fos_user.user_manager')->updateUser($user);
        $this->authenticateUser($user);

        return new RedirectResponse($this->container->get('router')->generate('fos_user_registration_confirmed'));
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:confirmed.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }
    
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
        $response = new Response('Page not found.', 404);
               $response->headers->set('Content-Type', 'application/json');
               return $response;
    } 
}