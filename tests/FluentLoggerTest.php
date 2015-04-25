<?php

namespace FluentTests\FluentLogger;

use DQNEO\Fluent;
use DQNEO\Fluent\SimpleLogger;

class FluentLoggerTest extends \PHPUnit_Framework_TestCase
{
    const TAG          = 'debug.test';
    const OBJECT_KEY   = 'hello';
    const OBJECT_VALUE = 'world';

    public function tearDown()
    {
        SimpleLogger::clearInstances();
    }

    /**
     * Post will return true in the case of posting successfully
     */
    public function testPostWillReturnTrueInTheCaseOfPostingSuccessfully()
    {
        $socket = fopen("php://memory", "a+");

        /* localhost is dummy string. we set php://memory as a socket */
        $logger     = SimpleLogger::open("localhost");
        $reflection = new \ReflectionProperty("DQNEO\Fluent\SimpleLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);

        $this->assertTrue($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");

        return $socket;
    }

    /**
     * @depends testPostWillReturnTrueInTheCaseOfPostingSuccessfully
     */
//    public function testPostedStringIsJson($socket)
//    {
//        fseek($socket, 0);
//        $actual = "";
//        while ($string = fread($socket, 1024)) {
//            $actual .= $string;
//        }
//        $this->assertStringMatchesFormat('["debug.test",%d,{"foo":"bar"}]', $actual);
//    }

    /**
     *  fwrite on read only memory returns zero
     *  HHVM has a bug which returns a positive integer
     *  see https://github.com/facebook/hhvm/issues/5187
     */
    public function testFwriteOnReadonlyMemoryRreturnsZero()
    {
        $n = fwrite(fopen("php://memory", "r"), "hello");
        $this->assertEquals(0, $n, "fwrite on ROM returns 0");
    }


    /**
     * Post will return false in the case of posting unsuccessfully by reached max retry count
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByReachedMaxRetryCount()
    {
        /* localhost is dummy string. we set php://memory as a socket */
        $logger = SimpleLogger::open("localhost");
        $this->setSocket($logger, fopen("php://memory", "r"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by writing failed
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByWritingFailed()
    {
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(false));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    /**
     * Post will return false in the case of posting unsuccessfully by connection aborted
     */
    public function testPostWillReturnFalseInTheCaseOfPostingUnsuccessfullyByConnectionAborted()
    {
        $logger = $this->getMockOfLogger(array("write"));
        $logger->expects($this->any())->method("write")->will($this->returnValue(""));
        $this->setSocket($logger, fopen("php://memory", "a+"));

        $this->assertFalse($logger->post(self::TAG, array("foo" => "bar")), "Post method returned boolean");
    }

    private function setSocket($logger, $socket)
    {
        $reflection = new \ReflectionProperty("DQNEO\Fluent\SimpleLogger", "socket");
        $reflection->setAccessible(true);
        $reflection->setValue($logger, $socket);
    }

    private function getMockOfLogger(array $method)
    {
        return $this->getMock("DQNEO\Fluent\SimpleLogger", array("write"), array("localhost"));
    }

    /**
     * @dataProvider providesTransport
     */
    public function testGetTransportUri($host, $port, $expected_uri, $error_msg)
    {
        $actual_uri = SimpleLogger::getTransportUri($host, $port);
        $this->assertEquals($expected_uri, $actual_uri, $error_msg);
    }

    public function providesTransport()
    {
        return array(
            array("localhost", 8080, "tcp://localhost:8080", "unexpected uri returns"),
            array("127.0.0.1", 8080, "tcp://127.0.0.1:8080", "unexpected uri returns"),
            array("tcp://localhost", 8080, "tcp://localhost:8080", "unexpected uri returns"),
            array("tcp://127.0.0.1", 8080, "tcp://127.0.0.1:8080", "unexpected uri returns"),
            array("unix:///var/fluentd", 0, "unix:///var/fluentd", "unexpected uri returns"),
            array("unix:///var/fluentd", 8080, "unix:///var/fluentd", "unix domain uri have to ignores port number"),
            array("fe80::1", 8080, "tcp://[fe80::1]:8080", "ipv6 support failed"),
            array("tcp://fe80::1", 8081, "tcp://[fe80::1]:8081", "ipv6 support failed"),
            array("tcp://[fe80::1]", 8082, "tcp://[fe80::1]:8082", "ipv6 support failed"),
        );
    }

    public function testGetTransportUriCauseExcpetion()
    {
        try {
            SimpleLogger::getTransportUri("udp://localhost", 1192);
            $this->fail("getTransportUri does not thorow exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }
    }

    public function testClearInstances()
    {
        $prop = new \ReflectionProperty("\DQNEO\Fluent\SimpleLogger", "instances");
        $prop->setAccessible(true);

        SimpleLogger::open("localhost", 1191);
        SimpleLogger::open("localhost", 1192);
        $this->assertCount(2, $prop->getValue("SimpleLogger"));

        SimpleLogger::clearInstances();
        $this->assertCount(0, $prop->getValue("SimpleLogger"));
    }

    public function testMergeOptions()
    {
        $logger = new SimpleLogger("localhost");
        $prop   = new \ReflectionProperty($logger, "options");
        $prop->setAccessible(true);

        $default = $prop->getValue($logger);

        $additional_options = array("socket_timeout" => 10);
        $logger->mergeOptions($additional_options);
        $this->assertEquals(array_merge($default, $additional_options), $prop->getValue($logger), "mergeOptions looks wired");
    }

    public function testMergeOptionsThrowsException()
    {
        $logger             = new SimpleLogger("localhost");
        $additional_options = array("unexpected_key" => 10);
        try {
            $logger->mergeOptions($additional_options);
            $this->fail("mergeOptions doesn't thorw Exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }

    }

    public function testSetOptions()
    {
        $logger = new SimpleLogger("localhost");
        $prop   = new \ReflectionProperty($logger, "options");
        $prop->setAccessible(true);

        $additional_options = array("socket_timeout" => 10);
        $logger->setOptions($additional_options);
        $this->assertEquals($additional_options, $prop->getValue($logger), "setOptions looks wired");
    }

    public function testConnect()
    {
        $logger = new SimpleLogger("localhost", 119223);
        $method = new \ReflectionMethod($logger, "connect");
        $method->setAccessible(true);
        try {
            $method->invoke($logger);
            $this->fail("mergeOptions doesn't thorw Exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }
    }

    public function testGetOption()
    {
        $logger = new SimpleLogger("localhost", 119223);
        $this->assertEquals(SimpleLogger::CONNECTION_TIMEOUT, $logger->getOption("socket_timeout"),
            "getOptions retunrs unexpected value");
    }

    public function testReconnect()
    {
        $logger = new SimpleLogger("localhost", 119223);
        $method = new \ReflectionMethod($logger, "reconnect");
        $method->setAccessible(true);
        try {
            $method->invoke($logger);
            $this->fail("reconnect doesn't throw Exception");
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
        }
        $fp   = fopen("php://memory", "r");
        $prop = new \ReflectionProperty($logger, "socket");
        $prop->setAccessible(true);
        $prop->setValue($logger, $fp);
        $method->invoke($logger);
    }
}
