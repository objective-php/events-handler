<?php

namespace Tests\ObjectivePHP\Events;

use Codeception\Test\Unit;
use ObjectivePHP\Events\Event;
use ObjectivePHP\Events\Exception\EventException;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Primitives\String\Str;

class EventTest extends Unit
{
    public function testEventNameAccessorAndMutator()
    {
        $event = new Event();

        $fluent = $event->setName('event.name');
        $this->assertEquals('event.name', $event->getName());

        // check fluent interface
        $this->assertSame($event, $fluent);

        // check event name is normalized
        $event->setName('EVENT.Name');
        $this->assertEquals('event.name', $event->getName());

    }

    public function testOriginAccessorAndMutator()
    {
        $event = new Event();

        $origin = new \stdClass();

        $fluent = $event->setOrigin($origin);

        // check fluent interface
        $this->assertSame($event, $fluent);

        $this->assertEquals($origin, $event->getOrigin());

        // check that origin cannot be overriden
        $this->expectException(EventException::class);
        $this->expectExceptionCode(EventException::ORIGIN_IS_IMMUTABLE);

        $event->setOrigin('should not override $origin');

    }

    public function testContextAccessorAndMutator()
    {
        $event = new Event();

        // test default context is an array
        $this->assertEquals(new Collection(), $event->getContext());

        $context = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        $fluent = $event->setContext($context);

        // check fluent interface
        $this->assertSame($event, $fluent);

        $this->assertEquals(new Collection(['a' => 'a', 'b' => 'b', 'c' => 'c']), $event->getContext());

    }

    public function testPreviousEventReferenceAccessorAndMutator()
    {
        $previousEvent = new Event;
        $previousEvent->setName('previous.event');

        $currentEvent = new Event();
        $currentEvent->setName('current.event')->setPrevious($previousEvent);

        $this->assertInstanceOf(Event::class, $currentEvent->getPrevious());
        $this->assertEquals(Str::cast('previous.event'), $currentEvent->getPrevious()->getName());

    }

    public function testPropagationStatus()
    {
        $event = new Event;

        // check default status
        $this->assertEquals(Event::WAITING, $event->getStatus());

        // check that setting event origin updates the status to 'TRIGGERED'
        // => origin should be set only once, when the event is triggered
        $event->setOrigin(new \stdClass);

        $this->assertEquals(Event::TRIGGERED, $event->getStatus());


        $this->assertFalse($event->isHalted());
        $event->halt();
        $this->assertTrue($event->isHalted());
    }

    public function testContextCanBeReplacedByAnArray()
    {
        $event = new Event();

        $event->setContext(['a' => 'value']);
        $event->setContext(['b' => 'other value']);
        $this->assertEquals(Collection::cast(['b' => 'other value']), $event->getContext());
        /*
                $this->expectsException(function () use ($event) {

                    $event->setContext('wrong type - should be an array or an ArrayObject');

                }, EventException::class, null, EventException::INVALID_CONTEXT);

                $this->expectsException(function () use ($event) {

                    $event->setContext($this);

                }, EventException::class, null, EventException::INVALID_CONTEXT);
        */
        $this->expectException(EventException::class);
        $this->expectExceptionCode(EventException::INVALID_CONTEXT);

        $newContext = new \stdClass;
        $newContext->property = 'wrong type - should be an array or an ArrayObject';
        $event->setContext($newContext);

    }

    public function testContextCanBeOverridden()
    {
        $event = new Event();

        $event->setContext(['a' => 'value', 'b' => 'other value']);
        $event->getContext()['a'] = 'yet another value';
        $this->assertEquals(new Collection(['a' => 'yet another value', 'b' => 'other value']), $event->getContext());
    }

    public function testContextCanBeAppended()
    {
        $event = new Event();

        $event->setContext(['a' => 'value']);
        $event->getContext()['b'] = 'other value';
        $this->assertEquals(new Collection(['a' => 'value', 'b' => 'other value']), $event->getContext());

    }

    public function testEventIsFaultyWhenCallbacksReturnExceptions()
    {
        $event = new Event;
        $event->setOrigin($this);
        $event->setException('test', new EventException());

        $this->assertTrue($event->isFaulty());

        $this->assertCount(1, $event->getExceptions());
        $this->assertArrayHasKey('test', $event->getExceptions());
    }

    public function testEventResultsSetting()
    {
        $event = new Event;
        $event->setOrigin($this);
        $event->setResult('test', 'result');
        $this->assertFalse($event->isFaulty());
        $this->assertCount(1, $event->getResults());
        $this->assertArrayHasKey('test', $event->getResults()->toArray());
    }


    public function testEventThrowAnExceptionIfResultsAreAccededBeforeAnyCallbacksHasRun()
    {
        $event = new Event;
        $this->expectException(EventException::class);
        $this->expectExceptionCode(EventException::IS_NOT_TRIGGERED_YET);
        $event->getResults();
    }

    public function testEventResultsCanBeSetOnlyIfWhenEventIsTriggered()
    {
        $event = new Event;
        $this->expectException(EventException::class);
        $this->expectExceptionCode(EventException::IS_NOT_TRIGGERED_YET);
        $event->setResult('callback', 'return');
    }

    public function testEventThrowAnExceptionIfExceptionsAreAccededBeforeAnyCallbacksHasRun()
    {
        $event = new Event;
        $this->expectException(EventException::class);
        $this->expectExceptionCode(EventException::IS_NOT_TRIGGERED_YET);
        $event->getExceptions();
    }

    public function testExceptionsCanBeSetOnEventOnlyIfEventIsTriggered()
    {
        $event = new Event;
        $this->expectException(EventException::class);
        $this->expectExceptionCode(EventException::IS_NOT_TRIGGERED_YET);
        $event->setException('callback', new \Exception('test'));
    }
}
