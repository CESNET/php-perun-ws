<?php

namespace PerunWs\Listener;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;
use PhlyRestfully\Link;


class ResourceControllerListener extends AbstractSharedListenerAggregate
{

    protected $collectionControllers = array(
        'PerunWs\UserController',
        'PerunWs\GroupController',
        'PerunWs\SystemGroupController',
        'PerunWs\GroupUsersController',
        'PerunWs\SystemGroupUsersController',
        'PerunWs\UserGroupsController',
        'PerunWs\GroupAdminsController'
    );


    public function attachShared(SharedEventManagerInterface $sharedEvents)
    {
        $this->addListener($sharedEvents, $this->collectionControllers, 'getList.post', array(
            $this,
            'onGetListPost'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\PrincipalController', 'get.post', array(
            $this,
            'onPrincipalGetPost'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\GroupController', 'get.post', array(
            $this,
            'onGroupGetPost'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\GroupAdminsController', 'getList.post', array(
            $this,
            'onGroupAdminsGetListPost'
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


    /**
     * Callback for the "get.post" controller event on the PrincipalController.
     * Adjusts the links to self and to the user resource.
     * 
     * @param EventInterface $e
     */
    public function onPrincipalGetPost(EventInterface $e)
    {
        /* @var $resource \PhlyRestfully\HalResource */
        $resource = $e->getParam('resource');
        $principalName = $e->getParam('id');
        
        $link = new Link('user');
        $link->setRoute('users', array(
            'user_id' => $resource->id
        ));
        
        /* @var $links \PhlyRestfully\LinkCollection */
        $links = $resource->getLinks();
        
        $links->get('self')->setRouteParams(array(
            'principal_name' => $principalName
        ));
        
        $links->add($link);
    }
    
    public function onGroupGetPost(EventInterface $e)
    {
        /* @var $resource \PhlyRestfully\HalResource */
        $resource = $e->getParam('resource');
        
        /* @var $links \PhlyRestfully\LinkCollection */
        $links = $resource->getLinks();
        
        $link = new Link('users');
        $link->setRoute('groups/group-users');        
        $links->add($link);
        
        $link = new Link('admins');
        $link->setRoute('groups/group-admins');
        $links->add($link);
    }


    public function onGroupAdminsGetListPost(EventInterface $e)
    {
        /* @var $e \Zend\EventManager\Event */
        /* @var $controller \PhlyRestfully\ResourceController */
        $controller = $e->getTarget();
        $groupId = $controller->getResource()
            ->getRouteMatch()
            ->getParam('group_id');
        
        /* @var $halCollection \PhlyRestfully\HalCollection */
        $halCollection = $e->getParam('collection');
        
        $links = $halCollection->getLinks();
        
        $link = new Link('group');
        $link->setRoute('groups', array(
            'group_id' => $groupId
        ));
        
        $links->add($link);
    }
}