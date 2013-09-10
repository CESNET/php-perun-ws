<?php

namespace PerunWs\Listener;

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
        $count = 0;
        $total = 0;
        
        /* @var $halCollection \PhlyRestfully\HalCollection */
        $halCollection = $e->getParam('collection');
        $collection = $halCollection->collection;
        
        if ($collection instanceof \InoPerunApi\Entity\Collection\Collection) {
            /* @var $collection \InoPerunApi\Entity\Collection\Collection */
            $total = $count = $collection->count();
        }
        
        $halCollection->setAttributes(array(
            'count' => $count,
            'total' => $total
        ));
    }
}