<?php

namespace PerunWs\Group\User;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PerunWs\Group\Service\ServiceInterface;
use PhlyRestfully\ResourceEvent;


class Listener extends AbstractListenerAggregate
{

    /**
     * @var ServiceInterface
     */
    protected $service;


    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
    }


    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetchAll', array(
            $this,
            'onFetchAll'
        ));
        $this->listeners[] = $events->attach('update', array(
            $this,
            'onUpdate'
        ));
        $this->listeners[] = $events->attach('delete', array(
            $this,
            'onDelete'
        ));
    }


    public function onFetchAll(ResourceEvent $e)
    {
        $id = $e->getRouteParam('group_id');
        
        $users = $this->service->fetchMembers($id);
        //_dump($users);
        return $users;
    }


    public function onUpdate(ResourceEvent $e)
    {}


    public function onDelete(ResourceEvent $e)
    {}
}