<?php

class FileLib{

	public static function readDirectory($inputdir){
		$file_info_array = array();

		if ($handle = opendir($inputdir)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$time = substr($entry, 0, strpos($entry, "_"));
					$name = substr($entry, strpos($entry, "_") + 1, strlen($entry)); 
					$mtime = filemtime($inputdir . $entry);
					array_push($file_info_array, array('time'=>$time, 'name'=>$name, 'mtime'=>$mtime));
				}
			}
			closedir($handle);
		}

		return $file_info_array;
	}

	public static function getFilenameByFid($fid, $file_info_array){
		$filename = "";
		for($i = 0; $i < count($file_info_array); $i++){
			if($file_info_array[$i]['time'] == $fid){
				$filename = $file_info_array[$i]['name'];
				break;
			}
		}
		return $filename;
	}

	public static function getFilename($inputdir, $fid){
		$file_info_array = array();
		$file_info_array = self::readDirectory($inputdir);
		return self::getFilenameByFid($fid, $file_info_array);
	}

	public static function getDatapackMeta($datapack){
		$fp = fopen($datapack, "r");
		if($fp){
			$start_date = trim(fgets($fp, 4096), "#\n");
			$end_date = trim(fgets($fp, 4096), "#\n");
			$date_num = trim(fgets($fp, 4096), "#\n");
			fclose($fp);
			return array('start_date'=>$start_date, 'end_date'=>$end_date, 'date_num'=>$date_num);
		} else {
			return array();
		}
	}
}

?>
