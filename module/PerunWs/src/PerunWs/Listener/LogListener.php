<?php

namespace PerunWs\Listener;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Log\Logger;
use PerunWs\Exception\MissingDependencyException;
use PhlyRestfully\ApiProblem;


/**
 * Listens to certain events and logs them in the provided logger.
 */
class LogListener extends AbstractSharedListenerAggregate
{

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * Constructor.
     * 
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->setLogger($logger);
    }


    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (! $this->logger instanceof Logger) {
            throw new MissingDependencyException('logger');
        }
        return $this->logger;
    }


    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }


    /**
     * {@inheritdoc}
     * @see \Zend\EventManager\SharedListenerAggregateInterface::attachShared()
     */
    public function attachShared(SharedEventManagerInterface $sharedEvents)
    {
        $this->addListener($sharedEvents, 'PerunWs\Listener\DispatchListener', 'dispatch.post', array(
            $this,
            'onDispatchPost'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\Listener\DispatchListener', 'dispatch.error', array(
            $this,
            'onDispatchError'
        ));
        
        $this->addListener($sharedEvents, 'PerunWs\Listener\DispatchListener', 'auth.error', array(
            $this,
            'onAuthError'
        ));
    }


    /**
     * @param EventInterface $event
     */
    public function onDispatchPost(EventInterface $event)
    {
        /* @var $mvcEvent \Zend\Mvc\MvcEvent */
        $mvcEvent = $event->getParam('mvcEvent');
        if ($mvcEvent) {
            $routeMatch = $mvcEvent->getRouteMatch();
            $this->log(sprintf("[%s] controller=%s action=%s", $mvcEvent->getParam('clientId'), $routeMatch->getParam('controller'), $routeMatch->getParam('action')));
            
            /*
             * Check for an ApiProblem in the result and extract the exception
             */
            $result = $mvcEvent->getResult();
            if ($result && ($result->getPayload() instanceof ApiProblem)) {
                /* @var $exception \PhlyRestfully\Exception\DomainException */
                $exception = $result->getPayload()->detail;
                
                $this->log(sprintf("[%s] [%s] %s", $mvcEvent->getParam('clientId'), get_class($exception), $exception->getMessage()), Logger::ERR);
                $this->logException($exception);
            }
        }
    }


    /**
     * @param EventInterface $event
     */
    public function onDispatchError(EventInterface $event)
    {
        /* @var $mvcEvent \Zend\Mvc\MvcEvent */
        $mvcEvent = $event->getParam('mvcEvent');
        if ($error = $mvcEvent->getError()) {
            $this->log(sprintf("Dispatch error: %s", $error), Logger::ERR);
        }
        
        $result = $mvcEvent->getResult();
        if ($result) {
            $exception = $result->exception;
            if ($exception) {
                $this->log(sprintf("EXCEPTION [%s]: %s", get_class($exception), $exception->getMessage()), Logger::ERR);
                $this->logException($exception);
            }
        }
    }


    /**
     * @param EventInterface $event
     */
    public function onAuthError(EventInterface $event)
    {
        /* @var $exception \Exception */
        $exception = $event->getParam('exception');
        if ($exception) {
            $this->log(sprintf("[%s] %s", get_class($exception), $exception->getMessage()), Logger::ERR);
            $this->logException($exception);
        }
    }


    /**
     * Logs a message through the logger.
     * 
     * @param string $message
     * @param integer $priority
     * @param array $extra
     * @throws MissingDependencyException
     */
    public function log($message, $priority = Logger::INFO, $extra = array())
    {
        $message = sprintf("[%s] [%s] %s", $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_URI'], $message);
        
        _dump('LOG: ' . $message);
        $this->getLogger()->log($priority, $message, $extra);
    }


    /**
     * Logs an exception.
     * 
     * @param \Exception $e
     */
    public function logException(\Exception $e)
    {
        $this->log("$e");
    }
}