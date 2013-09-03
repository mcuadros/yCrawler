<?php
namespace yCrawler\Tests;
use yCrawler\Request;
use yCrawler\Request\Exceptions;

class RequestTest extends  \PHPUnit_Framework_TestCase
{
    private function createRequest()
    {
        return new Request();
    }

    public function testSetURL()
    {
        $request = $this->createRequest();
        $request->setURL('http://www.yunait.com/');

        $this->assertEquals('http://www.yunait.com/', $request->getUrl());
        $this->assertEquals('199a0e28a47733f59d54938386c0c41d', $request->getId());
        $this->assertEquals('http', $request->getScheme());
        $this->assertEquals('www.yunait.com', $request->getHost());
    }

    public function testSetPost()
    {
        $test = Array('test' => 'element');
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/post');
        $request->setPost($test);
        $request->call();

        $response = json_decode($request->getResponse(), true);
        $this->assertEquals($test, $response['form']);
    }

    public function testSetUserAgent()
    {
        $test = 'Uno cualquiera';
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/user-agent');
        $request->setUserAgent($test);
        $request->call();

        $response = json_decode($request->getResponse(), true);
        $this->assertEquals('Uno cualquiera', $response['user-agent']);
    }

    public function testSetHeaders()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/user-agent');
        $request->setHeaders(true);
        $request->call();

        $this->assertTrue(strlen($request->getResponseHeaders()) > 1);
    }

    /**
     * @expectedException yCrawler\Request\Exceptions\NetworkError
     * @expectedExceptionCode 28
     */
    public function testSetMaxExecutionTime()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/delay/2');
        $request->setMaxExecutionTime(1);
        $request->call();
    }

    public function testSetCookie()
    {
        $test = sys_get_temp_dir() . '/' . 'cookie' . uniqid();
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/cookies/set?supercali=fristicoespialidoso');
        $request->setCookie($test);
        $request->call();

        //Se hace unset para que se escriba la cookie al sistema.
        unset($request);

        $cookieContent = file_get_contents($test);
        $this->assertTrue(strpos($cookieContent, 'supercali') > 1);
        $this->assertTrue(strpos($cookieContent, 'fristicoespialidoso') > 1);
    }

    public function testGetResponseCode()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/status/200');
        $request->call();

        $this->assertEquals(200, $request->getResponseCode());
    }

    public function testGetResponseCodeNetworkError()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/delay/2');
        $request->setMaxExecutionTime(1);

        try {
            $request->call();
        } catch (Exceptions\NetworkError $e) {
            $this->assertSame(28, $request->getResponseCode());
            $this->assertSame(Request::STATUS_RETRY, $request->getStatus());
        }
    }

    public function testGetResponseCodeHTTPError()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/status/418');

        try {
            $request->call();
        } catch (Exceptions\HTTPError $e) {
            $this->assertSame(418, $request->getResponseCode());
            $this->assertSame(Request::STATUS_FAILED, $request->getStatus());
        }
    }

    /**
     * @expectedException yCrawler\Request\Exceptions\HTTPError
     * @expectedExceptionCode 418
     */
    public function testHTTPError()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/status/418');
        $request->call();
    }

    public function testGetExecutionTime()
    {
        $request = $this->createRequest();
        $request->setURL('http://httpbin.org/status/200');
        $request->call();

        $this->assertTrue($request->getExecutionTime() != null);
    }

}
