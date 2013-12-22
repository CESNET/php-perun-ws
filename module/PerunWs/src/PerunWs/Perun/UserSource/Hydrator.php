<?php

namespace PerunWs\Perun\UserSource;

use PerunWs\Hydrator\Exception\UnsupportedObjectException;
use Zend\Stdlib\Hydrator\HydratorInterface;


class Hydrator implements HydratorInterface
{


    public function hydrate(array $data, $userSource)
    {}


    public function extract($userSource)
    {
        if (! $userSource instanceof \InoPerunApi\Entity\UserExtSource) {
            throw new UnsupportedObjectException(get_class($userSource));
        }
        
        /* @var $userSource \InoPerunApi\Entity\UserExtSource */
        $source = $userSource->getExtSource();
        
        $data = array(
            'id' => $userSource->getId(),
            'name' => $source->getName(),
            'loa' => $userSource->getLoa(),
            'login' => $userSource->getLogin()
        );
        
        return $data;
    }
}