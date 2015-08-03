<?php

    namespace ObjectivePHP\Events;

    use ObjectivePHP\Matcher\Matcher;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\ServicesFactory\ServicesFactory;
    use ObjectivePHP\ServicesFactory\Reference;

    class EventsHandler
    {

        const BINDING_MODE_REPLACE = 'replace';
        const BINDING_MODE_FIRST   = 'first';
        const BINDING_MODE_LAST    = 'last';

        protected $currentEventsQueue = [];

        protected $listeners          = [];

        protected $unboundListeners   = [];

        protected $aliases            = [];

        /**
         * @var Matcher
         */
        protected $matcher;

        /**
         * The service factory allows to bind services directly to events
         *
         * @var ServicesFactory
         */
        protected $servicesFactory;

        /**
         *
         */
        public function __construct()
        {
            $this->setMatcher(new Matcher());
        }

        /**
         * Trigger an event
         *
         * @param string                        $eventName
         * @param mixed                         $origin
         * @param array|\ArrayObject|Collection $context
         *
         * @return mixed
         */
        public function trigger($eventName, $origin = null, $context = [])
        {

            if (is_null($origin))
            {
                $origin = $this;
            }

            // cannot get event from factory
            // because of injection process
            // which triggers an event causing
            // an infinite loop...
            $event = (new Event())->setName($eventName)->setOrigin($origin)->setContext($context);

            // add reference to previous event if any
            if ($previous = \current($this->currentEventsQueue))
            {
                $event->setPrevious($previous);
            }

            $this->currentEventsQueue [(string) $event->getName()] = $event;

            $listeners = $this->getListeners($eventName);

            // TODO sort listeners according to their priority or other mechanism
            $i = 0;
            foreach ($listeners as $listenersGroup)
            {
                foreach ($listenersGroup as $alias => $listener)
                {

                    // handle service references
                    if($listener instanceof Reference)
                    {
                        $listener = $this->getServicesFactory()->get($listener->getId());
                    }

                    $result = call_user_func($listener, $event);

                    // gathers exceptions
                    if ($result instanceof \Exception)
                    {
                        $event->setException($i, $result);
                    }

                    $event->setResult(is_string($alias) ? $alias : $i, $result);

                    if ($event->isHalted())
                    {
                        // yes, this is a goto...
                        // I know that it was not absolutely needed, but it's a quite long story
                        // so please keep it as is.
                        // ping @EmmanuelJacoby :)
                        //
                        // @gdelamarre
                        goto shunt;
                    }
                    $i++;
                }
            }

            // target reached from within the triggering loop
            // if the event was halted by a listener
            shunt:
            // event has been triggered, it is now removed from events queue
            array_pop($this->currentEventsQueue);
            end($this->currentEventsQueue);

            return $event;
        }

        /**
         * Attaches a callback to an event
         *
         * @param string                $eventName  Event reference
         * @param string|callable|array $callback   Callback to attach to the event or component reference. If an array is passed, several listeners are bound at once, and array keys (if associative) are used as listeners aliases.
         * @param string                $mode       Tells where to stack the callbacks for a given event
         */
        public function bind($eventName, $callback, $mode = self::BINDING_MODE_LAST)
        {
            // default alias is ... no alias
            $alias = false;

            // check if callback is aliased
            // need to distinguish between callables and actual arrays
            if (is_array($callback) && !is_callable($callback))
            {
                list ($alias, $callback) = each($callback);
                if (!isset ($this->aliases[$alias]))
                {
                    $this->aliases[$alias] = &$callback;
                }
                else
                {
                    throw new Exception(sprintf('Alias "%s" is already bound to another callback', $alias), Exception::EVENT_INVALID_CALLBACK);
                }
            }
            else
            {
                // check if listener is an alias of a previous listener
                if (is_string($callback) && !is_callable($callback))
                {
                    if (isset ($this->aliases[$callback]))
                    {
                        $alias    = $callback;
                        $callback = $this->aliases[$callback];
                    }
                }
                else
                {
                    $alias = false;
                }
            }

            // check callback validity
            if (!is_callable($callback) && !$callback instanceof Reference)
            {
                throw new Exception ('Callback must be a callable or a service reference', Exception::EVENT_INVALID_CALLBACK);
            }

            if (!isset ($this->listeners [$eventName]) || $mode == self::BINDING_MODE_REPLACE)
            {
                $this->listeners [$eventName] = [];
            }

            switch ($mode)
            {
                case self::BINDING_MODE_FIRST :
                    if ($alias)
                    {
                        $this->listeners [$eventName] = array_merge([
                            $alias => $callback
                        ], $this->listeners [$eventName]);
                    }
                    else
                    {
                        array_unshift($this->listeners [$eventName], $callback);
                    }
                    break;

                case self::BINDING_MODE_REPLACE :
                    // same case because if mode == replace, listeners[eventName] has been emptied
                case self::BINDING_MODE_LAST :
                    if ($alias)
                    {
                        $this->listeners [$eventName] [$alias] = $callback;
                    }
                    else
                    {
                        $this->listeners [$eventName] [] = $callback;
                    }
                    break;
            }

            return $this;
        }

        public function getListeners($eventFilter = '*')
        {
            if ($eventFilter == '*')
            {
                return $this->listeners;
            }

            $listeners        = array_keys($this->listeners);
            $matcher          = $this->getMatcher();
            $matchedListeners = array_flip(array_filter($listeners, function ($listener) use ($eventFilter, $matcher)
            {
                return $matcher->match($eventFilter, $listener);
            }));

            return array_intersect_key($this->listeners, $matchedListeners);
        }

        public function unbind($eventFilter)
        {

            // get bound listeners
            $boundListeners = $this->getListeners($eventFilter);

            if (!$boundListeners)
            {
                // no listener to unbound
                return $this;
            }

            // actually unbound listeners
            $this->listeners = array_diff_key($this->listeners, $boundListeners);

            // log unbound listeners
            if (!isset($this->unboundListeners[$eventFilter]))
            {
                $this->unboundListeners[$eventFilter] = [];
            }

            // blacklist event name for
            $this->unboundListeners [$eventFilter] += $boundListeners;

            return $this;
        }

        public function getUnboundListeners()
        {
            return $this->unboundListeners;
        }

        /**
         * @param Matcher $matcher
         *
         * @return $this
         */
        public function setMatcher(Matcher $matcher = null)
        {
            $this->matcher = $matcher;

            return $this;
        }

        /**
         * @return Matcher
         */
        public function getMatcher()
        {
            return $this->matcher;
        }

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory()
        {
            return $this->servicesFactory;
        }

        /**
         * @param ServicesFactory $servicesFactory
         *
         * @return $this
         */
        public function setServicesFactory(ServicesFactory $servicesFactory)
        {
            $this->servicesFactory = $servicesFactory;
            return $this;
        }

}