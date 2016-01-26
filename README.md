Grouphub
=========

De business laag van de GroupHub applicatie: https://wiki.surfnet.nl/display/P3GFeI2015/2.+Business+Laag

# Host machine requirements

 - Virtualbox
 - Vagrant
 - Ansible
 - composer

## Vagrant plugins
Make sure you have the following vagrant plugins installed.

    vagrant-hostsupdater >=(0.0.11)
    vagrant-share >=(1.1.4, system)
    vagrant-vbguest >=(0.10.1)

# Installation
- Run `vagrant up` in order to get the vagrant machine running

```sh
<projectdir>$ vagrant ssh
<vagrantbox>$ cd /vagrant
<vagrantbox>$ curl -sS https://getcomposer.org/installer | php
<vagrantbox>$ sudo mv composer.phar /usr/local/bin/composer
<vagrantbox>$ composer install
```

# Getting started
After starting and provisioning your vagrant box you can go to:
<http://dev.grouphub.org/app_dev.php>

# Running synchronization scripts
```sh
<projectdir>$ vagrant ssh
<vagrantbox>$ cd /vagrant
<vagrantbox>$ php bin/console grouphub:sync -vvv
```

# Accessing VOOT url's
- <http://dev.grouphub.org/voot/user/[userId]/groups>
- <http://dev.grouphub.org/voot/user/[userId]/groups/[groupId]>
