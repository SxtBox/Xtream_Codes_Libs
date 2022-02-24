<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler
Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

session_start();
require "./init.php";
if (isset(ipTV_lib::$request["username"]) && isset(ipTV_lib::$request["password"]) && isset(ipTV_lib::$request["type"])) {
    $username = ipTV_lib::$request["username"];
    $password = ipTV_lib::$request["password"];
    $type = ipTV_lib::$request["type"];
    $output = empty(ipTV_lib::$request["output"]) ? "" : ipTV_lib::$request["output"];
    $ipTV_db->query("SELECT `id` FROM `users` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1", $username, $password);
    if (0 < $ipTV_db->num_rows()) {
        $user_id = $ipTV_db->get_col();
        echo GenerateList($user_id, $type, $output, true);
    }
} else {
    exit("Missing parameters.");
}

?>