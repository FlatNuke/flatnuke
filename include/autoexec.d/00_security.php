<?php

/**
 * This module executes a list of security checks before
 * loading Flatnuke.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

// deny direct access to this file
if (preg_match("/security.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

// security checks
$ip     = strip_tags(getparam("REMOTE_ADDR",  PAR_SERVER, SAN_NULL));
$ref    = strip_tags(getparam("HTTP_REFERER", PAR_SERVER, SAN_NULL));
$mod    = getparam("mod",   PAR_GET,SAN_FLAT);
$action = getparam("action",PAR_GET,SAN_FLAT);
$op     = getparam("op",    PAR_GET,SAN_FLAT);

// check if IP address is blacklisted
if (is_blocked_ip($ip)){
	fnlog("Security", "$ip||Access denied to IP address $ip.");
	header("Location: http://www.spam.com/");	// have fun ;)
	exit;
}

// check if REFERER is a spammer
if (is_spam($ref, "words")) {
	fnlog("Security", "$ip||Access denied to spamming REFERER URL $ref.");
	echo "<b>ERROR</b>: Spam referer!";
// 	header("Location: http://www.spam.com/");	// have fun ;)
	exit;
}

// check if REFERER is blacklisted
if (is_spam($ref, "referers")) {
	fnlog("Security", "$ip||Access denied to REFERER URL $ref.");
	header("Location: index.php");
	exit;
}

// deny remote cross access to this file in case of comments or login/administration access
if( $mod!="" AND ($action=='addcommentinterface' OR ($mod=='none_Login' AND $action!='activateuser' AND $action!='newpwd' AND $action!='') OR ($mod=='none_Admin' AND $op!='fnccnews')) ){
	if( isset($_SERVER['HTTP_REFERER']) AND !preg_match("/". $_SERVER['HTTP_HOST'] ."/", $_SERVER['HTTP_REFERER'] ) ){
		if ($action == 'addcommentinterface') {
			$action = 'viewnews';
		} else {
			//fnlog("Security", "$ip||Blocked unauthorized remote access to index.php. URL=".$_SERVER['HTTP_REFERER']);	// uncomment if interested on lamers' access
			header("Location: index.php");
			exit;
		}
	}
}

// deny access to administration web interface if not allowed
if (preg_match("/none_Admin/i", $mod) AND !is_admin()) {
	fnlog("Security", "$ip||Access denied to Admin section.");
	header("Location: index.php");
	exit;
}

// check if REQUEST_URI tries to overwrite username
$req = getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT);
if(strstr($req,"myforum=")) {
	header("Location: index.php");
	exit;
}

// if there's no active session, start a new one
if (session_id() == "") session_start();
$_SESSION['fn_session'] = session_id();

?>
