<?php
 /*
 * Authors
 * -------
 * Aldo Boccacci <zorba_@tin.it> at http://www.aldoboccacci.it
 * Lorenzo Caporale <piercolone@gmail.com> at http://www.fn-look.org/team/~piercolone/
 *
 * License
 * -------
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or any
 * later version.
 *
 * Changelog
 * ---------
 * Marco Segato <segatom@users.sourceforge.net> | 20070210: Code revision and inclusion in Flatnuke 2.5.9
 * Marco Segato <segatom@users.sourceforge.net> | 20070325: French language added
 * Marco Segato <segatom@users.sourceforge.net> | 20090324: Redirect to the self page (idea from Jack Overfull <jack@jackoverfull.com>)
 *
 */

// prevent direct access to this file
if (preg_match("/Language.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../../index.php");
    die();
}

/**
 * -- Start CONFIGURATION --
 * If the variable $showtext is set to '1', it will be printed the text "Select your language";
 * if the variable $showtext is set to '0', only flag images will be printed.
 */
	$showtext = 1;
/** -- End CONFIGURATION -- */

// rebuild current URL with all GET values
$newurl = "index.php?";
if (_FN_MOD!=""){
	$newurl = $newurl."mod="._FN_MOD;
	if (_FN_FILE!="")
		$newurl=$newurl."&amp;file="._FN_FILE;

}
// $newurl.="&amp;";
if(sizeof($_GET)>0) {
	$value = "";
	foreach($_GET as $key => $value) {
		$value = getparam($key, PAR_GET, SAN_FLAT);
		if (ctype_alnum("$key") and ctype_alnum("$value")
			and $key!="mod" and $key!="file")
		$newurl .= "&amp;$key=".$value;
	}
}

// set chosen language
if (isset($_GET['userlang'])) {
	$userlang = getparam("userlang", PAR_GET, SAN_FLAT);
	if(!is_alphanumeric($userlang)) {
		return;
	}
	if(file_exists("languages/$userlang.php")) {
		setcookie("userlang", $userlang, time()+31536000); // --> 1 year
	}
	$newurl = preg_replace("/(userlang=([a-z][a-z]))/i", "", $newurl);
	$newurl = preg_replace("/\?\&$/", "", $newurl);
	$newurl = str_replace("&&", "", $newurl);
	$newurl = str_replace("&amp;", "&", $newurl);
	Header("Location: $newurl");
}

// print text if expected from configuration
if($showtext == 1){
	echo "<div style=\"padding:10px 0 5px 0; text-align:center; font-weight:bold;\">";
	$preflang = substr(preflang(),0,2);
	switch($preflang) {
		case("de"):
			echo "Deine Sprache vorwählen:";
		break;
		case("en"):
			echo "Choose your language:";
		break;
		case("es"):
			echo "Selecciona tu lengua:";
		break;
		case("fr"):
			echo "Choisir votre langue";
		break;
		case("it"):
			echo "Scegli la lingua:";
		break;
		case("pt"):
			echo "Seleciona tua língua:";
		break;
		default:
			echo _LANG.":";
	}
	echo "</div>";
}

// print flag images
?><div style="text-align:center;">
<a href="<?php echo $newurl?>&amp;userlang=de" title="deutsch"><img alt="deutsch" src="images/languages/de.png" style="border:1px solid #000000;padding:2px;" /></a>
<a href="<?php echo $newurl?>&amp;userlang=en" title="english"><img alt="english" src="images/languages/en.png" style="border:1px solid #000000;padding:2px;" /></a>
<a href="<?php echo $newurl?>&amp;userlang=es" title="español"><img alt="español" src="images/languages/es.png" style="border:1px solid #000000;padding:2px;" /></a>
<a href="<?php echo $newurl?>&amp;userlang=fr" title="français"><img alt="français" src="images/languages/fr.png" style="border:1px solid #000000;padding:2px;" /></a>
<a href="<?php echo $newurl?>&amp;userlang=it" title="italiano"><img alt="italiano" src="images/languages/it.png" style="border:1px solid #000000;padding:2px;" /></a>
<a href="<?php echo $newurl?>&amp;userlang=pt" title="português"><img alt="português" src="images/languages/pt.png" style="border:1px solid #000000;padding:2px;" /></a>
</div>

<?php
/**
 * Return prefered language(s) of the browser in use
 *
 * This function gets the prefered language(s) of the client browser
 * visiting the page, and returns it/them into an array.
 *
 * @author Inspired to a comment found at {@link http://www.php.net/manual/en/reserved.variables.php }
 * @return array $lang_index Default language(s) of the browser
 */
function preflang() {
	$lang_list = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en';
	$languages = preg_split("/,/", $lang_list);
	$lang_q = Array();
	foreach( $languages as $aLang ) {
		$lang_array = preg_split("/;q=/", trim( $aLang ) );
		$lang = trim( $lang_array[0] );
		if( !isset( $lang_array[1] ) )
			$q = 1;
		else
			$q = trim($lang_array[1]);
		$lang_q["$lang"] = (float)$q;
	}
	arsort($lang_q);
	//extra code for making the languages key indexed
	$i = 0;
	$lang_index = Array();
	foreach($lang_q as $lang => $q) {
		$lang_index[$i] = $lang;
		$i++;
	}
	return $lang_index[0];
}

?>
