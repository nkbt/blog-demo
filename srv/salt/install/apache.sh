#!/bin/bash
#

cd /tmp
apt-get -y -qq install apache2 libapache2-mod-php5
a2enmod rewrite

echo  # an empty line
echo "changed=yes comment='Apache installed'"