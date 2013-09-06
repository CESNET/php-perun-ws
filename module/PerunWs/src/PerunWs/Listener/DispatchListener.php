<?php

namespace PerunWs\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;


class DispatchListener extends AbstractListenerAggregate
{


    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('dispatch', array(
            $this,
            'onDispatch'
        ), 1000);
        
        $this->listeners[] = $events->attach('dispatch.error', array(
            $this,
            'onDispatchError'
        ), 1000);
    }


    public function onDispatch(MvcEvent $e)
    {
        _dump('DISPATCH');
        
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $e->getResponse();
        
        // authenticate
    }


    public function onDispatchError(MvcEvent $e)
    {
        // FIXME - move to separate class
        $e->stopPropagation(true);
        
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $e->getResponse();
        
        $error = $e->getError();
        
        if ($error) {
            _dump('DISPATCH ERROR: ' . $error);
        }
        
        $result = $e->getResult();
        if ($result) {
            $exception = $result->exception;
            if ($exception) {
                _dump(sprintf("EXCEPTION [%s]: %s", get_class($exception), $exception->getMessage()));
                _dump("$exception");
            }
        }
        
        $response->setStatusCode(500);
        
        return $response;
    }
}