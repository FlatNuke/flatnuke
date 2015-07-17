<?php
if (preg_match("/fd_vote_functions.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../../index.php");
    fd_die("You cannot call fd_vote_functions.php!",__FILE,__LINE);
}
$fdiplogfile = _FN_VAR_DIR."/fdplus/fdipvotelog.php";

$GLOBALS['fdiplogfile'] = $fdiplogfile;

include_once("shared.php");

if (!file_exists($fdiplogfile)) {
	if (!is_dir(_FN_VAR_DIR."/fdplus"))
		fn_mkdir(_FN_VAR_DIR."/fdplus/",0777);
	fnwrite($fdiplogfile," ","w",array());
}

/**
 * Aggiunge un voto
 *
 * Funzione che si occupa di aggiungere un voto seguendo le indicazioni del form di invio
 * e controllando che l'utente non abbia già votato.
 *
 * @author Aldo Boccacci
 * @since 0.8
 */
function fd_add_vote(){
	global $filename;

// 	echo $filename."<br>";
// 	echo $_POST['fdfilename'];
// 	echo $_POST['fdvote'];

	if (!check_path($filename,"sections/","false")) return;

	if (!fd_user_can_vote($filename)) return;
	//vote
	if (isset($_POST['fdvote'])){
		$fdvote = getparam("fdvote",PAR_POST,SAN_FLAT);
		if (!check_var($fdvote,"digit")) {
			fdlogf("\$fdvote (".strip_tags($fdvote).") is not valid! FD plugin \"vote.php\": ".__LINE__);
			return;
		}
	}
	else {
		return;
	}

	if ($fdvote>5) return;
	if ($fdvote<0) return;

	//file
	if (isset($_POST['fdfilename'])){
		$fdfilename = getparam("fdfilename",PAR_POST,SAN_FLAT);
		if (!check_path($fdfilename,"sections/","false")) {
			fdlogf("\$fdfilename (".strip_tags($fdfilename).") is not valid! FD plugin \"vote.php\": ".__LINE__);
			return;
		}
	}
	else {
		return;
	}
	if ($fdfilename!="$filename") return;

	$mod = "";
	$mod = preg_replace("/sections\//i","",basename($fdfilename));

	$tempdesc = array();
	$tempdesc = load_description($fdfilename);

	$tempdesc['totalvote'] = $tempdesc['totalvote']+1;
	$tempdesc['totalscore'] = $tempdesc['totalscore']+$fdvote;

	//se non è gestito da fd+ ritorno
	if (!file_exists($fdfilename.".description")) return;

	save_description($fdfilename,$tempdesc);

	$logdata = array();
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);
	$logdata['md5ip'] = fd_encode_ip($ip);
	$logdata['time'] = time();
	$logdata['path'] = $fdfilename;

	add_ip_log_data($logdata);

}//fine function fd_add_vote


/**
 * Codifica l'indirizzo ip con l'algoritmo md5 per evitare che altri utenti lo possano leggere
 * @author Aldo Boccacci
 * @param string $ip l'ip da codificare
 * @return string l'indirizzo ip codificato con l'algoritmo md5
 */
function fd_encode_ip($ip){
	$ip=getparam($ip, PAR_NULL, SAN_FLAT);
	return md5($ip);
}


/**
 * Verifica che i dati inseriti nel file di log non siano troppo vecchi e,
 * se lo sono, provvede a eliminarli ripulendo il file.
 * @param string $logfile il percorso del file di log
 * @author Aldo Boccacci
 */
function fd_check_ip_log_data() {
	global $fdiplogfile;
	if ($fdiplogfile=="") $fdiplogfile = _FN_VAR_DIR."/fdplus/fdipvotelog.php";
	if (!check_path($fdiplogfile,"","true")) fd_die("IP log file is not valid! fd_vote_functions: ".__LINE__);

	$old_data = load_ip_log_data();
	$newdata = array();
	if (count($old_data)>0){
		$data = array();
		foreach($old_data as $data){
// 	echo "time:".time();
// 	echo "<br>file:".$data['time']."<br>";
// 	echo (time()-$data['time']);
			if ($data['time']>(time()-86400)) {
//				echo $data['path']." è vecchio";
				$newdata[] = $data;
			}
//			echo "è minore";
//			$newdata[] = $data;
		}
	}
//	print_r($newdata);
	save_ip_log_data($newdata);
}

/**
 * Restituisce true se l'utente può votare. Restituisce FALSE se l'utente ha già votato questo file
 */
function fd_user_can_vote($file ){

$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);

$ip = fd_encode_ip($ip);

$logdata = array();
$logdata = load_ip_log_data();

if (count($logdata)>0){
	$logitem = array();
	foreach ($logdata as $logitem){
		if ($logitem['path']==$file){
			if ($logitem['md5ip'] = $ip) return FALSE;
		}
	}

}

return TRUE;
}

/**
 * Salva i dati relativi ai voti dati all'interno di fd+.
 * Struttura dell'array:
 * $data['']['md5ip']
 * $data['']['time']
 * $data['']['path']
 * @param array $data l'array con i dati
 * @author Aldo Boccacci
 * @since 0.8
 */
function save_ip_log_data($data){

	global $fdiplogfile;
	if ($fdiplogfile=="") $fdiplogfile = _FN_VAR_DIR."/fdplus/fdipvotelog.php";
	if (!check_path($fdiplogfile,"","true")) fd_die("IP log file is not valid! fd_vote_functions: ".__LINE__);

	if (!is_array($data)){
		fdlogf("\$data must be an array! ".__FILE__.": line ".__LINE__, "Error");
	}

	$datastring = "<votelog>";

	if (count($data)>0){
		$file = array();
		foreach ($data as $votedata){
			if (check_var($votedata['md5ip'],"alnum")){
				$md5ip = $votedata['md5ip'];
			}
			else return;
			if (check_var($votedata['time'],"digit")){
				$time = $votedata['time'];
			}
			else return;
			if (check_path($votedata['path'],"sections","false")){
				$path = $votedata['path'];
			}
			else return;

			$datastring .= "\n\t<entry>
			<md5ip>$md5ip</md5ip>
			<time>$time</time>
			<path>$path</path>
			</entry>";
		}
	}
	$datastring .= "\n</votelog>";
	if (preg_match("/\<\?/",$datastring) or preg_match("/\?\>/",$datastring)) continue;
	fnwrite($fdiplogfile, "<?xml version='1.0' encoding='UTF-8'?>\n".$datastring,"w",array("nonull")); // ISO-8859-1 to UTF-8
}

/**
 * Carica i dati relativi ai voti.
 *
 * Questa funzione carica i dati presenti nel file che tiene traccia dei voti espressi
 * sui file gestiti da fd+.
 *
 * @return una array con i dati salvati
 * @author Aldo Boccacci
 * @since 0.8
 */
function load_ip_log_data(){
	global $fdiplogfile;
	if ($fdiplogfile=="") $fdiplogfile = _FN_VAR_DIR."/fdplus/fdipvotelog.php";

// 	if (!check_path($fdiplogfile,"","true")) fd_die("IP log file is not valid! fd_vote_functions: ".__LINE__);

	if (file_exists($fdiplogfile))
		$xml = simplexml_load_file($fdiplogfile);
	else $xml = FALSE;

	if (!$xml){
// 		fdlogf("SIMPLEXML: I was not able to load the file fdipvotelog",
// 		"ERROR");
		return load_ip_log_data_old();
	}

	$counter=0;
	$data= array();
	$item= array();
	foreach ($xml->entry as $item){
		$path = "";
		$path = (string)$item->path;
		if (!check_path($path,"sections/","false")) continue;
		$md5ip = "";
		$md5ip = (string)$item->md5ip;
		if (!ctype_alnum($md5ip)) continue;
		$time = (string)$item->time;
		if (!ctype_digit($time)) continue;

		$data[$counter]['md5ip']= $md5ip;
		$data[$counter]['time'] = $time;
		$data[$counter]['path'] = $path;
	$counter++;
	}

	return $data;
}

/**
 * Carica i dati relativi ai voti.
 * Questa è la vecchia funzione che viene mantenuta perchè viene chiamata
 * nel caso che simplexml_load_file fallisca il suo compito.
 *
 * Questa funzione carica i dati presenti nel file che tiene traccia dei voti espressi
 * sui file gestiti da fd+.
 *
 * @return una array con i dati salvati
 * @author Aldo Boccacci
 * @since 0.8
 */
function load_ip_log_data_old(){
	global $fdiplogfile;
	if ($fdiplogfile=="") $fdiplogfile = "misc/fdplus/fdipvotelog.php";

// 	if (!check_path($fdiplogfile,"","true")) fd_die("IP log file is not valid! fd_vote_functions: ".__LINE__);

	$datastring = "";
	$datastring = get_file($fdiplogfile);
	$datastring = get_xml_element("votelog",$datastring);

	$item_array = array();
	$item_array = get_xml_array("entry",$datastring);

	$data = array();

	if (count($item_array)>0){
		$item= array();
		$counter = 0;
		foreach ($item_array as $item){
			$path = "";
			$path = get_xml_element("path",$item);
// 			if (!check_path($path,"sections/","false")) continue;
			$md5ip = "";
			$md5ip = trim(get_xml_element("md5ip",$item));
			if (!ctype_alnum($md5ip)) continue;
			$time = trim(get_xml_element("time",$item));
			if (!ctype_digit($time)) continue;

			$data[$counter]['md5ip']= $md5ip;
			$data[$counter]['time'] = $time;
			$data[$counter]['path'] = $path;

			$counter++;
		}
	}

	return $data;
}

/**
 * Aggiunge un nuovo elemento al file con i dati dei voti dati ai file di fd+.
 *
 * @param array $itemdata l'array con i dati del nuovo elemento
 * @author Aldo Boccacci
 * @since 0.8
 */
function add_ip_log_data($itemdata){

	if (!is_array($itemdata)) {
		fdlogf("\$itemdata is not an array! FDVOTE: ".__LINE__);
		return;
	}

	$old_data = load_ip_log_data();

	if (check_var($itemdata['md5ip'],"alnum")){
		$md5ip = $itemdata['md5ip'];
	}
	else return;
	if (check_var($itemdata['time'],"digit")){
		$time = $itemdata['time'];
	}
	else return;
	if (check_path($itemdata['path'],"sections","false")){
		$path = $itemdata['path'];
	}
	else return;

	$old_data[$path]['md5ip'] = $md5ip;
	$old_data[$path]['time'] = $time;
	$old_data[$path]['path'] = $path;

	save_ip_log_data($old_data);
}

/**
 * Funzione che mostra il voto nella scheda del file e permette di votare il file.
 *
 * @param string $filename il file
 * @param $votedesc l'array contenente la descrizione del file
 * @author Aldo Boccacci
 * @since 0.8
 *
 */
function fd_show_vote($filename, $votedesc=array()){

//CONFIGURAZIONE
$fdiplogfile = _FN_VAR_DIR."/fdplus/fdipvotelog.php";
global $sitename;
if (!file_exists($fdiplogfile))
	fnwrite($fdiplogfile," ","w",array());

//controllo $mod
$mod ="";
$mod = _FN_MOD;
// if ($mod!=""){
// 	if (!fd_check_path($mod,"","false")) return;
// }
// else return;

//---------------------------------------------------------
//ORA QUESTO CODICE È INSERITO IN FDVIEW.PHP
//IN MODO CHE VENGA AGGIORNATO PRIMA DELLA VISUALIZZAZIONE
//---------------------------------------------------------
//CONTROLLO COSA FARE
// if (isset($_POST['fdvote'])){
// fd_add_vote();
// $votedesc = load_description($filename);
// // se ho aggiunto un voto devo ricaricare la pagina per mostrarlo
// echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=$mod\">";
// }

$phpself = getparam("PHP_SELF", PAR_SERVER, SAN_NULL);



// $votedesc = array();
// $votedesc = $desc;

if ($votedesc['enablerating']!="1") return;

if ($votedesc['totalvote']=="0" or $votedesc['totalvote']=="" or $votedesc['totalvote']==NULL) $voteaverage = 0;
else $voteaverage = $votedesc['totalscore']/$votedesc['totalvote'];

$voteaverage = round($voteaverage,1);

//-----------------------------------
//VISUALIZZAZIONE
//-----------------------------------

$roundedvote = "";
$roundedvote = fd_get_rounded_vote($voteaverage);
$fdvoteclass = "";
if ($roundedvote==0) $fdvoteclass = "000";
else if ($roundedvote==0.25) $fdvoteclass = "025";
else if ($roundedvote==0.5) $fdvoteclass = "050";
else if ($roundedvote==0.75) $fdvoteclass = "075";
else if ($roundedvote==1) $fdvoteclass = "100";
else if ($roundedvote==1.25) $fdvoteclass = "125";
else if ($roundedvote==1.5) $fdvoteclass = "150";
else if ($roundedvote==1.75) $fdvoteclass = "175";
else if ($roundedvote==2) $fdvoteclass = "200";
else if ($roundedvote==2.25) $fdvoteclass = "225";
else if ($roundedvote==2.5) $fdvoteclass = "250";
else if ($roundedvote==2.75) $fdvoteclass = "275";
else if ($roundedvote==3) $fdvoteclass = "300";
else if ($roundedvote==3.25) $fdvoteclass = "325";
else if ($roundedvote==3.5) $fdvoteclass = "350";
else if ($roundedvote==3.75) $fdvoteclass = "375";
else if ($roundedvote==4) $fdvoteclass = "400";
else if ($roundedvote==4.25) $fdvoteclass = "425";
else if ($roundedvote==4.5) $fdvoteclass = "450";
else if ($roundedvote==4.75) $fdvoteclass = "475";
else if ($roundedvote==5) $fdvoteclass = "500";

if (!_FN_IS_GUEST){
//ripulisco il file di log dai dati vecchi
//spostato in fd_view_section
// fd_check_ip_log_data();

//MOSTRO LE STELLINE

if (fd_user_can_vote($filename)){

//CONTO I FORM GIÀ STAMPATI
global $fdcountform;
if (!isset($fdcountform)){
// 	echo "non è settata<br>";
	$GLOBALS['fdcountform'] = 0;
}
else $fdcountform++;

?>
<form name="fdvoteform<?php echo $fdcountform; ?>" action="index.php?mod=<?php echo rawurlencodepath($mod) ?>" method="post">
<input type="hidden" name="fdfilename" value="<?php echo $filename; ?>" />
<input type="hidden" name="fdvote" value="" />

<ul class="rating fnrating_<?php echo $fdvoteclass; ?>" title="<?php echo _FDRATEFILE; ?>">
	<li class="star_1"><a href="javascript:void(0)" onclick="document.getElementsByName('fdvote')[<?php echo $fdcountform; ?>].value=1;document.getElementsByName('fdvoteform<?php echo $fdcountform; ?>')[0].submit()" title="1 Star">1</a></li>
	<li class="star_2"><a href="javascript:void(0)" onclick="document.getElementsByName('fdvote')[<?php echo $fdcountform; ?>].value=2;document.getElementsByName('fdvoteform<?php echo $fdcountform; ?>')[0].submit()" title="2 Stars">2</a></li>
	<li class="star_3"><a href="javascript:void(0)" onclick="document.getElementsByName('fdvote')[<?php echo $fdcountform; ?>].value=3;document.getElementsByName('fdvoteform<?php echo $fdcountform; ?>')[0].submit()" title="3 Stars">3</a></li>
	<li class="star_4"><a href="javascript:void(0)" onclick="document.getElementsByName('fdvote')[<?php echo $fdcountform; ?>].value=4;document.getElementsByName('fdvoteform<?php echo $fdcountform; ?>')[0].submit()" title="4 Stars">4</a></li>
	<li class="star_5"><a href="javascript:void(0)" onclick="document.getElementsByName('fdvote')[<?php echo $fdcountform; ?>].value=5;document.getElementsByName('fdvoteform<?php echo $fdcountform; ?>')[0].submit()" title="5 Stars">5</a></li>
</ul>

</form>

<?php
}//fine controllo usercan vote
else {
?>

<ul class="rating fnrating_<?php echo $fdvoteclass; ?>" title="<?php echo _FDALREADYRATED; ?>">
	<li class="star_1">&nbsp;</li>
	<li class="star_2">&nbsp;</li>
	<li class="star_3">&nbsp;</li>
	<li class="star_4">&nbsp;</li>
	<li class="star_5">&nbsp;</li>
</ul>
<?php


}
}//fine controllo is_guest
else {
//se non sono registrato non posso votare
?>

	<ul class="rating fnrating_<?php echo $fdvoteclass; ?>" title="<?php echo _DEVIREG." '$sitename' "._FDTOVOTEAFILE ?>">
	<li class="star_1">&nbsp;</li>
	<li class="star_2">&nbsp;</li>
	<li class="star_3">&nbsp;</li>
	<li class="star_4">&nbsp;</li>
	<li class="star_5">&nbsp;</li>
</ul>
<?php

}


}

/**
 * Restituisce la variabile $value arrotondata allo 0.5 inferiore
 * @param int $value la cifra da arrotondare
 * @author Aldo Boccacci
 * @since 0.8
 */
function fd_get_rounded_vote($value){
	$value = getparam($value,PAR_NULL,SAN_FLAT);
// 	if (!check_var($value,"digit")) return NULL;
// 	echo "VALUE: ".$value;
	if ($value<0.25) return 0;
	if ($value<0.5) return 0.25;
	if ($value<0.75) return 0.5;
	if ($value<1) return 0.75;
	if ($value<1.25) return 1;
	if ($value<1.5) return 1.25;
	if ($value<1.75) return 1.50;
	if ($value<2) return 1.75;
	if ($value<2.25) return 2;
	if ($value<2.5) return 2.25;
	if ($value<2.75) return 2.50;
	if ($value<3) return 2.75;
	if ($value<3.25) return 3;
	if ($value<3.5) return 3.25;
	if ($value<3.75) return 3.50;
	if ($value<4) return 3.75;
	if ($value<4.25) return 4;
	if ($value<4.5) return 4.25;
	if ($value<4.75) return 4.50;
	if ($value<5) return 4.75;
	if ($value==5) return 5;
	if ($value>5) return 5;
}

?>