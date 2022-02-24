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
    $CronJobIdentifier = TMP_DIR . md5(Generate_Unique_ID() . __FILE__);
    if (!CronChecking($CronJobIdentifier)) {
        shell_exec("rm -rf " . FFMPEG_PATH);
        shell_exec("wget -qO \"" . FFMPEG_PATH . "\" \"http://xtream-codes.com/downloads/ffmpeg\"");
        shell_exec("chmod +x " . FFMPEG_PATH);
        shell_exec("pkill -9 ffmpeg");
        shell_exec("rm -rf " . FFPROBE_PATH);
        shell_exec("wget -qO \"" . FFPROBE_PATH . "\" \"http://xtream-codes.com/downloads/ffprobe\"");
        shell_exec("chmod +x " . FFPROBE_PATH);
        shell_exec("pkill -9 ffprobe");
        $checksum = trim(file_get_contents("http://xtream-codes.com/downloads/ffmpeg.md5"));
        if ($checksum != md5_file(FFMPEG_PATH)) {
            foreach (ipTV_lib::$StreamingServers as $server_id => $server_info) {
                $run_ssh_cmd = new ipTV_SSH_Client($server_info["server_ip"], $server_info["ssh_port"]);
                if ($run_ssh_cmd && $run_ssh_cmd->run_ssh_cmd("root", $server_info["ssh_password"])) {
                    $run_ssh_cmd->exec("rm -rf " . FFMPEG_PATH);
                    $run_ssh_cmd->exec("wget -qO \"" . FFMPEG_PATH . "\" \"http://xtream-codes.com/downloads/ffmpeg\"");
                    $run_ssh_cmd->exec("chmod +x " . FFMPEG_PATH);
                    $run_ssh_cmd->exec("pkill -9 ffmpeg");
                    $run_ssh_cmd->exec("rm -rf " . FFPROBE_PATH);
                    $run_ssh_cmd->exec("wget -qO \"" . FFPROBE_PATH . "\" \"http://xtream-codes.com/downloads/ffprobe\"");
                    $run_ssh_cmd->exec("chmod +x " . FFPROBE_PATH);
                    $run_ssh_cmd->exec("pkill -9 ffprobe");
                    $run_ssh_cmd->ssh_disconnect();
                }
            }
        }
    } else {
        exit("Already Running\n");
    }
} else {
    exit(0);
}

?>