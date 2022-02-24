<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class SMTP
{
    public $SMTP_PORT = 25;
    public $CRLF = "\r\n";
    public $do_debug = 0;
    public $Debugoutput = "echo";
    public $do_verp = false;
    public $Timeout = 15;
    public $Timelimit = 30;
    public $Version = "5.2.6";
    protected $smtp_conn = NULL;
    protected $error = NULL;
    protected $helo_rply = NULL;
    protected function edebug($str)
    {
        switch ($this->Debugoutput) {
            case "error_log":
                error_log($str);
                break;
            case "html":
                echo htmlentities(preg_replace("/[\\r\\n]+/", "", $str), ENT_QUOTES, "UTF-8") . "<br>\n";
                break;
            case "echo":
            default:
                echo $str;
        }
    }
    public function __construct()
    {
        $this->smtp_conn = 0;
        $this->error = NULL;
        $this->helo_rply = NULL;
        $this->do_debug = 0;
    }

    public function Connect($host, $port = null, $timeout = 30) // NEW
    //public function Connect($host, $port = 0, $timeout = 30) // OLD
	{
        $this->error = NULL;
        if (!$this->Connected()) {
            if (empty($port)) {
                $port = $this->SMTP_PORT;
            }
            $this->smtp_conn = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!empty($this->smtp_conn)) {
                if (substr(PHP_OS, 0, 3) != "WIN") {
                    $max = ini_get("max_execution_time");
                    if ($max != 0 && $max < $timeout) {
                        @set_time_limit($timeout);
                    }
                    stream_set_timeout($this->smtp_conn, $timeout, 0);
                }
                $announce = $this->get_lines();
                if (2 <= $this->do_debug) {
                    $this->edebug("SMTP -> FROM SERVER:" . $announce);
                }
                return true;
            }

            $this->error = array("error" => "Failed to connect to server", "errno" => $errno, "errstr" => $errstr);
            if (1 <= $this->do_debug) {
                $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $errstr . " (" . $errno . ")");
            }
            return false;
        }
        $this->error = array("error" => "Already connected to a server");
        return false;
    }

    public function StartTLS()
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $this->client_send("STARTTLS" . $this->CRLF);
            $rply = $this->get_lines();
            $code = substr($rply, 0, 3);
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $rply);
            }
            if ($code == 220) {
                if (stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return true;
                }
                return false;
            }

            $this->error = array("error" => "STARTTLS not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
            if (1 <= $this->do_debug) {
                $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
            }
            return false;
        }
        $this->error = array("error" => "Called StartTLS() without being connected");
        return false;
    }

    public function Authenticate($username, $password, $authtype = "LOGIN", $realm = "", $workstation = "")
    {
        if (empty($authtype)) {
            $authtype = "LOGIN";
        }
        switch ($authtype) {
            case "PLAIN":
                $this->client_send("AUTH PLAIN" . $this->CRLF);
                $rply = $this->get_lines();
                $code = substr($rply, 0, 3);
                if ($code == 334) {
                    $this->client_send(base64_encode("\0" . $username . "\0" . $password) . $this->CRLF);
                    $rply = $this->get_lines();
                    $code = substr($rply, 0, 3);
                    if ($code == 235) {
                        break;
                    }
                    $this->error = array("error" => "Authentication not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                    if (1 <= $this->do_debug) {
                        $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                    }
                    return false;
                }
                $this->error = array("error" => "AUTH not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                if (1 <= $this->do_debug) {
                    $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                }
                return false;
            case "LOGIN":
                $this->client_send("AUTH LOGIN" . $this->CRLF);
                $rply = $this->get_lines();
                $code = substr($rply, 0, 3);
                if ($code == 334) {
                    $this->client_send(base64_encode($username) . $this->CRLF);
                    $rply = $this->get_lines();
                    $code = substr($rply, 0, 3);
                    if ($code == 334) {
                        $this->client_send(base64_encode($password) . $this->CRLF);
                        $rply = $this->get_lines();
                        $code = substr($rply, 0, 3);
                        if ($code == 235) {
                            break;
                        }
                        $this->error = array("error" => "Password not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                        if (1 <= $this->do_debug) {
                            $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                        }
                        return false;
                    }
                    $this->error = array("error" => "Username not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                    if (1 <= $this->do_debug) {
                        $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                    }
                    return false;
                }
                $this->error = array("error" => "AUTH not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                if (1 <= $this->do_debug) {
                    $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                }
                return false;
            case "NTLM":
                require_once "extras/ntlm_sasl_client.php";
                $temp = new stdClass();
                $ntlm_client = new ntlm_sasl_client_class();

                if ($ntlm_client->Initialize($temp)) {
                    $msg1 = $ntlm_client->TypeMsg1($realm, $workstation);
                    $this->client_send("AUTH NTLM " . base64_encode($msg1) . $this->CRLF);
                    $rply = $this->get_lines();
                    $code = substr($rply, 0, 3);
                    if ($code == 334) {
                        $challenge = substr($rply, 3);
                        $challenge = base64_decode($challenge);
                        $ntlm_res = $ntlm_client->NTLMResponse(substr($challenge, 24, 8), $password);
                        $msg3 = $ntlm_client->TypeMsg3($ntlm_res, $username, $realm, $workstation);
                        $this->client_send(base64_encode($msg3) . $this->CRLF);
                        $rply = $this->get_lines();
                        $code = substr($rply, 0, 3);
                        if ($code == 235) {
                            break;
                        }
                        $this->error = array("error" => "Could not authenticate", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                        if (1 <= $this->do_debug) {
                            $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                        }
                        return false;
                    }
                    $this->error = array("error" => "AUTH not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                    if (1 <= $this->do_debug) {
                        $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                    }
                    return false;
                }
                $this->error = array("error" => $temp->error);
                if (1 <= $this->do_debug) {
                    $this->edebug("You need to enable some modules in your php.ini file: " . $this->error["error"]);
                }
                return false;
            case "CRAM-MD5":
                $this->client_send("AUTH CRAM-MD5" . $this->CRLF);
                $rply = $this->get_lines();
                $code = substr($rply, 0, 3);
                if ($code == 334) {
                    $challenge = base64_decode(substr($rply, 4));
                    $response = $username . " " . $this->hmac($challenge, $password);
                    $this->client_send(base64_encode($response) . $this->CRLF);
                    $rply = $this->get_lines();
                    $code = substr($rply, 0, 3);
                    if ($code == 235) {
                        break;
                    }
                    $this->error = array("error" => "Credentials not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                    if (1 <= $this->do_debug) {
                        $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                    }
                    return false;
                }
                $this->error = array("error" => "AUTH not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                if (1 <= $this->do_debug) {
                    $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                }
                return false;
        }
        return true;
    }

    protected function hmac($data, $key)
    {
        if (!function_exists("hash_hmac")) {
            $b = 64;
            if ($b < strlen($key)) {
                $key = pack("H*", md5($key));
            }
            $key = str_pad($key, $b, chr(0));
            $ipad = str_pad("", $b, chr(54));
            $opad = str_pad("", $b, chr(92));
            $k_ipad = $key ^ $ipad;
            $k_opad = $key ^ $opad;
            return md5($k_opad . pack("H*", md5($k_ipad . $data)));
        }
        return hash_hmac("md5", $data, $key);
    }

    public function Connected()
    {
        if (empty($this->smtp_conn)) {
            return false;
        }
        $sock_status = stream_get_meta_data($this->smtp_conn);
        if (!$sock_status["eof"]) {
            return true;
        }
        if (1 <= $this->do_debug) {
            $this->edebug("SMTP -> NOTICE: EOF caught while checking if connected");
        }
        $this->Close();
        return false;
    }

    public function Close()
    {
        $this->error = NULL;
        $this->helo_rply = NULL;
        if (!empty($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = 0;
        }
    }

    public function Data($msg_data)
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $this->client_send("DATA" . $this->CRLF);
            $rply = $this->get_lines();
            $code = substr($rply, 0, 3);
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $rply);
            }
            if ($code == 354) {
                $msg_data = str_replace("\r\n", "\n", $msg_data);
                $msg_data = str_replace("\r", "\n", $msg_data);
                $lines = explode("\n", $msg_data);
                $field = substr($lines[0], 0, strpos($lines[0], ":"));
                $in_headers = false;
                if (!(empty($field) || strstr($field, " "))) {
                    $in_headers = true;
                }
                $max_line_length = 998;
                while (!(list($line) = @each($lines))) {
                    $this->client_send($this->CRLF . "." . $this->CRLF);
                    $rply = $this->get_lines();
                    $code = substr($rply, 0, 3);
                    if (2 <= $this->do_debug) {
                        $this->edebug("SMTP -> FROM SERVER:" . $rply);
                    }
                    if ($code == 250) {
                        return true;
                    }
                    $this->error = array("error" => "DATA not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                    if (1 <= $this->do_debug) {
                        $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                    }
                    return false;
                }
                $lines_out = NULL;
                if ($line == "" && $in_headers) {
                    $in_headers = false;
                }
                while ($max_line_length >= strlen($line)) {
                    $lines_out[] = $line;
                    while (!(list($line) = @each($lines_out))) {
                    }
                    if (0 < strlen($line)) {
                        if (substr($line, 0, 1) == ".") {
                            $line = "." . $line;
                        }
                    }
                    $this->client_send($line . $this->CRLF);
                }
                $pos = strrpos(substr($line, 0, $max_line_length), " ");
                if (!$pos) {
                    $pos = $max_line_length - 1;
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos);
                } else {
                    $lines_out[] = substr($line, 0, $pos);
                    $line = substr($line, $pos + 1);
                }
                if ($in_headers) {
                    $line = "\t" . $line;
                }
            } else {
                $this->error = array("error" => "DATA command not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
                if (1 <= $this->do_debug) {
                    $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
                }
                return false;
            }
        } else {
            $this->error = array("error" => "Called Data() without being connected");
            return false;
        }
    }

    public function Hello($host = "")
    {
        $this->error = NULL;
        if ($this->Connected()) {
            if (empty($host)) {
                $host = "localhost";
            }

            if (!$this->SendHello("EHLO", $host)) {
                if ($this->SendHello("HELO", $host)) {
                } else {
                    return false;
                }
            }
            return true;
        }
        $this->error = array("error" => "Called Hello() without being connected");
        return false;
    }

    protected function SendHello($hello, $host)
    {
        $this->client_send($hello . " " . $host . $this->CRLF);
        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);
        if (2 <= $this->do_debug) {
            $this->edebug("SMTP -> FROM SERVER: " . $rply);
        }
        if ($code == 250) {
            $this->helo_rply = $rply;
            return true;
        }
        $this->error = array("error" => $hello . " not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
        if (1 <= $this->do_debug) {
            $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
        }
        return false;
    }

    public function Mail($from)
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $useVerp = $this->do_verp ? " XVERP" : "";
            $this->client_send("MAIL FROM:<" . $from . ">" . $useVerp . $this->CRLF);
            $rply = $this->get_lines();
            $code = substr($rply, 0, 3);
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $rply);
            }
            if ($code == 250) {
                return true;
            }
            $this->error = array("error" => "MAIL not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
            if (1 <= $this->do_debug) {
                $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
            }
            return false;
        }
        $this->error = array("error" => "Called Mail() without being connected");
        return false;
    }

    public function Quit($close_on_error = true)
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $this->client_send("quit" . $this->CRLF);
            $byemsg = $this->get_lines();
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $byemsg);
            }
            $rval = true;
            $e = NULL;
            $code = substr($byemsg, 0, 3);
            if ($code != 221) {
                $e = array("error" => "SMTP server rejected quit command", "smtp_code" => $code, "smtp_rply" => substr($byemsg, 4));
                $rval = false;
                if (1 <= $this->do_debug) {
                    $this->edebug("SMTP -> ERROR: " . $e["error"] . ": " . $byemsg);
                }
            }
            if (empty($e) || $close_on_error) {
                $this->Close();
            }
            return $rval;
        }
        $this->error = array("error" => "Called Quit() without being connected");
        return false;
    }

    public function Recipient($to)
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $this->client_send("RCPT TO:<" . $to . ">" . $this->CRLF);
            $rply = $this->get_lines();
            $code = substr($rply, 0, 3);
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $rply);
            }
            if (!($code != 250 && $code != 251)) {
                return true;
            }
            $this->error = array("error" => "RCPT not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
            if (1 <= $this->do_debug) {
                $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
            }
            return false;
        }
        $this->error = array("error" => "Called Recipient() without being connected");
        return false;
    }

    public function Reset()
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $this->client_send("RSET" . $this->CRLF);
            $rply = $this->get_lines();
            $code = substr($rply, 0, 3);
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $rply);
            }
            if ($code == 250) {
                return true;
            }
            $this->error = array("error" => "RSET failed", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
            if (1 <= $this->do_debug) {
                $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
            }
            return false;
        }
        $this->error = array("error" => "Called Reset() without being connected");
        return false;
    }

    public function SendAndMail($from)
    {
        $this->error = NULL;
        if ($this->Connected()) {
            $this->client_send("SAML FROM:" . $from . $this->CRLF);
            $rply = $this->get_lines();
            $code = substr($rply, 0, 3);
            if (2 <= $this->do_debug) {
                $this->edebug("SMTP -> FROM SERVER:" . $rply);
            }
            if ($code == 250) {
                return true;
            }
            $this->error = array("error" => "SAML not accepted from server", "smtp_code" => $code, "smtp_msg" => substr($rply, 4));
            if (1 <= $this->do_debug) {
                $this->edebug("SMTP -> ERROR: " . $this->error["error"] . ": " . $rply);
            }
            return false;
        }
        $this->error = array("error" => "Called SendAndMail() without being connected");
        return false;
    }

    public function Turn()
    {
        $this->error = array("error" => "This method, TURN, of the SMTP " . "is not implemented");
        if (1 <= $this->do_debug) {
            $this->edebug("SMTP -> NOTICE: " . $this->error["error"]);
        }
        return false;
    }

    public function client_send($data)
    {
        if (1 <= $this->do_debug) {
            $this->edebug("CLIENT -> SMTP: " . $data);
        }
        return fwrite($this->smtp_conn, $data);
    }

    public function getError()
    {
        return $this->error;
    }

    protected function get_lines()
    {
        $data = "";
        $endtime = 0;
        if (is_resource($this->smtp_conn)) {
            stream_set_timeout($this->smtp_conn, $this->Timeout);
            if (0 < $this->Timelimit) {
                $endtime = time() + $this->Timelimit;
            }
            while (!is_resource($this->smtp_conn) || feof($this->smtp_conn)) {
            }
            return $data;
        }
        return $data;
    }
}

?>