<?php

if (preg_match("/print_subsections.php/i",$_SERVER['PHP_SELF'])) {
	Header("Location: ../../../index.php");
	die();
}

include("download/fdconfig.php");

$sections_path = _FN_SECTIONS_DIR;
global $home_section;
if (_FN_MOD!="")
	$mod = _FN_MOD;
else if ($home_section!="" and _FN_MOD=="")
	$mod = $home_section;
else $mod ="";

if ($mod!="" AND !file_exists("$sections_path/$mod/forum")
	AND !file_exists("$sections_path/$mod/download")
	AND (!file_exists("$sections_path/$mod/downloadsection") OR (file_exists("$sections_path/$mod/downloadsection") AND $section_show_header=="0"))) {
		if ($home_section!="" and _FN_MOD=="")
			echo "<br>";
		print_subsections($mod);
}

?>