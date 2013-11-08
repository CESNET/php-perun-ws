<?php

namespace PerunWs\User\Service;

use PerunWs\Perun\Service\AbstractCachedService;


class CachedService extends AbstractCachedService implements ServiceInterface
{


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetch()
     */
    public function fetch($id)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetchAll()
     */
    public function fetchAll(array $params = array())
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\User\Service\ServiceInterface::fetchByPrincipalName()
     */
    public function fetchByPrincipalName($principalName)
    {
        return $this->cachedCall(__FUNCTION__, func_get_args());
    }
}