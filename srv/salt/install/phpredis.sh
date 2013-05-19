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

touch /etc/php5/mods-available/redis.ini
echo "extension=redis.so" > /etc/php5/mods-available/redis.ini
php5enmod redis

echo  # an empty line
echo "changed=yes comment='PHPRedis installed'"