Blog demo
====

Simple example of event-based models using PHP/Zend Framework 1.12, Redis and NodeJS.

Installation
====

1. Install [Vagrant](http://www.vagrantup.com/) and [VirtualBox](https://www.virtualbox.org/)
2. Add Precise64 box:

        vagrant box add precise64 http://files.vagrantup.com/precise64.box
    
3. Add salt plugin:

        vagrant plugin install vagrant-salt
    
4. Checkout project:
    
        git clone git://github.com/nkbt/blog-demo.git blog-demo

5. Run vagrant:

        cd blog-demo
        vagrant up
    
6. Go to [http://localhost:10080](http://localhost:10080)
