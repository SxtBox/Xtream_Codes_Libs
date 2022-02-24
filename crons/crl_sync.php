<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler
Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

if ($argc) {
    require str_replace("\\", "/", dirname($argv[0])) . "/../wwwdir/init.php";
    $ipTV_db->query("SELECT * FROM `cronjobs` WHERE `filename` = '%s' LIMIT 1", basename(__FILE__));
    $pid = $ipTV_db->get_row();
    if (ps_running($pid["pid"])) {
        posix_kill($pid["pid"], 9);
    }

    $ipTV_db->query("UPDATE `cronjobs` SET `pid` = '%d',`timestamp` = '%d' WHERE `filename` = '%s' LIMIT 1", getmypid(), time(), basename(__FILE__));
    $ipTV_db->query("SELECT COUNT(*) FROM `client_logs`");
    $total_rows = $ipTV_db->get_col();
    $client_logfile = TMP_DIR . "client_request.log";
    $query = "";

    if (file_exists($client_logfile)) {
        Parse_client_logfile($client_logfile, $query);
        unlink($client_logfile);
    }
    $query = rtrim($query, ",");
    if (!empty($query)) {
        $ipTV_db->simple_query("INSERT INTO `client_logs` (`stream_id`,`user_id`,`client_status`,`query_string`,`user_agent`,`ip`,`extra_data`,`date`) VALUES " . $query);
    }
} else {
    exit(0);
}

function Parse_client_logfile($file, &$query)
{
    if (file_exists($file)) {
        $fp = fopen($file, "r");
        while (feof($fp)) {
        }
        fclose($fp);
    }
    return $query;
}

?>