<?php
/**
 * A driver for handling RabbitMQ messages across the system
 * 
 * @author Anton Matiyenko <amatiyenko@gmail.com>
 */

/**
 * @todo Make a "publisher" middleware dependency injection interface, 
 * so the RabbitMQ, other providers and default system approach could be easily switched
 */

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * A utility class for all system operations where RabbitMQ is involved
 */
class RabbitMQ {
    
    private static $connection = null;
    
    private static $listenerCallbackFunction = null;
    
    /**
     * Instantiates a connection to
     */
    public static function connect() {
        if(empty(self::$connection)) {
            try {
                
                \Logger::write(
                        'Connecting... '  . "\r\n" .
                        getmypid() . ' Host: ' . \Config::read('amqp_host') . "\r\n" .
                        getmypid() . ' Port: ' . \Config::read('amqp_port') . "\r\n" .
                        getmypid() . ' User: ' . \Config::read('amqp_user'), 
                        
                        'custom', 'rabbit_connections');

                self::$connection = new AMQPStreamConnection(
                        \Config::read('amqp_host'), #host - host name where the RabbitMQ server is runing
                        \Config::read('amqp_port'), #port - port number of the service, 5672 is the default
                        \Config::read('amqp_user'), #user - username to connect to server
                        \Config::read('amqp_password')        #password
                );
            } catch(\ErrorException $e) {
                
                \Logger::write(getmypid() . ' Error', 'custom', 'rabbit_connections');
                \Logger::write(getmypid() . ' Could not connect to the RabbitMQ stack: ' . $e->getMessage(), 'custom', 'rabbit_connection_errors');
                
                die('Could not connect to the RabbitMQ stack: ' . $e->getMessage());  
            }
            
            \Logger::write(getmypid() . ' Connected', 'custom', 'rabbit_connections');
            
        }
    }
    
    /**
     * Disconnects from RabbitMQ and empties the connection object
     */
    public static function disconnect() {
        if(self::$connection) {
            \Logger::write(getmypid() . ' Disconnecting', 'custom', 'rabbit_connections');
            self::$connection->close();
        }
        self::$connection = null;        
    }

    /**
     * Publishes a RabbitMQ message in a system-standard way
     * 
     * @param type $queue
     * @param type $exchange
     * @param type $messageBody
     */
    public static function publishMessage($queue, $exchange, $messageBody) {
        
        try {
            self::connect();

            $ch = self::$connection->channel();
            $ch->queue_declare($queue, false, true, false, false);
            $ch->exchange_declare($exchange, 'direct', false, true, false);
            $ch->queue_bind($queue, $exchange);

            $ch->basic_publish(new AMQPMessage($messageBody, array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)), $exchange);

            $ch->close();

//            self::disconnect();
        } catch(\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Listens to RabbitMQ query and runs message processing by callback function
     * 
     * @param callable $callbackFunction
     */
    public static function listen($callbackFunction) {
        
        \Logger::write(getmypid() . ' Starting listening', 'custom', 'rabbit_connections');
        
        self::$listenerCallbackFunction = $callbackFunction;

        self::connect();
        $channel = self::$connection->channel();
        
        $channel->queue_declare(
                \Config::read('amqp_node_queue'), 
                false, #passive
                true, #durable
                false, #exclusive
                false #autodelete
        );

        $channel->basic_consume(
                \Config::read('amqp_node_channel'), #queue 
                '', #consumer tag - Identifier for the consumer, valid within the current channel. just string
                false, #no local - TRUE: the server will not send messages to the connection that published them
                true, #no ack - send a proper acknowledgment from the worker, once we're done with a task
                false, #exclusive - queues may only be accessed by the current connection
                false, #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
                array(__CLASS__, 'runCallback')    #callback - method that will receive the message
        );

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        self::disconnect();
    }
    
    /**
     * Runs the callback function passed to the listener: this is a proxy to carry on additional logging etc
     * 
     * @param \PhpAmqpLib\Message\AMQPMessage $message a message from AMQP stack
     * 
     * @return boolean
     */
    public static function runCallback(\PhpAmqpLib\Message\AMQPMessage $message) {
        if(self::$listenerCallbackFunction && is_callable(self::$listenerCallbackFunction)) {
            return call_user_func(self::$listenerCallbackFunction, $message);
        }
        return false;
    }

}
