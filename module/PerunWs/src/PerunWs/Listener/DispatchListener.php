<?php

namespace PerunWs\Listener;

use Zend\Mvc\Application;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Mvc\MvcEvent;
use PerunWs\Authentication;
use PerunWs\Exception\MissingDependencyException;


class DispatchListener extends AbstractListenerAggregate implements EventManagerAwareInterface
{

    /**
     * @var Authentication\Adapter\AdapterInterface
     */
    protected $authenticationAdapter;

    /**
     * @var EventManagerInterface
     */
    protected $events;


    /**
     * @return Authentication\Adapter\AdapterInterface
     */
    public function getAuthenticationAdapter()
    {
        return $this->authenticationAdapter;
    }


    /**
     * @param Authentication\Adapter\AdapterInterface $authenticationAdapter
     */
    public function setAuthenticationAdapter(Authentication\Adapter\AdapterInterface $authenticationAdapter)
    {
        $this->authenticationAdapter = $authenticationAdapter;
    }


    /**
     * @param EventManagerInterface $events
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this)
        ));
        $this->events = $events;
    }


    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }


    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('dispatch', array(
            $this,
            'onDispatch'
        ), 1000);
        
        $this->listeners[] = $events->attach('dispatch', array(
            $this,
            'onPostDispatch'
        ), - 1000);
        
        $this->listeners[] = $events->attach('dispatch.error', array(
            $this,
            'onDispatchError'
        ), - 1000);
    }


    /**
     * @param MvcEvent $event
     * @throws MissingDependencyException
     * @return \Zend\Http\PhpEnvironment\Response|void
     */
    public function onDispatch(MvcEvent $event)
    {
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        $request = $event->getRequest();
        
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $event->getResponse();
        
        $authenticationAdapter = $this->getAuthenticationAdapter();
        if (null === $authenticationAdapter) {
            throw new MissingDependencyException('authentication adapter');
        }
        
        $authException = null;
        $statusCode = null;
        
        try {
            $clientInfo = $authenticationAdapter->authenticate($request);
        } catch (Authentication\Exception\AuthenticationException $e) {
            $authException = $e;
            $statusCode = 401;
        } catch (\Exception $e) {
            $authException = $e;
            $statusCode = 500;
        }
        
        if ($authException) {
            $this->getEventManager()->trigger('auth.error', $this, array(
                'mvcEvent' => $event,
                'exception' => $authException
            ));
            
            $event->stopPropagation(true);
            $response->setStatusCode($statusCode);
            return $response;
        }
        
        /*
         * If there is a query parameter "fresh = 1", do not read from the cache.
         */
        if (1 == $request->getQuery('fresh')) {
            $cacheStorage = $event->getApplication()
                ->getServiceManager()
                ->get('PerunWs\CacheStorage');
            $cacheStorage->getOptions()->setReadable(false);
        }
        
        $event->setParam('clientId', $clientInfo->getClientId());
    }


    /**
     * @param MvcEvent $event
     */
    public function onPostDispatch(MvcEvent $event)
    {
        $this->getEventManager()->trigger('dispatch.post', $this, array(
            'mvcEvent' => $event
        ));
    }


    /**
     * @param MvcEvent $event
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function onDispatchError(MvcEvent $event)
    {
        $statusCode = 500;
        
        $event->stopPropagation(true);
        
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $event->getResponse();
        
        $error = $event->getError();
        if ($error === Application::ERROR_ROUTER_NO_MATCH) {
            $statusCode = 404;
        }
        
        $this->getEventManager()->trigger('dispatch.error', $this, array(
            'mvcEvent' => $event
        ));
        
        $response->setStatusCode($statusCode);
        
        return $response;
    }
}