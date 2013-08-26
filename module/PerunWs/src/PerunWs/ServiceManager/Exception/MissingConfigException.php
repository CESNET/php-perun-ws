<?php

namespace PerunWs\ServiceManager\Exception;


class MissingConfigException extends \RuntimeException
{

    protected $configName;


    public function __construct($configName)
    {
        $this->configName = $configName;
        parent::__construct(sprintf("Missing configuration '%s'", $configName));
    }
}