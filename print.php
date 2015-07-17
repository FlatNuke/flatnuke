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

//Time zone
if (function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	@date_default_timezone_set(date_default_timezone_get());

include_once "functions.php";

if (!defined("_FN_MOD")){
	create_fn_constants();
}

include "header.php";
include_once "themes/$theme/theme.php";

$host = getparam("HTTP_HOST", PAR_SERVER, SAN_FLAT);
$self = getparam("PHP_SELF", PAR_SERVER, SAN_FLAT);

$url = "http://".$host.$self;
$url = str_replace(basename($url),"",$url);

// Security convertions
$news = getparam("news",PAR_GET,SAN_FLAT);
$mod = _FN_MOD;
$file = _FN_FILE;

global $home_section;

if ($mod==""){
	if (trim($home_section)=="")
	$mod = "none_News";
	else $mod = $home_section;
}

// intercept possible directory changes
if(stristr($mod,".."))
	die(_NONPUOI);
if(stristr($file,".."))
	die(_NONPUOI);
if(stristr($news,".."))
	die(_NONPUOI);

// no action for empty datas
if( ($news == "") and ($mod == ""))
	die(_NONPUOI);


if (user_can_view_section($mod)){
	// print the news
	if($news!=""){
		include_once("flatnews/include/news_functions.php");
		if(!file_exists(_FN_SECTIONS_DIR."/$mod/none_newsdata/$news.fn.php")) {
			OpenTable();
			print("<div class=\"centeredDiv\"><b>"._NORESULT."</b></div>");
			CloseTable();
			return;
		}
		$data = load_news($mod,$news);

		$title = tag2html($data['title']);
		$header = tag2html($data['header']);
		$body = tag2html($data['body']);

		echo "<div class=\"centeredDiv\"><h1>$title</h1><br><table width='80%'><tr><td>";
		echo "<font size='3'>$header<br><br>$body</font>";
		echo "</td></tr></table>";
		echo "<br><br><small>"._ARTTR." <b>$sitename</b> - <a href='$url'>$url</a><br>";
		echo _URLREF." <a href='$url"."index.php?mod=$mod&amp;action=viewnews&amp;news=$news'>$url"."index.php?mod=$mod&amp;action=viewnews&amp;news=$news</a>";
		echo "</small></div>";
	}
	else {
		if($file=="") {
			if(!file_exists("sections/$mod/section.php") AND !file_exists("sections/$mod/gallery")) {
				OpenTable();
				print("<div class=\"centeredDiv\"><b>"._NORESULT."</b></div>");
				CloseTable();
				return;
			}
		} else {
			if(!file_exists("sections/$mod/$file")) {
				OpenTable();
				print("<div class=\"centeredDiv\"><b>"._NORESULT."</b></div>");
				CloseTable();
				return;
			}
		}

		$mod_title = preg_replace("/^([0-9]*)_|(none)_/i", "", $mod);
		$mod_title = str_replace("_", " ", $mod_title);
		echo "<div class=\"centeredDiv\"><h3>$mod_title</h3><br><table width='90%'><tr><td><font size='3'>" ;
		if($file=="") {
			if(file_exists("sections/$mod/section.php"))
				include("sections/$mod/section.php");
		} else include("sections/$mod/$file");
		/* Gestisce la galleria con gallery */
		if(file_exists("sections/$mod/gallery")) {
			echo "<br><br>";
			include("gallery/gallery.php");
		}
		echo "</font></td></tr></table>";
		echo "<br><br><small>"._ARTTR." <b>$sitename</b> - <a href=\"$url\">$url</a><br>";
		echo _URLREF." <a href=\"$url"."index.php?mod=$mod\">$url"."index.php?mod=$mod</a></small></div>";
	}
}//fine user_can_view_section
else {
	OpenTable();
	print("<div class=\"centeredDiv\"><b>"._NOLEVELSECT."</b></div>");
	CloseTable();
	return;
}
?>

<script type="text/javascript" language="javascript1.2">
<!--
// Do print the page
if (typeof(window.print) != 'undefined') {
    window.print();
    }
    //-->
    </script>

</body>
</html>
