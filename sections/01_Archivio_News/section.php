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


if (preg_match("/section.php/i",$_SERVER['PHP_SELF']))
	{ Header("Location: ../../index.php");
	die();
	}

include_once("flatnews/include/news_functions.php");
global $mesi,$fuso_orario,$theme;
$tags_string = getparam("tags",PAR_GET,SAN_FLAT);
$tags_string = strip_tags($tags_string);
$tags = explode(",",$tags_string);

$category = getparam("category",PAR_GET,SAN_FLAT);
$category = strip_tags($category);
$year = getparam("year",PAR_GET,SAN_FLAT);
$year = strip_tags($year);
$month = getparam("month",PAR_GET,SAN_FLAT);
$month = strip_tags($month);

if (!check_var($year,"digit"))
	$year = NULL;
if (!check_var($month,"digit")){
	$month = NULL;
// 	$year = NULL;
}

view_news_archive_section();

/**
 * Visualizza la sezione di archivio delle news
 *
 * @author Aldo Boccacci
 */
function view_news_archive_section(){
	global $fuso_orario,$theme,$mesi;
	$news_sections = load_news_sections_list();
// 	print_r($news_sections);
	$year = getparam("year",PAR_GET,SAN_FLAT);
	$year = trim(strip_tags($year));
	if (!check_var($year,"digit"))
		$year = "";
	$month = getparam("month",PAR_GET,SAN_FLAT);
	$month = trim(strip_tags($month));
	if (!check_var($month,"alnum"))
		$month = "";

	if ($year==""){
		$years_array = array();
		for ($n=0;$n<count($news_sections);$n++){
			$news_array = load_news_list($news_sections[$n],TRUE);
// 			$news_array = list_news($news_sections[$n],TRUE,TRUE);
			for ($i=0;$i<count($news_array);$i++){
				$year = date("Y",get_news_time($news_array[$i])+(3600*$fuso_orario));
				if (!in_array($year,$years_array))
					$years_array[]=$year;
			}
		}
		echo "<h2>"._BYYEAR.":</h2>";
		echo "<ul>";
		if(count($years_array)>0) rsort($years_array);
		for ($i=0; $i < count($years_array); $i++){
			if($years_array[$i]!="")
				echo "<li><a href=\"index.php?mod=".get_mod()."&amp;year=".($years_array[$i])."\">$years_array[$i]</a></li>";
		}
		echo "</ul>";
	}
	else if ($year!="" and $month==""){
		$month_array = array();
		for ($n=0;$n<count($news_sections);$n++){
			$news_array = load_news_list($news_sections[$n]);
			//fix for month view from January to December
			sort($news_array);
			for ($i=0;$i<count($news_array);$i++){
				$this_year = date("Y",get_news_time($news_array[$i])+(3600*$fuso_orario));
				$month = date("m",get_news_time($news_array[$i])+(3600*$fuso_orario));
				if (!in_array($month,$month_array) and $this_year==$year)
					$month_array[]=$month;
			}
		}

		echo "<h2>$year</h2>";
		echo "<ul>";
// 		if(count($month_array)>0) ksort($month_array);
// 		print_r($month_array);
		for ($i=0; $i < count($month_array); $i++){
			$index=$month_array[$i]-1;
			if($month_array[$i]!="")
				echo "<li><a href=\"index.php?mod="._FN_MOD."&amp;year=$year&amp;month=".($month_array[$i])."\">$mesi[$index]</a></li>";
		}
		echo "</ul>";

	}
	else if ($year!="" and $month!=""){
		$month_tmp = $month-1;
		echo "<h2><a href=\"index.php?mod="._FN_MOD."&amp;year=$year\" title=\"$year\">$year</a> - ".$mesi[$month_tmp]."</h2>";
		for ($n=0;$n<count($news_sections);$n++){
			$news_array = load_news_list($news_sections[$n]);
			$newsok=array();
			for ($i=0;$i<count($news_array);$i++){
				$this_year = date("Y",get_news_time($news_array[$i])+(3600*$fuso_orario));
				$this_month = date("m",get_news_time($news_array[$i])+(3600*$fuso_orario));
				if ($this_month==$month and $this_year==$year)
					$newsok[]=$news_array[$i];
			}
			if (count($newsok)==0) continue;
			if ($news_sections[$n]=="none_News")
				echo "<h2>Home</h2>";
			else echo "<h2>".preg_replace("/^[0-9][0-9]_/s","",$news_sections[$n])."</h2>";
			for ($i=0;$i<count($newsok);$i++){
				$data = load_news_header($news_sections[$n],$newsok[$i]);
				$modstring="";
				if ($news_sections[$n]!="none_News")
					$modstring="mod=".$news_sections[$n]."&amp;";
				echo _ICONREAD."&nbsp;<a href=\"index.php?$modstring"."action=viewnews&amp;news=".$newsok[$i]."\" title=\""._READNEWS."\">";
				if (!$data['title']=="")
					echo $data['title'];
				else echo "------";
				echo "</a> (".date("d/m/Y - H:i", get_news_time($newsok[$i])).", "._LETTO.$data['reads']." "._VOLTE.")<br>";
			}
		}
	}

	if (getparam("year",PAR_GET,SAN_FLAT)==""){
		echo "<h2>Per tags:</h2>";
		$tags = load_tags_list();

		// Questo codice è parzialmente tratto da Wordpress 2.2
		// WP CODE
		$largest = 20;
		$smallest = 8;
		if (is_array($tags) and count($tags)>0)
			$min_count = min( $tags );
		else $min_count=0;
		if (is_array($tags) and count($tags)>0)
			$spread = max( $tags ) - $min_count;
		else $spread =1;
		if ( $spread <= 0 )
			$spread = 1;
		$font_spread = $largest - $smallest;
		if ( $font_spread < 0 )
			$font_spread = 1;
		$font_step = $font_spread / $spread;
		//FINE WP CODE
		if (is_array($tags)){
			foreach ($tags as $tag => $count){
				if ($count>0)
					echo "<span style=\"font-size: ".( $smallest + ( ( $count - $min_count ) * $font_step ) )."pt;\"><a href=\"index.php?mod=none_Search&amp;where=news&amp;tags=$tag\" title=\"$count  news\">$tag</a> </span>";
			// 		echo "&#187;&nbsp;<a href=\"index.php\">$tag</a>&nbsp;($count)<br>";
			}
		}
		echo "<h2>"._PERARGOMENTI.":</h2>";
		$categories = list_news_categories();
	// 	print_r($categories);return;
		for ($i=0; $i < count($categories); $i++) {
		$label=str_replace("_"," ", preg_replace("/\..*/","",$categories[$i]));
			echo "<div style=\"float:left; padding: 10px 10px 10px 10px; text-align:center\">";
			echo "<a href=\"index.php?mod=none_Search&amp;where=news&amp;category=".$categories[$i]."\">";
			$w3c_title = _ARGOMENTO.": ".preg_replace("/\.png$|\.gif$|\.jpeg$|\.jpg$/i","",$categories[$i]);
			echo "<img src=\"images/news/".$categories[$i]."\" style=\"border:0\" alt=\"".$label."\" title=\"$w3c_title\" />";
			echo "<br>".$label."</a></div>";
// 			if($i%5==0) echo "<div style=\"clear:both\"></div>";
		}
		//gestisco anche le news senza categoria
		echo "<div style=\"float:left; padding: 10px 10px 10px 10px; text-align:center\">";
		echo "<a href=\"index.php?mod=none_Search&amp;where=news&amp;category=nonews.png\">";
		echo "<img src=\"images/nonews.png\" style=\"border:0\" alt=\"nonews\" title=\"nonews\" />";
		echo "<br>nonews</a></div>";
		if($i%5==0) echo "<div style=\"clear:both\"></div>";
		echo "<div style=\"clear:both\"></div>";
	}
}

?>
