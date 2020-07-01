#!/bin/bash

openvt -f -c 8 -s -w -- sh -c "clear;/usr/bin/fbi -d /dev/fb0 --noverbose -a -t 15 -1 /var/www/html/raspiwebos/images/splash.png"
chvt 7
