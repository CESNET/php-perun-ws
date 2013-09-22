<?php

namespace PerunWs\Group;

use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use InoPerunApi\Entity\Group;
use Zend\Stdlib\Hydrator\HydratorInterface;


/**
 * Group hydrator.
 */
class Hydrator implements HydratorInterface
{


    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
     */
    public function hydrate(array $data, $group)
    {}


    /**
     * {@inheritdoc}
     * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
     */
    public function extract($group)
    {
        if (! $group instanceof Group) {
            throw new UnsupportedObjectException(get_class($group));
        }
        
        /* @var $group \InoPerunApi\Entity\Group */
        
        $data = array(
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'parent_group_id' => $group->getParentGroupId()
        );
        
        return $data;
    }
}