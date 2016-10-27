<?php
/**
* Gamebox Web waf
* By Hcamael
*/

/**
 $level:
 	0: low
 	1: high(death exit)
*/
function upload_waf($level)
{
	$conf = array(
		'replace_str' => array("<?"=>"《?", ),
		'white_ext_list' => array(),
		'black_ext_list' => array(),
	);
	foreach($_FILES as $key => $value) {
		$content = file_get_contents($value['tmp_name']);
		$f = fopen($value['tmp_name'], "w");
		if (!$level) {
			if (empty($conf['replace_str'])) {
				$content = str_replace("<?", "《?", $content);
			}
			else {
				foreach ($conf['replace_str'] as $key => $value) {
					$content = str_ireplace($key, $value, $content);
				}
			}
		}
		else {
			$content = "<?php exit;?>\n$content";
		}
		fwrite($f, $content);
		fclose($f);
	}
}

/**
* keyword waf
* mode:
*	1 - replace the keyword
*	2 - return error info
*/
function keyword_waf($keyword, $mode=1){
	// $mode = 1;
	// $keyword = array(
	// 	["select" => "ｓｅｌｅｃｔ"],
	// 	["'" => "‘"],
	// );
	if (isset($_SESSION)) {
		$arg = array(&$_GET, &$_POST, &$_COOKIE, &$_SESSION);
	}
	else {
		$arg = array(&$_GET, &$_POST, &$_COOKIE);
	}

	foreach ($arg as $key => $value) {
		$tmp = json_encode($value);
		foreach ($keyword as $k => $v) {
			if ($mode == 1) {
				$tmp = str_ireplace($k, $v, $tmp);
			}
			elseif ($mode == 2) {
				if(stripos($tmp, $k)!==false)
					die("Fuckddog!");
			}
		}

		$arg[$key] = json_decode($tmp, true);

		/**
		* waf2
		*/
		// foreach ($value as $k => $v) {
		// 	$tmp = json_encode($v);
		// 	foreach ($keyword as $k2 => $v2) {
		// 		if ($mode == 1) {
		// 			$tmp = str_ireplace($k2, $v2, $tmp);
		// 		}
		// 		elseif ($mode == 2) {
		// 			if(stripos($tmp, $k2)!==false)
		// 				die("Fuckddog!");
		// 		}
		// 	}
		// 	$value[$k] = json_decode($tmp, true);
		// }
		// $arg[$key] = $value;
	}

}

/**
* main waf / invincible waf
* black_ip_list : Need to defense the IP
* level : 
*	0 : low, black_ip through waf
*	9 : hith, black_ip exit;
*/
function ip_waf($level)
{
	$conf = array(
		"black_ip_list" => array("127.0.0.1"),
		"level" => $level,
	);
	foreach ($conf['black_ip_list'] as $value) {
		if ($value == $_SERVER['REMOTE_ADDR']) {
			if ($conf['level'] === 9) {
				//header("Location: ");
				exit;
			}
			elseif ($conf['level'] === 0) {
				global $headers;
	 			$headers['HTTP_BLACK_LIST'] = 1;
	 			return;
			}
		}
	}

}

/**
* log
*/
function waf_log()
{
	/* waf conf */
	$conf = array(
		"log_info" => array(
				"GET"    => $_SERVER['REQUEST_URI'], 
				"POST"   => $_POST, 
				"COOKIE" => $_COOKIE, 
		),
	);
	/* SESSION */
	if (isset($_SESSION)) {
		$conf['log_info']['SESSION'] = $_SESSION;
	}
	/* HTTP_HEADERS*/
	global $headers; 
	foreach ($_SERVER as $key => $value) { 
    	if ('HTTP_' == substr($key, 0, 5)) { 
        	$headers[str_replace('_', '-', substr($key, 5))] = $value; 
    	}
	}
	$conf['log_info']['HEADER'] = $headers;
	/* the same ip to write the same file */
	$ip = $_SERVER['REMOTE_ADDR'];
	$f = fopen("/tmp/$ip.log", "a");
	/* Requests time */
	$t = date("H:i:s", $_SERVER['REQUEST_TIME']);
	fwrite($f, "===========$t===========\n");
	foreach ($conf['log_info'] as $key => $value) {
		if (is_array($value)) {
			$value = json_encode($value);
		}
		fwrite($f, "$key\t===>\t$value\n");
	}
	fwrite($f, "\n");
	fclose($f);
}

/* main (use waf example)*/
$headers = array();
$level = 0;
$conf = array(
	"sql_keyword" => array(
		"select" => "ｓｅｌｅｃｔ",
		"'" => "‘",
		'\"' => "“",
	),
	"command_exec_keyword" => array(
		"system" => "ｓystem",
		"exec" => "ｅxec",
		"passthru" => "ｐassthru",
		";" => "；",
		//"}" => "｝",
	),
	"other_keyword" => array(
		"10.0.0.1" => "10.1.1.1",
	),
);
waf_log();
upload_waf($level);
ip_waf($level);
if (isset($headers['HTTP_BLACK_LIST'])) {
	# sql_waf
	keyword_waf($conf['sql_keyword']);
	# exec_waf
	keyword_waf($conf['command_exec_keyword']);
	# other waf
	keyword_waf($conf['other_keyword']);
}
# test
var_dump($_GET);
echo "<br><br>";
var_dump($_POST);
?>
