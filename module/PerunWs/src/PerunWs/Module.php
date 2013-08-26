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
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('dispatch.error', function ($event)
        {
            $error = $event->getError();
            if ($error) {
                _dump('ERROR: ' . $error);
                $event->setError(false);
            }
            
            $exception = $event->getResult()->exception;
            if ($exception) {
                // $sm = $event->getApplication()->getServiceManager();
                // $service = $sm->get('Application\Service\ErrorHandling');
                _dump(sprintf("EXCEPTION [%s]: %s", get_class($exception), $exception->getMessage()));
                _dump($exception->getTraceAsString());
            }
        });
    }
}