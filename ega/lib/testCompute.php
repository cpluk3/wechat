<?php
include_once("Compute.php");

$input_file = "../var/rate_15";
$output_file = "test.data";

Compute::cdf($input_file, $output_file);


?>
