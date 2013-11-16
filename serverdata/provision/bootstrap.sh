#!/usr/bin/env bash

function showText {
    echo ""
    echo ""
    echo ""
    echo "       ________________________________________________________________________"
    echo "      /   $1"
    echo "     /"
    echo "/___/"
    echo "\\"
    echo ""
    echo ""
}


# ensure fast booting with grub ;)

showText "Remount FS with ACL"
    mount -o remount,acl /dev/sda1

showText "Setup Grub"
    cp /etc/default/grub /etc/default/grub.orig
    sed -i -e 's/GRUB_TIMEOUT=\([0-9]\)\+/GRUB_TIMEOUT=0/' /etc/default/grub
    update-grub

showText "Setup Exim4"
    cp -rf /vagrant/serverdata/etc/exim4 /etc/
    update-exim4.conf
    /etc/init.d/exim4 restart

showText "Setup PHPMyAdmin"
    # debian install
    cp /etc/phpmyadmin/apache.conf /etc/apache2/sites-enabled/phpmyadmin
    cp /vagrant/serverdata/etc/phpmyadmin/config.inc.php /etc/phpmyadmin/config.inc.php

    if [ -f /etc/apache2/sites-enabled/latestPhpMyAdmin ]; then
        rm -rf /etc/apache2/sites-enabled/latestPhpMyAdmin
    fi

    service apache2 restart

showText "Setup composer"
    cp /serverdata/serverdata/tools/composer/composer.phar   /usr/local/bin/composer
    chmod +x /usr/local/bin/composer

# configure doxygen
if ! type "doxygen" > /dev/null; then
    showText "Setup doxygen ... can take some time, compiled from source ..."
        mkdir /tmp/doxygen
        cd /tmp/doxygen
        git clone https://github.com/doxygen/doxygen.git .
        ./configure
        make
        make install
        touch /installed-doxygen-1.8.4-1
else
    showText "Setup Doxygen ... is already compiled and installed"
        printf "you have installed %s already, lucky guy\n" $(doxygen --version)
        echo   "to force reinstall rm /installed-doxygen-*"
fi

showText "Create blank database 'flow'"
    mysql -uroot -e "CREATE DATABASE IF NOT EXISTS flow DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;"


if [ ! -d /project ]; then
    showText "Create project directory"
        mkdir /project
        cd    /project

    showText "Install basic flow with composer"
        composer create-project --dev --no-progress --keep-vcs typo3/flow-base-distribution . 2.0.0

    showText "Create symlink for Application"
        rm -rf /project/Packages/Application
        ln -s /serverdata/project/Packages/Application/  /project/Packages/Application

    showText "Install dependencies with composer"
        cd /project
        composer update --no-progress

    showText "Set Permissions"
        php flow core:setfilepermissions vagrant www-data www-data

fi

showText "Copy composer.json, Settings.yaml, etc."
    cp /serverdata/project/composer.json                           /project/composer.json
    cp /serverdata/project/Configuration/Settings.yaml             /project/Configuration/Settings.yaml
    cp /serverdata/project/Configuration/Routes.yaml               /project/Configuration/Routes.yaml
    cp /serverdata/project/Configuration/Development/Settings.yaml /project/Configuration/Development/Settings.yaml

showText "Update Dependencies"
    cd /project
    composer install --no-progress
    composer update  --no-progress

showText "Disable TYPO3.Welcome"
    /project/flow package:deactivate TYPO3.Welcome

showText "Perform database updates if needed"
    cd /project
    php flow doctrine:migrate

showText "Initialize Cache and refreeze packages :D"
    php flow package:freeze all
    php flow package:unfreeze --package-key YourVendor.YourPackage

    php flow package:refreeze
    php flow cache:warmup

showText "Doing Database imports / updates"
    #php flow import:fach

showText "Run Doxygen"
    if [ ! -d /vagrant/documentation/doxygen/ ]
      then
        mkdir /vagrant/documentation/doxygen/
    fi

    rm -rf /vagrant/documentation/doxygen/*
    cd /vagrant/serverdata/tools/doxygen/
    doxygen doxygen.conf

showText "Run SchemaSpy"
    if [ ! -d /vagrant/documentation/schemaspy/ ]
      then
        mkdir /vagrant/documentation/schemaspy/
    fi
    java -jar /vagrant/serverdata/tools/schemaspy/schemaSpy.jar -t mysql -db flow -host localhost -u root -o /vagrant/documentation/schemaspy/ -dp /usr/share/java/mysql-connector-java-5.1.10.jar > /dev/null


echo "======================================================================="
echo "  Access the vm in your Browser via:"
echo "      - 192.168.31.11    64bit 1GB Ram    (Vmware Fusion Provider)"
echo "      - 192.168.31.11    32bit 1GB Ram    (Virtualbox Provider)"
echo "======================================================================="
echo "  Important directories"
echo "      - /project                   FLOW"
echo "      - /vagrant/serverdata        mapped directories *"
echo "      - /serverdata                mapped directories www-data:www-data"
echo "======================================================================="
