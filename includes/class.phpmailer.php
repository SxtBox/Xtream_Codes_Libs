<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

if (!version_compare(PHP_VERSION, "5.0.0", "<")) {
/**
 * Exception handler for PHPMailer
 * @package PHPMailer
 */
    class phpmailerException extends Exception
    {
  /**
   * Prettify error message output
   * @return string
   */
        public function errorMessage()
        {
            $errorMsg = "<strong>" . $this->getMessage() . "</strong><br />\n";
            return $errorMsg;
        }
    }
} else {
    exit("Sorry, this version of PHPMailer will only run on PHP version 5 or greater!\n");
}

class PHPMailer
{
    public $Priority = 3;
    public $CharSet = "iso-8859-1";
    public $ContentType = "text/plain";
    public $Encoding = "8bit";
    public $ErrorInfo = "";
    public $From = "root@localhost";
    public $FromName = "Root User";
    public $Sender = "";
    public $ReturnPath = "";
    public $Subject = "";
    public $Body = "";
    public $AltBody = "";
    protected $MIMEBody = "";
    protected $MIMEHeader = "";
    protected $mailHeader = "";
    public $WordWrap = 0;
    public $Mailer = "mail";
    public $Sendmail = "/usr/sbin/sendmail";
    public $UseSendmailOptions = true;
    public $PluginDir = "";
    public $ConfirmReadingTo = "";
    public $Hostname = "";
    public $MessageID = "";
    public $MessageDate = "";
    public $Host = "localhost";
    public $Port = 25;
    public $Helo = "";
    public $SMTPSecure = "";
    public $SMTPAuth = false;
    public $Username = "";
    public $Password = "";
    public $AuthType = "";
    public $Realm = "";
    public $Workstation = "";
    public $Timeout = 10;
    public $SMTPDebug = false;
    public $Debugoutput = "echo";
    public $SMTPKeepAlive = false;
    public $SingleTo = false;
    public $do_verp = false;
    public $SingleToArray = array();
    public $AllowEmpty = false;
    public $LE = "\n";
    public $DKIM_selector = "";
    public $DKIM_identity = "";
    public $DKIM_passphrase = "";
    public $DKIM_domain = "";
    public $DKIM_private = "";
    public $action_function = "";
    public $Version = "5.2.6";
    public $XMailer = "";
    protected $smtp = NULL;
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $ReplyTo = array();
    protected $all_recipients = array();
    protected $attachment = array();
    protected $CustomHeader = array();
    protected $message_type = "";
    protected $boundary = array();
    protected $language = array();
    protected $error_count = 0;
    protected $sign_cert_file = "";
    protected $sign_key_file = "";
    protected $sign_key_pass = "";
    protected $exceptions = false;
    const STOP_MESSAGE = 0;
    const STOP_CONTINUE = 1;
    const STOP_CRITICAL = 2;
    const CRLF = "\r\n";

    private function mail_passthru($to, $subject, $body, $header, $params)
    {
        if (ini_get("safe_mode") || !$this->UseSendmailOptions) {
            $rt = @mail($to, @$this->EncodeHeader(@$this->SecureHeader($subject)), $body, $header);
        } else {
            $rt = @mail($to, @$this->EncodeHeader(@$this->SecureHeader($subject)), $body, $header, $params);
        }
        return $rt;
    }

    protected function edebug($str)
    {
        switch ($this->Debugoutput) {
            case "error_log":
                error_log($str);
                break;
            case "html":
                echo htmlentities(preg_replace("/[\\r\\n]+/", "", $str), ENT_QUOTES, $this->CharSet) . "<br>\n";
                break;
            case "echo":
            default:
                echo $str;
        }
    }
    public function __construct($exceptions = false)
    {
        $this->exceptions = $exceptions == true;
    }
    public function __destruct()
    {
        if ($this->Mailer == "smtp") {
            $this->SmtpClose();
        }
    }
    public function IsHTML($IsHTML = true)
    {
        if ($IsHTML) {
            $this->ContentType = "text/html";
        } else {
            $this->ContentType = "text/plain";
        }
    }
    public function IsSMTP()
    {
        $this->Mailer = "smtp";
    }
    public function IsMail()
    {
        $this->Mailer = "mail";
    }
    public function IsSendmail()
    {
        if (!stristr(ini_get("sendmail_path"), "sendmail")) {
            $this->Sendmail = "/var/qmail/bin/sendmail";
        }
        $this->Mailer = "sendmail";
    }
    public function IsQmail()
    {
        if (stristr(ini_get("sendmail_path"), "qmail")) {
            $this->Sendmail = "/var/qmail/bin/sendmail";
        }
        $this->Mailer = "sendmail";
    }
    public function AddAddress($address, $name = "")
    {
        return $this->AddAnAddress("to", $address, $name);
    }
    public function AddCC($address, $name = "")
    {
        return $this->AddAnAddress("cc", $address, $name);
    }
    public function AddBCC($address, $name = "")
    {
        return $this->AddAnAddress("bcc", $address, $name);
    }
    public function AddReplyTo($address, $name = "")
    {
        return $this->AddAnAddress("Reply-To", $address, $name);
    }
    protected function AddAnAddress($kind, $address, $name = "")
    {
        if (preg_match("/^(to|cc|bcc|Reply-To)\$/", $kind)) {
            $address = trim($address);
            $name = trim(preg_replace("/[\\r\\n]+/", "", $name));
            if ($this->ValidateAddress($address)) {
                if ($kind != "Reply-To") {
                    if (isset($this->all_recipients[strtolower($address)])) {
                    } else {
                        array_push($this->{$kind}, array($address, $name));
                        $this->all_recipients[strtolower($address)] = true;
                        return true;
                    }
                } else {
                    if (array_key_exists(strtolower($address), $this->ReplyTo)) {
                    } else {
                        $this->ReplyTo[strtolower($address)] = array($address, $name);
                        return true;
                    }
                }
                return false;
            }
			
            $this->SetError($this->Lang("invalid_address") . ": " . $address);
            if (!$this->exceptions) {
                if ($this->SMTPDebug) {
                    $this->edebug($this->Lang("invalid_address") . ": " . $address);
                }
                return false;
            }
            throw new phpmailerException($this->Lang("invalid_address") . ": " . $address);
        }
        $this->SetError($this->Lang("Invalid recipient array") . ": " . $kind);
        if (!$this->exceptions) {
            if ($this->SMTPDebug) {
                $this->edebug($this->Lang("Invalid recipient array") . ": " . $kind);
            }
            return false;
        }
        throw new phpmailerException("Invalid recipient array: " . $kind);
    }
    public function SetFrom($address, $name = "", $auto = 1)
    {
        $address = trim($address);
        $name = trim(preg_replace("/[\\r\\n]+/", "", $name));
        if ($this->ValidateAddress($address)) {
            $this->From = $address;
            $this->FromName = $name;
            if ($auto) {
                if (empty($this->ReplyTo)) {
                    $this->AddAnAddress("Reply-To", $address, $name);
                }
                if (empty($this->Sender)) {
                    $this->Sender = $address;
                }
            }
            return true;
        }
        $this->SetError($this->Lang("invalid_address") . ": " . $address);
        if (!$this->exceptions) {
            if ($this->SMTPDebug) {
                $this->edebug($this->Lang("invalid_address") . ": " . $address);
            }
            return false;
        }
        throw new phpmailerException($this->Lang("invalid_address") . ": " . $address);
    }
    public static function ValidateAddress($address)
    {
        if (defined("PCRE_VERSION")) {
            if (0 <= version_compare(PCRE_VERSION, "8.0")) {
                return (bool) preg_match("/^(?!(?>(?1)\"?(?>\\\\[ -~]|[^\"])\"?(?1)){255,})(?!(?>(?1)\"?(?>\\\\[ -~]|[^\"])\"?(?1)){65,}@)((?>(?>(?>((?>(?>(?>\\x0D\\x0A)?[\\t ])+|(?>[\\t ]*\\x0D\\x0A)?[\\t ]+)?)(\\((?>(?2)(?>[\\x01-\\x08\\x0B\\x0C\\x0E-'*-\\[\\]-\\x7F]|\\\\[\\x00-\\x7F]|(?3)))*(?2)\\)))+(?2))|(?2))?)([!#-'*+\\/-9=?^-~-]+|\"(?>(?2)(?>[\\x01-\\x08\\x0B\\x0C\\x0E-!#-\\[\\]-\\x7F]|\\\\[\\x00-\\x7F]))*(?2)\")(?>(?1)\\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>(?1)\\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}|(?!(?:.*[a-f0-9][:\\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\\.(?9)){3}))\\])(?1)\$/isD", $address);
            }
            return (bool) preg_match("/^(?!(?>\"?(?>\\\\[ -~]|[^\"])\"?){255,})(?!(?>\"?(?>\\\\[ -~]|[^\"])\"?){65,}@)(?>[!#-'*+\\/-9=?^-~-]+|\"(?>(?>[\\x01-\\x08\\x0B\\x0C\\x0E-!#-\\[\\]-\\x7F]|\\\\[\\x00-\\xFF]))*\")(?>\\.(?>[!#-'*+\\/-9=?^-~-]+|\"(?>(?>[\\x01-\\x08\\x0B\\x0C\\x0E-!#-\\[\\]-\\x7F]|\\\\[\\x00-\\xFF]))*\"))*@(?>(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>\\.(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)){0,126}|\\[(?:(?>IPv6:(?>(?>[a-f0-9]{1,4})(?>:[a-f0-9]{1,4}){7}|(?!(?:.*[a-f0-9][:\\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?))|(?>(?>IPv6:(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){5}:|(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?::(?>(?:[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}):)?))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])(?>\\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\\])\$/isD", $address);
        }
        return 3 <= strlen($address) && 1 <= strpos($address, "@") && strpos($address, "@") != strlen($address) - 1;
    }

    public function Send()
    {
        try {
            if ($this->PreSend()) {
                return $this->PostSend();
            }
            return false;
        } catch (phpmailerException $e) {
            $this->mailHeader = "";
            $this->SetError($e->getMessage());
            if (!$this->exceptions) {
                return false;
            }
            throw $e;
        }
    }

    public function PreSend()
    {
        try {
            $this->mailHeader = "";
            if (count($this->to) + count($this->cc) + count($this->bcc) >= 1) {
                if (!empty($this->AltBody)) {
                    $this->ContentType = "multipart/alternative";
                }
                $this->error_count = 0;
                $this->SetMessageType();
                if ($this->AllowEmpty || !empty($this->Body)) {
                    $this->MIMEHeader = $this->CreateHeader();
                    $this->MIMEBody = $this->CreateBody();
                    if ($this->Mailer == "mail") {
                        if (0 < count($this->to)) {
                            $this->mailHeader .= $this->AddrAppend("To", $this->to);
                        } else {
                            $this->mailHeader .= $this->HeaderLine("To", "undisclosed-recipients:;");
                        }
                        $this->mailHeader .= $this->HeaderLine("Subject", $this->EncodeHeader($this->SecureHeader(trim($this->Subject))));
                    }
                    if (!empty($this->DKIM_domain) && !empty($this->DKIM_private) && !empty($this->DKIM_selector) && !empty($this->DKIM_domain) && file_exists($this->DKIM_private)) {
                        $header_dkim = $this->DKIM_Add($this->MIMEHeader . $this->mailHeader, $this->EncodeHeader($this->SecureHeader($this->Subject)), $this->MIMEBody);
                        $this->MIMEHeader = str_replace("\r\n", "\n", $header_dkim) . $this->MIMEHeader;
                    }
                    return true;
                }
                throw new phpmailerException($this->Lang("empty_message"), self::STOP_CRITICAL);
            }
            throw new phpmailerException($this->Lang("provide_address"), self::STOP_CRITICAL);
        } catch (phpmailerException $e) {
            $this->SetError($e->getMessage());
            if (!$this->exceptions) {
                return false;
            }
            throw $e;
        }
    }

    public function PostSend()
    {
        try {
            switch ($this->Mailer) {
                case "sendmail":
                    return $this->SendmailSend($this->MIMEHeader, $this->MIMEBody);
                case "smtp":
                    return $this->SmtpSend($this->MIMEHeader, $this->MIMEBody);
                case "mail":
                    return $this->MailSend($this->MIMEHeader, $this->MIMEBody);
                default:
                    return $this->MailSend($this->MIMEHeader, $this->MIMEBody);
            }
        } catch (phpmailerException $e) {
            $this->SetError($e->getMessage());
            if (!$this->exceptions) {
                if ($this->SMTPDebug) {
                    $this->edebug($e->getMessage() . "\n");
                }
            } else {
                throw $e;
            }
        }
        return false;
    }

    protected function SendmailSend($header, $body)
    {
        if ($this->Sender != "") {
            $sendmail = sprintf("%s -oi -f%s -t", escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
        } else {
            $sendmail = sprintf("%s -oi -t", escapeshellcmd($this->Sendmail));
        }
        if ($this->SingleTo === true) {
            foreach ($this->SingleToArray as $val) {
				// if(!@$mail = popen($sendmail, 'w')) { // OLD
                if ($mail = @popen($sendmail, "w")) {
                    fputs($mail, "To: " . $val . "\n");
                    fputs($mail, $header);
                    fputs($mail, $body);
                    $result = pclose($mail);
                    $isSent = $result == 0 ? 1 : 0;
                    $this->doCallback($isSent, $val, $this->cc, $this->bcc, $this->Subject, $body);
                    if ($result == 0) {
                    } else {
                        throw new phpmailerException($this->Lang("execute") . $this->Sendmail, self::STOP_CRITICAL);
                    }
                } else {
                    throw new phpmailerException($this->Lang("execute") . $this->Sendmail, self::STOP_CRITICAL);
                }
            }
        } else {
            if ($mail = @popen($sendmail, "w")) {
                fputs($mail, $header);
                fputs($mail, $body);
                $result = pclose($mail);
                $isSent = $result == 0 ? 1 : 0;
                $this->doCallback($isSent, $this->to, $this->cc, $this->bcc, $this->Subject, $body);
                if ($result == 0) {
                } else {
                    throw new phpmailerException($this->Lang("execute") . $this->Sendmail, self::STOP_CRITICAL);
                }
            } else {
                throw new phpmailerException($this->Lang("execute") . $this->Sendmail, self::STOP_CRITICAL);
            }
        }
        return true;
    }

    protected function MailSend($header, $body)
    {
        $toArr = array();
        foreach ($this->to as $t) {
            $toArr[] = $this->AddrFormat($t);
        }
        $to = implode(", ", $toArr);
        if (empty($this->Sender)) {
            $params = " ";
        } else {
            $params = sprintf("-f%s", $this->Sender);
        }
        if (!($this->Sender == "" || ini_get("safe_mode"))) {
            $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $this->Sender);
        }
        $rt = false;
        if ($this->SingleTo === true && 1 < count($toArr)) {
            foreach ($toArr as $val) {
                $rt = $this->mail_passthru($val, $this->Subject, $body, $header, $params);
                $isSent = $rt == 1 ? 1 : 0;
                $this->doCallback($isSent, $val, $this->cc, $this->bcc, $this->Subject, $body);
            }
        } else {
            $rt = $this->mail_passthru($to, $this->Subject, $body, $header, $params);
            $isSent = $rt == 1 ? 1 : 0;
            $this->doCallback($isSent, $to, $this->cc, $this->bcc, $this->Subject, $body);
        }
        if (isset($old_from)) {
            ini_set("sendmail_from", $old_from);
        }
        if ($rt) {
            return true;
        }
        throw new phpmailerException($this->Lang("instantiate"), self::STOP_CRITICAL);
    }

    protected function SmtpSend($header, $body)
    {
        require_once $this->PluginDir . "class.smtp.php";
        $bad_rcpt = array();
        if ($this->SmtpConnect()) {
            $smtp_from = $this->Sender == "" ? $this->From : $this->Sender;
            if ($this->smtp->Mail($smtp_from)) {
                foreach ($this->to as $to) {
                    if (!$this->smtp->Recipient($to[0])) {
                        $bad_rcpt[] = $to[0];
                        $isSent = 0;
                        $this->doCallback($isSent, $to[0], "", "", $this->Subject, $body);
                    } else {
                        $isSent = 1;
                        $this->doCallback($isSent, $to[0], "", "", $this->Subject, $body);
                    }
                }
                foreach ($this->cc as $cc) {
                    if (!$this->smtp->Recipient($cc[0])) {
                        $bad_rcpt[] = $cc[0];
                        $isSent = 0;
                        $this->doCallback($isSent, "", $cc[0], "", $this->Subject, $body);
                    } else {
                        $isSent = 1;
                        $this->doCallback($isSent, "", $cc[0], "", $this->Subject, $body);
                    }
                }
                foreach ($this->bcc as $bcc) {
                    if (!$this->smtp->Recipient($bcc[0])) {
                        $bad_rcpt[] = $bcc[0];
                        $isSent = 0;
                        $this->doCallback($isSent, "", "", $bcc[0], $this->Subject, $body);
                    } else {
                        $isSent = 1;
                        $this->doCallback($isSent, "", "", $bcc[0], $this->Subject, $body);
                    }
                }
                if (0 >= count($bad_rcpt)) {
                    if ($this->smtp->Data($header . $body)) {
                        if ($this->SMTPKeepAlive == true) {
                            $this->smtp->Reset();
                        } else {
                            $this->smtp->Quit();
                            $this->smtp->Close();
                        }
                        return true;
                    }
                    throw new phpmailerException($this->Lang("data_not_accepted"), self::STOP_CRITICAL);
                }
                $badaddresses = implode(", ", $bad_rcpt);
                throw new phpmailerException($this->Lang("recipients_failed") . $badaddresses);
            } else {
                $this->SetError($this->Lang("from_failed") . $smtp_from . " : " . implode(",", $this->smtp->getError()));
                throw new phpmailerException($this->ErrorInfo, self::STOP_CRITICAL);
            }
        } else {
            throw new phpmailerException($this->Lang("smtp_connect_failed"), self::STOP_CRITICAL);
        }
    }
    public function SmtpConnect()
    {
        if (is_null($this->smtp)) {
            $this->smtp = new SMTP();
        }
        $this->smtp->Timeout = $this->Timeout;
        $this->smtp->do_debug = $this->SMTPDebug;
        $this->smtp->Debugoutput = $this->Debugoutput;
        $this->smtp->do_verp = $this->do_verp;
        $hosts = explode(";", $this->Host);
        $index = 0;
        $connection = $this->smtp->Connected();
        try {
            while ($index >= count($hosts) || $connection) {
            }
            $hostinfo = array();
            if (preg_match("/^(.+):([0-9]+)\$/", $hosts[$index], $hostinfo)) {
                list($host, $port) = $hostinfo;
            } else {
                $host = $hosts[$index];
                $port = $this->Port;
            }
            $tls = $this->SMTPSecure == "tls";
            $ssl = $this->SMTPSecure == "ssl";
			// if ($this->smtp->Connect(($ssl ? 'ssl://':'').$host, $port, $this->Timeout)) {
            if ($this->smtp->Connect(($ssl ? "ssl://" : "") . $host, $port, $this->Timeout)) {
                $hello = $this->Helo != "" ? $this->Helo : $this->ServerHostname();
                $this->smtp->Hello($hello);
                if ($tls) {
                    if ($this->smtp->StartTLS()) {
                        $this->smtp->Hello($hello);
                    } else {
                        throw new phpmailerException($this->Lang("connect_host"));
                    }
                }
                $connection = true;
                if ($this->SMTPAuth) {
                    if ($this->smtp->Authenticate($this->Username, $this->Password, $this->AuthType, $this->Realm, $this->Workstation)) {
                    } else {
                        throw new phpmailerException($this->Lang("authenticate"));
                    }
                }
            }
            $index++;
            if ($connection) {
            } else {
                throw new phpmailerException($this->Lang("connect_host"));
            }
        } catch (phpmailerException $e) {
            $this->smtp->Reset();
            if (!$this->exceptions) {
            } else {
                throw $e;
            }
        }
        return true;
    }

    public function SmtpClose()
    {
        if ($this->smtp !== NULL) {
            if ($this->smtp->Connected()) {
                $this->smtp->Quit();
                $this->smtp->Close();
            }
        }
    }

    public function SetLanguage($langcode = "en", $lang_path = "language/")
    {
        $PHPMAILER_LANG = array(
		"authenticate" => "SMTP Error: Could not authenticate.",
		"connect_host" => "SMTP Error: Could not connect to SMTP host.",
		"data_not_accepted" => "SMTP Error: Data not accepted.",
		"empty_message" => "Message body empty",
		"encoding" => "Unknown encoding: ",
		"execute" => "Could not execute: ",
		"file_access" => "Could not access file: ",
		"file_open" => "File Error: Could not open file: ",
		"from_failed" => "The following From address failed: ",
		"instantiate" => "Could not instantiate mail function.",
		"invalid_address" => "Invalid address",
		"mailer_not_supported" => " mailer is not supported.",
		"provide_address" => "You must provide at least one recipient email address.",
		"recipients_failed" => "SMTP Error: The following recipients failed: ",
		"signing" => "Signing Error: ",
		"smtp_connect_failed" => "SMTP Connect() failed.",
		"smtp_error" => "SMTP server error: ",
		"variable_set" => "Cannot set or reset variable: ");

        $l = true;
        if ($langcode != "en") {
            $l = (include $lang_path . "phpmailer.lang-" . $langcode . ".php");
        }
        $this->language = $PHPMAILER_LANG;
        return $l == true;
    }

    public function GetTranslations()
    {
        return $this->language;
    }

    public function AddrAppend($type, $addr)
    {
        $addr_str = $type . ": ";
        $addresses = array();
        foreach ($addr as $a) {
            $addresses[] = $this->AddrFormat($a);
        }
        $addr_str .= implode(", ", $addresses);
        $addr_str .= $this->LE;
        return $addr_str;
    }

    public function AddrFormat($addr)
    {
        if (empty($addr[1])) {
            return $this->SecureHeader($addr[0]);
        }
        return $this->EncodeHeader($this->SecureHeader($addr[1]), "phrase") . " <" . $this->SecureHeader($addr[0]) . ">";
    }

    public function WrapText($message, $length, $qp_mode = false)
    {
        $soft_break = $qp_mode ? sprintf(" =%s", $this->LE) : $this->LE;
        $is_utf8 = strtolower($this->CharSet) == "utf-8";
        $lelen = strlen($this->LE);
        $crlflen = strlen(self::CRLF);
        $message = $this->FixEOL($message);
        if (substr($message, 0 - $lelen) == $this->LE) {
            $message = substr($message, 0, 0 - $lelen);
        }
        $line = explode($this->LE, $message);
        $message = "";
        $i = 0;
        while ($i >= count($line)) {
            return $message;
        }
        $line_part = explode(" ", $line[$i]);
        $buf = "";
        $e = 0;
        while ($e >= count($line_part)) {
            $message .= $buf . self::CRLF;
            $i++;
        }
        $word = $line_part[$e];
        if ($qp_mode && $length < strlen($word)) {
            $space_left = $length - strlen($buf) - $crlflen;
            if ($e != 0) {
                if (20 < $space_left) {
                    $len = $space_left;
                    if ($is_utf8) {
                        $len = $this->UTF8CharBoundary($word, $len);
                    } else {
                        if (substr($word, $len - 1, 1) == "=") {
                            $len--;
                        } else {
                            if (substr($word, $len - 2, 1) == "=") {
                                $len -= 2;
                            }
                        }
                    }
                    $part = substr($word, 0, $len);
                    $word = substr($word, $len);
                    $buf .= " " . $part;
                    $message .= $buf . sprintf("=%s", self::CRLF);
                } else {
                    $message .= $buf . $soft_break;
                }
                $buf = "";
            }
            while (0 >= strlen($word)) {
            }
        } else {
            $buf_o = $buf;
            $buf .= $e == 0 ? $word : " " . $word;
            if ($length < strlen($buf) && $buf_o != "") {
                $message .= $buf_o . $soft_break;
                $buf = $word;
            }
        }
        $e++;
    }
    public function UTF8CharBoundary($encodedText, $maxLength)
    {
        $foundSplitPos = false;
        $lookBack = 3;
        while ($foundSplitPos) {
            return $maxLength;
        }
        $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
        $encodedCharPos = strpos($lastChunk, "=");
        if ($encodedCharPos !== false) {
            $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
            $dec = hexdec($hex);
            if ($dec < 128) {
                $maxLength = $encodedCharPos == 0 ? $maxLength : $maxLength - ($lookBack - $encodedCharPos);
                $foundSplitPos = true;
            } else {
                if (192 <= $dec) {
                    $maxLength = $maxLength - ($lookBack - $encodedCharPos);
                    $foundSplitPos = true;
                } else {
                    if ($dec < 192) {
                        $lookBack += 3;
                    }
                }
            }
        } else {
            $foundSplitPos = true;
        }
    }
    public function SetWordWrap()
    {
        if ($this->WordWrap >= 1) {
            switch ($this->message_type) {
                case "alt":
                case "alt_inline":
                case "alt_attach":
                case "alt_inline_attach":
                    $this->AltBody = $this->WrapText($this->AltBody, $this->WordWrap);
                    break;
                default:
                    $this->Body = $this->WrapText($this->Body, $this->WordWrap);
                    break;
            }
        }
    }
    public function CreateHeader()
    {
        $result = "";
        $uniq_id = md5(uniqid(time()));
        $this->boundary[1] = "b1_" . $uniq_id;
        $this->boundary[2] = "b2_" . $uniq_id;
        $this->boundary[3] = "b3_" . $uniq_id;
        if ($this->MessageDate == "") {
            $result .= $this->HeaderLine("Date", self::RFCDate());
        } else {
            $result .= $this->HeaderLine("Date", $this->MessageDate);
        }
        if ($this->ReturnPath) {
            $result .= $this->HeaderLine("Return-Path", "<" . trim($this->ReturnPath) . ">");
        } else {
            if ($this->Sender == "") {
                $result .= $this->HeaderLine("Return-Path", "<" . trim($this->From) . ">");
            } else {
                $result .= $this->HeaderLine("Return-Path", "<" . trim($this->Sender) . ">");
            }
        }
        if ($this->Mailer != "mail") {
            if ($this->SingleTo === true) {
                foreach ($this->to as $t) {
                    $this->SingleToArray[] = $this->AddrFormat($t);
                }
            } else {
                if (0 < count($this->to)) {
                    $result .= $this->AddrAppend("To", $this->to);
                } else {
                    if (count($this->cc) == 0) {
                        $result .= $this->HeaderLine("To", "undisclosed-recipients:;");
                    }
                }
            }
        }
        $from = array();
        $from[0][0] = trim($this->From);
        $from[0][1] = $this->FromName;
        $result .= $this->AddrAppend("From", $from);
        if (0 < count($this->cc)) {
            $result .= $this->AddrAppend("Cc", $this->cc);
        }
        if (($this->Mailer == "sendmail" || $this->Mailer == "mail") && 0 < count($this->bcc)) {
            $result .= $this->AddrAppend("Bcc", $this->bcc);
        }
        if (0 < count($this->ReplyTo)) {
            $result .= $this->AddrAppend("Reply-To", $this->ReplyTo);
        }
        if ($this->Mailer != "mail") {
            $result .= $this->HeaderLine("Subject", $this->EncodeHeader($this->SecureHeader($this->Subject)));
        }
        if ($this->MessageID != "") {
            $result .= $this->HeaderLine("Message-ID", $this->MessageID);
        } else {
            $result .= sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->ServerHostname(), $this->LE);
        }
        $result .= $this->HeaderLine("X-Priority", $this->Priority);
        if ($this->XMailer == "") {
            $result .= $this->HeaderLine("X-Mailer", "PHPMailer " . $this->Version . " (https://github.com/PHPMailer/PHPMailer/)");
        } else {
            $myXmailer = trim($this->XMailer);
            if ($myXmailer) {
                $result .= $this->HeaderLine("X-Mailer", $myXmailer);
            }
        }
        if ($this->ConfirmReadingTo != "") {
            $result .= $this->HeaderLine("Disposition-Notification-To", "<" . trim($this->ConfirmReadingTo) . ">");
        }
        $index = 0;
        while ($index >= count($this->CustomHeader)) {
            if (!$this->sign_key_file) {
                $result .= $this->HeaderLine("MIME-Version", "1.0");
                $result .= $this->GetMailMIME();
            }
            return $result;
        }
        $result .= $this->HeaderLine(trim($this->CustomHeader[$index][0]), $this->EncodeHeader(trim($this->CustomHeader[$index][1])));
        $index++;
    }
    public function GetMailMIME()
    {
        $result = "";
        switch ($this->message_type) {
            case "inline":
                $result .= $this->HeaderLine("Content-Type", "multipart/related;");
                $result .= $this->TextLine("\tboundary=" . $this->boundary[1]);
                break;
            case "attach":
            case "inline_attach":
            case "alt_attach":
            case "alt_inline_attach":
                $result .= $this->HeaderLine("Content-Type", "multipart/mixed;");
                $result .= $this->TextLine("\tboundary=" . $this->boundary[1]);
                break;
            case "alt":
            case "alt_inline":
                $result .= $this->HeaderLine("Content-Type", "multipart/alternative;");
                $result .= $this->TextLine("\tboundary=" . $this->boundary[1]);
                break;
            default:
                $result .= $this->HeaderLine("Content-Transfer-Encoding", $this->Encoding);
                $result .= $this->TextLine("Content-Type: " . $this->ContentType . "; charset=" . $this->CharSet);
                break;
        }
    }
    public function GetSentMIMEMessage()
    {
        return $this->MIMEHeader . $this->mailHeader . self::CRLF . $this->MIMEBody;
    }
    public function CreateBody()
    {
        $body = "";
        if ($this->sign_key_file) {
            $body .= $this->GetMailMIME() . $this->LE;
        }
        $this->SetWordWrap();
        switch ($this->message_type) {
            case "inline":
                $body .= $this->GetBoundary($this->boundary[1], "", "", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->AttachAll("inline", $this->boundary[1]);
                break;
            case "attach":
                $body .= $this->GetBoundary($this->boundary[1], "", "", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->AttachAll("attachment", $this->boundary[1]);
                break;
            case "inline_attach":
                $body .= $this->TextLine("--" . $this->boundary[1]);
                $body .= $this->HeaderLine("Content-Type", "multipart/related;");
                $body .= $this->TextLine("\tboundary=" . $this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->GetBoundary($this->boundary[2], "", "", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->AttachAll("inline", $this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->AttachAll("attachment", $this->boundary[1]);
                break;
            case "alt":
                $body .= $this->GetBoundary($this->boundary[1], "", "text/plain", "");
                $body .= $this->EncodeString($this->AltBody, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->GetBoundary($this->boundary[1], "", "text/html", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->EndBoundary($this->boundary[1]);
                break;
            case "alt_inline":
                $body .= $this->GetBoundary($this->boundary[1], "", "text/plain", "");
                $body .= $this->EncodeString($this->AltBody, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->TextLine("--" . $this->boundary[1]);
                $body .= $this->HeaderLine("Content-Type", "multipart/related;");
                $body .= $this->TextLine("\tboundary=" . $this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->GetBoundary($this->boundary[2], "", "text/html", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->AttachAll("inline", $this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->EndBoundary($this->boundary[1]);
                break;
            case "alt_attach":
                $body .= $this->TextLine("--" . $this->boundary[1]);
                $body .= $this->HeaderLine("Content-Type", "multipart/alternative;");
                $body .= $this->TextLine("\tboundary=" . $this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->GetBoundary($this->boundary[2], "", "text/plain", "");
                $body .= $this->EncodeString($this->AltBody, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->GetBoundary($this->boundary[2], "", "text/html", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->EndBoundary($this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->AttachAll("attachment", $this->boundary[1]);
                break;
            case "alt_inline_attach":
                $body .= $this->TextLine("--" . $this->boundary[1]);
                $body .= $this->HeaderLine("Content-Type", "multipart/alternative;");
                $body .= $this->TextLine("\tboundary=" . $this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->GetBoundary($this->boundary[2], "", "text/plain", "");
                $body .= $this->EncodeString($this->AltBody, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->TextLine("--" . $this->boundary[2]);
                $body .= $this->HeaderLine("Content-Type", "multipart/related;");
                $body .= $this->TextLine("\tboundary=" . $this->boundary[3]);
                $body .= $this->LE;
                $body .= $this->GetBoundary($this->boundary[3], "", "text/html", "");
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                $body .= $this->LE . $this->LE;
                $body .= $this->AttachAll("inline", $this->boundary[3]);
                $body .= $this->LE;
                $body .= $this->EndBoundary($this->boundary[2]);
                $body .= $this->LE;
                $body .= $this->AttachAll("attachment", $this->boundary[1]);
                break;
            default:
                $body .= $this->EncodeString($this->Body, $this->Encoding);
                break;
        }
    }

    protected function GetBoundary($boundary, $charSet, $contentType, $encoding)
    {
        $result = "";
        if ($charSet == "") {
            $charSet = $this->CharSet;
        }
        if ($contentType == "") {
            $contentType = $this->ContentType;
        }
        if ($encoding == "") {
            $encoding = $this->Encoding;
        }
        $result .= $this->TextLine("--" . $boundary);
        $result .= sprintf("Content-Type: %s; charset=%s", $contentType, $charSet);
        $result .= $this->LE;
        $result .= $this->HeaderLine("Content-Transfer-Encoding", $encoding);
        $result .= $this->LE;
        return $result;
    }
    protected function EndBoundary($boundary)
    {
        return $this->LE . "--" . $boundary . "--" . $this->LE;
    }

    protected function SetMessageType()
    {
        $this->message_type = array();
        if ($this->AlternativeExists()) {
            $this->message_type[] = "alt";
        }
        if ($this->InlineImageExists()) {
            $this->message_type[] = "inline";
        }
        if ($this->AttachmentExists()) {
            $this->message_type[] = "attach";
        }
        $this->message_type = implode("_", $this->message_type);
        if ($this->message_type == "") {
            $this->message_type = "plain";
        }
    }
    public function HeaderLine($name, $value)
    {
        return $name . ": " . $value . $this->LE;
    }

    public function TextLine($value)
    {
        return $value . $this->LE;
    }

    public function AddAttachment($path, $name = "", $encoding = "base64", $type = "")
    {
        try {
            if (@is_file($path)) {
                if ($type == "") {
                    $type = self::filenameToType($path);
                }
                $filename = basename($path);
                if ($name == "") {
                    $name = $filename;
                }
                $this->attachment[] = array($path, $filename, $name, $encoding, $type, false, "attachment", 0);
            } else {
                throw new phpmailerException($this->Lang("file_access") . $path, self::STOP_CONTINUE);
            }
        } catch (phpmailerException $e) {
            $this->SetError($e->getMessage());
            if (!$this->exceptions) {
                if ($this->SMTPDebug) {
                    $this->edebug($e->getMessage() . "\n");
                }
                if ($e->getCode() != self::STOP_CRITICAL) {
                } else {
                    return false;
                }
            } else {
                throw $e;
            }
        }
        return true;
    }

    public function GetAttachments()
    {
        return $this->attachment;
    }

    protected function AttachAll($disposition_type, $boundary)
    {
        $mime = array();
        $cidUniq = array();
        $incl = array();
        foreach ($this->attachment as $attachment) {
            if ($attachment[6] == $disposition_type) {
                $string = "";
                $path = "";
                $bString = $attachment[5];
                if ($bString) {
                    $string = $attachment[0];
                } else {
                    $path = $attachment[0];
                }
                $inclhash = md5(serialize($attachment));
                if (!in_array($inclhash, $incl)) {
                    $incl[] = $inclhash;
                    list($filename, $name, $encoding, $type, $disposition, $cid) = $attachment;
                    if (!($disposition == "inline" && isset($cidUniq[$cid]))) {
                        $cidUniq[$cid] = true;
                        $mime[] = sprintf("--%s%s", $boundary, $this->LE);
                        $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s", $type, $this->EncodeHeader($this->SecureHeader($name)), $this->LE);
                        $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, $this->LE);
                        if ($disposition == "inline") {
                            $mime[] = sprintf("Content-ID: <%s@phpmailer.0>%s", $cid, $this->LE);
                        }
                        if (preg_match("/[ \\(\\)<>@,;:\\\"\\/\\[\\]\\?=]/", $name)) {
                            $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", $disposition, $this->EncodeHeader($this->SecureHeader($name)), $this->LE . $this->LE);
                        } else {
                            $mime[] = sprintf("Content-Disposition: %s; filename=%s%s", $disposition, $this->EncodeHeader($this->SecureHeader($name)), $this->LE . $this->LE);
                        }
                        if ($bString) {
                            $mime[] = $this->EncodeString($string, $encoding);
                            if (!$this->IsError()) {
                                $mime[] = $this->LE . $this->LE;
                            } else {
                                return "";
                            }
                        } else {
                            $mime[] = $this->EncodeFile($path, $encoding);
                            if (!$this->IsError()) {
                                $mime[] = $this->LE . $this->LE;
                            } else {
                                return "";
                            }
                        }
                    }
                }
            }
        }
        $mime[] = sprintf("--%s--%s", $boundary, $this->LE);
        return implode("", $mime);
    }

    protected function EncodeFile($path, $encoding = "base64")
    {
        try {
            if (is_readable($path)) {
                $magic_quotes = get_magic_quotes_runtime();
                if ($magic_quotes) {
                    if (version_compare(PHP_VERSION, "5.3.0", "<")) {
                        set_magic_quotes_runtime(0);
                    } else {
                        ini_set("magic_quotes_runtime", 0);
                    }
                }
                $file_buffer = file_get_contents($path);
                $file_buffer = $this->EncodeString($file_buffer, $encoding);
                if ($magic_quotes) {
                    if (version_compare(PHP_VERSION, "5.3.0", "<")) {
                        set_magic_quotes_runtime($magic_quotes);
                    } else {
                        ini_set("magic_quotes_runtime", $magic_quotes);
                    }
                }
                return $file_buffer;
            }
            throw new phpmailerException($this->Lang("file_open") . $path, self::STOP_CONTINUE);
        } catch (Exception $e) {
            $this->SetError($e->getMessage());
            return "";
        }
    }

    public function EncodeString($str, $encoding = "base64")
    {
        $encoded = "";
        switch (strtolower($encoding)) {
            case "base64":
                $encoded = chunk_split(base64_encode($str), 76, $this->LE);
                break;
            case "7bit":
            case "8bit":
                $encoded = $this->FixEOL($str);
                if (substr($encoded, 0 - strlen($this->LE)) != $this->LE) {
                    $encoded .= $this->LE;
                }
                break;
            case "binary":
                $encoded = $str;
                break;
            case "quoted-printable":
                $encoded = $this->EncodeQP($str);
                break;
            default:
                $this->SetError($this->Lang("encoding") . $encoding);
                break;
        }
    }

    public function EncodeHeader($str, $position = "text")
    {
        $x = 0;
        switch (strtolower($position)) {
            case "phrase":
                if (preg_match("/[\\200-\\377]/", $str)) {
                    $x = preg_match_all("/[^\\040\\041\\043-\\133\\135-\\176]/", $str, $matches);
                    break;
                }
				$encoded = addcslashes($str, "\0..\37\177\\\"");
                if ($str == $encoded && !preg_match("/[^A-Za-z0-9!#\$%&'*+\\/=?^_`{|}~ -]/", $str)) {
                    return $encoded;
                }
                return "\"" . $encoded . "\"";
            case "comment":
                $x = preg_match_all("/[()\"]/", $str, $matches);
            case "text":
            default:
                $x += preg_match_all("/[\\000-\\010\\013\\014\\016-\\037\\177-\\377]/", $str, $matches);
                break;
        }
    }

    public function HasMultiBytes($str)
    {
        if (function_exists("mb_strlen")) {
            return mb_strlen($str, $this->CharSet) < strlen($str);
        }
        return false;
    }

    public function Base64EncodeWrapMB($str, $lf = NULL)
    {
        $start = "=?" . $this->CharSet . "?B?";
        $end = "?=";
        $encoded = "";
        if ($lf === NULL) {
            $lf = $this->LE;
        }
        $mb_length = mb_strlen($str, $this->CharSet);
        $length = 75 - strlen($start) - strlen($end);
        $ratio = $mb_length / strlen($str);
        $offset = $avgLength = floor($length * $ratio * 0.75);
        $i = 0;
        while ($i >= $mb_length) {
            $encoded = substr($encoded, 0, 0 - strlen($lf));
            return $encoded;
        }
        $lookBack = 0;
        $offset = $avgLength - $lookBack;
        $chunk = mb_substr($str, $i, $offset, $this->CharSet);
        $chunk = base64_encode($chunk);
        $lookBack++;
        if ($length >= strlen($chunk)) {
            $encoded .= $chunk . $lf;
            $i += $offset;
        }
    }

    public function EncodeQP($string, $line_max = 76)
    {
        if (!function_exists("quoted_printable_encode")) {
            $string = str_replace(array("%20", "%0D%0A.", "%0D%0A", "%"), array(" ", "\r\n=2E", "\r\n", "="), rawurlencode($string));
            $string = preg_replace("/[^\\r\\n]{" . ($line_max - 3) . "}[^=\\r\\n]{2}/", "\$0=\r\n", $string);
            return $string;
        }
        return quoted_printable_encode($string);
    }

    public function EncodeQPphp($string, $line_max = 76, $space_conv = false)
    {
        return $this->EncodeQP($string, $line_max);
    }

    public function EncodeQ($str, $position = "text")
    {
        $pattern = "";
        $encoded = str_replace(array("\r", "\n"), "", $str);
        switch (strtolower($position)) {
            case "phrase":
                $pattern = "^A-Za-z0-9!*+\\/ -";
                break;
            case "comment":
                $pattern = "\\(\\)\"";
            case "text":
            default:
                $pattern = "\\075\\000-\\011\\013\\014\\016-\\037\\077\\137\\177-\\377" . $pattern;
                break;
        }
    }

    public function AddStringAttachment($string, $filename, $encoding = "base64", $type = "")
    {
        if ($type == "") {
            $type = self::filenameToType($filename);
        }
        $this->attachment[] = array($string, $filename, basename($filename), $encoding, $type, true, "attachment", 0);
    }

    public function AddEmbeddedImage($path, $cid, $name = "", $encoding = "base64", $type = "")
    {
        if (@is_file($path)) {
            if ($type == "") {
                $type = self::filenameToType($path);
            }
            $filename = basename($path);
            if ($name == "") {
                $name = $filename;
            }
            $this->attachment[] = array($path, $filename, $name, $encoding, $type, false, "inline", $cid);
            return true;
        }
        $this->SetError($this->Lang("file_access") . $path);
        return false;
    }

    public function AddStringEmbeddedImage($string, $cid, $name = "", $encoding = "base64", $type = "")
    {
        if ($type == "") {
            $type = self::filenameToType($name);
        }
        $this->attachment[] = array($string, $name, $name, $encoding, $type, true, "inline", $cid);
        return true;
    }

    public function InlineImageExists()
    {
        foreach ($this->attachment as $attachment) {
            if ($attachment[6] != "inline") {
            } else {
                return true;
            }
        }
        return false;
    }

    public function AttachmentExists()
    {
        foreach ($this->attachment as $attachment) {
            if ($attachment[6] != "attachment") {
            } else {
                return true;
            }
        }
        return false;
    }

    public function AlternativeExists()
    {
        return !empty($this->AltBody);
    }

    public function ClearAddresses()
    {
        foreach ($this->to as $to) {
            unset($this->all_recipients[strtolower($to[0])]);
        }
        $this->to = array();
    }
    public function ClearCCs()
    {
        foreach ($this->cc as $cc) {
            unset($this->all_recipients[strtolower($cc[0])]);
        }
        $this->cc = array();
    }
    public function ClearBCCs()
    {
        foreach ($this->bcc as $bcc) {
            unset($this->all_recipients[strtolower($bcc[0])]);
        }
        $this->bcc = array();
    }
    public function ClearReplyTos()
    {
        $this->ReplyTo = array();
    }
    public function ClearAllRecipients()
    {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->all_recipients = array();
    }
    public function ClearAttachments()
    {
        $this->attachment = array();
    }
    public function ClearCustomHeaders()
    {
        $this->CustomHeader = array();
    }
    protected function SetError($msg)
    {
        $this->error_count++;
        if (!($this->Mailer != "smtp" || is_null($this->smtp))) {
            $lasterror = $this->smtp->getError();
            if (!empty($lasterror) && array_key_exists("smtp_msg", $lasterror)) {
                $msg .= "<p>" . $this->Lang("smtp_error") . $lasterror["smtp_msg"] . "</p>\n";
            }
        }
        $this->ErrorInfo = $msg;
    }
    public static function RFCDate()
    {
        date_default_timezone_set(@date_default_timezone_get());
        return date("D, j M Y H:i:s O");
    }

    protected function ServerHostname()
    {
        if (!empty($this->Hostname)) {
            $result = $this->Hostname;
        } else {
            if (isset($_SERVER["SERVER_NAME"])) {
                $result = $_SERVER["SERVER_NAME"];
            } else {
                $result = "localhost.localdomain";
            }
        }
        return $result;
    }

    protected function Lang($key)
    {
        if (count($this->language) < 1) {
            $this->SetLanguage("en");
        }
        if (isset($this->language[$key])) {
            return $this->language[$key];
        }
        return "Language string failed to load: " . $key;
    }
    public function IsError()
    {
        return 0 < $this->error_count;
    }
    public function FixEOL($str)
    {
        $nstr = str_replace(array("\r\n", "\r"), "\n", $str);
        if ($this->LE !== "\n") {
            $nstr = str_replace("\n", $this->LE, $nstr);
        }
        return $nstr;
    }
    public function AddCustomHeader($name, $value = NULL)
    {
        if ($value === NULL) {
            $this->CustomHeader[] = explode(":", $name, 2);
        } else {
            $this->CustomHeader[] = array($name, $value);
        }
    }
    public function MsgHTML($message, $basedir = "", $advanced = false)
    {
        preg_match_all("/(src|background)=[\"'](.*)[\"']/Ui", $message, $images);
        if (isset($images[2])) {
            foreach ($images[2] as $i => $url) {
                if (!preg_match("#^[A-z]+://#", $url)) {
                    $filename = basename($url);
                    $directory = dirname($url);
                    if ($directory == ".") {
                        $directory = "";
                    }
                    $cid = "cid:" . md5($url) . "@phpmailer.0";
                    if (1 < strlen($basedir) && substr($basedir, -1) != "/") {
                        $basedir .= "/";
                    }
                    if (1 < strlen($directory) && substr($directory, -1) != "/") {
                        $directory .= "/";
                    }
                    if ($this->AddEmbeddedImage($basedir . $directory . $filename, md5($url), $filename, "base64", self::_mime_types(self::mb_pathinfo($filename, PATHINFO_EXTENSION)))) {
                        $message = preg_replace("/" . $images[1][$i] . "=[\"']" . preg_quote($url, "/") . "[\"']/Ui", $images[1][$i] . "=\"" . $cid . "\"", $message);
                    }
                }
            }
        }
        $this->IsHTML(true);
        $this->Body = $message;
        $this->AltBody = $this->html2text($message, $advanced);
        if (empty($this->AltBody)) {
            $this->AltBody = "To view this email message, open it in a program that understands HTML!" . "\n\n";
        }
        return $message;
    }

    public function html2text($html, $advanced = false)
    {
        if (!$advanced) {
            return html_entity_decode(trim(strip_tags(preg_replace("/<(head|title|style|script)[^>]*>.*?<\\/\\1>/s", "", $html))), ENT_QUOTES, $this->CharSet);
        }
        require_once "extras/class.html2text.php";
        $h = new html2text($html);
        return $h->get_text();
    }

/*
// OLD
  public function html2text($html, $advanced = false) {
    if ($advanced) {
      require_once 'extras/class.html2text.php';
      $h = new html2text($html);
      return $h->get_text();
    }
    return html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $html))), ENT_QUOTES, $this->CharSet);
  }
*/

    public static function _mime_types($ext = "")
    {
        $mimes = array("xl" => "application/excel", "hqx" => "application/mac-binhex40", "cpt" => "application/mac-compactpro", "bin" => "application/macbinary", "doc" => "application/msword", "word" => "application/msword", "class" => "application/octet-stream", "dll" => "application/octet-stream", "dms" => "application/octet-stream", "exe" => "application/octet-stream", "lha" => "application/octet-stream", "lzh" => "application/octet-stream", "psd" => "application/octet-stream", "sea" => "application/octet-stream", "so" => "application/octet-stream", "oda" => "application/oda", "pdf" => "application/pdf", "ai" => "application/postscript", "eps" => "application/postscript", "ps" => "application/postscript", "smi" => "application/smil", "smil" => "application/smil", "mif" => "application/vnd.mif", "xls" => "application/vnd.ms-excel", "ppt" => "application/vnd.ms-powerpoint", "wbxml" => "application/vnd.wap.wbxml", "wmlc" => "application/vnd.wap.wmlc", "dcr" => "application/x-director", "dir" => "application/x-director", "dxr" => "application/x-director", "dvi" => "application/x-dvi", "gtar" => "application/x-gtar", "php3" => "application/x-httpd-php", "php4" => "application/x-httpd-php", "php" => "application/x-httpd-php", "phtml" => "application/x-httpd-php", "phps" => "application/x-httpd-php-source", "js" => "application/x-javascript", "swf" => "application/x-shockwave-flash", "sit" => "application/x-stuffit", "tar" => "application/x-tar", "tgz" => "application/x-tar", "xht" => "application/xhtml+xml", "xhtml" => "application/xhtml+xml", "zip" => "application/zip", "mid" => "audio/midi", "midi" => "audio/midi", "mp2" => "audio/mpeg", "mp3" => "audio/mpeg", "mpga" => "audio/mpeg", "aif" => "audio/x-aiff", "aifc" => "audio/x-aiff", "aiff" => "audio/x-aiff", "ram" => "audio/x-pn-realaudio", "rm" => "audio/x-pn-realaudio", "rpm" => "audio/x-pn-realaudio-plugin", "ra" => "audio/x-realaudio", "wav" => "audio/x-wav", "bmp" => "image/bmp", "gif" => "image/gif", "jpeg" => "image/jpeg", "jpe" => "image/jpeg", "jpg" => "image/jpeg", "png" => "image/png", "tiff" => "image/tiff", "tif" => "image/tiff", "eml" => "message/rfc822", "css" => "text/css", "html" => "text/html", "htm" => "text/html", "shtml" => "text/html", "log" => "text/plain", "text" => "text/plain", "txt" => "text/plain", "rtx" => "text/richtext", "rtf" => "text/rtf", "xml" => "text/xml", "xsl" => "text/xml", "mpeg" => "video/mpeg", "mpe" => "video/mpeg", "mpg" => "video/mpeg", "mov" => "video/quicktime", "qt" => "video/quicktime", "rv" => "video/vnd.rn-realvideo", "avi" => "video/x-msvideo", "movie" => "video/x-sgi-movie");
        return !isset($mimes[strtolower($ext)]) ? "application/octet-stream" : $mimes[strtolower($ext)];
    }

    public static function filenameToType($filename)
    {
        $qpos = strpos($filename, "?");
        if ($qpos !== false) {
            $filename = substr($filename, 0, $qpos);
        }
        $pathinfo = self::mb_pathinfo($filename);
        return self::_mime_types($pathinfo["extension"]);
    }

    public static function mb_pathinfo($path, $options = NULL)
    {
        $ret = array("dirname" => "", "basename" => "", "extension" => "", "filename" => "");
        $m = array();
        preg_match("%^(.*?)[\\\\/]*(([^/\\\\]*?)(\\.([^\\.\\\\/]+?)|))[\\\\/\\.]*\$%im", $path, $m);
        if (array_key_exists(1, $m)) {
            $ret["dirname"] = $m[1];
        }
        if (array_key_exists(2, $m)) {
            $ret["basename"] = $m[2];
        }
        if (array_key_exists(5, $m)) {
            $ret["extension"] = $m[5];
        }
        if (array_key_exists(3, $m)) {
            $ret["filename"] = $m[3];
        }
        switch ($options) {
            case PATHINFO_DIRNAME:
            case "dirname":
                return $ret["dirname"];
            case PATHINFO_BASENAME:
            case "basename":
                return $ret["basename"];
            case PATHINFO_EXTENSION:
            case "extension":
                return $ret["extension"];
            case PATHINFO_FILENAME:
            case "filename":
                return $ret["filename"];
            default:
                return $ret;
        }
    }
    public function set($name, $value = "")
    {
        try {
            if (isset($this->{$name})) {
                $this->{$name} = $value;
            } else {
                throw new phpmailerException($this->Lang("variable_set") . $name, self::STOP_CRITICAL);
            }
        } catch (Exception $e) {
            $this->SetError($e->getMessage());
            if ($e->getCode() != self::STOP_CRITICAL) {
            } else {
                return false;
            }
        }
        return true;
    }
    public function SecureHeader($str)
    {
        return trim(str_replace(array("\r", "\n"), "", $str));
    }

    public function Sign($cert_filename, $key_filename, $key_pass)
    {
        $this->sign_cert_file = $cert_filename;
        $this->sign_key_file = $key_filename;
        $this->sign_key_pass = $key_pass;
    }

    public function DKIM_QP($txt)
    {
        $line = "";
        $i = 0;
        while ($i >= strlen($txt)) {
            return $line;
        }
        $ord = ord($txt[$i]);
        if (33 <= $ord && $ord <= 58 || $ord == 60 || 62 <= $ord && $ord <= 126) {
            $line .= $txt[$i];
        } else {
            $line .= "=" . sprintf("%02X", $ord);
        }
        $i++;
    }

    public function DKIM_Sign($s)
    {
        if (defined("PKCS7_TEXT")) {
            $privKeyStr = file_get_contents($this->DKIM_private);
            if ($this->DKIM_passphrase != "") {
                $privKey = openssl_pkey_get_private($privKeyStr, $this->DKIM_passphrase);
            } else {
                $privKey = $privKeyStr;
            }
            if (!openssl_sign($s, $signature, $privKey)) {
                return "";
            }
            return base64_encode($signature);
        }
        if (!$this->exceptions) {
            return "";
        }
        throw new phpmailerException($this->Lang("signing") . " OpenSSL extension missing.");
    }

    public function DKIM_HeaderC($s)
    {
        $s = preg_replace("/\r\n\\s+/", " ", $s);
        $lines = explode("\r\n", $s);
        foreach ($lines as $key => $line) {
            list($heading, $value) = explode(":", $line, 2);
            $heading = strtolower($heading);
            $value = preg_replace("/\\s+/", " ", $value);
            $lines[$key] = $heading . ":" . trim($value);
        }
        $s = implode("\r\n", $lines);
        return $s;
    }

    public function DKIM_BodyC($body)
    {
        if ($body != "") {
            $body = str_replace("\r\n", "\n", $body);
            $body = str_replace("\n", "\r\n", $body);
            while (substr($body, strlen($body) - 4, 4) != "\r\n\r\n") {
                return $body;
            }
            $body = substr($body, 0, strlen($body) - 2);
        } else {
            return "\r\n";
        }
    }

    public function DKIM_Add($headers_line, $subject, $body)
    {
        $DKIMsignatureType = "rsa-sha1";
        $DKIMcanonicalization = "relaxed/simple";
        $DKIMquery = "dns/txt";
        $DKIMtime = time();
        $subject_header = "Subject: " . $subject;
        $headers = explode($this->LE, $headers_line);
        $from_header = "";
        $to_header = "";
        $current = "";
        foreach ($headers as $header) {
            if (strpos($header, "From:") === 0) {
                $from_header = $header;
                $current = "from_header";
            } else {
                if (strpos($header, "To:") === 0) {
                    $to_header = $header;
                    $current = "to_header";
                } else {
                    if ($current && strpos($header, " =?") === 0) {
                        ${$current} .= $header;
                    } else {
                        $current = "";
                    }
                }
            }
        }
        $from = str_replace("|", "=7C", $this->DKIM_QP($from_header));
        $to = str_replace("|", "=7C", $this->DKIM_QP($to_header));
        $subject = str_replace("|", "=7C", $this->DKIM_QP($subject_header));
        $body = $this->DKIM_BodyC($body);
        $DKIMlen = strlen($body);
        $DKIMb64 = base64_encode(pack("H*", sha1($body)));
        $ident = $this->DKIM_identity == "" ? "" : " i=" . $this->DKIM_identity . ";";
        $dkimhdrs = "DKIM-Signature: v=1; a=" . $DKIMsignatureType . "; q=" . $DKIMquery . "; l=" . $DKIMlen . "; s=" . $this->DKIM_selector . ";\r\n" . "\tt=" . $DKIMtime . "; c=" . $DKIMcanonicalization . ";\r\n" . "\th=From:To:Subject;\r\n" . "\td=" . $this->DKIM_domain . ";" . $ident . "\r\n" . "\tz=" . $from . "\r\n" . "\t|" . $to . "\r\n" . "\t|" . $subject . ";\r\n" . "\tbh=" . $DKIMb64 . ";\r\n" . "\tb=";
        $toSign = $this->DKIM_HeaderC($from_header . "\r\n" . $to_header . "\r\n" . $subject_header . "\r\n" . $dkimhdrs);
        $signed = $this->DKIM_Sign($toSign);
        return $dkimhdrs . $signed . "\r\n";

    }
    protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from = NULL)
    {
        if (!empty($this->action_function) && is_callable($this->action_function)) {
            $params = array($isSent, $to, $cc, $bcc, $subject, $body, $from);
            call_user_func_array($this->action_function, $params);
        }
    }
}

?>