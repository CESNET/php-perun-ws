<?php

namespace PerunWs\Authentication\Adapter;

use Zend\Http\Request;
use Zend\Stdlib\Parameters;
use PerunWs\Exception\MissingOptionException;
use PerunWs\Authentication\Exception as AuthException;
use PerunWs\Authentication\Info;


/**
 * Simple authentication adapter. Checks the 'Authorization' header value against client records
 * stored as a PHP array in a file.
 */
class Simple implements AdapterInterface
{

    const OPT_AUTH_FILE_PATH = 'auth_file_path';

    /**
     * @var Parameters
     */
    protected $options;


    /**
     * Constructor.
     * 
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = new Parameters($options);
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate(Request $httpRequest)
    {
        $clientSecret = $this->extractSecret($httpRequest);
        if (! $clientSecret) {
            throw new AuthException\MissingClientCredentialsException();
        }
        
        $filePath = $this->options->get(self::OPT_AUTH_FILE_PATH);
        if (! $filePath) {
            throw new MissingOptionException(self::OPT_AUTH_FILE_PATH);
        }
        
        if (! file_exists($filePath)) {
            throw new Exception\AdapterException(sprintf("Missing file '%s'", $filePath));
        }
        
        if (! is_file($filePath) || ! is_readable($filePath)) {
            throw new Exception\AdapterException(sprintf("Invalid file '%s'", $filePath));
        }
        
        $clientsData = require $filePath;
        if (! is_array($clientsData)) {
            throw new Exception\AdapterException(sprintf("Invalid clients data in file '%s'", $filePath));
        }
        
        $authInfo = null;
        foreach ($clientsData as $clientData) {
            if (isset($clientData['id']) && isset($clientData['secret']) && $clientSecret === $clientData['secret']) {
                $authInfo = new Info($clientData['id'], isset($clientData['description']) ? $clientData['description'] : '');
                break;
            }
        }
        
        if (null === $authInfo) {
            throw new AuthException\UnknownClientException();
        }
        
        return $authInfo;
    }


    /**
     * Extracts the client's secret from the HTTP request.
     * 
     * @param Request $httpRequest
     * @return string|null
     */
    protected function extractSecret(Request $httpRequest)
    {
        $authHeader = $httpRequest->getHeader('Authorization');
        if ($authHeader) {
            return trim($authHeader->getFieldValue());
        }
        
        return null;
    }
}