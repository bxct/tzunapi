#!/bin/bash -eux
# Install the PuppetLabs repo
echo "Configuring PuppetLabs repo..."
sudo repo_deb_path=$(mktemp)
sudo wget --output-document=${repo_deb_path} ${REPO_DEB_URL} 2>/dev/null
sudo dpkg -i ${repo_deb_path} >/dev/null
sudo apt-get update >/dev/null

# Install Puppet
echo "==> LAMP ==> Installing Puppet..."
apt-get install -y puppet >/dev/null
echo "==> LAMP ==> Puppet installed!"

echo "==> LAMP ==> Installing MC"
sudo apt-get install -y mc

echo "==> LAMP ==> Installing LAMP..."
echo "==> LAMP ==> Installing Apache and PHP..."
apt-get -y install apache2
apt-get -y install php5 libapache2-mod-php5 php5-curl

echo "==> LAMP ==> Installing MySQL..."
echo mysql-server-5.1 mysql-server/root_password password ${MYSQL_PASSWORD} | debconf-set-selections
echo mysql-server-5.1 mysql-server/root_password_again password ${MYSQL_PASSWORD} | debconf-set-selections
apt-get install -y mysql-server
apt-get install -y php5-mysql

echo "==> LAMP ==> Enabling mod_rewrite"
a2enmod rewrite

echo "==> LAMP ==> Setting up APC..."
sudo apt-get install php-apc -y

echo "==> LAMP ==> Restarting Apache..."
service apache2 restart

echo "==> LAMP ==> Locale settings..."
export LANGUAGE=en_US.UTF-8
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
locale-gen en_US.UTF-8
sudo dpkg-reconfigure locales

echo "==> LAMP ==> GIT and password utility..."
apt-get install -y git pwgen