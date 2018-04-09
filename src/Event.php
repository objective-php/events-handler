<?php

namespace ObjectivePHP\Events;

use ObjectivePHP\Events\Exception\EventException;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Primitives\String\Str;

/**
 * Class Event
 * @package ObjectivePHP\Events
 */
class Event implements EventInterface
{

    /**
     *
     */
    const WAITING = 'waiting';
    /**
     *
     */
    const TRIGGERED = 'triggered';
    /**
     *
     */
    const FINISHED = 'finished';
    /**
     *
     */
    const HALTED = 'halted';

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $previous;

    /**
     * @var
     */
    protected $origin;

    /**
     * @var Collection
     */
    protected $context;

    /**
     * @var Collection
     */
    protected $results;

    /**
     * @var Collection
     */
    protected $exceptions;

    /**
     * @var string
     */
    protected $status = self::WAITING;


    /**
     * Event constructor.
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function __construct()
    {
        $this->results = new Collection();
        $this->context = new Collection();
        $this->exceptions = (new Collection())->restrictTo(\Exception::class, false);
    }

    /**
     * Event name setter
     *
     * @param string $name The event name
     */
    public function setName($name)
    {
        $name = Str::cast($name);

        $this->name = $name->lower();

        return $this;
    }

    /**
     * @return Str
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Event origin setter
     *
     * @param mixed $origin Source of the event (usually the object from which the event was triggered)
     * @throws EventException
     */
    public function setOrigin($origin)
    {
        // origin overwriting is forbidden
        if (!is_null($this->origin)) {
            throw new EventException('Overwriting origin of an event is forbidden', EventException::ORIGIN_IS_IMMUTABLE);
        }

        $this->origin = $origin;

        // update status to reflect event triggering
        // => setting the origin means that the event has been triggered
        $this->status = self::TRIGGERED;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Event status getter (no mutator on this property)
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Event context setter
     *
     * @param array|\ArrayObject|Collection $context context parameters
     * @param string $mode
     * @throws EventException
     */
    public function setContext($context)
    {
        if (!is_array($context) && !$context instanceof \ArrayObject && !$context instanceof \Iterator) {
            throw new EventException('Unexpected value type for context', EventException::INVALID_CONTEXT);
        }

        $context = Collection::cast($context);

        $this->context = $context;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Event predecessor accessor
     *
     * This set the event from which this event has been triggered
     *
     * @param EventInterface $event Previous event
     *
     * @return Event
     */
    public function setPrevious(EventInterface $event)
    {
        $this->previous = $event;

        return $this;

    }

    /**
     * @return EventInterface
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * Stops event propagation
     */
    public function halt()
    {
        $this->status = self::HALTED;

        return $this;
    }

    /**
     * Indicates whether the current event has stopped event propagation
     *
     * @return bool
     */
    public function isHalted()
    {
        return $this->status == self::HALTED;
    }


    /**
     * @return Collection
     * @throws EventException
     */
    public function getResults()
    {
        if ($this->getStatus() == self::WAITING) {
            throw new EventException('Event results cannot be retrieved before it has been triggered', EventException::IS_NOT_TRIGGERED_YET);
        }

        return $this->results;
    }

    /**
     * @param $callbackName
     * @param $result
     * @return $this
     * @throws EventException
     */
    public function setResult($callbackName, $result)
    {
        if ($this->getStatus() == self::WAITING) {
            throw new EventException('Event result can be set once event has been triggered only', EventException::IS_NOT_TRIGGERED_YET);
        }

        $this->results[$callbackName] = $result;

        return $this;
    }

    /**
     * @return $this|Collection
     * @throws EventException
     */
    public function getExceptions()
    {
        if ($this->getStatus() == self::WAITING) {
            throw new EventException('Event exceptions cannot be retrieved before it has been triggered', EventException::IS_NOT_TRIGGERED_YET);
        }

        return $this->exceptions;
    }

    /**
     * @param $callbackName
     * @param \Exception $exception
     * @return $this
     * @throws EventException
     */
    public function setException($callbackName, \Throwable $exception)
    {
        if ($this->getStatus() == self::WAITING) {
            throw new EventException('Event exception can be set once event has been triggered only', EventException::IS_NOT_TRIGGERED_YET);
        }

        $this->getExceptions()[$callbackName] = $exception;

        return $this;
    }

    /**
     * @return bool
     * @throws EventException
     */
    public function isFaulty()
    {
        return !$this->getExceptions()->isEmpty();
    }

}