<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class ipTV_SSH_Client
{
    public $ServerID = NULL;
    public $ssh = NULL;
    public $stream = NULL;
    public $MainIP = NULL;
    public $SystemOS = NULL;
    public $CodeName = NULL;
    public $valid = false;
    public $http_port = NULL;
    public $host_ip = NULL;
    public $repo = NULL;

    public function __construct($host, $port = 22)
    {
        $this->host_ip = $host;
        if ($this->ssh = ssh2_connect($host, $port)) {
        } else {
            ipTV_lib::SaveLog("Could not Connect to " . $host . ":" . $port . " SSH Service");
            return false;
        }
    }

    public function ssh_disconnect()
    {
        if ($this->ssh) {
            $this->exec("echo \"EXITING\" && exit;");
            $this->ssh = NULL;
        }
    }

    public function __destruct()
    {
        $this->ssh_disconnect();
    }

    public function install_packages($file)
    {
        if ($this->valid) {
            if ($this->repo == "apt-get") {
                $packages = array("cron", "sudo", "unzip", "nscd", "libxslt1-dev", "libpq-dev", "libmcrypt-dev", "libltdl-dev", "libjpeg-dev", "libjpeg8-dev", "libcurl4-openssl-dev", "libcurl3", "libfreetype6-dev", "libpng12-dev", "wget", "libgnutls-dev", "libmysqlclient-dev", "libgeoip-dev", "openssl", "libbz2-dev", "libssh2-1-dev");
                foreach ($packages as $package) {
                    $this->exec("DEBIAN_FRONTEND=noninteractive apt-get install --force-yes -f -q -y -o Dpkg::Options::=\"--force-confdef\" -o Dpkg::Options::=\"--force-confold\" " . $package);
                }
            } else {
                $packages = array("sudo", "nscd", "unzip", "wget", "bzip2-devel", "curl-devel", "libpng-devel", "geoip", "libjpeg-devel", "freetype-devel", "libc-client-devel.i686", "libc-client-devel", "libmcrypt-devel");
                foreach ($packages as $package) {
                    $this->exec("yum install -y " . $package);
                }
            }

            $this->exec("pkill -9 php");
            $this->exec("mkdir /home");
            $this->exec("mkdir /home/xtreamcodes");
            $this->exec("mkdir " . IPTV_PANEL_DIR);
            $this->exec("mkdir " . IPTV_PANEL_DIR . "tmp");
            $this->exec("/usr/sbin/useradd -s /sbin/nologin -U -d " . MAIN_DIR . " -m xtreamcodes");
            $this->exec("pkill -9 nginx");
            $this->exec("rm -f /home/xtreamcodes/iptv_xtream_codes/php/php5-fpm.sock");
            if ($this->repo == "apt-get") {
                $this->exec("wget -qO \"" . IPTV_PANEL_DIR . "platform.zip\" \"http://xtream-codes.com/downloads/IPTV_PLATFORM.zip\"");
            } else {
                $this->exec("wget -qO \"" . IPTV_PANEL_DIR . "platform.zip\" \"http://xtream-codes.com/downloads/IPTV_PLATFORMrh.zip\"");
            }
            $this->exec("unzip -o \"" . IPTV_PANEL_DIR . "platform.zip\" -d \"" . IPTV_PANEL_DIR . "\"");
            $this->exec("rm -rf \"" . IPTV_PANEL_DIR . "platform.zip\"");
            $this->Send($file, IPTV_PANEL_DIR . basename($file), 493);
            $this->exec("tar xzvf " . IPTV_PANEL_DIR . basename($file) . " -C " . IPTV_PANEL_DIR . " --overwrite");
            $this->exec("rm -rf " . IPTV_PANEL_DIR . basename($file));
            if ($this->repo == "apt-get") {
                $this->exec("echo '#!/bin/sh -' > /etc/init.d/xtreamcodeslb");
                $nginx_bin = $this->exec("cat /etc/init.d/xtreamcodeslb | grep -v grep | grep -c 'iptv_xtream_codes/nginx/sbin/nginx'");
                if ($nginx_bin == 0) {
                    $this->exec("echo '" . IPTV_PANEL_DIR . "nginx/sbin/nginx' >> /etc/init.d/xtreamcodeslb");
                }
                $phpfpm_bin = $this->exec("cat /etc/init.d/xtreamcodeslb | grep -v grep | grep -c 'iptv_xtream_codes/php/sbin/php-fpm'");
                if ($phpfpm_bin == 0) {
                    $this->exec("echo '" . IPTV_PANEL_DIR . "php/sbin/php-fpm' >> /etc/init.d/xtreamcodeslb");
                }
                $this->exec("chmod +x /etc/init.d/xtreamcodeslb && update-rc.d xtreamcodeslb defaults");
            } else {
                $nginx_bin = $this->exec("cat /etc/rc.d/rc.local | grep -v grep | grep -c 'iptv_xtream_codes/nginx/sbin/nginx'");
                if ($nginx_bin == 0) {
                    $this->exec("echo '" . IPTV_PANEL_DIR . "nginx/sbin/nginx' >> /etc/rc.d/rc.local");
                }
                $phpfpm_bin = $this->exec("cat /etc/rc.d/rc.local | grep -v grep | grep -c 'iptv_xtream_codes/php/sbin/php-fpm'");
                if ($phpfpm_bin == 0) {
                    $this->exec("echo '" . IPTV_PANEL_DIR . "php/sbin/php-fpm' >> /etc/rc.d/rc.local");
                }
                $this->exec("chmod +x /etc/rc.d/rc.local");
            }
            $nginx_sudo = $this->exec("cat /etc/sudoers | grep -v grep | grep -c 'iptv_xtream_codes/nginx/sbin/nginx'");
            if ($nginx_sudo == 0) {
                $this->exec("echo 'xtreamcodes ALL = (root) NOPASSWD: " . IPTV_PANEL_DIR . "nginx/sbin/nginx' >> /etc/sudoers");
            }
            $phpfpm_sudo = $this->exec("cat /etc/sudoers | grep -v grep | grep -c 'iptv_xtream_codes/php/sbin/php-fpm'");
            if ($phpfpm_sudo == 0) {
                $this->exec("echo 'xtreamcodes ALL = (root) NOPASSWD: " . IPTV_PANEL_DIR . "php/sbin/php-fpm' >> /etc/sudoers");
            }
            $this->exec("ln -s " . IPTV_PANEL_DIR . "bin/ffmpeg /usr/bin/");
            $this->exec("ln -s " . IPTV_PANEL_DIR . "bin/ffprobe /usr/bin/");
            $this->exec("sed -i 's/listen {http_broad_cast_port};/listen " . $this->http_port . ";/g' \"" . IPTV_PANEL_DIR . "nginx/conf/nginx.conf" . "\"");
            $this->exec("pkill -9 ffprobe");
            $this->exec("pkill -9 ffmpeg");
            $this->exec("rm -rf " . IPTV_PANEL_DIR . "streams/*");
            $this->exec("umount -l " . IPTV_PANEL_DIR . "streams");
            $this->exec("awk '!/iptv_xtream_codes/' /etc/fstab > /tmp/temp_file && mv /tmp/temp_file /etc/fstab");
            $this->exec("echo 'tmpfs " . IPTV_PANEL_DIR . "streams tmpfs defaults,noatime,nosuid,nodev,noexec,mode=1777,size=90% 0 0' >> /etc/fstab");
            $this->exec("mount -a");
            $this->exec("chown xtreamcodes:xtreamcodes -R /home/xtreamcodes");
            $this->exec("chown xtreamcodes:xtreamcodes -R /sys");
            $this->exec("chmod -R 777 /home/xtreamcodes");
            $this->exec("service cron restart");
            $this->exec(IPTV_PANEL_DIR . "nginx/sbin/nginx");
            $this->exec(IPTV_PANEL_DIR . "php/sbin/php-fpm");
            $this->Create_LB_DB_Config();
        }
        return false;
    }

    public function Create_LB_DB_Config()
    {
        global $_INFO;
        $code = "<?php\n";
        $code .= "\$_INFO = array();\r\n";
        $code .= "\$_INFO['host'] = \"" . $this->MainIP . "\";\r\n";
        $code .= "\$_INFO['db_user'] = \"" . $_INFO["db_user"] . "_" . $this->ServerID . "\";\r\n";
        $code .= "\$_INFO['db_pass'] = \"" . $_INFO["db_pass"] . "\";\r\n";
        $code .= "\$_INFO['db_name'] = \"" . $_INFO["db_name"] . "\";\r\n";
        $code .= "define( 'SERVER_ID', " . $this->ServerID . " );\r\n";
        $code .= "?>";
        $code = base64_encode($code);
        $this->exec("echo " . $code . " | base64 --decode > " . IPTV_PANEL_DIR . "wwwdir/config.php");
    }

    public function IdentifyOS()
    {
        if (!$this->valid) {
            $this->Arch = $this->exec("uname -m");
            if (stristr($this->Arch, "64")) {
                $apt_command = $this->exec("command -v apt-get");
                $yum_command = $this->exec("command -v yum");
                $this->exec("echo 'nameserver 8.8.8.8' > /etc/resolv.conf");
                if (!empty($apt_command)) {
                    $this->exec("apt-get update -y && apt-get install lsb-release -y --force-yes");
                    $this->SystemOS = trim($this->exec("lsb_release -d -s"));
                    $this->CodeName = trim(strtolower($this->exec("lsb_release -c -s")));
                    if (!$this->InstallSources()) {
                    } else {
                        $this->exec("apt-get update");
                        $this->valid = true;
                        $this->repo = "apt-get";
                        return true;
                    }
                } else {
                    if (!empty($yum_command)) {
                        $this->exec("yum install epel-release -y > /dev/null 2>&1");
                        $this->SystemOS = $this->exec("cat /etc/redhat-release");
                        if (!stristr($this->SystemOS, "core")) {
                        } else {
                            $this->repo = "yum";
                            $this->valid = true;
                            return true;
                        }
                    }
                }
            }
            return false;
        }
        return true;
    }

    public function InstallSources()
    {
        if (empty($this->CodeName)) {
        } else {
            switch ($this->CodeName) {
                case "trusty":
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ trusty-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ trusty-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                case "vivid":
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ vivid-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ vivid-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                case "utopic":
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ utopic-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ utopic-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                case "saucy":
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy main restricted universe multiverse' > /etc/apt/sources.list.d/xtream_codes.list'");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://de.archive.ubuntu.com/ubuntu/ saucy-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-security main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-updates main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-proposed main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://de.archive.ubuntu.com/ubuntu/ saucy-backports main restricted universe multiverse' >> /etc/apt/sources.list.d/xtream_codes.list");
                case "wheezy":
                    $this->exec("echo 'deb http://ftp.de.debian.org/debian stable main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.de.debian.org/debian stable main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://ftp.debian.org/debian/ wheezy-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.debian.org/debian/ wheezy-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://security.debian.org/ wheezy/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://security.debian.org/ wheezy/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                case "squeeze":
                    $this->exec("echo 'deb http://archive.debian.org/debian oldstable main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://archive.debian.org/debian oldstable main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://ftp.debian.org/debian/ squeeze-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.debian.org/debian/ squeeze-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://security.debian.org/ squeeze/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://security.debian.org/ squeeze/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                case "jessie":
                    $this->exec("echo 'deb http://ftp.de.debian.org/debian testing main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.de.debian.org/debian testing main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                case "stretch":
                    $this->exec("echo 'deb http://ftp.de.debian.org/debian testing main contrib non-free' > /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.de.debian.org/debian testing main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://ftp.debian.org/debian/ jessie-updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    $this->exec("echo 'deb-src http://security.debian.org/ jessie/updates main contrib non-free' >> /etc/apt/sources.list.d/xtream_codes.list");
                    break;
                default:
                    return false;
            }
        }
    }

    public function run_ssh_cmd($username, $pubkeyfile, $privkeyfile = NULL, $passphrase = NULL)
    {
        if (is_file($pubkeyfile) && is_readable($pubkeyfile) && isset($privkeyfile)) {
            if (ssh2_auth_pubkey_file($this->ssh, $username, $pubkeyfile, $privkeyfile, $passphrase)) {
            } else {
                return false;
            }
        } else {
            if (ssh2_auth_password($this->ssh, $username, $pubkeyfile)) {
            } else {
                ipTV_lib::SaveLog("Could not Authenticate to the Remote SSH Service On " . $this->host_ip . ". Wrong Info provided.");
                return false;
            }
        }
        return true;
    }

    public function Send($local, $remote_file, $create_mode) /* $create_mode = 0644 */
    {
        if (ssh2_scp_send($this->ssh, $local, $remote_file, $create_mode)) {
            return true;
        }
        return false;
    }

    public function receive($remote_file, $local)
    {
        if (!ssh2_scp_recv($this->ssh, $remote_file, $local)) {
            return true;
        }
        return false;
    }

    public function exec($cmd)
    {
        $this->stream = ssh2_exec($this->ssh, $cmd);
        stream_set_blocking($this->stream, true);
        return trim(stream_get_contents($this->stream));
    }
}

?>