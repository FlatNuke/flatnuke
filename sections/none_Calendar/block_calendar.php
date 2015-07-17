<?php

/**
 * FLATCALENDAR
 *
 * Calendar block for FlatNuke (http://www.flatnuke.org)
 *
 * @version 20130216
 * @license GNU General Public License 2
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Giovanni Piller Cottrer <giovanni.piller@gmail.com>
 * @author Aldo Boccacci <zorba_@tin.it> | 20110124: Changes
 *
 */

/* ============== START SCRIPT CONFIGURATION ============== */
	$basesez = "none_Calendar";
	$baseurl = "sections/$basesez/block_calendar.php";
/* =============== END SCRIPT CONFIGURATION =============== */

if (preg_match("/calendar/i",$_SERVER['PHP_SELF'])) {
	chdir("../../");
	include_once("functions.php");
	include_once("config.php");
	include_once("languages/$lang.php");
	include_once("flatnews/include/news_functions.php");
	// set again charset: using ajax, it could be rewritten by the web server
	@header("Content-Type: text/html; charset="._CHARSET."");
}

//Time zone
if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	@date_default_timezone_set(date_default_timezone_get());

if (!defined("_FN_MOD"))
	create_fn_constants();

// arrays from Flatnuke containing months and week days
global $mesi, $giorni;
// server time-shift taken from Flatnuke settings
global $fuso_orario;

// security conversions; if no date is given, it takes current time as default
$aa   = trim(getparam("aa", PAR_GET, SAN_NULL));
$mm   = trim(getparam("mm", PAR_GET, SAN_NULL));

if (!ctype_digit("$aa"))
	$aa=date("Y");
if (!ctype_digit("$mm"))
	$mm=date("n");

if ( $aa=="" OR !is_numeric($aa) OR $aa<(date("Y")-1000) OR $aa>(date("Y")+1000) ) {
	$aa = date("Y");
}
if ( $mm=="" OR !is_numeric($mm) OR $mm<1 OR $mm>12 ) {
	$mm = date("n");
}

// array containing all the news of the month
$arraynews = array();

//se sono amministratore o moderatore delle news le posso vedere tutte,
//altrimenti soltanto quelle visibili
if (is_admin() or is_news_moderator())
	$newslist = load_news_list("none_News",TRUE);
else $newslist = load_news_list("none_News",FALSE);
#print_r($newslist); //-> TEST

for ($newscount=0;$newscount<count($newslist);$newscount++){
	$annonews = date("Y", intval($newslist[$newscount]));
	$mesenews = date("n", intval($newslist[$newscount]));
	if ($annonews==$aa AND $mesenews==$mm) {
		array_push($arraynews, $newslist[$newscount]);
	}
}

if(count($arraynews>0))
	rsort($arraynews);
#print_r($arraynews); //-> TEST

//---> START BUILDING CALENDAR
echo "\n<div id='calendar' class='calendar'>\n";

// print current date and time
echo "\n<div style='padding-bottom:0.5em; text-align:left'>\n";
echo "<b>"._DATA.":</b> ".date("d/m/Y",time()+(3600*$fuso_orario))."<br>";
echo "<b>"._ORA.":</b> ".date("H:i",time()+(3600*$fuso_orario));
echo "\n</div>";

// print header navigation; something like: « Month Year »
$anno = $aa;
$mese = $mm;
if ($mese==1) { // first month of the year
	$back = "aa=".($anno-1)."&amp;mm=12";
	$backtitle = $mesi[11]." ".($aa-1);
} else {
	$back = "aa=".$anno."&amp;mm=".($mese-1);
	$backtitle = $mesi[$mm-2]." ".$aa;
}
if ($mese==12) { // last month of the year
	$next = "aa=".($anno+1)."&amp;mm=1";
	$nexttitle = $mesi[0]." ".($aa+1);
} else {
	$next = "aa=".$anno."&amp;mm=".($mese+1);
	$nexttitle = $mesi[$mm]." ".($aa);
}

echo "\n<div class=\"centeredDiv\">\n";
echo "<a href=\"javascript:jQueryFNcall('$baseurl?$back','GET','calendar');\" title='$backtitle'>&laquo;</a>&nbsp;"; // go back 1 month
echo "<b>".$mesi[$mm-1]."&nbsp;$aa</b>&nbsp;"; // current month
echo "<a href=\"javascript:jQueryFNcall('$baseurl?$next','GET','calendar');\" title='$nexttitle'>&raquo;</a>&nbsp;"; // go next month
echo "\n</div>";

// print list of days-of-week's names
echo "\n<div class='row'>";
for ($i=1; $i<7; $i++) {
   echo "\n\t<span class='heading'>".substr($giorni[$i],0,2)."</span>";	// from monday to saturday
}
echo "\n\t<span class='heading'>".substr($giorni[0],0,2)."</span>";		// sunday
echo "\n</div>";

// print empty days
$primo = mktime(0,0,0,$mm,1,$aa); // first day of the month
$delta = date("w",$primo)-1;
#echo $delta; //-> TEST
if($delta == -1) {	// sunday as first day of the month
	$delta = 6;
}
if($delta!=0) {		// start a new week
	echo "\n<div class='row'>";
}
for ($i=0;$i<$delta;$i++) {
	echo "\n\t<span class='blankDay'>&nbsp;</span>";
}

// print all valid days
for ($i=1;$i<=date("t",$primo);$i++) {
	if (($i+$delta)%7==1) {		// first day of the week
		echo "<div class='row'>";
	}
	$thereisanews = is_there_a_news(trim($i), $mm, $aa, $arraynews);	// check if there's a news in this day
    if (($i==date("d"))&&($mm==date("n"))&&($aa==date("Y"))) {
		if ($thereisanews!="") {
			echo "\n\t<a href=\"index.php?mod=$basesez".$thereisanews."</a>";
		} else echo "\n\t<span class='currDay' title='today'>$i</span>";	// today
    } else {
		if ($thereisanews!="") {
			echo "\n\t<a href=\"index.php?mod=$basesez".$thereisanews."</a>";
		} else echo "\n\t<span class='day'>$i</span>";
	}
	// particular cases: 1 is first day of week OR 28/29/30/31 is the last day of week
	if (($i+$delta)%7==0 OR $i==date("t",$primo)) {
		echo "\n</div>\n";
	}
}

// print options boxes to change month/year
echo "\n<div class=\"centeredDiv\">";
echo "\n<form method='get' action='".str_replace("&","&amp;","index.php")."' name='calendar_module'>";
echo "\n<select name='mm' onchange=\"javascript:jQueryFNcall('$baseurl?aa='+aa.options[aa.selectedIndex].value+'&amp;mm='+this.options[this.selectedIndex].value,'GET','calendar');\">";
for ($i=1; $i<=12; $i++) {
    if ($i==$mm) {
		echo "\n\t<option value='$i' selected='selected'>".$mesi[$i-1]."</option>";
	} else echo "\n\t<option value='$i'>".$mesi[$i-1]."</option>";
}
echo "\n</select>";
echo "\n<select name='aa' onchange=\"javascript:jQueryFNcall('$baseurl?aa='+this.options[this.selectedIndex].value+'&amp;mm='+mm.options[mm.selectedIndex].value,'GET','calendar');\">";
for ($i=date("Y")-10; $i<=date("Y")+5; $i++) {
	if ($i==$aa) {
		echo "\n\t<option value='$i' selected='selected'>".$i."</option>";
	} else echo "\n\t<option value='$i'>".$i."</option>";
}
echo "\n</select>";
echo "\n<noscript><br><br><input type='submit' value='Ok' /></noscript>";
echo "\n</form>";
echo "\n</div>\n";

// END BUILDING CALENDAR <----
echo "\n</div>\n";


/*
 * Check if there's a news in a particular day
 *
 * The function verifies if it can find a news from the
 * array $arraynews that is dated $dd/$mm/$yy.
 *
 * @param int $dd Day to check
 * @param int $mm Month to check
 * @param int $yy Year to check
 * @param array $arraynews Contains the list of news for a particular month
 *
 * @return string The string that will be printed (empty or the argument of the link)
 */
function is_there_a_news($dd, $mm, $yy, $arraynews=array()) {
	$dd = getparam($dd,PAR_NULL,SAN_NULL);
	$mm = getparam($mm,PAR_NULL,SAN_NULL);
	$yy = getparam($yy,PAR_NULL,SAN_NULL);
	if (!ctype_digit("$dd")) return FALSE;
	if (!ctype_digit("$mm")) return FALSE;
	if (!ctype_digit("$yy")) return FALSE;
	global $basesez;
	global $fuso_orario;

	for ($i=0; $i<count($arraynews); $i++){
		$tmp    = $arraynews[$i];
		$giorno = date("j", $tmp+(3600*$fuso_orario));
		$mese   = date("n", $tmp+(3600*$fuso_orario));
		$anno   = date("Y", $tmp+(3600*$fuso_orario));
		#echo "$dd-$mm-$yy|$giorno/$mese/$anno<br>";	//-> TEST
		if($mese==$mm AND $anno==$yy AND $giorno==$dd) {
			return "&amp;aa=$anno&amp;mm=$mese&amp;dd=$giorno\" class=\"day\" title=\"News ".$giorno."/".$mese."/".$anno."\"><b>".$dd."</b>";
		}
	}
}

?>
