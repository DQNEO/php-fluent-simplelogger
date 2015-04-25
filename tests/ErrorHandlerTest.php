<?php
/**
 */

namespace FluentTests\FluentLogger;

use DQNEO\Fluent\SimpleLogger;

function fluentTests_FluentLogger_DummyFunction()
{
}

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testing compatible before and after
     *
     * @dataProvider errorHandlerProvider
     */
    public function testRegisterErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('DQNEO\Fluent\SimpleLogger');
        $this->assertTrue($base->registerErrorHandler($eh));
    }

    public function errorHandlerProvider()
    {
        return array(
            array(
                'FluentTests\FluentLogger\fluentTests_FluentLogger_DummyFunction'
            ),
            array(
                array($this, 'errorHandlerProvider')
            ),
            array(
                function () {
                }, // closure
            ),
        );
    }

    /**
     * @dataProvider invalidErrorHandlerProvider
     * @expectedException InvalidArgumentException
     */
    public function testRegisterInvalidErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('DQNEO\Fluent\SimpleLogger');
        $base->registerErrorHandler($eh);
    }

    public function invalidErrorHandlerProvider()
    {
        return array(
            array(
                null,
            ),
            array(
                array($this, 'errorHandlerProvider_Invalid') // not exists
            ),
            array(
                array($this, 'errorHandlerProvider_Invalid', 'hoge') // invalid
            ),
        );
    }
}
