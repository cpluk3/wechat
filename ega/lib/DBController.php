<?php

include_once(dirname(__FILE__) . "/Util.php");

class DBController{

    private $dbhost;
    private $dbuser;
    private $dbpass;
    private $dbname;
    public $conn = '';
    private $debug = 0;

    function __construct($dbhost, $dbuser, $dbpass, $dbname, $debug=0){
        //connect to database
        $this->debug = $debug;
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbname = $dbname;

        $this->conn = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);

        if(!$this->conn){
            if($this->debug){
                error_log('[DBController] Connect Failed:' . mysql_error());
            }
        } else {
                mysql_select_db($dbname, $this->conn);
                }
    }

    public function executeSQL($sql){
        if($this->debug){
                        error_log($sql);
        }
        $result = mysql_query($sql);
        return $result;
    }

    public function close(){
        $this->conn = null;
    }

    public function startTransaction(){
        mysql_query("START TRANSACTION");
    }

    public function commit(){
        mysql_query("COMMIT");
    }

}
?>
