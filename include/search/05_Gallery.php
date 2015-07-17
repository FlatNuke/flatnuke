<?php
/**
 * Plugin che permette di effettuare ricerche tra i file presenti nelle Gallery di Flatnuke
 * Autore: Aldo Boccacci
 * e-mail: zorba_ (AT) tin.it
 * sito web: www.aldoboccacci.it
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
 */
if (preg_match("/".basename(__FILE__)."/",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}
$find = getparam("find",PAR_POST,SAN_FLAT);
$where = getparam("where",PAR_POST,SAN_FLAT);
$method = getparam("method",PAR_POST,SAN_FLAT);

if (!defined("_FN_MOD")){
	create_fn_constants();
}

//effettuo la ricerca e mostro i risultati
show_gallery_result(find_gallery_files($find,list_gallery_files("sections/"),$method));


/**
 * Formatta il risultato della ricerca
 * @param array $files i file risultato della ricerca
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function show_gallery_result($files){
	$where = getparam("where",PAR_POST,SAN_FLAT);
	//se non ho trovato niente...
	if ($where!="allsite"){
		if (count($files)==0){
			echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";

		}
		else echo "<br><b>"._FP_RISULTATI.":</b><br><br>";
	}
	else {
		echo "<h4>Gallery:</h4>";
	}

	foreach ($files as $file){
		$file = preg_replace("/\.description$/i","",$file);
		$tempmod ="";
		$tempmod = preg_replace("/^sections\//i","",dirname($file));

		//per l'icona
		include_once("download/include/fdfunctions.php");
		include("download/fdconfig.php");
		$ext="";
		$ext = get_file_extension($file);
		echo getIcon("png",$icon_style);
		echo "<a href=\"index.php?mod=".rawurlencodepath($tempmod)."\" title=\""._GOTOSECTION.": ".preg_replace("/^\//","",$tempmod)."\">".basename($file)."</a><br>";
	}
}

/**
 * Elenca i file gestiti dalla gallerua presenti a partire dalla cartella $dirbase
 * @param string $dirbase la cartella a partire dalla quale cercare i file
 * @return un array con i percorsi dei file trovati
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function list_gallery_files($dirbase){
	$dirbase=getparam($dirbase,PAR_NULL,SAN_FLAT);
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$myforum=getparam("myforum",PAR_COOKIE,SAN_FLAT);

	if (!is_alphanumeric($myforum) and trim($myforum)!=""){
		fnlog("list_files", "$addr||$myforum||username not valid!");
		die("username is not valid!");
	}

	if (trim($dirbase)=="") $dirbase="sections/";

	$files = array();
	//elenco delle sottocartelle
	include_once("include/filesystem/DeepDir.php");
	$tempfiles = NULL;
	$tempfiles = new DeepDir();
	$tempfiles ->setDir("$dirbase");
	$tempfiles ->load();

	foreach ($tempfiles->files as $file){
		$tempmod ="";
		$tempmod = preg_replace("/^sections\//i","",dirname($file));
		if (_FN_USERLEVEL < getsectlevel($tempmod)) continue;

		if (preg_match("/\.png$|\.jpg$|\.jpeg$/i",$file)){
			if (file_exists("sections/$tempmod/gallery")) $files[] = $file;
		}

	}
// 	print_r($files);
	return $files;
}

/**
 * Cerca la stringa $string nei nomi dei file $files
 * @param string $string la stringa da cercare
 * @param array $files l'array di file in cui cercare la stringa
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return un array con i percorsi dei file in cui è stata trovata la stringa
 */
function find_gallery_files($string,$files,$method){
	$string=getparam($string,PAR_NULL,SAN_FLAT);
	if (!preg_match("/AND|OR/i",$method)) $method="AND";
	if (count($files)==0) return array();

	if (trim($string)=="") return array();

	$results= array();
	$file="";
	foreach ($files as $file){
		if (fn_search_string($string,basename($file),$method)) $results[]=$file;

	}//fine foreach generale
// 	print_r($files);
	return $results;
}

?>
