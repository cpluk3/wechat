<?php

class Compute{

	public static function expectedRate($input_file, $output_file, $output_file_2, $base_date, $cutoff_start, $cutoff_end, $n1=0){

		$input_handle = fopen($input_file, "r");
		$output_handle = fopen($output_file, "w");
		$output_handle_2 = fopen($output_file_2, "w");

		/* output file 2 vars */
		$input_user_count = $n1; //N1
		$active_user_count = 0; //N2
		$sum_rate_before = 0;
		$sum_rate_after = 0;
		$sum_rate_diff = 0;
		$active_threshold = 0.3;

		/* classification */
		$group = array();
		for($i = 0; $i <= 1; $i = $i + 0.1){
			$group[strval($i)][0] = 0; //no change
			$group[strval($i)][1] = 0; //positive change
			$group[strval($i)][2] = 0; //negative change
			$group[strval($i)][3] = 0; //very active users
			$group[strval($i)][4] = 0; //inactive users
		}

		/* Output file 1 */
		$zero_count = 0;
		$total_exp = 0;
		$total_count = 0;
		$exp_by_date = array();		
		$total_by_date = array();

		for($i = $cutoff_start; $i <= $cutoff_end; $i++){
			$exp_by_date[$i] = 0;
			$total_by_date[$i] = 0;
		}

		/*Loop through the files */

		while(1){

			$buffer = self::getLine($input_handle);

			if($buffer === false){
				break;
			}

			if(substr($buffer, 0, 1) == "#"){
				//Skip buffer line
				continue;
			}

			$data_array = explode(",", $buffer);
			$uin = trim($data_array[0]);
			$rate_before = trim($data_array[1]);
			$rate_after = trim($data_array[2]);
			$rate_diff = trim($data_array[3]);
			$date_index = trim($data_array[4]);

			/* For out file 2 */
			$sum_rate_before += $rate_before;
			$sum_rate_after += $rate_after;
			$sum_rate_diff += $rate_diff;
	
			if($rate_diff != 0){
				$exp_by_date[$date_index] += $rate_diff;
				$total_by_date[$date_index] += 1;
			
			} else {
				$zero_count++;
			}

			/* For out file 2 */
			for($L = 0; $L <= 1; $L = $L+0.1){
				$L_string = strval($L);
				if($rate_diff == 0 || ($rate_diff > $L * -1 && $rate_diff < $L)){
					$group[$L_string][0]++;
					if( ($rate_before + $rate_after)/2 >= 1 - $active_threshold){
						$group[$L_string][3]++; //highly active
					} else if( ($rate_before + $rate_after)/2 <= $active_threshold){
						$group[$L_string][4]++; //in active
					}

				} else if($rate_diff < $L * -1){
					//negative rate
					$group[$L_string][2]++;
				} else {
					//positive rate
					$group[$L_string][1]++;
				}
			}

			$active_user_count++;
			/* end of out file 2 */

			$total_exp += $rate_diff;
			$total_count += 1;

		} //end of while

		fwrite($output_handle, "$total_exp, $total_count\n");
		fwrite($output_handle, "0, $zero_count\n");
		/* Write result to out file */
		for($i = $cutoff_start; $i <= $cutoff_end; $i++){
			fwrite($output_handle,  Date('Y-m-d', strtotime($base_date) + $i * 86400) . ", " . $exp_by_date[$i] . ", " . $total_by_date[$i] . "\n");
		}

		/* Write result to out file 2 */
		fwrite($output_handle_2, "n1: $input_user_count\n");
		fwrite($output_handle_2, "n2: $active_user_count\n");
		fwrite($output_handle_2, "sum_rate_before: $sum_rate_before\n");
		fwrite($output_handle_2, "sum_rate_after: $sum_rate_after\n");
		fwrite($output_handle_2, "sum_rate_diff: $sum_rate_diff\n");
		
		for($i = 0; $i <= 1; $i = $i + 0.1){
			$index = strval($i);	
			fwrite($output_handle_2, "$i," . $group[$index][0] . "," . $group[$index][1] . "," . $group[$index][2] . "," . $group[$index][3] .  "," . $group[$index][4] . "\n");
		}

		fclose($input_handle);
		fclose($output_handle);
		fclose($output_handle_2);
	}

	public static function cdf($input_file, $output_file, $interval=0.1){
		$input_handle = fopen($input_file, "r");
		$output_handle = fopen($output_file, "w");

		$cdf = array();
		$interval_array = array();
		for($i = 0; $i <= 1; $i = $i + $interval){
			array_push($interval_array, $i);
		}

		for($j = 0; $j < count($interval_array); $j++){
			$cdf[$j] = 0; 
		}

		$count = 0;
		while(1){
			$buffer = self::getLine($input_handle);
			if($buffer === false){
				break;
			}

			if(substr($buffer, 0, 1) == "#"){
				continue;
			}

			$count++;

			$data_array = explode(",", $buffer);
			$uin = trim($data_array[0]);
			$rate_before = trim($data_array[1]);
			$rate_after = trim($data_array[2]);
			$rate_diff = trim($data_array[3]);
			$date_index = trim($data_array[4]);

			for($j = 0; $j < count($interval_array); $j++){
				//echo "Rate diff: $rate_diff, interval $j: " . $interval_array[$j] . "\n";
				if($rate_diff <= $interval_array[$j]){
					//echo "Added\n";
					$cdf[$j] = $cdf[$j] + 1;
				}
			}
		}

		/* Write result to out file */
		for($i = 0; $i < count($interval_array); $i++){
			fwrite($output_handle, $interval_array[$i] . "," . $cdf[$i] . "\n");
		}

		fclose($input_handle);
		fclose($output_handle);
	}

	public static function heatmap($input_file, $output_file, $output_file_2, $cutoff_start, $cutoff_end){
		$input_handle = fopen($input_file, "r");
		$output_handle = fopen($output_file, "w");
		$output_handle_2 = fopen($output_file_2, "w");

		$xmin = $cutoff_start;
		$xmax = $cutoff_end;
		$xinterval = 1;
		$ymin = 0;
		$ymax = 1;
		$yinterval = 0.1;

		$data_array = array();
		$x_array = array();
		$y_array = array();

		$first = 0;
		$t_array = array();

		$sr_array = array();
		$s_array = array();
		$r_array = array();

		for($y = $ymin; $y < (intval($ymax) + intval($yinterval) ); $y = $y + $yinterval){
			$t_array[strval($y)] = 0;
			array_push($y_array, $y);
			/* add s r array for init */
			array_push($s_array, $y);
			array_push($r_array, $y);
		}

		for($y = $ymin; $y < (intval($ymax) + intval($yinterval) ); $y = $y + $yinterval){
			$sr_array[strval($y)] = $t_array;
		}

		for($x = $xmin; $x < $xmax + $xinterval; $x = $x + $xinterval){
			$data_array[strval($x)] = $t_array;
			array_push($x_array, $x);
		}

		$input_handle = fopen($input_file, "r");
		if ($input_handle) {
			while (($buffer = self::getLine($input_handle)) !== false) {
				if(substr($buffer, 0, 1) == "#"){
					continue;
				}

				$buffer_array = explode(", ", $buffer);
				$col1 = trim($buffer_array[4]);
				$col2 = trim($buffer_array[3]);
				$col_s = trim($buffer_array[1]);
				$col_r = trim($buffer_array[2]);

				/*
				if($col2 == 0){
					//If rate diff is zero, do not put data
					continue;	
				}
				*/

				$temp_x = strval(self::getNearest($col1 , $x_array));
				$temp_y = strval(self::getNearest($col2 , $y_array));
				
				$temp_s = strval(self::getNearest($col_s, $s_array));				
				$temp_r = strval(self::getNearest($col_r, $r_array));				
				
				$sr_array[$temp_s][$temp_r]++;
				$data_array[$temp_x][$temp_y]++;
			}
		} else {
			// error opening the file.
		}

		$output_handle = fopen($output_file, "w");
		for($x = $xmin; $x < $xmax + $xinterval; $x = $x + $xinterval){
			for($y = $ymin; $y < (intval($ymax) + intval($yinterval) ); $y = $y + $yinterval){
				fwrite($output_handle, "$x, $y, " . $data_array[strval($x)][strval($y)] . "\n");
			}
		}

		$output_handle_2 = fopen($output_file_2, "w");
		for($s = $ymin; $s < (intval($ymax) + intval($yinterval) ); $s = $s + $yinterval){
			for($r = $ymin; $r < (intval($ymax) + intval($yinterval) ); $r = $r + $yinterval){
				fwrite($output_handle_2, "$s, $r, " . $sr_array[strval($s)][strval($r)] . "\n");
			}
		}

	}

	public static function getLine($handle){

		if(!feof($handle)){
			$buffer = trim(fgets($handle, 4096));
			if(empty($buffer)){
				return false;
			}
			return $buffer;
		}
		return false;
	}

	public static function getNearest($search, $arr) {
		$closest = null;
		foreach($arr as $item) {
			if($closest === null || abs($search - $closest) > abs($item - $search)) {
				$closest = $item;
			}
		}
		return $closest;
	}

}

?>
