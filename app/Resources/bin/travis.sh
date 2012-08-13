#!/bin/bash

cp app/config/parameters.yml.dist app/config/parameters.yml

app/console doctrine:database:drop --force
app/console doctrine:database:create
app/console doctrine:schema:create
app/console doctrine:schema:update
app/console fos:user:create admin admin@qa.int admin
app/console fos:user:activate admin
app/console fos:user:promote admin ROLE_DEVELOPER
app/console cache:clear
