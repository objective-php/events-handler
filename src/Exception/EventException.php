<?php

namespace ObjectivePHP\Events\Exception;

class EventException extends \Exception
{
    // error codes
    const ORIGIN_IS_IMMUTABLE = 0x20;
    const STATUS_IS_IMMUTABLE = 0x21;
    const IS_NOT_TRIGGERED_YET = 0x22;
    const INVALID_CALLBACK = 0x23;
    const INVALID_CONTEXT = 0x24;
}