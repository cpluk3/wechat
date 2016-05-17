<?php
include_once(dirname(__FILE__) . "/../lib/Util.php");
include_once(dirname(__FILE__) . "/../lib/Merge.php");
date_default_timezone_set('Asia/Hong_Kong');

//Read Config file
$base_path = dirname(__FILE__) . "/..";
$config_file_path = "$base_path/conf/config.ini";
$ini_array = parse_ini_file($config_file_path, true);
$debug = $ini_array['setting']['debug'];

//search raw data folder
$raw_file_prefix = $base_path . "/" . $ini_array['datapack']['rawfolder'] . "/" . $ini_array['datapack']['rawprefix'];
$tmp_file = $base_path . "/" . $ini_array['datapack']['tmpfolder'] . "/" . $ini_array['datapack']['tmpfile'];

$search_end = 0;
while(!$search_end){

	$datapack_file = $base_path . "/" . $ini_array['datapack']['datafolder'] . '/' . $ini_array['datapack']['datafile'];
	$fp = fopen($datapack_file, "r");
	$start_date = ltrim(Util::getLine($fp), "#");
	$end_date = ltrim(Util::getLine($fp), "#");
	$date_num = ltrim(Util::getLine($fp), "#");
	fclose($fp);

	//Read Data file meta
	Util::debugMsg(__FILE__, __FUNCTION__, "start date: $start_date", $debug);
	Util::debugMsg(__FILE__, __FUNCTION__, "end date: $end_date", $debug);
	Util::debugMsg(__FILE__, __FUNCTION__, "date_num: $date_num", $debug);

	$prev_date = Date("Ymd", strtotime($start_date . " - 1 day") );
	$next_date = Date("Ymd", strtotime($end_date . " + 1 day") );

	Util::debugMsg(__FILE__, __FUNCTION__, "prev date: $prev_date", $debug);
	Util::debugMsg(__FILE__, __FUNCTION__, "next date: $next_date", $debug);

	//check if either file exists
	$next_date_file = $raw_file_prefix . $next_date;
	$prev_date_file = $raw_file_prefix . $prev_date; 

	if(file_exists($next_date_file)){

		$mergeObj = new Merge($datapack_file, $next_date_file, $tmp_file, $debug);
		$ret = $mergeObj->process($start_date, $next_date, $date_num + 1);
		//Replace datapackfile
		if($ret === false){
			break;
		}

		rename($tmp_file, $datapack_file);
		unset($mergeObj);
		//remove the uin source file
		continue;
	}

	if(file_exists($prev_date_file)){
		$mergeObj = new Merge($prev_date_file, $datapack_file, $tmp_file, $debug);
		$ret = $mergeObj->process($prev_date, $end_date, $date_num + 1);
		//Replace datapackfile
		if($ret === false){
			break;
		}

		//Replace datapackfile
		rename($tmp_file, $datapack_file);
		unset($mergeObj);
		//remove the uin source file
		continue;
	}
	$search_end = 1;
	Util::debugMsg(__FILE__, __FUNCTION__, "Search End", $debug);
}

?>
