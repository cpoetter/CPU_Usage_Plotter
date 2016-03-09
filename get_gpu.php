<?php
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
    include('Net/SSH2.php');
    
    $server_list = array("<<AddressServer1>>", "<<AddressServer2>>", "<<AddressServer3>>", "<<AddressServer4>>");

    $utilization = array_fill(0, count($server_list), 0);
    $users = array_fill(0, count($server_list), '');
    $processes = array_fill(0, count($server_list), '');

    $counter = 0;
    foreach ($server_list as $server_name) {
        $client = new Net_SSH2($server_name, 22);
        if (!$client->login("<<Username>>", "<<Password>>")) {
            exit('Login Failed');
        }

        $gpu_usage = trim($client->exec("nvidia-smi -q -g 0 2>&1 | grep -A 2 -i utilization | grep -i memory | tail -1 | awk '{print $3}' | sed s/\%//g"));
        
        if($gpu_usage == "N/A") {
            $gpu_usage = 0;
        }
        
        $utilization[$counter] = $gpu_usage;

        $client->disconnect();
        $counter++;
    }

    echo json_encode(array("names" => json_encode($server_list), "data" => json_encode($utilization)));
?>      
