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
    }


    public function onDispatch(MvcEvent $e)
    {
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        $response = $e->getResponse();
        
        // authenticate
    }
}