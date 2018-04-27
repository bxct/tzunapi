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

#2. Detect and output the real IP address of the instance (?)
awsip=$(curl http://169.254.169.254/latest/meta-data/public-ipv4 --connect-timeout 2 -s)
if [ -z "$awsip" ];
then
    /sbin/ifconfig | grep 'inet addr:' | cut -d: -f2
else
    echo "aws-ip:$awsip"
fi