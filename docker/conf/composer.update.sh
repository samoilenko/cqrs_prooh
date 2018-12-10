#!/bin/bash

if [ -f /project/composer.phar ];
then
    cd /project && php composer.phar update
fi