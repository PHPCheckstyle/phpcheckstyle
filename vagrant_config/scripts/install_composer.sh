#!/usr/bin/env bash

# ---------------------------------------------------------------
# This provision is executed as "root"
# ---------------------------------------------------------------


echo "--------------------------------------------------" 
echo " Install PHP "
echo "--------------------------------------------------"

# Add a repository for PHP 7
echo "deb http://packages.dotdeb.org jessie all" > /etc/apt/sources.list.d/dotdeb.list
wget https://www.dotdeb.org/dotdeb.gpg && apt-key add dotdeb.gpg
apt-get update -y
apt-get install -y php7.0 php7.0-cli php7.0-xdebug php7.0-xml 

apt-get install -y git zip


echo "--------------------------------------------------" 
echo " Install Composer "
echo "--------------------------------------------------"

cd /vagrant/

wget -O composer-setup.php https://getcomposer.org/installer
php composer-setup.php
rm composer-setup.php

sudo mv composer.phar /usr/local/bin/composer



# Set the default directory to /vagrant
echo "
cd /vagrant/
" >> /home/vagrant/.profile