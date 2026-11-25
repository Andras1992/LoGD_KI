<?php

// 11072004

// Item Editor
// by anpera; based on mount editor
//
// This is for administer items of all kind with anpera's item table
// (first introduced in houses mod)
// items table REQUIRED!
//
// insert:
// 	if ($session[user][superuser]>=2) addnav("Item Editor","itemeditor.php");
// into menu of superuser.php
//

require_once "common.php";

page_header("Item Editor");
addnav("G?Zurück zur Grotte","superuser.php");
addnav("W?Zurück zum Weltlichen","village.php");

if ($_GET['op']=="del"){
$sql = "DELETE FROM items WHERE id=?"; // Die Item-ID wird als Zahl gebunden, bevor der Gegenstand geloescht wird.
db_query_params($sql,array((int)db_unquote_legacy_input($_GET['id'])));
	$_GET['op']="";
	$_GET['show']=$_GET['show']; // huh? weshalb hab ich das geschrieben?
}

if ($_GET['op']=="add"){
	output("Item erzeugen:`n");
	addnav("Item Editor","itemeditor.php");
	if ($_GET['show']) addnav("$_GET[show]","itemeditor.php?show=".urlencode($_GET['show'])."");
	itemform(array("class"=>$_GET['show']));
}elseif ($_GET['op']=="edit"){
	addnav("Item Editor","itemeditor.php");
	if ($_GET['show']) addnav("$_GET[show]","itemeditor.php?show=".urlencode($_GET['show'])."");
$sql = "SELECT * FROM items WHERE id=?"; // Die Item-ID wird als Zahl an die Datenbank uebergeben.
$result = db_query_params($sql,array((int)db_unquote_legacy_input($_GET['id'])));
	if (db_num_rows($result)<=0){
		output("`iItem nicht vorhanden.`i");
	}else{
		output("Item Editor:`n");
		$row = db_fetch_assoc($result);
		$row['buff']=unserialize($row['buff']);
		itemform($row);
	}
}elseif ($_GET['op']=="save"){
	$buff = array();
	// Die Zauber-Eigenschaften werden wie bisher in einem Feld gespeichert.
	if (isset($_POST['item']['buff']) && is_array($_POST['item']['buff'])) {
		if (isset($_POST['item']['buff']['activate']) && is_array($_POST['item']['buff']['activate'])) $_POST['item']['buff']['activate']=join(",",$_POST['item']['buff']['activate']);
		foreach ($_POST['item']['buff'] as $key => $val) {
			if ($val > "") $buff[$key]=db_unquote_legacy_input($val);
		}
	}
	if (count($buff)>0) $_POST['item']['buff']=$buff;

	// Nur Spalten, die MariaDB wirklich in items kennt, duerfen als Feldnamen verwendet werden.
	$columnResult=db_query("SHOW COLUMNS FROM items");
	$allowedColumns=array();
	while ($columnRow=db_fetch_assoc($columnResult)) $allowedColumns[$columnRow['Field']]=true;
	$columnNames=array();
	$values=array();
	foreach ($_POST['item'] as $key => $val) {
		// Unbekannte Formularfelder werden ignoriert, damit keine SQL-Spalten eingeschleust werden koennen.
		if (!isset($allowedColumns[$key])) continue;
		if (is_array($val)) $val=serialize($val);
		else $val=db_unquote_legacy_input($val);
		$columnNames[]=$key;
		$values[]=$val;
	}

	if ($_GET['id']>""){
		// Spaltennamen stammen aus der Datenbank, Werte werden gebunden, und die Item-ID bleibt eine Zahl.
		$setParts=array();
		foreach ($columnNames as $columnName) $setParts[]="`".$columnName."`=?";
		$sql="UPDATE items SET ".implode(",",$setParts)." WHERE id=?";
		$values[]=(int)db_unquote_legacy_input($_GET['id']);
	}else{
		// Die Datenbank-Spaltennamen sind geprueft; fuer jeden Wert wird ein PDO-Platzhalter genutzt.
		$quotedColumns=array();
		foreach ($columnNames as $columnName) $quotedColumns[]="`".$columnName."`";
		$placeholders=implode(",",array_fill(0,count($values),"?"));
		$sql="INSERT INTO items (".implode(",",$quotedColumns).") VALUES (".$placeholders.")";
	}
	// So bleiben Texte mit Apostrophen unveraendert und koennen den SQL-Befehl nicht aufbrechen.
	db_query_params($sql,$values);	if (db_affected_rows()>0){
		output("Item gespeichert!");
	}else{
		output("Item nicht gespeichert: $sql");
	}
	addnav("Item Editor","itemeditor.php");
	if ($_GET['show']) addnav("$_GET[show]","itemeditor.php?show=".urlencode($_GET['show'])."");
}else{
	if($_GET['show']){
		$ppp=50; // Player Per Page to display
		if (!$_GET['limit']){
			$page=0;
		}else{
			$page=(int)$_GET['limit'];
			addnav("Vorherige Seite","itemeditor.php?show=".urlencode($_GET['show'])."&limit=".($page-1)."");
		}
		$limit="".($page*$ppp).",".($ppp+1);
// Die folgende vorbereitete Abfrage liefert die gefilterte Itemliste.
$sql = "SELECT items.*,accounts.name AS ownername FROM items LEFT JOIN accounts ON accounts.acctid=items.owner WHERE class=? ORDER BY id LIMIT $limit"; // Die Item-Klasse wird als Wert gebunden; die Seitenzahlen wurden vorher aus Zahlen berechnet.
$result = db_query_params($sql,array(db_unquote_legacy_input($_GET['show'])));
		output("<tr><td>Ops</td><td>Name</td><td>Besitzer</td><td>Beschreibung</td></tr>",true);
// Die vorbereitete Abfrage wurde bereits ausgefuehrt.
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","itemeditor.php?show=".urlencode($_GET['show'])."&limit=".($page+1)."");
		$cat = "";
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			output("<tr>",true);
			output("<td>[ <a href='itemeditor.php?op=edit&id=$row[id]&show=".urlencode($row['class'])."'>Edit</a> |",true);
			addnav("","itemeditor.php?op=edit&id=$row[id]&show=".urlencode($row['class'])."");
			output(" <a href='itemeditor.php?op=del&id=$row[id]&show=".urlencode($row['class'])."' onClick=\"return confirm('Diesen Gegenstand wirklich löschen?');\">Löschen</a> ]</td>",true);
			addnav("","itemeditor.php?op=del&id=$row[id]&show=".urlencode($row['class'])."");
			output("<td>$row[name]</td>",true);
			output("<td>$row[ownername]</td>",true);
			output("<td>$row[description]</td>",true);
			output("</tr>",true);
		}
		output("</table>",true);
		addnav("Item Editor","itemeditor.php");
		addnav("Item hinzufügen","itemeditor.php?op=add&show=".urlencode($_GET['show'])."");
	}else{
		$sql = "SELECT class FROM items ORDER BY class";
		output("Verfügbare \"classes\":`n`n<table>",true);
		output("<tr><td>Name</td></tr>",true);
		$result = db_query($sql);
		$cat = "";
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			if ($cat!=$row['class']){
				output("<tr><td><a href='itemeditor.php?show=".urlencode($row['class'])."'>$row[class]</a></td></tr>",true);
				$cat = $row['class'];
				addnav("","itemeditor.php?show=".urlencode($row['class'])."");
				output("</tr>",true);
			}
		}
		output("</table>`n`nUm eine class zu löschen, müssen alle Items dieser class gelöscht werden.`nUm eine neue class zu erstellen, einfach ein Item erzeugen.",true);
		addnav("Item hinzufügen","itemeditor.php?op=add");
	}
}

function itemform($item){
	global $output;
	output("<form action='itemeditor.php?op=save&id=$item[id]' method='POST'>",true);
	addnav("","itemeditor.php?op=save&id=$item[id]");
	$output.="<table>";
	$output.="<tr><td>Item Name:</td><td><input name='item[name]' value=\"".htmlentities($item['name'])."\" maxlength='25'></td></tr>";
	$output.="<tr><td>Item Beschreibung:</td><td><input name='item[description]' value=\"".htmlentities($item['description'])."\" maxlength='255'></td></tr>";
	$output.="<tr><td>Item Class:</td><td><input name='item[class]' value=\"".htmlentities($item['class'])."\" maxlength='25'></td></tr>";
	$output.="<tr><td>Besitzer ID:</td><td><input name='item[owner]' value=\"".htmlentities((int)$item['owner'])."\" size='5'></td></tr>";
	$output.="<tr><td>Item Wert (Edelsteine):</td><td><input name='item[gems]' value=\"".htmlentities((int)$item['gems'])."\" size='5'></td></tr>";
	$output.="<tr><td>Item Wert (Gold):</td><td><input name='item[gold]' value=\"".htmlentities((int)$item['gold'])."\" size='5'></td></tr>";
	$output.="<tr><td>Item Wert 1:</td><td><input name='item[value1]' value=\"".htmlentities((int)$item['value1'])."\" size='5'></td></tr>";
	$output.="<tr><td>Item Wert 2:</td><td><input name='item[value2]' value=\"".htmlentities((int)$item['value2'])."\" size='5'></td></tr>";
	$output.="<tr><td>Versteckter Wert:</td><td><input name='item[hvalue]' value=\"".htmlentities((int)$item['hvalue'])."\" size='5'></td></tr>";
	$output.="<tr><td valign='top'>Item Buff:</td><td>";
	$output.="<b>Meldungen:</b><Br/>";
	$output.="Buff Name: <input name='item[buff][name]' value=\"".htmlentities($item['buff']['name'])."\"><Br/>";
	//output("Initial Message: <input name='mount[mountbuff][startmsg]' value=\"".htmlentities($mount['mountbuff']['startmsg'])."\">`n",true);
	$output.="Meldung jede Runde: <input name='item[buff][roundmsg]' value=\"".htmlentities($item['buff']['roundmsg'])."\"><Br/>";
	$output.="Ablaufmeldung: <input name='item[buff][wearoff]' value=\"".htmlentities($item['buff']['wearoff'])."\"><Br/>";
	$output.="Effektmeldung: <input name='item[buff][effectmsg]' value=\"".htmlentities($item['buff']['effectmsg'])."\"><Br/>";
	$output.="Kein Schaden Meldung: <input name='item[buff][effectnodmgmsg]' value=\"".htmlentities($item['buff']['effectnodmgmsg'])."\"><Br/>";
	$output.="Fehlgeschlagen Meldung: <input name='item[buff][effectfailmsg]' value=\"".htmlentities($item['buff']['effectfailmsg'])."\"><Br/>";
	$output.="<Br/><b>Effekt:</b><Br/>";
	$output.="Hält Runden (nach Aktivierung): <input name='item[buff][rounds]' value=\"".htmlentities($item['buff']['rounds'])."\" size='5'><Br/>";
	$output.="Angriffsmulti Spieler: <input name='item[buff][atkmod]' value=\"".htmlentities($item['buff']['atkmod'])."\" size='5'><Br/>";
	$output.="Verteidigungsmulti Spieler: <input name='item[buff][defmod]' value=\"".htmlentities($item['buff']['defmod'])."\" size='5'><Br/>";
	$output.="Regen: <input name='item[buff][regen]' value=\"".htmlentities($item['buff']['regen'])."\"><Br/>";
	$output.="Diener Anzahl: <input name='item[buff][minioncount]' value=\"".htmlentities($item['buff']['minioncount'])."\"><Br/>";
	$output.="Min Badguy Damage: <input name='item[buff][minbadguydamage]' value=\"".htmlentities($item['buff']['minbadguydamage'])."\" size='5'><Br/>";
	$output.="Max Badguy Damage: <input name='item[buff][maxbadguydamage]' value=\"".htmlentities($item['buff']['maxbadguydamage'])."\" size='5'><Br/>";
	$output.="Lifetap: <input name='item[buff][lifetap]' value=\"".htmlentities($item['buff']['lifetap'])."\" size='5'><Br/>";
	$output.="Damage shield: <input name='item[buff][damageshield]' value=\"".htmlentities($item['buff']['damageshield'])."\" size='5'> (multiplier)<Br/>";
	$output.="Badguy Damage mod: <input name='item[buff][badguydmgmod]' value=\"".htmlentities($item['buff']['badguydmgmod'])."\" size='5'> (multiplier)<Br/>";
	$output.="Badguy Atk mod: <input name='item[buff][badguyatkmod]' value=\"".htmlentities($item['buff']['badguyatkmod'])."\" size='5'> (multiplier)<Br/>";
	$output.="Badguy Def mod: <input name='item[buff][badguydefmod]' value=\"".htmlentities($item['buff']['badguydefmod'])."\" size='5'> (multiplier)<Br/>";
	//$output.=": <input name='mount[mountbuff][]' value=\"".htmlentities($mount['mountbuff'][''])."\">`n",true);
	
	$output.="<Br/><b>Aktiviert bei:</b><Br/>";
	$output.="<input type='checkbox' name='item[buff][activate][]' value=\"roundstart\"".(strpos($item['buff']['activate'],"roundstart")!==false?" checked":"")."> Start der Runde<Br/>";
	$output.="<input type='checkbox' name='item[buff][activate][]' value=\"offense\"".(strpos($item['buff']['activate'],"offense")!==false?" checked":"")."> Bei Angriff<Br/>";
	$output.="<input type='checkbox' name='item[buff][activate][]' value=\"defense\"".(strpos($item['buff']['activate'],"defense")!==false?" checked":"")."> Bei Verteidigung<Br/>";
	$output.="<Br/>";
	$output.="</td></tr>";
	$output.="</table>";
	$output.="<input type='submit' class='button' value='Speichern'></form>";
}

page_footer();
?>
