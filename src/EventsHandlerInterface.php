<?php

namespace ObjectivePHP\Events;

/**
 * Interface EventsHandlerInterface
 *
 * @package ObjectivePHP\Events
 */
interface EventsHandlerInterface
{
    const BINDING_MODE_REPLACE = 'replace';
    const BINDING_MODE_FIRST = 'first';
    const BINDING_MODE_LAST = 'last';

    /**
     * Trigger an event
     *
     * @param string         $eventName
     * @param mixed          $origin
     * @param array          $context
     * @param EventInterface $event
     *
     * @return EventInterface
     */
    public function trigger($eventName, $origin = null, $context = [], EventInterface $event = null);

    /**
     * Attaches a callback to an event
     *
     * @param string                $eventName Event reference
     * @param string|callable|array $callback  Callback to attach to the event or component reference. If an array is
     *                                         passed, several listeners are bound at once, and array keys (if
     *                                         associative) are used as listeners aliases.
     * @param string                $mode      Tells where to stack the callbacks for a given event
     *
     * @return $this
     */
    public function bind($eventName, $callback, $mode = self::BINDING_MODE_LAST);
}
