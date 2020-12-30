#!/bin/bash

# Copyright (C) 2020 Francesco Zei <zei@communicationbox.it>

# Make sure only root can run our script
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

if [ ! $1 ]; then
	echo -e "Usage: wifi.sh status|scan|connect|recover [new ssid] [new psk]"
  exit 1
fi

case "$1" in

status) iwconfig wlan0
  ;;

scan) iwlist wlan0 scan
  ;;

connect) if [[ ! $2 || ! $3 ]]; then
    echo "Missing parameters"
    exit 1
  fi

  cp /etc/wpa_supplicant/wpa_supplicant.conf /etc/wpa_supplicant/wpa_supplicant.conf.bak
  if [ $3 == 'open' ]; then
    PSK="key_mgmt=NONE"
  else
    PSK="psk=\"${3}\""
  fi
  ifconfig wlan0 down
  cat > /etc/wpa_supplicant/wpa_supplicant.conf <<EOF
country=IT
ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
update_config=1
network={
	ssid="${2}"
	${PSK}
}
EOF
  ifconfig wlan0 up
  echo true

  #RES=$(wpa_cli -i wlan0 reconfigure)
  #if [ $RES == 'OK' ]; then
	#  echo true
  #else
  #  mv /etc/wpa_supplicant/wpa_supplicant.conf.bak /etc/wpa_supplicant/wpa_supplicant.conf
    #wpa_cli -i wlan0 reconfigure
	#  echo false
  #fi

  ;;

recover)
    mv /etc/wpa_supplicant/wpa_supplicant.conf.bak /etc/wpa_supplicant/wpa_supplicant.conf
    wpa_cli -i wlan0 reconfigure
  ;;

*)	echo "Invalid option"
exit 1
esac