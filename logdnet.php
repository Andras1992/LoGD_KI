<?php
require_once "common.php";

if ($_GET['op']==""){
	// Die Serveradresse aus der Anfrage wird als Datenwert gebunden.
	$sql = "SELECT lastupdate,serverid FROM logdnet WHERE address=?";
	$result = db_query_params($sql, array(db_unquote_legacy_input($_GET['addy'])));
	$row = db_fetch_assoc($result);
	
	if (db_num_rows($result)>0){
		if (strtotime($row['lastupdate'])<strtotime(date("r")."-1 minutes")){
			//echo strtotime($row[lastupdate])."<br>".strtotime("-5 minutes");
			$sql = "UPDATE logdnet SET priority=priority*0.99";
			db_query($sql);
			//use PHP server time for lastupdate in case mysql server and PHP server have different times.
			// Beschreibung, Zeitpunkt und Datensatz-ID bleiben getrennte Datenwerte.
			$sql = "UPDATE logdnet SET priority=priority+1,description=?,lastupdate=? WHERE serverid=?";
			db_query_params($sql, array(soap(db_unquote_legacy_input($_GET['desc'])), date("Y-m-d H:i:s"), (int)$row['serverid']));
			echo "Ok - upgedated";
		}else{
			echo "Ok - noch zu früh für ein Update";
		}
	}else{
		// Serveradresse und Beschreibung werden gebunden, damit sie keine SQL-Syntax werden koennen.
		$sql = "INSERT INTO logdnet (address,description,lastupdate) VALUES (?,?,NOW())";
		$result = db_query_params($sql, array(db_unquote_legacy_input($_GET['addy']), soap(db_unquote_legacy_input($_GET['desc']))));
		echo "Ok - hinzugefügt";
	}
}elseif ($_GET['op']=="net"){
	$sql = "SELECT address,description FROM logdnet WHERE lastupdate > '".date("Y-m-d H:i:s",strtotime(date("r")."-7 days"))."' ORDER BY priority DESC";
	$result=db_query($sql);
	for ($i=0;$i<db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		$row = serialize($row);
		echo $row."\n";
	}
}else{
	page_header("LoGD Netz");
	//$sql = "SELECT * FROM logdnet ORDER BY priority DESC";
	//$result=db_query($sql);
	addnav("Zurück zum Login","index.php");
	output("`@Eine Liste mit anderen LoGD Servern, die im LoGD-Netz registriert sind. (Sortiert nach Logins)`n`n");
	output("<table>",true);
	output("<tr><td>`@`bServername und Link`b`0</td><td width='130'>`@`bVersion`b`0</td></tr>",true);
	$servers=file(getsetting("logdnetserver","http://lotgd.net/")."logdnet.php?op=net");
	foreach ($servers as $key => $val) {
		$row=unserialize($val);
		if (trim($row['description'])=="") $row['description']="Another LoGD Server";
		if (substr($row['address'],0,7)!="http://"){

		}else{
			output("<tr><td valign='top'><a href='".HTMLEntities($row['address'])."' target='_blank'>".stripslashes(HTMLEntities($row['description']))."`0</a></td><td valign='top' width='130'>".HTMLEntities($row['version'])."</td></tr>",true);
		}
	}
	output("</table>",true);
	page_footer();
}
?> 
