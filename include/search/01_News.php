<?php
/**
 * Plugin che permette di effettuare ricerche tra i file gestiti da Usercomment
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

include_once("flatnews/include/news_functions.php");

if (!defined("_FN_MOD")){
	create_fn_constants();
}

$find = getparam("find",PAR_POST,SAN_FLAT);
$find = strip_tags($find);
$where = getparam("where",PAR_POST,SAN_FLAT);
$where = strip_tags($where);
$method = getparam("method",PAR_POST,SAN_FLAT);
$where = strip_tags($where);
$tags_string = getparam("tags",PAR_GET,SAN_FLAT);
$tags_string = strip_tags($tags_string);
$category = getparam("category",PAR_GET,SAN_FLAT);
$category = strip_tags($category);

//effettuo la ricerca e mostro i risultati
// show_news_result(find_news($find,list_news(),$method));
if ($tags_string!=""){
	//cerco i tag
	$user = get_username();
	$tags = explode(",",$tags_string);
	$news_sections = load_news_sections_list();
	$no_result=TRUE;
	for ($n=0;$n<count($news_sections);$n++){
		$newslist= list_news($news_sections[$n],TRUE);
		$arrayok=array();
		for ($i=0;$i<count($newslist);$i++){
			if (find_news_by_tags($news_sections[$n],$newslist[$i],$tags,$user)){
				$no_result=FALSE;
				$arrayok[]=$newslist[$i];
			}
		}
// 		print_r($arrayok);
		show_result($news_sections[$n],$arrayok);
	}
	if ($no_result==TRUE)
		echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";
}
else if ($category!=""){
	$news_sections = load_news_sections_list();
	$no_result=TRUE;
	$user = get_username();
	for ($n=0;$n<count($news_sections);$n++){
		$newslist= load_news_list($news_sections[$n]);
		$arrayok=array();
		for ($i=0;$i<count($newslist);$i++){
			if (find_news_by_category($news_sections[$n],$newslist[$i],$category,$user)){
				$no_result=FALSE;
				$arrayok[]=$newslist[$i];
			}
		}
// 		print_r($arrayok);
		show_result($news_sections[$n],$arrayok);
	}
	if ($no_result==TRUE)
		echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";
}
else if ($find!=""){
	if ($where=="allsite")
		echo "<h4>News:</h4>";

	$news_sections = load_news_sections_list();
	$no_result=TRUE;
	for ($n=0;$n<count($news_sections);$n++){
		$newslist= load_news_list($news_sections[$n]);
		$arrayok=array();
		for ($i=0;$i<count($newslist);$i++){
			if (find_news_by_text($news_sections[$n],$newslist[$i],$find,$method)){
				$no_result=FALSE;
				$arrayok[]=$newslist[$i];
			}
		}
// 		print_r($arrayok);
		show_result($news_sections[$n],$arrayok);
	}
	if ($no_result==TRUE)
		echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";

}

/**
 * Cerca la stringa $string nei file $files
 * @param string $string la stringa da cercare
 * @param array $files l'array di file in cui cercare la stringa
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return un array con i percorsi dei file in cui è stata trovata la stringa
 */
function find_news($string,$files,$method){
	$string=getparam($string,PAR_NULL,SAN_FLAT);
	if (trim($string)=="") return array();
	if (!preg_match("/AND|OR/i",$method)) $method="AND";
	$results= array();
	$file="";
	foreach ($files as $file){
		if (fn_search_string($string,strip_tags(get_file($file)),$method)) $results[]= $file;
	}
// 	print_r($files);
	return $results;
}

/**
 * Cerca nella news specificata i tag indicati nell'array
 *
 * @param string $section la sezione
 * @param string $news la notizia
 * @param array $tags i tag da cercare nella notizia
 * @author Aldo Boccacci
 *
 */
function find_news_by_tags($section,$news,$tags,$user){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	if ($section=="") return FALSE;
	if ($news=="") return FALSE;

	$newsfile= get_news_file($section,$news);
	if (!is_file($newsfile)) return FALSE;
	if (!check_path($newsfile,"","true"))
		return FALSE;
	$user = getparam($user,PAR_NULL,SAN_FLAT);
	if (!user_can_view_news($section,$news,$user,FALSE))
		return FALSE;

	$data = get_news_tags($section,$news);
	for ($n=0;$n<count($tags);$n++){
		if (!in_array($tags[$n],$data['tags']))
			return FALSE;
	}

	//se sono presenti tutti i tag nell'array
	return TRUE;
}

/**
 * Cerca nella news specificata i tag indicati nell'array
 *
 * @param string $section la sezione
 * @param string $news la notizia
 * @param string $find il testo da cercare nella notizia
 * @author Aldo Boccacci
 *
 */
function find_news_by_text($section,$news,$string,$method){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	$news = getparam($news,PAR_NULL,SAN_NULL);
	$string = getparam($string,PAR_NULL,SAN_FLAT);
	$method = getparam($method,PAR_NULL,SAN_NULL);
	if ($section=="") return FALSE;
	if ($news=="") return FALSE;

	$newsfile= get_news_file($section,$news);
	if (!is_file($newsfile)) return FALSE;
	if (!check_path($newsfile,"","true"))
		return FALSE;
	if (!user_can_view_news($section,$news,"",FALSE))
		return FALSE;

	if (fn_search_string($string,strip_tags(get_file($newsfile)),$method))
		return TRUE;

	return FALSE;
}

/**
 * Cerca la caregoria indicata
 *
 * @param string $section la sezione
 * @param string $news la notizia
 * @param array $catgory la categoria cercare nella notizia
 * @author Aldo Boccacci
 */
function find_news_by_category($section,$news,$category,$user){
// 	$section = getparam($section,PAR_NULL,SAN_NULL);
// 	$news = getparam($news,PAR_NULL,SAN_NULL);
	$user = getparam($user,PAR_NULL,SAN_FLAT);
	if ($section=="") return FALSE;
	if ($news=="") return FALSE;
	$newsfile= get_news_file($section,$news);
	if (!is_file($newsfile)) return FALSE;
// 	if (!check_path($newsfile,"","true"))
// 		return FALSE;
	if (!user_can_view_news($section,$news,$user,FALSE))
		return FALSE;
	if (!check_path($category,"","false"))
		return FALSE;
	$newscategory = get_news_category($section,$news);
// print_r($data);
	if (trim($category)==$newscategory)
		return TRUE;
}

/**
 * Mostra i risultati formattati
 *
 * @param string $section la sezione contenente le notizie
 * @param array $news_array l'array con le notizie
 * @author Aldo Boccacci
 */
function show_result($section, $news_array){
	global $theme;
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!check_path($section,"","false"))
		flatnews_die("\$section is not valid",__FILE__,__LINE__);
	if (!is_array($news_array))
		flatnews_die("\$news_array must be an array!",__FILE__,__LINE__);

	if ($section!="" and $section!="none_News") $modstring = "mod=$section&amp;";
		else $modstring="";

	if (count($news_array)>0){
		$sectionshow = preg_replace("/^none_/s","",$section);
		$sectionshow = preg_replace("/\/none_/s","/",$sectionshow);
		$sectionshow = preg_replace("/^[0-9][0-9]_/s","",$sectionshow);
		$sectionshow = preg_replace("/\/[0-9][0-9]_/s","/",$sectionshow);
		$sectionshow = preg_replace("/^\//s","",$sectionshow);
		if ($section=="none_News")
			echo "<h2>Home</h2>";
		else echo "<h2>$sectionshow</h2>";
		for ($i=0;$i<count($news_array);$i++){
			$data = load_news_header($section,$news_array[$i]);
			echo _ICONREAD."&nbsp;<a href=\"index.php?$modstring"."action=viewnews&amp;news=".$news_array[$i]."\" title=\"visualizza news\">".$data['title']."</a> (".date("d/m/Y - H:i", get_news_time($news_array[$i])).", "._LETTO.$data['reads']." "._VOLTE.")<br>";
		}
	}
}
?>
