<?php
/**
 * Copyright (C) 2004 Aldo Boccacci
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the license, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
 *
 * Visualizzatore delle statistiche di download
 *
 * Autore: Aldo Boccacci
 * E-Mail: doc4it(AT)altervista.org
 * Sito web: doc4it.altervista.org
 * data ultima modifica: 27/01/2006
 * versione: 0.7
 */

/**
 * Il file che contiene le statistiche di download
 */
$topdownloadsfile = get_fn_dir("var")."/fdplus/fd_top_download.php";

//-------------------
//FINE CONFIGURAZIONE
//-------------------

include_once("download/fd+.php");

if(file_exists($topdownloadsfile)) {
	view($topdownloadsfile,$newfiletime,$archivedir,$icon_style);
} else return;


function view($topdownloadsfile,$newfiletime,$archivedir,$icon_style){
	echo "<div align=\"center\"><h2>Statistiche di Download</h2></div><br>";
	$statfile = fopen($topdownloadsfile,"r");
	$statstring = fread($statfile,filesize($topdownloadsfile));
	fclose($statfile);

	$statstring = get_xml_element("topdownloads",$statstring);
	$files = get_xml_array("file",$statstring);

	$archivedfiles = array();

	foreach ($files as $file){
		$filepath = get_xml_element("path",$file);
		//verifico che il file esista
		if (!file_exists($filepath)) continue;
		$lastdir = strrchr(dirname($filepath),"/");
		if (preg_match("/".$archivedir."/",$lastdir)){
			$archivedfiles[] = $filepath;
			continue;
		}
		show_file($filepath,$archivedir,$newfiletime,$icon_style);
	}

	echo "<div align=\"center\"><h2>File Archiviati</h2></div><br>";
// 	print_r($archivedfiles);
	if (count($archivedfiles)!=0){
		foreach ($archivedfiles as $archivedfile){
			show_file($archivedfile,$archivedir,$newfiletime,$icon_style);
		}
	}
}


/**
 * Controlla l'esistenza del file $topdownloadsfile. Se non lo trova lo crea.
 * @param string il percorso del file
 * @author Aldo Boccacci
 */
function fdview_check_file($topdownloadsfile){
	if (!is_dir(dirname($topdownloadsfile))) fn_mkdir(dirname($topdownloadsfile),0777);
	if (!file_exists($topdownloadsfile)){
		$tmpfile = fopen($topdownloadsfile,"w");
		fwrite($tmpfile,"<?xml version='1.0' encoding='UTF-8'?>\n<topdownloads>\n</topdownloads>");
		fclose($tmpfile);
	}
}


/**
 * Crea la tabella per mostrare il file
 * @param string $filepath il percorso del file da mostrare
 * @param string il set di icone da mostrare
 * @author Aldo Boccacci
 * @since 0.3
 */
function show_file($filepath,$archivedir,$newfiletime,$icon_style){
	$description  = load_description($filepath);
	if ($description['hide']=="true" and !is_admin()) return;
	//controllo se il livello dell'utente è adeguato a quello del file
	if ($description['level']!="-1"){
		if($description['level'] > _FN_USERLEVEL)
			return;
	}

	//controllo se il livello dell'utente è adeguato a quello della sezione che ospita il file
	$mod="";
	$mod = preg_replace("/^.*sections\//i","",dirname($filepath));
	if(getsectlevel($mod) > _FN_USERLEVEL) return;

	//MOSTRO IL FILE
	echo "<table width=\"100%\" style=\"border:1px solid #000000;\">";

	//intestazione con il nome del file
	echo "	<thead><tr><th colspan=\"4\">";
	$path = pathinfo($filepath);
	echo getIcon($path['extension'],$icon_style);

	$mod = preg_replace("/sections\//i","",dirname($filepath));

	echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."#".rawurlencode(basename($filepath))."\"";
	if ($description['hide']=="true") echo " style=\"color : #ff0000; text-decoration : line-through;\"";
	echo "title=\""._FDDOWNLOADFILE.basename($filepath)."\">".basename($filepath)."</a>";
	if (time()-filectime(get_xml_element("path",$filepath))<($newfiletime*3600)){
		//controllo se ci troviamo in una dir di archivio
		$lastdir = strrchr(dirname($filepath),"/");
		if (!preg_match("/".$archivedir."/",$lastdir)){
			if (file_exists("images/mime/new.gif"))
			echo "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file!\">";
		}
	}
	echo "</th></tr></thead>";

	$hits = 0;
	if (trim($description['hits'])=="") $hits=0;
	else $hits =$description['hits'];

	//corpo
	echo "<tr><td width=\"8%\"><b>"._FDSECT.":</b></td><td><a href=\"index.php?mod=".rawurlencode(preg_replace("/^sections\//i","",dirname($filepath)))."\" title=\""._GOTOSECTION.": ".preg_replace("/^sections\//i","",dirname($filepath))."\">".preg_replace("/^sections\//i","",dirname($filepath))."</a></td>
	<td width=\"10%\"><b>"._FDHITS.":</b></td><td width=\"8%\"><b>$hits</b></td>
	</tr>";
	echo "<tr><td><b>"._FDDATE.":</b></td><td>".date(_FDDATEFORMAT,filectime($filepath))."</td>";
	echo "<td><b>"._FDSIZE.":</b></td><td>".round((filesize($filepath)/1024))."&nbsp;kb</td></tr>";
	echo "</table><br>";
}

?>