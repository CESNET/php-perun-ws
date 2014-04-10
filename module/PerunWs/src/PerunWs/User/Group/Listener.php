<?php

namespace PerunWs\User\Group;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\RuntimeException;
use PerunWs\Group;
use PerunWs\Group\Service\Exception\MemberRetrievalException;
use PerunWs\Util\ParametersFactory;
use PerunWs\Util\CsvParser;


/**
 * User groups resource listener.
 */
class Listener extends AbstractListenerAggregate
{

    /**
     * @var Group\Service\ServiceInterface
     */
    protected $service;

    /**
     * @var ParametersFactory
     */
    protected $parametersFactory;

    /**
     * @var CsvParser
     */
    protected $csvParser;


    /**
     * Constructor.
     * 
     * @param Group\Service\ServiceInterface $service
     */
    public function __construct(Group\Service\ServiceInterface $service)
    {
        $this->setService($service);
    }


    /**
     * @return Group\Service\ServiceInterface
     */
    public function getService()
    {
        return $this->service;
    }


    /**
     * @param Group\Service\ServiceInterface $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }


    /**
     * @return ParametersFactory
     */
    public function getParametersFactory()
    {
        if (! $this->parametersFactory instanceof ParametersFactory) {
            $this->parametersFactory = new ParametersFactory();
        }
        
        return $this->parametersFactory;
    }


    /**
     * @return CsvParser
     */
    public function getCsvParser()
    {
        if (! $this->csvParser instanceof CsvParser) {
            $this->csvParser = new CsvParser();
        }
        
        return $this->csvParser;
    }


    /**
     * @param CsvParser $csvParser
     */
    public function setCsvParser(CsvParser $csvParser)
    {
        $this->csvParser = $csvParser;
    }


    /**
     * @param ParametersFactory $parametersFactory
     */
    public function setParametersFactory(ParametersFactory $parametersFactory)
    {
        $this->parametersFactory = $parametersFactory;
    }


    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetchAll', array(
            $this,
            'onFetchAll'
        ));
    }


    /**
     * Returns all groups, the user is member od.
     * 
     * @param ResourceEvent $e
     * @return \InoPerunApi\Entity\Collection\GroupCollection
     */
    public function onFetchAll(ResourceEvent $e)
    {
        $params = $this->getParametersFactory()->createParameters();
        
        if ($groupType = $e->getQueryParam('filter_type')) {
            $params->set('filter_type', $this->getCsvParser()
                ->parse($groupType));
        }
        
        $userId = $e->getRouteParam('user_id');
        
        $groups = $this->service->fetchUserGroups($userId, $params);
        
        return $groups;
    }
}