<?php
$input_file = $argv[1];
$output_file = $argv[2];
$xmin = 0;
$xmax = 1;
$xinterval = 0.05;

$total = system("cat $input_file | wc -l");

for($i = $xmin; $i < $xmax + $xinterval; $i = $i + $xinterval){
	system("php ./command.php $input_file $i less $total >> $output_file");
}

?>
