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
                    'route' => '/users[/:id]',
                    'controller' => 'PerunWs\UserController'
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
                            'controller' => 'PerunWs\UserGroupsController'
                        )
                    )
                )
            )
            ,
            
            /*
             * /groups/{group_id}
             */
            'groups' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/groups[/:group_id]',
                    'controller' => 'PerunWs\GroupController'
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
                            'controller' => 'PerunWs\GroupUsersController'
                        )
                    )
                )
            )
        )
        
    )
);