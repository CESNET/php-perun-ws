<?php

namespace PerunWs\User\Service;


/**
 * Service interface for manipulating users.
 */
interface ServiceInterface
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
}
