<?php
/**
 * @license MIT
 */

namespace Driver\Core\Queuer;

use \PhpAmqpLib\Connection\AMQPConnection;
use \PhpAmqpLib\Message\AMQPMessage;

class Rabbitmq implements \Driver\Core\Tool\Queuer
{
    protected $host = 'localhost';
    protected $port = 5672;
    protected $user = 'guest';
    protected $pass = 'guest';

    private $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = new AMQPConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->pass
        );
        $this->channel = $this->connection->channel();
    }

    public function queue($task, $message)
    {
        $this->channel->queue_declare($task, FALSE, TRUE, FALSE, FALSE);
        $amqp_message = new AMQPMessage($message, array('delivery_mode' => 2));
        $this->channel->basic_publish($amqp_message, '', $task);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
