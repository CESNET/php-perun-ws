<?php

namespace PerunWs\Listener;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;


class LogListener extends AbstractSharedListenerAggregate
{


    public function attachShared(SharedEventManagerInterface $sharedEvents)
    {
        $this->addListener($sharedEvents, 'PerunWs\Listener\DispatchListener', 'dispatch.post', array(
            $this,
            'onDispatchPost'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\Listener\DispatchListener', 'dispatch.error', array(
            $this,
            'onDispatchError'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\Listener\DispatchListener', 'auth.error', array(
            $this,
            'onAuthError'
        ));
    }


    public function onDispatchPost(EventInterface $event)
    {
        /* @var $mvcEvent \Zend\Mvc\MvcEvent */
        $mvcEvent = $event->getParam('mvcEvent');
        if ($mvcEvent) {
            $routeMatch = $mvcEvent->getRouteMatch();
            $this->log(sprintf("[%s] controller=%s action=%s", $mvcEvent->getParam('clientId'), $routeMatch->getParam('controller'), $routeMatch->getParam('action')));
        }
    }


    public function onDispatchError(EventInterface $event)
    {
        /* @var $mvcEvent \Zend\Mvc\MvcEvent */
        $mvcEvent = $event->getParam('mvcEvent');
        if ($error = $mvcEvent->getError()) {
            $this->log(sprintf("Dispatch error: %s", $error));
        }
        
        $result = $mvcEvent->getResult();
        if ($result) {
            $exception = $result->exception;
            if ($exception) {
                $this->log(sprintf("EXCEPTION [%s]: %s", get_class($exception), $exception->getMessage()));
                $this->logException($exception);
            }
        }
    }


    public function onAuthError(EventInterface $event)
    {
        $exception = $event->getParam('exception');
        if ($exception) {
            $this->log(sprintf("[%s] %s", get_class($exception), $exception->getMessage()));
            $this->logException($exception);
        }
    }


    public function log($message)
    {
        _dump('LOG: ' . $message);
    }


    public function logException(\Exception $e)
    {
        $this->log("$e");
    }
}