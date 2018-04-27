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
mysql tsunami</var/www/application/sql/current_schema/carriers.sql -uroot -p$MYSQLP;
mysql tsunami</var/www/application/sql/current_schema/jobs.sql -uroot -p$MYSQLP;
mysql tsunami</var/www/application/sql/current_schema/users_vendor.sql -uroot -p$MYSQLP;

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
line="\[NAME\_OF\_YOUR\_DATABASE\]"
rep=tsunami
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
line="\[NAME\_OF\_YOUR\_DATABASE\_USER]"
rep=$TSUNAMI_MYSQLU
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
line="\[DATABASE\_USER\_PASSWORD]"
rep=$TSUNAMI_MYSQLP
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
line="\[NAME\_OR\_IP\_OF\_YOUR\_DATABASE\_HOST]"
rep=localhost
sed -i "s/${line}/${rep}/g" /var/www/init/config.php
mkdir /var/www/logs;chown www-data:www-data /var/www/logs;chmod 0777 /var/www/logs

#5. setup vendor crons
#5.1. jobs process - every 20 sec
crontab -l > every20sec_cron
echo "* * * * * /var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 2;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 5;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 7;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 10;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 12;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 15;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 17;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 20;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 22;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 25;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 27;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 30;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 32;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 35;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 37;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 40;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 42;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 45;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 47;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 50;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 52;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 55;/var/www/cli.php jobs/process">>every20sec_cron
echo "* * * * * sleep 57;/var/www/cli.php jobs/process">>every20sec_cron
crontab every20sec_cron
rm every20sec_cron

#6. AMQP (RabbitMQ) access credentials
echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Port"
line="\[TSUNAMI\_AMQP\_PORT]"
rep=$RABBITMQ_COMMON_PORT
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> User"
line="\[TSUNAMI\_AMQP\_USER]"
rep=$RABBITMQ_USER
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Password"
line="\[TSUNAMI\_AMQP\_PASSWORD]"
rep=$RABBITMQ_PASSWORD
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php

echo "==> TSUNAMI DEPLOY ==> Configuring RabbitMQ ==> Exchange"
line="\[TSUNAMI\_AMQP\_EXCHANGE]"
rep=$RABBITMQ_EXCHANGE
sed -i "s/${line}/${rep}/g" /var/www/init/client.config.php