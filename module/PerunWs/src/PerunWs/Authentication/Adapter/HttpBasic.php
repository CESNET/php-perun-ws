<?php

namespace PerunWs\Authentication\Adapter;

use Zend\Http\Request;
use Zend\Stdlib\Parameters;
use PerunWs\Exception\MissingOptionException;
use PerunWs\Authentication\Exception as AuthException;
use PerunWs\Authentication\Info;


class HttpBasic implements AdapterInterface
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
    public function __construct(array $options = array())
    {
        $this->options = new Parameters($options);
    }


    /**
     * {@inheritdoc}
     * @see \PerunWs\Authentication\Adapter\AdapterInterface::authenticate()
     */
    public function authenticate(Request $httpRequest)
    {
        $clientCredentials = $this->extractClientCredentials($httpRequest);
        
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
            if (isset($clientData['id']) && isset($clientData['secret']) &&
                 $clientCredentials['id'] === $clientData['id'] && $clientCredentials['secret'] === $clientData['secret']) {
                $authInfo = new Info($clientData['id'], 
                    isset($clientData['description']) ? $clientData['description'] : '');
                break;
            }
        }
        
        if (null === $authInfo) {
            throw new AuthException\UnknownClientException();
        }
        
        return $authInfo;
    }


    /**
     * Extracts the client's credentials from the HTTP request and returns an array with 'id' and 'secret' fields.
     *
     * @param Request $httpRequest
     * @throws AuthException\MissingClientCredentialsException()
     * @return array
     */
    public function extractClientCredentials(Request $httpRequest)
    {
        $authHeader = $httpRequest->getHeader('Authorization');
        if (! $authHeader) {
            throw new AuthException\MissingClientCredentialsException('Authorization header not set');
        }
        
        $headerValue = $authHeader->getFieldValue();
        $parts = explode(' ', $headerValue, 2);
        if (2 != count($parts)) {
            throw new AuthException\MissingClientCredentialsException('Wrong header format');
        }
        
        if ('basic' !== trim(strtolower($parts[0]))) {
            throw new AuthException\MissingClientCredentialsException('Invalid authorization type');
        }
        
        return $this->decodeClientCredentials(trim($parts[1]));
    }


    /**
     * Decodes client's credentials and returns an array with 'id' and 'secret' fields.
     * 
     * @param string $encodedCredentials
     * @throws AuthException\InvalidClientCredentialsException
     * @return array
     */
    public function decodeClientCredentials($encodedCredentials)
    {
        $decodedString = base64_decode($encodedCredentials, true);
        if (false === $decodedString) {
            throw new AuthException\InvalidClientCredentialsException('Error decoding credentials');
        }
        
        $parts = explode(':', $decodedString);
        if (2 != count($parts)) {
            throw new AuthException\InvalidClientCredentialsException('Invalid credentials format');
        }
        
        return array(
            'id' => $parts[0],
            'secret' => $parts[1]
        );
    }
}