<?php
/**
 * Autore: Aldo Boccacci
 * e-mail: zorba_ (AT) tin.it
 * sito web: doc4it.altervista.org
 * versione: 0.2
 * data rilascio: 18/9/2005
 *
 * Il layout grafico è stato curato da Marco Segato
 * sito web: marcosegato.altervista.org
 *
 * Traduzione inglese: Speleoalex (con suggerimenti di Marco Segato)
 * Traduzione tedesca: Bjorn Splinter
 *
 * Da usare con Flatnuke versione 2.5.7 o superiore
 *
 * Questo script consente di mostrare quanti utenti sono on-line in un portale
 * basato su Flatnuke. E' inoltre in grado di distinguere tra utenti comuni, amministratori
 * e ospiti.
 *
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

//lo script non può essere richiamato da solo
if (preg_match("/Utenti.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}

//imposto la lingua
global $lang;
if (!isset($lang)) $lang="it";
uo_set_lang($lang);

//inizializzo le variabili
$logfile = _FN_VAR_DIR."/useronline/useronline.php";

// Security convertions
$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);
if($ip=="") return;
/*if (isset($_SERVER["REMOTE_ADDR"])) $ip = $_SERVER["REMOTE_ADDR"];
else return;*/
$time = time();

$countguest = 0;
$countuser = 0;
$countadmin = 0;
$useronline = "";
$adminonline = "";

//Controllo che esista il file di log
if (!file_exists($logfile)){
	echo _UO_LOG_NOT_EXIST;
	if (create_log_file($logfile)==0)
		echo "<br><b>"._UO_LOG_FILE_CREATED."</b><br>";
}

//verifico i dati già inseriti nel file di log
check_data($logfile);

//aggiungo i dati dell'utente collegato
add_data($ip, $time,$logfile);

$text="";
$text = get_file($logfile);
$text = str_replace("<?php die(); ?>\n","",$text);

if (ltrim($text=="")) return;

$item = array();
$item = preg_split("/\n/", $text);
//print_r($item);
//echo $time;
foreach ($item as $event){

if (ltrim($event)=="") continue;
	$data = array();
	$data = preg_split("/\|/", $event);
// 	if (!isset($data[1])) continue;
	//se il tempo limite non è scaduto
	if ($data[1]> $time-120){
	//echo "tempo ok";
		//echo "data3= $data[3]";
		//se sono un ospite
		if ($data[3] == -1) $countguest += 1;

		//se sono un utente registrato
		else if (0 <= $data[3] and $data[3] < 10) {
			$countuser += 1;
			if (ltrim($useronline)==""){
				$useronline = $useronline.add_image("usr-user")."<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$data[2]."\" title=\""._UO_VIEW_PROFILE."\">$data[2]</a>";
			}
			else $useronline = $useronline."<br>".add_image("usr-user")."<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$data[2]."\" title=\""._UO_VIEW_PROFILE."\">$data[2]</a>";
			//echo "sono un utente";
		}
		//se sono un amministratore
		else if ($data[3]==10){
			$countadmin += 1;
			if (ltrim($adminonline)==""){
				$adminonline = $adminonline.add_image("usr-admin")."<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$data[2]."\" title=\""._UO_VIEW_PROFILE."\">$data[2]</a>";
			}
			else $adminonline = $adminonline."<br>".add_image("usr-admin")."<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$data[2]."\" title=\""._UO_VIEW_PROFILE."\">$data[2]</a>";
		}
	}
	//else echo "tempo scaduto";
}

//mostro il tutto
echo "<b>"._UO_USERS_ONLINE."</b><br>";
echo add_image("group-admins")."<b>$countadmin</b>&nbsp;"._UO_ADMINISTRATORS."<br>";
//se esiste un elenco di amministratori lo mostro
if (!ltrim($adminonline)=="") echo "<i>$adminonline</i><br>";

echo add_image("group-users")."<b>$countuser</b> "._UO_USERS."<br>";
//se esists un elenco di utenti lo mostro
if (!ltrim($useronline)=="") echo "<i>$useronline</i><br>";


echo add_image("group-guests")."<b>$countguest</b> "._UO_GUESTS."<br>";


/**
 * Crea il file di log se non è presente
 * @param string $logfile il percorso del file di log
 * valori restituiti:
 * 0 : il file è stato creato con successo
 * 1 : il file esisteva già
 * 2 : non sono riuscito a scrivere
 * @author Aldo Boccacci
 */
function create_log_file($logfile){

	$logfile=getparam($logfile, PAR_NULL, SAN_FLAT);
	if (!file_exists($logfile)){
		echo _UO_LOG_CREATE;
		if (!is_dir(_FN_VAR_DIR."/useronline")) fn_mkdir(_FN_VAR_DIR."/useronline", 0777);
		$file=fopen($logfile,"w");
		if (!fputs($file," ")==false){
			return 0;
		}
		else return 2;
		fclose($file);
	}
	else {
		return 1;
	}
}


/**
 * Aggiunge i dati dell'utente on-line nel file di log
 * La struttura del file è:
 * indirizzo ip|time della visita|nome utente|livello
 * @param string $ip l'indirizzo ip del visitatore
 * @param string $time restituisce il timestamp del momento della visita
 * @param string $logfile il percorso del file di log
 * @author Aldo Boccacci
 */
function add_data($ip, $time, $logfile) {
// Security convertions
$ip=getparam($ip, PAR_NULL, SAN_FLAT);
$time=getparam($time, PAR_NULL, SAN_FLAT);
$logfile=getparam($logfile, PAR_NULL, SAN_FLAT);

$getip = getparam("ip",PAR_GET,SAN_FLAT);
$getlogfile = getparam("logfile",PAR_GET,SAN_FLAT);
if($getip!="" OR $getlogfile!="") {
	die(_NONPUOI);
}

$check = "";
$check = get_file($logfile);
$check = str_replace("<?php die(); ?>\n","",$check);
// echo htmlentities($check);
if (stristr($check,"<?php ") or stristr($check,"?>")) die(_NONPUOI);
//se l'utente è già stato censito ritorno
//Controllo prima l'ip e poi il nome utente (se necessario)
if (preg_match("/".encode_ip($ip)."/", $check)){
	//se l'indirizzo ip è già stato inserito ma il nome utente è diverso
	//(ovvero se ho più utenti all'interno di una lan)
	// Security convertions
	$myforum = _FN_USERNAME;

	if (ltrim($myforum)!="" and _FN_USERLEVEL!=-1){
		//gestisco gli utenti all'interno della lan
		//Se il nome utente è già inserito devo ritornare
		if (preg_match("/\|$myforum\|/i", $check)){
			return;
		}
	}
	//se l'indizzo ip è stato inserito e non è settato il nome utente
	//posso ritornare senza problemi
	else return;

}

if (!file_exists($logfile)) echo "<b>"._UO_LOG_NOT_EXIST."</b>";
	$file = fopen($logfile, "a");
	// Security convertions
	$myforum = _FN_USERNAME;

	//controllo i dati per evitare che siano inseriti valori inappropriati
	if (!ctype_alnum(encode_ip($ip))) return;
	if (!preg_match("/^[0-9]+$/",$time)) return;
	//strano a dirsi ma il seguente controllo può provocare dei crash di apache!
// 	if (!ctype_digit($time)) return;
	//Non vengono gestiti i nomi utente che contengono caratteri non alfanumerici
	//In questo caso si compare come semplici guest
	//è opportuno aggiornare i profili utente!
	if (!is_alphanumeric($myforum)) $myforum="";

	$text = encode_ip($ip)."|$time|".$myforum."|"._FN_USERLEVEL."\n";

	//Controllo che non siano presenti i tag di apertura del php
	if (preg_match("/\<\?/", $text) or preg_match("/\?\>/", $text)) die(_NONPUOI);
	if (!uo_check_string($text)) die(_NONPUOI);

	fwrite($file, "<?php die(); ?>\n".$text);
	fclose($file);
	check_data($logfile);
}


/**
 * Verifica che i dati inseriti nel file di log non siano troppo vecchi e,
 * se lo sono, provvede a eliminarli ripulendo il file.
 * @param string $logfile il percorso del file di log
 * @author Aldo Boccacci
 */
function check_data($logfile) {
// Security convertions
$logfile=getparam($logfile, PAR_NULL, SAN_FLAT);
$ip = getparam("ip",PAR_GET,SAN_FLAT);
$getlogfile = getparam("logfile",PAR_GET,SAN_FLAT);
if($ip!="" OR $getlogfile!="") die(_NONPUOI);

$newtext = "";
$text = "";
$text = get_file($logfile);
$text = str_replace("<?php die(); ?>\n","",$text);
// echo htmlentities($text);

//se trovo qualcosa che non va nel file di log...
if (!uo_check_string($text)) {
	//... azzero il tutto.
	$purgefile = fopen($logfile,"w");
	fwrite($purgefile,"<?php die(); ?>\n");
	fclose($purgefile);
	$text="";
}
// echo $text;

if (ltrim($text=="")) return;

$item = array();
$item = preg_split("/\n/", $text);
//print_r($item);
//echo $time;
foreach ($item as $event){
	if (ltrim($event)=="") continue;
	//echo $event."<br>";
	//$textdata = $event; // variabile inutilizzata
	$data=array();
	$data = preg_split("/\|/", $event);
	//controllo che il time non sia malformato
	if (!preg_match("/^[0-9]+$/",trim($data[1]))) continue;
	//se il tempo limite non è scaduto
	if ($data[1]> time()-120){
		$newtext = $newtext.$event."\n";
	}
}

//Controllo che non siano presenti i tag di apertura del php
if (preg_match("/\<\?/", $newtext) or preg_match("/\?\>/", $newtext)) die(_NONPUOI);
if (!uo_check_string($newtext)) die(_NONPUOI);

$file = fopen($logfile,"w");
fwrite($file, "<?php die(); ?>\n".$newtext);
fclose($file);
}


/**
 * Restituisce il codice atto ad inserire l'icona dell'utente (se esiste)
 * @param string $type il tipo di icona da inserire
 * @author Aldo Boccacci
 * @return string la stringa per l'inserimento del'immagine
 */
function add_image($type){
$type=getparam($type, PAR_NULL, SAN_FLAT);
	if (!isset($type)) return "";
	if (file_exists("images/useronline/$type.gif")){
		return "<img src=\"images/useronline/$type.gif\" alt=\"".preg_replace("/group-|usr-/i", "", $type)."\" />&nbsp;";
	}

	else return "";
}

/**
 * Restituisce true se l'utente è di livello 10
 * (e dunque possiede i privilegi di amministrazione)
 * @author Aldo Boccacci
 * @return TRUE se l'utente collegato è di livello 10, FALSE in tutti gli altri casi
 */
function is_admin2(){
	// Security convertions
	$myforum = _FN_USERNAME;

	if($myforum=="") return FALSE;

	if ((getlevel($myforum,"home"))=="10" and versecid($myforum)) {
		return TRUE;
	} else return FALSE;
}


/**
 * Codifica l'indirizzo ip con l'algoritmo md5 per evitare che altri utenti lo possano leggere
 * @author Aldo Boccacci
 * @param string $ip l'ip da codificare
 * @return string l'indirizzo ip codificato con l'algoritmo md5
 */
function encode_ip($ip){
	$ip=getparam($ip, PAR_NULL, SAN_FLAT);
	return md5($ip);
}


/**
 * Imposta la lingua dell'interfaccia.
 * La traduzione inglese delle stringhe è stata presa dal sito di speleoalex
 * (con suggerimenti di Marco Segato)
 * La traduzione tedesca è stata inviata da bjorn splinter (insites[at]gmail.com)
 * @param string $lang il codice a due caratteri che identifica la lingua
 * @since 0.2
 * @author Aldo Boccacci
 */
function uo_set_lang($lang){
	$lang=getparam($lang, PAR_NULL, SAN_FLAT);
	if (!isset($lang)) $lang ="it";
	if ($lang=="it"){
		define ("_UO_LOG_NOT_EXIST","<b>Attenzione:</b> il file di log non esiste.<br>");
		define ("_UO_LOG_FILE_CREATED","file di log creato con successo!");
		define ("_UO_USERS_ONLINE","Persone&nbsp;on-line:");
		define ("_UO_USERS","utenti");
		define ("_UO_ADMINISTRATORS","amministratori");
		define ("_UO_GUESTS","ospiti");
		define ("_UO_FILENOTEXIST","<b>Attenzione!</b>: il file non esiste.");
		define ("_UO_LOG_CREATE","creo il file di log.");
		define ("_UO_VIEW_PROFILE","Visualizza il profilo dell'utente");
	}
	else if ($lang=="de"){
		define ("_UO_LOG_NOT_EXIST","<b>Achtung:</b> Logfile existiert nicht.<br>");
		define ("_UO_LOG_FILE_CREATED","Logfile erzeugt!");
		define
		("_UO_USERS_ONLINE","Personen&nbsp;on-line:&nbsp;&nbsp;");
		define ("_UO_USERS","Angemeldete");
		define ("_UO_ADMINISTRATORS","Administratoren");
		define ("_UO_GUESTS","Gäste");
		define ("_UO_FILENOTEXIST","<b>Warnung!</b>: File existiert nicht.");
		define ("_UO_LOG_CREATE","Schreibe Logfile.");
		define ("_UO_VIEW_PROFILE","Userprofil ansehen.");
	}

	else {
		define ("_UO_LOG_NOT_EXIST","<b>Attention:</b> log file doesn't exist.<br>");
		define ("_UO_LOG_FILE_CREATED","log file created!");
		//gli spazi servono per evitare di spezzare la linea che mostra il numero di amministratori
		define ("_UO_USERS_ONLINE","People&nbsp;on-line:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		define ("_UO_USERS","users");
		define ("_UO_ADMINISTRATORS","administrators");
		define ("_UO_GUESTS","guests");
		define ("_UO_FILENOTEXIST","<b>Warning!</b>: file doesn't exist.");
		define ("_UO_LOG_CREATE","writing log file.");
		define ("_UO_VIEW_PROFILE","View user's profile");
	}
}


/**
 * Controlla la stringa da scrivere sul file di supporto, al fine di impedire l'inserimento
 * di caratteri non previsti dalla struttura del file. L'approccio è di tipo "white-list".
 * Questa funzione rimuove dalla stringa passata come parametro i caratteri: "-", "|" e "\n"
 * e restituisce true se la porzione rimanente risponde ai criteri fissati
 * da is_alphanumeric().
 * @param string $string la stringa da controllare
 * @return boolean TRUE se la stringa risponde ai criteri citati poco sopra, FALSE in caso
 *         contrario
 * @since 0.2
 * @author Aldo Boccacci
 */
function uo_check_string($string){
	$string=getparam($string, PAR_NULL, SAN_FLAT);
	if (!isset($string)) return FALSE;
	if (trim($string)=="") return TRUE;

	if (is_alphanumeric(str_replace("-","",str_replace("|","",str_replace("\n","",$string))))) return TRUE;
	else return FALSE;
}
?>
