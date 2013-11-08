<?php

namespace PerunWs\User\Service;

use PerunWs\Perun;


/**
 * Service interface for manipulating users.
 */
interface ServiceInterface extends Perun\Service\ServiceInterface
{


    /**
     * Returns all records.
     * 
     * @param array $params
     * @return \InoPerunApi\Entity\Collection\UserCollection
     */
    public function fetchAll(array $params = array());


    /**
     * Returns a single user.
     * 
     * @param integer $id
     * @return \InoPerunApi\Entity\User
     */
    public function fetch($id);


    /**
     * Fetches a user by his principal name.
     *
     * @param string $principalName
     * @throws Exception\MultipleUsersPerPrincipalNameException
     * @return \InoPerunApi\Entity\User
     */
    public function fetchByPrincipalName($principalName);
}
