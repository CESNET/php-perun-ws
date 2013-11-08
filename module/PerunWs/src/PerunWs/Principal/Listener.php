<?php

namespace PerunWs\Principal;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;
use PerunWs\User\Service\Service;


class Listener extends AbstractListenerAggregate
{

    /**
     * @var Service
     */
    protected $service;


    public function __construct(Service $service)
    {
        $this->service = $service;
    }


    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetch', array(
            $this,
            'onFetch'
        ));
    }


    public function onFetch(ResourceEvent $e)
    {
        $principalName = $e->getParam('id');
        
        $user = $this->service->fetchByPrincipalName($principalName);
        if (! $user) {
            throw new DomainException(sprintf("User with principal name '%s' not found", $principalName), 404);
        }
        
        return $user;
    }
}