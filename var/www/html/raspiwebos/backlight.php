<?php

/* NOTA BENE
 * create the file /etc/udev/rules.d/backlight-permissions.rules and add this line:
 * SUBSYSTEM=="backlight",RUN+="/bin/chmod 666 /sys/class/backlight/%k/brightness /sys/class/backlight/%k/bl_power"
*/

//die(print_r($_REQUEST,1));

if(!in_array($_SERVER['HTTP_HOST'], array('127.0.0.1','localhost'))) die('Not authorized');

switch ($_REQUEST['action']) {
    case 'get':
        $return['brightness'] = exec('cat /sys/class/backlight/rpi_backlight/actual_brightness');
        $return['backlight'] = exec( 'cat /sys/class/backlight/rpi_backlight/bl_power');
        break;
    case 'set':
        if($_REQUEST['brightness']) {
            $return['brightness'] = exec('echo '.$_REQUEST['brightness'].' > /sys/class/backlight/rpi_backlight/brightness');
        }

        if(isset($_REQUEST['backlight'])) {
            $return['backlight'] = exec('echo '.$_REQUEST['backlight'].' > /sys/class/backlight/rpi_backlight/bl_power');
        }
        break;
    default;
        die('Error');
        break;
}

echo json_encode($return);