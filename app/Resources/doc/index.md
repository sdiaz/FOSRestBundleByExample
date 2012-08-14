# Documentation #

This example project is based on Symfony Standard distribution. The project aims to show how to build powerful restful apis mainly with :

*  [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle) : This Bundle provides various tools to rapidly develop RESTful API's with Symfony2.
*  [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) : Provides user management for your Symfony2 Project. Compatible with Doctrine ORM & ODM, and Propel.
*  [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) : Generates documentation for your REST API from annotations.
*  [MopaWSSEAuthenticationBundle](https://github.com/phiamo/MopaWSSEAuthenticationBundle) : Symfony2 bundle to implement WSSE authentication.

# Installation #

Just clone the project and execute:

    curl -s http://getcomposer.org/installer | php composer.phar install --prefer-source

We use *--prefer-source* to use git instead of zipballs. After that we will setup the project by executing the following script:

    app/Resources/bin/validate.sh

Remember to set the database password properly in *parameters.yml*.

Now you will able to list the resources at http://localhost/app_dev.php

# Checking the Restful API #

Nelmio API Doc provides you with a *sandbox* that you can use to check the controllers. We recommend to use Chrome and the [Dev HTTP Client extension](https://chrome.google.com/webstore/detail/aejoelaoggembcahagimdiliamlcdmfm) instead, as it let you manage different calls and bookmark them in the extension.

As an example, the following screenshot show the interface while requesting a WSSE token with username and password parameters:

![](https://dl.dropbox.com/u/3972728/github/devhttpclient01.png)

As headers you will need to use:

    ACCEPT            : application/json
    HTTP_Content-Type : application/x-www-form-urlencoded

As parameters the default ones we preconfigured in validate.sh script:

    _username : admin
    _password : admin

Just copy the content of WSSE response to use later for authentication purposes as showed:

![](https://dl.dropbox.com/u/3972728/github/devhttpclient02.png)

As headers you will need to use for rest calls (update the X-WSSE by the one you got above):

    Authorization    : WSSE profile="UsernameToken"
    X-wsse           : UsernameToken Username="admin", PasswordDigest="uG4/uZRfXD424+Oi9Q67DH/rrzc=", Nonce="M2Y4ZDY1MWNkYWU5ODdmMw==", Created="2012-07-17T12:53:58+02:00"
    ACCEPT           : application/json

# Testing #

Just run phpunit from console:

    app/Resources/bin/validate.sh
    phpunit -c app

# Useful docs #

[Installing ant dependencies](ant_deps.md)