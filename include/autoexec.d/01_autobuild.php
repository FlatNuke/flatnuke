<?php

/**
 * This module executes a list of checks on Flatnuke's
 * structure, and corrects errors found.
 * It can also be used to start install/setup procedure.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */


// security checks
if (preg_match("/autobuild.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

$ip = strip_tags(getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL));
/* -> Commented since from Flatnuke 3.0 the news' dir is moved to none_newsdata
      in section's dirs
// prevent annoying newbies still using that fuckin' Winzip(C) ...
$list_empty_dirs = array(get_fn_dir("news"));
foreach($list_empty_dirs as $empty_dir) {
	if(!file_exists($empty_dir)) {
		if(fn_mkdir($empty_dir, 0777)) {
			fnlog("Homepage", "$ip||".get_username()."||Directory $empty_dir created.");
		} else {
			fnlog("Homepage", "$ip||".get_username()."||Directory $empty_dir cannot be created, check write permissions.");
		}
	}
}
*/
// automatically set write permissions if admin didn't (INSTALL file lost? grrr...)
$list_writables = array(get_fn_dir("var"), get_fn_dir("var")."/firstinstall");
foreach($list_writables as $writable) {
	if(file_exists($writable) AND !is_writable($writable)) {
		if(chmod("$writable", 0777)) {
			fnlog("Permissions", "$ip||".get_username()."||$writable is now writable.");
		} else {
			fnlog("Permissions", "$ip||".get_username()."||$writable cannot be set writable, manually check write permissions.");
		}
	}
}

// create some system files on-the-fly
$list_new_files = array(get_fn_dir("var")."/motd.php");
foreach($list_new_files as $new_file) {
	if(!file_exists($new_file)) {
		fnwrite($new_file, _MOTDMESS, "w+", array("nonull"));
	}
}

// start setup procedure if needed
if( file_exists("setup.php") AND file_exists(get_fn_dir("var")."/firstinstall") ) {
	$mod = getparam("mod", PAR_GET, SAN_FLAT);
	if(strcmp($mod, "none_Login")!=0) {
		header("Location: setup.php");
	}
}

?>
