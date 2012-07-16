FOSRestBundleByExample
======================

FOSRestBundle example project with Symfony 2.1 standard distribution.

**Note:** Work In Progress

**Caution:** This example project is developed in sync with [symfony's repository](https://github.com/symfony/symfony).
There is currently no release for Symfony 2.0.x

[![Build Status](https://secure.travis-ci.org/sdiaz/FOSRestBundleByExample.png?branch=master)](http://travis-ci.org/sdiaz/FOSRestBundleByExample)

Important
---------

Since Symfony Beta3, the security component is not working well with wsse. You can login but the token is not properly verified at server side, so you will get a *401 WSSE authentication failed* each time you try to access a resource. You can just point to another path to secure in security.yml while fixing this.

Documentation
-------------

The bulk of the documentation will be stored in the `Resources/doc/index.md`
file in this bundle:

[Read the Documentation for master](FOSRestBundleByExample/blob/master/src/ByExample/DemoBundle/Resources/doc/index.md)

After installation you will find in http://localhost/app_dev.php the Nelmio Api Doc with the resources available.

![API Example](https://dl.dropbox.com/u/3972728/github/apiexample.png)

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE