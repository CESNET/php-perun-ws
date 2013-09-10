<?php

namespace PerunWs\Authentication\Adapter;

use Zend\Http\Request;


interface AdapterInterface
{


    /**
     * Tries to authenticate the client using the HTTP request. If successful, returns client info, otherwise
     * throws an exception.
     * 
     * @param Request $httpRequest
     * @return \PerunWs\Authentication\Info
     */
    public function authenticate(Request $httpRequest);
}