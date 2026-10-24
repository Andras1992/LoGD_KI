<?php 

// 22062004

// translation found at http://logd.ist-hier.de
// small ... BIG ... modifications by anpera

if (!isset($session)) exit();

if ($HTTP_GET_VARS['op']=="do"){
	$session['user']['reputation']--;
	if ($session['user']['sex']>0){
		output("`%Du folgst dem Gott in die Büsche. Wenige Minuten später, sind "); 
		output("nur noch leise Geräusche zu hören. "); 
		output("Als du erwachst, stellst du fest dass ...`n`n`^"); 
		switch(e_rand(1,10)){ 
			case 1: 
			case 2: 
			output("er verschwunden ist ohne sich zu verabschieden. Aufgrund deiner Erschöpfung, verlierst du einen Waldkampf."); 
			$session['user']['turns']-=1; 
			$session['user']['experience']+=150; 
			break; 
			case 3: 
			case 4: 
			output("er verschwunden ist ohne sich zu verabschieden. Du fühlst dich gut und könntest jetzt einen Kampf vertragen."); 
			$session['user']['turns']+=1; 
			$session['user']['experience']+=150; 
			break; 
			case 5: 
			output("er dir einen Beutel mit Edelsteinen da gelassen hat. Du fühlst dich benutzt, akzeptierst aber die Bezahlung!"); 
			$session['user']['gems']+=3; 
			$session['user']['experience']+=150; 
			break; 
			case 6: 
			output("er einen goldenen Apfel da gelassen hat. Als du in den Apfel beißt, fühlst du zusätzliche Lebenskraft in dir!"); 
			$session['user']['maxhitpoints']+=1; 
			$session['user']['experience']+=150; 
			break; 
			case 7: 
			case 8: 
			case 9: 
			case 10: 
			increment_specialty(); 
			break; 
		} 
		addnews($session['user']['name']." hatte einen Quicky mit einem Gott.");
		$session['user']['specialinc']="";
		//addnav("Zurück in den Wald","forest.php");
	}else{ 
		output("`%Du folgst der Göttin in die Büsche. Wenige Minuten später, sind "); 
		output("nur noch leise Geräusche zu hören. "); 
		output("Als du erwachst, stellst du fest dass ...`n`n`^"); 
		switch(e_rand(1,10)){ 
			case 1: 
			case 2: 
			output("sie verschwunden ist ohne sich zu verabschieden. Aufgrund deiner Erschöpfung, verlierst du einen Waldkampf."); 
			$session['user']['turns']-=1; 
			$session['user']['experience']+=150; 
			break; 
			case 3: 
			case 4: 
			output("sie verschwunden ist ohne sich zu verabschieden. Du fühlst dich gut und könntest jetzt einen Kampf vertragen."); 
			$session['user']['turns']+=1; 
			$session['user']['experience']+=150; 
			break; 
			case 5: 
			output("sie dir einen Beutel mit Edelsteinen da gelassen hat. Du fühlst dich benutzt, akzeptierst aber die Bezahlung!"); 
			$session['user']['gems']+=3; 
			$session['user']['experience']+=150; 
			break; 
			case 6: 
			output("sie einen goldenen Apfel da gelassen hat. Als du in den Apfel beisst, fühlst du zusätzliche Lebenskraft in dir!"); 
			$session['user']['maxhitpoints']+=1; 
			$session['user']['experience']+=150; 
			break; 
			case 7: 
			case 8: 
			case 9: 
			case 10: 
			increment_specialty(); 
			break; 
		} 
		addnews($session['user']['name']." hatte einen Quicky mit einer Göttin.");
		$session['user']['specialinc']="";
		//addnav("Zurück in den Wald","forest.php");
	}
}else if ($HTTP_GET_VARS['op']=="dont"){
	output("In Gedanken an ".($session['user']['sex']?"deinen Geliebten":"deine Geliebte")." rennst du weg. Die Gottheit lacht auf und verschwindet in den Schatten des Waldes.");
	$session['user']['specialinc']="";
	$session['user']['reputation']+=2;
	//addnav("Zurück in den Wald","forest.php");
}else{
	if ($session['user']['sex']>0){
		output("`%Als du durch den Wald wanderst, spricht dich ein stattlicher Mann an. \"`^Ich bin der Gott Fexez. Ich habe von dir gehört. Komm mit mir.`%\" Was tust du ?"); 
	}else{
		output("`%Als du durch den Wald wanderst, erscheint Dir eine wunderschöne Frau. \"`^Ich bin die Göttin Aphrodite. Ich habe sogar in Athen von deiner Manneskraft gehört. Ich will es ausprobieren. Komm mit mir.`%\" Was tust du?"); 
	}
		addnav("Gehe mit","forest.php?op=do"); 
		addnav("Laufe weg","forest.php?op=dont"); 
		$session['user']['specialinc']="aphrodite.php"; 
}
?>