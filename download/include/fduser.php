<?php
if (preg_match("/fduser.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call fduser.php!",__FILE,__LINE);
}

/**
 * Questo script deve le sue origini a FD di Detronizator - aka Ivan De Marino
 * Modifiche di Aldo Boccacci
 * e-mail: zorba_ (AT) tin.it
 * sito web: www.aldoboccacci.it
 *
 * (Da usare con Flatnuke >= 2.5.7)
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

/**
 * Il carattere che separa il codice random di sicurezza dal nome del file
 * (questo è meglio non modificarlo)
 */
$separator_char ="-";

//ci sono prolemi con register_globals=off
$GLOBALS['separator_char'] = $separator_char;


/**
 * Questa funzione mostra il pulsante per permettere agli utenti di caricare dei file.
 * Se l'utente è abilitato ad uploadare file verrà mostrato il pulsante, in caso contrario sarà
 * mostrata una scritta specificante il motivo dell'impossibiltà di caricare files.
 * Se l'utente collegato è un amministratore sarà mostrato un pulsante per andare alla pagina
 * di validazione dei file in attesa di approvazione
 * @author Aldo Boccacci
 * @since 0.7
 */
function user_upload_interface(){
global $userfilelimit;

	if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
	}

	if (!check_var($userfilelimit,"digit")) fd_die("\$userfilelimit must be a digit!",__FILE__,__LINE__);

	if (user_can_upload() and (get_n_waiting_files()<$userfilelimit)){
		$path =getparam("mod", PAR_GET, SAN_FLAT);
		//pulsante per aggiungere un nuovo file
		echo "<div align=\"center\"><br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
		<input type=\"hidden\" name=\"path\" value=\"sections/$path\" readonly=\"readonly\">
		<input type=\"hidden\" name=\"fdaction\" value=\"useraddfile\" readonly=\"readonly\">
		<input type=\"hidden\" name=\"fdfile\" value=\"new\" readonly=\"readonly\">
		<input type=\"SUBMIT\" value=\""._FDPROPOSE."\"></form></div>";
	}
	else if (user_can_upload() and !(get_n_waiting_files()<$userfilelimit)){
		echo "<div align=\"center\"><br><span style=\"color : #ff0000;\">"._FDLIMIT."</span></div>";
	}
	else if (fd_is_admin()){
		echo "<div align=\"center\"><br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
		<input type=\"hidden\" name=\"fdaction\" value=\"publishuserfileinterface\" readonly=\"readonly\">
		<input type=\"SUBMIT\" value=\""._FDPUBLISHFILES." (".get_n_waiting_files().")\"></form></div>";
	}

}

/**
 * Restituisce true se l'utente è autorizzato a caricare file nella sezione corrente
 * @return TRUE se l'utente collegato è autorizzato a caricare file. FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 0.7
 */
function user_can_upload(){
//controlla livello sezione
	$myforum = get_username();
	if (trim($myforum)=="") return false;


	global $userblacklist,$minlevel;

	//controllo il livello
	$checklevel = FALSE;
	$minlevel = trim($minlevel);
	if ($minlevel=="" or $minlevel=="0") $checklevel=TRUE;
	else if (getlevel($myforum,"home")<$minlevel) $checklevel=FALSE;
	else $checklevel=TRUE;

	if (is_user() and !fd_is_admin() and versecid($myforum)) {
		if (trim($userblacklist)==""){
			if ($checklevel==TRUE) return TRUE;
			else return FALSE;
		}
		else {
			//controllo gli utenti nella black-list
			$bannedusers = array();
			$bannedusers = explode(",",$userblacklist);
			$counter = 0;
			foreach ($bannedusers as $banneduser){
				$bannedusers[$counter] = trim($banneduser);
				$counter++;
			}

			//controllo se è in elenco
			if (!in_array($myforum,$bannedusers)){
				if ($checklevel==TRUE) return TRUE;
				else return FALSE;
			}
			else return FALSE;
		}

	}
	else return FALSE;
}

/**
 * Richiama l'interfaccia per aggiungere un file
 * @author Aldo Boccacci
 * @since 0.7
 */
function user_addfile(){
edit_description_interface(array(),"userupload");

}

/**
 * La funzione che carica materialmente il file sul server
 * @author Aldo Boccacci
 * @since 0.7
 */
function user_upload(){
// 	include_once("shared.php");
	global $usermaxFileSize,$userfilelimit;

	if (!(get_n_waiting_files()<$userfilelimit)) fd_die("error! there are too files in the waiting list!",__FILE__,__LINE__);

	if (!check_var($usermaxFileSize,"digit")) fd_die("\$usermaxFileSize must be a digit! FDuser: ".__LINE__);

	//CONTROLLO TUTTE LE VARIABILI IN POST
	if (isset($_POST['path'])){
		if (fd_check_path($_POST['path'],"sections/","false")) $path = strip_tags($_POST['path']);
		else fd_die("\$_POST[\'path\'] is not valid. FDuser: ".__LINE__);
	}

	if (isset($_POST['filename'])){
		if (fd_check_path($_POST['filename'],"","false")) $filename = strip_tags($_POST['filename']);
		else fd_die("\$_POST[\'name\'] is not valid. FDuser: ".__LINE__);
	}

	if (isset($_POST['description'])){
		$description = strip_tags($_POST['description']);
	}

	if (isset($_POST['version'])){
		if (check_var(trim($_POST['version']),"text")) $version = trim(strip_tags($_POST['version']));
		else fd_die("\$_POST[\'version\'] is not valid. FDuser: ".__LINE__);
	}

		if (isset($_POST['userlabel'])){
		$userlabel = trim(getparam("userlabel",PAR_POST,SAN_FLAT));
		if (!check_var($userlabel,"text")) $userlabel="";
	}
	else $userlabel="";

	if (isset($_POST['uservalue'])){
		$uservalue = trim(getparam("uservalue",PAR_POST,SAN_FLAT));
		$uservalue = purge_html_string($uservalue);
	}
	else $uservalue ="";

	if (isset($_POST['md5'])){
		if (check_var(trim($_POST['md5']),"alnum")) $md5 = trim(strip_tags($_POST['md5']));
		else fd_die("\$_POST[\'md5\'] is not valid. FDuser: ".__LINE__);
	}

	if (isset($_POST['sha1'])){
		if (check_var(trim($_POST['sha1']),"alnum")) $sha1 = trim(strip_tags($_POST['sha1']));
		else fd_die("\$_POST[\'sha1\'] is not valid. FDuser: ".__LINE__);
	}

	if (isset($_POST['automd5post'])){
		$automd5post = trim(getparam("automd5post",PAR_POST,SAN_FLAT));
		if (!check_var($automd5post,"boolean"))	$automd5post="off";
	}
	else $automd5post="off";

	if (isset($_POST['autosha1post'])){
		$autosha1post = trim(getparam("autosha1post",PAR_POST,SAN_FLAT));
		if (!check_var($autosha1post,"boolean")) $autosha1post="off";
	}
	else $autosha1post="off";

// 	if (isset($_POST['hide'])){
// 		if (check_var(trim($_POST['hide']),"boolean")) $hide = trim(strip_tags($_POST['hide']));
// 		else fd_die("\$_POST[\'hide\'] is not valid. FDuser: ".__LINE__);
// 	}

	if (isset($_POST['showinblocks'])){
		if (check_var(trim($_POST['showinblocks']),"boolean")) $showinblocks = trim(strip_tags($_POST['showinblocks']));
		else fd_die("\$_POST[\'showinblocks\'] is not valid. FDuser: ".__LINE__);
	}

	if (isset($_POST['fdfilelevel'])){
		if (check_var(trim($_POST['fdfilelevel']),"digit") or trim($_POST['fdfilelevel'])!="---") $filelevel = trim(strip_tags($_POST['fdfilelevel']));
		else fd_die("\$_POST[\'level\'] is not valid. FDuser: ".__LINE__);
	}

	if(!user_can_upload()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	//CONTROLLO TUTTE LE VARIABILI IN POST E FILES
	$mod= "";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	if (trim($mod)!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (!isset($_FILES['fdfile'])) {
		echo "<br><br><div align=\"center\"><b>"._FDFILENOTSELECT."</b></div>";
		fd_die("File to upload not selected. FDuser:".__LINE__);
	}


	if (isset($_FILES['fdfile']['name']) and trim($_FILES['fdfile']['name'])==""){
		echo "<br><br><div align=\"center\">"._FDFILENOTSELECT;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die("File to upload not selected. FDuser:".__LINE__);
	}

	if (strlen($_FILES['fdfile']['name'])>40){
		fd_die("Error! The filename is toot long! (>40) FDuser:".__LINE__);
	}

	if ($_FILES['fdfile']['size']>$usermaxFileSize) {
		echo "<div align=\"center\"><br>"._FDTOOBIG;
		// Il file non è stato caricato perchè le sue dimensioni sono eccessive.<br>";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
		echo "</div>";
		fdlogf($_FILES['fdfile']['size'].": "._FDTOOBIG);
		fd_die("File to upload has size==0. FDuser:".__LINE__);

	}
	if ($_FILES['fdfile']['error']!=0){
		if ($_FILES['fdfile']['error']==1) echo _FDERROR1;
		else if ($_FILES['fdfile']['error']==2) echo _FDERROR2;
		else if ($_FILES['fdfile']['error']==3) echo _FDERROR3;
		else if ($_FILES['fdfile']['error']==4) echo _FDERROR4;
		else if ($_FILES['fdfile']['error']==6) echo _FDERROR6;
		else if ($_FILES['fdfile']['error']==7) echo _FDERROR7;

		fd_die("<br><br><b>FDuser:</b> upload error. (".$_FILES['fdfile']['error'].")");


	}
	if ($_FILES['fdfile']['size']==0) fd_die("<br><b>Error! </b>"._FDSIZE." file: 0 kb");


	//ULTIMO CONTROLLO ALL'ARRAY $_FILES
// 	if (fd_check_uploaded_file($_FILES['fdfile'],$maxFileSize,"false")!=TRUE){
// 		fd_die("The \$_FILES array contains invalid data! FDuser: ".__LINE__);
// 	}
	if (isset($_FILES['fdfile']['name'])){
		//impedisco di caricare file .php
		$info=array();
		$info = pathinfo($_FILES['fdfile']['name']);
		if (!isset($info['extension'])) $info['extension'] =" ";
		if (preg_match("/php/i",$info['extension'])) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

		//controllo il nome del file
		if (!fd_check_path($_FILES['fdfile']['name'],"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
		global $extensions;
		$extensions_array=array();
		$extensions_array = split(",",strtolower($extensions));
		if (!in_array(strtolower($info['extension']),$extensions_array)) {
			echo "<br><br><div align=\"center\">"._NOTVALIDEXT;
			echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die("",__FILE__,__LINE__);

		}

	}

	if (isset($_FILES['fdfile']['tmp_name'])){
		//impedisco di caricare file .php
		$info=array();
		$info = pathinfo($_FILES['fdfile']['tmp_name']);
		if (!isset($info['extension'])) $info['extension'] =" ";
		if (preg_match("/php/i",$info['extension'])) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

		//non posso inserire ..
// 		echo $_FILES['fdfile']['tmp_name'];
// 		if (fd_check_path($_FILES['fdfile']['tmp_name'],"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	}
	if(user_can_upload()){
// print_r($_FILES);
		if ($_FILES['fdfile']['name']<>""){
			//controllo che il file non sia già esistente
			if (file_exists("$path/".$_FILES['fdfile']['name'])){
				echo "<div align=\"center\"><br>"._FDUPLOADEXISTS."<br>";
				echo "<br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
				fd_die("",__FILE__,__LINE__);

			}
			global $separator_char;
			//genero il nome temporaneo del file
			$hiddenfilename = mt_rand().$separator_char.$_FILES['fdfile']['name'];
			//inserisci il path
// 			echo "$path/$hiddenfilename";
			if (user_can_upload()){
// 			echo $_FILES['fdfile']['tmp_name']."<br>"."$path/".$_FILES['fdfile']['name'];
			if (move_uploaded_file($_FILES['fdfile']['tmp_name'], "$path/$hiddenfilename")){
				echo "<div align=\"center\">"._FDUPLOADOK;

				echo "<br><br>"._FDWAITFORADMIN;
				//print_r($_FILES);
				echo "</div>";
			}
			else {
				echo "<div align=\"center\"><br>"._FDTOOBIG;
				// Il file non è stato caricato perchè le sue dimensioni sono eccessive.<br>";
				echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
				echo "</div>";
				fd_die("",__FILE__,__LINE__);
			}
			}
		}
		global $automd5,$autosha1;
		if (trim($md5)=="" and $automd5post=="on"){
			$md5 = md5_file("$path/$hiddenfilename");
		}
		if (trim($sha1)=="" and $autosha1post=="on"){
			$sha1 = sha1_file("$path/$hiddenfilename");
		}

		if ($showinblocks!="true") $showinblocks="false";


		$desc = array();
		$desc['name'] = $filename;
		$desc['desc'] = trim($description);
		$desc['version'] = $version;
		$desc['userlabel'] = $userlabel;
		$desc['uservalue'] = $uservalue;
		$desc['md5'] = $md5;
		$desc['sha1'] = $sha1;
		$desc['hits'] = 0;
		$desc['hide'] = "true";
		$desc['time'] = time();
		$desc['showinblocks'] = $showinblocks;
		$desc['filelevel'] = $filelevel;
		$desc['uploadedby'] = get_username();

		save_description("$path/$hiddenfilename",$desc);

		//solo dopo l'approvazione dell'admin!
// 		insert_in_max_download("$path/".$_FILES['fdfile']['name'],0);
		insert_in_waiting_list("$path/$hiddenfilename");
		fdlogf("$path/$hiddenfilename uploaded by ".get_username());

		//gestisco l'eventuale firma
// 		if ($_FILES['fdsig']['tmp_name']<>"") fd_upload_sig($path);

	}//fine is_admin(?)

		$path = preg_replace("/.*sections\//i", "", $path);
		echo "<br><br><div align=\"center\"><a href=\"index.php?mod=$path\"><b>"._FDRETURN."</b></a></div>";

}

/**
 * Verifica che l'array $_FILES['file'] passato come paramtero sia ok
 * tutti i controlli relativi dovranno essere spostati qua dentro
 * @param array $file_array l'array contenente i dati del file da uploadare
 * @param string $maxFileSize la dimensione massima del file da caricare
 * @param boolean $allow_php indica se permettere il caricamento di file con estensione php
 * @author Aldo Boccacci
 * @since 0.7
 */
function fd_check_uploaded_file($file_array,$maxFileSize,$allow_php="false"){
	if (!is_array($file_array)){
		fdlogf("error uploading file: \$file_array is not an array. FDuser:".__LINE__);
		return FALSE;
	}
	if (!check_var(trim($maxFileSize),"digit")){
		fdlogf("error uploading file: \$maxFileSize is not a digit. FDuser: ".__LINE__);
		return FALSE;
	}
	if (!preg_match("/^true$|^false$/i",$allow_php)){
		fdlogf("error uploading file: \$allow_php is not boolean. FDuser: ".__LINE__);
		return FALSE;
	}
	if (!isset($file_array['tmp_name'])){
		fdlogf("error uploading file: \$file_array[\'tmp_name\'] is not valid. FDuser: ".__LINE__);
		return FALSE;
	}
	if ($file_array['tmp_name']==""){
		fdlogf("error uploading file: \$file_array[\'tmp_name\'] is not valid. FDuser: ".__LINE__);
		return FALSE;
	}
	if (!is_uploaded_file($file_array['tmp_name'])){
		fdlogf("error uploading file: \$file_array[\'tmp_name\'] is not an uploaded file. FDuser: ".__LINE__);
		return FALSE;
	}

	if (!fd_check_path($file_array['name'],"","$allow_php")){
		fdlogf("error uploading file: \$file_array[\'name\'] is not valid. FDuser: ".__LINE__);

		return FALSE;
	}

	if ($file_array['size']=="0"){
		fdlogf("error uploading file: \$file_array[\'size\'] ==0. FDuser: ".__LINE__);

		return FALSE;
	}
	if ($file_array['error']!="0"){
		fdlogf("error uploading file: \$file_array[\'error\'] is".$file_array['error'].". FDuser: ".__LINE__);

		return FALSE;
	}
	return TRUE;

}


/**
 * Inserisce il file nella lista dei file in attesa di approvazione dall'amministratore
 * @param string $path il percorso del file da validare
 * @author Aldo Boccacci
 * @since 0.7
 */
function insert_in_waiting_list($path){
	if (!user_can_upload() and !fd_is_admin()) fd_die("You cannot insert files in waiting list! FDuser: ".__LINE__);
	global $userwaitingfile;
	if (!fd_check_path($path,"sections/","false")) fd_die("path isn't valid! FDuser: ".__LINE__);
	if (!fd_check_path($userwaitingfile,"","true")) fd_die("\$userwaitingfile isn't valid! FDuser: ".__LINE__);

	//controllo l'esistenza della sezione di statistica
	if (!file_exists(_FN_VAR_DIR."/fdplus/")) fn_mkdir(_FN_VAR_DIR."/fdplus/",0777);
	//idem per il file con le statistiche
	if (!file_exists(_FN_VAR_DIR."/fdplus/$userwaitingfile")){
		$string= "<?xml version='1.0' encoding='UTF-8'?>\n<userwaitaingfiles>\n</userwaitingfiles>";

		fnwrite(_FN_VAR_DIR."/fdplus/$userwaitingfile",$string,"w",array("nonull"));
	}

	$datastring="";
	$datastring = get_xml_element("userwaitingfiles",get_file(_FN_VAR_DIR."/fdplus/$userwaitingfile"));
	$userwaitingarray=array();
	$userwaitingarray= get_xml_array("file",$datastring);

// 	print_r($userwaitingarray);

	$newstring= "<userwaitingfiles>";

	//inserisco il file corrente
	if (file_exists($path) and is_file($path))
	$newstring .="\n\t<file>$path</file>";

	foreach ($userwaitingarray as $userwaitingelement){
// 		$userwaitingelement = get_xml_element("file",$userwaitingelement);
		if (file_exists(trim($userwaitingelement)))
		$newstring .="\n\t<file>$userwaitingelement</file>";
	}

	$newstring .= "\n</userwaitingfiles>";
	if (preg_match("/\<\?/",$newstring) or preg_match("/\?\>/",$newstring)) fd_die("You cannot insert php tags in the waiting list file! FDuser: ".__LINE__);

	fnwrite(_FN_VAR_DIR."/fdplus/$userwaitingfile","<?xml version='1.0' encoding='UTF-8'?>\n".$newstring,"w",array("nonull"));
}

/**
 * Resituisce il numero di file in attesa di approvazione
 * @return il numero di file in attesa di approvazione
 * @author Aldo Boccacci
 * @since 0.7
 */
function get_n_waiting_files(){

	return count(get_waiting_files());

}

/**
 * Restituisce l'elenco dei files in attesa di approvazione.
 * @return un array contenete l'elenco dei file in attesa di validazione
 * @author Aldo Boccacci
 * @since 0.7
 */
function get_waiting_files(){
	global $userwaitingfile;
	if (!fd_check_path($userwaitingfile,"","true")) fd_die(_NONPUOI.__LINE__);

	if (!file_exists(_FN_VAR_DIR."/fdplus/")) fn_mkdir(_FN_VAR_DIR."/fdplus/",0777);
	//idem per il file con le statistiche
	if (!file_exists(_FN_VAR_DIR."/fdplus/$userwaitingfile")){
		$string= "<?xml version='1.0' encoding='UTF-8'?>\n<userwaitingfiles>\n</userwaitingfiles>";

		fnwrite(_FN_VAR_DIR."/fdplus/$userwaitingfile",$string,"w",array("monull"));
	}
	$datastring="";
	$datastring = get_xml_element("userwaitingfiles",get_file(_FN_VAR_DIR."/fdplus/$userwaitingfile"));
	$userwaitingarray=array();
	$userwaitingarray= get_xml_array("file",$datastring);
	$arrayok=array();
	foreach ($userwaitingarray as $userwaitingelement){
// 		$userwaitingelement = get_xml_element("file",$userwaitingelement);
		if (file_exists(trim($userwaitingelement))) $arrayok[] = preg_replace("/\/\//","/",$userwaitingelement);
	}

	return $arrayok;
}

/**
 * L'interfaccia destinata all'amministratore per validare (o eliminare) i file in attesa di approvazione.
 * @author Aldo Boccacci
 * @since 0.7
 */
function publish_interface(){
	if (!fd_is_admin()) fd_die(_NONPUOI." FDuser: ".__LINE__);
	$mod= "";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	if (trim($mod)!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);


	global $userwaitingfile,$separator_char;
	if (!fd_check_path($userwaitingfile,"","true")) fd_die(_NONPUOI.__LINE__);

	if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
}

	if (!file_exists(_FN_VAR_DIR."/fdplus/")) fn_mkdir(_FN_VAR_DIR."/fdplus/",0777);
	//idem per il file con le statistiche
	if (!file_exists(_FN_VAR_DIR."/fdplus/$userwaitingfile")){
		$string= "<?xml version='1.0' encoding='UTF-8'?>\n<userwaitingfiles>\n</userwaitingfiles>";

		fnwrite(_FN_VAR_DIR."/fdplus/$userwaitingfile",$string,"w",array("nonull"));
	}

	$datastring="";
	$datastring = get_xml_element("userwaitingfiles",get_file(_FN_VAR_DIR."/fdplus/$userwaitingfile"));
	$userwaitingarray=array();
	$userwaitingarray= get_xml_array("file",$datastring);
	echo "<b>I seguenti file sono in attesa di validazione: </b><br>(Potrete modificare le descrizioni dopo la pubblicazione)";
	foreach ($userwaitingarray as $userwaitingelement){
// 		$userwaitingelement = get_xml_element("file",$userwaitingelement);
		if (!file_exists($userwaitingelement)) continue;
		fd_view_file($userwaitingelement);
		}
}

/**
 * Pubblica il file indicato come parametro (Nello stesso tempo lo rimuove dalla lista dei file in attesa
 * di approvazione)
 * @param string $path il percorso del file da approvare.
 * @author Aldo Boccacci
 * @since 0.7
 */
function publish_file($path){
	if (!fd_is_admin()) fd_die(_NONPUOI." FDuser: ".__LINE__);
	if (!fd_check_path($path,"sections/","false")) fd_die("\$path isn't valid! FDuser: ".__LINE__);
	$myforum ="";
	$myforum=get_username();


	global $separator_char;
	$newdir = dirname($path);
	$newname="";
	$newname = preg_replace("/^[0-9]+\\$separator_char/i","",basename($path));

	$newpath = "$newdir/$newname";

	if (file_exists($newpath)){
		echo "<b>"._ATTENTION."!</b> "._THEFILE." <b>$newpath</b> esiste già. <br>
		Sarà mantenuto il file temporaneo in attesa che l'amministratore lo modifichi manualmente.";
		$newpath = "$newdir/TOGLIMI-$newname";
	}


	rename($path,$newpath);
	rename("$path.description","$newpath.description");

	//rimuovo dalla lista dei file da caricare
	//inserire il vecchio indirizzo (non + esistente) equivale ad eliminare
	insert_in_waiting_list("$path");
	insert_in_max_download($newpath,"0");
	fdlogf("$newpath published by ".get_username());
	hide_file($newpath,"false");


}

/**
 * Elimina il file passato come parametro
 * @param string $path il percorso del file da eliminare
 * @author Aldo Boccacci
 * @since 0.7
 */
function fd_delete_file($path){
	if (!fd_check_path($path,"sections/","false")) fd_die("\$path isn't valid. FDuser: ".__LINE__);
	confermaelimina($path);
	//solo se è stato effettivamente eliminato...
	if (!file_exists($path)) insert_in_waiting_list($path);

}

/**
 * Restituisce true se il file è stato proposto da un utente e deve ancora essere
 * approvato dall'amministratore
 * @return TRUE se il file si trova nella lista dei file caricati da un utente in attesa di validazione,
 *         FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 0.7
 */
function user_uploaded($path){
	if (!fd_check_path($path,"sections/","false")) fd_die("\$path isn't valid! FDuser: ".__LINE__);
	if (in_array($path,get_waiting_files())) return TRUE;
	else return FALSE;
}

?>