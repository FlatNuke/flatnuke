<?php
if (preg_match("/dashboard.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}
//var_dump($_GET);
chdir("../../../");

include_once("config.php");
include_once("functions.php");

$mod = getparam("mod", PAR_GET, SAN_FLAT);
//$mod = _FN_MOD;

// external code declarations
//include_once (get_fn_dir("sections")."/$mod/none_functions/func_interfaces.php");

$file= getparam("file", PAR_GET, SAN_FLAT);

	$content = stripslashes(get_file(get_fn_dir("var")."/log/$file.php"));
	$content = preg_replace("/^\<\?php exit\(1\);\?\>/","",$content); 
	echo $content;
	
?>
