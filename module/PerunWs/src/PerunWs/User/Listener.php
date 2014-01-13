<?php

namespace PerunWs\User;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use PhlyRestfully\ResourceEvent;
use PhlyRestfully\Exception\DomainException;
use PhlyRestfully\Exception\InvalidArgumentException;
use PerunWs\Util\CsvParser;


/**
 * User resource listener.
 */
class Listener extends AbstractListenerAggregate
{

    /**
     * @var Service\ServiceInterface
     */
    protected $service;

    /**
     * @var CsvParser
     */
    protected $csvParser = null;

    /**
     * @var string
     */
    protected $searchParamName = 'search';

    /**
     * @var string
     */
    protected $userIdParamName = 'filter_user_id';

    /**
     * Reg. expression to match the "search" GET parameter.
     * @var string
     */
    protected $searchRegexp = '/^\w+$/';


    /**
     * Constructor.
     * 
     * @param Service\ServiceInterface $service
     */
    public function __construct(Service\ServiceInterface $service)
    {
        $this->setService($service);
    }


    /**
     * @return Service\ServiceInterface
     */
    public function getService()
    {
        return $this->service;
    }


    /**
     * @param Service\ServiceInterface $service
     */
    public function setService($service)
    {
        $this->service = $service;
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
     * {@inheritdoc}
     * @see \Zend\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('fetch', array(
            $this,
            'onFetch'
        ));
        $this->listeners[] = $events->attach('fetchAll', array(
            $this,
            'onFetchAll'
        ));
    }


    /**
     * Returns a single user by ID.
     * 
     * @param ResourceEvent $e
     * @throws DomainException
     * @return \InoPerunApi\Entity\User
     */
    public function onFetch(ResourceEvent $e)
    {
        $id = $e->getParam('id');
        $user = $this->service->fetch($id);
        if (! $user) {
            throw new DomainException(sprintf("User ID:%d not found", $id), 404);
        }
        
        return $user;
    }


    /**
     * Returns all users, optionally filtered by a search string.
     * 
     * @param ResourceEvent $e
     * @throws InvalidArgumentException
     * @return \InoPerunApi\Entity\Collection\UserCollection
     */
    public function onFetchAll(ResourceEvent $e)
    {
        $params = array();
        
        $searchString = $this->parseSearchParam($e->getQueryParam($this->searchParamName));
        if (null !== $searchString) {
            $params['searchString'] = $searchString;
        }
        
        $userIdList = $this->parseUserIdParam($e->getQueryParam($this->userIdParamName));
        if (null !== $userIdList) {
            $params['filter_user_id'] = $userIdList;
        }
        
        $users = $this->service->fetchAll($params);
        
        return $users;
    }


    /**
     * Processes the "search" GET param.
     * 
     * @param string $search
     * @throws InvalidArgumentException
     * @return string|null
     */
    protected function parseSearchParam($search)
    {
        if (null === $search) {
            return null;
        }
        
        if (! preg_match($this->searchRegexp, $search)) {
            throw new InvalidArgumentException(sprintf("Invalid search string '%s'", $search), 400);
        }
        
        return trim($search);
    }


    /**
     * Parses the "user_id" GET parameter and returns a list of user ID values.
     * 
     * @param string $userId
     * @throws InvalidArgumentException
     * @return array|null
     */
    protected function parseUserIdParam($userId)
    {
        try {
            return $this->getCsvParser()->parse($userId);
        } catch (\Exception $e) {
            throw new InvalidArgumentException($e->getMessage(), 400, $e);
        }
    }
}