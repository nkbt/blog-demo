Blog demo
====

Simple example of event-based models using PHP/Zend Framework 1.12, Redis and NodeJS.

Installation
====

1. Install [Vagrant](http://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/)
2. Add Ubuntu 13.04 i386 box:

        vagrant box add raring32 http://cloud-images.ubuntu.com/vagrant/raring/current/raring-server-cloudimg-i386-vagrant-disk1.box

3. Add salt plugin:

        vagrant plugin install vagrant-salt

4. Checkout project:

        git clone git://github.com/nkbt/blog-demo.git blog-demo

5. Run vagrant:

        cd blog-demo
        vagrant up

6. Go to [http://localhost:10080](http://localhost:10080)


TODO
====

- [x] Get working LAMP stack
- [x] Base PHP core with sample data, running site
- [x] Redis and PHPRedis
- [x] NodeJS 0.10 or newer
- [x] NPM
- [x] Forever
- [ ] Running Node app
