<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 14/08/15
     * Time: 18:40
     */
    
    namespace ObjectivePHP\Events;
    
    
    interface EventInterface
    {

        public function setName($name);

        public function getName();

        public function setOrigin($origin);

        public function getOrigin();

        public function setPrevious(EventInterface $previous);

        public function getPrevious();

        public function getResults();

        public function getContext();

        public function setContext($context);

        public function getStatus();

        public function getExceptions();

        public function halt();

        public function isHalted();

        public function isFaulty();




    }