<?php

// intercept direct access to this file & rebuild the right path
if (preg_match("/section.php/i",$_SERVER['PHP_SELF'])) {
	//Time zone
	if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
		@date_default_timezone_set(date_default_timezone_get());

	chdir("../../");
	include_once("config.php");
	include_once("functions.php");

 	if (!defined("_FN_MOD"))
		create_fn_constants();

	// set again cookies values of the language
	$userlang = getparam("userlang", PAR_COOKIE, SAN_FLAT);
	if ($userlang!="" AND is_alphanumeric($userlang) AND file_exists("languages/$userlang.php")) {
		$lang = $userlang;
	}
	include_once("languages/$lang.php");
	// set again cookies values of the theme
	$usertheme = getparam("usertheme", PAR_COOKIE, SAN_FLAT);
	if ($usertheme!="" AND !stristr("..",$usertheme) AND is_dir(get_fn_dir("themes")."/$usertheme")) {
		$theme = $usertheme;
	}
	// set again charset: using ajax, it could be rewritten by the web server
	@header("Content-Type: text/html; charset="._CHARSET."");
}
// security checks
$req = getparam("REQUEST_URI", PAR_SERVER, SAN_NULL);
if(strstr($req,"myforum="))
	die(_NONPUOI);
$conf_mod  = getparam("conf_mod",  PAR_POST, SAN_FLAT);
$get_act   = getparam("get_act",   PAR_GET,  SAN_FLAT);
$mod       = getparam("mod",       PAR_GET,  SAN_FLAT);
$op        = getparam("op",        PAR_GET,  SAN_FLAT);
$fncclist  = getparam("fncclist",  PAR_GET,  SAN_FLAT);

// language definition
global $lang;
switch($lang) {
	case "it":
		include_once ("languages/admin/$lang.php");
	break;
	default:
		include_once ("languages/admin/en.php");
}

// external code declarations
include_once (get_fn_dir("sections")."/$mod/none_functions/func_interfaces.php");

// constants definitions
define("_FNCC_GOTOP", "<a href=\"#fncctoppage\"><img src='".get_fn_dir("sections")."/$mod/none_images/top.png' alt='top' title='top' style='vertical-align:middle; border:0'></a>");

################################################################
/*                     MAIN EXECUTION                         */
################################################################

if(is_admin()) {
	// external code declarations
	include_once (get_fn_dir("sections")."/$mod/none_functions/func_operations.php");
	include_once (get_fn_dir("sections")."/$mod/none_functions/func_verify.php");
	// POST actions
	switch($conf_mod) {
		case "phpinfo":			fncc_phpinfo();			break;	// print PHP configuration on the web server
		case "modgeneralconf":	fncc_modgeneralconf();	break;	// save main Flatnuke configuration
		case "modbodyfile":		fncc_modbodyfile();		break;	// save standard text file
		case "savepoll":		fncc_savepoll();		break;	// save poll informations
		case "archpoll":		fncc_archpoll();		break;	// archive poll and build a new one
		case "moddownconf":		fncc_savedownconf();	break;	// save fdplus configuration
		case "saveprofile":		fncc_saveprofile();		break;	// save new user profile
		case "updatewaiting":	fncc_updatewaiting();	break;	// update email address of a profile waiting for activation
		case "sendactivation":	fncc_sendactivation();	break;	// re-send activation code to users
		case "dobackup":		fncc_dobackup();		break;	// make the backup
		case "cleanbackup":		fncc_cleanbackup();		break;	// delete backup files on the server
		case "cleanlog":		fncc_cleanlog();		break;	// clean log file
		case "modblacklist":	fncc_modbodyfile();		break;	// save blacklist text file
	}
	// GET actions
	switch($get_act) {
		case "deletewaiting":	fncc_delwaiting();	break;	// delete waiting user
	}
	// GET options
	switch($op) {
		case "fnccinfo":		fncc_create_module_page(_FNCC_SERVERINFO,'fncc_info');		break;	// general infos on the site
		case "fnccconf":		fncc_create_module_page(_FNCC_DESGENERALCONF, 'fncc_generalconf');	break;	// main Flatnuke configuration
		/*----------------------------------------------*/
		case "fnccmotd"    :	fncc_create_module_page(_FNCC_DESMOTD,'fncc_editconffile', get_fn_dir("var")."/motd.php" ); break;	// manage MOTD file
		case "fnccpolledit":	fncc_create_module_page(_FNCC_DESPOLL, 'fncc_editpoll');	break;	// manage poll configuration
		case "fnccdownconf":	fncc_create_module_page(_FNCC_DESDOWNCONF,'fncc_fdplusconf');	break;	// manage fdplus configuration
		/*----------------------------------------------*/
		case "fnccmembers"   :	fncc_create_module_page(_FNCC_DESUSERSLIST,'fncc_userslist');		break;	// manage users of the site
		case "fnccnewprofile":	fncc_create_module_page(_FNCC_DESADDUSER,'fncc_newuserprofile' );	break;	// add a new user profile
		case "fnccwaitingusers":fncc_create_module_page(_FNCC_WAITINGUSERS,'fncc_listwaiting');break;	// manage profiles waiting for activation
		/*----------------------------------------------*/
		case "fnccbackup":		fncc_create_module_page(_FNCC_DESBACKUPS,'fncc_managebackups');		break;	// manage FN backups
		case "fncclogs":		fncc_create_module_page(_FNCC_DESLOGS,'fncc_managelogs');		break;	// manage system logs
		case "fnccblacklists":	fncc_create_module_page(_FNCC_DESBLACKLISTS,'fncc_manageblacklists');	break;	// manage FN blacklists
		/*----------------------------------------------*/
		case "fnccthemestruct":	fncc_create_module_page(_FNCC_DESTHEMESTRUCTURE,'fncc_editconffile',get_fn_dir("themes")."/$theme/structure.php");	break;	// manage theme's structure
		case "fnccthemestyle" :	fncc_create_module_page(_FNCC_DESTHEMESTYLE,'fncc_editconffile',get_fn_dir("themes")."/$theme/theme.php" );	break;	// manage theme's style
		case "fnccthemecss"   :	fncc_create_module_page(_FNCC_DESCSSTHEME,'fncc_editconffile',get_fn_dir("themes")."/$theme/style.css");	break;	// manage theme's CSS
		case "fnccforumcss"   :	fncc_create_module_page(_FNCC_DESCSSFORUM, 'fncc_editconffile',get_fn_dir("themes")."/$theme/forum.css");	break;	// manage forum's CSS
	}
} else fncc_onlyadmin();	// only admins can access this section

?>
