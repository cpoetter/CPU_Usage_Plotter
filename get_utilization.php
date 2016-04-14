<?php
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);

    set_time_limit(0); // disable the time limit for this script

    $server = $_POST['server'];

    set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
    include('Net/SSH2.php');

    $client = new Net_SSH2($server, 22);
    if (!$client->login("cpoetter", "d8-j2K-Dz7")) {
        exit('Login Failed');
    }

    $client->setTimeout(3);

    $cpu_usage = trim($client->exec("echo $[100-$(vmstat 1 2|tail -1|awk '{print $15}')]"));
    $user = trim($client->exec("ps -eo pcpu,pid,user,args --no-headers| sort -t. -nk1,2 -k4,4 -r |head -n 1 | awk {'print $3'}"));
    $process = trim($client->exec("ps -eo pcpu,pid,user,args --no-headers| sort -t. -nk1,2 -k4,4 -r |head -n 1 | awk {'print $4'}"));
    $memory_total = trim($client->exec("cat /proc/meminfo | grep MemTotal | awk '{print $2}'"));
    $memory_used = trim($client->exec("cat /proc/meminfo | grep Active: | awk '{print $2}'"));
    $number_gpus = trim($client->exec("nvidia-smi -q -g 0 2>&1 | grep 'Attached GPUs' | awk '{print $4}'"));


    function array_check_numberic($array2check) {
        $check = TRUE;
        for($i = 0; $i < count($array2check); $i++) {
            $check = $check && is_numeric($array2check[$i]);
        }
        return $check;
    }

    $gpu_memory = 0;
    $gpu_usage = 0;
    if(is_numeric($number_gpus) == TRUE) {
        $gpu_memories = array_fill(0, $number_gpus, 0);
        $gpu_usages = array_fill(0, $number_gpus, 0);
        
        for($i = 0; $i < $number_gpus; $i++) {
            //$gpu_memories[$i] = trim($client->exec("nvidia-smi -q -g $i 2>&1 | grep -A 2 -i utilization | grep -i memory | tail -1 | awk '{print $3}' | sed s/\%//g"));
            $gpu_memories_mib = trim($client->exec("nvidia-smi -q -g $i 2>&1 | grep -A 2 -i 'FB Memory Usage' | grep Used | awk '{print $3}'"));
            $gpu_memories_mib_total = trim($client->exec("nvidia-smi -q -g $i 2>&1 | grep -A 2 -i 'FB Memory Usage' | grep Total | awk '{print $3}'"));
            $gpu_memories[$i] = $gpu_memories_mib/$gpu_memories_mib_total*100;
            
            $gpu_usages[$i] = trim($client->exec("nvidia-smi -q -g $i 2>&1 | grep -A 2 -i utilization | grep -i Gpu | tail -1 | awk '{print $3}' | sed s/\%//g"));
        }

        if(array_check_numberic($gpu_memories) == TRUE && array_check_numberic($gpu_usages) == TRUE) {
            $gpu_memory = round(array_sum($gpu_memories)/$number_gpus); 
            $gpu_usage = round(array_sum($gpu_usages)/$number_gpus);
        }
    }
        
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

    if(is_numeric($cpu_usage) == FALSE) {
        $cpu_usage = 0;
    }

    $memory = 0;
    if(is_numeric($memory_total) == TRUE && is_numeric($memory_used) == TRUE) {
        $memory =intval($memory_used/$memory_total*100);
    }

    $client->disconnect();

    // Cut string of too long at the beginning
    $process = strlen($process) > 10 ? "...".substr($process, -10) : $process;
    $user = strlen($user) > 10 ? "...".substr($user, -10) : $user;

    echo json_encode(array("cpu" => $cpu_usage, "user" => $user, "process" => $process, "gpu" => $gpu_usage, "gpu_memory" => $gpu_memory, "memory" => $memory));
?>      
