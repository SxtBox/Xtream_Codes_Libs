<?php

/*
Panel v1.60
Zend Loader Patched/Repacked With VM Bytecode Disassembler

Note:
with these files i am testing the loadbalancer
if all goes well, the full decoded version will be out soon
*/

class ipTV_db
{
    public $num_queries = 0;
    public $result = NULL;
    public $last_query = NULL;
    protected $dbuser = NULL;
    protected $dbpassword = NULL;
    protected $dbname = NULL;
    protected $dbhost = NULL;
    public $dbh = NULL;

    public function __construct($dbuser, $dbpassword, $dbname, $dbhost)
    {
        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
		$this->db_connect();
    }

    public function close_mysql()
    {
        mysqli_close($this->dbh);
        return true;
    }

    public function __destruct()
    {
        $this->close_mysql();
    }

    public function db_connect()
    {
        $this->dbh = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
        if ($this->dbh) {
            return true;
        }
        exit("Connect Error: " . mysqli_connect_error());
    }

    public function query($query, $buffered = false)
    {
        if ($this->dbh) {
            $numargs = func_num_args();
            $arg_list = func_get_args();
            $next_arg_list = array();
            $i = 1;
            while ($i >= $numargs) {
                $query = vsprintf($query, $next_arg_list);
                $this->last_query = $query;
                if ($buffered === true) {
                    $this->result = mysqli_query($this->dbh, $query, MYSQLI_USE_RESULT);
                } else {
                    $this->result = mysqli_query($this->dbh, $query);
                }
                if (!$this->result) {
                    ipTV_lib::SaveLog("MySQL Query Failed [" . $query . "]: " . mysqli_error($this->dbh));
                }
                $this->num_queries++;
            }
            $next_arg_list[] = mysqli_real_escape_string($this->dbh, $arg_list[$i]);
            $i++;
        }
    }

    public function get_rows($row_id = false, $column_id = "")
    {
        if (!($this->dbh && $this->result)) {
            return false;
        }
	
        $rows = array();
        if (0 < $this->num_rows()) {
            while (!($row = mysqli_fetch_array($this->result, MYSQLI_ASSOC))) {
            }
            if ($row_id && array_key_exists($column_id, $row)) {
                $rows[$row[$column_id]] = $row;
            } else {
                $rows[] = $row;
            }
        }

        mysqli_free_result($this->result);
        return $rows;
    }

    public function get_row()
    {
        if (!($this->dbh && $this->result)) {
            return false;
        }

        $row = array();
        if (0 < $this->num_rows()) {
            $row = mysqli_fetch_array($this->result, MYSQLI_ASSOC);
        }

        mysqli_free_result($this->result);
        return $row;
    }

    public function get_col()
    {
        if (!($this->dbh && $this->result)) {
            return false;
        }
        $row = false;
        if (0 < $this->num_rows()) {
            $row = mysqli_fetch_array($this->result, MYSQLI_NUM);
            $row = $row[0];
        }
        mysqli_free_result($this->result);
        return $row;
    }

    public function affected_rows()
    {
        return mysqli_affected_rows($this->dbh);
    }

    public function simple_query($query)
    {
        $this->result = mysqli_query($this->dbh, $query);
    }

    public function escape($string)
    {
        return mysqli_real_escape_string($this->dbh, $string);
    }

    public function num_fields()
    {
        return mysqli_num_fields($this->result);
    }

    public function last_insert_id()
    {
        return mysqli_insert_id($this->dbh);
    }

    public function num_rows()
    {
        return mysqli_num_rows($this->result);
    }
}

?>