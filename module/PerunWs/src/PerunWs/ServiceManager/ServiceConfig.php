<?php

namespace PerunWs\ServiceManager;

use Zend\Cache;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;
use Zend\ServiceManager\Config;
use InoPerunApi\Client\ClientFactory;
use InoPerunApi\Manager\Factory\GenericFactory;
use PerunWs\User;
use PerunWs\Group;
use PerunWs\Principal;
use PerunWs\Hydrator\DefaultHydrator;
use PerunWs\Listener;
use PerunWs\Exception\UndefinedClassException;
use PerunWs\Exception\MissingDependencyException;


class ServiceConfig extends Config
{


    public function getDelegators()
    {
        return array();
    }


    public function getFactories()
    {
        return array(
            
            'PerunWs\Logger' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['logger']) || ! is_array($config['perun_ws']['logger'])) {
                    throw new Exception\MissingConfigException('perun_ws/logger');
                }
                
                $logger = new Logger($config['perun_ws']['logger']);
                $logger->addProcessor('requestId');
                return $logger;
            },
            
            'PerunWs\CacheStorage' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['cache_storage']) || ! is_array($config['perun_ws']['cache_storage'])) {
                    throw new Exception\MissingConfigException('perun_ws/cache_storage');
                }
                
                $cacheStorage = Cache\StorageFactory::factory($config['perun_ws']['cache_storage']);
                
                return $cacheStorage;
            },
            
            'PerunWs\DefaultHydrator' => function ($services)
            {
                return new DefaultHydrator();
            },
            
            /**
             * Perun API client 
             */
            'PerunWs\Client' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_ws']) || ! is_array($config['perun_ws'])) {
                    throw new Exception\MissingConfigException('perun_ws');
                }
                
                $perunWsConfig = $config['perun_ws'];
                if (! isset($perunWsConfig['perun_api']) || ! is_array($perunWsConfig['perun_api'])) {
                    throw new Exception\MissingConfigException('perun_ws/perun_api');
                }
                
                $clientConfig = $perunWsConfig['perun_api'];
                
                $clientFactory = new ClientFactory();
                $client = $clientFactory->createClient($clientConfig);
                
                return $client;
            },
            
            /**
             * Perun API entity manager factory
             */
            'PerunWs\EntityManagerFactory' => function ($services)
            {
                $client = $services->get('PerunWs\Client');
                $factory = new GenericFactory($client);
                return $factory;
            },
            
            /**
             * Perun user service
             */
            'PerunWs\UserService' => function ($services)
            {
                $entityManagerFactory = $services->get('PerunWs\EntityManagerFactory');
                
                $userService = new User\Service\Service($services->get('PerunWs\UserServiceParameters'));
                $userService->setEntityManagerFactory($entityManagerFactory);
                
                $cacheStorage = $services->get('PerunWs\CacheStorage');
                if ($cacheStorage) {
                    $userService = new User\Service\CachedService($userService, $cacheStorage);
                }
                
                return $userService;
            },
            
            /**
             * Perun group service
             */
            'PerunWs\GroupService' => function ($services)
            {
                $entityManagerFactory = $services->get('PerunWs\EntityManagerFactory');
                
                $groupService = new Group\Service\Service($services->get('PerunWs\GroupServiceParameters'));
                $groupService->setEntityManagerFactory($entityManagerFactory);
                
                $cacheStorage = $services->get('PerunWs\CacheStorage');
                if ($cacheStorage) {
                    $groupService = new Group\Service\CachedService($groupService, $cacheStorage);
                }
                
                return $groupService;
            },
            
            /**
             * Perun user resource listener
             */
            'PerunWs\UserListener' => function ($services)
            {
                return new User\Listener($services->get('PerunWs\UserService'));
            },
            
            /**
             * Perun user's groups resource listener
             */
            'PerunWs\UserGroupsListener' => function ($services)
            {
                return new User\Group\Listener($services->get('PerunWs\GroupService'));
            },
            
            'PerunWs\GroupsListener' => function ($services)
            {
                return new Group\Listener($services->get('PerunWs\GroupService'));
            },
            
            'PerunWs\SystemGroupsListener' => function ($services)
            {
                $groupService = $services->get('PerunWs\GroupService');
                
                $systemGroupsParams = $services->get('PerunWs\SystemGroupServiceParameters');
                if (! $systemGroupsParams) {
                    throw new MissingDependencyException('PerunWs\SystemGroupServiceParameters');
                }
                
                $groupService->setParameters($systemGroupsParams);
                
                return new Group\Listener($groupService);
            },
            
            /**
             * Perun group's users resource listener
             */
            'PerunWs\GroupUsersListener' => function ($services)
            {
                $groupService = $services->get('PerunWs\GroupService');
                
                return new Group\User\Listener($groupService);
            },
            
            /**
             * Perun system group's users resource listener
             */
            'PerunWs\SystemGroupUsersListener' => function ($services)
            {
                $groupService = $services->get('PerunWs\GroupService');
                
                $systemGroupsParams = $services->get('PerunWs\SystemGroupServiceParameters');
                if (! $systemGroupsParams) {
                    throw new MissingDependencyException('PerunWs\SystemGroupServiceParameters');
                }
                
                $groupService->setParameters($systemGroupsParams);
                
                return new Group\User\Listener($groupService);
            },
            
            /**
             * Perun group admins resource listener
             */
            'PerunWs\GroupAdminsListener' => function ($services)
            {
                return new Group\Admin\Listener($services->get('PerunWs\GroupService'));
            },
            
            'PerunWs\PrincipalListener' => function ($services)
            {
                return new Principal\Listener($services->get('PerunWs\UserService'));
            },
            
            'PerunWs\UserServiceParameters' => function ($services)
            {
                
                $parameters = new Parameters();
                
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['service_options']['user']) || ! is_array($config['perun_ws']['service_options']['user'])) {
                    throw new Exception\MissingConfigException('perun_ws/service_options/user');
                }
                
                $parameters->fromArray($config['perun_ws']['service_options']['user']);
                
                return $parameters;
            },
            
            'PerunWs\GroupServiceParameters' => function ($services)
            {
                
                $parameters = new Parameters();
                
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['service_options']['group']) || ! is_array($config['perun_ws']['service_options']['group'])) {
                    throw new Exception\MissingConfigException('perun_ws/service_options/group');
                }
                
                $parameters->fromArray($config['perun_ws']['service_options']['group']);
                
                return $parameters;
            },
            
            'PerunWs\SystemGroupServiceParameters' => function ($services)
            {
                
                $parameters = new Parameters();
                
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['service_options']['systemgroup']) || ! is_array($config['perun_ws']['service_options']['systemgroup'])) {
                    throw new Exception\MissingConfigException('perun_ws/service_options/systemgroup');
                }
                
                $parameters->fromArray($config['perun_ws']['service_options']['systemgroup']);
                
                return $parameters;
            },
            
            'PerunWs\AuthenticationAdapter' => function ($services)
            {
                $config = $services->get('Config');
                if (! isset($config['perun_ws']['authentication']['adapter'])) {
                    throw new Exception\MissingConfigException('perun_ws/authentication/adapter');
                }
                
                $adapterClass = $config['perun_ws']['authentication']['adapter'];
                if (! class_exists($adapterClass)) {
                    throw new UndefinedClassException($adapterClass);
                }
                
                $options = array();
                if (isset($config['perun_ws']['authentication']['options'])) {
                    $options = $config['perun_ws']['authentication']['options'];
                }
                
                $adapter = new $adapterClass($options);
                return $adapter;
            },
            
            'PerunWs\DispatchListener' => function ($services)
            {
                $listener = new Listener\DispatchListener();
                $listener->setAuthenticationAdapter($services->get('PerunWs\AuthenticationAdapter'));
                
                return $listener;
            },
            
            'PerunWs\ResourceControllerListener' => function ($services)
            {
                return new Listener\ResourceControllerListener();
            },
            
            'PerunWs\LogListener' => function ($services)
            {
                return new Listener\LogListener($services->get('PerunWs\Logger'));
            }
        );
    }
}