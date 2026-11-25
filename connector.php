<?php
require_once "common.php";
header("Content-Type: text/xml");

switch($_GET['op']){
case "newssince":
	// Ohne Nummer zeigen wir die neueste Nachricht; eine vorhandene Nummer wird sicher gebunden.
	$since = max(0, (int)db_unquote_legacy_input($_GET['since']));
	if ($since > 0) {
		$sql = "SELECT * FROM news WHERE newsid > ? ORDER BY newsid DESC";
		$result = db_query_params($sql, array($since));
	} else {
		$sql = "SELECT * FROM news ORDER BY newsid DESC LIMIT 1";
		$result = db_query($sql);
	}
	$output = "<recentnews>";
	for ($i=0;$i<db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		$output.="<news id=\"$row[newsid]\">".HTMLEntities(preg_replace("'[`].'","",$row['newstext']))."</news>";
	}
	$output.="</recentnews>";
	echo $output;
	break;
case "online":
	$sql = "SELECT name,alive,location,sex,level,laston,loggedin,lastip,uniqueid FROM accounts WHERE locked=0 AND loggedin=1 ORDER BY level DESC";
	$output="<onlineusers>";
	$result = db_query($sql) or die(sql_error($sql));
	for ($i=0;$i<db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		$loggedin=(date("U") - strtotime($row['laston']) < getsetting("LOGINTIMEOUT",900) && $row['loggedin']);
		if ($loggedin) {
			$output.="<user name=\"".preg_replace("'[`].'","",$row['name'])."\" level=\"$row[level]\" sex=\"$row[sex]\"/>";
			$onlinecount++;
		}
	}
	$output.="</onlineusers>";
	echo $output;
	break;
}
?>
