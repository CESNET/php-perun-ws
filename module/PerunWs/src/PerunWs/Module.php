<?php

namespace PerunWs;

use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use PerunWs\ServiceManager\ServiceConfig;
use PerunWs\ServiceManager\ControllerConfig;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;


class Module implements AutoloaderProviderInterface, ControllerProviderInterface, ServiceProviderInterface, ConfigProviderInterface, BootstrapListenerInterface
{


    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__
                )
            )
        );
    }


    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }


    public function getControllerConfig()
    {
        return new ControllerConfig();
    }


    public function getServiceConfig()
    {
        return new ServiceConfig();
    }


    public function onBootstrap(EventInterface $e)
    {
        /* @var $e \Zend\Mvc\MvcEvent */
        $target = $e->getTarget();
        
        /* @var $events \Zend\EventManager\EventManager */
        $events = $target->getEventManager();
        $sharedEvents = $events->getSharedManager();
        
        /* @var $services \Zend\ServiceManager\ServiceManager */
        $services = $e->getApplication()->getServiceManager();
        
        $events->attachAggregate($services->get('PerunWs\DispatchListener'));
        
        $rcl = $services->get('PerunWs\ResourceControllerListener');
        $rcl->attachShared($sharedEvents);
        
        $logListener = $services->get('PerunWs\LogListener');
        $logListener->attachShared($sharedEvents);
    }
}