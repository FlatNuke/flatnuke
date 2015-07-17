<?php
/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003-2006 by Simone Vellei                             */
/* http://www.flatnuke.org                                              */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

// include le librerie di Flatnuke necessarie
include ("config.php");
include ("functions.php");
include ("header.php");
include_once ("themes/$theme/theme.php");

if (!defined("_FN_MOD"))
	create_fn_constants();

//controllo che l'ip di provenienza non sia vietato
$ip = strip_tags(getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL));
if (is_blocked_ip($ip)){
	fnlog("Security", "$ip||Access denied to IP address $ip.");
	// have fun ;)
	header("Location: http://www.spam.com/");
	exit;
}

// deny remote cross access to this file
//if( !eregi( $_SERVER['HTTP_HOST'],$_SERVER['HTTP_REFERER'] ) ){
if( !preg_match("/".$_SERVER['HTTP_HOST']."/", $_SERVER['HTTP_REFERER'] ) ){
	//fnlog("Security", "$ip||Blocked unauthorized remote access to verify.php. URL=".$_SERVER['HTTP_REFERER']);	// uncomment if interested on lamers' access
	// have fun ;)
	echo "<b>Error</b>: blocked unauthorized remote access to verify.php";
// 	header("Location: http://en.wikipedia.org/wiki/Lamer");
	exit;
}

// if no active session we start a new one + check session id
if (session_id() == "") session_start();
if (isset($_SESSION['fn_session']) AND $_SESSION['fn_session']!= session_id() ) {
	unset($_SESSION['fn_session']);
	//fnlog("Security", "$ip||Blocked, tried to breaks session rules. URL=".$_SERVER['HTTP_REFERER']);	// uncomment if interested on lamers' access
	header("Location: index.php");
	exit;
} elseif(!isset($_SESSION['fn_session'])) {
	$_SESSION['fn_session'] = session_id();
	//fnlog("Security", "$ip||Blocked, no valid fn_session. URL=".$_SERVER['HTTP_REFERER']);	// uncomment if interested on lamers' access
	header("Location: index.php");
	exit;
}

// opzioni di sicurezza!
$myforum = get_username();
$req     = getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT);
if(strstr($req,"myforum=")) {
	// have fun ;)
	header("Location: http://en.wikipedia.org/wiki/Lamer");
	exit;
}


/*
 * Parametro $mod da POST
 */
$mod=getparam("mod",PAR_POST,SAN_FLAT);

switch($mod){

// Modifica del livello di una sezione
case "modlevel":
	$section = getparam("section", PAR_POST, SAN_FLAT);
	$level   = getparam("level", PAR_POST, SAN_FLAT);

	if(is_admin() and is_writeable("sections/$section")) {
		if($level!=-1) {
			fnwrite("sections/$section/level.php", $level, "w", array("nonull"));
			//ora proteggi il file section.php per evitare accessi diretti alla sezione senza passare da index.php
			protect_file(get_fn_dir("sections")."/$section/section.php");
			fnlog("Site maintenance", $ip."||".$myforum."||Level of the section $section modified to $level.");
		} else {
			unlink("sections/$section/level.php");
			fnlog("Site maintenance", $ip."||".$myforum."||Section $section with no more level restrictions.");
		}
	}
	?><script>window.location='index.php?mod=<?php echo $section?>';</script><?php
break;

// Modifica di un file
case "modcont":
	$from=getparam("from", PAR_POST, SAN_FLAT);
	$file=stripslashes(getparam("file", PAR_POST, SAN_FLAT));

	if(isset($_POST['body']))
		$body=$_POST['body'];
	else
		$body="";

	//gestisco l'attivazione di magic quotes
	if (get_magic_quotes_gpc()){
		$mybody=stripslashes($body);
	}
	else {
		$mybody= $body;
	}

	if(is_admin() and file_exists($file)) {
		if(stristr($file,"..")) {
			?><script>window.location='index.php';</script><?php
		}
		fnwrite($file, $mybody, "w", array());
		fnlog("Site maintenance", $ip."||".$myforum."||File ".$file." modified.");

		if (file_exists(dirname($file)."/level.php") or file_exists(dirname($file)."/view.php") or file_exists(dirname($file)."/edit.php")){
			if (basename($file)=="section.php")
			protect_file($file);
		}
	}
	?><script>window.location='<?php echo $from?>';</script><?php
break;

// Modifica di un file
case "usermodcont":
	$from = getparam("from", PAR_POST, SAN_FLAT);
	$file = stripslashes(getparam("file", PAR_POST, SAN_FLAT));
	$username = get_username();
	$sectmod = preg_replace("/sections\//","",dirname($file));
	$text = getparam("body",PAR_POST,SAN_FLAT);

	if (preg_match("/\<\?|\?\>/",$text)) fn_die("USERMODCONT","Error, user".strip_tags($username)." cannot add php code in the section ".strip_tags($sectmod)." addr: $ip",__FILE__,__LINE__);
	if (!check_path($file,"sections/","true")) fn_die("USERMODCONT","Error, file ".strip_tags($file)." is not valid! user: $username addr: $ip",__FILE__,__LINE__);

	//gestisco l'attivazione di magic quotes
	if (get_magic_quotes_gpc()){
		$text=stripslashes($text);
	}

	//strippo tutto quello che non e' consentito
	$text = fn_purge_html_string($text);


	if(user_can_edit_section($sectmod,$username) and user_can_view_section($sectmod,$username) and file_exists($file)) {
		fnwrite($file, $text, "w", array());
		fnlog("USERMODCONT", $ip."||$username||File ".$file." modified.");

		if (file_exists(dirname($file)."/level.php") or file_exists(dirname($file)."/view.php") or file_exists(dirname($file)."/edit.php")){
			if (basename($file)=="section.php")
			protect_file($file);
		}
	}
	?><script>window.location='<?php echo $from?>';</script><?php
break;

}

?></body>
</html>
