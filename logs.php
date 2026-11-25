<?php

// 09072004

/*

View faillogs and user mail
Find multi accounts and cheaters 

by anpera

*/

	
require_once("common.php");
// Leere Standardwerte verhindern Warnungen, wenn eine URL oder ein Formular ein Feld nicht mitschickt.
$_GET += array('op'=>'','in'=>'','out'=>'','subop'=>'','limit'=>0,'order'=>'','id'=>'','ip'=>'','pw'=>'','act'=>'','searchby'=>'','userid'=>'');
$_POST += array('setupban'=>'','userid'=>array(),'type'=>'','ip'=>'','id'=>'','duration'=>0,'reason'=>'','deleteuser'=>'','stdreason'=>0,'savereason'=>'');

page_header("Logs und Mail");

if ($_GET['op']=="mail"){
	if ($_GET['in']){
		$ppp=25; // Player Per Page to display
		if (!$_GET['limit']){
			$page=0;
		}else{
			$page=max(0,(int)$_GET['limit']);
			addnav("Vorherige Seite","logs.php?op=mail&in=$_GET[in]&limit=".($page-1)."");
		}
		$limit="".($page*$ppp).",".($ppp+1);
		output("`qYe Olde Mail (".($_GET['limit']*$ppp)."-".($_GET['limit']*$ppp+$ppp).") -- Alle Mails: Inbox $_GET[in] `0`n`n");
		// Die Postfach-ID wird als Zahl gebunden; der Seitenbereich wird vorher numerisch berechnet.
		$sql = "SELECT mail.*,accounts.login AS absender FROM mail LEFT JOIN accounts ON accounts.acctid=mail.msgfrom WHERE msgto=? ORDER BY sent DESC LIMIT $limit";
		$result = db_query_params($sql, array((int)db_unquote_legacy_input($_GET['in']))) or die(db_error(LINK));
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","logs.php?op=mail&in=$_GET[in]&limit=".($page+1)."");
		output("<table align='center'><tr><td>`bDatum`b</td><td>`bAbsender`b</td><td>`bBetreff`b</td></tr>",true);
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			output("<tr><td>$row[sent]</td><td>$row[absender]</td><td>$row[subject]</td></tr><tr><td colspan='3'>$row[body]</td></tr>",true);
			output("<tr><td colspan='3'><hr></td></tr>",true);
		}
		output("</table>",true);
		addnav("Zurück","logs.php?op=mail");
	}else if ($_GET['out']){
		$ppp=25; // Player Per Page to display
		if (!$_GET['limit']){
			$page=0;
		}else{
			$page=max(0,(int)$_GET['limit']);
			addnav("Vorherige Seite","logs.php?op=mail&out=$_GET[in]&limit=".($page-1)."");
		}
		$limit="".($page*$ppp).",".($ppp+1);
		output("`qYe Olde Mail (".($_GET['limit']*$ppp)."-".($_GET['limit']*$ppp+$ppp).") -- Alle Mails: Outbox $_GET[out] `0`n`n");
		// Die Konto-ID der gesendeten Nachrichten bleibt ein gebundener Zahlenwert.
		$sql = "SELECT mail.*,accounts.login AS empfaenger FROM mail LEFT JOIN accounts ON accounts.acctid=mail.msgto WHERE msgfrom=? ORDER BY sent DESC LIMIT $limit";
		$result = db_query_params($sql, array((int)db_unquote_legacy_input($_GET['out']))) or die(db_error(LINK));
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","logs.php?op=mail&out=$_GET[out]&limit=".($page+1)."");
		output("<table align='center'><tr><td>`bDatum`b</td><td>`bEmpfänger`b</td><td>`bBetreff`b</td></tr>",true);
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			output("<tr><td>$row[sent]</td><td>$row[empfaenger]</td><td>$row[subject]</td></tr><tr><td colspan='3'>$row[body]</td></tr>",true);
			output("<tr><td colspan='3'><hr></td></tr>",true);
		}
		output("</table>",true);
		addnav("Zurück","logs.php?op=mail");
	}else{
		if (!$_GET['subop']) $_GET['subop']="system";
		$sort="sent";
		$ppp=25; // Player Per Page to display
		if (!$_GET['limit']){
			$page=0;
		}else{
			$page=max(0,(int)$_GET['limit']);
			addnav("Vorherige Seite","logs.php?op=mail&limit=".($page-1)."&subop=$_GET[subop]&order=$sort");
		}
		$limit="".($page*$ppp).",".($ppp+1);
		$sort="sent";
		if ($_GET['order']) $sort=$_GET['order'];
		output("`qYe Olde Mail (".($_GET['limit']*$ppp)."-".($_GET['limit']*$ppp+$ppp).") -- ".($_GET['subop']=="all"?"Private Mails":"Systemnachrichten")."`0`n`n");
		//addnav($_GET[subop]=="system"?"Normale Nachrichten":"Systemnachrichten","logs.php?op=mail&subop=".($_GET[subop]=="system"?"all":"system")."&order=$_GET[order]&limit=$_GET[limit]"); // ist möglicherweise nicht erlaubt
		// Nur diese drei Spalten sind als Sortierung fuer die Mailliste erlaubt.
		$sort = in_array($sort, array("sent","msgfrom","msgto"), true) ? $sort : "sent";
		$sql = "SELECT mail.*,accounts.login AS empfaenger FROM mail LEFT JOIN accounts ON accounts.acctid=mail.msgto WHERE ".($_GET['subop']=="system"?"msgfrom=0":"msgfrom<>0")." AND msgto<>0 ORDER BY $sort DESC LIMIT $limit";
		$result = db_query($sql) or die(db_error(LINK));
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","logs.php?op=mail&limit=".($page+1)."&order=$sort&subop=$_GET[subop]");
		output("<table align='center'><tr><td>`b<a href='logs.php?op=mail&limit=$page&order=sent&subop=$_GET[subop]'>Datum</a>`b</td><td>`b<a href='logs.php?op=mail&limit=$page&order=msgfrom&subop=$_GET[subop]'>Absender</a>`b</td><td>`b<a href='logs.php?op=mail&limit=$page&order=msgto&subop=$_GET[subop]'>Empfänger</a>`b</td><td>`bBetreff`b</td></tr>",true);
		addnav("","logs.php?op=mail&limit=$page&order=sent&subop=$_GET[subop]");
		addnav("","logs.php?op=mail&limit=$page&order=msgto&subop=$_GET[subop]");
		addnav("","logs.php?op=mail&limit=$page&order=msgfrom&subop=$_GET[subop]");
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			// Die Absender-ID stammt aus dem Datenbankeintrag und wird als Zahl gebunden.
			$row2=db_fetch_assoc(db_query_params("SELECT acctid,login FROM accounts WHERE acctid=?", array((int)$row['msgfrom'])));
			output("<tr><td>$row[sent]</td><td><a href='logs.php?op=mail&out=$row2[acctid]'>$row2[login]</a></td><td><a href='logs.php?op=mail&in=$row[msgto]'>$row[empfaenger]</a></td><td>$row[subject]</td></tr><tr><td colspan='4'>$row[body]</td></tr>",true);
			output("<tr><td colspan='4'><hr></td></tr>",true);
			addnav("","logs.php?op=mail&in=$row[msgto]");
			addnav("","logs.php?op=mail&out=$row2[acctid]");
		}
		output("</table>",true);
		addnav("Zurück","logs.php");
		output("`n`iUm das Postfach eines Spielers zu sehen, auf seinen Namen unter \"Empfänger\" klicken.`nUm zu sehen, was ein Spieler gesendet hat, auf seinen Namen unter \"Absender\" klicken.`i");
	}
}else if ($_GET['op']=="faillog"){
	if ($_GET['id']){
			$sort="date";
		$ppp=25; // Player Per Page to display
		if (!$_GET['limit']){
			$page=0;
		}else{
			$page=max(0,(int)$_GET['limit']);
			addnav("Vorherige Seite","logs.php?op=faillog&id=$_GET[id]&limit=".($page-1)."&order=$sort&pw=$_GET[pw]");
		}
		$limit="".($page*$ppp).",".($ppp+1);
		$sort="date";
		if ($_GET['order']) $sort=$_GET['order'];
		// Die Spieler-ID wird gebunden, damit die URL den Datenbankbefehl nicht veraendern kann.
		// Fuer diese Liste sind nur Datum und IP als Sortierung erlaubt.
		$sort = in_array($sort, array("date","ip"), true) ? $sort : "date";
		$sql = "SELECT * FROM faillog WHERE acctid=? ORDER BY $sort DESC LIMIT $limit";
		$result = db_query_params($sql, array((int)db_unquote_legacy_input($_GET['id']))) or die(db_error(LINK));
		output("Fehlgeschlagene Logins (".($_GET['limit']*$ppp)."-".($_GET['limit']*$ppp+$ppp).") für ID $_GET[id]`n`n");
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","logs.php?op=faillog&id=$_GET[id]&limit=".($page+1)."&order=$sort&pw=$_GET[pw]");
		output("<table align='center'><tr><td>`b<a href='logs.php?op=faillog&id=$_GET[id]&limit=$page&order=date'>Datum</a>`b</td><td>`b<a href='logs.php?op=faillog&id=$_GET[id]&limit=$page&order=ip'>IP</a>`b</td>".($_GET['pw']?"<td>`bfalsches PW`b</td>":"")."</tr>",true);
		addnav("","logs.php?op=faillog&id=$_GET[id]&limit=$page&order=date");
		addnav("","logs.php?op=faillog&id=$_GET[id]&limit=$page&order=ip");
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			if ($_GET['pw'] && $session['user']['superuser']>=3) $row['post']=unserialize($row['post']);
			output("<tr><td>$row[date]</td><td>$row[ip]</td>".($_GET['pw']?"<td>".$row['post']['password']."</td>":"")."</tr>",true);
		}
		addnav("Zurück","logs.php?op=faillog&order=$_GET[order]");
		output("</table>",true);
		if ($session['user']['superuser']>=3) addnav("PWs ".($_GET['pw']?"ausblenden":"einblenden")."","logs.php?op=faillog&id=$_GET[id]".($_GET['pw']?"":"&pw=true")."&order=$_GET[order]&limit=$_GET[limit]");
		if ($_GET['pw'] && $i>=3) output("`n`iDie Anzeige der falsch eingegebenen Passwörter dient dazu, um festzustellen, wo Passwörter geraten werden und wo nur ein Tippfehler vorliegt.`i");
	}else if ($_GET['ip']){
		$ppp=25; // Player Per Page to display
		if (!$_GET['limit']){
			$page=0;
		}else{
			$sort="date";
			$page=max(0,(int)$_GET['limit']);
			addnav("Vorherige Seite","logs.php?op=faillog&ip=$_GET[ip]&limit=".($page-1)."&pw=$_GET[pw]");
		}
		$limit="".($page*$ppp).",".($ppp+1);
		// Die IP-Adresse ist Text und wird als Parameter sicher gebunden.
		$sql = "SELECT faillog.*,accounts.name AS absender FROM faillog LEFT JOIN accounts ON accounts.acctid=faillog.acctid WHERE ip=? ORDER BY date DESC LIMIT $limit";
		$result = db_query_params($sql, array(db_unquote_legacy_input($_GET['ip']))) or die(db_error(LINK));
		output("Fehlgeschlagene Logins (".($_GET['limit']*$ppp)."-".($_GET['limit']*$ppp+$ppp).") von IP $_GET[ip]`n`n");
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","logs.php?op=faillog&ip=$_GET[ip]&limit=".($page+1)."&pw=$_GET[pw]");
		output("<table align='center'><tr><td>`bDatum`b</td><td>`bName`b</td>".($_GET['pw']?"<td>`bfalsches PW`b</td>":"")."</tr>",true);
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			if ($_GET['pw'] && $session['user']['superuser']>=3) $row['post']=unserialize($row['post']);
			output("<tr><td>$row[date]</td><td>$row[absender]</td>".($_GET['pw']?"<td>".$row['post']['password']."</td>":"")."</tr>",true);
		}
		addnav("Zurück","logs.php?op=faillog&order=$_GET[order]");
		output("</table>",true);
		if ($session['user']['superuser']>=3) addnav("PWs ".($_GET['pw']?"ausblenden":"einblenden")."","logs.php?op=faillog&ip=$_GET[ip]".($_GET['pw']?"":"&pw=true")."&order=$_GET[order]&limit=$_GET[limit]");
		if ($_GET['pw'] && $i>=3) output("`n`iDie Anzeige der falsch eingegebenen Passwörter dient dazu, um festzustellen, wo Passwörter geraten werden und wo nur ein Tippfehler vorliegt.`i");
	}else{
		$ppp=25; // Player Per Page to display
		output("Fehlgeschlagene Logins (".($_GET['limit']*$ppp)."-".($_GET['limit']*$ppp+$ppp).")`n`iSpielername oder IP anklicken, um alle Fehlversuche anzuzeigen.`i`n`n");
		if (!$_GET['limit']){
			$page=0;
		}else{
			$page=max(0,(int)$_GET['limit']);
			addnav("Vorherige Seite","logs.php?op=faillog&limit=".($page-1)."&order=$sort");
		}
		$limit="".($page*$ppp).",".($ppp+1);
		$sort="date";
		if ($_GET['order']) $sort=$_GET['order'];
		// Nur bekannte Spaltennamen duerfen die Reihenfolge der Fehlerliste bestimmen.
		$sort = in_array($sort, array("date","acctid","ip"), true) ? $sort : "date";
		$sql = "SELECT faillog.*,accounts.name AS absender FROM faillog LEFT JOIN accounts ON accounts.acctid=faillog.acctid WHERE 1 ORDER BY $sort DESC LIMIT $limit";
		$result = db_query($sql) or die(db_error(LINK));
		if (db_num_rows($result)>$ppp) addnav("Nächste Seite","logs.php?op=faillog&limit=".($page+1)."&order=$sort");
		output("<table align='center'><tr><td>`b<a href='logs.php?op=faillog&limit=$page&order=date'>Datum</a>`b</td><td>`b<a href='logs.php?op=faillog&limit=$page&order=acctid'>Acctid</a>`b</td><td>`bName`b</td><td>`b<a href='logs.php?op=faillog&limit=$page&order=ip'>IP</a>`b</td></tr>",true);
		addnav("","logs.php?op=faillog&limit=$page&order=date");
		addnav("","logs.php?op=faillog&limit=$page&order=acctid");
		addnav("","logs.php?op=faillog&limit=$page&order=ip");
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			output("<tr><td>$row[date]</td><td>$row[acctid]</td><td><a href='logs.php?op=faillog&id=$row[acctid]&order=$sort'>$row[absender]</a></td><td><a href='logs.php?op=faillog&ip=$row[ip]&order=$sort'>$row[ip]</a></td></tr>",true);
			addnav("","logs.php?op=faillog&id=$row[acctid]&order=$sort");
			addnav("","logs.php?op=faillog&ip=$row[ip]&order=$sort");
		}
		output("</table>",true);
		addnav("Zurück","logs.php");
	}
} elseif ($_GET['op']=='multi') {
	if (!empty($_POST['setupban']) && isset($_POST['userid']) && is_array($_POST['userid']) && count($_POST['userid'])>0) {
		output("<form action='logs.php?op=multi&act=saveban&searchby=$_GET[searchby]' method='POST'>",true);
		if ($_POST['setupban']=='IPs bannen') {
            // Konto-IDs werden erst in Zahlen umgewandelt und dann sicher an SQL gebunden.
            $selectedIds = array_map('intval', $_POST['userid']);
            $idPlaceholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $sql = "SELECT lastip FROM accounts WHERE acctid IN ($idPlaceholders) GROUP BY lastip";
            $result = db_query_params($sql, $selectedIds);
			$ips = array();
			while ($row = db_fetch_assoc($result)) $ips[] = $row['lastip'];
			output('Sperre für die IP '.implode(', ',$ips).'`n');
			output('<input type="hidden" name="type" value="ip"><input type="hidden" name="ip" value="'.implode('|',$ips).'">',true);
		}
		else {
            // Dieselben Konto-IDs werden gebunden, um die eindeutigen Spielerkennungen auszulesen.
            $selectedIds = array_map('intval', $_POST['userid']);
            $idPlaceholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $sql = "SELECT uniqueid FROM accounts WHERE acctid IN ($idPlaceholders) GROUP BY uniqueid";
            $result = db_query_params($sql, $selectedIds);
			$ids = array();
			while ($row = db_fetch_assoc($result)) $ids[] = $row['uniqueid'];
			output('Sperre für die ID '.implode(', ',$ids).'`n');
			output('<input type="hidden" name="type" value="id"><input type="hidden" name="id" value="'.implode('|',$ids).'">',true);
		}
		output("Dauer: <input name='duration' id='duration' size='3' value='14'> days (0 for permanent)`n",true);
		output("Grund für die Verbannung: <input name='reason' value=\"Ärger mich nicht.\">`n",true);
		output("<input type='submit' class='button' value='Post Ban' onClick='if (document.getElementById(\"duration\").value==0) {return confirm(\"Willst du wirklich eine permanente Verbannung aussprechen?\");} else {return true;}'></form>",true);
		addnav("","logs.php?op=multi&act=saveban&searchby=$_GET[searchby]");
	}
	elseif ($_GET['act']=='saveban') {
		if ($_POST['type']=='ip') $vals = explode('|',$_POST['ip']);
		else $vals = explode('|',$_POST['id']);

		// PHP 8 reserviert $this fuer Objekte; hier speichern wir den gerade bearbeiteten Ban-Wert in einer normalen Variable.
		foreach ($vals AS $loopValue) {
            // Die Spalte wird aus zwei festen Namen gewaehlt; IP, ID und Begruendung bleiben Datenwerte.
            $banColumn = ($_POST['type']=="ip") ? "ipfilter" : "uniqueid";
            $duration = (int)db_unquote_legacy_input($_POST['duration']);
            $banExpire = ($duration==0) ? "0000-00-00" : date("Y-m-d",strtotime("+".$duration." days"));
            $sql = "INSERT INTO bans ($banColumn,banexpire,banreason) VALUES (?,?,?)";
			if ($_POST['type']=="ip"){
				if (substr($_SERVER['REMOTE_ADDR'],0,strlen($loopValue)) == $loopValue){
					$sql = "";
					output("Du willst dich doch nicht wirklich selbst verbannen, oder?? Das ist deine eigene IP-Adresse!");
				}
			}else{
				if ($_COOKIE['lgi']==$loopValue){
					$sql = "";
					output("Du willst dich doch nicht wirklich selbst verbannen, oder?? Das ist deine eigene ID!");
				}
			}
			if ($sql!=""){
                db_query_params($sql, array(db_unquote_legacy_input($loopValue), $banExpire, db_unquote_legacy_input($_POST['reason']))) or die(db_error(LINK));
				output(db_affected_rows()." Bann eingetragen.`n`n");
				output(db_error(LINK));
			}
		}
		output('`n');
	}
	elseif (!empty($_POST['deleteuser']) && isset($_POST['userid']) && is_array($_POST['userid']) && count($_POST['userid'])>0) {
		// Formularwerte werden zu Zahlen gemacht, damit sie keinen SQL-Befehl einschleusen können.
		$userIds = array_map('intval', $_POST['userid']);
		// Für jede Konto-ID gibt es ein Fragezeichen; die Zahlen werden separat und sicher an SQL übergeben.
		$idPlaceholders = implode(',', array_fill(0, count($userIds), '?'));
		$sql = "SELECT name FROM accounts WHERE acctid IN ($idPlaceholders)";
		$result = db_query_params($sql, $userIds) or die(sql_error($sql));
		$delnames = array();
		while ($row = db_fetch_assoc($result)) {
			$delnames[] = '`$'.$row['name'].'`0';
		}
		output('User '.implode(', ',$delnames).' aus folgendem Grund löschen: ');
		output("<form action='logs.php?op=multi&act=dodeleteuser&searchby=$_GET[searchby]&userid=".implode(',',$userIds)."' method='POST'>",true);
		output('Begründung auswählen: ');
		output('<select name="stdreason" size="1">',true);
		output('<option value="0">--- keine Begründung angeben ---</option>',true);
		$sql = 'SELECT reasonid, reason FROM deluser_reasons';
		$result = db_query($sql);
		while ($row = db_fetch_assoc($result)) {
			if (strlen($row['reason'])>50) $row['reason'] = substr($row['reason'],0,47).'...';
			output('<option value="'.$row['reasonid'].'">'.htmlentities($row['reason']).'</option>',true);
		}
		output('</select>',true);
		output(' `n`ioder`i eingeben: ');
		output('<input type="Text" name="reason" value="" maxlength="255" size="50" /> ',true);
		output('<input type="Checkbox" name="savereason" value="1">',true);
		output('Begründung speichern`n');
		output('</option>',true);

		output('<input type="Submit" value="löschen">',true);
		output('</form>',true);
		addnav('','logs.php?op=multi&act=dodeleteuser&searchby='.$_GET['searchby'].'&userid='.implode(',',$userIds));
	}
	elseif ($_GET['act']=='dodeleteuser' && $_SERVER['REQUEST_METHOD']=='POST') {
		// Das Loeschen darf nur nach dem bestaetigten Formular per POST starten, nie durch einen einfachen Link.
		$userid = array_map('intval', explode(',', $_GET['userid']));
		// Diese Schleife verarbeitet jede ausgewaehlte Spieler-ID einzeln.
		foreach ($userid AS $loopValue) {
			deleteuser($loopValue, (int)$_POST['stdreason'], $_POST['reason'], !empty($_POST['savereason']));
		}
		output('
');
	}
	else output('`n');


	// Unterabfragen finden IPs und Kennungen, die bei mehr als einem Konto vorkommen; alle Werte bleiben in der Datenbank und werden nicht in SQL-Text eingesetzt.
	$sql = 'SELECT acctid,name,lastip,uniqueid,dragonkills,level FROM accounts WHERE ((lastip IN (SELECT lastip FROM accounts WHERE LENGTH(lastip)>0 GROUP BY lastip HAVING COUNT(*) > 1) AND LENGTH(lastip)>0) OR (uniqueid IN (SELECT uniqueid FROM accounts WHERE LENGTH(uniqueid)>0 GROUP BY uniqueid HAVING COUNT(*) > 1) AND LENGTH(uniqueid)>0)) AND locked=0 ORDER BY dragonkills ASC, level ASC';
	$result = db_query($sql) or die(db_error(LINK));
	$ip = $id = $users = array();
	while ($row = db_fetch_assoc($result)) {
		if ((!isset($id[$row['uniqueid']]) || $_GET['searchby']=='ip') && (!isset($ip[$row['lastip']]) || $_GET['searchby']=='id')) {
			if ($_GET['searchby']!='id') $ip[$row['lastip']] = count($users);
			if ($_GET['searchby']!='ip') $id[$row['uniqueid']] = count($users);
			$users[] = array($row);
		}
		elseif (isset($id[$row['uniqueid']])) {
			$ip[$row['lastip']] = $id[$row['uniqueid']];
			$users[$id[$row['uniqueid']]][] = $row;
		}
		else {
			$id[$row['uniqueid']] = $ip[$row['lastip']];
			$users[$ip[$row['lastip']]][] = $row;
		}
	}

	output('`n`bMultiaccounts`b`nNaaa, wer spielt denn hier noch wen?`n`n');
	output('Multiaccounts suchen nach: ');
	if ($_GET['searchby']!='ip') {
		output('<a href="logs.php?op=multi&searchby=ip">IP</a> ',true);
		addnav('','logs.php?op=multi&searchby=ip');
	}
	else output('`&`bIP`b`0 ');
	if ($_GET['searchby']!='id') {
		output('<a href="logs.php?op=multi&searchby=id">ID</a> ',true);
		addnav('','logs.php?op=multi&searchby=id');
	}
	else output('`&`bID`b`0 ');
	if (!empty($_GET['searchby'])) {
		output('<a href="logs.php?op=multi&searchby=">Beidem</a> ',true);
		addnav('','logs.php?op=multi&searchby=');
	}
	else output('`&`bBeidem`b`0 ');
	output('<table><tr><td>',true);
	foreach ($users AS $list) {
		if (count($list)<3) continue;
		$tmpstr = '';
		$ips = $ids = $accts = array();
		// Diese Schleife zeigt die Spieler aus der Ergebnisliste einzeln an.
		foreach ($list AS $loopValue) {
			$tmpstr .= ('<tr><td><input type="checkbox" name="userid[]" value="'.$loopValue['acctid'].'"></td>
							<td>'.$loopValue['name'].'</td>
							<td>'.$loopValue['lastip'].'</td>
							<td>'.$loopValue['uniqueid'].'</td>
							<td>'.$loopValue['dragonkills'].'</td>
							<td>'.$loopValue['level'].'</td></tr>');
		}
		output('<form action="logs.php?op=multi&searchby='.$_GET['searchby'].'" method="post">',true);
		addnav('','logs.php?op=multi&searchby='.$_GET['searchby']);
		output("<table align='center' class='input' width='100%'><tr><td>&nbsp;</td>
						<td>`bName`b</td>
						<td>`bIP`b</td>
						<td>`bID`b</td>
						<td>`bDK`b</td>
						<td>`bLevel`b</td>
						</tr>",true);
		output($tmpstr,true);
		output('<tr><td colspan="6" align="left">
						<input type="submit" name="deleteuser" value="löschen">
						<input type="submit" name="setupban" value="IPs bannen">
						<input type="submit" name="setupban" value="IDs bannen">
					</td></tr>',true);
		output('</table>`n`n',true);
		output('</form>',true);
	}
	output('</td></tr></table>',true);
	addnav('Aktualisieren','logs.php?op=multi&searchby='.$_GET['searchby']);
	addnav('Zurück','logs.php');
}else{
	output("Die 5 letzten fehlgeschlagenen Logins:`n`n");
	$sql = "SELECT faillog.*,accounts.name AS absender FROM faillog LEFT JOIN accounts ON accounts.acctid=faillog.acctid WHERE 1 ORDER BY date DESC LIMIT 5";
	$result = db_query($sql) or die(db_error(LINK));
	output("<table align='center'><tr><td>`bDatum`b</td><td>`bAcctid`b</td><td>`bName`b</td><td>`bIP`b</td></tr>",true);
	for ($i=0;$i<db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		output("<tr><td>$row[date]</td><td>$row[acctid]</td><td>$row[absender]</td><td>$row[ip]</td></tr>",true);
	}
	output("</table>`n`nDie 5 letzten Systemmails:`n`n",true);
	$sql = "SELECT mail.*,accounts.name AS empfaenger FROM mail LEFT JOIN accounts ON accounts.acctid=mail.msgto WHERE msgfrom=0 ORDER BY sent DESC LIMIT 5";
	$result = db_query($sql) or die(db_error(LINK));
	output("<table align='center'><tr><td>`bDatum`b</td><td>`bEmpfänger`b</td><td>`bBetreff`b</td></tr>",true);
	for ($i=0;$i<db_num_rows($result);$i++){
		$row = db_fetch_assoc($result);
		output("<tr><td>$row[sent]</td><td>$row[empfaenger]</td><td>$row[subject]</td></tr>",true);
	}
	output("</table>`n",true);
	addnav("Faillog","logs.php?op=faillog");
	addnav("Usermails","logs.php?op=mail");
	addnav("Multiaccounts","logs.php?op=multi");
	addnav("Aktualisieren","logs.php");
}
addnav("Zurück zur Grotte","superuser.php");
addnav("Zurück zum Weltlichen","village.php");
output("`n<div align='right'>`)2004 by anpera & Chaosmaker</div>",true);
page_footer();
?>
