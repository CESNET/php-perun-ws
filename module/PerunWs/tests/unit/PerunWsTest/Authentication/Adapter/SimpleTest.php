<?php

namespace PerunWsTest\Authentication\Adapter;

use PerunWs\Authentication\Adapter\Simple;


class SimpleTest extends \PHPUnit_Framework_TestCase
{


    public function testAuthenticateWithMissingClientCredentials()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\MissingClientCredentialsException');
        
        $adapter = new Simple(array());
        $adapter->authenticate($this->getHttpRequestMock(null));
    }


    public function testAuthenticateWithMissingParam()
    {
        $this->setExpectedException('PerunWs\Exception\MissingOptionException');
        
        $options = array();
        $adapter = new Simple($options);
        
        $adapter->authenticate($this->getHttpRequestMock());
    }


    public function testAuthenicateWithMissingFile()
    {
        $this->setExpectedException('PerunWs\Authentication\Adapter\Exception\AdapterException');
        
        $adapter = new Simple(array(
            Simple::OPT_AUTH_FILE_PATH => '/some/missing/file'
        ));
        
        $adapter->authenticate($this->getHttpRequestMock());
    }


    public function testAuthenticateWithInvalidFile()
    {
        $this->setExpectedException('PerunWs\Authentication\Adapter\Exception\AdapterException');
        
        $adapter = new Simple(array(
            Simple::OPT_AUTH_FILE_PATH => __DIR__
        ));
        
        $adapter->authenticate($this->getHttpRequestMock());
    }


    public function testAuthenticationWithFileWithInvalidData()
    {
        $this->setExpectedException('PerunWs\Authentication\Adapter\Exception\AdapterException');
        
        $adapter = new Simple(array(
            Simple::OPT_AUTH_FILE_PATH => TESTS_ROOT_DIR . '/data/invalid_clients_file.php'
        ));
        
        $adapter->authenticate($this->getHttpRequestMock());
    }


    public function testAuthenticationWithUnknownClient()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\UnknownClientException');
        
        $adapter = new Simple(array(
            Simple::OPT_AUTH_FILE_PATH => TESTS_ROOT_DIR . '/data/clients_file.php'
        ));
        
        $adapter->authenticate($this->getHttpRequestMock('unknown client secret'));
    }


    public function testAuthenticationWithSuccess()
    {
        $adapter = new Simple(array(
            Simple::OPT_AUTH_FILE_PATH => TESTS_ROOT_DIR . '/data/clients_file.php'
        ));
        
        $info = $adapter->authenticate($this->getHttpRequestMock('123456'));
        $this->assertInstanceOf('PerunWs\Authentication\Info', $info);
        $this->assertSame('foo', $info->getClientId());
        $this->assertSame('foo client', $info->getClientDescription());
    }
    
    /*
     * 
     */
    protected function getHttpRequestMock($secret = 'client secret')
    {
        $header = $this->getMock('Zend\Http\Header\HeaderInterface');
        $header->expects($this->once())
            ->method('getFieldValue')
            ->will($this->returnValue($secret));
        
        $request = $this->getMock('Zend\Http\Request');
        $request->expects($this->once())
            ->method('getHeader')
            ->with('Authorization')
            ->will($this->returnValue($header));
        
        return $request;
    }
}