<?php

namespace ObjectivePHP\Events;

/**
 * Interface EventsHandlerAwareInterface
 *
 * @package ObjectivePHP\Events
 */
interface EventsHandlerAwareInterface
{
    /**
     * Get the EventsHandler instance
     *
     * @return EventsHandlerInterface
     */
    public function getEventsHandler();

    /**
     * Set the EventsHandler instance
     *
     * @param EventsHandlerInterface $eventsHandler
     *
     * @return $this
     */
    public function setEventHandler(EventsHandlerInterface $eventsHandler);
}
