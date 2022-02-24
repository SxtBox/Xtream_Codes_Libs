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
    usleep(mt_rand(0, 500000));
    if (GetLicence()) {
        if (ipTV_lib::$settings["autobackup_status"] == 1) {
            $pass_dec = ipTV_lib::mc_decrypt(ipTV_lib::$settings["autobackup_pass"], hash("sha256", base64_encode("XtreamCodesRemoteB@ckupS3rvic3!")));
            mc_encrypt($pass_dec, "save_backup_file", NULL);
        }
    } else {
        exit("Licence is invalid!");
    }
} else {
    exit(0);
}

?>