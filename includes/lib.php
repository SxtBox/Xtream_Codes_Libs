<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class ipTV_lib
{
    public static $request = array();
    public static $ipTV_db = NULL;
    public static $settings = array();
    public static $licence = "";
    public static $StreamingServers = array();
    public static $SegmentsSettings = array();
    public static $countries = array();

    public static function init()
    {
		#clean global vars
        if (!empty($_GET)) {
            self::cleanGlobals($_GET);
        }
        if (!empty($_POST)) {
            self::cleanGlobals($_POST);
        }
        if (!empty($_SESSION)) {
            self::cleanGlobals($_SESSION);
        }
        if (!empty($_COOKIE)) {
            self::cleanGlobals($_COOKIE);
        }
		# GET first
        $input = @self::parseIncomingRecursively($_GET, array());
		# Then overwrite with POST
        self::$request = @self::parseIncomingRecursively($_POST, $input);
		#Get Settings
        self::GetSettings();
		#Get GetLicence
        self::GetLicence();

        self::$StreamingServers = self::GetServers();
        self::$SegmentsSettings = self::calculateSegNumbers();

        if (empty($argc) && !empty($_SERVER["SERVER_PORT"]) && self::$StreamingServers[SERVER_ID]["http_broadcast_port"] != $_SERVER["SERVER_PORT"]) {
            self::$ipTV_db->query("UPDATE streaming_servers SET `http_broadcast_port` = '%d' WHERE `id` = '%d'", $_SERVER["SERVER_PORT"], SERVER_ID);
        }
        self::$countries = array("A1" => "Anonymous Proxy", "A2" => "Satellite Provider", "O1" => "Other Country", "AF" => "Afghanistan", "AX" => "Aland Islands", "AL" => "Albania", "DZ" => "Algeria", "AS" => "American Samoa", "AD" => "Andorra", "AO" => "Angola", "AI" => "Anguilla", "AQ" => "Antarctica", "AG" => "Antigua And Barbuda", "AR" => "Argentina", "AM" => "Armenia", "AW" => "Aruba", "AU" => "Australia", "AT" => "Austria", "AZ" => "Azerbaijan", "BS" => "Bahamas", "BH" => "Bahrain", "BD" => "Bangladesh", "BB" => "Barbados", "BY" => "Belarus", "BE" => "Belgium", "BZ" => "Belize", "BJ" => "Benin", "BM" => "Bermuda", "BT" => "Bhutan", "BO" => "Bolivia", "BA" => "Bosnia And Herzegovina", "BW" => "Botswana", "BV" => "Bouvet Island", "BR" => "Brazil", "IO" => "British Indian Ocean Territory", "BN" => "Brunei Darussalam", "BG" => "Bulgaria", "BF" => "Burkina Faso", "BI" => "Burundi", "KH" => "Cambodia", "CM" => "Cameroon", "CA" => "Canada", "CV" => "Cape Verde", "KY" => "Cayman Islands", "CF" => "Central African Republic", "TD" => "Chad", "CL" => "Chile", "CN" => "China", "CX" => "Christmas Island", "CC" => "Cocos (Keeling) Islands", "CO" => "Colombia", "KM" => "Comoros", "CG" => "Congo", "CD" => "Congo, Democratic Republic", "CK" => "Cook Islands", "CR" => "Costa Rica", "CI" => "Cote D'Ivoire", "HR" => "Croatia", "CU" => "Cuba", "CY" => "Cyprus", "CZ" => "Czech Republic", "DK" => "Denmark", "DJ" => "Djibouti", "DM" => "Dominica", "DO" => "Dominican Republic", "EC" => "Ecuador", "EG" => "Egypt", "SV" => "El Salvador", "GQ" => "Equatorial Guinea", "ER" => "Eritrea", "EE" => "Estonia", "ET" => "Ethiopia", "FK" => "Falkland Islands (Malvinas)", "FO" => "Faroe Islands", "FJ" => "Fiji", "FI" => "Finland", "FR" => "France", "GF" => "French Guiana", "PF" => "French Polynesia", "TF" => "French Southern Territories", "MK" => "Fyrom", "GA" => "Gabon", "GM" => "Gambia", "GE" => "Georgia", "DE" => "Germany", "GH" => "Ghana", "GI" => "Gibraltar", "GR" => "Greece", "GL" => "Greenland", "GD" => "Grenada", "GP" => "Guadeloupe", "GU" => "Guam", "GT" => "Guatemala", "GG" => "Guernsey", "GN" => "Guinea", "GW" => "Guinea-Bissau", "GY" => "Guyana", "HT" => "Haiti", "HM" => "Heard Island & Mcdonald Islands", "VA" => "Holy See (Vatican City State)", "HN" => "Honduras", "HK" => "Hong Kong", "HU" => "Hungary", "IS" => "Iceland", "IN" => "India", "ID" => "Indonesia", "IR" => "Iran, Islamic Republic Of", "IQ" => "Iraq", "IE" => "Ireland", "IM" => "Isle Of Man", "IL" => "Israel", "IT" => "Italy", "JM" => "Jamaica", "JP" => "Japan", "JE" => "Jersey", "JO" => "Jordan", "KZ" => "Kazakhstan", "KE" => "Kenya", "KI" => "Kiribati", "KR" => "Korea", "KW" => "Kuwait", "KG" => "Kyrgyzstan", "LA" => "Lao People's Democratic Republic", "LV" => "Latvia", "LB" => "Lebanon", "LS" => "Lesotho", "LR" => "Liberia", "LY" => "Libyan Arab Jamahiriya", "LI" => "Liechtenstein", "LT" => "Lithuania", "LU" => "Luxembourg", "MO" => "Macao", "MG" => "Madagascar", "MW" => "Malawi", "MY" => "Malaysia", "MV" => "Maldives", "ML" => "Mali", "MT" => "Malta", "MH" => "Marshall Islands", "MQ" => "Martinique", "MR" => "Mauritania", "MU" => "Mauritius", "YT" => "Mayotte", "MX" => "Mexico", "FM" => "Micronesia, Federated States Of", "MD" => "Moldova", "MC" => "Monaco", "MN" => "Mongolia", "ME" => "Montenegro", "MS" => "Montserrat", "MA" => "Morocco", "MZ" => "Mozambique", "MM" => "Myanmar", "NA" => "Namibia", "NR" => "Nauru", "NP" => "Nepal", "NL" => "Netherlands", "AN" => "Netherlands Antilles", "NC" => "New Caledonia", "NZ" => "New Zealand", "NI" => "Nicaragua", "NE" => "Niger", "NG" => "Nigeria", "NU" => "Niue", "NF" => "Norfolk Island", "MP" => "Northern Mariana Islands", "NO" => "Norway", "OM" => "Oman", "PK" => "Pakistan", "PW" => "Palau", "PS" => "Palestinian Territory, Occupied", "PA" => "Panama", "PG" => "Papua New Guinea", "PY" => "Paraguay", "PE" => "Peru", "PH" => "Philippines", "PN" => "Pitcairn", "PL" => "Poland", "PT" => "Portugal", "PR" => "Puerto Rico", "QA" => "Qatar", "RE" => "Reunion", "RO" => "Romania", "RU" => "Russian Federation", "RW" => "Rwanda", "BL" => "Saint Barthelemy", "SH" => "Saint Helena", "KN" => "Saint Kitts And Nevis", "LC" => "Saint Lucia", "MF" => "Saint Martin", "PM" => "Saint Pierre And Miquelon", "VC" => "Saint Vincent And Grenadines", "WS" => "Samoa", "SM" => "San Marino", "ST" => "Sao Tome And Principe", "SA" => "Saudi Arabia", "SN" => "Senegal", "RS" => "Serbia", "SC" => "Seychelles", "SL" => "Sierra Leone", "SG" => "Singapore", "SK" => "Slovakia", "SI" => "Slovenia", "SB" => "Solomon Islands", "SO" => "Somalia", "ZA" => "South Africa", "GS" => "South Georgia And Sandwich Isl.", "ES" => "Spain", "LK" => "Sri Lanka", "SD" => "Sudan", "SR" => "Suriname", "SJ" => "Svalbard And Jan Mayen", "SZ" => "Swaziland", "SE" => "Sweden", "CH" => "Switzerland", "SY" => "Syrian Arab Republic", "TW" => "Taiwan", "TJ" => "Tajikistan", "TZ" => "Tanzania", "TH" => "Thailand", "TL" => "Timor-Leste", "TG" => "Togo", "TK" => "Tokelau", "TO" => "Tonga", "TT" => "Trinidad And Tobago", "TN" => "Tunisia", "TR" => "Turkey", "TM" => "Turkmenistan", "TC" => "Turks And Caicos Islands", "TV" => "Tuvalu", "UG" => "Uganda", "UA" => "Ukraine", "AE" => "United Arab Emirates", "GB" => "United Kingdom", "US" => "United States", "UM" => "United States Outlying Islands", "UY" => "Uruguay", "UZ" => "Uzbekistan", "VU" => "Vanuatu", "VE" => "Venezuela", "VN" => "Viet Nam", "VG" => "Virgin Islands, British", "VI" => "Virgin Islands, U.S.", "WF" => "Wallis And Futuna", "EH" => "Western Sahara", "YE" => "Yemen", "ZM" => "Zambia", "ZW" => "Zimbabwe");
    }

    public static function calculateSegNumbers()
    {
        $segments_settings = array();
        $segments_settings["seg_time"] = 10;
        $segments_settings["seg_list_size"] = 6;
        return $segments_settings;
    }

	static public function isValidMAC($mac)
	{
		return preg_match("/^([a-fA-F0-9]{2}:){5}[a-fA-F0-9]{2}$/", $mac) == 1;
	}

    public static function GetSettings()
    {
        self::$ipTV_db->query("SELECT * FROM `settings`");
        $rows = self::$ipTV_db->get_row();
        foreach ($rows as $key => $val) {
            self::$settings[$key] = $val;
        }
        self::$settings["allow_countries"] = json_decode(self::$settings["allow_countries"], true);
        if (array_key_exists("bouquet_name", self::$settings)) {
            self::$settings["bouquet_name"] = str_replace(" ", "_", self::$settings["bouquet_name"]);
        }
    }

    public static GetServers()
    {
        self::$ipTV_db->query("SELECT * FROM `streaming_servers`");
        $servers = array();
        foreach (self::$ipTV_db->get_rows() as $row) {
            if (!empty($row["vpn_ip"]) && inet_pton($row["vpn_ip"]) !== false) {
                $url = $row["vpn_ip"];
            } else {
                if (empty($row["domain_name"])) {
                    $url = $row["server_ip"];
                } else {
                    $url = str_replace(array("http://", "/"), "", $row["domain_name"]);
                }
            }
            $row["api_url"] = "http://" . $url . ":" . $row["http_broadcast_port"] . "/api.php?password=" . ipTV_lib::$settings["live_streaming_pass"];
            $row["site_url"] = "http://" . $url . ":" . $row["http_broadcast_port"] . "/";
            $row["api_url_ip"] = "http://" . $row["server_ip"] . ":" . $row["http_broadcast_port"] . "/api.php?password=" . ipTV_lib::$settings["live_streaming_pass"];
            $row["site_url_ip"] = "http://" . $row["server_ip"] . ":" . $row["http_broadcast_port"] . "/";
            $row["ssh_password"] = self::mc_decrypt($row["ssh_password"], md5(self::$settings["unique_id"]));
            $servers[$row["id"]] = $row;
        }
        return $servers;
    }


    public static function GetFFmpegArguments($parse_StreamArguments = array(), $add_default = true)
    {
        global $_LANG;
        self::$ipTV_db->query("SELECT * FROM `streams_arguments`");

        $rows = array();
        if (0 < self::$ipTV_db->num_rows()) {
            foreach (self::$ipTV_db->get_rows() as $row) {
                if (array_key_exists($row["id"], $parse_StreamArguments)) {
                    if (count($parse_StreamArguments[$row["id"]]) == 2) {
                        $value = $parse_StreamArguments[$row["id"]]["val"];
                    } else {
                        $value = $parse_StreamArguments[$row["id"]]["value"];
                    }
                } else {
                    $value = $add_default ? $row["argument_default_value"] : "";
                }
                if ($row["argument_type"] == "radio") {
                    if (is_null($value) || 0 < $value) {
                        $no = false;
                        $yes = true;
                    } else {
                        $no = true;
                        $yes = false;
                    }
                    if ($yes) {
                        $mode = "<input type=\"radio\" name=\"arguments[" . $row["id"] . "]\" value=\"1\" checked/> " . $_LANG["yes"] . " <input type=\"radio\" name=\"arguments[" . $row["id"] . "]\" value=\"0\" /> . " . $_LANG["no"];
                    } else {
                        $mode = "<input type=\"radio\" name=\"arguments[" . $row["id"] . "]\" value=\"1\" /> " . $_LANG["yes"] . " <input type=\"radio\" name=\"arguments[" . $row["id"] . "]\" value=\"0\" checked/> . " . $_LANG["no"];
                    }
                } else {
                    if ($row["argument_type"] == "text") {
                        $mode = "<input type=\"text\" name=\"arguments[" . $row["id"] . "]\" value=\"" . $value . "\" />";
                    }
                }
                $row["mode"] = $mode;
                $rows[$row["id"]] = $row;
            }
        }
        return $rows;
    }

    public static function mc_encrypt($encrypt_data, $key)
    {
        $encrypt_data = serialize($encrypt_data);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack("H*", $key);
        $mac = hash_hmac("sha256", $encrypt_data, substr(bin2hex($key), -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt_data . $mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passcrypt) . "|" . base64_encode($iv);
        return $encoded;
    }

    public static function mc_decrypt($decrypt_data, $key)
    {
        $decrypt_data = explode("|", $decrypt_data . "|");
        $decoded = base64_decode($decrypt_data[0]);
        $iv = base64_decode($decrypt_data[1]);
        if (strlen($iv) === mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
            $key = pack("H*", $key);
            $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
            $mac = substr($decrypted, -64);
            $decrypted = substr($decrypted, 0, -64);
            $calcmac = hash_hmac("sha256", $decrypted, substr(bin2hex($key), -32));
            if ($calcmac === $mac) {
                $decrypted = unserialize($decrypted);
                return $decrypted;
            }
            return false;
        }
        return false;
    }

    public static function formatOffset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = 0 < $hours ? "+" : "-";
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);
        if ($hour == 0 && $minutes == 0) {
            $sign = " ";
        }
        return $sign . str_pad($hour, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0");
    }

    public static function GetTimeZones($current = NULL)
    {
        $utc = new DateTimeZone("UTC");
        $dt = new DateTime("now", $utc);
        $timezones = array();
        foreach (DateTimeZone::listIdentifiers() as $tz) {
            $current_tz = new DateTimeZone($tz);
            $offset = $current_tz->getOffset($dt);
            $transition = $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
            $abbr = $transition[0]["abbr"];
            if (!is_null($current) && $current == $tz) {
                $timezones[] = "<option value=\"" . $tz . "\" selected>" . $tz . " [" . $abbr . " " . self::formatOffset($offset) . "]</option>";
            } else {
                $timezones[] = "<option value=\"" . $tz . "\">" . $tz . " [" . $abbr . " " . self::formatOffset($offset) . "]</option>";
            }
        }
        return $timezones;
    }

    public static function GetCurrentTimeOffset()
    {
        $utc = new DateTimeZone("UTC");
        $dt = new DateTime("now", $utc);
        $current_timezone = ipTV_lib::$settings["default_timezone"];
        $current_tz = new DateTimeZone($current_timezone);
        $offset = $current_tz->getOffset($dt);
        return self::formatOffset($offset);
    }

    public static function SimpleWebGet($url, $save_cache = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $results = curl_exec($ch);
        curl_close($ch);
        if ($results !== false) {
            if (!$save_cache) {
            } else {
                $cr_uniq_id = uniqid();
                file_put_contents(TMP_DIR . $cr_uniq_id, $results);
                return TMP_DIR . $cr_uniq_id;
            }
        }
        return $results;
    }

    public static function curlMultiRequest($urls, $callback = NULL, $array_key = "raw")
    {
        if (!empty($urls)) {
            $ch = array();
            $results = array();
            $curl_m_init = curl_multi_init();
            foreach ($urls as $key => $val) {
                $ch[$key] = curl_init();
                curl_setopt($ch[$key], CURLOPT_URL, $val["url"]);
                curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch[$key], CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch[$key], CURLOPT_CONNECTTIMEOUT, 120);
                curl_setopt($ch[$key], CURLOPT_TIMEOUT, 120);
                curl_setopt($ch[$key], CURLOPT_MAXREDIRS, 10);
                if ($val["postdata"] != NULL) {
                    curl_setopt($ch[$key], CURLOPT_POST, true);
                    curl_setopt($ch[$key], CURLOPT_POSTFIELDS, http_build_query($val["postdata"]));
                }
                curl_multi_add_handle($curl_m_init, $ch[$key]);
            }
            $running = NULL;
            curl_multi_exec($curl_m_init, $running);
            if (0 >= $running) {
                foreach ($ch as $key => $val) {
                    $results[$key] = curl_multi_getcontent($val);
                    if ($callback != NULL) {
                        $results[$key] = call_user_func($callback, $results[$key], true);
                        if (isset($results[$key][$array_key])) {
                            $results[$key] = $results[$key][$array_key];
                        }
                    }
                    if (!$results[$key]) {
                        $results[$key] = array();
                        ipTV_lib::SaveLog("Server [" . $key . "] is DOWN!");
                    }
                    curl_multi_remove_handle($curl_m_init, $val);
                }
                curl_multi_close($curl_m_init);
                return $results;
            }
        } else {
            return array();
        }
    }

    public static function cleanGlobals(&$data, $iteration = 0)
    {
        if (10 > $iteration) {
            foreach ($data as $k => $val) {
                if (is_array($val)) {
                    self::cleanGlobals($data[$k], ++$iteration);
                } else {
                    $val = str_replace(chr("0"), "", $val);
                    $val = str_replace("\0", "", $val);
                    $val = str_replace("\0", "", $val);
                    $val = str_replace("../", "&#46;&#46;/", $val);
                    $val = str_replace("&#8238;", "", $val);
                    $data[$k] = $val;
                }
            }
        }
    }

    public static function parseIncomingRecursively(&$data, $input = array(), $iteration = 0)
    {
        if (20 > $iteration) {
            if (is_array($data)) {
                foreach ($data as $k => $val) {
                    if (is_array($val)) {
                        $input[$k] = self::parseIncomingRecursively($data[$k], array(), $iteration + 1);
                    } else {
                        $k = self::parseCleanKey($k);
                        $val = self::parseCleanValue($val);
                        $input[$k] = $val;
                    }
                }
                return $input;
            } else {
                return $input;
            }
        } else {
            return $input;
        }
    }

    public static function parseCleanKey($key)
    {
        if ($key !== "") {
            $key = htmlspecialchars(urldecode($key));
            $key = str_replace("..", "", $key);
            $key = preg_replace("/\\_\\_(.+?)\\_\\_/", "", $key);
            $key = preg_replace("/^([\\w\\.\\-\\_]+)\$/", "\$1", $key);
            return $key;
        }
        return "";
    }

    public static function parseCleanValue($val)
    {
        if ($val != "") {
            $val = str_replace("&#032;", " ", stripslashes($val));
            $val = str_replace(array("\r\n", "\n\r", "\r"), "\n", $val);
            $val = str_replace("<!--", "&#60;&#33;--", $val);
            $val = str_replace("-->", "--&#62;", $val);
            $val = str_ireplace("<script", "&#60;script", $val);
            $val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val);
            $val = preg_replace("/&#(\\d+?)([^\\d;])/i", "&#\\1;\\2", $val);
            return trim($val);
        }
        return "";
    }

    public static function isDemo()
    {
        return file_exists(IPTV_ROOT_PATH . "demo.iptv");
    }

    public static function SaveLog($msg)
    {
        self::$ipTV_db->query("INSERT INTO `panel_logs` (`log_message`,`date`) VALUES('%s','%d')", $msg, time());
    }

    public static function GetLicence()
    {
        self::$ipTV_db->query("SELECT * from `licence` WHERE `id` = 1");
        if (0 >= self::$ipTV_db->num_rows()) {
            exit;
        }
        $row = self::$ipTV_db->get_row();
        self::$licence = $row;
        return $row["licence_key"];
    }

    public static function IsEmail($email)
    {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || 64 < $localLen) {
                $isValid = false;
            } else {
                if ($domainLen < 1 || 255 < $domainLen) {
                    $isValid = false;
                } else {
                    if ($local[0] == "." || $local[$localLen - 1] == ".") {
                        $isValid = false;
                    } else {
                        if (preg_match("/\\.\\./", $local)) {
                            $isValid = false;
                        } else {
                            if (!preg_match("/^[A-Za-z0-9\\-\\.]+\$/", $domain)) {
                                $isValid = false;
                            } else {
                                if (preg_match("/\\.\\./", $domain)) {
                                    $isValid = false;
                                } else {
                                    if (!preg_match("/^(\\\\.|[A-Za-z0-9!#%&`_=\\/\$'*+?^{}|~.-])+\$/", str_replace("\\\\", "", $local))) {
                                        if (!preg_match("/^\"(\\\\\"|[^\"])+\"\$/", str_replace("\\\\", "", $local))) {
                                            $isValid = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                $isValid = false;
            }
        }
        return $isValid;
    }

    public static function GenerateString($length = 10)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789qwertyuiopasdfghjklzxcvbnm";
        $str = "";
        $max = strlen($chars) - 1;
        $i = 0;
        while ($i >= $length) {
            return $str;
        }
        $str .= $chars[rand(0, $max)];
        $i++;
    }

    public static function array_values_recursive($array)
    {
        $arrayValues = array();
        foreach ($array as $value) {
            if (is_scalar($value) || is_resource($value)) {
                $arrayValues[] = $value;
            } else {
                if (is_array($value)) {
                    $arrayValues = array_merge($arrayValues, self::array_values_recursive($value));
                }
            }
        }
        return $arrayValues;
    }

    public static function BuildTreeArray($servers)
    {
        $tree = array();
        foreach ($servers as $server) {
            if (!isset($tree[$server["parent_id"]])) {
                $tree[$server["parent_id"]] = array();
                foreach ($servers as $parent_server_id) {
                    if ($parent_server_id["parent_id"] == $server["parent_id"]) {
                        $tree[$server["parent_id"]][] = $parent_server_id["server_id"];
                    }
                }
            }
        }
        ksort($tree);
        return $tree;
    }

    public static function PrintTree($array, $index = 0)
    {
        $out = "";
        if (isset($array[$index]) && is_array($array[$index])) {
            $out = "<ul>";
            foreach ($array[$index] as $servers_name) {
                $out .= "<li><a href=\"#\">" . ipTV_lib::$StreamingServers[$servers_name]["server_name"] . "</a>";
                $out .= self::PrintTree($array, $servers_name);
                $out .= "</li>";
            }
            $out .= "</ul>";
        }
        return $out;
    }

    public static function add_quotes_string($string)
    {
        return "\"" . $string . "\"";
    }

    public static function valid_ip_cidr($cidr, $must_cidr = false)
    {
        if (!preg_match("/^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}(\\/[0-9]{1,2})?\$/", $cidr)) {
            $return = false;
        } else {
            $return = true;
        }
        if ($return == true) {
            $parts = explode("/", $cidr);
            list($ip, $netmask) = $parts;
            $octets = explode(".", $ip);
            foreach ($octets as $octet) {
                if (255 < $octet) {
                    $return = false;
                }
            }
            if ($netmask != "" && 32 < $netmask && !$must_cidr || ($netmask == "" || 32 < $netmask) && $must_cidr) {
                $return = false;
            }
        }
        return $return;
    }
}

?>