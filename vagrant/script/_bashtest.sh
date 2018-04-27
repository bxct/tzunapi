#!/bin/bash

# ========> DEFAULTS
MYSQLP=bob4ever
TSUNAMI_DEFAULT_USER=$(printf '%q' $(pwgen 10))
TSUNAMI_DEFAULT_USER_PUBLIC_KEY='KXoeYsx5$t/XnTEd/ybBCLKCd/zw8k/'
TSUNAMI_DEFAULT_USER_PRIVATE_KEY='cGYtBy/V$xRuMbuM5mzJnnrmIHLqfM1'
TSUNAMI_API_PROTOCOL='http'
TSUNAMI_VENDOR_PUBLIC_KEY='KXoeYsx5$t/XnTEd/ybBCLKCd/zw8k/'
TSUNAMI_VENDOR_PRIVATE_KEY='cGYtBy/V$xRuMbuM5mzJnnrmIHLqfM1'
AWS_ACCESS_KEY='asdasdasdasd'
AWS_SECRET_KEY='qqweqweqwe'


# ========> APC installation test
#sudo apt-get install php-apc -y
#sudo service apache2 restart


# ========> MySQL schema tests
#echo "==> TSUNAMI DEPLOY ==> MySQL database"
#mysql -uroot -p$MYSQLP -e "DROP DATABASE IF EXISTS tsunami_test;";
#mysql -uroot -p$MYSQLP -e "CREATE DATABASE tsunami_test;";
#echo "==> TSUNAMI DEPLOY ==> MySQL database - carriers"
#mysql tsunami_test</var/www/application/sql/current_schema/carriers.sql -uroot -p$MYSQLP;
#echo "==> TSUNAMI DEPLOY ==> MySQL database - carriers_vendors"
#mysql tsunami_test</var/www/application/sql/current_schema/carriers_vendors.sql -uroot -p$MYSQLP;
#echo "==> TSUNAMI DEPLOY ==> MySQL database - devices"
#mysql tsunami_test</var/www/application/sql/current_schema/devices.sql -uroot -p$MYSQLP;
#echo "==> TSUNAMI DEPLOY ==> MySQL database - queries"
#mysql tsunami_test</var/www/application/sql/current_schema/queries.sql -uroot -p$MYSQLP;
#echo "==> TSUNAMI DEPLOY ==> MySQL database - sub_queries"
#mysql tsunami_test</var/www/application/sql/current_schema/sub_queries.sql -uroot -p$MYSQLP;
#echo "==> TSUNAMI DEPLOY ==> MySQL database - users_api"
#mysql tsunami_test</var/www/application/sql/current_schema/users_api.sql -uroot -p$MYSQLP;
#echo "==> TSUNAMI DEPLOY ==> MySQL database - vendors"
#mysql tsunami_test</var/www/application/sql/current_schema/vendors.sql -uroot -p$MYSQLP;


# ========> MySQL user test
#TSUNAMI_MYSQLU=$(printf '%q' $(pwgen 10))
##4.2. Create random pass
#TSUNAMI_MYSQLP=$(printf '%q' $(pwgen 25))
#echo "==> TSUNAMI DEPLOY ==> MySQL user"
#mysql -uroot -p$MYSQLP -e "CREATE USER '$TSUNAMI_MYSQLU'@'localhost' IDENTIFIED BY '$TSUNAMI_MYSQLP';"
##4.3. Add user
#echo "==> TSUNAMI DEPLOY ==> MySQL privileges"
#mysql -uroot -p$MYSQLP -e "GRANT ALL PRIVILEGES ON tsunami.* TO '$TSUNAMI_MYSQLU'@'localhost';"


# ========> New user creation test
#echo $TSUNAMI_DEFAULT_USER
#echo $TSUNAMI_DEFAULT_USER_PUBLIC_KEY
#echo $TSUNAMI_DEFAULT_USER_PRIVATE_KEY
#echo "username=$TSUNAMI_DEFAULT_USER&user_public_key=$TSUNAMI_DEFAULT_USER_PUBLIC_KEY&user_private_key=$TSUNAMI_DEFAULT_USER_PRIVATE_KEY&generate_password=1"
#/var/www/cli.php maintenance/add_user "username=$TSUNAMI_DEFAULT_USER&user_public_key=$TSUNAMI_DEFAULT_USER_PUBLIC_KEY&user_private_key=$TSUNAMI_DEFAULT_USER_PRIVATE_KEY&generate_password=1"


# ========> sed with complex keys testing
#cp /var/www/init/client.config.example ./client.config.php
#echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials"
#echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials ==> host"
#line="\[TSUNAMI\_API\_PROTOCOL\]"
#rep=$TSUNAMI_API_PROTOCOL
#sed -i "s/${line}/${rep}/g" ./client.config.php
#
#echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials ==> public key"
#line="\[TSUNAMI\_PUBLIC\_KEY\]"
#rep="${TSUNAMI_VENDOR_PUBLIC_KEY//\//\\/}"
#sed -i "s/${line}/${rep}/g" ./client.config.php
#
#echo "==> TSUNAMI DEPLOY ==> Tsunami client credentials ==> private key"
#line="\[TSUNAMI\_PRIVATE\_KEY\]"
#rep="${TSUNAMI_VENDOR_PRIVATE_KEY//\//\\/}"
#sed -i "s/${line}/${rep}/g" ./client.config.php


# ========> sed the hostname
#cp /var/www/init/config.example ./config.php
#echo "==> TSUNAMI DEPLOY ==> Primary hostname"
#line="tsunami.dev"
#rep="api.tzunami.com"
#sed -i "s/${line}/${rep}/g" ./config.php


# ========> AWS
cp /var/www/init/config.example ./config.php
echo "==> TSUNAMI DEPLOY ==> AWS ==> access key"
line="\[AWS\_ACCESS\_KEY\]"
rep="${AWS_ACCESS_KEY//\//\\/}"
sed -i "s/${line}/${rep}/g" ./config.php

echo "==> TSUNAMI DEPLOY ==> AWS ==> secret key"
line="\[AWS\_SECRET\_KEY\]"
rep="${AWS_SECRET_KEY//\//\\/}"
sed -i "s/${line}/${rep}/g" ./config.php