<?php

include_once(dirname(__FILE__) . "/../lib/Util.php");
include_once(dirname(__FILE__) . "/../lib/DBController.php");
include_once(dirname(__FILE__) . "/../lib/Job.php");
include_once(dirname(__FILE__) . "/../lib/Compute.php");

//Get job id
if(count($argv) >= 8){
	$jobid = $argv[7];
} else {
	$jobid = -1;
}

//Read Config file
$base_path = dirname(__FILE__) . "/..";
$config_file_path = "$base_path/conf/config.ini";
$ini_array = parse_ini_file($config_file_path, true);
$debug = $ini_array['setting']['debug'];

//Get input arguments
if(count($argv) < 7){
	echo "[Usage] php ./" . $argv[0] . " [Datapack] [Output File] [Input File] [Start index] [End index] [Window Size] [jobid]\n";
	exit;
}

$flag_all = 0;
$datapack_file = $argv[1];
$output_file =  $argv[2];
$input_file = $argv[3];

if($input_file == 'all'){
	$flag_all = 1;
}

$cutoff_start = $argv[4];
$cutoff_end = $argv[5];
$window_size = $argv[6];

Util::debugMsg(__FILE__, __FUNCTION__, "cutoff_start: $cutoff_start", $debug, 1);
Util::debugMsg(__FILE__, __FUNCTION__, "cutoff_end: $cutoff_end", $debug, 1);
Util::debugMsg(__FILE__, __FUNCTION__, "window_size: $window_size", $debug, 1);

//open files
if(!$flag_all){
	$handle_input = fopen($input_file, "r");
	if($handle_input == null){
		Util::debugMsg(__FILE__, __FUNCTION__, "Uin File Handle is Empty", $debug, 1);
		exit;
	}
}
$handle_datapack = fopen($datapack_file, "r");
if($handle_datapack == null){
	Util::debugMsg(__FILE__, __FUNCTION__, "Datapack File Handle is Empty", $debug, 1);
	exit;
}
$handle_output = fopen($output_file, "w");
if($handle_output == null){
	Util::debugMsg(__FILE__, __FUNCTION__, "Output File Handle is Empty", $debug, 1);
	exit;
}
/* Get Datapack Meta */
$start_date = ltrim(getLine($handle_datapack), "#");
$end_date = ltrim(getLine($handle_datapack), "#");
$datenum = ltrim(getLine($handle_datapack), "#");

Util::debugMsg(__FILE__, __FUNCTION__, "Start Date: $start_date", $debug, 1);
Util::debugMsg(__FILE__, __FUNCTION__, "End Date: $end_date", $debug, 1);
Util::debugMsg(__FILE__, __FUNCTION__, "Date Num: $datenum", $debug, 1);

/* Write meta data */
fwrite($handle_output, "#base date: $start_date\n");
fwrite($handle_output, "#uin, rate before, rate after, rate diff, date offset\n");

/* boundary check */
$start_index = $cutoff_start - $window_size;
if($start_index < 0){
	Util::debugMsg(__FILE__, __FUNCTION__, "start index < 0, please reset windows size or range of consideration", $debug, 1);
	exit;
}

if($cutoff_end + $window_size -1 >= $datenum){
	Util::debugMsg(__FILE__, __FUNCTION__, "max index >= limit, please reset windows size or range of consideration", $debug, 1);
	exit;
}

/* Get First line of datapack */
$buffer_datapack = getLine($handle_datapack);
$datapack_uin = getUin($buffer_datapack);

if(!$flag_all){
//read file line by line
	$buffer_input = getLine($handle_input);
	$input_uin = $buffer_input;
} else {
	$input_uin = $datapack_uin;
}


$input_count = 1;

while(1){
	//debug

	if(!$flag_all && $buffer_input == false){
		Util::debugMsg(__FILE__, __FUNCTION__, "input file reaches the end, leave system", $debug, 1);
		break; //input data reach the end, leave system	
	}

	if($buffer_datapack == false){
		Util::debugMsg(__FILE__, __FUNCTION__, "datapack file reaches the end, leave system", $debug, 1);
		break; //data pack reach the end, leave system
	}

	
	$compare_result = bccomp($input_uin, $datapack_uin);

        if( $compare_result == 0 ){

            //print Uin 1 value 1 value 2
	    $count_before = 0;
	    $count_after = 0;
	    $rate_before = 0;
	    $rate_after = 0;
	    $rate_diff = 0;
	    $offset = 0;
            $date_index = 0;

	    $data_map = getMap($buffer_datapack);

            //first time computation
            $temp_count_before = 0;
	    $temp_count_after = 0;
	    $temp_rate_before = 0;
	    $temp_rate_after = 0;
	    $temp_rate_diff = 0;
		
	    if($input_uin == '100962'){
		echo "start_index: $start_index\n";
	    }

	    for($i = $start_index; $i < $start_index + $window_size; $i++){
		$count_before = $count_before + $data_map[$i];
	    }
            $rate_before = $count_before / $window_size;
 
	    for(;$i < $start_index + $window_size * 2; $i++){
		$count_after = $count_after + $data_map[$i];
	    }
            $rate_after = $count_after / $window_size;
	    $rate_diff = $rate_after - $rate_before; 
	    $offset = $start_index + $window_size;

	    $temp_count_before = $count_before;
	    $temp_count_after = $count_after;

	    if($input_uin == '100962'){
		echo "first run\n";
		echo "buffer_datapack: $buffer_datapack\n";
		echo "count_before: $count_before\n";
		echo "count_after: $count_after\n";
		echo "rate_before: $rate_before\n";
		echo "rate_after: $rate_after\n";
		echo "offset: $offset\n";
	    }

	    for($i = $start_index+1; $i <= $cutoff_end - $window_size; $i++){
		$temp_count_before = $temp_count_before - $data_map[$i-1] + $data_map[$window_size+$i-1];
		$temp_count_after = $temp_count_after - $data_map[$window_size+$i-1] + $data_map[$window_size*2+$i-1];
		$temp_rate_before = $temp_count_before / $window_size;
		$temp_rate_after = $temp_count_after / $window_size;
		$temp_rate_diff = $temp_rate_after - $temp_rate_before;
	

		if($input_uin == '100962'){
			echo "current index: $index\n";
			echo "count_before: $temp_count_before\n";
			echo "count_after: $temp_count_after\n";
			echo "rate_before: $temp_rate_before\n";
			echo "rate_after: $temp_rate_after\n";
			echo "rate_diff: $temp_rate_diff\n";
		}

		//update max rate vars if newly computed rate is larger	
		if($temp_rate_diff > $rate_diff){
			$rate_before = $temp_rate_before;
			$rate_after = $temp_rate_after;
			$rate_diff = $temp_rate_diff;
			$offset = $i + $window_size;
			echo "greater, update\n";
		}
		
	    }

            //round off data
	    $rate_before = round($rate_before, 2);
	    $rate_after = round($rate_after, 2);
            $rate_diff = round($rate_diff, 2);

	    if($input_uin == "100962"){
	    	echo "rate_before: $rate_before\n";
 	   	echo "rate_after: $rate_after\n";
	        echo "rate_diff: $rate_diff\n";
		echo "offset: $offset\n";
	    }

	    //write data to output file
	    fwrite($handle_output, "$input_uin, $rate_before, $rate_after, $rate_diff, $offset\n");

	    //advance both pointers
	    $buffer_datapack = getLine($handle_datapack);
	    $datapack_uin = getUin($buffer_datapack);
	    if($flag_all){
		$input_uin = $datapack_uin;
	    } else {
	    	$buffer_input = getline($handle_input);
	    	$input_uin = getUin($buffer_input);
            }
	    $input_count++;
	} else if( $compare_result == 1 ){
		//raw uin list is greater, advance data file
		$buffer_datapack = getLine($handle_datapack);
		$datapack_uin = getUin($buffer_datapack);
		//$data_map = getMap($buffer_datapack);
	} else {
	    	//data file uin is greater, advance line1
		$buffer_input = getline($handle_input);
		$input_uin = getUin($buffer_input);
		$input_count++;
	}

}

if($handle_input != null){
	fclose($handle_input);
}

if($handle_output != null){
	fclose($handle_output);
}

/* Update database for job finish */
$db = new DBController($ini_array['db']['host'], $ini_array['db']['user'], $ini_array['db']['pass'], $ini_array['db']['name'], $debug);
$jobObj = new Job($db, $ini_array['db']['table1'], 0);
$status = "Analyzing";
$result = $jobObj->updateJob($jobid, $status);

/* Compute CDF */
/* input: rate file */
/* Output CDF distrubution */
$cdffile = $ini_array['setting']['mainpath'] . "/" . $ini_array['cdf']['path'] . "/" . $ini_array['cdf']['prefix'] . "_$jobid";
$heatmapfile = $ini_array['setting']['mainpath'] . "/" . $ini_array['heatmap']['path'] . "/" . $ini_array['heatmap']['prefix'] . "_$jobid";
$srmapfile = $ini_array['setting']['mainpath'] . "/" . $ini_array['srmap']['path'] . "/" . $ini_array['srmap']['prefix'] . "_$jobid";
$analyzefile = $ini_array['setting']['mainpath'] . "/" . $ini_array['analyze']['path'] . "/" . $ini_array['analyze']['prefix'] . "_$jobid";
$pipefile = $ini_array['setting']['mainpath'] . "/" . $ini_array['pipe']['path'] . "/" . $ini_array['pipe']['prefix'] . "_$jobid";

Compute::cdf($output_file, $cdffile);
/* Compute Heat Map */
Compute::heatmap($output_file, $heatmapfile, $srmapfile, $cutoff_start, $cutoff_end);

$base_date = Date("Y-m-d", strtotime($start_date) + $start_index * 86400);

$event_start_date = Date("Y-m-d", strtotime($base_date) + ($cutoff_start - $start_index) * 86400);
$event_end_date = Date("Y-m-d", strtotime($base_date) + ($cutoff_end - $start_index) * 86400);

Compute::expectedRate($output_file, $analyzefile, $pipefile, $base_date, $cutoff_start, $cutoff_end, $input_count);

$status = "Finished";
$result = $jobObj->updateJob($jobid, $status);

function getLine($handle){
	if(!feof($handle)){
		$buffer = trim(fgets($handle, 4096));
		if(empty($buffer)){
			return false;
		}
		return $buffer; 
	}
	return false;
}

function getUin($buffer){
	if(empty($buffer)){
		return "";
	}
	$temp_array = explode(" ", $buffer);
	return $temp_array[0];
}

function getMap($buffer){
	if(empty($buffer)){
		return array();
	}
	$value = substr($buffer, strpos($buffer, " ")+1);
	$temp_array = explode(" ", $value);
	return $temp_array;
}

?>
