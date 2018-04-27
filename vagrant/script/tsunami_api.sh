#!/bin/bash
#defaults
echo "==> TSUNAMI DEPLOY ==> starting"
echo "==> TSUNAMI DEPLOY ==> defaults"
if [ -z "${GITHUB_USER}" ]; 
then 
    GITHUBU="tsunami-deploy"
else
    GITHUBU=${GITHUB_USER}
fi

if [ -z "${GITHUB_PASSWORD}" ]; 
then 
    GITHUBP="wiaf%iek5Vee9eim1aef}ekiexooy"
else
    GITHUBP=${GITHUB_PASSWORD}
fi

if [ -z "${MYSQL_PASSWORD}" ]; 
then 
    MYSQLP="12345"
else
    MYSQLP=${MYSQL_PASSWORD}
fi

#1. Git clone the sources
#1.1. Disable Apache
echo "==> TSUNAMI DEPLOY ==> stopping Apache"
service apache2 stop
#1.2. Handle the document root files
echo "==> TSUNAMI DEPLOY ==> removing document root"
rm -r /var/www
echo "==> TSUNAMI DEPLOY ==> cloning the Tsunami repo"
git clone https://$GITHUBU:$GITHUBP@github.com/bugexorcist/tzunapi.git -b experimental /var/www
echo "==> TSUNAMI DEPLOY ==> GIT cleanup"
#rm -r /var/www/.git
#rm -r /var/www/.gitignore
rm -r /var/www/nbproject
#1.3. Composer install
echo "==> TSUNAMI DEPLOY ==> composer install"
/var/www/composer.phar self-update
/var/www/composer.phar install -d /var/www
#1.4. Fix the document root for default vhost configs
echo "==> TSUNAMI DEPLOY ==> updating vhosts"
line="\/var\/www"
rep="\/var\/www\/webroot"
if [ -f /etc/apache2/sites-available/default ]
then
    sed -i.bak "s/${line}/${rep}/g" /etc/apache2/sites-available/default
fi
if [ -f /etc/apache2/sites-available/default-ssl ]
then
    sed -i.bak "s/${line}/${rep}/g" /etc/apache2/sites-available/default-ssl
fi
if [ -f /etc/apache2/sites-available/000-default.conf ]
then
    line="\/var\/www\/html"
    sed -i.bak "s/${line}/${rep}/g" /etc/apache2/sites-available/000-default.conf
fi
if [ -f /etc/apache2/sites-available/default-ssl.conf ]
then
    line="\/var\/www\/html"
    sed -i.bak "s/${line}/${rep}/g" /etc/apache2/sites-available/default-ssl.conf
fi
#1.5. Enable Apache
echo "==> TSUNAMI DEPLOY ==> starting Apache"
service apache2 start
#2. Create configs
#2.1. Create config.php (copy config.example.php)
echo "==> TSUNAMI DEPLOY ==> configs"
cp /var/www/init/config.example /var/www/init/config.php
line="'development'"
rep="'production'"
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
#2.2. Create client.config.php (copy client.config.example.php)
cp /var/www/init/client.config.example /var/www/init/client.config.php

#3. Create database
echo "==> TSUNAMI DEPLOY ==> MySQL database"
mysql -uroot -p$MYSQLP -e "DROP DATABASE IF EXISTS tsunami;";
mysql -uroot -p$MYSQLP -e "CREATE DATABASE tsunami;";
echo "==> TSUNAMI DEPLOY ==> MySQL database - carriers"
mysql tsunami</var/www/application/sql/current_schema/carriers.sql -uroot -p$MYSQLP;
echo "==> TSUNAMI DEPLOY ==> MySQL database - carriers_vendors"
mysql tsunami</var/www/application/sql/current_schema/carriers_vendors.sql -uroot -p$MYSQLP;
echo "==> TSUNAMI DEPLOY ==> MySQL database - devices"
mysql tsunami</var/www/application/sql/current_schema/devices.sql -uroot -p$MYSQLP;
echo "==> TSUNAMI DEPLOY ==> MySQL database - queries"
mysql tsunami</var/www/application/sql/current_schema/queries.sql -uroot -p$MYSQLP;
echo "==> TSUNAMI DEPLOY ==> MySQL database - sub_queries"
mysql tsunami</var/www/application/sql/current_schema/sub_queries.sql -uroot -p$MYSQLP;
echo "==> TSUNAMI DEPLOY ==> MySQL database - users_api"
mysql tsunami</var/www/application/sql/current_schema/users_api.sql -uroot -p$MYSQLP;
echo "==> TSUNAMI DEPLOY ==> MySQL database - vendors"
mysql tsunami</var/www/application/sql/current_schema/vendors.sql -uroot -p$MYSQLP;

#4. Create MySQL user
#4.1. Create random user name
TSUNAMI_MYSQLU=$(printf '%q' $(pwgen 10))
#4.2. Create random pass
TSUNAMI_MYSQLP=$(printf '%q' $(pwgen 25))
echo "==> TSUNAMI DEPLOY ==> MySQL user"
mysql -uroot -p$MYSQLP -e "CREATE USER '$TSUNAMI_MYSQLU'@'localhost' IDENTIFIED BY '$TSUNAMI_MYSQLP';"
#4.3. Add user
echo "==> TSUNAMI DEPLOY ==> MySQL privileges"
mysql -uroot -p$MYSQLP -e "GRANT ALL PRIVILEGES ON tsunami.* TO '$TSUNAMI_MYSQLU'@'localhost';"
#4.4. Update config.php
echo "==> TSUNAMI DEPLOY ==> database config"
echo "==> TSUNAMI DEPLOY ==> database config ==> name"
line="\[NAME\_OF\_YOUR\_DATABASE\]"
rep='tsunami'
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
echo "==> TSUNAMI DEPLOY ==> database config ==> username"
line="\[NAME\_OF\_YOUR\_DATABASE\_USER]"
rep=$TSUNAMI_MYSQLU
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
echo "==> TSUNAMI DEPLOY ==> database config ==> password"
line="\[DATABASE\_USER\_PASSWORD]"
rep=$TSUNAMI_MYSQLP
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
echo "==> TSUNAMI DEPLOY ==> database config ==> host"
line="\[NAME\_OR\_IP\_OF\_YOUR\_DATABASE\_HOST]"
rep='localhost'
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
mkdir /var/www/logs;chown www-data:www-data /var/www/logs;chmod 0777 /var/www/logs

#5 Modify client keys
echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials"
echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials ==> host"
line="\[TSUNAMI\_API\_PROTOCOL\]"
rep=$TSUNAMI_API_PROTOCOL
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials ==> public key"
line="\[TSUNAMI\_PUBLIC\_KEY\]"
rep="${TSUNAMI_VENDOR_PUBLIC_KEY//\//\\/}"
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials ==> private key"
line="\[TSUNAMI\_PRIVATE\_KEY\]"
rep="${TSUNAMI_VENDOR_PRIVATE_KEY//\//\\/}"
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> AWS ==> access key"
line="\[AWS\_ACCESS\_KEY\]"
rep="${AWS_ACCESS_KEY//\//\\/}"
sed -i "s/${line}/${rep}/g" /var/www/init/config.php

echo "==> TSUNAMI DEPLOY ==> AWS ==> secret key"
line="\[AWS\_SECRET\_KEY\]"
rep="${AWS_SECRET_KEY//\//\\/}"
sed -i "s/${line}/${rep}/g" /var/www/init/config.php

#6. setup vendor crons
#6.1. jobs process - every 20 sec
echo "==> TSUNAMI DEPLOY ==> crontab"
crontab -l > api_crontab_list
echo "* * * * * /var/www/cli.php cron/check_unassigned_sub_queries">>api_crontab_list
echo "* * * * * /var/www/cli.php cron/check_old_sub_queries">>api_crontab_list
echo "* * * * * sleep 30;/var/www/cli.php cron/check_old_sub_queries">>api_crontab_list
echo "* * * * * /var/www/cli.php cron/process_gsma_queries">>api_crontab_list
echo "* * * * * sleep 20;/var/www/cli.php cron/process_gsma_queries">>api_crontab_list
echo "* * * * * sleep 40;/var/www/cli.php cron/process_gsma_queries">>api_crontab_list
crontab api_crontab_list
rm api_crontab_list

#7. Add a new user to the database
echo "==> TSUNAMI DEPLOY ==> Default Tsunami control user"
/var/www/cli.php maintenance/add_user "username=$TSUNAMI_DEFAULT_USER&user_public_key=$TSUNAMI_DEFAULT_USER_PUBLIC_KEY&user_private_key=$TSUNAMI_DEFAULT_USER_PRIVATE_KEY&generate_password=1"

#8. Vagrant routines
echo "==> TSUNAMI DEPLOY ==> Installing Vagrant"
wget https://dl.bintray.com/mitchellh/vagrant/vagrant_1.7.4_x86_64.deb
sudo dpkg -i vagrant_1.7.4_x86_64.deb
vagrant plugin install vagrant-aws
vagrant box add dummy https://github.com/mitchellh/vagrant-aws/raw/master/dummy.box
sudo chmod 0777 /var/www/vagrant/
sudo chmod 0777 /var/www/vagrant/vagrant_template_aws
sudo chmod 0644 /var/www/vagrant/vagrant_template_aws/key.pem

#9. RabbitMQ setup
echo "==> TSUNAMI DEPLOY ==> Installing Rabbit MQ"
sudo wget http://www.rabbitmq.com/rabbitmq-signing-key-public.asc
sudo apt-key add rabbitmq-signing-key-public.asc
sudo apt-get update
sudo apt-get install -q -y screen htop vim curl wget
sudo apt-get install -q -y rabbitmq-server
# RabbitMQ Plugins
sudo service rabbitmq-server stop
sudo rabbitmq-plugins enable rabbitmq_management
sudo rabbitmq-plugins enable rabbitmq_jsonrpc
sudo service rabbitmq-server start
sudo rabbitmq-plugins list
rabbitmqctl change_password guest ${RABBITMQ_PASSWORD}
sudo service rabbitmq-server stop

sudo cp /var/www/vagrant/vagrant_template_aws/rabbitmq.config.example /var/www/vagrant/vagrant_template_aws/rabbitmq.config

#9.1. RabbitMQ config
#echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Host"
#line="\[TSUNAMI\_AMQP\_HOST]"
#rep=$RABBITMQ_HOST
#sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Port"
line="\[TSUNAMI\_AMQP\_PORT]"
rep=$RABBITMQ_COMMON_PORT
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

line="\[COMMON\_PORT]"
rep=$RABBITMQ_COMMON_PORT
sed -i "s/${line}/${rep}/g" /var/www/vagrant/vagrant_template_aws/rabbitmq.config

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> User"
line="\[TSUNAMI\_AMQP\_USER]"
rep=$RABBITMQ_USER
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Password"
line="\[TSUNAMI\_AMQP\_PASSWORD]"
rep=$RABBITMQ_PASSWORD
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

line="\[PASSWORD]"
rep=$RABBITMQ_PASSWORD
sed -i "s/${line}/${rep}/g" /var/www/vagrant/vagrant_template_aws/rabbitmq.config

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Exchange"
line="\[TSUNAMI\_AMQP\_EXCHANGE]"
rep=$RABBITMQ_EXCHANGE
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Control Port Config"
line="\[CONTROL\_PORT]"
rep=$RABBITMQ_CONTROL_PORT
sed -i "s/${line}/${rep}/g" /var/www/vagrant/vagrant_template_aws/rabbitmq.config

sudo cp /var/www/vagrant/vagrant_template_aws/rabbitmq.config /etc/rabbitmq/
sudo service rabbitmq-server start
echo "==> TSUNAMI DEPLOY ==> Open Ports for Rabbit MQ: common"
sudo iptables -I INPUT 1 -p tcp --dport $RABBITMQ_COMMON_PORT -j ACCEPT
echo "==> TSUNAMI DEPLOY ==> Open Ports for Rabbit MQ: 5673(???)"
sudo iptables -I INPUT 1 -p tcp --dport 5673 -j ACCEPT
echo "==> TSUNAMI DEPLOY ==> Open Ports for Rabbit MQ: control"
sudo iptables -I INPUT 1 -p tcp --dport $RABBITMQ_CONTROL_PORT -j ACCEPT
