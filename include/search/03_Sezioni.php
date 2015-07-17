<?php
/**
 * Plugin che permette di effettuare ricerche nelle sezioni di Flatnuke
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
format_section_result(find_section($find,list_files("sections/"),$method));

/**
 * Formatta il risultato della ricerca nelle sezioni
 * @param array i file risultato della ricerca
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function format_section_result($files){
global $theme;
$where = getparam("where",PAR_POST,SAN_FLAT);
	if ($where!="allsite"){
		if (count($files)==0){
			echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";

		}
		else echo "<br><b>"._FP_RISULTATI.":</b><br><br>";
	}
	else {
		echo "<h4>Sezioni:</h4>";
	}

	foreach ($files as $file){
	$last_update="";
	$last_update = filemtime($file);
		if (preg_match("/section\.php$/i",$file)){
			$file = preg_replace("/\/\//","/",$file);
			echo "<img src=\"themes/$theme/images/section.png\" alt=\"section\" />&nbsp;<a href=\"index.php?mod=".rawurlencodepath(preg_replace("/^sections\//i","",dirname($file)))."\"  title=\""._GOTOSECTION.": ".preg_replace("/^sections\//i","",dirname($file))."\">".preg_replace("/^sections\//u","",preg_replace("/none_/i","",dirname($file)))."</a> (".date("d/m/Y - H:i",$last_update).")<br>";
		}
		else {
			$file = preg_replace("/\/\//","/",$file);
			echo "<img src=\"themes/$theme/images/section.png\" alt=\"section\" />&nbsp;<a href=\"index.php?mod=".rawurlencodepath(preg_replace("/^sections\//i","",dirname($file)))."&amp;file=".rawurlencodepath(basename($file))."\" title=\""._GOTOSECTION.": ".preg_replace("/sections\//i","",$file)."\">".preg_replace("/sections\//i","",preg_replace("/none_/i","",$file))."</a> (".date("d/m/Y - H:i",$last_update).") <br>";

		}
	}

}


/**
 * Cerca la stringa $string nei file $files
 * @param string $string la stringa da cercare
 * @param array $files l'array di file in cui cercare la stringa
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return un array con i percorsi dei file in cui è stata trovata la stringa
 */
function find_section($string,$files,$method){
	$string=getparam($string,PAR_NULL,SAN_FLAT);
	if (!preg_match("/AND|OR/i",$method)) $method="AND";
	if (trim($string)=="") {

		$results=array();
		return $results;
	}

	$results= array();
	$file="";
	foreach ($files as $file){

		//cerco nel nome del file/sezione e nel contenuto assieme
		if (fn_search_string($string,strip_tags(basename(preg_replace("/section\.php$/i","",$file)))."\n".strip_tags(get_file($file)),$method)) $results[]=$file;
		//cerco nel contenuto
// 		else if (fn_search_string($string,strip_tags(get_file($file)),$method)) $results[]=$file;

	}
	return $results;
}

/**
 * Elenca i file presenti a partire dalla cartella $dirbase
 * @param string $dirbase la cartella a partire dalla quale cercare i file
 * @return un array con i percorsi dei file trovati
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function list_files($dirbase){
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

		//permetti anche altri file php...
		if (preg_match("/\.htmlarea/i",$file)) continue;
		if (_FN_USERLEVEL < getsectlevel($tempmod)) continue;
		if (preg_match("/section\.php$|\.htm$|\.html$|\.txt$/i",$file)){
			$files[] = $file;
		}

	}

	return $files;
}

?>
