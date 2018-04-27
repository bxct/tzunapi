#!/bin/bash
sudo wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
sudo apt-key add rabbitmq-signing-key-public.asc
sudo apt-get update
sudo apt-get install -q -y screen htop vim curl wget
sudo apt-get install -q -y rabbitmq-server
# RabbitMQ Plugins
sudo service rabbitmq-server stop
sudo rabbitmq-plugins enable rabbitmq_management
sudo rabbitmq-plugins enable rabbitmq_jsonrpc
sudo service rabbitmq-server start
sudo rabbitmq-plugins list
rabbitmqctl change_password guest ${RABBITMQ_PASSWORD}

sudo service rabbitmq-server stop
sudo cp /var/www/vagrant/vagrant_template_aws/rabbitmq.config /etc/rabbitmq/
sudo service rabbitmq-server start

sudo iptables -I INPUT 1 -p tcp --dport 5677 -j ACCEPT
sudo iptables -I INPUT 1 -p tcp --dport 5673 -j ACCEPT
sudo iptables -I INPUT 1 -p tcp --dport 13313 -j ACCEPT