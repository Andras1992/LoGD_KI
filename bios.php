<?php
require_once "common.php";

isnewday(2);

if ($_GET['op']=="block"){
    // Die URL-ID wird als Zahl gebunden, damit sie den SQL-Befehl nicht veraendern kann.
    $acctid = (int)db_unquote_legacy_input($_GET['userid']);
    $sql = "UPDATE accounts SET bio='`iGesperrt aufgrund unangebrachten Inhalts`i',biotime='9999-12-31 23:59:59' WHERE acctid=?";
	systemmail($_GET['userid'],"Deine Kurzbeschreibung wurde gesperrt","Der Administrator hat beschlossen, dass deine Kurzbeschreibung unangebracht ist und hat sie gesperrt.`n`nWenn du darüber diskutieren willst, benutze bitte den Link zur Hilfeanfrage.");
	db_query_params($sql, array($acctid));
}
if ($_GET['op']=="unblock"){
	// Die Kontonummer wird als Zahl an PDO uebergeben und bleibt damit vom SQL-Text getrennt.
	$acctid = (int)db_unquote_legacy_input($_GET['userid']);
	$sql = "UPDATE accounts SET bio='',biotime='0000-00-00 00:00:00' WHERE acctid=?";
	systemmail($acctid,"Deine Kurzbeschreibung wurde wieder freigegeben","Der Administrator hat beschlossen, deine Kurzbeschreibung wieder freizugeben. Du kannst wieder eine Beschreibung eingeben.");
	db_query_params($sql, array($acctid));
}
$sql = "SELECT name,acctid,bio,biotime FROM accounts WHERE biotime<'9999-12-31' AND bio>'' ORDER BY biotime DESC LIMIT 100";
$result = db_query($sql);
page_header("Spielerkurzbeschreibungen");
output("`b`&Spieler Kurzbeschreibungen:`0`b`n");
for ($i=0;$i<db_num_rows($result);$i++){
    $row = db_fetch_assoc($result);
    if ($row['biotime']>$session['user']['recentcomments'])
        output("<img src='images/new.gif' alt='&gt;' width='3' height='5' align='absmiddle'> ",true);
    output("`![<a href='bios.php?op=block&userid={$row['acctid']}'>Block</a>]",true);
    addnav("","bios.php?op=block&userid={$row['acctid']}");
    output("`&{$row['name']}: `^".soap($row['bio'])."`n");
}
db_free_result($result);
addnav("G?Zurück zur Grotte","superuser.php");
addnav("W?Zurück zum Weltlichen","village.php");
addnav("Aktualisieren","bios.php");
//output("`n`n`bBlocked Bios:`b`n"); //This seems unneeded since we print it below
$sql = "SELECT name,acctid,bio,biotime FROM accounts WHERE biotime>'9000-01-01' AND bio>'' ORDER BY biotime DESC LIMIT 100";
$result = db_query($sql);
output("`n`n`b`&Gesperrte Beschreibungen:`0`b`n");
for ($i=0;$i<db_num_rows($result);$i++){
    $row = db_fetch_assoc($result);
    output("`![<a href='bios.php?op=unblock&userid={$row['acctid']}'>Unblock</a>]",true);
    addnav("","bios.php?op=unblock&userid={$row['acctid']}");
    output("`&{$row['name']}: `^".soap($row['bio'])."`n");
}
db_free_result($result);
page_footer();
?>
