#!/bin/bash
#

add-apt-repository -y ppa:chris-lea/node.js
apt-get update
apt-get install nodejs npm
npm install -g forever