<?php

namespace PerunWs\Perun\Service;

use InoPerunApi\Manager\Factory\FactoryInterface;
use PerunWs\Exception\MissingDependencyException;
use Zend\Stdlib\Parameters;


abstract class AbstractService
{

    /**
     * The manager factory from the Perun API
     * @var FactoryInterface
     */
    protected $entityManagerFactory;

    /**
     * @var Parameters
     */
    protected $parameters;


    /**
     * Constructor.
     * 
     * @param Parameters $parameters
     */
    public function __construct(Parameters $parameters)
    {
        $this->parameters = $parameters;
    }


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


    /**
     * Returns the ID of the default VO.
     * 
     * @throws MissingParameterException
     * @return integer
     */
    public function getVoId()
    {
        $voId = intval($this->parameters->get('vo_id'));
        if (! $voId) {
            throw new Exception\MissingParameterException('vo_id');
        }
        
        return $voId;
    }


    /**
     * Returns the name  of the Perun attribute holding the user's ePPNs.
     * 
     * @throws Exception\MissingParameterException
     * @return string
     */
    public function getPrincipalNamesAttributeName()
    {
        $attributeName = $this->parameters->get('principal_names_attribute_name');
        if (! $attributeName) {
            throw new Exception\MissingParameterException('principal_names_attribute_name');
        }
        
        return $attributeName;
    }
}