<?php

namespace PerunWs\Perun\Service;

use InoPerunApi\Manager\Factory\FactoryInterface;
use PerunWs\Exception\MissingDependencyException;


abstract class AbstractService
{

    /**
     * The manager factory from the Perun API
     * @var FactoryInterface
     */
    protected $entityManagerFactory;


    /**
     * @return FactoryInterface
     */
    public function getEntityManagerFactory()
    {
        if (! $this->entityManagerFactory instanceof FactoryInterface) {
            throw new MissingDependencyException('entityManagerFactory');
        }
        return $this->entityManagerFactory;
    }


    /**
     * @param FactoryInterface $entityManagerFactory
     */
    public function setEntityManagerFactory(FactoryInterface $entityManagerFactory)
    {
        $this->entityManagerFactory = $entityManagerFactory;
    }


    /**
     * Creates a new manager with the requested name.
     * 
     * @param string $managerName
     * @return \InoPerunApi\Manager\Factory\GenericManager
     */
    public function createManager($managerName)
    {
        return $this->getEntityManagerFactory()->createManager($managerName);
    }
}