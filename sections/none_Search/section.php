<?php

/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003-2004 by Simone Vellei                             */
/* http://flatnuke.sourceforge.net                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/


if (preg_match("/section.php/i",$_SERVER['PHP_SELF'])) {
	chdir("../../");
	include_once("functions.php");
	include_once("config.php");
	include_once("languages/$lang.php");
	// set again charset: using ajax, it could be rewritten by the web server
	@header("Content-Type: text/html; charset="._CHARSET."");
}

//configurazione
//la cartella contenente i plugins
$search_plugins_dir= "include/search/";
//la lunghezza massima della stringa di ricerca
$search_string_limit = "35";

$GLOBALS['search_plugins_dir'] =$search_plugins_dir;
$GLOBALS['search_string_limit'] =$search_string_limit;

// Security convertions
$find = getparam("find",PAR_POST,SAN_FLAT);
$where = getparam("where",PAR_POST,SAN_FLAT);
$findget = getparam("find",PAR_GET,SAN_FLAT);
$whereget = getparam("where",PAR_GET,SAN_FLAT);
$tags_string = getparam("tags",PAR_GET,SAN_FLAT);
$category = getparam("category",PAR_GET,SAN_FLAT);

if (strlen($find)>$search_string_limit){
	echo "<b>The search string is too long!</b>";
	fnlog("Search","search string \"$find\" too long");
	return;
}
if (strlen($findget)>$search_string_limit){
	echo "<b>The search string is too long!</b>";
	fnlog("Search","search string \"$findget\" too long");
	return;
}

if (trim($findget)!=""){
	if ($whereget=="news"){
		include("include/search/01_News.php");
		return;
	}
}

if (trim($find)=="" and $category=="" and $tags_string=="") {
	view_search_interface();
	return;
}
else if (preg_match("/news/i",$whereget)){
	if ($tags_string!="" or $category!=""){
		include_once($search_plugins_dir."01_News.php");
	}

}
else if ($where=="allsite"){

	$plugins = glob("$search_plugins_dir/*.php");
	if (!$plugins) $plugins = array(); // glob may returns boolean false instead of an empty array on some systems

	foreach ($plugins as $plugin){
		if (preg_match("/^none_/i",basename($plugin))) continue;
		include_once($plugin);
	}
}
else if (trim($where)!=""){
	if (file_exists("$search_plugins_dir/$where.php")){

		include_once("$search_plugins_dir/$where.php");
		return;
	}
	else {
		view_search_interface();
	}
}
else {
	view_search_interface();
}


/**
 * Mostra l'interfaccia utente per compiere ricerche
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function view_search_interface(){
	?><br><br>
	<script type="text/javascript">
	function validatesearch()
		{
			if(document.getElementById('findsect').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CERCA?>');
					document.getElementById('findsect').focus();
					document.getElementById('findsect').value='';
					return false;
				}
			else return true;
		}
	</script>
	<div style="text-align:center">
	<form id="search_section" action="javascript:jQueryFNcall('sections/none_Search/section.php','POST','search_results','search_section');" onsubmit="return validatesearch()">
	<input type="hidden" name="mod" value="none_Search" />
	<label for="findsect" ><?php echo _CERCA;?>:</label>
	<input type="text" name="find" size="16" id="findsect" /><br><br>
	<label for="wheresect"><?php echo _CERCASTR; ?></label>
	<select name="where" id="wheresect">
	<option value="allsite" selected="selected"><?php echo _ALLSITE; ?></option>
	<?php
	global $search_plugins_dir;
	$plugin="";
	$pathstring="";

	$plugins = glob("$search_plugins_dir/*.php");
	if (!$plugins) $plugins = array(); // glob may returns boolean false instead of an empty array on some systems

	foreach ($plugins as $plugin){
		$plugin_name ="";
		$plugin_name = preg_replace("/\.php$/i","",basename($plugin));
		if (preg_match("/^none_/i",$plugin_name)) continue;
		echo "<option value=\"$plugin_name\">".preg_replace("/^[0-9]*_/i","",$plugin_name)."</option>\n";
	}
	//gestisci plugins
	?>
	</select><br><br><?php echo $pathstring; ?>
	<input type="radio" value="AND" id="AND" name="method" checked /><label for="AND">AND</label>
	<input type="radio" value="OR" id="OR" name="method" /><label for="OR">OR</label>
	<br><br>
	<input type="submit" value="<?php echo _CERCA?>" />
	</form>
	</div>
	<div id="search_results"></div><?php
}


/**
 * Cerca $pattern nella stringa $string secondo il metodo $method.
 * Questa funzione e' pensata per essere usata dai vari plugin
 * @param string $pattern la stringa da cercare
 * @param string $string la stringa in cui cercare
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return TRUE se pattern viene trovato, FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function fn_search_string($pattern, $string,$method="AND"){
	$pattern=getparam($pattern,PAR_NULL,SAN_FLAT);
	$string=getparam($string,PAR_NULL,SAN_FLAT);
	if (!preg_match("/AND|OR/i",$method)) $method="AND";
	$results = array();
	$tokens = explode(" ", $pattern);
	foreach ($tokens as $token){
		if (trim($token)=="") continue;
		if (preg_match("/".quotemeta($token)."/i",$string)) $results[]="true";
		else $results[]="false";
	}

	if ($method=="AND"){
		if (in_array("false",$results)) return FALSE;
		else return TRUE;
	}
	else if ($method=="OR"){
		if (in_array("true",$results)) return TRUE;
		else return FALSE;
	}
}

?>
