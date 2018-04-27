#!/bin/bash
#defaults
echo "==> TSUNAMI VAGRANT UP ==> starting"

#1. Update client.config.php
echo "==> TSUNAMI VAGRANT UP ==> client config setup"

echo "==> TSUNAMI VAGRANT UP ==> endpoint setup"
line="\[TSUNAMI\_API\_ENDPOINT\]"
rep=$3
sed -i.bak "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI VAGRANT UP ==> protocol setup"
line="\[TSUNAMI\_API\_PROTOCOL\]"
rep=$4
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI VAGRANT UP ==> public key setup"
line="\[TSUNAMI\_PUBLIC\_KEY\]"
rep=$1
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI VAGRANT UP ==> private key setup"
line="\[TSUNAMI\_PRIVATE\_KEY\]"
rep=$2
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI VAGRANT UP ==> AMQP queue host"
line="\[TSUNAMI\_AMQP\_HOST]"
rep=$5
sed -i.bak "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI VAGRANT UP ==> AMQP queue setup"
line="\[TSUNAMI\_NODE\_AMQP\_QUEUE\]"
rep=$6
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

cd /var/www;sudo git pull origin experimental