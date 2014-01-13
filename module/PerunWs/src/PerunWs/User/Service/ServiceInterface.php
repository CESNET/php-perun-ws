<?php

namespace PerunWs\User\Service;

use PerunWs\Perun;


/**
 * Service interface for manipulating users.
 */
interface ServiceInterface extends Perun\Service\ServiceInterface
{


    /**
     * Returns all users (members).
     * 
     * @param array $params
     * @return \InoPerunApi\Entity\Collection\RichMemberCollection
     */
    public function fetchAll(array $params = array());


    /**
     * Returns a single user (member).
     * 
     * @param integer $id
     * @return \InoPerunApi\Entity\RichMember
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
