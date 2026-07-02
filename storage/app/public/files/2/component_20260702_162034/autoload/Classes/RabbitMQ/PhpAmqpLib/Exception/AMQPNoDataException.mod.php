<?php

namespace Classes\RabbitMQ\PhpAmqpLib\Exception;

/**
 * Used mostly in non-blocking methods when no data is ready for processing.
 */
class AMQPNoDataException extends AMQPRuntimeException
{
}
