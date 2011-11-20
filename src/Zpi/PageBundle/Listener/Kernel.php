<?php
    
    namespace Zpi\PageBundle\Listener;
 
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\HttpKernel\Event\GetResponseEvent;
    use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
 
    class Kernel 
    {
 
        /**
         * @var \Symfony\Component\DependencyInjection\ContainerInterface
         */
        private $router;
        private $doctrine;
 
        public function __construct(\Symfony\Component\Routing\Router $router, $doctrine) {
            $this->router = $router;
            $this->doctrine = $doctrine;
        }
 
        public function onKernelRequest(GetResponseEvent $event)
        {
            if ($event->getRequestType() !== \Symfony\Component\HttpKernel\HttpKernel::MASTER_REQUEST) {
                return;
            }

            $request = $event->getRequest();
            $session = $request->getSession();
            $parameters = $this->router->match($request->getPathInfo());
            $request->attributes->add($parameters);
            $prefix = $request->attributes->get('_conf');
            $this->router->getContext()->setParameter('_conf', $prefix);
            
            if($prefix != 'comas')
            {    
                $conference = $this->doctrine->getEntityManager()->getRepository('ZpiConferenceBundle:Conference')
                    ->findOneBy(array('prefix' => $prefix));
                
                if(empty($conference) && !empty($prefix))
                    throw new NotFoundHttpException('conf.not.exist');
                else
                {
                    $session->set('conference', $conference);
                    $session->set('comas', false);
                }
            }
            else
                $session->set('comas', true);
            
            $routeName = $parameters['_route'];
            if ($routeName[0] == '_')
            {
                return;
            }
            unset($parameters['_route']);
            $routeData = array('name' => $routeName, 'params' => $parameters);
 
            $thisRoute = $session->get('this_route', array());
            
            // Jeśli tylko odświeżamy tę samą route, to nic nie ustawiamy na nowo
            if ($thisRoute == $routeData) 
            {
                return;
            }
            $session->set('last_route', $thisRoute);
            $session->set('this_route', $routeData);
            
        }
        
        public function onKernelController(FilterControllerEvent $event)
        {
            //$controller = $event->getController();

            //d$event->setController($controller);
        }
    }
