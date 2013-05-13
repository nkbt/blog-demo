php5-pkgs:
  pkg.installed:
    - names:
      - php5
      - php5-mysql
      - php5-curl
      - php5-cli
      - php5-cgi
      - php5-dev
      - php-pear
      - php5-gd

apache2:
  pkg:
    - installed

libapache2-mod-php5:
  pkg:
    - installed
    - require:
      - pkg: apache2
    - require_in:
      - reload-apache
      - set-php5-config

set-apache-vhost-config:
  file.symlink:
    - name: /etc/apache2/sites-available/default
    - target: /vagrant/srv/setup/vhosts.conf
    - force: true
    - require_in:
      - reload-apache
      - set-php5-config
    - require:
      - pkg: apache2

set-apache-config:
  file.symlink:
    - name: /etc/apache2/httpd.conf
    - target: /vagrant/srv/setup/httpd.conf
    - force: true
    - require_in:
      - reload-apache
      - set-php5-config
    - require:
      - pkg: apache2

set-php5-config:
  file.symlink:
    - name: /etc/php5/apache2/php.ini
    - target: /vagrant/srv/setup/php.ini
    - force: true
    - require_in:
      - reload-apache
    - require:
      - pkg: apache2

mysql-server:
  pkg.installed

mysql:
  service.running:
    - name: mysql
    - require:
      - pkg: mysql-server

python-mysqldb:
  pkg.installed

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
      - mysql_database.present : dbconfig

git:
  pkg.installed

nodejs:
  pkg.installed

npm:
  pkg.installed

build-essential:
  pkg.installed

redis-server:
  pkg.installed

unzip:
  pkg.installed

phpredis:
  cmd.run:
    - name: /vagrant/srv/setup/phpredis.sh
    - unless: "grep redis /etc/php5/conf.d/redis.ini"
    - cwd: /tmp
    - require_in:
      - reload-apache
    - require:
      - pkg: unzip
      - pkg: git

apache-enable-rewrites:
  cmd.run:
    - name: a2enmod rewrite
    - unless: "ls /etc/apache2/mods-enabled/ | grep rewrite"
    - require_in:
      - reload-apache
    - require:
      - pkg: apache2

reload-apache:
  cmd.run:
    - name: service apache2 restart
    - require_in:
      - prefill-database

prefill-database:
  cmd.run:
    - name: php /vagrant/scripts/load.php --withdata

latest-node:
  cmd.run:
    - name: /vagrant/srv/setup/node.sh
    - unless: "ls /etc/apt/sources.list.d | grep chris-lea"