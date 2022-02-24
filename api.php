<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler
Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

set_time_limit(0);
require "./init.php";
$User_IP = ipTV_Stream::getUserIP();
if (in_array($User_IP, ipTV_Stream::getAllowedIPsAdmin())) {
    if (!(empty(ipTV_lib::$request["password"]) || ipTV_lib::$request["password"] != ipTV_lib::$settings["live_streaming_pass"] || empty(ipTV_lib::$request["action"]))) {
        header("Access-Control-Allow-Origin: *");
        header("X-Accel-Buffering: no");
        switch (ipTV_lib::$request["action"]) {
            case "runCMD":
                if (empty(ipTV_lib::$request["command"])) {
                    break;
                }
				
                exec(ipTV_lib::$request["command"], $return);
                echo json_encode($return);
                exit;
            case "stats":
                $json_data = array();
                $json_data["cpu"] = intval(GetTotalCPUsage());
                $json_data["mem"] = intval(memory_usage()[0]["percent"]);
                $json_data["uptime"] = get_boottime();
                echo json_encode($json_data);
                exit;
            case "getDiff":
                if (empty(ipTV_lib::$request["main_time"])) {
                    break;
                }
                $main_time = ipTV_lib::$request["main_time"];
                echo json_encode($main_time - time());
                exit;
            case "pidsAreRunning":
                if (empty(ipTV_lib::$request["pids"]) || !is_array(ipTV_lib::$request["pids"]) || empty(ipTV_lib::$request["program"])) {
                    break;
                }
                $pids = array_map("intval", ipTV_lib::$request["pids"]);
                $exe = ipTV_lib::$request["program"];
                $output = array();
                foreach ($pids as $pid) {
                    $output[$pid] = false;
                    if (file_exists("/proc/" . $pid) && is_readable("/proc/" . $pid . "/exe") && readlink("/proc/" . $pid . "/exe") == $exe) {
                        $output[$pid] = true;
                    }
                }
                echo json_encode($output);
                exit;

            case "getFile":
                if (empty(ipTV_lib::$request["filename"])) {
                    break;
                }
                $filename = urldecode(ipTV_lib::$request["filename"]);
                if (file_exists($filename) && is_readable($filename)) {
                    header("X-Accel-Buffering: no");
                    header("Transfer-encoding: chunked");
                    header("Content-Description: File Transfer");
                    header("Content-Type: application/octet-stream");
                    header("Content-Length: " . filesize($filename));
                    header("Content-Disposition: attachment; filename=" . basename($filename));
                    ob_end_flush();
                    readfile($filename);
                }
                exit;

            case "viewDir":
                $dir = urldecode(ipTV_lib::$request["dir"]);
                if (file_exists($dir)) {
                    $files = scandir($dir);
                    natcasesort($files);
                    if (2 < count($files)) {
                        echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
                        foreach ($files as $file) {
                            if (file_exists($dir . $file) && $file != "." && $file != ".." && is_dir($dir . $file) && is_readable($dir . $file)) {
                                echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "/\">" . htmlentities($file) . "</a></li>";
                            }
                        }
                        foreach ($files as $file) {
                            if (file_exists($dir . $file) && $file != "." && $file != ".." && !is_dir($dir . $file) && is_readable($dir . $file)) {
                                $ext = preg_replace("/^.*\\./", "", $file);
                                echo "<li class=\"file ext_" . $ext . "\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "\">" . htmlentities($file) . "</a></li>";
                            }
                        }
                        echo "</ul>";
                    }
                }
                exit;
        }
    } else {
        exit;
    }
} else {
    exit;
}

?>