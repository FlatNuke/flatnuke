<?php
/**
 * Plugin che permette di effettuare ricerche tra gli utenti registrati su Flatnuke
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

// print_r(find_topics("prova",list_topics()));

//effettuo la ricerca e mostro i risultati
show_users_result(find_users($find,list_users(),$method));


/**
 * Formatta il risultato della ricerca nei file gestiti da usercomment
 * @param array $files i file risultato della ricerca
 * @author Aldo Boccacci
 * @since 2.5.8
 */
function show_users_result($files){
	$where = getparam("where",PAR_POST,SAN_FLAT);

	//se non sono un utente registrato
	if (is_guest()){
		echo "<h4>".ucfirst(_FUTENTI).":</h4>";
		echo "<i>("._FERRACC.")</i>";
		return;
	}

	//se non ho trovato niente...
	if ($where!="allsite"){
		if (count($files)==0){
			echo _NORESULT."<br><a href=\"javascript:history.back();\" title=\""._INDIETRO."\">&lt;&lt; "._INDIETRO."</a>";

		}
		else echo "<br><b>"._FP_RISULTATI.":</b><br><br>";
	}
	else {
		echo "<h4>".ucfirst(_FUTENTI).":</h4>";
	}

	foreach ($files as $file){
		$username = "";
		$username = preg_replace("/\.php$/i","",basename($file));

		if (getlevel($username,"home")=="10") echo "<img src=\"images/useronline/usr-admin.gif\" alt=\"admin\" />&nbsp;";
		else echo "<img src=\"images/useronline/usr-user.gif\" alt=\"user\" />&nbsp;";
		echo "<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=$username\" title=\""._VIEW_USERPROFILE."\">$username</a><br>";

	}
}



/**
 * Cerca la stringa $string nei file $files
 * @param string $string la stringa da cercare
 * @param array $files l'array di file in cui cercare la stringa
 * @param string $method il metodo di ricerca. Può essere "OR" oppure "AND"
 * @return un array con i percorsi dei file in cui è stata trovata la stringa
 */
function find_users($string,$files,$method){
	//se non sono un utente registrato
	if (is_guest()){
		return;
	}
	$string=getparam($string,PAR_NULL,SAN_FLAT);
	if (trim($string)=="") return array();
	if (!preg_match("/AND|OR/i",$method)) $method="AND";
	$results= array();
	$file="";
	foreach ($files as $file){
		$username = "";
		$username = preg_replace("/\.php$/i","",basename($file));
		if (fn_search_string($string,$username,$method)){
			$results[]= $file;
			continue;
		}
		$userdata = array();
		$userdata = load_user_profile($username);
		if (fn_search_string($string,$userdata['name'],$method)){
			$results[]= $file;
			continue;
		}
		if (fn_search_string($string,$userdata['homepage'],$method)){
			$results[]= $file;
			continue;
		}
		if (fn_search_string($string,$userdata['work'],$method)){
			$results[]= $file;
			continue;
		}
		if (fn_search_string($string,$userdata['from'],$method)){
			$results[]= $file;
			continue;
		}
		if (fn_search_string($string,$userdata['sign'],$method)){
			$results[]= $file;
			continue;
		}
	}
// 	print_r($files);
	return $results;
}

?>