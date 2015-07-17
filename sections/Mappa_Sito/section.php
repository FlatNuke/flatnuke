<?php
/**
	Mappa del sito per Flatnuke
	funziona in modo ottimale con la versione che trovate su :
	http://speleoalex.altervista.org/flatnuke3/index.php

	@author Alessandro Vernassa <speleoalex@gmail.com>

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the license, or any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
**/

// previene che il blocco sia eseguito direttamente e redirige a index.php
if (preg_match("/section.php/i",$_SERVER['PHP_SELF']))
{ Header("Location: ../../index.php");
	die();
}



if (!function_exists("getLang"))	//compatibilità con flatnuke 2.xx (non traduce nulla)
{
	function getLang($filename,$title=null)
	{
		return $title;
	}
}

global $lang,$theme;


echo "\n\n<ul class=\"fa-ul\" ><li><i class=\"fa-li fa fa-home fa-lg\"></i>&nbsp;";
echo "<a href='index.php' title=\""._HOMEMENUTITLE."\">";

if(($text = getLang("sections/.lang.xml")))
	 echo $text;
else
	#if (defined(_FIRSTBUTTONMENU))
	#	echo _FIRSTBUTTONMENU ;
	#else
		echo _HOMEMENUTITLE;


echo "</a>";
printsection("sections/");
echo "\n\n</li></ul>";


//if (!function_exists("list_sections"))
//{
/**
 *
 *
 * Ricava la lista delle sezioni o sottosezioni all' interno di una cartella
 *
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 *
 * @param string $path dove cercare le sezioni (es. sections/01_pippo)
 * @return array che contiene la lista delle sezioni, per scorrerlo utilizzare foreach
 */
function list_sections_sitemap($path)
{
	$path=getparam($path, PAR_NULL, SAN_FLAT);
	$handle=opendir("$path");
	$modlist=array();
	while ($file = readdir($handle))
	{
		if ($file != "." and $file!=".."  and (!preg_match("/^\./",$file) and ($file!="CVS") ) and is_dir("$path/$file") and stristr($file,"none_")==false)
		{
			array_push($modlist,$file);
		}
	}
	closedir($handle);
	natsort($modlist);
	return $modlist;
}
//}


/**
 * Stampa la lista delle sezioni e delle sottosessioni
 *
 * @param string $path dove cercare la sezione (es. sections/01_pippo)
 *
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 *
 * Edited by Alfredo Cosco <orazio.nelson@gmail.com>
 * 05/2014
 * Uses FontAwesome icons as default, sections icons 
 * can be overwritten by custom *.png in the theme.
 */
function printsection($path)
{
	global $theme;
	$path=getparam($path, PAR_NULL, SAN_FLAT);
	$modlist= list_sections_sitemap($path);
	if (count($modlist>0))
	{
		foreach ($modlist as $mod)
		{
			if(stristr($mod,"_"))
			{
				$tmp=preg_replace("/^[0-9]*_/i","",$mod);
				$tmp=str_replace("_","&nbsp;",$tmp);
			}
			else
				$tmp=$mod;
					if (file_exists("$path/$mod/lang.xml"))
			{
				$tmp=getLang("$path/$mod/lang.xml",$tmp);
			}

			//controllo il livello
			if (!user_can_view_section(preg_replace("/^sections\//i","","$path/$mod"))) continue;


			//$next=count(list_sections("$path/$mod"));
			//if ($next>0)
				echo "\n<ul class=\"fa-ul\">";

			// Find the image that identifies the current (sub)section; if not find, it takes the default one by the theme
			if(file_exists(str_replace("//","/","$path/$mod/section.png"))) {
				$section_image = str_replace("//","/","$path/$mod/section.png");
				$list_trigger = "<li style= \" list-style-image: url(".$section_image.") \">";
			} else {
				$section_image = "themes/$theme/images/section.png";
				$list_trigger =  "<li><i class=\"fa-li fa fa-folder-open-o fa-lg\"></i>&nbsp;";
			}


			echo $list_trigger;




			if ($path!="sections/")
				//echo "<a href='index.php?mod=".str_replace("sections//","",$path)."/$mod'>$tmp</a>";
				echo "<a href='index.php?mod=".rawurlencodepath(str_replace("sections//","",$path))."/".rawurlencodepath($mod)."' title=\"$tmp\">$tmp</a>";
			else
				//echo "<a href='index.php?mod=$mod'>$tmp</a>";
				echo "<a href='index.php?mod=".rawurlencodepath($mod)."' title=\"$tmp\">$tmp</a>";
			printsection("$path/$mod");
			echo "\n</li>";

			//if ($next>0)
				echo "\n</ul> ";



		}
	}
}


?>
