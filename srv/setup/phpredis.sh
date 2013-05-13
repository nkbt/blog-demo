#!/bin/bash
#
cd /tmp
wget https://github.com/nicolasff/phpredis/zipball/master -O phpredis.zip
unzip -o phpredis.zip
cd nicolasff-phpredis-*
phpize
./configure
make
make install
touch /etc/php5/conf.d/redis.ini
echo extension=redis.so > /etc/php5/conf.d/redis.ini