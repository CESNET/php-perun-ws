<?php

namespace PerunWs\Group;

use Zend\Stdlib\Hydrator\HydratorInterface;


class Hydrator implements HydratorInterface
{


    public function hydrate(array $data, $group)
    {}


    public function extract($group)
    {
        /* @var $group \InoPerunApi\Entity\Group */
        $data = $group->getProperties();
        
        $data = array(
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'parent_group_id' => $group->getParentGroupId()
        );
        
        return $data;
    }
}