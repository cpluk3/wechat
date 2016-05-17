<?php

if(count($argv) < 4){
    echo "Usage: ./" . $argv[0] . " [Input file path 1] [Input file path 2] [Output file]\n"; 
    exit;
}

$inputfile = $argv[1];
$inputfile2 = $argv[2];
$outputfile = $argv[3];

/* assume file is sorted */
$input_handle = fopen($inputfile, "r") or die("Couldn't get input file handle");
$input_handle2 = fopen($inputfile2, "r") or die("Couldn't get input file2 handle");
$output_handle = fopen($outputfile, "w") or die("Couldn't get output file handle");

$input_padding_1 = "";
$input_padding_2 = "";

if($input_handle && $input_handle2){
    /* get one line for each handle */
    while(true){
    	$input_buffer = getLine($input_handle);
    	$uin1 = getUin($input_buffer);
    	$value1 = getValue($input_buffer);
	if(strpos($input_buffer, "#") === false){
		break;
	}
    }

    for($i = 0; $i < getCount($input_buffer); $i++){
	$input_padding_1 .= "0 ";
    }
    $input_padding_1 = rtrim($input_padding_1, " ");
    echo "Input Padding 1: " . $input_padding_1 . "\n";

    while(true){
	    $input_buffer2 = getLine($input_handle2);
	    $uin2 = getUin($input_buffer2);
	    $value2 = getValue($input_buffer2);
	    if(strpos($input_buffer2, "#") === false){
		break;
	    }
    }

    for($i = 0; $i < getCount($input_buffer2); $i++){
	$input_padding_2 .= "0 ";
    }
    $input_padding_2 = rtrim($input_padding_2, " ");
    echo "Input Padding 2: " . $input_padding_2 . "\n";

    //If file 1 is empty
    if($input_padding_1 == ""){
	   while(1){
                    if($input_buffer2 === false){
                        break;
                    }

                    fwrite($output_handle, "$uin2 $value2\n");
                    $input_buffer2 = getLine($input_handle2);
                    $uin2 = getUin($input_buffer2);
                    $value2 = getValue($input_buffer2);
            }
	echo "file 2 is empty\n";
	exit; //exit, just copy file2 to out
    }

    //if file 2 is empty
    if($input_padding_2 == ""){
	   while(1){
                    if($input_buffer1 === false){
                        break;
                    }

                    fwrite($output_handle, "$uin1 $value1\n");
                    $input_buffer1 = getLine($input_handle1);
                    $uin1 = getUin($input_buffer1);
                    $value1 = getValue($input_buffer1);
            }
	echo "file 1 is empty\n";
	exit; //exit, just copy file to out
    }


    while(1){
        
        if($input_buffer === false){
            while(1){
                    if($input_buffer2 === false){
                        break;
                    }

                    fwrite($output_handle, "$uin2 $input_padding_1 $value2\n");
                    $input_buffer2 = getLine($input_handle2);
                    $uin2 = getUin($input_buffer2);
                    $value2 = getValue($input_buffer2);
            }
            break;
        }

        if($input_buffer2 === false){
            while(1){
                if($input_buffer === false){
                    break;
                }

                fwrite($output_handle, "$uin1 $value1 $input_padding_2\n");
                $input_buffer = getLine($input_handle);
                $uin1 = getUin($input_buffer);
                $value1 = getValue($input_buffer);
            }
            break;
        }

        $result = bccomp($uin1, $uin2);

        if( $result == 0 ){
            //print Uin 1 value 1 value 2
            fwrite($output_handle, "$uin1 $value1 $value2\n");
            //Advance both lines
            $input_buffer = getLine($input_handle);
            $uin1 = getUin($input_buffer);
            $value1 = getValue($input_buffer);

            $input_buffer2 = getLine($input_handle2);
            $uin2 = getUin($input_buffer2);
            $value2 = getValue($input_buffer2);

        } else if( $result == 1 ){
            //Input Greater, put line2
            fwrite($output_handle, "$uin2 $input_padding_1 $value2\n");

            $input_buffer2 = getLine($input_handle2);
            $uin2 = getUin($input_buffer2);
            $value2 = getValue($input_buffer2);


        } else {
            //Input2 Greater, put line1
            fwrite($output_handle, "$uin1 $value1 $input_padding_2\n");

            $input_buffer = getLine($input_handle);
            $uin1 = getUin($input_buffer);
            $value1 = getValue($input_buffer);

        }
    }
    fclose($input_handle);
    fclose($input_handle2);
    fclose($output_handle);
}

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

function getValue($buffer){
    if(empty($buffer)){
        return "";
    }
    $value = substr($buffer, strpos($buffer, " ")+1);
    return $value;
}

function getCount($buffer){
    if(empty($buffer)){
	return "";
    }
    $value = explode(" ", getValue($buffer));
    return count($value);
}

?>
