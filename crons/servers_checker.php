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
    define("IN_CHECKER", true);
    $CronJobIdentifier = TMP_DIR . md5(Generate_Unique_ID() . __FILE__);
    if (!CronChecking($CronJobIdentifier)) {
        $ipTV_db->query("SELECT * FROM `streaming_servers` WHERE `id` <> '%d'", SERVER_ID);
        if (0 < $ipTV_db->num_rows()) {
            $rows = $ipTV_db->get_rows();
            foreach ($rows as $row) {
                echo "Checking: " . $row["server_ip"] . "\n";
                switch ($row["status"]) {
                    case -1:
                        $loadbalance = false;
                        $package = "load" . mt_rand(0, 100000) . "_package.tar.gz";
                        if (!($packages = install_packages("iptv_pro_load_balance", $package))) {
                            $loadbalance = true;
                            ipTV_lib::SaveLog("Package downloaded from xtream-codes.com is empty. Load balancing.");
                        }
                        if (!$loadbalance) {
                            $this = new ipTV_SSH_Client($row["server_ip"], $row["ssh_port"]);
                            if ($this && $this->run_ssh_cmd("root", ipTV_lib::$StreamingServers[$row["id"]]["ssh_password"])) {
                                echo "Connection established. Preparing... -> " . $row["server_ip"] . "\n";
                                $this->ServerID = $row["id"];
                                $this->MainIP = ipTV_lib::$StreamingServers[SERVER_ID]["server_ip"];
                                $this->http_port = $row["http_broadcast_port"];
                                ipTV_Stream::Close_All_Connections_By_Server($row["id"]);
                                if ($this->IdentifyOS()) {
                                    $ipTV_db->query("UPDATE `streaming_servers` SET `system_os` = '%s' WHERE `id` = '%d'", $this->SystemOS, $row["id"]);
                                    $this->install_packages($packages);
                                } else {
                                    $ipTV_db->query("UPDATE `streaming_servers` SET `status` = 0 WHERE `id` = '%d'", $row["id"]);
                                }
                                $this->ssh_disconnect();
                            } else {
                                echo "Connection Failed. Wrong Auth : " . $row["server_ip"] . "\n";
                            }
                        } else {
                            echo "No Package For : " . $row["server_ip"] . "\n";
                        }
                }
                $time = microtime(true);
                $result = ipTV_Servers::ServerSideRequest($row["id"], ipTV_lib::$StreamingServers[$row["id"]]["api_url_ip"] . "&action=getDiff", array("main_time" => time()), true);
                if ($result !== false) {
                    echo "Result: " . $result . "\n";
                    $timef = microtime(true);
                    $total = (double) number_format($timef - $time, 3);
                    if ($row["status"] != 1) {
                        $ipTV_db->query("UPDATE `streaming_servers` SET `status` = 1 WHERE `id` = '%d'", $row["id"]);
                    }
                    $result = intval($result);
                    $ipTV_db->query("UPDATE `streaming_servers` SET `diff_time_main` = '%d',`latency` = '%f' WHERE `id` = '%d'", intval($result), $total, $row["id"]);
                } else {
                    if ($row["status"] == 1) {
                        echo "Result: Offline. Trying other\n";
                    }
                }
            }
        }
        @unlink($packages);
        @unlink($CronJobIdentifier);
    } else {
        exit("Already Running\n");
    }
} else {
    exit(0);
}

?>