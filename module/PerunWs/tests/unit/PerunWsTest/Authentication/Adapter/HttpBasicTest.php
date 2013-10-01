<?php

namespace PerunWsTest\Authentication\Adapter;

use PerunWs\Authentication\Adapter\HttpBasic;


class HttpBasicTest extends \PHPUnit_Framework_TestCase
{

    protected $credentials = array(
        'id' => 'bar',
        'secret' => 'qwerty'
    );

    protected $desc = 'bar client';


    public function testDecodeClientCredentialsWithInvalidBase64String()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\InvalidClientCredentialsException', 
            'Error decoding credentials');
        
        $adapter = new HttpBasic();
        $adapter->decodeClientCredentials('non base64 string;');
    }


    public function testDecodeClientCredentialsWithInvalidFormat()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\InvalidClientCredentialsException', 
            'Invalid credentials format');
        
        $adapter = new HttpBasic();
        $adapter->decodeClientCredentials(base64_encode('string without id and secret'));
    }


    public function testDecodeClientCredentials()
    {
        $id = 'foo';
        $secret = 'bar';
        $authString = base64_encode("$id:$secret");
        $credentials = array(
            'id' => $id,
            'secret' => $secret
        );
        
        $adapter = new HttpBasic();
        $this->assertEquals($credentials, $adapter->decodeClientCredentials($authString));
    }


    public function testExtractClientCredentialsWithMissingAuthHeader()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\MissingClientCredentialsException', 
            'Authorization header not set');
        
        $httpRequest = $this->getHttpRequestMock();
        $adapter = new HttpBasic();
        $adapter->extractClientCredentials($httpRequest);
    }


    public function testExtractClientCredentialsWithInvalidHeaderFormat()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\MissingClientCredentialsException', 
            'Wrong header format');
        
        $httpRequest = $this->getHttpRequestMock('xyz');
        $adapter = new HttpBasic();
        $adapter->extractClientCredentials($httpRequest);
    }
    
    
    public function testExtractClientCredentialsWithInvalidAuth()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\MissingClientCredentialsException',
            'Invalid authorization type');
    
        $httpRequest = $this->getHttpRequestMock('xyz qwerty');
        $adapter = new HttpBasic();
        $adapter->extractClientCredentials($httpRequest);
    }


    public function testExtractClientCredentials()
    {
        $authString = 'xyz';
        $httpRequest = $this->getHttpRequestMock('Basic ' . $authString);
        
        $adapter = $this->getMockBuilder('PerunWs\Authentication\Adapter\HttpBasic')
            ->setMethods(array(
            'decodeClientCredentials'
        ))
            ->getMock();
        $adapter->expects($this->once())
            ->method('decodeClientCredentials')
            ->with($authString)
            ->will($this->returnValue($this->credentials));
        
        $this->assertSame($this->credentials, $adapter->extractClientCredentials($httpRequest));
    }


    public function testAuthenticateWithMissingOption()
    {
        $this->setExpectedException('PerunWs\Exception\MissingOptionException');
        
        $httpRequest = $this->getHttpRequestMock();
        $adapter = $this->getAdapterMock($httpRequest);
        
        $adapter->authenticate($httpRequest);
    }


    public function testAuthenicateWithMissingFile()
    {
        $this->setExpectedException('PerunWs\Authentication\Adapter\Exception\AdapterException');
        
        $httpRequest = $this->getHttpRequestMock();
        $adapter = $this->getAdapterMock($httpRequest, 
            array(
                HttpBasic::OPT_AUTH_FILE_PATH => '/some/missing/file'
            ));
        
        $adapter->authenticate($httpRequest);
    }


    public function testAuthenticateWithInvalidFile()
    {
        $this->setExpectedException('PerunWs\Authentication\Adapter\Exception\AdapterException');
        
        $httpRequest = $this->getHttpRequestMock();
        $adapter = $this->getAdapterMock($httpRequest, 
            array(
                HttpBasic::OPT_AUTH_FILE_PATH => __DIR__
            ));
        
        $adapter->authenticate($httpRequest);
    }


    public function testAuthenticationWithFileWithInvalidData()
    {
        $this->setExpectedException('PerunWs\Authentication\Adapter\Exception\AdapterException');
        
        $httpRequest = $this->getHttpRequestMock();
        $adapter = $this->getAdapterMock($httpRequest, 
            array(
                HttpBasic::OPT_AUTH_FILE_PATH => TESTS_ROOT_DIR . '/data/invalid_clients_file.php'
            ));
        
        $adapter->authenticate($httpRequest);
    }


    public function testAuthenticationWithUnknownClient()
    {
        $this->setExpectedException('PerunWs\Authentication\Exception\UnknownClientException');
        
        $adapter = new HttpBasic(
            array(
                HttpBasic::OPT_AUTH_FILE_PATH => TESTS_ROOT_DIR . '/data/clients_file.php'
            ));
        
        $adapter->authenticate($this->getHttpRequestMock('Basic ' . base64_encode('unknown_client:secret')));
    }


    public function testAuthenticationWithSuccess()
    {
        $adapter = new HttpBasic(
            array(
                HttpBasic::OPT_AUTH_FILE_PATH => TESTS_ROOT_DIR . '/data/clients_file.php'
            ));
        
        $info = $adapter->authenticate(
            $this->getHttpRequestMock('Basic ' . base64_encode($this->credentials['id'] . ':' . $this->credentials['secret'])));
        
        $this->assertInstanceOf('PerunWs\Authentication\Info', $info);
        $this->assertSame($this->credentials['id'], $info->getClientId());
        $this->assertSame($this->desc, $info->getClientDescription());
    }
    
    /*
     *
    */
    
    /**
     * @param string $authString
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHttpRequestMock($authString = null)
    {
        $request = $this->getMock('Zend\Http\Request');
        
        if ($authString) {
            $header = $this->getMock('Zend\Http\Header\HeaderInterface');
            $header->expects($this->once())
                ->method('getFieldValue')
                ->will($this->returnValue($authString));
            
            $request->expects($this->once())
                ->method('getHeader')
                ->with('Authorization')
                ->will($this->returnValue($header));
        }
        
        return $request;
    }


    protected function getAdapterMock($httpRequest, array $options = array())
    {
        $adapter = $this->getMockBuilder('PerunWs\Authentication\Adapter\HttpBasic')
            ->setMethods(array(
            'extractClientCredentials'
        ))
            ->setConstructorArgs(array(
            $options
        ))
            ->getMock();
        
        $adapter->expects($this->once())
            ->method('extractClientCredentials')
            ->with($httpRequest)
            ->will($this->returnValue($this->credentials));
        
        return $adapter;
    }
}