<?php
/**
 * Plugin che permette di effettuare ricerche tra i file gestiti da fd+
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

global $lang;
if (!isset($lang))$lang="it";

if ($lang=="en" or $lang=="es"){
    include_once ("languages/fd+lang/fd+en.php");
    include_once ("languages/$lang.php");
}
else if ($lang=="it"){
    include_once ("languages/fd+lang/fd+it.php");
    include_once ("languages/it.php");
}
else if ($lang=="de"){
    include_once ("languages/fd+lang/fd+de.php");
    include_once ("languages/de.php");
}
else if ($lang=="pt"){
    include_once ("languages/fd+lang/fd+pt.php");
    include_once ("languages/pt.php");
}
else {
    include_once ("languages/fd+lang/fd+en.php");
    include_once ("languages/$lang.php");
}
//fine impostazione lingua


//effettuo la ricerca e mostro i risultati
show_fd_result(find_fd_files($find,search_list_fd_files("sections/"),$method));


/**
 * Formatta il risultato della ricerca nei file gestiti da fd+
 * @param array $files i file risultato della ricerca
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function show_fd_result($files){
	$where = getparam("where",PAR_POST,SAN_FLAT);
	//se non ho trovato niente...
	if ($where!="allsite"){
		if (count($files)==0){
			echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";

		}
		else echo "<br><b>"._FP_RISULTATI.":</b><br><br>";
	}
	else {
		echo "<h4>Download:</h4>";
	}

	foreach ($files as $file){
		$file = preg_replace("/\.description$/i","",$file);
		$tempmod ="";
		$tempmod = preg_replace("/^sections\//i","",dirname($file));

		//per l'icona
		include_once("download/include/fdfunctions.php");
		global $icon_style;
		$ext="";
		$ext = get_file_extension($file);
		include "download/fdconfig.php";
		echo getIcon($ext,$icon_style);
		echo "<a href=\"index.php?mod=".rawurlencodepath($tempmod)."#".create_id(basename($file))."\"title=\""._FDDOWNLOADFILE.basename($file)."\">".basename($file)."</a><br>";
	}
}

/**
 * Elenca i file di fd+ presenti a partire dalla cartella $dirbase
 * @param string $dirbase la cartella a partire dalla quale cercare i file
 * @return un array con i percorsi dei file trovati
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function search_list_fd_files($dirbase){
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

// 		if (getLevel($myforum,"home") < getsectlevel($tempmod)) continue;
		if (preg_match("/\.description$/i",$file)){
			if (user_can_view_fdfile(preg_replace("/.description$/i","",$file)))
			$files[] = $file;
// 			echo "$file<br>";
		}

	}

	return $files;
}

/**
 * Cerca la stringa $string nei file $files
 * @param string $string la stringa da cercare
 * @param array $files l'array di file in cui cercare la stringa
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return un array con i percorsi dei file in cui è stata trovata la stringa
 */
function find_fd_files($string,$files,$method){
	$string=getparam($string,PAR_NULL,SAN_FLAT);
	if (!preg_match("/AND|OR/i",$method)) $method="AND";
	if (count($files)==0) return array();

	if (trim($string)=="") return array();

	$results= array();
	$file="";
	foreach ($files as $file){
		$text = "";
		if (preg_match("/\.txt$|\.htm$|\.html$|\.xml$|\.sgml$/i",preg_replace("/\.description$/i","",$file))){
			$text = strip_tags(get_file(preg_replace("/\.description$/i","",$file)));
		}
		//cerco nel file .description, nel nome del file e nel contenuto
		if (fn_search_string($string,strip_tags(get_file($file))."\n".basename(preg_replace("/\.description$/i","",$file))."\n".$text,$method)) $results[]=$file;

	}//fine foreach generale
// 	print_r($files);
	return $results;
}
/**
 * Restituisce TRUE se l'utente ha i permessi per vedere il file specificato,
 * FALSE in caso contrario
 * @param string $file il file da verificare
 * @param string $user l'utente di cui verificare l'autorizzazione
 * @since
 * @author Aldo Boccacci
 */
function user_can_view_fdfile($file,$user=""){

	include_once("download/include/fdfunctions.php");
	if (!fd_check_path($file,"sections/","false")){
		fd_die("the path $file is invalid! FD+: ".__LINE__);
	}
	if (!is_alphanumeric($user) and trim($user)!=""){
		return FALSE;
	}

	if (trim($user)=="") $user = getparam("myforum",PAR_COOKIE,SAN_FLAT);
	$fdmod = "";
	$fdmod = preg_replace("/^sections\//i","", dirname($file));
	$description = array();
	$description = load_description($file);

	//CONTROLLO:
	//il livello della sezione
	if (_FN_USERLEVEL<getsectlevel($fdmod)){
		return FALSE;
	}

	//se il file è nascosto
	if ($description['hide']=="true" and getlevel($user,"home")!="10"){
		return FALSE;
	}

	//il livello del file stesso
	if (_FN_USERLEVEL<$description['level']){
		return FALSE;
	}

	//se è tutto ok...
	return TRUE;

}
?>