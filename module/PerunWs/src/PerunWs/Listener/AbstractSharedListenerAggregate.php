<?php

namespace PerunWs\Listener;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;


abstract class AbstractSharedListenerAggregate implements SharedListenerAggregateInterface
{

    /**
     * @var array
     */
    protected $listeners = array();


    public function detachShared(SharedEventManagerInterface $sharedEvents)
    {
        foreach ($this->listeners as $id => $handlers) {
            foreach ($handler as $handler) {
                $sharedEvents->detach($id, $handler);
            }
        }
    }


    protected function addListener(SharedEventManagerInterface $sharedEvents, $id, $event, $callback, $priority = null)
    {
        $ids = $id;
        if (! is_array($ids)) {
            $ids = array(
                $ids
            );
        }
        
        foreach ($ids as $id) {
            $handler = $sharedEvents->attach($id, $event, $callback, $priority);
            $this->listeners[$id][] = $handler;
        }
    }
}
