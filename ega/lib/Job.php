<?php

class Job{

        private $db;
        private $table;
        private $debug;

        function __construct($db, $table, $debug=0){
                $this->db = $db;
                $this->table = $table;
                $this->debug = $debug;
        }

	public function getJobsByKeyword($keyword, $limit=20){
	        if($this->db == null || $this->table == null){
                        return false;
                }

                $sql = "SELECT * FROM $this->table WHERE filename LIKE '%" . mysql_real_escape_string($keyword) . "%' ORDER BY ctime DESC LIMIT $limit";
                
		$result = $this->db->executeSQL($sql);
                if($result === false){
                        return false;
                }

                if(mysql_num_rows($result) == 0){
                        return array();
                }

                $result_array = array();
                while($assoc_array = mysql_fetch_assoc($result)){
                	array_push($result_array, $assoc_array);
		}

                return $result_array;
	}

	public function getJobById($job_id){
	        if($this->db == null || $this->table == null || !isset($job_id)){
                        return false;
                }

                $sql = "SELECT * FROM $this->table WHERE job_id='" . mysql_real_escape_string($job_id) . "'";
		
		$result = $this->db->executeSQL($sql);
                if($result === false){
                        return false;
                }
		
		if(mysql_num_rows($result) == 0){
			return array();
		}
	
		$ret = mysql_fetch_assoc($result);
		return $ret;
	}


        /* Get all records in users array */
        public function getJobs($offset, $limit){

                if($this->db == null || $this->table == null){
                        return false;
                }

                $sql = "SELECT * FROM $this->table ORDER BY ctime DESC LIMIT $offset, $limit";
                
		$result = $this->db->executeSQL($sql);
                if($result === false){
                        return false;
                }

                if(mysql_num_rows($result) == 0){
                        return array();
                }

                $result_array = array();
                while($assoc_array = mysql_fetch_assoc($result)){
                	array_push($result_array, $assoc_array);
		}

                return $result_array;
        }

	public function getTotalJobs(){

                if($this->db == null || $this->table == null){
                        return false;
                }

                $sql = "SELECT COUNT(*) AS job_count FROM $this->table";
                
		$result = $this->db->executeSQL($sql);
                if($result === false){
                        return false;
                }

                $assoc_array = mysql_fetch_assoc($result);
                return $assoc_array['job_count'];
        }

        public function insertJob($filename, $filets, $datapack_start, $datapack_end, $event_start, $event_end, $window_size, $status){
                if($this->db == null || $this->table == null || empty($filename) || empty($filets) || empty($datapack_start) || empty($datapack_end) || empty($event_start) || empty($event_end) || empty($window_size) || empty($status) ){
                        return false;
                }

                $sql = "INSERT INTO $this->table (filename, filets, datapack_start, datapack_end, event_start, event_end, window_size, status, ctime) VALUES (" .
                        "'" . mysql_real_escape_string($filename) . "'," .
                        "'" . mysql_real_escape_string($filets) . "'," .
                        "'" . mysql_real_escape_string($datapack_start) . "'," .
                        "'" . mysql_real_escape_string($datapack_end) . "'," .
                        "'" . mysql_real_escape_string($event_start) . "'," .
                        "'" . mysql_real_escape_string($event_end) . "'," .
                        "'" . mysql_real_escape_string($window_size) . "'," .
                        "'" . mysql_real_escape_string($status) . "'," .
                        "null);";

                $ret = $this->db->executeSQL($sql);
		$jobid = mysql_insert_id();
                if($ret !== false){
                        return $jobid;
                }
                return false;
        }
	
	public function updateJob($job_id, $status){
	        if($this->db == null || $this->table == null || empty($job_id) || empty($status)){
                        return false;
                }

		$sql = "UPDATE $this->table SET status='" . mysql_real_escape_string($status) . "' WHERE job_id='" . mysql_real_escape_string($job_id) . "'";
		error_log($sql);
		$ret = $this->db->executeSQL($sql);
                if($ret !== false){
                	return true;
		}
                return false;
	}

        public function deleteJob($job_id){
                if($this->db == null || $this->table == null || empty($job_id)){
                        return false;
                }

                $sql = "DELETE FROM $this->table WHERE job_id=" .
                        "'" . mysql_real_escape_string($job_id) . "'";
                $ret = $this->db->executeSQL($sql);
                return $ret;
        }

}

?>
