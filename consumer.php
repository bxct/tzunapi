<?php

require_once __DIR__ . '/init/common.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$conn = new AMQPStreamConnection(
        \Config::read('amqp_host'), #host - host name where the RabbitMQ server is runing
        \Config::read('amqp_port'), #port - port number of the service, 5672 is the default
        \Config::read('amqp_user'), #user - username to connect to server
        \Config::read('amqp_password')        #password
);

$ch = $conn->channel();
$ch->queue_declare(\Config::read('amqp_queue_name'), false, true, false, false);
$ch->exchange_declare(\Config::read('amqp_exchange'), 'direct', false, true, false);
$ch->queue_bind(\Config::read('amqp_queue_name'), \Config::read('amqp_exchange'));

$ch->basic_consume(\Config::read('amqp_queue_name'), '', false, false, false, false, 'process_message');

function shutdown($ch, $conn) {
    $ch->close();
    $conn->close();
}

function process_message($msg) {
    echo "\n--------\n";
    echo $msg->body;
    echo "\n--------\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    if ($msg->body === 'quit') {
        $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
    }
}

register_shutdown_function('shutdown', $ch, $conn);

// Loop as long as the channel has callbacks registered
while (count($ch->callbacks)) {
    $ch->wait();
}
