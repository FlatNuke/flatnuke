<?php
/**
 * Plugin che permette di effettuare ricerche nei topic gestiti da Flatorum
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

include_once("forum/include/ff_functions.php");

if (!defined("_FN_MOD")){
	create_fn_constants();
}

//effettuo la ricerca e mostro i risultati
format_ff_result(find_ff_topics($find,list_ff_topics(get_forum_root()),$method));

/**
 * Formatta il risultato della ricerca nelle sezioni
 * @param array i file risultato della ricerca
 * @author Aldo Boccacci
 * @since 0.1
 */
function format_ff_result($files){
global $theme;
$where = getparam("where",PAR_POST,SAN_FLAT);
	if ($where!="allsite"){
		if (count($files)==0){
			echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";

		}
		else echo "<br><b>"._FP_RISULTATI.":</b><br><br>";
	}
	else {
		echo "<h4>Topics:</h4>";
	}
// include_once("forum/include/ff_functions.php");

	foreach ($files as $file){
		$topic = basename($file);
		$tmp = dirname($file);
		$argument = basename($tmp);
		$tmp = dirname($tmp);
		$group = basename($tmp);
		$tmp = dirname($tmp);
		$tmpmod = preg_replace("/.*sections\//i","",$tmp);
		$tmpmod = preg_replace("/^\//","",$tmpmod);

		if (!topic_is_visible($file)) continue;

		$topicdata = load_topic($file);

		$argdata=array();
		$argdata = load_argument_props(get_forum_root(),$group,$argument);

		if ($argdata['level']!="-1"){
			if (is_guest()) continue;
			else if (getlevel(get_username(),"home")<$argdata['level']) continue;
		}

		echo _ICONREAD."&nbsp;<a href=\"index.php?mod=".rawurlencodepath(find_forum_mod())."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=".rawurlencodepath($topic)."\" >".$topicdata['properties']['topictitle']."</a><br>";

	}

}


/**
 * Cerca la stringa $string nei file $files
 *
 * @param string $string la stringa da cercare
 * @param array $files l'array di file in cui cercare la stringa
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return un array con i percorsi dei file in cui è stata trovata la stringa
 * @author Aldo Boccacci
 * @since 0.1
 */
function find_ff_topics($string,$files,$method){
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
		if (fn_search_string($string, strip_tags(get_file($file)),$method))
			$results[]=$file;

	}
	return $results;
}

/**
 * Elenca i topics presenti a partire dalla cartella $dirbase
 * @param string $dirbase la cartella a partire dalla quale cercare i topics
 * @return un array con i percorsi dei file trovati
 * @author Aldo Boccacci
 * @since 0.1 (Flatforum)
 */
function list_ff_topics($dirbase){
	$dirbase=getparam($dirbase,PAR_NULL,SAN_FLAT);
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$myforum=getparam("myforum",PAR_COOKIE,SAN_FLAT);

	if (!is_alphanumeric($myforum) and trim($myforum)!=""){
		fnlog("list_files", "$addr||$myforum||username not valid!");
		die("username is not valid!");
	}

	if (trim($dirbase)=="") $dirbase=get_fn_dir("var")."/";

	$files = array();
	//elenco delle sottocartelle
	include_once("include/filesystem/DeepDir.php");
	$tempfiles = NULL;
	$tempfiles = new DeepDir();
	$tempfiles ->setDir("$dirbase");
	$tempfiles ->load();

	foreach ($tempfiles->files as $file){
		$tempmod ="";
		/* deprecated in PHP 5.3: verifiy how to use preg_replace instead str_replace
		$tempmod = eregi_replace(get_forum_root(),"",dirname($file));*/
		$tempmod = str_replace(get_forum_root(),"",dirname($file));

		if (preg_match("/\.ff.php$/i",$file)){
			$files[] = $file;
		}

	}

	return $files;
}

/**
 * Trova il mod di una sezione che sia un forum
 * @return il $mod di una sezione che sia un forum
 * @author Aldo Boccacci
 * @since 0.1 (Flatforum)
 */
function find_forum_mod(){
	//elenco delle sottocartelle di section
	include_once("include/filesystem/DeepDir.php");

	$dirs= array();
	$dirs = glob("sections/*",GLOB_ONLYDIR);
	if (!$dirs) $dirs = array(); // glob may returns boolean false instead of an empty array on some systems
	$dir = "";
	foreach($dirs as $dir){
		if (file_exists("$dir/forum")){
			return preg_replace("/^sections\//i","",$dir);
		}
	}

	//se non trovo nelle sezioni principali...
	$tempfiles = NULL;
	$tempfiles = new DeepDir();
	$tempfiles ->setDir("sections/");
	$tempfiles ->load();

	$tempfile="";
	foreach ($tempfiles->files as $tempfile){
		if (basename($tempfile)=="forum"){
			return preg_replace("/^sections\//i","",dirname($tempfile));
		}
	}

}

?>
