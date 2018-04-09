<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 14/08/15
 * Time: 18:40
 */

namespace ObjectivePHP\Events;


use ObjectivePHP\Primitives\Collection\Collection;

/**
 * Interface EventInterface
 * @package ObjectivePHP\Events
 */
interface EventInterface
{

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @param $origin
     * @return mixed
     */
    public function setOrigin($origin);

    /**
     * @param EventInterface $previous
     * @return mixed
     */
    public function setPrevious(EventInterface $previous);

    /**
     * @return Collection
     */
    public function getResults();

    /**
     * @return Collection
     */
    public function getContext();

    /**
     * @param $context
     * @return mixed
     */
    public function setContext($context);

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @return Collection
     */
    public function getExceptions();

    /**
     * @return mixed
     */
    public function halt();

    /**
     * @return bool
     */
    public function isHalted();

    /**
     * @return bool
     */
    public function isFaulty();

}