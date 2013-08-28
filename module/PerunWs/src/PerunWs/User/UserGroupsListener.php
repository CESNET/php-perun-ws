<?php

namespace PerunWs\User;

use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\ResourceEvent;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\HalCollection;


class UserGroupsListener extends AbstractListenerAggregate
{


    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetchAll', array(
            $this,
            'onFetchAll'
        ));
        $this->listeners[] = $events->attach('fetch', array(
            $this,
            'onFetch'
        ));
    }
    
    public function onFetch(ResourceEvent $e)
    {
        _dump('fetch');
        return array();
    }


    public function onFetchAll(ResourceEvent $e)
    {
        _dump('fetchAll');
        _dump($e->getParams());
        return array();
    }
}