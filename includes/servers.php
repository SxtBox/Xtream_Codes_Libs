<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class ipTV_Servers
{
    public static function server($servers_ID, $pid, $exe)
    {
        if (!is_null($pid) && is_numeric($pid) && array_key_exists($servers_ID, ipTV_lib::$StreamingServers)) {
            if (!($output = self::server($servers_ID, array($pid), $exe))) {
                return false;
            }
            return $output[$servers_ID][$pid];
        }
        return false;
    }

    public static function RunCommandServer($serverIDS, $cmd, $type = "array", $force = false)
    {
        $output = array();
        if (!is_array($serverIDS)) {
            $serverIDS = array(intval($serverIDS));
        }
        if (!empty($cmd)) {
            foreach ($serverIDS as $server_id) {
                if ($server_id != SERVER_ID) {
                    if (array_key_exists($server_id, ipTV_lib::$StreamingServers)) {
                        $response = self::ServerSideRequest($server_id, ipTV_lib::$StreamingServers[$server_id]["api_url_ip"] . "&action=runCMD", array("command" => $cmd), $force);
                        if ($response) {
                            $result = json_decode($response, true);
                            $output[$server_id] = $type == "array" ? $result : implode("\n", $result);
                        } else {
                            $output[$server_id] = false;
                        }
                    }
                } else {
                    exec($cmd, $return);
                    $output[$server_id] = $type == "array" ? $return : implode("\n", $return);
                }
            }
            return $output;
        } else {
            foreach ($serverIDS as $server_id) {
                $output[$server_id] = "";
            }
            return $output;
        }
    }

    public static function server($serverIDS, $pids = array(), $exe)
    {
        if (!is_array($serverIDS)) {
            $serverIDS = array(intval($serverIDS));
        }
        $pids = array_map("intval", $pids);
        $output = array();
        foreach ($serverIDS as $server_id) {
            if ($server_id != SERVER_ID) {
                if (array_key_exists($server_id, ipTV_lib::$StreamingServers)) {
                    $response = self::ServerSideRequest($server_id, ipTV_lib::$StreamingServers[$server_id]["api_url_ip"] . "&action=pidsAreRunning", array("program" => $exe, "pids" => $pids));
                    if ($response) {
                        $output[$server_id] = array_map("trim", json_decode($response, true));
                    } else {
                        $output[$server_id] = false;
                    }
                }
            } else {
                foreach ($pids as $pid) {
                    if (file_exists("/proc/" . $pid) && is_readable("/proc/" . $pid . "/exe") && readlink("/proc/" . $pid . "/exe") == $exe) {
                        $output[$server_id][$pid] = true;
                    } else {
                        $output[$server_id][$pid] = false;
                    }
                }
            }
        }
        return $output;
    }

    public static function ServerSideRequest($server_id, $server_url, $PostData = array(), $force = false)
    {
        global $ipTV_db;
        if ($force === false) {
            if (ipTV_lib::$StreamingServers[$server_id]["status"] == 1) {
            } else {
                return false;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server_url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0) Gecko/20100101 Firefox/9.0");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (!empty($PostData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($PostData));
        }

        $output = curl_exec($ch);
        $response_data = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch);
        if ($error != 0) {
            if (ipTV_lib::$StreamingServers[$server_id]["status"] == 1) {
                $ipTV_db->query("UPDATE `streaming_servers` SET `status` = 3 WHERE `id` = '%d'", $server_id);
                ipTV_lib::SaveLog("cURL Error ( Server ID: " . $server_id . " ) - ( ERROR: " . $error . " | ResponseCode: " . $response_data . " | URL: " . $server_url . " | Data: " . serialize($PostData) . "  )");
            }
            return false;
        }
        if (stristr($output, "Can Not Connect to database")) {
            $ipTV_db->query("UPDATE `streaming_servers` SET `status` = 2 WHERE `id` = '%d'", $server_id);
            ipTV_lib::SaveLog("MySQL Error ( Server ID: " . $server_id . " )");
            return false;
        }
        if (ipTV_lib::$StreamingServers[$server_id]["status"] != 1) {
            $ipTV_db->query("UPDATE `streaming_servers` SET `status` = 1 WHERE `id` = '%d'", $server_id);
        }
        @curl_close($ch);
        return $output;
    }

// Loadbalancer Code Removed

{
	// Loadbalancer Code Here
}

}

?>