#!/bin/bash

cp app/config/parameters.yml.dist app/config/parameters.yml

bin/console doctrine:database:drop --force
bin/console doctrine:database:create
bin/console doctrine:schema:create
bin/console doctrine:schema:update
bin/console fos:user:create admin admin@sample.com password
bin/console fos:user:activate admin
bin/console fos:user:promote admin ROLE_API
bin/console cache:clear
