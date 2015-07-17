<?php

/*
--------------------------------------------------------------------
FLATCALENDAR - Calendar block for FlatNuke (http://www.flatnuke.org)
--------------------------------------------------------------------

Author:		Marco Segato
Website:	http://marcosegato.altervista.org/
email:		segatom (at) users.sourceforge.net

Author:		Giovanni Piller Cottrer
Website:	http://gigasoft.altervista.org
email:		giovanni.piller (at) gmail.com

Version:	0.11 - 20110124 (changes by Aldo Boccacci)

License:	GNU General Public License 2

*/


// do not let users directly access this file
if (preg_match("/section.php/i",$_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}
//Time zone
if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	@date_default_timezone_set(date_default_timezone_get());

if (!defined("_FN_MOD"))
	create_fn_constants();

// security convertions
// $req = getparam("REQUEST_URI", PAR_SERVER, SAN_NULL);
// $url = basename($req);
$aa  = trim(getparam("aa", PAR_GET, SAN_FLAT));
$mm  = trim(getparam("mm", PAR_GET, SAN_FLAT));
$dd  = trim(getparam("dd", PAR_GET, SAN_FLAT));

if (!ctype_digit("$aa"))
	$aa=date("Y");
if (!ctype_digit("$mm"))
	$mm=date("m");
if (!ctype_digit("$dd"))
	$dd=date("d");

// Flanuke configuration
global $mesi, $fuso_orario;

// variables definition
$okmese = array(0,0,0,0,0,0,0,0,0,0,0,0);
$okanno = array();


if(($aa!="" AND is_numeric($aa) AND $aa>(date("Y")-1000) AND $aa<(date("Y")+1000)) AND ($mm!="" AND is_numeric($mm) AND $mm>0 AND $mm<13) AND ($dd!="" AND is_numeric($dd) AND $dd>0 AND $dd<32)) {
	$my_modlist = list_news("none_News/",FALSE,FALSE);


/*	if(count($my_modlist)>0) { // ordinamento inutile, tanto il successivo ciclo for considera le news una per una. rivedere il ciclo, c'e' spazio di miglioramento per le prestazioni.
		krsort($my_modlist);
	}*/
	#print_r($my_modlist); //-> TEST
	for ($i=0; $i < sizeof($my_modlist); $i++) {
		$tmp = str_replace(".xml","",$my_modlist[$i]);
		$giorno = date("d",$tmp+(3600*$fuso_orario));
		$mese   = date("m",$tmp+(3600*$fuso_orario));
		$anno   = date("Y",$tmp+(3600*$fuso_orario));

		if($anno==$aa AND $mese==$mm AND $giorno==$dd) {
			$data = load_news_header("none_News/",$my_modlist[$i]);
			$title  = $data['title'];
			$reads  = $data['reads'];
			$id     = str_replace(".xml","",$my_modlist[$i]);
			echo "\n"._ICONREAD."&nbsp;<a href='index.php?mod=none_News&amp;action=viewnews&amp;news=$id' title='"._FLEGGI." news: $title'>$title</a>&nbsp;";
			echo "(".date("d/m/Y - H:i",$id+(3600*$fuso_orario)).") "._LETTO." $reads "._VOLTE."<br>";
		}
	}
} else echo _NORESULT;

?>
