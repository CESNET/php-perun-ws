<?php
return array(
    
    'router' => array(
        'routes' => array(
            
            /*
             * /users/{id}
             */
            'users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/users[/:user_id]',
                    'defaults' => array(
                        'controller' => 'PerunWs\UserController'
                    )
                ),
                
                'may_terminate' => true,
                'child_routes' => array(
                    
                    /*
                     * /users/{id}/groups
                     */
                    'user-groups' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/groups',
                            'defaults' => array(
                                'controller' => 'PerunWs\UserGroupsController'
                            )
                        )
                    )
                )
            ),
            
            /*
             * /groups/{group_id}
             */
            'groups' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/groups[/:group_id]',
                    'defaults' => array(
                        'controller' => 'PerunWs\GroupController'
                    )
                ),
                'may_terminate' => true,
                
                /*
                 * /groups/{group_id}/users/{user_id}
                 */
                'child_routes' => array(
                    'group-users' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/users[/:user_id]',
                            'defaults' => array(
                                'controller' => 'PerunWs\GroupUsersController'
                            )
                        )
                    )
                )
            )
        )
    ),
    
    'phlyrestfully' => array(
        
        'resources' => array(
            
            'PerunWs\UserController' => array(
                'identifier_name' => 'user_id',
                'listener' => 'PerunWs\UserListener',
                'resource_identifiers' => array(
                    'UserResource'
                ),
                'collection_http_options' => array(
                    'get'
                ),
                'collection_name' => 'users',
                'page_size' => 10,
                'resource_http_options' => array(
                    'get'
                ),
                'route_name' => 'users'
            ),
            
            'PerunWs\UserGroupsController' => array(
                'listener' => 'PerunWs\UserGroupsListener',
                'identifier_name' => 'group_id',
                'resource_identifiers' => array(
                    'UserGroupsResource'
                ),
                'collection_http_options' => array(
                    'get'
                ),
                'collection_name' => 'groups',
                'page_size' => 10,
                'resource_http_options' => array(
                    'get'
                ),
                'route_name' => 'users/user-groups'
            ),
            
            'PerunWs\GroupController' => array(
                'identifier_name' => 'group_id',
                'listener' => 'PerunWs\GroupsListener',
                'resource_identifiers' => array(
                    'GroupsResource'
                ),
                'collection_http_options' => array(
                    'get',
                    'post'
                ),
                'collection_name' => 'groups',
                'page_size' => 10,
                'resource_http_options' => array(
                    'get',
                    'patch',
                    'delete'
                ),
                'route_name' => 'groups'
            ),
            
            'PerunWs\GroupUsersController' => array(
                'identifier_name' => 'user_id',
                'listener' => 'PerunWs\GroupUsersListener',
                'resource_identifiers' => array(
                    'GroupUsersResource'
                ),
                'collection_http_options' => array(
                    'get'
                ),
                'collection_name' => 'users',
                'page_size' => 10,
                'resource_http_options' => array(
                    'put',
                    'delete'
                ),
                'route_name' => 'groups/group-users'
            )
        ),
        
        'metadata_map' => array(
            'InoPerunApi\Entity\RichUser' => array(
                'hydrator' => 'PerunWs\User\Hydrator',
                'route' => 'users'
            ),
            
            'InoPerunApi\Entity\Member' => array(
                'hydrator' => 'PerunWs\Member\Hydrator',
                'route' => 'users'
            ),
            
            'InoPerunApi\Entity\RichMember' => array(
                'hydrator' => 'PerunWs\Member\Hydrator',
                'route' => 'users'
            ),
            
            'InoPerunApi\Entity\Collection\RichUserCollection' => array(
                'is_collection' => true,
                'route' => 'users'
            ),
            
            'InoPerunApi\Entity\Collection\RichMemberCollection' => array(
                'is_collection' => true,
                'route' => 'users'
            ),
            
            'InoPerunApi\Entity\Group' => array(
                'hydrator' => 'PerunWs\Group\Hydrator',
                'route' => 'groups'
            ),
            
            'InoPerunApi\Entity\Collection\GroupCollection' => array(
                'is_collection' => true,
                'route' => 'groups'
            )
        )
    ),
    
    'perun_ws' => array(
        
        'authentication' => array(
            'adapter' => 'PerunWs\Authentication\Adapter\Simple',
            'options' => array(
                'auth_file_path' => '/path/to/file'
            )
        ),
        
        'perun_service' => array(
            'vo_id' => 123
        ),
        
        'perun_api' => array(
            
            'client' => array(
                'url' => 'https://perun.example.org/api/',
                'default_change_state' => true
            ),
            
            'http_client' => array(
                'adapter' => 'Zend\Http\Client\Adapter\Curl',
                'useragent' => 'Perun Client',
                'curloptions' => array(
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_CAINFO => '/etc/ssl/certs/ca-bundle.pem'
                )
            ),
            
            'authenticator' => array(
                'class' => 'InoPerunApi\Client\Authenticator\ClientCertificate',
                'options' => array(
                    'key_file' => '/etc/ssl/private/key.pem',
                    'crt_file' => '/etc/ssl/certs/crt.pem'
                )
            )
        )
    )
);