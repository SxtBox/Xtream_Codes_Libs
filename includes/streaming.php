<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class ipTV_Stream
{
    public static $ipTV_db = NULL;
    public static function getAllowedIPsAdmin()
    {
        $ips = array("127.0.0.1", $_SERVER["SERVER_ADDR"]);
        foreach (ipTV_lib::$StreamingServers as $server_id => $server_info) {
            $ips[] = gethostbyname($server_info["server_ip"]);
        }

        self::$ipTV_db->query("SELECT `ip` FROM `reg_users` WHERE `member_group_id` = 1 AND `last_login` >= '%d'", strtotime("-2 hour"));
        $ips = array_merge($ips, ipTV_lib::array_values_recursive(self::$ipTV_db->get_rows()));
		//$ips = array_merge($ips, ipTV_lib::array_values_recursive(self::$ipTV_db->get_rows()));
        if (!empty(ipTV_lib::$settings["allowed_ips_admin"])) {
            $ips = array_merge($ips, explode(",", ipTV_lib::$settings["allowed_ips_admin"]));
        }
        return $ips;
    }

    public static function FileParser($FileName)
    {
        if (file_exists($FileName)) {
            $streams = array();
            $need_stream_url = false;
            $fp = fopen($FileName, "r");
            while (feof($fp)) {
                return $streams;
            }
            $line = urldecode(trim(fgets($fp)));
            if (!empty($line)) {
                if (!stristr($line, "#EXTM3U")) {
                    if (stristr($line, "#EXTINF") || !$need_stream_url) {
                        if (stristr($line, "#EXTINF")) {
                            $stream_name = trim(end(explode(",", $line)));
                            $need_stream_url = true;
                        }
                    } else {
                        $streams[$stream_name] = json_encode(array($line));
                        $need_stream_url = false;
                    }
                }
            }
        } else {
            return false;
        }
    }

    public static function CanServerStream($server_id, $stream_id, $type = "live", $extension = NULL)
    {
        if ($type == "live") {
            self::$ipTV_db->query("\r\n                    SELECT *\r\n                    FROM `streams` t1\r\n                    INNER JOIN `streams_types` t4 ON t4.type_id = t1.type\r\n                    INNER JOIN `streams_sys` t2 ON t2.stream_id = t1.id AND t2.pid IS NOT NULL AND t2.server_id = '%d'\r\n                    WHERE t1.`id` = '%d'", $server_id, $stream_id);
        } else {
            self::$ipTV_db->query("\r\n                    SELECT * \r\n                    FROM `streams` t1\r\n                    INNER JOIN `streams_sys` t2 ON t2.stream_id = t1.id AND t2.pid IS NOT NULL AND t2.server_id = '%d'\r\n                    INNER JOIN `movie_containers` t3 ON t3.container_id = t1.target_container_id AND t3.container_extension = '%s'\r\n                    WHERE t1.`id` = '%d'", $server_id, $extension, $stream_id);
        }
        if (!self::$ipTV_db->num_rows()) {
            return false;
        }
        $stream_info = self::$ipTV_db->get_row();
        return $stream_info;
    }

    public static function Redirect($user_id, $user_auth, $external_device, $type)
    {
        if (count(ipTV_lib::$StreamingServers) > 1 && array_key_exists(SERVER_ID, ipTV_lib::$StreamingServers)) {
            parse_str($_SERVER["QUERY_STRING"], $query);
            $available_servers = array();
            if ($type == "live") {
                $stream_id = $query["stream"];
                $extension = $query["extension"];
                if ($extension == "m3u8") {
                    self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE container = 'hls' AND `user_id` = '%d' AND `stream_id` = '%d' AND ISNULL(`date_end`) LIMIT 1", $user_id["id"], $stream_id);
                    if (0 >= self::$ipTV_db->num_rows()) {
                    } else {
                        $activity_info = self::$ipTV_db->get_row();
                        if ($activity_info["server_id"] != SERVER_ID) {
                            $valid_time = 0;
                            $md5_hash_key = md5(ipTV_lib::$settings["live_streaming_pass"] . ipTV_lib::$StreamingServers[$activity_info["server_id"]]["server_ip"] . $user_auth . $stream_id . $query["username"] . $query["password"] . $valid_time);
                            header("Location: " . ipTV_lib::$StreamingServers[$activity_info["server_id"]]["site_url"] . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "&hash=" . $md5_hash_key . "&time=" . $valid_time . "&external_device=" . $external_device);
                            ob_end_flush();
                            exit;
                        }
                        return false;
                    }
                }
            } else {
                $stream = pathinfo($query["stream"]);
                $stream_id = intval($stream["filename"]);
                $extension = $stream["extension"];
            }
            foreach (ipTV_lib::$StreamingServers as $servers_ID => $server_info) {
                if (isset($query["stream"]) && self::CanServerStream($servers_ID, $stream_id, $type, isset($extension) ? $extension : NULL)) {
                    $available_servers[] = $servers_ID;
                }
            }
            if (!empty($available_servers)) {
                self::$ipTV_db->query("SELECT a.server_id, SUM(ISNULL(a.date_end)) AS online_clients FROM `user_activity_now` a WHERE a.server_id IN (" . implode(",", $available_servers) . ") GROUP BY a.server_id ORDER BY online_clients ASC");
                $CanAcceptCons = array();
                foreach (self::$ipTV_db->get_rows() as $row) {
                    if ($row["online_clients"] < ipTV_lib::$StreamingServers[$row["server_id"]]["total_clients"]) {
                        $CanAcceptCons[$row["server_id"]] = $row["online_clients"];
                    } else {
                        $CanAcceptCons[$row["server_id"]] = false;
                    }
                }
                foreach (array_keys(ipTV_lib::$StreamingServers) as $server_id) {
                    if (in_array($server_id, $available_servers)) {
                        if (!array_key_exists($server_id, $CanAcceptCons)) {
                            if (0 < ipTV_lib::$StreamingServers[$server_id]["total_clients"]) {
                                $CanAcceptCons[$server_id] = 0;
                            } else {
                                $CanAcceptCons[$server_id] = false;
                            }
                        }
                    }
                }
                $CanAcceptCons = array_filter($CanAcceptCons, "is_numeric");
                foreach (array_keys($CanAcceptCons) as $server_id) {
                    if ($server_id != SERVER_ID) {
                        if (ipTV_lib::$StreamingServers[$server_id]["status"] != 1) {
                            unset($CanAcceptCons[$server_id]);
                        }
                    }
                }
                if (!empty($CanAcceptCons)) {
                    $split_clients = ipTV_lib::$settings["split_clients"];
                    if ($split_clients == "equal") {
                        $keys = array_keys($CanAcceptCons);
                        $values = array_values($CanAcceptCons);
                        array_multisort($values, SORT_ASC, $keys, SORT_ASC);
                        $CanAcceptCons = array_combine($keys, $values);
                        $redirect_server_ip = key($CanAcceptCons);
                    } else {
                        $keys = array_keys($CanAcceptCons);
                        $values = array_values($CanAcceptCons);
                        array_multisort($values, SORT_ASC, $keys, SORT_DESC);
                        $CanAcceptCons = array_combine($keys, $values);
                        end($CanAcceptCons);
                        $redirect_server_ip = key($CanAcceptCons);
                    }
                    if ($user_id["force_server_id"] != 0 && array_key_exists($user_id["force_server_id"], $CanAcceptCons)) {
                        $redirect_server_ip = $user_id["force_server_id"];
                    }
                    if ($redirect_server_ip == SERVER_ID) {
                    } else {
                        if ($extension == "m3u8") {
                            $valid_time = 0;
                        } else {
                            $valid_time = time() + 10;
                        }

                        $md5_hash_key = md5(ipTV_lib::$settings["live_streaming_pass"] . ipTV_lib::$StreamingServers[$redirect_server_ip]["server_ip"] . $user_auth . $stream_id . $query["username"] . $query["password"] . $valid_time);
                        header("Location: " . ipTV_lib::$StreamingServers[$redirect_server_ip]["site_url"] . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "&hash=" . $md5_hash_key . "&time=" . $valid_time . "&external_device=" . $external_device);
                        ob_end_flush();
                        exit;
                    }
                }
                return false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function GetUserInfo($user_id = NULL, $username = NULL, $password = NULL, $get_ChannelIDS = false, $get_Bouquet_Info = false, $get_cons = false)
    {
        if (empty($user_id)) {
            self::$ipTV_db->query("SELECT * FROM `users` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1", $username, $password);
        } else {
            self::$ipTV_db->query("SELECT * FROM `users` WHERE `id` = '%d'", $user_id);
        }
        if (0 >= self::$ipTV_db->num_rows()) {
            return false;
        }
        $user_id = self::$ipTV_db->get_row();
        $user_id["bouquet"] = json_decode($user_id["bouquet"], true);
        $user_id["allowed_ips"] = json_decode($user_id["allowed_ips"], true);
        $user_id["allowed_ua"] = json_decode($user_id["allowed_ua"], true);
        if ($get_cons) {
            self::$ipTV_db->query("SELECT COUNT(`activity_id`) FROM `user_activity_now` WHERE `user_id` = '%d'", $user_id["id"]);
            $user_id["active_cons"] = self::$ipTV_db->get_col();
            $user_id["pair_line_info"] = array();
            if (!is_null($user_id["pair_id"]) && RowExists("users", "id", $user_id["pair_id"])) {
                self::$ipTV_db->query("SELECT COUNT(`activity_id`) FROM `user_activity_now` WHERE `user_id` = '%d'", $user_id["pair_id"]);
                $user_id["pair_line_info"]["active_cons"] = self::$ipTV_db->get_col();
                self::$ipTV_db->query("SELECT max_connections FROM `users` WHERE `id` = '%d'", $user_id["pair_id"]);
                $user_id["pair_line_info"]["max_connections"] = self::$ipTV_db->get_col();
            }
        } else {
            $user_id["active_cons"] = "N/A";
        }
        if ($user_id["is_mag"] == 1) {
            self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `user_id` = '%d' LIMIT 1", $user_id["id"]);
            if (0 < self::$ipTV_db->num_rows()) {
                $user_id["mag_device"] = self::$ipTV_db->get_row();
            }
        }
        self::$ipTV_db->query("SELECT *\r\n                                    FROM `access_output` t1\r\n                                    INNER JOIN `user_output` t2 ON t1.access_output_id = t2.access_output_id\r\n                                    WHERE t2.user_id = '%d'", $user_id["id"]);
        $user_id["output_formats"] = self::$ipTV_db->get_rows(true, "output_ext");
        if ($get_ChannelIDS) {
            $channel_ids = array();
            self::$ipTV_db->query("SELECT `bouquet_channels` FROM `bouquets` WHERE `id` IN (" . implode(",", $user_id["bouquet"]) . ")");
            foreach (self::$ipTV_db->get_rows() as $row) {
                $channel_ids = array_merge($channel_ids, json_decode($row["bouquet_channels"], true));
            }
            $user_id["channel_ids"] = array_unique($channel_ids);
            $user_id["channels"] = array();
            if ($get_Bouquet_Info && !empty($user_id["channel_ids"])) {
                self::$ipTV_db->query("SELECT t1.*,t2.*,t3.category_name,t4.*\r\n                                            FROM `streams` t1 \r\n                                            LEFT JOIN  `stream_categories` t3 on t3.id = t1.category_id\r\n                                            INNER JOIN `streams_types` t2 ON t2.type_id = t1.type \r\n                                            LEFT JOIN `movie_containers` t4 ON t4.container_id = t1.target_container_id\r\n                                            WHERE t1.`id` IN(" . implode(",", $user_id["channel_ids"]) . ") \r\n                                            ORDER BY FIELD(t1.id, " . implode(",", $user_id["channel_ids"]) . ");");
                $user_id["channels"] = self::$ipTV_db->get_rows();
            }
        }
        return $user_id;
    }

    public static function GetMagInfo($mag_id = NULL, $mac = NULL, $get_ChannelIDS = false, $get_Bouquet_Info = false, $get_cons = false)
    {
        if (empty($mag_id)) {
            self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `mac` = '%s'", base64_encode($mac));
        } else {
            self::$ipTV_db->query("SELECT * FROM `mag_devices` WHERE `mag_id` = '%d'", $mag_id);
        }
        if (0 >= self::$ipTV_db->num_rows()) {
            return false;
        }

        $mag_data = array();
        $mag_data["mag_device"] = self::$ipTV_db->get_row();
        $mag_data["mag_device"]["mac"] = base64_decode($mag_data["mag_device"]["mac"]);
        $mag_data["mag_device"]["ver"] = base64_decode($mag_data["mag_device"]["ver"]);
        $mag_data["mag_device"]["device_id"] = base64_decode($mag_data["mag_device"]["device_id"]);
        $mag_data["mag_device"]["device_id2"] = base64_decode($mag_data["mag_device"]["device_id2"]);
        $mag_data["mag_device"]["hw_version"] = base64_decode($mag_data["mag_device"]["hw_version"]);
        $mag_data["user_info"] = array();
        if ($user_id = self::GetUserInfo($mag_data["mag_device"]["user_id"], NULL, NULL, $get_ChannelIDS, $get_Bouquet_Info, $get_cons)) {
            $mag_data["user_info"] = $user_id;
        }
        $mag_data["pair_line_info"] = array();
        if (!empty($mag_data["user_info"])) {
            $mag_data["pair_line_info"] = array();
            if (!is_null($mag_data["user_info"]["pair_id"])) {
                if ($user_id = self::GetUserInfo($mag_data["user_info"]["pair_id"], NULL, NULL, $get_ChannelIDS, $get_Bouquet_Info, $get_cons)) {
                    $mag_data["pair_line_info"] = $user_id;
                }
            }
        }
        return $mag_data;
    }

    public static function CloseLastCon($user_id)
    {
        self::$ipTV_db->query("SELECT activity_id,server_id,pid FROM `user_activity_now` WHERE `user_id` = '%d' ORDER BY activity_id DESC LIMIT 1", $user_id);
        if (0 >= self::$ipTV_db->num_rows()) {
            return false;
        }
        $info = self::$ipTV_db->get_row();
        ipTV_Servers::RunCommandServer($info["server_id"], "kill -9 " . $info["pid"]);
        self::CloseAndTransfer($info["activity_id"]);
        return true;
    }

    public static function GetChannelsByBouquet($bouquet_id)
    {
        if (is_array($bouquet_id) && !empty($bouquet_id)) {
            $bouquet_id = array_map("intval", $bouquet_id);
            $bouquet_ids = array();
            self::$ipTV_db->query("SELECT bouquet_channels FROM `bouquets` WHERE `id` IN (" . implode(",", $bouquet_id) . ")");
            foreach (self::$ipTV_db->get_rows() as $row) {
                $bouquet_ids = array_merge($bouquet_ids, json_decode($row["bouquet_channels"], true));
            }
            $bouquet_ids = array_unique($bouquet_ids);
            sort($bouquet_ids);
            self::$ipTV_db->query("SELECT * FROM `streams` WHERE `id` IN (" . implode(",", $bouquet_ids) . ") ORDER BY `stream_display_name` ASC");
            return self::$ipTV_db->get_rows();
        } else {
            return array();
        }
    }

    public static function getMAGLog($mag_id, $action)
    {
        if (!is_numeric($mag_id) || empty($mag_id)) {
            $mag_id = "NULL";
        }
        self::$ipTV_db->query("INSERT INTO `mag_logs` (`mag_id`,`action`) VALUES(%s,'%s')", $mag_id, $action);
    }

    public static function getClientLog($stream_id, $user_id, $action, $user_ip, $data = "")
    {
        $user_agent = !empty($_SERVER["HTTP_USER_AGENT"]) ? htmlentities($_SERVER["HTTP_USER_AGENT"]) : "";
        $query_string = empty($_SERVER["QUERY_STRING"]) ? "" : $_SERVER["QUERY_STRING"];
        $data = array(
		"user_id" => $user_id,
		"stream_id" => $stream_id,
		"action" => $action,
		"query_string" => htmlentities($_SERVER["QUERY_STRING"]),
		"user_agent" => $user_agent,
		"user_ip" => $user_ip,
		"time" => time(),
		"extra_data" => $data);
        file_put_contents(TMP_DIR . "client_request.log", base64_encode(json_encode($data)) . "\n", FILE_APPEND);
    }

    public static function ClientConnected()
    {
        if (!(connection_status() != CONNECTION_NORMAL || connection_aborted())) {
            return true;
        }
        return false;
    }

    public static function Get_Playlist_Segments($playlist, $prebuffer = 0)
    {
        if (file_exists($playlist)) {
            $source = file_get_contents($playlist);
            if (!preg_match_all("/(.*?).ts/", $source, $matches)) {
            } else {
                if (0 >= $prebuffer) {
                    return $matches[0];
                }
                $total_segments = intval($prebuffer / 10);
                return array_slice($matches[0], 0 - $total_segments);
            }
        }
        return false;
    }

    public static function Generate_Authentication_PlayList($m3u8_playlist, $username = "", $password = "", $stream)
    {
        if (!file_exists($m3u8_playlist)) {
        } else {
            $source = file_get_contents($m3u8_playlist);
            if (!preg_match_all("/(.*?)\\.ts/", $source, $matches)) {
                return false;
            }
            foreach ($matches[0] as $match) {
                $source = str_replace($match, "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"] . "?extension=m3u8&username=" . $username . "&password=" . $password . "&stream=" . $stream . "&type=hls&segment=" . $match, $source);
            }
            return $source;
        }
    }

    public static function Check_Global_Blocked_UA($user_agent)
    {
        $user_agent = self::$ipTV_db->escape($user_agent);
        self::$ipTV_db->simple_query("SELECT * FROM `blocked_user_agents` WHERE (exact_match = 1 AND user_agent = '" . $user_agent . "') OR (exact_match = 0 AND INSTR('" . $user_agent . "',user_agent) > 0)");
        if (0 >= self::$ipTV_db->num_rows()) {
        } else {
            $info = self::$ipTV_db->get_row();
            self::$ipTV_db->query("UPDATE `blocked_user_agents` SET `attempts_blocked` = `attempts_blocked`+1 WHERE `id` = '%d'", $info["id"]);
            exit;
        }
    }
    public static function ps_running($pid, $exe)
    {
        if (!empty($pid)) {
            if (!(file_exists("/proc/" . $pid) && is_readable("/proc/" . $pid . "/exe") && basename(readlink("/proc/" . $pid . "/exe")) == basename($exe))) {
                return false;
            }
            return true;
        }
        return false;
    }

    public static function ShowVideo($is_restreamer = 0, $video_id_setting, $video_path_id)
    {
        if ($is_restreamer == 0 && ipTV_lib::$settings[$video_id_setting] == 1) {
            header("Content-Type: video/mp2t");
            readfile(ipTV_lib::$settings[$video_path_id]);
        }
        exit;
    }

    public static function CloseConnection($activity_id)
    {
        self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE `activity_id` = '%d'", $activity_id);
        if (0 < self::$ipTV_db->num_rows()) {
            $info = self::$ipTV_db->get_row();
            if (!is_null($info["pid"])) {
                ipTV_Servers::RunCommandServer($info["server_id"], "kill -9 " . $info["pid"]);
                self::CloseAndTransfer($activity_id);
            }
        }
    }

    public static function CloseAndTransfer($activity_id)
    {
        if (!is_array($activity_id)) {
            $activity_id = array(intval($activity_id));
        }
        foreach ($activity_id as $id) {
            self::$ipTV_db->query("INSERT INTO `user_activity` SELECT NULL,`user_id`,`stream_id`,`server_id`,`user_agent`,`user_ip`,`container`,NULL,`date_start`,'" . time() . "',`geoip_country_code`,`isp`,`external_device`,`divergence`,NULL,NULL FROM `user_activity_now` WHERE `activity_id` = '%d'", $id);
            self::$ipTV_db->query("DELETE FROM `user_activity_now` WHERE `activity_id` = '%d'", $id);
        }
    }

    public static function Close_All_Connections_By_User($user_id)
    {
        self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE `user_id` = '%d'", $user_id);
        if (0 < self::$ipTV_db->num_rows()) {
            $rows = self::$ipTV_db->get_rows();
            $server_ids = array();
            $activity_ids = array();
            foreach ($rows as $row) {
                if (empty($server_ids[$row["server_id"]])) {
                    $server_ids[$row["server_id"]] = array();
                }
                $server_ids[$row["server_id"]][] = $row["pid"];
                $activity_ids[] = $row["activity_id"];
            }
            foreach ($server_ids as $server_id => $pid) {
                $command = "kill -9 " . implode(" ", $pid);
                ipTV_Servers::RunCommandServer($server_id, $command);
            }
            self::CloseAndTransfer($activity_ids);
        }
    }

    public static function Close_All_Connections_By_Server($server_id)
    {
        self::$ipTV_db->query("SELECT * FROM `user_activity_now` WHERE `server_id` = '%d'", $server_id);
        if (0 < self::$ipTV_db->num_rows()) {
            $rows = self::$ipTV_db->get_rows();
            $pids = array();
            $activity_ids = array();
            foreach ($rows as $row) {
                $pids[] = $row["pid"];
                $activity_ids[] = $row["activity_id"];
            }
            $command = "kill -9 " . implode(" ", $pids);
            ipTV_Servers::RunCommandServer($server_id, $command);
            self::CloseAndTransfer($activity_ids);
        }
    }

    public static function IsValidStream($playlist, $pid)
    {
        return self::ps_running($pid, FFMPEG_PATH) && file_exists($playlist);
    }

    public static function getUserIP()
    {
        foreach (array("HTTP_CF_CONNECTING_IP", "HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_X_CLUSTER_CLIENT_IP", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED", "REMOTE_ADDR") as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(",", $_SERVER[$key]) as $ip_address) {
                    $ip_address = trim($ip_address);
                    if (filter_var($ip_address, FILTER_VALIDATE_IP) === false) {
                    } else {
                        return $ip_address;
                    }
                }
            }
        }
    }

    public static function Get_Stream_Bitrate($type, $path, $force_duration = NULL)
    {

        $bitrate = 0;
        if (file_exists($path)) {
            switch ($type) {
                case "movie":
                    if (!is_null($force_duration)) {
                        sscanf($force_duration, "%d:%d:%d", $hours, $minutes, $seconds);
                        $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
                        $bitrate = round(filesize($path) * 0.008 / $time_seconds);
                    }
                    break;

                case "live":
                    $fp = fopen($path, "r");
                    $bitrates = array();
                    while (feof($fp)) {
                    }
                    fclose($fp);
                    $bitrate = 0 < count($bitrates) ? round(array_sum($bitrates) / count($bitrates)) : 0;
                    break;
            }
            return $bitrate;
        }
        return $bitrate;
    }
}

?>