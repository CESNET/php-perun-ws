<?php

namespace PerunWs\Perun\Service;

use InoPerunApi\Manager\Factory\FactoryInterface;
use PerunWs\Exception\MissingDependencyException;
use Zend\Stdlib\Parameters;


abstract class AbstractService
{

    const OPT_VO_ID = 'vo_id';

    const OPT_BASE_GROUP_ID = 'base_group_id';

    const OPT_PRINCIPAL_NAMES_ATTRIBUTE_NAME = 'principal_names_attribute_name';

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
        $this->setParameters($parameters);
    }


    /**
     * @return Parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * @param Parameters $parameters
     */
    public function setParameters(Parameters $parameters)
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
     * @return integer
     */
    public function getVoId()
    {
        return $this->getRequiredParameter(self::OPT_VO_ID);
    }


    /**
     * Returns the ID of the base group.
     * 
     * @return integer
     */
    public function getBaseGroupId()
    {
        return $this->getRequiredParameter(self::OPT_BASE_GROUP_ID);
    }


    /**
     * Returns the name  of the Perun attribute holding the user's ePPNs.
     * 
     * @return string
     */
    public function getPrincipalNamesAttributeName()
    {
        return $this->getRequiredParameter(self::OPT_PRINCIPAL_NAMES_ATTRIBUTE_NAME);
    }


    /**
     * Tries to retrieve the required parameter. Throws an exception if the parameter is not set.
     * 
     * @param string $name
     * @throws Exception\MissingParameterException
     * @return mixed
     */
    protected function getRequiredParameter($name)
    {
        $value = $this->parameters->get($name);
        if (null === $value) {
            throw new Exception\MissingParameterException($name);
        }
        
        return $value;
    }
}