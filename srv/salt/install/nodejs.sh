#!/bin/bash

cd /tmp
add-apt-repository -y ppa:chris-lea/node.js
apt-get -y -qq update
apt-get -y -qq install nodejs
npm install -g forever

echo  # an empty line
echo "changed=yes comment='NodeJS and Forever installed'"