#!/usr/bin/env bash

# Debian supervisor config folder
CONFIG_DIRECTORY="/etc/supervisor/conf.d"

if [ -d $CONFIG_DIRECTORY ]
then
    "ln -s script/supervisor/inowas.rabbit.flopy.runner.conf" $CONFIG_DIRECTORY
fi
