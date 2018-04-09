<?php

namespace Tests\ObjectivePHP\Events\Callback;

use Codeception\Test\Unit;
use ObjectivePHP\Events\Callback\CallbacksAggregate;
use ObjectivePHP\Primitives\Collection\Collection;

class CallbacksAggregateTest extends Unit
{

    public function testCallbacksAreSetUsingConstructorParametersList()
    {
        $aggregate = new CallbacksAggregate('aggregate', $lambda = function () {
        }, $otherLambda = function () {
        });

        $this->assertEquals(Collection::cast([$lambda, $otherLambda]), $aggregate->getCallbacks());
    }

    public function testCallbacksAreSetUsingAnArrayAsConstructorParam()
    {
        $aggregate = new CallbacksAggregate('aggreagate', [$lambda = function () {
        }, $otherLambda = function () {
        }]);

        $this->assertEquals(Collection::cast([$lambda, $otherLambda]), $aggregate->getCallbacks());
    }

}