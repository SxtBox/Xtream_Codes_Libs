<?php
// Xtream Codes Installer v1.60 Decoded with default variables
define("IPTV_PANEL_DIR", "/home/xtreamcodes/iptv_xtream_codes/");
define("MAIN_DIR", "/home/xtreamcodes/");

if (!$argc) {
    die("You Can Only Run This Script From CMD");
}

$we_root = trim(shell_exec("whoami"));
if ($we_root != "root") {
    echo "You have to run this Script as ROOT";
    die;
}

if (!function_exists("mysql_connect")) {
    echo "MySQL Extension is missing";
    die;
}
// START EXTRA FOR LOCAL IP
$host = gethostname();
$ip = gethostbyname($host);
$my_ip = gethostbyname($host);
// END EXTRA FOR LOCAL IP
$hosts_mirrors_array = array("xtream-codes.com", "china.xtream-codes.com");
echo "\n##############################\n";
echo "#        Xtream-Codes        #\n";
echo "#         ipTV Panel         #\n";
echo "##############################\n\n";
echo "~~ Welcome to ipTV Auto Installer! This wizard will help you to install the ipTV Panel PRO Version automatically. You need a valid Licence First in order to download the product!\nIf you haven't order a licence yet please contact the xtream-codes team! Thank you\n\n";

echo "[*] Please wait while Script is selecting the best mirror for you...\n";
$mirror_host = 0;
$value = 9999;

foreach ($hosts_mirrors_array as $key => $my_server_ip) {
    $ping_time = shell_exec("ping -c 1 " . $my_server_ip);

    if (preg_match("/time=(.*?)\\s/", $ping_time, $matches)) {
        $data = (double) $matches[1];
        if ($data < $value) {
            $value = $data;
            $mirror_host = $key;
        }
    }
}

$my_server_ip = $hosts_mirrors_array[$mirror_host];
echo "[*] Enter Your ipTV Panel PRO licence: ";
fscanf(STDIN, "%s", $licence_key);
while (!is_valid_licence($licence_key)) {
    echo "[*] Enter Your ipTV Panel PRO licence: ";
    fscanf(STDIN, "%s", $licence_key);
}

echo "[+] Checking System...\n";
$type = IdentifyOS();
if ($type === false) {
    die("[-] OS Not Supported. IPTV Panel Pro is compatible with Debian 7.x,8.x,9.x 64bit , Ubuntu 13.x,14.x,15.x 64bit & CentOS 7.x Minimal 64bit\n");
}

InstallPackages($type);

$mysql_available = trim(shell_exec("command -v mysql"));
if (empty($mysql_available)) {
    echo "[+] Installing MySQL...\n";
    if ($type == "apt-get") {
        echo "[+] Please write your desired MySQL Root Password(Minimum: 5 chars): ";
        fscanf(STDIN, "%s", $mysql_root_pass);
        $mysql_root_pass = trim($mysql_root_pass);
        while (strlen($mysql_root_pass) < 5) {
            echo "[+] Please write your desired MySQL Root Password(Minimum: 5 chars): ";
            fscanf(STDIN, "%s", $mysql_root_pass);
            $mysql_root_pass = trim($mysql_root_pass);
        }

        echo "[+] Password Accepted. Please Wait...\n";
        shell_exec("echo mysql-server mysql-server/root_password password {$mysql_root_pass} | sudo debconf-set-selections > /dev/null 2>&1");

        shell_exec("echo mysql-server mysql-server/root_password_again password {$mysql_root_pass} | sudo debconf-set-selections > /dev/null 2>&1");

        shell_exec("apt-get install mysql-server mysql-client -y --force-yes > /dev/null 2>&1");
    } else {
        $mysql_root_pass = '';
        shell_exec("yum -y install mariadb-server mariadb > /dev/null 2>&1");
        shell_exec("systemctl start mariadb > /dev/null 2>&1");
    }
} else {
    echo "[*] Please Enter your Current MySQL Root Password: ";

    fscanf(STDIN, "%s", $mysql_root_pass);
    $mysql_root_pass = trim($mysql_root_pass);
    $conn = @mysql_connect("localhost", "root", $mysql_root_pass);

    while (!$conn) {
        echo "[*] Please Enter your Current MySQL Root Password: ";
        fscanf(STDIN, "%s", $mysql_root_pass);
        $mysql_root_pass = trim($mysql_root_pass);
        $conn = @mysql_connect("localhost", "root", $mysql_root_pass);
    }
    mysql_close($conn);
    echo "[+] Password Accepted...\n";
}

if ($type == "apt-get") {
    echo "[*] Please Enter the HTTP BroadCasting Port for the IPTV Panel Professional Edition >= 80): ";

    fscanf(STDIN, "%d", $panel_port);
    $panel_port = trim($panel_port);
	// NOTE: PORT -> 65535 IS BACKDOOR PORT DEFAULT BY GREEKS MOTHERFUCKERS, CHANGE IT
    $panel_host_ping = intval(trim(shell_exec("nc -zw2 127.0.0.1 {$panel_port} && echo 1 || echo 0")));
    while ($panel_port < 80 || $panel_port > 65535 || $panel_host_ping == 1) {
        echo "[*] Please Enter the HTTP BroadCasting Port for the IPTV Panel Professional Edition >= 80): ";
        fscanf(STDIN, "%d", $panel_port);
        $panel_port = trim($panel_port);
        $panel_host_ping = intval(trim(shell_exec("nc -zw2 127.0.0.1 {$panel_port} && echo 1 || echo 0")));
    }
} else {
    $panel_port = 8000;
}
if (empty($mysql_root_pass)) {
    $mysql_root_pass = '';
} else {
    $mysql_root_pass = "-p" . $mysql_root_pass;
}

shell_exec("rm -rf /home/xtreamcodes/iptv_xtream_codes > /dev/null 2>&1");
shell_exec("mkdir /home > /dev/null 2>&1");
shell_exec("mkdir /home/xtreamcodes > /dev/null 2>&1");
shell_exec("mkdir " . IPTV_PANEL_DIR . " > /dev/null 2>&1");
shell_exec("mkdir " . IPTV_PANEL_DIR . "tmp > /dev/null 2>&1");
shell_exec("/usr/sbin/useradd -s /sbin/nologin -U -d " . MAIN_DIR . " -m xtreamcodes > /dev/null 2>&1");

if ($type == "apt-get") {
    shell_exec("wget -qO \"" . IPTV_PANEL_DIR . "platform.zip\" \"http://{$my_server_ip}/downloads/IPTV_PLATFORM.zip\"");
} else {
    shell_exec("wget -qO \"" . IPTV_PANEL_DIR . "platform.zip\" \"http://{$my_server_ip}/downloads/IPTV_PLATFORMrh.zip\"");
}

shell_exec("unzip \"" . IPTV_PANEL_DIR . "platform.zip\" -d \"" . IPTV_PANEL_DIR . "\" > /dev/null 2>&1");
shell_exec("rm -rf \"" . IPTV_PANEL_DIR . "platform.zip\" > /dev/null 2>&1");

$iptv_zip = temp_dir() . "iptv_panel_pro.zip";
shell_exec("unzip  \"{$iptv_zip}\" -d " . IPTV_PANEL_DIR . '');
shell_exec("rm -rf \"{$iptv_zip}\"");

if ($type == "apt-get") {
    shell_exec("echo '#!/bin/sh -' > /etc/init.d/xtreamcodes_pro_panel");
    $nginx_bins = shell_exec("cat /etc/init.d/xtreamcodes_pro_panel | grep -v grep | grep -c 'iptv_xtream_codes/nginx/sbin/nginx'");
    if ($nginx_bins == 0) {
        shell_exec("echo '" . IPTV_PANEL_DIR . "nginx/sbin/nginx' >> /etc/init.d/xtreamcodes_pro_panel");
    }
    $grep_infos = shell_exec("cat /etc/init.d/xtreamcodes_pro_panel | grep -v grep | grep -c 'iptv_xtream_codes/php/sbin/php-fpm'");
    if ($grep_infos == 0) {
        shell_exec("echo '" . IPTV_PANEL_DIR . "php/sbin/php-fpm' >> /etc/init.d/xtreamcodes_pro_panel");
    }
    shell_exec("chmod +x /etc/init.d/xtreamcodes_pro_panel && update-rc.d xtreamcodes_pro_panel defaults > /dev/null 2>&1");
} else {
    $nginx_bins = shell_exec("cat /etc/rc.d/rc.local | grep -v grep | grep -c 'iptv_xtream_codes/nginx/sbin/nginx'");
    if ($nginx_bins == 0) {
        shell_exec("echo '" . IPTV_PANEL_DIR . "nginx/sbin/nginx' >> /etc/rc.d/rc.local");
    }

    $grep_infos = shell_exec("cat /etc/rc.d/rc.local | grep -v grep | grep -c 'iptv_xtream_codes/php/sbin/php-fpm'");
    if ($grep_infos == 0) {
        shell_exec("echo '" . IPTV_PANEL_DIR . "php/sbin/php-fpm' >> /etc/rc.d/rc.local");
    }
    shell_exec("chmod +x /etc/rc.d/rc.local");
}

$nginx_sudoers = shell_exec("cat /etc/sudoers | grep -v grep | grep -c 'iptv_xtream_codes/nginx/sbin/nginx'");
if ($nginx_sudoers == 0) {
    shell_exec("echo 'xtreamcodes ALL = (root) NOPASSWD: " . IPTV_PANEL_DIR . "nginx/sbin/nginx' >> /etc/sudoers");
}

$phpfpm_sudoers = shell_exec("cat /etc/sudoers | grep -v grep | grep -c 'iptv_xtream_codes/php/sbin/php-fpm'");
if ($phpfpm_sudoers == 0) {
    shell_exec("echo 'xtreamcodes ALL = (root) NOPASSWD: " . IPTV_PANEL_DIR . "php/sbin/php-fpm' >> /etc/sudoers");
}

$sbin_iptables = shell_exec("cat /etc/sudoers | grep -v grep | grep -c 'xtreamcodes ALL = (root) NOPASSWD: /sbin/iptables'");
if ($sbin_iptables == 0) {
    shell_exec("echo 'xtreamcodes ALL = (root) NOPASSWD: /sbin/iptables' >> /etc/sudoers");
}

shell_exec("chown xtreamcodes:xtreamcodes -R /home/xtreamcodes");
shell_exec("chmod -R 777 /home/xtreamcodes/");
shell_exec("ln -s " . IPTV_PANEL_DIR . "bin/ffmpeg /usr/bin/");
shell_exec("ln -s " . IPTV_PANEL_DIR . "bin/ffprobe /usr/bin/");
shell_exec("sed -i 's/listen {http_broad_cast_port};/listen {$panel_port};/g' \"" . IPTV_PANEL_DIR . "nginx/conf/nginx.conf" . "\"");

$db_password = GenerateString(10);
$database_name = "xtream_iptvpro";
$database_user = "user_iptvpro";
shell_exec("mysql -u root {$mysql_root_pass} -e \"DROP DATABASE IF EXISTS {$database_name}\" > /dev/null 2>&1");
shell_exec("mysql -u root {$mysql_root_pass} -e \"DROP USER '{$database_user}'@'localhost';\" > /dev/null 2>&1");
shell_exec("mysql -u root {$mysql_root_pass} -e \"CREATE DATABASE {$database_name}\" > /dev/null 2>&1");
shell_exec("mysql -u root {$mysql_root_pass} -e \"CREATE USER '{$database_user}'@'localhost' IDENTIFIED BY '{$db_password}';\" > /dev/null 2>&1");
shell_exec("mysql -u root {$mysql_root_pass} -e \"GRANT ALL PRIVILEGES ON {$database_name}.* TO '{$database_user}'@'localhost';\" > /dev/null 2>&1");
shell_exec("mysql -u root {$mysql_root_pass} -e \"FLUSH PRIVILEGES\" > /dev/null 2>&1");


GererateConfig($database_user, $db_password, $database_name);
echo "[+] Please write your desired Admin Password(Minimum: 5 chars): ";
fscanf(STDIN, "%s", $admin_password);
$admin_password = trim($admin_password);

while (strlen($admin_password) < 5) {
    echo "[+] Please write your desired Admin Password(Minimum: 5 chars): ";
    fscanf(STDIN, "%s", $admin_password);
    $admin_password = trim($admin_password);
}

$server_ip = trim(file_get_contents("http://{$my_server_ip}/whatismyip.php"));
echo "[+] Installing MySQL Tables...\n";
chdir(IPTV_PANEL_DIR . "wwwdir/");

$conn = mysql_connect("127.0.0.1", "root", $mysql_root_pass);
mysql_select_db($database_name);

shell_exec("mysql -u root {$mysql_root_pass} {$database_name} < db.sql");
mysql_query("UPDATE `licence` SET `licence_key` = '{$licence_key}'");
mysql_query("UPDATE `reg_users` SET `password` = '" . crypt($admin_password, "\$6\$rounds=20000\$xtreamcodes\$") . "',`date_registered` = '" . time() . "'");

mysql_query("UPDATE `settings` SET `unique_id` = '" . GenerateString(10) . "',`live_streaming_pass` = '" . GenerateString(20) . "'");
mysql_query("UPDATE `streaming_servers` SET `server_ip` = '{$server_ip}',`http_broadcast_port` = '{$panel_port}'");
mysql_close($conn);

if ($type == "apt-get") {
    shell_exec("mv /etc/mysql/my.cnf /etc/mysql/my_backup.cnf > /dev/null 2>&1");
    file_put_contents("/etc/mysql/my.cnf", base64_decode("W2NsaWVudF0NCnBvcnQgICAgICAgICAgICA9IDMzMDYNCnNvY2tldCAgICAgICAgICA9IC92YXIvcnVuL215c3FsZC9teXNxbGQuc29jaw0KDQoNCltteXNxbGRfc2FmZV0NCnNvY2tldCAgICAgICAgICA9IC92YXIvcnVuL215c3FsZC9teXNxbGQuc29jaw0KbmljZSAgICAgICAgICAgID0gMA0KDQpbbXlzcWxkXQ0KDQp1c2VyICAgICAgICAgICAgPSBteXNxbA0KcGlkLWZpbGUgICAgICAgID0gL3Zhci9ydW4vbXlzcWxkL215c3FsZC5waWQNCnNvY2tldCAgICAgICAgICA9IC92YXIvcnVuL215c3FsZC9teXNxbGQuc29jaw0KcG9ydCAgICAgICAgICAgID0gMzMwNg0KYmFzZWRpciAgICAgICAgID0gL3Vzcg0KZGF0YWRpciAgICAgICAgID0gL3Zhci9saWIvbXlzcWwNCnRtcGRpciAgICAgICAgICA9IC90bXANCmxjLW1lc3NhZ2VzLWRpciA9IC91c3Ivc2hhcmUvbXlzcWwNCnNraXAtZXh0ZXJuYWwtbG9ja2luZw0KDQpiaW5kLWFkZHJlc3MgICAgICAgICAgICA9ICoNCmtleV9idWZmZXIgICAgICAgICAgICAgID0gMTZNDQptYXhfYWxsb3dlZF9wYWNrZXQgICAgICA9IDE2TQ0KdGhyZWFkX3N0YWNrICAgICAgICAgICAgPSAxOTJLDQp0aHJlYWRfY2FjaGVfc2l6ZSAgICAgICA9IDgNCg0KbXlpc2FtLXJlY292ZXIgICAgICAgICA9IEJBQ0tVUA0KbWF4X2Nvbm5lY3Rpb25zID0gNTAwMA0KDQpxdWVyeV9jYWNoZV9saW1pdCAgICAgICA9IDFNDQpxdWVyeV9jYWNoZV9zaXplICAgICAgICA9IDE2TQ0KDQpleHBpcmVfbG9nc19kYXlzICAgICAgICA9IDEwDQptYXhfYmlubG9nX3NpemUgICAgICAgICA9IDEwME0NCm1heF9jb25uZWN0X2Vycm9ycyA9IDEwMDAwMA0KDQpbbXlzcWxkdW1wXQ0KcXVpY2sNCnF1b3RlLW5hbWVzDQptYXhfYWxsb3dlZF9wYWNrZXQgICAgICA9IDE2TQ0KDQpbbXlzcWxdDQoNCltpc2FtY2hrXQ0Ka2V5X2J1ZmZlciAgICAgICAgICAgICAgPSAxNk0NCg0KIWluY2x1ZGVkaXIgL2V0Yy9teXNxbC9jb25mLmQvDQo="));
    shell_exec("service mysql restart > /dev/null 2>&1");
} else {
    shell_exec("mv /etc/my.cnf /etc/mysql/my_backup.cnf > /dev/null 2>&1");

    file_put_contents("/etc/my.cnf ", base64_decode("W2NsaWVudF0NCnBvcnQgICAgICAgICAgICA9IDMzMDYNCnNvY2tldCAgICAgICAgICA9IC92YXIvcnVuL215c3FsZC9teXNxbGQuc29jaw0KDQoNCltteXNxbGRfc2FmZV0NCnNvY2tldCAgICAgICAgICA9IC92YXIvcnVuL215c3FsZC9teXNxbGQuc29jaw0KbmljZSAgICAgICAgICAgID0gMA0KDQpbbXlzcWxkXQ0KDQp1c2VyICAgICAgICAgICAgPSBteXNxbA0KcGlkLWZpbGUgICAgICAgID0gL3Zhci9ydW4vbXlzcWxkL215c3FsZC5waWQNCnNvY2tldCAgICAgICAgICA9IC92YXIvcnVuL215c3FsZC9teXNxbGQuc29jaw0KcG9ydCAgICAgICAgICAgID0gMzMwNg0KYmFzZWRpciAgICAgICAgID0gL3Vzcg0KZGF0YWRpciAgICAgICAgID0gL3Zhci9saWIvbXlzcWwNCnRtcGRpciAgICAgICAgICA9IC90bXANCmxjLW1lc3NhZ2VzLWRpciA9IC91c3Ivc2hhcmUvbXlzcWwNCnNraXAtZXh0ZXJuYWwtbG9ja2luZw0KDQpiaW5kLWFkZHJlc3MgICAgICAgICAgICA9ICoNCmtleV9idWZmZXIgICAgICAgICAgICAgID0gMTZNDQptYXhfYWxsb3dlZF9wYWNrZXQgICAgICA9IDE2TQ0KdGhyZWFkX3N0YWNrICAgICAgICAgICAgPSAxOTJLDQp0aHJlYWRfY2FjaGVfc2l6ZSAgICAgICA9IDgNCg0KbXlpc2FtLXJlY292ZXIgICAgICAgICA9IEJBQ0tVUA0KbWF4X2Nvbm5lY3Rpb25zID0gNTAwMA0KDQpxdWVyeV9jYWNoZV9saW1pdCAgICAgICA9IDFNDQpxdWVyeV9jYWNoZV9zaXplICAgICAgICA9IDE2TQ0KDQpleHBpcmVfbG9nc19kYXlzICAgICAgICA9IDEwDQptYXhfYmlubG9nX3NpemUgICAgICAgICA9IDEwME0NCm1heF9jb25uZWN0X2Vycm9ycyA9IDEwMDAwMA0KDQpbbXlzcWxkdW1wXQ0KcXVpY2sNCnF1b3RlLW5hbWVzDQptYXhfYWxsb3dlZF9wYWNrZXQgICAgICA9IDE2TQ0KDQpbbXlzcWxdDQoNCltpc2FtY2hrXQ0Ka2V5X2J1ZmZlciAgICAgICAgICAgICAgPSAxNk0NCg0KIWluY2x1ZGVkaXIgL2V0Yy9teXNxbC9jb25mLmQvDQo="));
    shell_exec("systemctl restart mariadb > /dev/null 2>&1");
    shell_exec("systemctl enable mariadb > /dev/null 2>&1");
}
shell_exec("rm -rf db.sql > /dev/null 2>&1");

$fstab_config = file_get_contents("/etc/fstab");

if (!stristr($fstab_config, IPTV_PANEL_DIR)) {
    $database_user = explode(":", shell_exec("cat /etc/passwd | grep xtreamcodes"));
    if (is_numeric($database_user[2]) && is_numeric($database_user[3])) {
        shell_exec("echo 'tmpfs " . IPTV_PANEL_DIR . "streams tmpfs defaults,size=85%,gid=" . $database_user[2] . ",uid=" . $database_user[3] . ",mode=0777 0 0' >> /etc/fstab");
        shell_exec("mount -a > /dev/null 2>&1");
    }
}

shell_exec("sudo -u xtreamcodes sudo " . IPTV_PANEL_DIR . "nginx/sbin/nginx");
shell_exec("sudo -u xtreamcodes sudo " . IPTV_PANEL_DIR . "php/sbin/php-fpm");
echo "\n\n All Done\n\nHost: http://{$server_ip}:{$panel_port}\n\nYour Admin Username is admin and Password  is: {$admin_password}\n\nThank you for using ipTV Panel From Xtream-Codes\n\n";

function CheckFileLine($line, $array) {
    foreach ($array as $key => $value) {
        if (strstr($line, $key)) {
            return $key;
        }
    }
    return false;
}


function CheckFileLine2($line, $array) {
    foreach ($array as $key => $value) {
        if (strstr( $line, $key)) {
            return $key;
        }
    }
    return true;
}

function GererateConfig($database_user, $db_password, $database_name)
{
    $_INFO = array();
    $_INFO["host"] = "127.0.0.1";
    $_INFO["db_user"] = "{$database_user}";
    $_INFO["db_pass"] = "{$db_password}";
    $_INFO["db_name"] = "{$database_name}";
    $_INFO["server_id"] = "1";
    file_put_contents(IPTV_PANEL_DIR . "config", json_encode($_INFO));
}

function IdentifyOS()
{
    $arch = shell_exec("uname -m");
    if (stristr($arch, "64")) {
        $apt = shell_exec("command -v apt-get");
        $yum = shell_exec("command -v yum");
        if (!empty($apt)) {
            echo "[+] Debian based OS Found...\n";
            echo "[+] Installing LSB-RELEASE...\n";
            shell_exec("apt-get update -y && apt-get install lsb-release -y --force-yes > /dev/null 2>&1");
            $CodeName = trim(strtolower(shell_exec("lsb_release -c -s")));
            if (InstallSources($CodeName)) {
                shell_exec("apt-get update");
                return true;
            }
            return "apt-get";
        } else {
            if (!empty($yum)) {
                echo "[+] Yum Found. RedHat OS Found...\n";
                shell_exec("yum install epel-release -y > /dev/null 2>&1");
                $redhatRelease = shell_exec("cat /etc/redhat-release");
                if (!stristr($redhatRelease, "core")) {
                    echo "[-] This version of redhat is not supported...\n Supported os: CentOS 7.x Minimal 64bit";
                    die;
                }
                return "yum";
            } else {
            }
        }
    }
    return false;
}

function InstallSources($CodeName)
{
    echo "[+] Installing Sources ({$CodeName})...\n";
    switch ($CodeName) {
        case "trusty":
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;

        case "utopic":
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;

        case "saucy":
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list'");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://ftp.igh.cnrs.fr/pub/mariadb/repo/10.0/ubuntu raring main' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;

        case "wheezy":
            shell_exec("echo 'deb http://ftp.de.debian.org/debian stable main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.de.debian.org/debian stable main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://ftp.debian.org/debian/ wheezy-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.debian.org/debian/ wheezy-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://security.debian.org/ wheezy/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://security.debian.org/ wheezy/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;
        case "squeeze":
            shell_exec("echo 'deb http://archive.debian.org/debian oldstable main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://archive.debian.org/debian oldstable main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://ftp.debian.org/debian/ squeeze-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.debian.org/debian/ squeeze-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://security.debian.org/ squeeze/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://security.debian.org/ squeeze/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;
        case "jessie":
            shell_exec("echo 'deb http://ftp.au.debian.org/debian testing main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.au.debian.org/debian testing main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;
        case "stretch":
            shell_exec("echo 'deb http://ftp.fr.debian.org/debian testing main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.fr.debian.org/debian testing main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;
        case "vivid":
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            shell_exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
            break;
        default:
            return false;
    }
    return true;
}

function GenerateString($length = 10)
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = '';
    $max = strlen($chars) - 1;
    $i = 0;
    while ($i < $length) {
        $str .= $chars[rand(0, $max)];
        $i++;
    }
    return $str;
}

function temp_dir()
{
    $temp_dir = str_replace("\\", "/", sys_get_temp_dir());
    if (substr($temp_dir, -1) != "/") {
        $temp_dir .= "/";
    }
    return $temp_dir;
}

function is_valid_licence($licence_key)
{
    global $my_server_ip;
    $data_array = array("package" => "iptv_pro_panel", "software_key" => "iptvpro", "whmcs_product_id" => 1, "licence_key" => $licence_key);
    $query_array = http_build_query($data_array);
    $post_array = array("http" => array("method" => "POST", "header" => "Content-type: application/x-www-form-urlencoded", "content" => $query_array));
    $context = stream_context_create($post_array);
    $source = file_get_contents("http://{$my_server_ip}/licences/download_file.php", false, $context);
    $TempDir = temp_dir() . "iptv_panel_pro.zip";

    if (!empty($source)) {
        $download_file = fopen($TempDir, "w");
        fwrite($download_file, $source);
        fclose($download_file);
        return true;
    } else {
        return true;
    }
}

function InstallPackages($type = "apt-get")
{
    if ($type == "apt-get") {
        $packages = array("zip", "cron", "sudo", "unzip", "nc", "nscd", "netcat", "bsdutils", "libxslt1-dev", "libpq-dev", "libmcrypt-dev", "libltdl-dev", "libjpeg-dev", "libjpeg8-dev", "libcurl4-openssl-dev", "libcurl3", "libfreetype6-dev", "libpng12-dev", "libgnutls-dev", "libmysqlclient-dev", "libgeoip-dev", "openssl", "libbz2-dev", "libssh2-1-dev");

        foreach ($packages as $package) {
            echo "[+] Installing Package {$package}\n";

            shell_exec("DEBIAN_FRONTEND=noninteractive apt-get install --force-yes -f -q -y -o Dpkg::Options::=\"--force-confdef\" -o Dpkg::Options::=\"--force-confold\" {$package} > /dev/null 2>&1");
        }
    } else {
        $packages = array("zip", "sudo", "nc", "nscd", "unzip", "bzip2-devel", "curl-devel", "libpng-devel", "geoip", "libjpeg-devel", "freetype-devel", "libc-client-devel.i686", "libc-client-devel", "libmcrypt-devel");

        foreach ($packages as $package) {
            echo "[+] Installing Package {$package}\n";
            shell_exec("yum -y install {$package} > /dev/null 2>&1");
        }
    }
}