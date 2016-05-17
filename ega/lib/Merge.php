<?php
include_once("../lib/Util.php");

class Merge{

	public $left_file;
	public $right_file;
	public $output_file;
	public $debug;

	function __construct($left_file, $right_file, $output_file, $debug){
		$this->left_file = $left_file;
		$this->right_file = $right_file;
		$this->output_file = $output_file;
		$this->debug = $debug;
	
		Util::debugMsg(__FILE__, __FUNCTION__, "left_file: $left_file", $this->debug);
		Util::debugMsg(__FILE__, __FUNCTION__, "right_file: $right_file", $this->debug);
		Util::debugMsg(__FILE__, __FUNCTION__, "output_file: $output_file", $this->debug);
	}

	public function process($new_startdate, $new_enddate, $new_datenum){
		if(empty($new_startdate) || empty($new_enddate) || empty($new_datenum)){
			Util::debugMsg(__FILE__, __FUNCTION__, "empty process param", $this->debug);
			return false;
		}

		/* assume file is sorted */
		$handle_left = fopen($this->left_file, "r");
		$handle_right = fopen($this->right_file, "r");
		$handle_output = fopen($this->output_file, "w");;

		if(empty($handle_left) || empty($handle_right) || empty($handle_output) ){
			Util::debugMsg(__FILE__, __FUNCTION__, "empty handle", $this->debug);
			return false;
		}

		$padding_left = "";
		$padding_right = "";

		$ret_array = self::skipMeta($handle_left);
		$buffer_left = $ret_array['buffer'];
		$handle_left = $ret_array['handle'];
		$uin_left = self::getUin($buffer_left);
		$value_left = self::getValue($buffer_left);
		$padding_left = self::getPaddingString(self::getCount($buffer_left));

		$ret_array = self::skipMeta($handle_right);
		$buffer_right = $ret_array['buffer'];
		$handle_right = $ret_array['handle'];
		$uin_right = self::getUin($buffer_right);
		$value_right = self::getValue($buffer_right);
		$padding_right = self::getPaddingString(self::getCount($buffer_right));	
		//If file 1 is empty
		if($padding_left == "" || $padding_right == ""){
			Util::debugMsg(__FILE__, __FUNCTION__, "empty file", $this->debug);
			return false;
		}

		//write meta
		fwrite($handle_output, "#$new_startdate\n");
		fwrite($handle_output, "#$new_enddate\n");
		fwrite($handle_output, "#$new_datenum\n");

		while(1){

			/* if left file reach the end */
			if($buffer_left === false){
				while(1){
					if($buffer_right === false){
						break;
					}

					fwrite($handle_output, "$uin_right $padding_left $value_right\n");
					$buffer_right = self::getLine($handle_right);
					$uin_right = self::getUin($buffer_right);
					$value_right = self::getValue($buffer_right);
				}
				break;
			}

			if($buffer_right === false){
				while(1){
					if($buffer_left === false){
						break;
					}

					fwrite($handle_output, "$uin_left $value_left $padding_right\n");
					$buffer_left = self::getLine($handle_left);
					$uin_left = self::getUin($buffer_left);
					$value_left = self::getValue($buffer_left);
				}
				break;
			}

			$result = bccomp($uin_left, $uin_right);

			/*
			echo "result: $result\n";
			echo "uin_left: $uin_left\n";
			echo "uin_right: $uin_right\n";
			echo "value_left: $value_left\n";
			echo "value_right: $value_right\n";
			*/

			if( $result == 0 ){
				//print Uin 1 value 1 value 2
				fwrite($handle_output, "$uin_left $value_left $value_right\n");
				//Advance both lines
				$buffer_left = self::getLine($handle_left);
				$uin_left = self::getUin($buffer_left);
				$value_left = self::getValue($buffer_left);

				$buffer_right = self::getLine($handle_right);
				$uin_right = self::getUin($buffer_right);
				$value_right = self::getValue($buffer_right);

			} else if( $result == 1 ){
				//Input Greater, put line2
				fwrite($handle_output, "$uin_right $padding_left $value_right\n");

				$buffer_right = self::getLine($handle_right);
				$uin_right = self::getUin($buffer_right);
				$value_right = self::getValue($buffer_right);


			} else {
				//Input2 Greater, put line1
				fwrite($handle_output, "$uin_left $value_left $padding_right\n");
				$buffer_left = self::getLine($handle_left);
				$uin_left = self::getUin($buffer_left);
				$value_left = self::getValue($buffer_left);

			}
		}

		fclose($handle_left);
		fclose($handle_right);
		fclose($handle_output);

		return true;
	}

	private static function getPaddingString($count){
		$padding = "";
		for($i = 0; $i < $count; $i++){
			$padding .= "0 ";
		}
		$padding = rtrim($padding, " ");
		return $padding;
	}

	private static function skipMeta($handle){
		while(true){
			$buffer = self::getLine($handle);
			if(strpos($buffer, "#") === false){
				break;
			}
		}
		return array('handle'=>$handle, 'buffer'=>$buffer);
	}

	private static function getLine($handle){
		if(!feof($handle)){
			$buffer = trim(fgets($handle, 4096));
			if(empty($buffer)){
				return false;
			}
			return $buffer; 
		}
		return false;
	}

	private static function getUin($buffer){
		if(empty($buffer)){
			return "";
		}
		$temp_array = explode(" ", $buffer);
		return $temp_array[0];
	}

	private static function getValue($buffer){
		if(empty($buffer)){
			return "";
		}
		$value = substr($buffer, strpos($buffer, " ")+1);
		return $value;
	}

	private static function getCount($buffer){
		if(empty($buffer)){
			return "";
		}
		$value = explode(" ", self::getValue($buffer));
		return count($value);
	}

}

?>
