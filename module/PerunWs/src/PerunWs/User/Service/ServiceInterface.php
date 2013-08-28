<?php

namespace PerunWs\User\Service;

use InoPerunApi\Entity\Collection\Collection;
use InoPerunApi\Entity\User;


interface ServiceInterface
{


    /**
     * Returns all records.
     * 
     * @param array $params
     * @return Collection
     */
    public function fetchAll(array $params = array());


    /**
     * Returns a single user.
     * 
     * @param integer $id
     * @return User
     */
    public function fetch($id);
}
