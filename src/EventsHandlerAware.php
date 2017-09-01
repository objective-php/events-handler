<?php
namespace ObjectivePHP\Events;

/**
 * Trait EventsHandlerAware
 * @package ObjectivePHP\Events
 */
trait EventsHandlerAware
{
    /**
     * @var EventsHandler $eventsHandler
     */
    protected $eventsHandler;

    /**
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler
    {
        return $this->eventsHandler;
    }

    /**
     * @param EventsHandler $eventsHandler
     * @return $this
     */
    public function setEventsHandler(EventsHandler $eventsHandler)
    {
        $this->eventsHandler = $eventsHandler;

        return $this;
    }
}
