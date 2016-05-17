<?php

$file = $argv[1];
$handle = fopen($file, "r") or die("Couldn't get file handle");

$threshold = $argv[2];
$operator = $argv[3];
$total = $argv[4];

$count = 0;
while(1){
	$buffer = getLine($handle);
	if($buffer === false){
		break;
	}
	
	$var = explode(" ", $buffer);
	$rate_diff = $var[3];
	if($operator == 'less'){
		if($rate_diff <= $threshold){
			$count++;
		}
	} else {
		if($rate_diff >= $threshold){
			$count++;
		}
	}
}

if(isset($total)){
	$count = round($count/$total, 2);
} else {
	$count = $count;
}
echo "$threshold, $count\n";

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

?>
