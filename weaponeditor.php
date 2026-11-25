<?php
require_once "common.php";
isnewday(2);

page_header("Waffen Editor");
$weaponlevel = (int)$_GET['level'];
addnav("G?Zurück zur Grotte","superuser.php");
addnav("W?Zurück zum Weltlichen","village.php");
addnav("Waffeneditor","weaponeditor.php?level=$weaponlevel");

addnav("Waffe hinzufügen","weaponeditor.php?op=add&level=$weaponlevel");
$values = array(1=>48,225,585,990,1575,2250,2790,3420,4230,5040,5850,6840,8010,9000,10350);
	output("`&<h3>Waffen für $weaponlevel Drachen Kills</h3>`0",true);

$weaponarray=array(
	"Waffen,title",
	"weaponid"=>"Waffen ID,hidden",
	"weaponname"=>"Waffen Name",
	"damage"=>"Schaden,enum,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13,14,14,15,15",
	"Waffen,title");
if($_GET['op']=="edit" || $_GET['op']=="add"){
	if ($_GET['op']=="edit"){
		// Die Waffennummer aus der URL wird als Zahl gebunden.
		$sql = "SELECT * FROM weapons WHERE weaponid=?";
		$result = db_query_params($sql, array((int)db_unquote_legacy_input($_GET['id'])));
		$row = db_fetch_assoc($result);
	}else{
		$sql = "SELECT max(damage+1) AS damage FROM weapons WHERE level=$weaponlevel";
		$result = db_query($sql);
		$row = db_fetch_assoc($result);
	}
	output("<form action='weaponeditor.php?op=save&level=$weaponlevel' method='POST'>",true);
	addnav("","weaponeditor.php?op=save&level=$weaponlevel");
	showform($weaponarray,$row);
	output("</form>",true);
}else if($_GET['op']=="del"){
	// Die Waffennummer wird sicher gebunden, bevor der Eintrag entfernt wird.
	$sql = "DELETE FROM weapons WHERE weaponid=?";
	db_query_params($sql, array((int)db_unquote_legacy_input($_GET['id'])));
	//output($sql);
	redirect("weaponeditor.php?level=$weaponlevel");
}else if($_GET['op']=="save"){
	// Formularwerte werden als Daten gebunden und die Auswahl auf bekannte Schadensstufen begrenzt.
	$recordId = (int)db_unquote_legacy_input($_POST['weaponid']);
	$damage = (int)db_unquote_legacy_input($_POST['damage']);
	if (!isset($values[$damage])) $damage = 1;
	$value = $values[$damage];
	$weaponName = db_unquote_legacy_input($_POST['weaponname']);
	if ($recordId > 0) {
		$sql = "UPDATE weapons SET weaponname=?,damage=?,value=? WHERE weaponid=?";
		db_query_params($sql, array($weaponName, $damage, $value, $recordId));
	} else {
		$sql = "INSERT INTO weapons (level,damage,weaponname,value) VALUES (?,?,?,?)";
		db_query_params($sql, array($weaponlevel, $damage, $weaponName, $value));
	}
	//output($sql);
	redirect("weaponeditor.php?level=$weaponlevel");
}else if ($_GET['op']==""){
	$sql = "SELECT max(level+1) as level FROM weapons";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$max = $row['level'];
	for ($i=0;$i<=$max;$i++){
		addnav("Waffen für $i DK's","weaponeditor.php?level=$i");
	}
	output("<table>",true);
	$sql = "SELECT * FROM weapons WHERE level=? ORDER BY damage"; // Die Waffenstufe wird als Zahl gebunden.
	$result= db_query_params($sql,array((int)db_unquote_legacy_input($_GET['level']))) or die(db_error(LINK));
	for ($i=0;$i<db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		if ($i==0){
			output("<tr>",true);
			output("<td>Ops</td>",true);
			foreach ($row as $key => $val) {
				output("<td>$key</td>",true);
			}
			output("</tr>",true);
			reset($row);
		}
		output("<tr>",true);
		output("<td>[<a href='weaponeditor.php?op=edit&id=$row[weaponid]&level=$weaponlevel'>Edit</a>|<a href='weaponeditor.php?op=del&id=$row[weaponid]&level=$weaponlevel' onClick='return confirm(\"Diese Waffe wirklich löschen?\");'>Löschen</a>]</td>",true);
		addnav("","weaponeditor.php?op=edit&id=$row[weaponid]&level=$weaponlevel");
		addnav("","weaponeditor.php?op=del&id=$row[weaponid]&level=$weaponlevel");
		foreach ($row as $key => $val) {
			output("<td>$val</td>",true);
		}
		output("</tr>",true);
	}
	output("</table>",true);
}
page_footer();
?>
