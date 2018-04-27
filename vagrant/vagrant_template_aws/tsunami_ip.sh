#!/bin/bash
#2. Detect and output the real IP address of the instance (?)
awsip=$(curl http://169.254.169.254/latest/meta-data/public-ipv4 --connect-timeout 2 -s)
if [ -z "$awsip" ];
then
    /sbin/ifconfig | grep 'inet addr:' | cut -d: -f2
else
    echo "aws-ip:$awsip"
fi