<?php	
require_once "common.php";
isnewday(2);

//select distinct creaturelevel,max(creaturehealth) as creaturehealth,max(creatureattack) as creatureattack,max(creaturedefense) as creaturedefense,max(creatureexp) as creatureexp,max(creaturegold) as creaturegold from creatures where creaturelevel<17 group by creaturelevel;
$creaturestattable="
+---------------+----------------+----------------+-----------------+-------------+--------------+
| creaturelevel | creaturehealth | creatureattack | creaturedefense | creatureexp | creaturegold |
+---------------+----------------+----------------+-----------------+-------------+--------------+
|             1 |             10 |              1 |               1 |          14 |           36 |
|             2 |             21 |              3 |               3 |          24 |           97 |
|             3 |             32 |              5 |               4 |          34 |          148 |
|             4 |             43 |              7 |               6 |          45 |          162 |
|             5 |             53 |              9 |               7 |          55 |          198 |
|             6 |             64 |             11 |               8 |          66 |          234 |
|             7 |             74 |             13 |              10 |          77 |          268 |
|             8 |             84 |             15 |              11 |          89 |          302 |
|             9 |             94 |             17 |              13 |         101 |          336 |
|            10 |            105 |             19 |              14 |         114 |          369 |
|            11 |            115 |             21 |              15 |         127 |          402 |
|            12 |            125 |             23 |              17 |         141 |          435 |
|            13 |            135 |             25 |              18 |         156 |          467 |
|            14 |            145 |             27 |              20 |         172 |          499 |
|            15 |            155 |             29 |              21 |         189 |          531 |
|            16 |            166 |             31 |              22 |         207 |          563 |
+---------------+----------------+----------------+-----------------+-------------+--------------+
";
$creaturestats=Array();
$creaturestattable=explode("\n",$creaturestattable);
$x=0;
foreach ($creaturestattable as $key => $val) {
	if (strpos($val,"|")!==false){
		//echo("$val`n");
		$x++;
		$a = explode("|",$val);
		if ($x==1){
			$stats=array();
			foreach ($a as $key1 => $val1) {
				if (trim($val1)>"") {
					$stats[$key1]=trim($val1);
					//output($val1." is col $key1`n");
				}
			}
		}else{
			reset($stats);
			foreach ($stats as $key1 => $val1) {
				$creaturestats[(int)$a[1]][$val1]=trim($a[$key1]);
				//output ("[".(int)$a[1]."][$val1]=".trim($a[$key1])."`n");
			}
		}
	}
}

page_header("Creature Editor");

if ($session['user']['superuser'] >= 2){
	addnav("G?Zurück zur Grotte","superuser.php");
	addnav("W?Zurück zum Weltlichen","village.php");
	if ($HTTP_POST_VARS['save']<>""){
		// Ein fehlender Ort wird als Anfangswert gesetzt, damit neue Formulare ohne Ortsauswahl funktionieren.
		if (!isset($HTTP_POST_VARS['location'])) $HTTP_POST_VARS['location']=0;
		$creatureLevel=(int)$HTTP_POST_VARS['creaturelevel'];
		if (!isset($creaturestats[$creatureLevel])){
			output("Ungueltige Kreaturenstufe.");
		}else{
			// Erlaubte Spaltennamen kommen nur aus der festen Kreaturenstatistik, nie aus den Formularnamen.
			$fields=$creaturestats[$creatureLevel];
			// Diese drei Textfelder stehen im Formular, gehoeren aber nicht zur Zahlen-Tabelle der Standardwerte.
			$fields['creaturename']='';
			$fields['creatureweapon']='';
			$fields['creaturelose']='';
			$fields['location']=0;
			$fields['createdby']='';
			$columns=array();
			$values=array();
			foreach ($fields as $fieldName => $defaultValue){
				$columns[]=$fieldName;
				if ($fieldName=="createdby") $values[]=$session['user']['login'];
				elseif ($fieldName=="location") $values[]=(int)$HTTP_POST_VARS['location'];
				elseif (isset($HTTP_POST_VARS[$fieldName])) $values[]=db_unquote_legacy_input($HTTP_POST_VARS[$fieldName]);
				else $values[]=$defaultValue;
			}
			if ($HTTP_POST_VARS['id']!=""){
				// Spaltennamen stammen aus bekannten Spielwerten; alle Eingaben und die Datensatz-ID werden gebunden.
				$setParts=array();
				foreach ($columns as $columnName) $setParts[]=chr(96).$columnName.chr(96)."=?";
				$sql="UPDATE creatures SET ".implode(",",$setParts)." WHERE creatureid=?";
				$values[]=(int)$HTTP_POST_VARS['id'];
				db_query_params($sql,$values) or output("Fehler: ".db_error(LINK));
				output(db_affected_rows()." Datensatz(e) geaendert.");
			}else{
				// Spaltennamen stammen aus der festen Statistik; Platzhalter halten Texte aus dem SQL-Befehl heraus.
				$quotedColumns=array();
				foreach ($columns as $columnName) $quotedColumns[]=chr(96).$columnName.chr(96);
				$placeholders=implode(",",array_fill(0,count($values),"?"));
				$sql="INSERT INTO creatures (".implode(",",$quotedColumns).") VALUES (".$placeholders.")";
				db_query_params($sql,$values);
			}
		}
	}	if ($HTTP_GET_VARS['op']=="del"){
$sql="DELETE FROM creatures WHERE creatureid=?"; // Die Kreaturen-ID wird beim Loeschen als Zahl gebunden.
		db_query_params($sql,array((int)db_unquote_legacy_input($HTTP_GET_VARS['id'])));
		if (db_affected_rows()>0){
			output("Kreatur gelöscht`n`n");
		}else{
			output("Kreatur nicht gelöscht: ".db_error(LINK));
		}
		$HTTP_GET_VARS['op']="";
	}
	if ($HTTP_GET_VARS['op']==""){
		$sql = "SELECT * FROM creatures ORDER BY creaturelevel,creaturename";
		$result = db_query($sql) or die(db_error(LINK));
		addnav("Eine Kreatur hinzufügen","creatures.php?op=add");
		output("<table><tr><td>Ops</td><td>Kreaturname</td><td>Level</td><td>Waffe</td><td>Nachricht beim Tod</td><td>Autor</td></tr>",true);
		addnav("","creatures.php");
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			if ($row['creaturelevel']==17 || $row['creaturelevel']==18){
				output("<tr><td> [Edit|Del] </td><td>",true);
			}else{
				output("<tr><td> [<a href='creatures.php?op=edit&id=$row[creatureid]'>Edit</a>|".
				"<a href='creatures.php?op=del&id=$row[creatureid]' onClick='return confirm(\"Bist du dir sicher, dass du diese Kreatur löschen willst?\");'>Del</a>] </td><td>",true);
				addnav("","creatures.php?op=edit&id=$row[creatureid]");
				addnav("","creatures.php?op=del&id=$row[creatureid]");
			}
			output($row['creaturename']);
			output("</td><td>",true);
			output($row['creaturelevel']);
			output("</td><td>",true);
			output($row['creatureweapon']);
			output("</td><td>",true);
			output($row['creaturelose']);
			output("</td><td>",true);
			output($row['createdby']);
			output("</td></tr>",true);
		}
		output("</table>",true);
	}else{
		if ($HTTP_GET_VARS['op']=="edit" || $HTTP_GET_VARS['op']=="add"){
			if ($HTTP_GET_VARS['op']=="edit"){
$sql = "SELECT * FROM creatures WHERE creatureid=?"; // Die angeforderte Kreaturen-ID wird als Zahl gebunden.
				$result = db_query_params($sql,array((int)db_unquote_legacy_input($HTTP_GET_VARS['id']))) or die(db_error(LINK));
				if (db_num_rows($result)<>1){
					output("`4Fehler`0, diese Kreatur wurde nicht gefunden!");
				}else{
					$row = db_fetch_assoc($result);
				}
			}
			output("<form action='creatures.php' method='POST'>",true);
			output("<input name='id' value=\"".HTMLEntities($HTTP_GET_VARS['id'])."\" type='hidden'>",true);
			output("<table border='0' cellpadding='2' cellspacing='0'>",true);
			output("<tr><td>Kreaturname:</td><td><input name='creaturename' maxlength='50' value=\"".HTMLEntities($row['creaturename'])."\"></td></tr>",true);
			output("<tr><td>Waffe: </td><td><input name='creatureweapon' maxlength='50' value=\"".HTMLEntities($row['creatureweapon'])."\"></td></tr>",true);
			output("<tr><td colspan='2'>Nachricht beim Tod: <br><input name='creaturelose' size='65' maxlength='120' value=\"".HTMLEntities($row['creaturelose'])."\"></td></tr>",true);
			output("<tr><td>Level: </td><td><select name='creaturelevel'>",true);
			for ($i=1;$i<=16;$i++){
				output("<option value='$i'".($row['creaturelevel']==$i?" selected":"").">$i</option>\n",true);
			}
			output("</select></td></tr>",true);
			output("<tr><td>Kreatur ist auch auf dem Friedhof</td><td><input type='radio' name='location' value='1'".($row['location']==1?" checked":"").">Ja <input type='radio' name='location' value='0'".($row['location']==0?" checked":"").">Nein </td></tr>",true);
			output("<tr><td colspan='2'><input type='hidden' name='save' value='Save'><input type='submit' class='button' name='submit' value='Speichern'></td></tr>",true);
			output("</table>",true);
			output("</form>",true);
			addnav("","creatures.php");
		}else{
		
		}
		addnav("Zurück zum Monster-Editor","creatures.php");
	}
}else{
	output("Weil du versucht hast die Götter zu betrügen, wurdest du niedergeschmettert!");
	addnews("`&".$session['user']['name']." wurde für den Versuch, die Götter zu betrügen, niedergeschmettert (hat versucht die Superuser-Seiten zu hacken).");
	$session['user']['hitpoints']=0;
}
page_footer();
?>
