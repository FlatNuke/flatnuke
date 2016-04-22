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

// include Flatnuke APIs
include_once "config.php";
include_once "functions.php";
create_fn_constants();

// language definition by configuration or by cookie
$userlang = getparam("userlang", PAR_COOKIE, SAN_FLAT);
if ($userlang!="" AND is_alphanumeric($userlang) AND file_exists("languages/$userlang.php")) {
	$lang = $userlang;
}
switch($lang) {
	case "de" OR "es" OR "fr" OR "it" OR "pt":
		include_once ("languages/$lang.php");
		include_once ("languages/fd+lang/fd+$lang.php");
	break;
	default:
		include_once ("languages/en.php");
		include_once ("languages/fd+lang/fd+en.php");
	break;
}

// build and print headersite
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
	$title = "";
	$header = "";
	$body = "";
	$news_link = "";
	
	// print the news
	if($news!=""){
		include_once("flatnews/include/news_functions.php");
		include_once("flatnews/include/news_view.php");
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
		$news_link = get_news_link_array($mod,$news);
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
		$title = preg_replace("/^([0-9]*)_|(none)_/i", "", $mod);
		$title = str_replace("_", " ", $title);
	}

	?>
	<div class="section">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1><span class="fa fa-book"> <?php echo $title ?></span></h1></h1>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?php
					// news
					if($news!="") {
						echo "<p>$header</p>";
					}
					// section / file
					if($file=="") {
						if(file_exists("sections/$mod/section.php"))
							include("sections/$mod/section.php");
					} else include("sections/$mod/$file");
					// gallery
					if(file_exists("sections/$mod/gallery")) {
						echo "<br><br>";
						include("gallery/gallery.php");
					}
					?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<p><?php echo $body ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<hr>
					<small><?php echo _ARTTR." <b>$sitename</b> - <a href='$url'>$url</a>" ?></small><br/>
					<?php
					if($news!=""){
						?><small><?php echo $news_link['news_infos'] ?></small><br/>
						<small><?php echo _URLREF." <a href='$url"."index.php?mod=$mod&amp;action=viewnews&amp;news=$news'>$url"."index.php?mod=$mod&amp;action=viewnews&amp;news=$news</a>" ?></small><br/><?php
					} else {
						?><small><?php echo _URLREF." <a href='$url"."index.php?mod=$mod'>$url"."index.php?mod=$mod</a>" ?></small><br/><?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<?php

}//fine user_can_view_section
else {
	OpenTable();
	print("<div class=\"centeredDiv\"><b>"._NOLEVELSECT."</b></div>");
	CloseTable();
	return;
}
?>

<script type="text/javascript">
	<!--
	// Do print the page
	if (typeof(window.print) != 'undefined') {
		window.print();
	}
	//-->
</script>

</body>
</html>
