<?php

namespace ObjectivePHP\Events;

/**
 * Trait EventsHandlerAwareTrait
 *
 * @package ObjectivePHP\Events
 */
trait EventsHandlerAwareTrait
{
    /**
     * @var EventsHandlerInterface $eventsHandler
     */
    protected $eventsHandler;

    /**
     * Get EventsHandler
     *
     * @return EventsHandlerInterface
     */
    public function getEventsHandler()
    {
        return $this->eventsHandler;
    }

    /**
     * Set EventsHandler
     *
     * @param EventsHandlerInterface $eventsHandler
     *
     * @return $this
     */
    public function setEventsHandler(EventsHandlerInterface $eventsHandler)
    {
        $this->eventsHandler = $eventsHandler;

        return $this;
    }
}
