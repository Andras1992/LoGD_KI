<?php
$translate_page = $_SERVER['PHP_SELF'];
$translate_page = substr($translate_page,strrpos($translate_page,"/")+1);
function translate($input){
	global $translate_page;
	// Ohne Sonderuebersetzung bleibt diese Liste leer und der Originaltext wird verwendet.
	$replace = array();
	//echo $_SERVER['SCRIPT_FILENAME'];
	//echo $translate_page;
	switch ($translate_page){
	case "logdnet.php":
		$replace = array(
		"Unknown"=>"Unbekannt"
		);
		break;
	}
	return replacer($input,$replace);
}

?>