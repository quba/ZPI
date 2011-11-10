<?php

namespace Zpi\PageBundle\Core;
 
use Symfony\Component\Security\Http\HttpUtils as BaseHttpUtils;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/* 
 * Niektóre z tych funkcji można by usunąć, jako że są dokładnie takie jak u rodzica, jednak jakieś błędy były
 * jak zostawiałem samo resetLocale(). Generalnie myślę, że lekka nadmiarowość nie ma jakiegoś dużego znaczenia.
 * Jak w którymś apdejcie zmienią coś w HttpUtils, to się pomyśli nad mądrzejszym dziedziczeniem.
 */ 
class HttpUtils extends BaseHttpUtils
{
    private $router;

    /**
     * Constructor.
     *
     * @param RouterInterface $router An RouterInterface instance
     */
    public function __construct(RouterInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Creates a redirect Response.
     *
     * @param Request $request A Request instance
     * @param string  $path    A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     * @param integer $status  The status code
     *
     * @return Response A RedirectResponse instance
     */
    public function createRedirectResponse(Request $request, $path, $status = 302)
    { 
        if ('/' === $path[0]) {
            $path = $request->getUriForPath($path);
        } elseif (0 !== strpos($path, 'http')) {
            $this->resetLocale($request);
            $path = $this->generateUrl($path, true);
        }

        return new RedirectResponse($path, $status);
    }

    /**
     * Creates a Request.
     *
     * @param Request $request The current Request instance
     * @param string  $path    A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     *
     * @return Request A Request instance
     */
    public function createRequest(Request $request, $path)
    {
        if ($path && '/' !== $path[0] && 0 !== strpos($path, 'http')) {
            $this->resetLocale($request);
            $path = $this->generateUrl($path, true);
        }
        if (0 !== strpos($path, 'http')) {
            $path = $request->getUriForPath($path);
        }

        $newRequest = Request::create($path, 'get', array(), $request->cookies->all(), array(), $request->server->all());
        if ($session = $request->getSession()) {
            $newRequest->setSession($session);
        }

        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $newRequest->attributes->set(SecurityContextInterface::AUTHENTICATION_ERROR, $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR));
        }
        if ($request->attributes->has(SecurityContextInterface::ACCESS_DENIED_ERROR)) {
            $newRequest->attributes->set(SecurityContextInterface::ACCESS_DENIED_ERROR, $request->attributes->get(SecurityContextInterface::ACCESS_DENIED_ERROR));
        }
        if ($request->attributes->has(SecurityContextInterface::LAST_USERNAME)) {
            $newRequest->attributes->set(SecurityContextInterface::LAST_USERNAME, $request->attributes->get(SecurityContextInterface::LAST_USERNAME));
        }

        return $newRequest;
    }

    /**
     * Checks that a given path matches the Request.
     *
     * @param Request $request A Request instance
     * @param string  $path    A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     *
     * @return Boolean true if the path is the same as the one from the Request, false otherwise
     */
    public function checkRequestPath(Request $request, $path)
    {
        if ('/' !== $path[0]) {
            try {
                $parameters = $this->router->match($request->getPathInfo());

                return $path === $parameters['_route'];
            } catch (\Exception $e) {
                return false;
            }
        }

        return $path === $request->getPathInfo();
    }

    // hack (don't have a better solution for now)
    private function resetLocale(Request $request)
    {
        $context = $this->router->getContext();
        if ($context->getParameter('_locale')) {
            return;
        }

        try {
            $parameters = $this->router->match($request->getPathInfo());

            if (isset($parameters['_locale'])) {
                $context->setParameter('_locale', $parameters['_locale']);
            } elseif ($session = $request->getSession()) {
                $context->setParameter('_locale', $session->getLocale());
            }
            if (isset($parameters['_conf'])) {
                $context->setParameter('_conf', $parameters['_conf']);
            }

        } catch (\Exception $e) {
            // let's hope user doesn't use the locale in the path
        }
    }

    private function generateUrl($route, $absolute = false)
    {
        if (null === $this->router) {
            throw new \LogicException('You must provide a RouterInterface instance to be able to use routes.');
        }

        return $this->router->generate($route, array(), $absolute);
    }
}