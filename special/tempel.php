<?php

// 22062004

/* ******************* 
Tempel der Götter 
Written by Romulus von Grauhaar 
    Visit http://www.scheibenwelt-logd.de.vu

Das Special fügt einen Tempel im Wald hinzu, bei dem Spieler eine beliebige Menge Gold spenden können. 
Ab einer gewissen Menge Gold passiert ein zufälliges Ereignis, je nach dem Gott, dem der Tempel geweiht ist.
Sowohl der benötigte Goldbetrag als auch die Namen der Gottheiten lassen sich ganz einfach am Skriptanfang
vom Admin festlegen.
Der Sinn dieses Specials ist, dass viele Spieler kurz vor ihrem Drachenkill eine Menge Gold übrig haben, was sie 
beim Drachenkill verlieren würden. Hier können sie mit etwas Glück brauchbare Dinge dafür bekommen, allerdings
kann der Schuss auch nach hinten losgehen (wobei die negativen Auswirkungen nicht dauerhaft sind, immerhin
hat der Spieler ne Menge Gold geopfert)

Um das Special zu benutzen, muss folgender SQL-Befehl ausgeführt werden: 

ALTER TABLE 'accounts' ADD 'tempelgold' INT( 30 ) DEFAULT '0' NOT NULL;

Optional kann in der user.php an geeigneter Stelle eingefügt werden:
	"tempelgold"=>"Gold im Tempel gespendet,int",
so dass der Admin die Datenbank-Variable im User-Editor bearbeiten kann.

!!! Changes by anpera !!!
- no field must be added into database
- no changes to user.php needed
- all donations go to settings table and all players must help together

******************* */  


// Die nun folgenden Variablen konfigurieren das Special. Die Variable $spendenbetrag steht für den
// Betrag an Gold, den ein Spieler gespendet haben muss, damit die Götter reagieren.
// Die einzelnen Götternamen sind frei wählbar.

$spendenbetrag = "10000";
$gott_gem = "Aphrodite";
$gott_defense = "Om";
$gott_hp = "dem Schicksal";
$gott_attack = "Mephistos";
$gott_charm = "Aphrodite";
$gott_fight = "Fexez";
$gott_kill = "der Gott der Waldkreaturen";
$gott_hurt = "der Gott der Waldkreaturen";
$gott_spec="Foilwench";


// $session[user][specialinc]="tempel.php"; 

if ($HTTP_GET_VARS['op']=="verlassen"){    
	output("`@Du läßt den alten, baufälligen Tempel hinter dir.");
	$session['user']['specialinc']="";
	//addnav("Zurück in die weite Welt","forest.php");	
}else if ($HTTP_GET_VARS['op']=="spenden"){    
	$session['user']['specialinc']="tempel.php"; 
	addnav("50 Gold spenden","forest.php?op=spendeneingang&betrag=50");
	addnav("100 Gold spenden","forest.php?op=spendeneingang&betrag=100");
	addnav("500 Gold spenden","forest.php?op=spendeneingang&betrag=500");
	addnav("1000 Gold spenden","forest.php?op=spendeneingang&betrag=1000");
	addnav("5000 Gold spenden","forest.php?op=spendeneingang&betrag=5000");
	addnav("Doch nichts spenden","forest.php?op=verlassen");
	output("Wieviele Goldstücke spendest du für die Renovierung des Tempels?",true);
}else if ($HTTP_GET_VARS['op']=="spendeneingang"){
	if ($HTTP_GET_VARS['betrag']>$session['user']['gold'])	{
		output("`@Tja, das hast du dir wohl so gedacht. Soviel Gold hast du gar nicht dabei. Wenn das mal hoffentlich nicht die Götter bemerkt haben.`n`n");
		output("Du verlässt den Tempel, bevor die Götter auf deinen kleinen Verzähler aufmerksam werden.");
		//addnav("Zurück in die weite Welt","forest.php");
	}else{
		$betrag=$HTTP_GET_VARS['betrag'];
		$drin=getsetting("tempelgold",0)+$betrag;
		output("`^`bDu spendest `&$betrag`^ Gold für die Tempelrenovierung. ");
		//debuglog("spendete $betrag Gold für die Tempelrenovierung");
		savesetting("tempelgold",$drin);
		$session['user']['gold']-=$betrag;
		if ($betrag>100) $session['user']['reputation']+=3;
		output("Die Gottheit, der der Tempel geweiht ist, hat deine Spende registriert.`b");
		addnav("Den Tempel verlassen","forest.php");
		output("`nAm Geräusch, das deine Goldstücke beim Einwerfen verursachen, vermutest du, dass bisher etwa ".($drin+round($drin/100*e_rand(-3,3)))." gespendet worden sein muss. ");
		if($drin >= $spendenbetrag) {
			output("`@Nachdem du die Goldmünzen in den Opfer stock geworfen hast, ertönt plötzlich ein Donnern. Anscheinend hat die Gottheit, der der Tempel geweiht ist, deine großzügigen Gaben bemerkt.`n`n");
			output("`@Vor dir erscheint die Gottheit, der der Tempel geweiht ist, nämlich `^");
			savesetting("tempelgold","0");
			switch(e_rand(1,7)) {
          				case 1:
              				output("$gott_gem`@. Das Glück scheint dir hold zu sein, denn $gott_gem übberreicht dir `\$4 Edelsteine`@!");
				$session['user']['gems']+=4;
				addnews("`%".$session['user']['name']."`7 wurde in einem Tempel von $gott_gem mit großem steinernen Reichtum beschenkt.");
                			break;
          				case 2:
              				output("$gott_defense`@. Mit göttlicher Kraft wächst deine `\$Verteidigungsstärke`@, als Dank für deine Spenden!");
				$session['user']['defence']+=2;
				addnews("`%".$session['user']['name']."`7s Haut wurde in einem Tempel von $gott_defense widerstandsfähiger gemacht.");
                			break;
          				case 3:
              				output("`^$gott_attack`@. Mit göttlicher Kraft wächst deine `\$Angriffssstärke`@, als Dank für deine Spenden!");
				$session['user']['attack']+=2;
				addnews("`%".$session['user']['name']."s`7 Muskeln wurden in einem Tempel von $gott_attack gestärkt.");
                			break;
          				case 4:
              				output("$gott_hp`@. Dein Schicksal, zusätzliche `\$Trefferpunkte`@ dauerhaft zu besitzen, erfüllt sich als Dank für deine Spenden!");
				$session['user']['maxhitpoints']+=2;
				addnews("`%".$session['user']['name']."`7 wurde in einem Tempel von $gott_hp mit erhöhter Lebenskraft versehen.");
                			break;
          				case 5:
              				output("$gott_fight`@. Mit göttlicher Kraft darfst du am heutigen Tag `\$3 Waldkämpfe`@ mehr bestreiten, als Dank für deine Spenden!");
				$session['user']['turns']+=3;
				addnews("`%".$session['user']['name']."`7 wurde in einem Tempel von $gott_fight mit neuen Kampfrunden gesegnet.");
                			break;
          				case 6:
              				output("$gott_charm`@. Mit göttlicher Kraft siehst du wesentlich besser aus. Du erhälst `\$3 Charmepunkte`@ als Dank für deine Spenden!");
				$session['user']['charm']+=3;
				addnews("`%".$session['user']['name']."`7 wurde in einem Tempel von $gott_charm zu einem besser aussehenden ".($races[$session['user']['race']])." `7gemacht.");
                			break;
          				case 7:
              				output("$gott_hurt`@. Was hast du dir nur dabei gedacht, diese Gottheit zu beschwören, die für ihre Ausraster und Schlägereien berühmt ist? Nach einem harten `\$Schlag`@ erwachst du aus einer Ohnmacht und hast fast alle Lebenspunkte verloren.");
				$session['user']['hitpoints']=1;
				addnews("`%".$session['user']['name']."`7 wurde in einem Tempel von $gott_hurt schwer verletzt. Man sollte halt nicht mit gefährlichen Göttern herumspielen.");
                			break;
          				case 8:
              				output("$gott_spec`@.`n");
				increment_specialty();
				addnews("`%".$session['user']['name']."`7 wurde in einem Tempel von $gott_spec in seiner Fertigkeit unterrichtet.");
                			break;
			} //switch
		} // benötigten betrag erreicht?
	}
	$session['user']['specialinc']=""; 
}else{
	output("`@Auf deiner Reise kommst du plötzlich an einem Tempel vorbei. Ein imposanter, aber schon leicht verfallener Bau mit Säulen vor dem Eingang. Du betrittst das heilige Haus und siehst, dass der Tempel eine Renovierung dringend notwendig hätte. Das einzige, was noch intakt zu sein scheint, ist der Opferstock, über dem ein neu wirkendes Schild prangt: `n`&\"Sehr geehrter Besucher, unser Tempel ist leider dem Verfall preisgegeben, bitte spende etwas für die Renovierung. Die Götter mögen es dir danken.`nGez. der Hohepriester.\"`@"); 
	output("`n`nWas wirst du tun?"); 
	addnav("Spende etwas","forest.php?op=spenden");
	addnav("Tempel verlassen","forest.php?op=verlassen");
	$session['user']['specialinc']="tempel.php"; 
}
//page_footer(); 
?>