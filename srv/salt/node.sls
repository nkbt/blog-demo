##
##
##
##
##
##
## Misc. needed packages
##
#git:
#  pkg:
#    - installed
#
#build-essential:
#  pkg:
#    - installed

redis-server:
  pkg:
    - installed

unzip:
  pkg:
    - installed






#
#
#
#
#
#
# Apache
#
apache:
  cmd.script:
    - source: salt://install/apache.sh
    - unless: "command -v apache2 | grep apache2"
    - stateful: true

  service.running:
    - name: apache2
    - require:
      - cmd: apache
    - watch:
      - cmd: apache
      - cmd: phpredis
      - file: /etc/apache2/sites-available/default
      - file: /etc/apache2/httpd.conf
      - file: /etc/php5/apache2/php.ini

/etc/apache2/httpd.conf:
  file.managed:
    - source: salt://apache/httpd.conf
    - user: root
    - group: root
    - mode: 644

/etc/apache2/sites-available/default:
  file.managed:
    - source: salt://apache/vhosts.conf
    - user: root
    - group: root
    - mode: 644

/etc/php5/apache2/php.ini:
  file.managed:
    - require:
      - pkg: php5-pkgs
    - source: salt://apache/php.ini
    - user: root
    - group: root
    - mode: 644

#
#
#
#
#
#
#
#
#
# PHP installation
#
php5-pkgs:
  pkg:
    - installed
    - names:
      - php5
      - php5-mysql
      - php5-curl
      - php5-cli
      - php5-dev
      - php5-gd

prefill-database:
  cmd:
    - run
    - name: php /vagrant/scripts/load.php --withdata
    - require:
      - pkg: php5-pkgs
      - service: mysql
      - mysql_grants.present: dbconfig
#
#
#
#
#
#
#
# MySQL installation
#
mysql-server:
  pkg:
    - installed
  service:
    - running
    - name: mysql
    - require:
      - pkg: mysql-server

python-mysqldb:
  pkg:
    - installed
    - require:
      - service: mysql

dbconfig:
  mysql_user.present:
    - name: blog
    - password: blog
    - require:
      - service: mysql
      - pkg: python-mysqldb
  mysql_database.present:
    - name: blog
    - require:
      - mysql_user.present: dbconfig
  mysql_grants.present:
    - grant: all privileges
    - database: blog.*
    - user: blog
    - require:
      - mysql_database.present: dbconfig


#
#
#
#
#
#
# NodeJS installation
#
nodejs:
  cmd.script:
    - source: salt://install/nodejs.sh
    - unless: "command -v forever | grep forever"
    - stateful: true

#
#
#
#
#
#
# PHPRedis installation
#
phpredis:
  cmd.script:
    - source: salt://install/phpredis.sh
    - unless: 'php -r "echo class_exists(\"Redis\", false) ? \"OK\" : null;" | grep OK'
    - stateful: true
    - require:
      - pkg: unzip
      - pkg: php5-pkgs

sydney-timezone:
  cmd:
    - run
    - name: ln -sf /usr/share/zoneinfo/Australia/Sydney /etc/localtime