#!/bin/bash

if [ $# -eq 0 ]
then
    echo "==> REBUILD ==> Assumed default box name"
    BOX=ubuntu-14.04.2
fi

echo $BOX

if [ -d ./box ];
then
    echo "==> REBUILD ==> Removing old boxes"
    rm -r ./box
fi

#1. remove existing box
echo "==> REBUILD ==> Removing Vagrant box"
vagrant box remove my/vendor_$BOX

#2. back the Vagrantfile up
echo "==> REBUILD ==> Checking if Vagrantfile exists"
if [ -f ./Vagrantfile ];
then
    echo "==> REBUILD ==> Backing Vagrantfile up"
    mv ./Vagrantfile ./bcp_Vagrantfile
fi

#3. build the boxes
echo "==> REBUILD ==> Building boxes"
packer build  -var "iso_url=./$BOX-server-amd64.iso" vendor_$BOX.json

#4. add the created vagrant box
echo "==> REBUILD ==> Adding Parallels box"
vagrant box add my/vendor_$BOX ./box/parallels/vendor_$BOX-nocm-0.1.0.box

#5 recover the Vagrantfile
echo "==> REBUILD ==> Checking if Vagrantfile backed up"
if [ -f ./bcp_Vagrantfile ];
then
    echo "==> REBUILD ==> Restoring Vagrantfile"
    mv ./bcp_Vagrantfile ./Vagrantfile
fi