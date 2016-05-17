<?php

class Util{

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

	public static function debugMsg($filename, $function, $msg, $debug=0, $script=0){
		$log_msg = "[$filename][$function]$msg";
		if($debug){
			if($script){
				echo "$log_msg\n";
			} else {
				error_log($log_msg);
			}
		}
	}

       /* Input filter */
        public static function filter($string){
                $string = filter_var($string, FILTER_SANITIZE_STRING);
                return $string;
        }

        /* Input filter for argument */
        public static function filter_args($string){
                preg_match("/[a-zA-Z0-9\$\-_\.\+\!\*'\(\),\;\?\:\@\=\&]+/", $string, $matches);
                $result = $matches[0];
                return $result;
        }

        /* Input filter for url */
        public static function filter_url($string){
                preg_match("/([a-zA-Z+.-]+:\/\/)?([a-zA-Z0-9\$\-_\.\+\!\*'\(\),\;\/\?\:\@\=\&]+)/", $string, $matches);
                $result = $matches[0];
                return $result;
        }

        public static function getParam($arr, $key, $default){
                if(array_key_exists($key, $arr)){
                        return $arr[$key];
                }
                return $default;
        }

        public static function getParamNumeric($arr, $key, $default){
                if(array_key_exists($key, $arr)){

                        if(is_numeric($arr[$key])){
                                return $arr[$key];
                        }
                }
                return $default;
        }

        public static function getIniParam($arr, $key1, $key2, $default){
                if(array_key_exists($key1, $arr) && array_key_exists($key2, $arr[$key1])){
                        return $arr[$key1][$key2];
                }
                return $default;
        }


        public static function redirectNotFound(){
                header("HTTP/1.0 404 Not Found");
                exit;
        }

        public static function getBrowserByUA($ua){
                $browser        =   "Unknown Browser";
                $browser_array  =   array(
                                '/msie/i'       =>  'Internet Explorer',
                                '/firefox/i'    =>  'Firefox',
                                '/safari/i'     =>  'Safari',
                                '/chrome/i'     =>  'Chrome',
                                '/opera/i'      =>  'Opera',
                                '/netscape/i'   =>  'Netscape',
                                '/maxthon/i'    =>  'Maxthon',
                                '/konqueror/i'  =>  'Konqueror',
                                '/mobile/i'     =>  'Handheld Browser',
                                '/MicroMessenger/i'     =>  'WeChat'
                                );

                foreach ($browser_array as $regex => $value) {
                        if (preg_match($regex, $ua)) {
                                $browser    =   $value;
                        }
                }
                return $browser;
        }

        public static function generateNumericRandomString($length = 10) {
                        $characters = '0123456789Z';
                        $randomString = '';
                        for ($i = 0; $i < $length; $i++) {
                                        $randomString .= $characters[rand(0, strlen($characters) - 1)];
                        }
                        return $randomString;
        }

        public static function generateSig($previous_id, $flow_id, $counter, $nonce, $key){
                $sig = md5(strtoupper("p=$previous_id&f=$flow_id&c=$counter&n=$nonce&key=$key"));
                return $sig;
        }

        public static function generateArraySig($param_array){
                $str = '';
                foreach($param_array as $key => $value){
                        $str = $str . "$key=$value&";
                }
                $sig = md5(strtoupper(rtrim($str, "& ")));
                return $sig;
        }

        public static function generateRandomString($length = 10) {
                        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $randomString = '';
                        for ($i = 0; $i < $length; $i++) {
                                        $randomString .= $characters[rand(0, strlen($characters) - 1)];
                        }
                        return $randomString;
        }

}

?>
