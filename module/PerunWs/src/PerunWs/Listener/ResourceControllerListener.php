<?php

namespace PerunWs\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;


class ResourceControllerListener extends AbstractSharedListenerAggregate
{

    protected $collectionControllers = array(
        'PerunWs\UserController',
        'PerunWs\GroupController',
        'PerunWs\GroupUsersController',
        'PerunWs\UserGroupsController'
    );


    public function attachShared(SharedEventManagerInterface $sharedEvents)
    {
        $this->addListener($sharedEvents, $this->collectionControllers, 'getList.post', array(
            $this,
            'onGetListPost'
        ));
    }


    public function onGetListPost(EventInterface $e)
    {
        /* @var $halCollection \PhlyRestfully\HalCollection */
        $halCollection = $e->getParam('collection');
        /* @var $collection \InoPerunApi\Entity\Collection\Collection */
        $collection = $halCollection->collection;
        
        $total = $count = $collection->count();
        $halCollection->setAttributes(array(
            'count' => $count,
            'total' => $total
        ));
    }
}