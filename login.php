<?php
require_once "common.php";

if ($HTTP_POST_VARS['name']!=""){
	if ($session['loggedin']){
		redirect("badnav.php");
	}else{
		if(0){
		}else{
			$result = db_fetch_assoc(db_query_params("SELECT COUNT(acctid) AS onlinecount FROM accounts WHERE locked=0 AND loggedin=1 AND laston>?", array(date("Y-m-d H:i:s",strtotime(date("r")."-".getsetting("LOGINTIMEOUT",900)." seconds")))));
			$onlinecount = $result['onlinecount'];

			$sql = "SELECT * FROM accounts WHERE login = ? AND password=MD5(?) AND locked=0";
			$result = db_query_params($sql, array(db_unquote_legacy_input($HTTP_POST_VARS["name"]), db_unquote_legacy_input($HTTP_POST_VARS["password"])));
			if (db_num_rows($result)==1){
				$session['user']=db_fetch_assoc($result);
				//echo "Ooga Booga";
				//flush();
				//exit();
				checkban($session['user']['login']); //check if this account is banned
				checkban(); //check if this computer is banned
				/*if (strpos($_SERVER['SERVER_NAME'],"logd.mightye.org")!==false && $session['user']['superuser']<1){
					if (date("H")<2 || date("H")>17){
					$session[message]="`\$The dev server shuts down in the
						evening now so that I may partake of my home
						bandwidth.  This server is only available from
						2am to 5pm from now on.";
					//echo $session[message];
					header("Location: index.php");
					exit();
					//redirect("index.php");
					}
				}*/
				if ($session['user']['emailvalidation']!="" && substr($session['user']['emailvalidation'],0,1)!="x"){
					$session['user']=array();
					$session['message']="`4Fehler: Du musst deine E-Mail Adresse bestätigen lassen, bevor du dich einloggen kannst.";
					echo $session['message'];
					//header("Location: index.php");
					exit();
				}else{
					if ($onlinecount<getsetting("maxonline",10) || getsetting("maxonline",10)==0 || $session['user']['superuser']>0){
						//loaduser($session['user']);
						$session['loggedin']=true;
						$session['output']=$session['user']['output'];
						$session['petitions'] = array();
						$session['laston']=date("Y-m-d H:i:s");
						$session['sentnotice']=0;
						$session['user']['dragonpoints']=unserialize($session['user']['dragonpoints']);
						$session['user']['prefs']=unserialize($session['user']['prefs']);
						$session['bufflist']=unserialize($session['user']['bufflist']);
						if (!is_array($session['user']['dragonpoints'])) $session['user']['dragonpoints']=array();
						if ($session['user']['loggedin']){
							$session['allowednavs']=unserialize($session['user']['allowednavs']);
							saveuser();
							header("Location: {$session['user']['restorepage']}");
							exit();
							//redirect($session['user']['page']);//"badnav.php");
						}
								$result = db_query_params("UPDATE accounts SET loggedin=?, location=0 WHERE acctid = ?", array(true, (int)$session["user"]["acctid"]));
						$session['user']['loggedin']=true;
						$location = $session['user']['location'];
						$session['user']['location']=0;
						debuglog("logged in ");
						if ($session['user']['alive']==0 && $session['user']['slainby']!=""){
							//they're not really dead, they were killed in pvp.
							$session['user']['alive']=true;
						}
						if (getsetting("logdnet",0)){
							//register with LoGDnet
							@file(getsetting("logdnetserver","http://lotgd.net/")."logdnet.php?addy=".URLEncode(getsetting("serverurl","http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])))."&desc=".URLEncode(getsetting("serverdesc","Another LoGD Server"))."&version=".URLEncode($logd_version)."");
						}
						if ($location==0){
							redirect("news.php");
						}else if($location==1){
							redirect("inn.php?op=strolldown");
						}else if($location==2){
							redirect("houses.php?op=newday");
						}else{
							saveuser();
							header("Location: {$session['user']['restorepage']}");
							exit();
						}
					}else{
						$session['user'] = array();
						$session['message']="`4Fehler: Der Server ist voll.`0";
						redirect("index.php");
					}
				}
			}else{
				$session['message']="`4Fehler: Login-Daten waren ungültig.`0";
				//now we'll log the failed attempt and begin to issue bans if there are too many, plus notify the admins.
$sql = "DELETE FROM faillog WHERE date<?";
				checkban();
db_query_params($sql, array(date("Y-m-d H:i:s",strtotime(date("r")."-".(getsetting("expirecontent",180)/4)." days"))));
$sql = "SELECT acctid FROM accounts WHERE login=?";
$result = db_query_params($sql, array(db_unquote_legacy_input($_POST["name"])));
				if (db_num_rows($result)>0){ // just in case there manage to be multiple accounts on this name.
					while ($row=db_fetch_assoc($result)){
$sql = "INSERT INTO faillog VALUES (0,now(),?,?,?,?)";
db_query_params($sql, array(serialize($_POST), $_SERVER["REMOTE_ADDR"], (int)$row["acctid"], db_unquote_legacy_input($_COOKIE["lgi"])));
$sql = "SELECT faillog.*,accounts.superuser,name,login FROM faillog INNER JOIN accounts ON accounts.acctid=faillog.acctid WHERE ip=? AND date>?";
$result2 = db_query_params($sql, array($_SERVER["REMOTE_ADDR"], date("Y-m-d H:i:s",strtotime(date("r")."-1 day"))));
						$c=0;
						$alert="";
						$su=false;
						while ($row2=db_fetch_assoc($result2)){
							if ($row2['superuser']>0) {$c+=1; $su=true;}
							$c+=1;
							$alert.="`3{$row2['date']}`7: Failed attempt from `&{$row2['ip']}`7 [`3{$row2['id']}`7] to log on to `^{$row2['login']}`7 ({$row2['name']}`7)`n";
						}
						if ($c>=10){ // 5 failed attempts for superuser, 10 for regular user
$sql = "INSERT INTO bans VALUES (?,?,?,?)";
db_query_params($sql, array($_SERVER["REMOTE_ADDR"], "", date("Y-m-d H:i:s",strtotime(date("r")."+".($c*3)." hours")), "Automatischer Systembann: Zu viele fehlgeschlagene Loginversuche."));
							if ($su){ // send a system message to admins regarding this failed attempt if it includes superusers.
								$sql = "SELECT acctid FROM accounts WHERE superuser>=3";
								$result2 = db_query($sql);
								$subj = "`#{$_SERVER['REMOTE_ADDR']} failed to log in too many times!";
								for ($i=0;$i<db_num_rows($result2);$i++){
									$row2 = db_fetch_assoc($result2);
									//delete old messages that 
$sql = "DELETE FROM mail WHERE msgto=? AND msgfrom=0 AND subject=? AND seen=0";
db_query_params($sql, array((int)$row2["acctid"], $subj));
									if (db_affected_rows()>0) $noemail = true; else $noemail = false;
									systemmail($row2['acctid'],"$subj","This message is generated as a result of one or more of the accounts having been a superuser account.  Log Follows:`n`n$alert",0,$noemail);
								}//end for
							}//end if($su)
						}//end if($c>=10)
					}//end while
				}else{
					
				}//end if (db_num_rows)
				redirect("index.php");
			}
		}
	}
}else if ($HTTP_GET_VARS['op']=="logout"){
	if ($session['user']['loggedin']){
	debuglog("logged out ");
		$sql = "UPDATE accounts SET loggedin=0 WHERE acctid = ?";
		db_query_params($sql, array((int)$session["user"]["acctid"]));
	}
	$session=array();
	redirect("index.php");
}
// If you enter an empty username, don't just say oops.. do something useful.
$session=array();
$session['message']="`4Fehler: Die Login-Daten waren fehlerhaft.`0";
redirect("index.php");
?>
