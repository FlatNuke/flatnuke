<?php

/**
 * File delle funzioni condivise tra il forum e il core di FlatNuke
 *
 * Questo file contiene le procedure di sistema necessarie al funzionamento
 * di FlatNuke e del suo forum.
 *
 * @package Funzioni_di_sistema
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */


/**
 * A collection of easy to include PHP functions that sanitize user inputs
 *
 * @author The Open Web Application Security Project - {@link http://www.owasp.org/software/labs/phpfilters.html}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
include_once("include/php_filters/sanitize.php");


/**
 * Restituisce la versione attuale di Flatnuke
 *
 * @since 2.5.7
 *
 * @return constant Versione di Flatnuke
 */
function get_fn_version(){
   define("FN_VERSION", "4.0.0");
   return FN_VERSION;
}


/**
 * Elimina i caratteri che possono provocare problemi di sicurezza
 *
 * Elimina dalla stringa passata come parametro i caratteri che possono
 * provocare problemi di sicurezza se eseguiti in concomitanza con codice
 * maligno.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.5.7
 *
 * @param string $string Stringa da verificare
 * @return string $string Stringa elaborata
 */
function fnsanitize($string) {
	$search = array(chr(10),chr(13),chr(00),"%00","[","]");
	$replace = array("","","","","&#91","&#93");
	$string = str_replace($search, $replace, $string);

// 	$string = str_replace(chr(10), "", $string);
// 	$string = str_replace(chr(13), "", $string);
// 	$string = str_replace(chr(00), "", $string);
// 	$string = str_replace("%00", "", $string);
// 	$string = str_replace("[", "&#91;", $string);
// 	$string = str_replace("]", "&#93;", $string);

	return $string;
}

/**
 * Restituisce un parametro POST o GET
 *
 * Restituisce un parametro controllato con fnsanitize derivante dai
 * metodi POST o GET.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @since 2.5.7
 *
 * @param string $param Nome del parametro
 * @param string $opt Opzione per specificare metodo (POST,GET,ALL, COOKIE, SESSION)
 * @param pointer $sanitize Puntatore a funzione che sanitizza il parametro
 * @return string $param Contenuto del parametro
 */
function getparam($param, $opt, $sanitize) {

	if(!isset($sanitize) OR ($sanitize==SAN_FLAT))
		$sanitize="fnsanitize";
	else if($sanitize==SAN_SYST)
		$sanitize="sanitize_system_string";
	else if($sanitize==SAN_PARA)
		$sanitize="sanitize_paranoid_string";
	else if($sanitize==SAN_NULL)
		$sanitize="sanitize_null_string";
	else if($sanitize==SAN_HTML)
		$sanitize="sanitize_html_string";

	//performance improvenents
	if($opt==PAR_NULL) {
		if(isset($param))
			return($sanitize($param));
		else
			return("");
	}
	else if($opt==PAR_ALL) {
		if(isset($_GET[$param]))
			return($sanitize($_GET[$param]));
		else {
			if(isset($_POST[$param]))
				return($sanitize($_POST[$param]));
			else
				return("");
		}
	}

	else if($opt==PAR_POST) {
		if(isset($_POST[$param]))
			return($sanitize($_POST[$param]));
		else
			return("");
	}

	else if($opt==PAR_GET) {
		if(isset($_GET[$param]))
			return($sanitize($_GET[$param]));
		else
			return("");
	}

	else if($opt==PAR_COOKIE) {
		if(isset($_COOKIE[$param]))
			return($sanitize($_COOKIE[$param]));
		else
			return("");
	}

	else if($opt==PAR_SESSION) {
		if(isset($_SESSION[$param]))
			return($sanitize($_SESSION[$param]));
		else
			return("");
	}

	else if($opt==PAR_SERVER) {
		if(isset($_SERVER[$param]))
			return($sanitize($_SERVER[$param]));
		else
			return("");
	}

	return("");
}


/**
 * Restituisce il codice di controllo codificato MD5 di un utente
 *
 * Restituisce il codice di controllo dell'utente passato come parametro,
 * mantenendo la codifica MD5.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $user Nome assoluto del profilo utente da elaborare
 * @return string Codice di controllo codificato MD5 dell'utente
 */
function getpass($user) {
	$user = getparam($user,PAR_NULL, SAN_FLAT);
	$userdata = array();
	$userdata = load_user_profile($user);
	return $userdata['password'];
}


/**
 * Applica un semaforo ad un file
 *
 * Applica un semaforo al file passato come parametro, impedendo qualsiasi
 * azione fintanto che non sarà spento con la chiave MD5 corretta.
 * NB: la gestione dei semafori deve essere supportata dal proprio webserver.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $user Nome assoluto del file da bloccare
 * @return string ID del semaforo
 */
function lock($filename) {

	if(function_exists("sem_get")) {
		/* uncomment this if your webserver
		// supports semaphores
		// get semaphore key*/
		$sem_key = @ftok($filename, 'F');
		// get semaphore identifier
		$sem_id = sem_get($sem_key, 1);
		// acquire semaphore lock
		sem_acquire($sem_id);
		// return sem_id
		return $sem_id;
	} else if(function_exists("flock")) { // alternative method
		if(!is_dir(_FN_VAR_DIR."/lockfile"))
			fn_mkdir(_FN_VAR_DIR."/lockfile", 0777);
		$fp = fopen(_FN_VAR_DIR."/lockfile/".md5($filename), "w+");
		if(!$fp)
			return;
		if (flock($fp, LOCK_EX)) { // Esegue un lock esclusivo
			return $fp;
		}

	} else {
		// pray!
	}
}


/**
 * Rilascia un semaforo applicato ad un file
 *
 * Rilascia il semaforo con ID uguale a quello passato come parametro,
 * permettendo nuovamente l'accesso al relativo file.
 * NB: la gestione dei semafori deve essere supportata dal proprio webserver.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $user ID del semaforo
 */
function unlock($object) {
	/* uncomment this if your webserver
	// supports semaphores
	// release semaphore lock*/
	if(function_exists("sem_get")) {
		sem_release($object);
		sem_remove($object);

	} else if(function_exists("flock")) { // alternative method
		$fp = fopen(_FN_VAR_DIR."/lockfile/".md5("$object"), "w+");
		if(!$fp)
			return;
		if (flock($object, LOCK_UN)) { // rilascia un lock esclusivo
			fclose($fp);
		}

	} else {
		// pray!
	}
}


/**
 * Restituisce il codice di controllo decodificato MD5 di un utente
 *
 * Restituisce il codice di controllo dell'utente passato come parametro,
 * decodificandolo con algoritmo MD5.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $user Nome assoluto del profilo utente da elaborare
 * @return string Codice di controllo decodificato MD5 dell'utente
 */
function getsecid($user) {
	$user = getparam($user,PAR_NULL, SAN_FLAT);
	if(!file_exists(get_fn_dir("users")."/".$user.".php"))
		return("");
	$userdata = array();
	$userdata = load_user_profile($user);
	return (md5($user.$userdata['password']));
}


/**
 * Verifica il codice di controllo da FlatNuke
 *
 * Verifica che il codice di controllo immesso dall'utente e salvato nel cookie
 * corrisponda a quello del profilo dell'utente stesso.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $user Nome assoluto del profilo utente da elaborare
 * @return boolean Vero o falso
 */
function versecid($user) {
	$user = getparam($user,PAR_NULL, SAN_FLAT);
	$secid = getparam("secid",PAR_COOKIE, SAN_FLAT);

	if (getsecid($user) == $secid)
		return (TRUE);
	else
		return (FALSE);
}


/**
 * Verifica il codice di controllo dal forum
 *
 * Verifica che il codice di controllo immesso dall'utente e salvato nel cookie
 * corrisponda a quello del profilo dell'utente stesso.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $user Nome assoluto del profilo utente da elaborare
 * @return boolean Vero o falso
 */
function versecid2($user) {
	$user = getparam($user,PAR_NULL, SAN_FLAT);
	$secid = getparam("secid",PAR_COOKIE, SAN_FLAT);

	if (md5($user.getpass($user)) == $secid)
		return (TRUE);
	else
		return (FALSE);
}


/**
 * Mette un file in una stringa
 *
 * Trasforma il file passato come parametro in una stringa di testo.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $filename Nome relativo alla root del file da elaborare
 * @return string Stringa con il contenuto del file
 */
function get_file($filename) {
	$filename = getparam($filename,PAR_NULL, SAN_FLAT);
	return file_get_contents($filename);
}


/**
 * Restituisce un elemento XML
 *
 * Restituisce un elemento XML da un file passato come parametro.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Aldo Boccacci <zorba_@tin.it> | 20060513: se non trova $elem restituisce una stringa vuota
 *
 * @param string $elem Nome dell'elemento XML da cercare
 * @param string $xml Nome del file XML da processare
 * @return string Stringa contenente il valore dell'elemento XML
 */
function get_xml_element($elem, $xml) {
	$elem = getparam($elem,PAR_NULL, SAN_FLAT);
	$xml  = getparam($xml, PAR_NULL, SAN_NULL);

	$ok = preg_match( "/\<$elem\>(.*?)\<\/$elem\>/s",$xml, $out );
	$return = ($ok) ? ($out[1]) : ("");
	return $return;
}


/**
 * Restituisce l'array di un ramo di elementi XML
 *
 * Restituisce l'array di un ramo di elementi XML da un file passato come parametro.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Aldo Boccacci <zorba_@tin.it> | 20060513: rimosso l'elemento di apertura dall'array dei risultati
 *
 * @param string $elem Nome del ramo di elementi XML da cercare
 * @param string $xml Nome del file XML da processare
 * @return array Array contenente il ramo di elementi XML
 */
function get_xml_array($elem, $xml) {
	$elem = getparam($elem,PAR_NULL, SAN_FLAT);
	$xml  = getparam($xml, PAR_NULL, SAN_NULL);
	$buff = explode("</".$elem.">", $xml);
	array_splice ($buff, count($buff)-1);
	$buffelement = "";
	$newbuff = array();
	foreach($buff as $buffelement){
		$newbuff[] = preg_replace("/^\<$elem\>/","",ltrim($buffelement));
	}
	return $newbuff;
}


/**
 * Restituisce il livello di un utente
 *
 * Restituisce il livello dell'utente passato come primo parametro; il secondo
 * parametro serve per gestire il riferimento alla cartella dove sono
 * presenti gli utenti, a seconda che la chiamata parta dall'homepage oppure
 * dal forum:
 *  - 0 per un utente registrato;
 *  - da 1 a 9 per un utente registrato di livello intermedio;
 *  - 10 per un utente amministratore.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $admin Nome assoluto del profilo utente da elaborare
 * @param string $from Origine della chiamata ("home"/"forum")
 * @return int Livello dell'utente
 */
function getlevel($admin, $from){
	// controlla implicitamente anche l'esistenza dell'account
	// del nome passato, quindi anche la corrispondenza del cookie
	$admin = getparam($admin,PAR_NULL, SAN_FLAT);
	$from = getparam($from,PAR_NULL, SAN_FLAT);
	if($from=="home"){
		if(!file_exists(get_fn_dir("users")."/$admin.php"))
			return(-1);
		$userdata = array();
		$userdata = load_user_profile($admin);
		return ((int)$userdata['level']);
	}
	if($from=="forum"){
		if(!file_exists("users/$admin.php"))
			return(-1);
		$fd = file("users/$admin.php");
		$level = str_replace("#","",str_replace("\n","",$fd[9]));
		return((int)$level);
	}
}


/**
 * Controllo sulla validità di una stringa alfanumerica
 *
 * Esegue un controllo sulla stringa passata come parametro, verificando che
 * contenga unicamente caratteri oppure numeri, secondo lo standard UNIX.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.5.7
 *
 * @param string $string Stringa da verificare
 * @return boolean Vero o Falso
 */
function is_alphanumeric($string) {
	$string = getparam($string,PAR_NULL, SAN_NULL);
	if (ctype_alnum("$string"))
		return (TRUE);
	else return (FALSE);
}


/**
 * Restituisce l'estensione di un file
 *
 * Restituisce l'estensione di un file passato come parametro.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.5.7
 *
 * @param string $filename Nome del file da verificare
 * @return string Estensione del file
 */
function get_file_extension($filename) {
	$filename = getparam($filename,PAR_NULL, SAN_FLAT);
	/* deprecated: PHP 5.3 upgrade
	ereg('[\.]*[[:alpha:]]+$', $filename, $extension);*/
	if(preg_match('/\.[^\.]+$/i', $filename, $matches)) {
		$extension = str_replace(".", "", $matches[0]);
	} else $extension = "";
	return $extension;
}


/**
 * Restituisce un timestamp formattato in secondi
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.5.7
 *
 * @return number Timestamp formattato
 */
function get_microtime() {
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = doubleval($mtime[1]) + doubleval($mtime[0]);

	return $mtime;
}


/**
 * Rende un link W3C compliant
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.7
 *
 * @param string $path URL da codificare
 * @return string URL codificato
 */
function rawurlencodepath($path){
	$parts=array();
	$parts = explode('/', $path);
	for ($i = 0; $i < count($parts); $i++) {
		$parts[$i] = rawurlencode($parts[$i]);
	}
	return implode('/', $parts);
}


/**
 * Elimina una cartella ed il suo contenuto
 *
 * @author Anton Makarenko <makarenkoa at ukrpost dot net> <webmaster at eufimb dot edu dot ua>
 * @author Original idea: http://ua2.php.net/manual/en/function.rmdir.php <development at lab-9 dot com>
 * @since 2.5.8
 *
 * @param string $target Directory da eliminare
 * @param boolean $verbose Indica se attivare o meno la modalità 'verbose' (disattivata di default)
 * @return boolean Vero o Falso
*/
function rmdirr($target, $verbose=FALSE) {
	$exceptions = array('.','..');
	if (!$sourcedir = @opendir($target)) {
		if ($verbose)
			echo '<strong>Couldn&#146;t open '.$target."</strong><br>\n";
		return FALSE;
	}
	while(false!==($sibling=readdir($sourcedir))) {
		if(!in_array($sibling,$exceptions)) {
			$object = str_replace('//','/',$target.'/'.$sibling);
			if($verbose)
				echo 'Processing: <strong>'.$object."</strong><br>\n";
			if(is_dir($object))
				rmdirr($object);
			if(is_file($object)) {
				$result = @unlink($object);
				if ($verbose&&$result)
					echo "File has been removed<br>\n";
				if ($verbose&&(!$result))
					echo "<strong>Couldn&#146;t remove file</strong>";
			}
		}
	}
	closedir($sourcedir);
	if($result=@rmdir($target)) {
		if ($verbose)
			echo "Target directory has been removed<br>\n";
		return TRUE;
	}
	if ($verbose)
		echo "<strong>Couldn&#146;t remove target directory</strong>";
	return FALSE;
}


/**
 * Funzione di scrittura controllata di un file
 *
 * Scrive una stringa nel file specificato rispettando una serie di opzioni (facoltative)
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $file    nome del file su cui scrivere
 * @param string $text    testo da scrivere
 * @param string $mode    modo di scrittura da passare alla funzione fopen
 * @param array  $options lista delle opzioni di scrittura
 * Le opzioni possono essere:
 * 	- nonull: la stringa da scrivere non può essere vuota
 * 	- nophp:  il testo non può contenere codice php
 * 	- nohtml: il testo non può contenere codice html
 */
function fnwrite($file, $text, $mode, $options=array()) {
	$file = getparam($file, PAR_NULL, SAN_NULL);
	$text = getparam($text, PAR_NULL, SAN_NULL);
	$mode = getparam($mode, PAR_NULL, SAN_NULL);
	$addr = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

	foreach ($options as $option) {
		if (trim($option)=="nonull") {
			if (trim($text, "\t\h\v\0 ")=="") {
				fnlog("Write", $addr."||".get_username()."||Writing an empty file is not allowed!");
				return;
			}
		}
		if (trim($option)=="nophp") {
			/* deprecated: PHP 5.3 upgrade
			if (eregi("\<\?", $text) or eregi("\?\>", $text)) {*/
			if (preg_match("/\<\?/", $text) or preg_match("/\?\>/", $text)) {
				fnlog("Write", $addr."||".get_username()."||Writing PHP code is not allowed!");
				return;
			}
		}
		if (trim($option)=="nohtml") {
			if (strip_tags($text)!=$text) {
				fnlog("Write", $addr."||".get_username()."||Writing HTML code is not allowed!");
				return;
			}
		}
	}

	if (file_exists($file))
		copy($file,$file."bak");

	for ($try=0;$try<20;$try++){
		$fd = fopen($file."bak", $mode);
		fwrite($fd, $text);
		fclose($fd);
// 		if ((filesize($file."bak") > 0) or (filesize($file."bak") == 0 and $text==""))
// 			break;
// 		else usleep(100);
		if (file_get_contents($file."bak")==$text)
			break;
		else {
			usleep(100);
			fnlog("FNWRITE","error writing file: ".strip_tags($file));
		}
	}


	if (file_get_contents($file."bak")==$text) {
		if (file_exists($file)) rename($file,$file."old");
		if (rename($file."bak", $file)){
			if (file_exists($file."old")) unlink($file."old");
		}
		else {
			if (file_exists($file."old")) rename($file."old",$file);
			fnlog("FNWRITE","RECOVERED BACKUP FILE: ".strip_tags($file));
		}

	} else {
		//fnlog("Write", $addr."||".get_username()."||Null \"$file.bak\" backup file!!");	// uncomment if you experience problems with filesystem
		unlink($file."bak");
	}

	if (file_exists($file."bak")){
		@unlink($file."bak");
	}
}

/**
 * Funzione che creare le cartelle in Flatnuke.
 * Questa funzione è ridefinibile per permettere di adattarla ad esigenze particolari,
 * ad esempio il safe_mode=on
 *
 * @param string $dirpath il percorso della cartella da creare
 * @param int $mode la modalità di creazione della cartella
 * @author Aldo Boccacci
 * @since 3.0
 */
function fn_mkdir($dirpath,$mode){
	if (file_exists("include/redefine/fn_mkdir.php")){
		include("include/redefine/fn_mkdir.php");
		if (is_dir($dirpath)) return TRUE;
		else return FALSE;
	}

	//it is necessare to return a value
	$return = FALSE;
	if (!check_path($dirpath,"","false"))
		fn_die("Mkdir","the dir ".strip_tags($dirpath)." isn't valid!");
	$mode = trim($mode);
	//se $mode non è valido metto quello di default del php
	if (!check_var($mode,"digit"))
		$mode = 0777;
// 		fn_die("MKDIR","the mode ".strip_tags($mode)." isn't valid!");
	if (mkdir($dirpath,$mode)){
		fnlog("Mkdir","dir ".strip_tags($dirpath)." created");
		$return = TRUE;
// 		@chmod($dirpath,$mode);
	}
	else fnlog("Mkdir"," I'm not able to create the dir ".strip_tags($dirpath));

	return $return;

}

/**
 * Funzione per includere automaticamente codice PHP
 *
 * Permette di includere tutti i files con estensione .php che sono
 * presenti all'interno di una cartella del sito.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.5.8
 *
 * @param string $path_phpcode	directory contenente i files da includere
 */
function load_php_code($path_phpcode) {
	$path_phpcode = getparam($path_phpcode, PAR_NULL, SAN_FLAT);
	if(file_exists($path_phpcode)) {
		$dir_phpcode = opendir($path_phpcode);
		$file_phpcode = 0;
		while ($filename_phpcode = readdir($dir_phpcode)) {
			/* deprecated: PHP 5.3 upgrade
			eregi('[\.]*[[:alpha:]]+$', $filename_phpcode, $extension_phpcode);
			if(strtolower($extension_phpcode[0])==".php" AND $filename_phpcode!="." AND $filename_phpcode!=".." AND !eregi("^none_", $filename_phpcode) AND !eregi("^\.", $filename_phpcode)) {*/
			if(preg_match('/[\.]php$/', $filename_phpcode) AND $filename_phpcode!="." AND $filename_phpcode!=".." AND !preg_match("/^none_/i", $filename_phpcode) AND !preg_match("/^\./", $filename_phpcode)) {
				$array_phpcode[$file_phpcode] = $filename_phpcode;
				$file_phpcode++;
			}
		}
		closedir($dir_phpcode);
		if($file_phpcode>0) {
			sort($array_phpcode);
		}
		for($i=0; $i<$file_phpcode; $i++) {
			include_once "$path_phpcode/$array_phpcode[$i]";
		}
	}
}


/**
 * Funzione che maschera un indirizzo email
 *
 * Permette di codificare un indirizzo email laciato in chiaro,
 * di modo che non sia facilmente interpretabile dai bot.
 *
 * @author Andrea Biondo <andrearobot@hotmail.com>
 * @since 2.5.8
 *
 * @param string $email_address	indirizzo email da codificare
 * @return string indirizzo email codificato
 */
function email_mask($email_address) {
	$email_address = getparam($email_address, PAR_NULL, SAN_FLAT);
	$email_replace = array( "@" => "%20&#040;at&#041;%20", "." => "%20&#040;dot&#041;%20");
	foreach($email_replace as $chr => $rep) {
		$email_address = str_replace($chr, $rep, $email_address);
	}
	return $email_address;
}


/**
 * Controlla il percorso di file che gli viene passato come parametro.
 *
 * Questa funzione verifica la validità del percorso passato come parametro. Innanzitutto viene
 * verificata la presenza di caratteri potenzialmente pericolosi.
 * Con il secondo parametro si indica eventualmente la cartella all'interno della quale deve trovarsi il file
 * (o sue sottocartelle).
 * Con il terzo parametro si indica se il file può avere estensione "php*"
 * Se il percorso rispetta tutte le condizioni la funzione restituisce TRUE,
 * in caso contrario restituisce FALSE.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $filename il nome del file
 * @param string $dirbase il file deve essere all'interno di questa directory o di una sua sottodirectory
 * @param string $allow_php se settato a "true" permette che il file abbia estensione .php e simili
 */
function check_path($filename,$dirbase,$allow_php){
	if (stristr($filename,"..")) return FALSE;
	if (stristr($filename,"%00")) return FALSE;
	if (stristr($filename,chr(13))) return FALSE;
	if (stristr($filename,chr(10))) return FALSE;
	if (stristr($filename,chr(00))) return FALSE;
	if (stristr($filename,"://")) return FALSE;
	if (stristr($filename,"?")) return FALSE;
	if (stristr($filename,"&")) return FALSE;
	if (stristr($filename,"$")) return FALSE;
	if (stristr($filename,"[")) return FALSE;
	if (stristr($filename,"]")) return FALSE;
	if (stristr($filename,"(")) return FALSE;
	if (stristr($filename,")")) return FALSE;
	if (stristr($filename,"<")) return FALSE;
	if (stristr($filename,">")) return FALSE;

	if ($dirbase!=""){
		$dir = "";
		$path = $filename;
		$limit = 0;
		//controlla / iniziale
		while (preg_match("/^\//",$path)){
			$path = preg_replace("/^\//","",$path);
			if ($limit==5)
				return FALSE;
			$limit++;
		}
		if (!preg_match("/^".preg_quote($dirbase,"/")."/",$path))
			return FALSE;

	}

	if ($allow_php!="true"){
		//  echo "controllo php";
		if (preg_match("/\.php.$|\.php$/",$filename))
			return FALSE;
	}
	return TRUE;
}


/**
 * Questa funzione controlla il tipo della variabile passata come primo parametro.
 *
 * Questa funzione controlla il tipo di variabile passata come primo parametro. Restituisce TRUE
 * se la variabile corrisponde al tipo specificato come secondo parametro.
 * Sono supportati i seguenti tipi:
 * 1. digit: solo numeri
 * 2. alnum: numeri e cifre (no spazi o segni di punteggiatura)
 * 3. text (no html): restituisce true se il testo è uguale al testo restituito dalla funzione strip_tag
 *                    (ovvero non sono presenti tag html/php nella stringa)
 * 4. boolean: i valori validi sono "1", "0", "true", "false", "on" e "off" (case insensitive)
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $var la variabile da controllare
 * @param string $type il tipo di variabile richiesto per ritornare TRUE.
 * @param string $lenght la lunghezza massima (attualmente inutilizzato)
 * @return TRUE se la variabile da controllare corrisponde al tipo richiesto. FALSE in caso contrario
 */
function check_var($var, $type="alnum",$lenght=""){
include_once("include/php_filters/sanitize.php");
	$var = trim($var);
	if ($var=="") return TRUE;
	if ($type=="digit"){
		if (ctype_digit("$var")){
			return TRUE;
		}
		else return FALSE;
	}
	else if ($type=="alnum"){
		if (ctype_alnum("$var")){
			return TRUE;
		}
		else return FALSE;
	}
	else if ($type=="text"){
		if ($var==strip_tags($var)){
			return TRUE;
		}
		else return FALSE;
	}
	else if ($type=="boolean"){
		/* deprecated: PHP 5.3 upgrade
		if (eregi("^1$|^0$|^true$|^false$|^on$|^off$",$var)){*/
		if (preg_match("/^1$|^0$|^true$|^false$|^on$|^off$/i",$var)){
			return TRUE;
		}
		else return FALSE;
	}
	else return FALSE;
}


/**
 * Questa funzione restituisce TRUE se l'indirizzo passato come parametro è incluso nella lista degli ip bloccati.
 * FALSE se l'indirizzo ip è ok. L'elenco degli ip bloccati è incluso nel file include/blacklists/ipblacklist.php
 *
 * Restituisce TRUE se l'indirizzo passato come parametro è inserito nella blacklist,
 * FALSE in caso contrario.
 *
 * @author Aldo Boccacci
 * @since 2.6
 *
 * @param string $ip l'indirizzo ip da verificare.
 * @return TRUE se l'indirizzo è vietato, FALSE in caso contrario
 */
function is_blocked_ip($ip){
	if (trim($ip)=="") return FALSE;

	/* lighthttp uses ::ffff:127.0.0.1 */
	if (strpos($ip, '::') === 0) {
        	$ip = substr($ip, strrpos($ip, ':')+1);
	}

	/* deprecated: PHP 5.3 upgrade
	if (!eregi("^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$",$ip)){*/
	if (!preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/",$ip)){
		echo "Invalid remote ip adress!<br>";
		return TRUE;
	}
	$blstring ="";
	if (!file_exists("include/blacklists/ipblacklist.php")){
		fnlog("Flatforum","Blocked ip list file doesn't exists!");
		return FALSE;
	}
	$blstring=get_file("include/blacklists/ipblacklist.php");

	$iparray=array();
	$iparray=explode("\n",$blstring);
	$item="";

	foreach ($iparray as $item){
		/* deprecated: PHP 5.3 upgrade
		if (eregi("^#",trim($item))) continue;
		if (trim($item)=="") continue;
		if (eregi("\<",$item)) continue;
		$item = trim(eregi_replace("\*",".",$item));
		if (eregi($item,$ip)) return TRUE;*/
		if (preg_match("/^#/",trim($item))) continue;
		if (trim($item)=="") continue;
		if (preg_match("/\</",$item)) continue;
		$item = trim(preg_replace("/\*/",".",$item));
		if (preg_match('/$item/',$ip)) return TRUE;
	}
	return FALSE;
}


/**
 * Restituisce TRUE se la stringa specificata viene ritenuta spam
 * secondo i criteri contenuti nel file $spamfile
 *
 * @author Aldo Boccacci
 * @since 2.6
 *
 * @param string $string la stringa da controllare
 * @param string $spamfile il nome senza estensione del file contenente i criteri di riconiscimento
 * @param boolean $print_alert se settato a TRUE mostra un avviso indicante la parola trovata
 * @param boolean $return_word se settato a TRUE restituisce anziché TRUE la parola trovata
 *                (se non viene trovata restituisce comunque FALSE)
 * @return TRUE se la stringa è vietata, FALSE in caso contrario
 *         (se $return_word è settato a TRUE restituisce, se viene trovata, la parola che risponde alla ricerca)
 */
function is_spam($string,$spamfile,$print_alert=FALSE,$return_word=FALSE){
	if (!check_path($spamfile,"","false")) die("spam file path is not valid! ".basename(__FILE__).": ".__LINE__);
	if (trim($string)=="") return FALSE;
	// load spam file
	if (!file_exists("include/blacklists/$spamfile.php")){
		return FALSE;
	}
	// limit the number of the links
	$links_limit = 8;
	if (substr_count($string, "http://")>$links_limit
		OR substr_count($string, "https://")>$links_limit
		OR substr_count($string, "ftp://")>$links_limit){
		if ($print_alert)
			echo "<div style='text-align: center;'><blockquote>"._TOOMANYLINKS."</blockquote></div>";
		return TRUE;
	}
	$blstring = get_file("include/blacklists/$spamfile.php");
	// check for spam
	$wordsarray = array();
	$wordsarray = explode("\n", $blstring);
	$item = "";
	foreach ($wordsarray as $item){
		/* deprecated: PHP 5.3 upgrade
		if (eregi("^#",trim($item))) continue;*/
		if (preg_match("/^#/",trim($item))) continue;
		if (trim($item)=="") continue;
		if (preg_match("/\b$item\b/i",$string,$out)) {
			if ($print_alert)
				echo "<div style='text-align: center'><blockquote>"._SPAMWORDIS.": <b>".$out[0]."</b></blockquote></div>";
			$ip = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
			fnlog("Spam", "$ip||".get_username()."||Spamfilter blocked the regexp \"$item\".");
			//se $return_word è settato a TRUE restituisco la parola trovata
			if ($return_word)
				return $out[0];
			else return TRUE;
		}
	}
	return FALSE;
}


/**
 * Restituisce il percorso della cartella richiesta attraverso il parametro $dir
 * Le opzioni possibili sono: news, blocks, themes, sections, users, var
 *
 * @author Aldo Boccacci
 * @since 2.6
 *
 * @param string $dir la cartella da restituire.
 * @return string il percorso della cartella richiesta
 */
function get_fn_dir($dir){
	$dir = getparam($dir, PAR_NULL, SAN_FLAT);
	$dir = trim($dir);
	switch($dir){
// 		case "news":     return "news";	break;
		case "users":    return "var/users";	break;
		case "blocks":   return "blocks";	break;
		case "themes":   return "themes";	break;
		case "var":      return "var";	break;
		case "sections": return "sections";	break;
		default:         return "";	break;
	}
}


/**
 * Funzione di die() personalizzata per Flatnuke che prima di uccidere il processo
 * salva un messaggio nel log
 * @param string $message il messaggio da stampare a schermo e da salvare nel log
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_die($zone, $message="",$file="",$line=""){
	$zone = strip_tags($zone);
	$message = strip_tags($message);
	if ($file!="" and check_path($file,"","true")) $file=strip_tags(basename(trim($file)));
	else $file="";
	if (check_var(trim($line),"digit")) $line=strip_tags(trim($line));
	else $line="";

	$file = basename($file);

	if ($file!="" and $line!="")
		$message = "$message $file: $line";
	fnlog($zone,$message);

	if (is_admin()) echo "<b>Fn_die:</b> $message";
	else echo "<b>Fn_die:</b> error";

	die();
}


/**
 * Funzione che restituisce il parametro $mod attuale (GET)
 * Per essere valido il parametro $mod deve puntare a una cartella esistente
 * oppure essere alfanumerico.
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function get_mod(){
	$mod= getparam("mod",PAR_GET,SAN_NULL);
	if (!check_path($mod,"","false")) return NULL;
	$mod = stripslashes($mod);

	/* deprecated: PHP 5.3 upgrade
	$mod = eregi_replace("//","/",$mod);
	$mod = eregi_replace("^/","",$mod);
	$mod = eregi_replace("/$","",$mod);
	$mod = eregi_replace("\./","",$mod);*/
	$mod = preg_replace("/\/\//","/",$mod);
	$mod = preg_replace("/^\//","",$mod);
	$mod = preg_replace("/\/$/","",$mod);
	$mod = preg_replace("/\.\//","",$mod);

	if (!is_dir(get_fn_dir("sections")."/$mod") and !ctype_alnum("$mod")){
		$mod= "FN_INVALID_SECTION";
		fnlog("ERROR","il parametro \$mod non è valido!");
	}

	return $mod;
}


/**
 * Funzione che restituisce il parametro $file attuale (GET)
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function get_file_var($nophp="false"){
	/* deprecated: PHP 5.3 upgrade
	if (!eregi("^true$|^false$",$nophp)) $nophp="true";*/
	if (!preg_match("/^true$|^false$/",$nophp)) $nophp="true";
	$file = getparam("file",PAR_GET,SAN_FLAT);
	if (!check_path($file,"",$nophp)) return NULL;

	/* deprecated: PHP 5.3 upgrade
	$file = eregi_replace("^/","",$file);
	if (!eregi("\.htm$|\.html$|\.xhtml|\.txt$|\.php",$file)) return NULL;
	if (eregi("/",$file)) return NULL;*/
	$file = preg_replace("/^\//i","",$file);
	if (!preg_match("/\.htm$|\.html$|\.xhtml|\.txt$|\.php/i",$file)) return NULL;
	if (preg_match("/\//i",$file)) return NULL;

	return $file;
}

?>
