<?php
if (preg_match("/fdurl.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call fdurl.php!",__FILE,__LINE);
}

/**
 * Mette on-line il nuovo file selezionato
 * @param string $maxFileSize la dimensione massima del file da caricare
 * @param string $path il percorso in cui caricare il file
 * @param string $filename il nome del file
 * @param string $description la descrizione del file
 * @param string $version la versione del file
 * @param string $md5 la somma md5 del file
 * @param string $extensions l'elenco delle estensioni ricnonosciute
 * @param boolean $showinblocks true se il file deve essere mostrato nei blocchi
 * @author Aldo Boccacci
 */
function uploadurl(){
//$maxFileSize, $path ,$filename, $description, $version, $md5, $sha1, $extensions, $automd5,$autosha1,$showinblocks,$filelevel){
	global $maxFileSize,$extensions;

	if(!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (!check_var($maxFileSize,"digit")) fd_die("\$maxFileSize (".strip_tags($maxFileSize).") must be a digit! FDadmin: ".__LINE__);

	if (isset($_POST['filename'])){
		$filename = trim(getparam("filename",PAR_POST,SAN_FLAT));
		if (!check_var($filename,"text")) $filename="";
	}
	else $filename ="";

	if (isset($_POST['description'])){
		$description = trim(getparam("description",PAR_POST,SAN_NULL));
		$description = purge_html_string($description);
	}
	else $description ="";

	if (isset($_POST['version'])){
		$version = trim(getparam("version",PAR_POST,SAN_FLAT));
		if (!check_var($version,"text")) $version="";
	}
	else $description="";

	if (isset($_POST['userlabel'])){
		$userlabel = trim(getparam("userlabel",PAR_POST,SAN_FLAT));
		if (!check_var($userlabel,"text")) $userlabel="";
	}
	else $userlabel="";

	if (isset($_POST['uservalue'])){
		$uservalue = trim(getparam("uservalue",PAR_POST,SAN_NULL));
		$uservalue = purge_html_string($uservalue);
	}
	else $uservalue ="";

	if (isset($_POST['md5'])){
		$md5 = trim(getparam("md5",PAR_POST,SAN_FLAT));
		if (!check_var($md5,"alnum")) $md5="";
	}
	else $md5="";

	if (isset($_POST['sha1'])){
		$sha1 = trim(getparam("sha1",PAR_POST,SAN_FLAT));
		if (!check_var($sha1,"text")) $sha1="";
	}
	else $sha1="";

	if (isset($_POST['automd5'])){
		$automd5 = trim(getparam("automd5",PAR_POST,SAN_FLAT));
		if (!check_var($automd5,"boolean")) $automd5="false";
	}
	else $automd5="false";

	if (isset($_POST['autosha1'])){
		$autosha1 = trim(getparam("autosha1",PAR_POST,SAN_FLAT));
		if (!check_var($autosha1,"boolean")) $autosha1="false";
	}
	else $autosha1="false";

	if (isset($_POST['showinblocks'])){
		$showinblocks = trim(getparam("showinblocks",PAR_POST,SAN_FLAT));
		if (!check_var($showinblocks,"boolean")) $showinblocks="true";
	}
	else $showinblocks="false";

	if (isset($_POST['fdfilelevel'])){
		$filelevel = trim(getparam("fdfilelevel",PAR_POST,SAN_FLAT));
		if (!check_var($filelevel,"digit") and $filelevel!="-1") $filelevel="-1";
	}
	else $filelevel ="-1";

	if (isset($_POST['fdfile'])){
		$fdfile = trim(getparam("fdfile",PAR_POST,SAN_FLAT));
		if (!check_path($fdfile,"sections/","false") and $fdfile!="") fd_die("fdfile param (".strip_tags($fdfile).") is not valid! FDurl: ".__LINE__);
	}
	else fd_die("fdfile param no set! FDurl: ".__LINE__);

// 	if (isset($_POST['fdurl'])){
// 		$fdurl = trim(getparam("fdurl",PAR_POST,SAN_FLAT));
// 		if (!check_var($fdurl,"text")) fd_die("fdurl param (".strip_tags($fdurl).") is not valid! FDurl: ".__LINE__);
// 	}
// 	else fd_die("fdurl param not set! FDurl: ".__LINE__);

	if (isset($_POST['path'])){
		$path = trim(getparam("path",PAR_POST,SAN_FLAT));
		if (!check_path($path,"sections/","false")) fd_die("path param (".strip_tags($path).") is not valid! FDurl: ".__LINE__);
	}
	else fd_die("path param not set! FDurl: ".__LINE__);

	//devo essere in none_Fdplus
	$mod= "";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	if (trim($mod)!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);


	if (isset($_POST['fdurl']) and trim($_POST['fdurl'])!=""){
		$fdurl = getparam("fdurl",PAR_POST,SAN_FLAT);

	}
	else {
		echo "<br><br><div align=\"center\">"._FDFILENOTSELECT;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
		fd_die("File to upload not selected. FDadmin:".__LINE__);
	}

	//controllo che l'estensione del file sia permessa
	$info = array();
	$info = pathinfo($fdurl);
	if (!isset($info['extension'])) $info['extension'] =" ";
	if (preg_match("/php/i",$info['extension'])) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$extensions_array=array();
	$extensions_array = split(",",strtolower($extensions));
	if (!in_array(strtolower($info['extension']),$extensions_array)) {
		echo "<br><br><div align=\"center\">"._NOTVALIDEXT;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
		fd_die("Invalid extension! FDurl:".__LINE__);

	}

	//creo il nome del file
	$file = basename($fdurl);
	$file = preg_replace("/\?.*/i","",$file);

	//controllo che nella sezione non ci sia già un file con lo stesso nome
	if (file_exists("$path/$file")){
		echo "<div align=\"center\"><br>"._FDUPLOADEXISTS."<br>";
		echo "<br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
		return;

	}

	//creo il file vuoto di supporto

// 	$filetmp = fopen("$path/$file","w");
	fnwrite("$path/$file","DO NOT REMOVE THIS FILE!","w");
// 	fclose($filetmp);


	$mod= "";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	if (trim($mod)!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(fd_is_admin()){
		if ($showinblocks!="true") $showinblocks="false";


		$data= array();
		$data['name'] = $filename;
		$data['desc'] = trim($description);
		$data['version'] = $version;
		$data['userlabel'] = $userlabel;
		$data['uservalue'] = $uservalue;
		$data['md5'] = $md5;
		$data['sha1'] = $sha1;
		$data['showinblocks'] = $showinblocks;
		$data['level'] = $filelevel;
		$data['uploadedby'] = get_username();
		$data['url'] = $fdurl;
		$data['time'] = filectime("$path/$file");

		save_description("$path/$file",$data);
		//gestisco l'eventuale firma
		if ($_FILES['fdsig']['tmp_name']<>"") fd_upload_sig("$path/$file");
		if ($_FILES['fdscreenshot']['tmp_name']<>"") fd_upload_screenshot("$path/$file");


	}//fine fd_is_admin(?)

		$path = preg_replace("/http:\/\//i","",$path);
		$path = preg_replace("/.*sections\//i", "", $path);
		echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDARCHIVERETURN."</b></a></div>";
}

/**
 * Salva le modifiche apportate
 *
 * @author Aldo Boccacci
 * @since 0.8
 */
function saveurl(){

if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

global $maxFileSize,$extensions,$extsig,$extscreenshot;
	//controllo tutte le variabili esterne
	if (!check_var($maxFileSize,"digit")) fd_die("\$maxFileSize (".strip_tags($maxFileSize).") must be a digit! FDadmin: ".__LINE__);

	if (isset($_POST['filename'])){
		$filename = trim(getparam("filename",PAR_POST,SAN_FLAT));
		if (!check_var($filename,"text")) $filename="";
	}
	else $filename ="";

	if (isset($_POST['description'])){
		$description = trim(getparam("description",PAR_POST,SAN_NULL));
		$description = purge_html_string($description);
	}
	else $description ="";

	if (isset($_POST['version'])){
		$version = trim(getparam("version",PAR_POST,SAN_FLAT));
		if (!check_var($version,"text")) $version="";
	}
	else $version="";

	if (isset($_POST['userlabel'])){
		$userlabel = trim(getparam("userlabel",PAR_POST,SAN_FLAT));
		if (!check_var($userlabel,"text")) $userlabel="";
	}
	else $userlabel="";

	if (isset($_POST['uservalue'])){
		$uservalue = trim(getparam("uservalue",PAR_POST,SAN_NULL));
		$uservalue = purge_html_string($uservalue);
	}
	else $uservalue ="";

	if (isset($_POST['md5'])){
		$md5 = trim(getparam("md5",PAR_POST,SAN_FLAT));
		if (!check_var($md5,"alnum")) $md5="";
	}
	else $md5="";

	if (isset($_POST['sha1'])){
		$sha1 = trim(getparam("sha1",PAR_POST,SAN_FLAT));
		if (!check_var($sha1,"text")) $sha1="";
	}
	else $sha1="";

	if (isset($_POST['automd5'])){
		$automd5 = trim(getparam("automd5",PAR_POST,SAN_FLAT));
		if (!check_var($automd5,"boolean")) $automd5="false";
	}
	else $automd5="false";

	if (isset($_POST['autosha1'])){
		$autosha1 = trim(getparam("autosha1",PAR_POST,SAN_FLAT));
		if (!check_var($autosha1,"boolean")) $autosha1="false";
	}
	else $autosha1="false";


	if (isset($_POST['showinblocks'])){
		$showinblocks = trim(getparam("showinblocks",PAR_POST,SAN_FLAT));
		if (!check_var($showinblocks,"boolean")) $showinblocks="true";
	}
	else $showinblocks="false";

	if (isset($_POST['enablerating'])){
		$enablerating = trim(getparam("enablerating",PAR_POST,SAN_FLAT));
		if (!check_var($enablerating,"boolean")) $enablerating="$defaultvoteon";
		if ($enablerating=="on") $enablerating="1";
	}
	else $enablerating="0";

	if (isset($_POST['fdfilelevel'])){
		$filelevel = trim(getparam("fdfilelevel",PAR_POST,SAN_FLAT));
		if (!check_var($filelevel,"digit") and $filelevel!="-1") $filelevel="-1";
	}
	else $filelevel ="-1";

	if (isset($_POST['fdfile'])){
		$fdfile = trim(getparam("fdfile",PAR_POST,SAN_FLAT));
		if (!check_path($fdfile,"sections/","false")) fd_die("fdfile param (".strip_tags($fdfile).") is not valid! FDurl: ".__LINE__);
	}
	else fd_die("fdfile param no set! FDurl: ".__LINE__);

	if (isset($_POST['fdurl'])){
		$fdurl = trim(getparam("fdurl",PAR_POST,SAN_FLAT));
		if (!check_var($fdurl,"text")) fd_die("fdurl param (".strip_tags($fdurl).") is not valid! FDurl: ".__LINE__);
	}
	else fd_die("fdurl param not set! FDurl: ".__LINE__);

	if (isset($_POST['path'])){
		$path = trim(getparam("path",PAR_POST,SAN_FLAT));
		if (!check_path($path,"sections/","false")) fd_die("path param (".strip_tags($path).") is not valid! FDurl: ".__LINE__);
	}
	else fd_die("path param not set! FDurl: ".__LINE__);

	//devo essere in none_Fdplus
	$mod= "";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	if (trim($mod)!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$olddescription = array();
	$olddescription = load_description($fdfile);

	//se cambio l'url adatto anche i file associati
	if ($olddescription['url']!=$fdurl){
		$filepathok = $path."/".basename(preg_replace("/\?.*/i","",$fdurl));
		rename($fdfile,$filepathok);
		rename($fdfile.".description",$filepathok.".description");
		if (file_exists("$fdfile.$extsig")){
			rename("$fdfile.$extsig","$filepathok.$extsig");
		}
		if (file_exists("$fdfile.$extscreenshot")){
			rename("$fdfile.$extscreenshot","$filepathok.$extscreenshot");
		}
	}
	else {
		$filepathok = $fdfile;
	}

	//aggiusto gli "a capo": solo se non è attivo fckeditor
	if (!file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
		$description =str_replace("\n", "<br>", $description);
	}
	else if (!preg_match("/gecko/i",$_SERVER['HTTP_USER_AGENT']) and !preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])){
		$description =str_replace("\n", "<br>", $description);
	}


	$data= array();
	$data['name'] = $filename;
	$data['desc'] = trim($description);
	$data['version'] = $version;
	$data['userlabel'] = $userlabel;
	$data['uservalue'] = $uservalue;
	$data['md5'] = $md5;
	$data['sha1'] = $sha1;
	$data['hits'] = $olddescription['hits'];
	$data['hide'] = $olddescription['hide'];
	$data['showinblocks'] = $showinblocks;
	$data['enablerating'] = $enablerating;
	$data['level'] = $filelevel;
	$data['uploadedby'] = $olddescription['uploadedby'];
	$data['url'] = $fdurl;
	if ($olddescription['url']!=$fdurl){
		$data['time'] = filectime($filepathok);
	}
	else $data['time'] = $olddescription['time'];

	//MODIFICA PER VOTO
	$data['totalvote'] = trim($olddescription['totalvote']);
	$data['totalscore'] = trim($olddescription['totalscore']);

	save_description($filepathok, $data);

	insert_in_max_download($filepathok,get_xml_element("hits",get_file($filepathok.".description")));


	fdlogf("$filepathok edited by ".get_username());


	//permetti di tornare indietro.
	echo "<br><br><div align=\"center\">"._FDEDITDONE."<br><br>";
	$path = dirname($fdfile);
	$path = preg_replace("/.*sections\//i", "", $path);
	echo "<a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDRETURN."</b></a></div>";

	if ($_FILES['fdsig']['tmp_name']<>""){
		fd_upload_sig($fdfile,"edit");
	}
	else {
		//do nothing
	}
	if ($_FILES['fdscreenshot']['tmp_name']<>""){
		fd_upload_screenshot($fdfile,"edit");
	}
	else {
		//do nothing
	}
}

?>