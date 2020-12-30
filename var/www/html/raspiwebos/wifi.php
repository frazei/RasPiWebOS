<?php

/* NOTA BENE
root@pitunes:/etc/sudoers.d# cat 020_apache-nopasswd
www-data ALL=(ALL) NOPASSWD: /sbin/wpa_cli
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//if(!in_array($_SERVER['HTTP_HOST'], array('127.0.0.1','localhost'))) die('Not authorized');

switch ($_REQUEST['action']) {
    case 'status':
        exec("iwconfig wlan0", $output);

        foreach ($output as $ln => $line) {
            $line = trim($line);
            //echo "[$ln] $line";
            if ($ln == 0) {
                $essid = substr($line, strpos($line, 'ESSID')+6);
            }
            if ($ln == 1) {
                $mac = substr($line, strpos($line, 'Access Point')+14);
            }
        }

        exec("ifconfig wlan0 | grep 'inet' | cut -d: -f2 | awk '{print $2}'", $ip);

        $results = Array(
            'status' => $output,
            'essid' => $essid,
            'mac' => $mac,
            'ip' => $ip
        );

        break;
    case 'scan':
        $scan = exec("sudo wpa_cli scan");

        if($scan == "OK") {
            exec("sudo wpa_cli scan_results", $output);

            $results = Array();
            $mac = null;
            foreach ($output as $ln => $line) {
                $line = trim($line);
                //echo "[$ln] $line<br>";

                if($ln <= 1) {
                    continue;
                } else {
                    $result['bssid'] = substr($line, 0, 17);
                    $result['frequency'] = substr($line, 18, 4);
                    $result['signal_level'] = substr($line, 23, 3);
                    $first_bracket = strpos($line, '[');
                    $last_bracket = strrpos($line, ']');
                    $result['flags'] = substr($line, $first_bracket, ($last_bracket-$first_bracket+1));
                    $result['open'] = strpos($result['flags'], 'WPA') ? false : true;
                    $result['ssid'] = substr($line, $last_bracket+2);
                }

                $results[] = $result;
            }
        } else {
            die('errore permessi');
        }

        //echo "<hr/>";
        //echo "<pre>".print_r($results,1)."</pre>";
        break;
    case 'connect':
        if(empty($_REQUEST['ssid'])) return;
        $exec = exec("sudo wpa_cli set_network 0 ssid '\"{$_REQUEST['ssid']}\"'");
        if($exec == 'OK') {
            if(!empty($_REQUEST['psk'])) {
                $exec = exec("sudo wpa_cli set_network 0 psk '\"{$_REQUEST['psk']}\"'");
            } else {
                $exec = exec("sudo wpa_cli set_network 0 key_mgmt NONE");
            }
            if($exec == 'OK') {
                $exec = exec("sudo wpa_cli enable_network 0");
                $results = array('result' => $exec);

                // $reconfigure = exec("sudo wpa_cli -i wlan0 reconfigure");
            } else {
                die('psk error');
            }
        } else {
            die('ssid error');
        }
        break;
    default:
        die('Error');
        break;
}

echo json_encode($results);
