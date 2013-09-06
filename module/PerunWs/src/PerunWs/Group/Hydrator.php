<?php

namespace PerunWs\Group;

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