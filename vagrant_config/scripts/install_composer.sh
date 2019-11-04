#!/usr/bin/env bash

# ---------------------------------------------------------------
# This provision is executed as "root"
# ---------------------------------------------------------------


echo "--------------------------------------------------" 
echo " Install PHP "
echo "--------------------------------------------------"

# Add a repository for PHP 7
apt-get install -y php php-cli php-xdebug php-xml 

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