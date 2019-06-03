<?php

namespace Tests\ObjectivePHP\Events;

use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Events\EventsHandlerAccessors;
use PHPUnit\Framework\TestCase;

/**
 * Class EventsHandlerAwareTraitTest
 *
 * @package Tests\ObjectivePHP\Events
 */
class EventsHandlerAwareTraitTest extends TestCase
{
    public function testEventsHandlerAccessors()
    {
        $instance = new class {
            use EventsHandlerAccessors;
        };

        $eventsHandler = new EventsHandler();

        $instance->setEventsHandler($eventsHandler);

        $this->assertEquals($eventsHandler, $instance->getEventsHandler());
        $this->assertAttributeEquals($instance->getEventsHandler(), 'eventsHandler', $instance);
    }

    public function testEventIsTriggered()
    {
        $instance = new class {
            use EventsHandlerAccessors;
        };

        $eventName = 'event.test';
        $origin = $this;

        $eventsHandler = $this->getMockBuilder(EventsHandler::class)->getMock();
        $eventsHandler->expects($this->once())->method('trigger')->with($eventName, $origin);

        $instance->setEventsHandler($eventsHandler);

        $instance->trigger($eventName, $this);
    }

    public function testEventIsBound()
    {
        $instance = new class {
            use EventsHandlerAccessors;
        };

        $eventName = 'event.test';
        $callback = function () {};

        $eventsHandler = $this->getMockBuilder(EventsHandler::class)->getMock();
        $eventsHandler->expects($this->once())->method('bind')->with($eventName, $callback);

        $instance->setEventsHandler($eventsHandler);

        $instance->bind($eventName, $callback);
    }

    public function testEventIsNotTriggered()
    {
        $instance = new class {
            use EventsHandlerAccessors;
        };

        $eventsHandler = $this->getMockBuilder(EventsHandler::class)->getMock();
        $eventsHandler->expects($this->never())->method('trigger');

        $instance->trigger('test', $this);
    }

    public function testEventIsNotBound()
    {
        $instance = new class {
            use EventsHandlerAccessors;
        };

        $eventsHandler = $this->getMockBuilder(EventsHandler::class)->getMock();
        $eventsHandler->expects($this->never())->method('bind');

        $instance->bind('test', function () {});
    }
}
