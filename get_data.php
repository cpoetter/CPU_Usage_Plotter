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

        $cpu_usage = trim($client->exec("echo $[100-$(vmstat 1 2|tail -1|awk '{print $15}')]"));
        $user = trim($client->exec("ps -eo pcpu,pid,user,args --no-headers| sort -t. -nk1,2 -k4,4 -r |head -n 1 | awk {'print $3'}"));
        $process = trim($client->exec("ps -eo pcpu,pid,user,args --no-headers| sort -t. -nk1,2 -k4,4 -r |head -n 1 | awk {'print $4'}"));
        
        // /user/local/.../MATLAB -> MATLAB
        $process_to_long = strrchr($process, "/");
        if($process_to_long !== FALSE) {
            $process = substr($process_to_long, 1);
        }
        
        // sshd: -> sshd
        $process_to_long = strpos($process, ":");
        if($process_to_long !== FALSE) {
            $process = substr($process, 0, $process_to_long);
        }
        
        $utilization[$counter] = $cpu_usage;
        $users[$counter] = $user;
        $processes[$counter] = $process;

        $client->disconnect();
        $counter++;
    }

    echo json_encode(array("names" => json_encode($server_list), "data" => json_encode($utilization), "user" => json_encode($users), "process" => json_encode($processes)));
?>      
