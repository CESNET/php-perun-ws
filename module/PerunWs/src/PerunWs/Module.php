<?php

namespace PerunWs;

use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;


class Module implements AutoloaderProviderInterface, ControllerProviderInterface, ServiceProviderInterface, ConfigProviderInterface
{


    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getControllerConfig()
    {
        return new ControllerConfig();
    }


    public function getServiceConfig()
    {
        return new ServiceConfig();
    }
}