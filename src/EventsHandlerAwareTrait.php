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

    /**
     * Proxy trigger method
     *
     * @param string              $eventName
     * @param mixed               $origin
     * @param array               $context
     * @param EventInterface|null $event
     */
    public function trigger($eventName, $origin = null, $context = [], EventInterface $event = null)
    {
        if ($eventsHandler = $this->getEventsHandler()) {
            $eventsHandler->trigger($eventName, $origin, $context, $event);
        }
    }

    /**
     * Proxy bind method
     *
     * @param string $eventName
     * @param string $callback
     * @param string $mode
     */
    public function bind($eventName, $callback, $mode = EventsHandlerInterface::BINDING_MODE_LAST)
    {
        if ($eventsHandler = $this->getEventsHandler()) {
            $eventsHandler->bind($eventName, $callback, $mode);
        }
    }
}
