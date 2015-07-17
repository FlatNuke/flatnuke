<?php
//lo script non può essere richiamato direttamente

if (preg_match("/fdadmin.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call fdadmin.php!",__FILE,__LINE);
}

//impedisco l'include del file da parte dei non amministratori.
if (!fd_is_admin()) fd_die("Only admins can include the file FDadmin.php. FDadmin: ".__LINE__);

/**
 * Mostra i controlli di amministrazione per il file specificato.
 *
 * Questa funzione crea tutti i link di amministrazione da inserire in fondo alla visualizzazione
 * del file.
 *
 * @param string $path il percorso del file da amministrare
 * @param array l'array con la descrizione del file
 * @author Aldo Boccacci
 * @since 0.7
 */
function file_admin_panel($path,$description){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($path,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$mod="";
	$mod =_FN_MOD;
// 	$mod = trim($mod);
	if ($mod=="") return;
// 	if (!fd_check_path($mod,"","false")) return;

	echo "<tr><td>&nbsp;</td></tr>";

	//gestisco i file in attesa di validazione
	if ($mod=="none_Fdplus" or user_uploaded($path)) {
		global $separator_char;
		$correctname ="";
		$correctname = preg_replace("/[0-9]+\\$separator_char/","",$path);
		echo "<tr><th colspan=\"2\"><div align=\"center\"><br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
		<input type=\"hidden\" name=\"fdaction\" value=\"publishuserfile\" readonly=\"readonly\">
		<input type=\"hidden\" name=\"fdfile\" value=\"$path\" readonly=\"readonly\">
		<input type=\"SUBMIT\" value=\""._FDPUBLISH." ".basename($correctname)."\"></form></div>";

		echo "<div align=\"center\"><br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
		<input type=\"hidden\" name=\"fdaction\" value=\"deleteuserfile\" readonly=\"readonly\">
		<input type=\"hidden\" name=\"fdfile\" value=\"$path\" readonly=\"readonly\">
		<input type=\"SUBMIT\" value=\""._FDDELETE." ".basename($correctname)."\"></form></div></th></tr>";
		return;
	}


	global $extsig,$archivedir,$extscreenshot;

	$filename=$path;

// 	$description=array();
// 	$description = load_description($path);

	echo "<tr>";

				if ($description['hide'] != "true"){
					if(is_writable($filename.".description")){
						echo "<th colspan=\"2\"><div align='center'><a href=\"index.php?mod=none_Fdplus&amp;fdaction=hide&amp;value=true&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDHIDETITLE.basename($filename)."\">"._FDHIDE."</a> | ";
					}
					else {
						echo "<th colspan=\"2\"><span style=\"color : #ff0000; text-decoration : line-through;\">"._FDHIDE."</span> | ";
					}
				}

				if ($description['hide'] == "true"){
					if(is_writable($filename.".description")){
						echo "<th colspan=\"2\"><a href=\"index.php?mod=none_Fdplus&amp;fdaction=hide&amp;value=false&amp;fdfile=".rawurlencodepath($filename)."\" style=\"color : #ff0000;\" title=\""._FDSHOWTITLE.basename($filename)."\">"._FDSHOW."</a> | ";
					}
					else {
						echo "<th colspan=\"2\"><span style=\"color : #ff0000; text-decoration : line-through;\">"._FDSHOW."</span> | ";
					}
				}
				//pulsante per rinominare
				if (is_writable($filename) and is_writable($filename.".description")
				and is_writable(dirname($filename))
				and (!file_exists($filename.".$extsig") or is_writable($filename.".$extsig"))
				and (!file_exists($filename.".$extscreenshot") or is_writable($filename.".$extscreenshot"))){
					echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=renameinterface&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDRENAMETITLE.basename($filename)."\">"._FDRENAME."</a> | ";
				}
				else {
					echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDRENAME."</span> | ";
				}

				//pulsante per spostare

				if (is_writable($filename) and is_writable($filename.".description") and (!file_exists($filename.".$extsig") or (is_writeable($filename.".$extsig")))
				and (!file_exists($filename.".$extscreenshot") or is_writable($filename.".$extscreenshot"))) {

					echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=moveinterface&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDMOVETITLE.basename($filename)."\">"._FDMOVE."</a> | ";

				}
				else {
					echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDMOVE."</span> | ";
				}

				//fine pulsante per spostare


				//pulsante per archiviare/ripristinare
				$lastdir="";
				//controllo se ci troviamo in una dir di archivio
				$lastdir = strrchr(dirname($filename),"/");
				if (preg_match("/".$archivedir."/i",$lastdir)){
					if (is_writable($filename) and is_writable($filename.".description")
					and is_writable(dirname($filename))
					and is_writable(preg_replace("/$archivedir$/i","",dirname($filename)))
					and (!file_exists($filename.".$extsig") or is_writable($filename.".$extsig"))
					and (!file_exists($filename.".$extscreenshot") or is_writable($filename.".$extscreenshot"))){
						echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=ripristina&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDRESTORETITLE.basename($filename)."\">"._FDRESTORE."</a> | ";
					}
					else {
						echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDRESTORE."</span> | ";
					}
				}
				else if (!preg_match("/$archivedir/i",$lastdir)){
					if (is_writable($filename) and is_writable($filename.".description") and is_writable(dirname($filename))  and (!file_exists($filename.".$extsig") or is_writable($filename.".$extsig"))
					and (!file_exists($filename.".$extscreenshot") or is_writable($filename.".$extscreenshot"))){
						if (is_dir(dirname($filename)."/$archivedir") and is_writable(dirname($filename)."/$archivedir")){
							echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=archive&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDARCHIVETITLE.basename($filename)."\">"._FDARCHIVE."</a> | ";
					 	}
					 	else {
					   		if (!is_dir(dirname($filename)."/$archivedir")){
					   		echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=archive&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDARCHIVETITLE.basename($filename)."\">"._FDARCHIVE."</a> | ";
					    		}
					    	else echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDARCHIVE."</span> | ";
					    	}
					}
					else {
						echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDARCHIVE."</span> | ";
					}
				}

				if (is_writable($filename) and is_writable($filename.".description")
				and is_writable(dirname($filename))
				and (!file_exists($filename.".$extsig") or is_writable($filename.".$extsig"))
				and (!file_exists($filename.".$extscreenshot") or is_writable($filename.".$extscreenshot"))){
					echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=modify&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDEDITTITLE.basename($filename)."\">"._FDEDIT."</a> | ";
				}
				else {
					echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDEDIT."</span> ";
				}

				//Opzione elimina
				if (is_writable($filename) and is_writable($filename.".description") and is_writable(dirname($filename)) and (!file_exists($filename.".$extsig") or is_writable($filename.".$extsig"))
				and (!file_exists($filename.".$extscreenshot") or is_writable($filename.".$extscreenshot"))){
					echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=confirmDelete&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDDELETETITLE.basename($filename)."\">"._FDDELETE." file</a>";
				}
				else {
					echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDDELETE." file</span>";
				}

				if (file_exists($filename.".$extsig") ){
					if (is_writable($filename.".$extsig")){
						echo " | <a href=\"index.php?mod=none_Fdplus&amp;fdaction=confirmDeleteSign&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDDELETETITLE.basename($filename).".$extsig\">"._FDDELETESIG."</a>";
					}
					else {
						echo "<span style=\"color : #ff0000; text-decoration : line-through;\">"._FDDELETESIG."</span>";
					}
				}

				if (file_exists($filename.".png") ){
					if (is_writable($filename.".png")){
						echo " | <a href=\"index.php?mod=none_Fdplus&amp;fdaction=confirmDeleteScreenshot&amp;fdfile=".rawurlencodepath($filename)."\" title=\""._FDDELETETITLE.basename($filename).".$extscreenshot\">"._FDDELETESCREEN."</a>";
					}
					else {
						echo " | <span style=\"color : #ff0000; text-decoration : line-through;\">"._FDDELETESCREEN."</span>";
					}
				}
				
				echo "<br /><hr style=\"width : 60%;\">";
				
				echo "</div></th>";
				echo "</tr>";
}

/**
 * Questa funzione si occupa di caricare la firma gpg
 *
 * Funzione che si occupa di tutte le operazioni per il caricamento della firma associata al file.
 *
 * @param string $dir la cartella in cui caricare la firma
 * @param string $mode "upload" se stiamo caricando un nuovo file, "edit" se ne stiamo modificando
 *                     uno già presente
 * @author Aldo Boccacci
 * @since 0.7
 */

function fd_upload_sig($file,$mode="upload"){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	//CONTROLLO PARAMETRI
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!preg_match("/^upload$|^edit$/i",$mode)) {
		fd_die("Error: \$mode value is invalid: must be \"upload\" or \"edit\". FDadmin: ".__LINE__);
	}
	global $extsig;
	if ($extsig=="") $extsig="sig";
	//FINE CONTROLLO PARAMETRI
	$dir="";
	$dir = dirname($file);
	if (!file_exists($file)){
		fdlogf("<b>Error! </b> the file $file doesn't exists! The sign file will not be uploaded! FDadmin: ".__LINE__);
		echo "<b>Error! </b> the file $file doesn't exists! The sign file will not be uploaded! FDadmin: ".__LINE__;
		return;
	}

	if (basename($file)!=preg_replace("/\.$extsig$/i","",basename($_FILES['fdsig']['name']))){
		fdlogf("<b>Error! </b> the sig file is called ".basename($_FILES['fdsig']['name'])." but the correspondant file is called ".basename($file).". FDadmin: ".__LINE__);
		echo "<b>Error! </b> the sig file is called ".basename($_FILES['fdsig']['name'])." but the correspondant file is called ".basename($file).". FDadmin: ".__LINE__."<br>The sign file will not be uploaded.";
		return;
	}

	if (!isset($_FILES['fdsig'])) {
		$message="";
		$message="<b>Error! </b> ".'$_FILES[\'fdsig\'] is not set! FDadmin: '.__LINE__;
		fdlogf($message);
		return;
	}

	if (preg_match("/\.php*$|\.php$/i",$_FILES['fdsig']['name'])){
// 		echo "The extension of the sig file is \".php\", not \".sig\"";
		fd_die("The extension of the sig file is \".php\", not \".$extsig\" FDadmin: ".__LINE__);
	}

	if (isset($_FILES['fdsig']['name']) and trim($_FILES['fdsig']['name'])==""){
		echo "<br><br><div align=\"center\">"._FDFILENOTSELECT;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	}

	if ($_FILES['fdsig']['size']>300) {
		fd_die("The sig file is too big! ".$dir."/".$_FILES['fdsig']['name'].".$extsig"."FDadmin: ".__LINE__);
		die_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	}


	if ($_FILES['fdsig']['error']!=0){
		if ($_FILES['fdfile']['error']==1) echo _FDERROR1;
		else if ($_FILES['fdsig']['error']==2) echo _FDERROR2;
		else if ($_FILES['fdsig']['error']==3) echo _FDERROR3;
		else if ($_FILES['fdsig']['error']==4) echo _FDERROR4;
		else if ($_FILES['fdsig']['error']==6) echo _FDERROR6;
		else if ($_FILES['fdsig']['error']==7) echo _FDERROR7;

		fd_die("<br><br><b>FDadmin:</b> upload error.");


	}
	if ($_FILES['fdsig']['size']==0) fd_die("<br><b>Error! </b>"._FDSIZE." file: 0 kb FDadmin: ".__LINE__);

	if (file_exists("$file.$extsig")) unlink("$file.$extsig");

	//controllo per codice php
	if (preg_match("/\<\?|\?\>/",get_file($_FILES['fdsig']['tmp_name']))){
		fd_die("The sign file contains php tags! "._FDNONPUOI.basename(__FILE__).": ".__LINE__);
	}

	if (!fd_check_path($dir."/".$_FILES['fdsig']['name'],"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	//effettuo l'upload
	fd_upload_file($_FILES['fdsig'],$dir."/".$_FILES['fdsig']['name']);
}

/**
 * elimina il file da scaricare con il corrispettivo .description
 * @param string $file il file da eliminare
 * @author Aldo Boccacci
 */
function elimina($file){

	include_once("shared.php");
	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod = trim($mod);
	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if(fd_is_admin()){
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!file_exists($file.".description")) fd_die("The file $file is not managed by FD+. FDadmin: ".__LINE__);
// 		/*include "config.php";*/ global $siteurl;

		if (unlink($file)&&unlink("$file.description")){
			echo "<div align=\"center\"><br><br>"._FDDELOK."<br><b>".preg_replace("/^sections\//i","",$file)."</b> </div>";
			//._FDAND."<br><b> ".$file.".description</b><br>";
			//l'utente che ha modificato il file
			$myforum=get_username();


			fdlogf("$file deleted by $myforum");

		}

		global $extsig;
		if ($extsig=="") $extsig="sig";
		if (file_exists($file.".$extsig")){
			if (unlink($file.".$extsig")){
				fdlogf("succesfully deleted gpg sign: $file.$extsig");
			}
			else fdlogf("I was no able to delete the gpg sign: $file.$extsig");
		}

		global $extscreenshot;
		if ($extscreenshot=="") $extscreenshot="png";
		if (file_exists($file.".$extscreenshot")){
			if (unlink($file.".$extscreenshot")){
				fdlogf("succesfully deleted screenshot: $file.$extsig");
			}
			else fdlogf("I was no able to delete the screenshot: $file.$extsig");
		}
		//permetti di ritornare
		$path = preg_replace("/http:\/\//i","",dirname($file));
		#$path = preg_replace("/$siteurl/", "", $path);
		$path = preg_replace("/\/sections\//i", "", $path);
		$path = preg_replace("/sections\//i", "", $path);
		echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDRETURN."</b></a></div>";

	}
}


/**
 * Prima di eliminare definitivamente un file, chiedo conferma dell'operazione
 * @param string $file il file da eliminare
 * @author Aldo Boccacci
 */
function confermaElimina($file){
// 	include("shared.php");
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!file_exists($file.".description")) fd_die("The file $file is not managed by FD+. FDadmin: ".__LINE__);
	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod = trim($mod);
	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	?>
	<div style="text-align:center">
	<br><?php echo _FDDELSURE; ?><b><?php echo preg_replace("/^sections\//i","",$file); ?></b>?
	<br><br>
	<a href="index.php?mod=none_Fdplus&amp;fdaction=delete&amp;fdfile=<?php echo rawurlencodepath($file); ?>"><?php echo _FDDEL; ?></a>
	<a href="javascript:history.back()"><?php echo _FDCANC; ?></a>
	</div>
	<?php
}

/**
 * Questa funzione consente di archiviare in una sottocartella un file messo in download.
 * Utile per tenere traccia delle vecchie versioni dei nostri file.
 * @param string $file il file da archiviare
 * @param string $archivedir la cartella di archivio
 * @author Aldo Boccacci
 */
function archivia($file,$archivedir){
// 	include_once("shared.php");
	if(!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($archivedir,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod = trim($mod);
	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(fd_is_admin()){
// 		/*include "config.php";*/ global $siteurl;
		$path="";
		$path = dirname($file);
		if (!file_exists("$path/$archivedir/section.php")){

			fn_mkdir("$path/$archivedir/",0777);
			fnwrite("$path/$archivedir/section.php","&nbsp;","w",array());
			fnwrite("$path/$archivedir/downloadsection","&nbsp;","w",array());
		}
		$filename="";
		$filename = basename($file);
		rename($file,"$path/$archivedir/$filename");
		rename("$file.description","$path/$archivedir/$filename.description");

		global $extsig;
		if ($extsig=="") $extsig="sig";
		if (file_exists($file.".$extsig")){
			if (rename($file.".$extsig","$path/$archivedir/$filename.$extsig")){
				fdlogf("archived gpg sign: $file.$extsig");
			}
			else echo "I was not able to archive the gpg sign: $file.$extsig";
		}

		global $extscreenshot;
		if ($extscreenshot=="") $extscreenshot="png";
		if (file_exists($file.".$extscreenshot")){
			if (rename($file.".$extscreenshot","$path/$archivedir/$filename.$extscreenshot")){
				fdlogf("archived screenshot: $file.$extscreenshot");
			}
			else {
				echo "I was not able to archive the screenshot: $file.$extsig";
				fdlogf("I was not able to archive the screenshot: $file.$extsig");
			}
		}


		//l'utente che ha modificato il file
		$myforum="";
		$myforum=get_username();
		if (!is_alphanumeric($myforum)) $myforum ="";
		if (!versecid($myforum)) $myforum = "";

		fdlogf("$file archived by $myforum");

	}
	$description=array();
	$description = load_description("$path/$archivedir/$filename");
	insert_in_max_download("$path/$archivedir/$filename",$description['hits']);
	echo "<br><br><div align=\"center\">"._FDARCHIVEOK."<br><br>";
			$path = preg_replace("/http:\/\//i","",$path);
			$path = preg_replace("/.*sections\//i", "", $path);
			echo "<a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDARCHIVERETURN."</b></a> | ";
			echo "<a href=\"index.php?mod=".rawurlencodepath("$path/$archivedir/")."\"><b>"._FDARCHIVEGO."</b></a></div>";
// 			echo "</div>";

}

/**
 * Riporta il file archiviato alla cartella di origine
 * @param string $file il file da ripristinare
 * @param string $archivedir il nome della cartella di archivio
 * @author Aldo Boccacci
 */
function ripristina($file,$archivedir){
	include_once("shared.php");
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($archivedir,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod = trim($mod);
	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if(!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if(fd_is_admin()){
// 	/*include "config.php";*/ global $siteurl;
		$filename="";
		$filename = basename($file);
		$path="";
		$path = dirname($file);

		if (preg_match("/$archivedir/", $path)){
			$path_up="";
			$path_up = substr($path,0,-16);
			rename($file,$path_up."/".$filename);
			rename("$file.description",$path_up."/".$filename.".description");

			//l'utente che ha modificato il file
			$myforum="";
			$myforum=get_username();
			if (!is_alphanumeric($myforum)) $myforum ="";
			if (!versecid($myforum)) $myforum = "";

			fdlogf("$file restored by $myforum");
// 			echo "PATHUP: $path_up";
			global $extsig;
			if ($extsig=="") $extsig="sig";
			if (file_exists($file.".$extsig")){
				if (rename($file.".$extsig","$path_up/$filename.$extsig")){
					fdlogf("restored gpg sign: $file.$extsig");
				}
				else echo "I was not able to restore the gpg sign: $file.$extsig";
			}

			global $extscreenshot;
			if ($extscreenshot=="") $extscreenshot="png";
			if (file_exists($file.".$extscreenshot")){
				if (rename($file.".$extscreenshot","$path_up/$filename.$extscreenshot")){
					fdlogf("restored screenshot: $file.$extscreenshot");
				}
				else {
					echo "I was not able to restore the screenshot: $file.$extsig";
					fdlogf("I was not able to restore the screenshot: $file.$extsig");
				}
			}

			//avvisa e permetti di ritornare indietro
			echo "<br><br><div align=\"center\">"._FDRESTOREOK."<br><br>";
			$path = preg_replace("/http:\/\//i","",$path_up);
			$path = preg_replace("/.*sections\//i", "", $path);
			echo "<a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._GOTOSECTION."</b></a></div>";
			echo "</div>";
		}
		$tempdesc=array();
		$tempdesc = load_description($path_up."/".$filename);
		insert_in_max_download($path_up."/".$filename,$tempdesc['hits']);
	}
}


/**
 * Mette on-line il nuovo file selezionato
 * @author Aldo Boccacci
 */
function upload(){
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
// 		$description = nl2br($description);
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

	if (isset($_POST['showinblocks'])){
		$showinblocks = trim(getparam("showinblocks",PAR_POST,SAN_FLAT));
		if (!check_var($showinblocks,"boolean")) $showinblocks="true";
	}
	else $showinblocks="true";

	if (isset($_POST['fdfilelevel'])){
		$filelevel = trim(getparam("fdfilelevel",PAR_POST,SAN_FLAT));
		if (!check_var($filelevel,"digit") and $filelevel!="-1") $filelevel="-1";
	}
	else $filelevel ="-1";

	if (isset($_POST['fdfile'])){
		$fdfile = trim(getparam("fdfile",PAR_POST,SAN_FLAT));
		if (!check_path($fdfile,"sections/","false") and $fdfile!="") fd_die("fdfile param (".strip_tags($fdfile).") is not valid! FDadmin: ".__LINE__);
	}
	else fd_die("fdfile param no set! FDadmin: ".__LINE__);

	if (isset($_POST['path'])){
		$path = trim(getparam("path",PAR_POST,SAN_FLAT));
		if (!check_path($path,"sections/","false")) fd_die("path param (".strip_tags($path).") is not valid! FDadmin: ".__LINE__);
	}
	else fd_die("path param not set! FDadmin: ".__LINE__);

	//CONTROLLO TUTTE LE VARIABILI IN GET E FILES
	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod =trim($mod);
	if (!check_path($mod,"","false") and $mod!="") fd_die("\$_GET['mod'] value is not valid! (".strip_tags($mod)."). FDadmin: ".__LINE__);


	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);


	if (!isset($_FILES['fdfile'])) {
		echo "<br><br><div align=\"center\"><b>"._FDFILENOTSELECT."</b></div>";
		fd_die("File to upload not selected. FDadmin:".__LINE__);
	}

	if (!check_file_array($_FILES['fdfile'],$maxFileSize)) fd_die("\$_FILES array is not valid! FDadmin: ".__LINE__);
	//conviene usare un array alternativo??? Lo ho comunque controllato, è sicuro
	$_files_array = check_file_array($_FILES['fdfile'],$maxFileSize);

	if (isset($_FILES['fdfile']['name']) and trim($_FILES['fdfile']['name'])==""){
		echo "<br><br><div align=\"center\">"._FDFILENOTSELECT;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die("File to upload not selected. FDadmin:".__LINE__);
	}

	if ($_FILES['fdfile']['size']>$maxFileSize) {
		echo "<div align=\"center\"><br>"._FDTOOBIG;
		// Il file non è stato caricato perchè le sue dimensioni sono eccessive.<br>";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
		echo "</div>";
		fdlogf($_FILES['fdfile']['size'].": "._FDTOOBIG);
		fd_die("File to upload has size==0. FDadmin:".__LINE__);

	}
	if ($_FILES['fdfile']['error']!=0){
		if ($_FILES['fdfile']['error']==1) echo _FDERROR1;
		else if ($_FILES['fdfile']['error']==2) echo _FDERROR2;
		else if ($_FILES['fdfile']['error']==3) echo _FDERROR3;
		else if ($_FILES['fdfile']['error']==4) echo _FDERROR4;
		else if ($_FILES['fdfile']['error']==6) echo _FDERROR6;
		else if ($_FILES['fdfile']['error']==7) echo _FDERROR7;

		fd_die("<br><br><b>FD+:</b> upload error.");


	}
	if ($_FILES['fdfile']['size']==0) fd_die("<br><b>Error! </b>"._FDSIZE." file: 0 kb. FDadmin: ".__LINE__);

	//ULTIMO CONTROLLO ALL'ARRAY $_FILES
// 	if (fd_check_uploaded_file($_FILES['fdfile'],$maxFileSize,"false")!=TRUE){
// 		fd_die("The \$_FILES array contains invalid data! FD+: ".__LINE__);
// 	}

// 	echo $_FILES['file']['size'];
	if (isset($_FILES['fdfile']['name'])){
		//impedisco di caricare file .php
		$info=array();
		$info = pathinfo($_FILES['fdfile']['name']);
		if (!isset($info['extension'])) $info['extension'] =" ";
		if (preg_match("/php/i",$info['extension'])) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

		//non posso inserire
		if (!fd_check_path($_FILES['fdfile']['name'],"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
		$extensions_array=array();
		$extensions_array = split(",",strtolower($extensions));
		if (!in_array(strtolower($info['extension']),$extensions_array)) {
			echo "<br><br><div align=\"center\">"._NOTVALIDEXT;
			echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die();

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
	if(fd_is_admin()){

		if ($_FILES['fdfile']['name']<>""){
			//controllo che il file non sia già esistente
			if (file_exists("$path/".$_FILES['fdfile']['name'])){
				echo "<div align=\"center\"><br>"._FDUPLOADEXISTS."<br>";
				echo "<br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
				fd_die();

			}
			//effettuo l'upload
			if (fd_is_admin()){
				fd_upload_file($_FILES['fdfile'],"$path/".$_FILES['fdfile']['name']);
			}
		}
		if (trim($md5)=="" and $automd5post=="on"){
			$md5 = md5_file("$path/".$_FILES['fdfile']['name']);
		}
		if (trim($sha1)=="" and $autosha1post=="on"){
			$sha1 = sha1_file("$path/".$_FILES['fdfile']['name']);
		}

		if ($showinblocks!="true") $showinblocks="false";

		//l'utente che ha caricato il file
		$myforum="";
		$myforum=get_username();
		if (!is_alphanumeric($myforum)) $myforum ="";
		if (!versecid($myforum)) $myforum = "";

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
		$data['uploadedby'] = $myforum;
		$data['time'] = filectime("$path/".$_FILES['fdfile']['name']);

		save_description("$path/".$_FILES['fdfile']['name'],$data);

		insert_in_max_download("$path/".$_FILES['fdfile']['name'],0);
		fdlogf("$path/".$_FILES['fdfile']['name']." uploaded by $myforum");

		//gestisco l'eventuale firma
		if ($_FILES['fdsig']['tmp_name']<>"") fd_upload_sig("$path/".$_FILES['fdfile']['name']);
		if ($_FILES['fdscreenshot']['tmp_name']<>"") fd_upload_screenshot("$path/".$_FILES['fdfile']['name']);

		echo "<br><div align=\"center\">"._FDUPLOADOK."</div>";

	}//fine fd_is_admin(?)

		$path = preg_replace("/http:\/\//","",$path);
		$path = preg_replace("/.*sections\//", "", $path);
		echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDARCHIVERETURN."</b></a></div>";
}

/**
 * Salva le modifiche apportate al file
 * @author Aldo Boccacci
 */
function save_changes(){
	global $maxFileSize,$extensions,$defaultvoteon;

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
	else $version="";

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
		$md5 = trim(getparam("md5",PAR_POST,SAN_FLAT));
		if (!check_var($md5,"alnum")) $md5="";
	}
	else $md5="";

	if (isset($_POST['sha1'])){
		$sha1 = trim(getparam("sha1",PAR_POST,SAN_FLAT));
		if (!check_var($sha1,"text")) $sha1="";
	}
	else $sha1="";

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
		if (!check_path($fdfile,"sections/","false") and $fdfile!="") fd_die("fdfile param (".strip_tags($fdfile).") is not valid! FDadmin: ".__LINE__);
	}
	else fd_die("fdfile param no set! FDadmin: ".__LINE__);

	if (isset($_POST['path'])){
		$path = trim(getparam("path",PAR_POST,SAN_FLAT));
		if (!check_path($path,"sections/","false")) fd_die("path param (".strip_tags($path).") is not valid! FDadmin: ".__LINE__);
	}
	else fd_die("path param not set! FDadmin: ".__LINE__);

	//CONTROLLO TUTTE LE VARIABILI IN GET E FILES
	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod=trim($mod);
	if (!check_path($mod,"","false") and $mod!="") fd_die("\$_GET['mod'] value is not valid! (".strip_tags($mod)."). FDadmin: ".__LINE__);

	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (isset($_FILES['newfile']['tmpname']) and !check_file_array($_FILES['newfile'],$maxFileSize)) fd_die("\$_FILES array is not valid! FDadmin: ".__LINE__);
	//conviene usare un array alternativo??? Lo ho comunque controllato, è sicuro
	$_newfile_array = check_file_array($_FILES['newfile'],$maxFileSize);

	if (isset($_FILES['newfile']['name']) and trim($_FILES['newfile']['name'])!=""){
		//impedisco di caricare file .php
		$info = pathinfo($_FILES['newfile']['name']);
		if (!isset($info['extension'])) {
			echo "<br><br><div align=\"center\">"._NOTVALIDEXT;
			echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
		}
		if (preg_match("/php/i",$info['extension'])) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

		//non posso inserire ..
		if (!fd_check_path($_FILES['newfile']['name'],"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
// 		if (stristr($_FILES['newfile']['name'],"%00")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

		$extensions_array = split(",",strtolower($extensions));
		if (!in_array(strtolower($info['extension']),$extensions_array)) {
			echo "<br><br><div align=\"center\">"._NOTVALIDEXT;
			echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die();

		}
	}

	if (isset($_FILES['newfile']['tmp_name']) and trim($_FILES['newfile']['tmp_name'])!=""){
		//impedisco di caricare file .php
		$info = pathinfo($_FILES['newfile']['tmp_name']);
		if (!isset($info['extension'])) $info['extension'] =" ";
		if (preg_match("/php/i",$info['extension'])) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

		//non posso inserire ..
		if (stristr($_FILES['newfile']['tmp_name'],"..")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
		if (stristr($_FILES['newfile']['tmp_name'],"%00")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	}

	if (isset($_POST['fdfile'])){
 		if (stristr($_POST['fdfile'],"..")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
 		if (stristr($_POST['fdfile'],"%00")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	}
	if(fd_is_admin()){
		//controllo le dimensioni del file
		if ($_FILES['newfile']['size']>$maxFileSize){
			echo "<br><div align=\"center\">"._FDSAVESIZE.filesize($_FILES['newfile']['tmp_name'])."bit";
			echo "<br>"._FDTOOBIG;
			echo "<br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
			fd_die();
		}

		//gestisco l'eventuale cambio di file
		if ($_FILES['newfile']['tmp_name']<>""){
			$md5="";
			$sha1="";
			fd_upload_file($_FILES['newfile'],dirname($_POST['fdfile'])."/".$_FILES['newfile']['name']);
			if (basename($_POST['fdfile'])!=$_FILES['newfile']['name']){
				unlink($_POST['fdfile']);
				rename($_POST['fdfile'].".description",dirname($_POST['fdfile'])."/".$_FILES['newfile']['name'].".description");
				$filepathok ="";
				$filepathok = dirname($_POST['fdfile'])."/".$_FILES['newfile']['name'];
			}
			else {
				$filepathok = dirname($_POST['fdfile'])."/".$_FILES['newfile']['name'];
			}
			//se modifico il file allora devo aggiornare alche md5 e sha1
			if ($automd5post=="on"){
				$md5 = md5_file($filepathok);
			}
			if ($autosha1post=="on"){
				$sha1 = sha1_file($filepathok);
			}
			//se modifico il file devo eliminare anche la firma gpg
			global $extsig;
			if ($extsig=="") $extsig="sig";
			if (file_exists($_POST["fdfile"].".$extsig")){
				if (unlink($_POST["fdfile"].".$extsig")){
					fdlogf("Gpg sign deleted because the related file was changed: ".$_POST["fdfile"].".$extsig");
				}
				else "I was not able to delete the gpg sign: ".$_POST["fdfile"].".$extsig";
			}

			//aggiorno lo screenshot
			global $extscreenshot;
			if (file_exists($_POST['fdfile'].".$extscreenshot")){
				if (rename($_POST['fdfile'].".$extscreenshot",$filepathok.".$extscreenshot")){
					fdlogf("Screenshot ".$_POST['fdfile'].".$extscreenshot successfully renamed in $filepathok.$extscreenshot. FDadmin: ".__LINE__);
				}
				else {
					fdlogf("I was not able to rename the screenshot ".$_POST['fdfile'].".$extscreenshot: FDadmin: ".__LINE__);
				}
			}

			if ($_FILES['newfile']['error']!=0){
				if ($_FILES['newfile']['error']==1) echo _FDERROR1;
				else if ($_FILES['newfile']['error']==2) echo _FDERROR2;
				else if ($_FILES['newfile']['error']==3) echo _FDERROR3;
				else if ($_FILES['newfile']['error']==4) echo _FDERROR4;
				else if ($_FILES['newfile']['error']==6) echo _FDERROR6;
				else if ($_FILES['newfile']['error']==7) echo _FDERROR7;

				fd_die("<br><br><b>FD+:</b> errore nell'upload.");
			}

		}
		else $filepathok = $_POST['fdfile'];
		/*
		//x risolvere il problema degli spazi iniziali e finali quando si usa htmlarea
		$description = ltrim($description);
		$description = rtrim($description);
		$desc = "$filename||$description||$version";
		*/

		//se necessario calcolo md5 e sha1
		if ($md5 =="" and $automd5post=="on"){
			$md5 = md5_file($filepathok);
		}
		if ($sha1 =="" and $autosha1post=="on"){
			$sha1 = sha1_file($filepathok);
		}

		if ($showinblocks!="true") $showinblocks="false";

		//aggiusto gli "a capo": solo se non è attivo fckeditor
		if (!file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
			$description =str_replace("\n", "<br>", $description);
		}
		else if (!preg_match("/gecko/i",$_SERVER['HTTP_USER_AGENT']) and !preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])){
			$description =str_replace("\n", "<br>", $description);
		}

		$origdesc = load_description($filepathok);

		$data= array();
		$data['name'] = $filename;
		$data['desc'] = trim($description);
		$data['version'] = $version;
		$data['userlabel'] = $userlabel;
		$data['uservalue'] = $uservalue;
		$data['md5'] = $md5;
		$data['sha1'] = $sha1;
		$data['hits'] = $origdesc['hits'];
		$data['hide'] = $origdesc['hide'];
		$data['showinblocks'] = $showinblocks;
		$data['enablerating'] = $enablerating;
		$data['level'] = $filelevel;
		$data['uploadedby'] = $origdesc['uploadedby'];
		if ($_FILES['newfile']['tmp_name']<>""){
			$data['time'] = filectime($filepathok);
		}
		else $data['time'] = $origdesc['time'];

		//MODIFICA PER VOTO
		$data['totalvote'] = trim($origdesc['totalvote']);
		$data['totalscore'] = trim($origdesc['totalscore']);

		save_description($filepathok, $data);

		insert_in_max_download($filepathok,get_xml_element("hits",get_file($filepathok.".description")));

		//l'utente che ha modificato il file
		$myforum="";
		$myforum=get_username();
		if (!is_alphanumeric($myforum)) $myforum ="";
		if (!versecid($myforum)) $myforum = "";

		fdlogf("$filepathok edited by $myforum");

		if ($_FILES['fdsig']['tmp_name']<>""){
			fd_upload_sig($filepathok,"edit");
		}
		else {
			//do nothing
		}
		if ($_FILES['fdscreenshot']['tmp_name']<>""){
			fd_upload_screenshot($filepathok,"edit");
		}
		else {
			//do nothing
		}

		//permetti di tornare indietro.
		echo "<br><br><div align=\"center\">"._FDEDITDONE."<br><br>";
		$path = dirname($_POST['fdfile']);
		$path = preg_replace("/.*sections\//i", "", $path);
		echo "<a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDRETURN."</b></a></div>";

	}
	else {
	//se non sono amministratore stampo:
	echo _FDNONPUOI.basename(__FILE__).": ".__LINE__;
	}
}

/**
 * Maschera per la creazione di una nuova sezione.
 * @author Aldo Boccacci
 * @since 0.7
 */
function mksection_interface(){
	$fdmod="";
	if (isset($_POST['fdmod'])) $fdmod=$_POST['fdmod'];
	else fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

// 	if (trim($fdmod)=="")fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($fdmod,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	echo "<h3>"._FDCREATESUBSECT."<b>$fdmod</b></h3><br>";

	echo _FDCHOOSESECTNAME."<br>";

	echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
	<input type=\"hidden\" name=\"fdaction\" readonly=\"readonly\" value=\"createsect\">
	<input type=\"hidden\" name=\"fdmod\" readonly=\"readonly\" value=\"$fdmod\">
	<input type=\"text\" name=\"fdnewsect\" size=\"20\"><br><br>
	<input type=\"checkbox\" name=\"fdsecthidden\" value=\"true\">"._FDHIDDENSECT."<br><br>
	<input type=\"checkbox\" name=\"fdsectallowuserupload\" value=\"true\">"._FDALLOWUSERUPLOAD."<br><br><br>
	<input type=\"submit\" name=\"fdok\" value=\""._FDCREATESECT."\">
	</form>";

}

/**
 * Crea una sottosezione.
 * @param string il mod della sezione nella quale creare la sottosezione
 * @param string $dirname il nome della nuova sezione
 */
function mksection($mod, $dirname,$hidden="false",$allowuserupload="false"){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$currentmod="";
	$currentmod=getparam("mod",PAR_GET,SAN_FLAT);
	$currentmod=trim($currentmod);
	if ($currentmod=="") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (!fd_check_path($currentmod,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($dirname,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if ($currentmod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!preg_match("/^false$|^true$/i",trim($hidden))) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!check_var($allowuserupload,"boolean")) fd_die("\$allowuserupload must be true or false! ".strip_tags($allowuserupload)." FDAdmin: ".__LINE__);

	$dirname = preg_replace("/ /","_",$dirname);
// 	$dirname = strtr($dirname,
//   "???????¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
//   "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");

	if (fd_is_admin()){
		if ($hidden=="true") $dirname="none_$dirname";
		if (is_dir("sections/$mod/".$dirname)){
			echo "<div align=\"center\"><br><br>"._THEDIR." <b>$mod/$dirname</b> "._ALREADYEXISTS."
			<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
			return;
		}
		if(fn_mkdir("sections/$mod/".$dirname,0777)){
			echo "<div align=\"center\"><br><br>"._FDSECT." <b>$mod/$dirname</b> "._FDCREATESECTOK;
			echo "<br><br><a href=\"index.php?mod=".rawurlencodepath("$mod/$dirname")."\" title=\""._GOTOSECTION." $dirname\"><b>"._GOTOSECTION."</b></a></div>";
			fnwrite("sections/$mod/$dirname/section.php","&nbsp;","w",array());
			fnwrite("sections/$mod/$dirname/downloadsection","&nbsp;","w",array());
			if (file_exists("sections/$mod/$dirname/downloadsection")){
				fdlogf("section $mod/$dirname/ created by ".get_username());
			}
			else {
				fdlogf("section $mod/$dirname/ created by ".get_username()." (Only dir!)");
				echo _FDCREATEONLYDIR;
			}
// 			echo "UPLOAD: $allowuserupload";
			if ($allowuserupload=="true"){
// 			echo "ciao";
				fnwrite("sections/$mod/$dirname/fduserupload","userupload","w",array("nonull"));

			}
		}
		else echo "<div align=\"center\"><br><br>"._FDCREATEDIRERROR."<b>sections/$mod/$dirname</b><br><br>"._FDCHECKPERM."</div>";
	}
}

/**
 * Imposta se nascondere o meno un file.
 * Il file viene nascosto se value è impostata a "true" e viene mostrato se è impostata
 * a "false".
 * @param string $filepath il percorso del file di cui mosificare le impostazioni
 * @param string $value indica se nascondere o meno un file
 * @author Aldo Boccacci
 * @since 0.6
 */
function hide_file($filepath,$value){
	include_once("shared.php");
	if (!fd_check_path($filepath,"sections/","false")) fd_die("\$filepath is not valid! FDadmin: ".__LINE__);
	if (!preg_match("/^false$|^true$/i",$value)) fd_die("\$value must be true or false! FDadmin: ".__LINE__);
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (fd_is_admin()){
		$description=array();
		$description = load_description($filepath);
		$description['hide'] = $value;

		save_description($filepath, $description);
	}

	//l'utente che ha modificato il file
	$myforum="";
	$myforum=get_username();
	if (!is_alphanumeric($myforum)) $myforum ="";
	if (!versecid($myforum)) $myforum = "";

	fdlogf("$filepath hide status set to \"$value\" by $myforum");

	//torna alla sezione
	$mod = dirname($filepath);
// 		echo $mod;
	$mod = preg_replace("/^.*sections\//","",$mod);
// 	echo $mod;
	header("Location: index.php?mod=".rawurlencodepath($mod));
}

/**
 * Questa funzione crea l'interfaccia grafica per rinominare un file gestito da fd+
 * @param string $file il file da rinominare
 * @since 0.7
 * @author Aldo Boccacci
 */
function rename_interface($file){
if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
$mod="";
$mod = getparam("mod",PAR_GET,SAN_FLAT);
$mod = trim($mod);
if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);


echo _FDRENAMETITLE."<b>".basename($file)."</b><br><br>";

echo _FDRENAMECHOOSE."<br>";
echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
<input type=\"hidden\" name=\"fdaction\" readonly=\"readonly\" value=\"renamefile\">
<input type=\"hidden\" name=\"fdpath\" readonly=\"readonly\" value=\"".dirname($file)."\">
<input type=\"hidden\" name=\"oldfilename\" readonly=\"readonly\" value=\"".basename($file)."\">
<input type=\"text\" name=\"newfilename\" size=\"35\" value=\"".basename($file)."\"><br><br>
<input type=\"submit\" name=\"fdok\" value=\""._FDRENAME." file\">";

echo "</form>";

echo _FDRENAMEEXTLIMIT;

}

/**
 * Rinomina un file gestito da fd+
 * @param string $oldname il vecchio nome del file
 * @param string $newname il nuovo nome del file
 * @param string $path il percorso del file
 * @since 0.7
 * @author Aldo Boccacci
 */
function rename_file($oldname, $newname, $path){
if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
if (!fd_check_path($oldname,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
if (!fd_check_path($newname,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
if (!fd_check_path($path,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
$mod="";
$mod = getparam("mod",PAR_GET,SAN_FLAT);
$mod = trim($mod);
if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
// echo "$path/$oldname.description";
//controllo la presenza del corrispondente file .description
if (!file_exists("$path/$oldname.description")){
	echo _FDRENAMEFILE."<b>$path/$oldname</b>"._FDRENAMENOFD;
	fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
}
$basemod = "";
$basemod = preg_replace("/^sections\//i","",$path);
//se il nome non cambia...
if ($oldname==$newname) {
	echo _FDRENAMENOTCHANGED;
	echo "<br><br><a href=\"index.php?mod=".rawurlencodepath($basemod)."\">"._FDARCHIVERETURN."</a>";
	return;
}

//se la cartella non è scrivibile...
if (!is_writable($path)){
	echo _FDREADONLYDIR;
	echo "<br><br><a href=\"index.php?mod=".rawurlencodepath($basemod)."\">"._FDARCHIVERETURN."</a>";
	return;
}
//se esiste già un file con quel nome...
if (file_exists("$path/$newname")){
	echo _FDRENAMEEXISTS1." <b>$newname</b> "._FDRENAMEEXISTS2." <b>$path</b>.<br>"._FDRENAMECHANGENAME;
	echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a>";
	return;
}

//non posso cambiare l'estensione
$oldinfo = array();
$newinfo = array();
$oldinfo = pathinfo($oldname);
$newinfo = pathinfo($newname);
if (isset($oldinfo['extension'])) $oldext = $oldinfo['extension'];
else $oldext="";
if (isset($newinfo['extension'])) $newext = $newinfo['extension'];
else $newext="";

if ($oldext !=$newext){
	echo _FDERROR." "._FDRENAMEEXTLIMIT;
	echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a>";
	return;
}

//se tutti i controlli vengono passati posso rinominare il file
if (rename("$path/$oldname","$path/$newname") and rename("$path/$oldname.description","$path/$newname.description")){
	echo "<div align=\"center\"><br><br><b>"._FDRENAMEOK."</b>";
	echo "<br><br><a href=\"index.php?mod=".rawurlencodepath($basemod)."\">"._FDARCHIVERETURN."</a></div>";
	//l'utente che ha modificato il file
	$myforum="";
	$myforum=get_username();
	if (!is_alphanumeric($myforum)) $myforum ="";
	if (!versecid($myforum)) $myforum = "";

	fdlogf("$path/$oldname \n\trenamed in $path/$newname by $myforum");
}

else {
	echo _FDRENAMEALERT."<b> $oldname</b>";
	return;
}

//inserisco in max_download
$desc=array();
$desc= load_description("$path/$newname");
insert_in_max_download("$path/$newname",$desc['hits']);

//firma gpg
global $extsig;
if ($extsig=="") $extsig="sig";
if (file_exists("$path/$oldname.$extsig")){
	if (rename("$path/$oldname.$extsig","$path/$newname.$extsig")){
		fdlogf("Gpg sign file succesfully renamed: $path/$newname.$extsig");
	}
	else {
		echo "I was not able to rename the gpg sign file $path/$oldname.$extsig in $path/$newname.$extsig";
		fdlogf("I was not able to rename the gpg sign file $path/$oldname.$extsig in $path/$newname.$extsig");
	}

}

//screenshot
global $extscreenshot;
if ($extscreenshot=="") $extscreenshot="png";
if (file_exists("$path/$oldname.$extscreenshot")){
	if (rename("$path/$oldname.$extscreenshot","$path/$newname.$extscreenshot")){
		fdlogf("Screenshot succesfully renamed: $path/$newname.$extscreenshot");
	}
	else {
		echo "I was not able to rename the gscreenshot $path/$oldname.$extscreenshot in $path/$newname.$extscreenshot";
		fdlogf("I was not able to rename the screenshot $path/$oldname.$extscreenshot in $path/$newname.$extscreenshot");
	}

}

}


/**
 * Interfaccia grafica per permettere lo spostamento dei file gestiti da fd+
 * @param string $file il file da spostare
 * @author Aldo Boccacci
 * @since 0.7
 */
function move_file_interface($file){
// 	echo "FILE: $file";
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(!fd_check_path($file,"sections/","false")) die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$mod="";
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
	$mod = trim($mod);
	if ($mod!="none_Fdplus") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$startdir ="";
	$startdir = dirname($file);

	echo "<br>"._FDMOVEFILE."<b>".preg_replace("/^sections\//i","",$file)."</b>"._FDMOVEFILESECTION.":<br><i>"._FDMOVELINK."</i><br><br>";
	$dirs = list_fd_sections(".");
	foreach($dirs as $dir){
// 	echo "$startdir<br>$dir<br><rb>";
		if ($startdir==preg_replace("/^\.\//","",$dir)) continue;
		if (is_writable($dir)) echo "<a href=\"index.php?mod=none_Fdplus&amp;fdaction=movefileconfirm&amp;fdfile=".rawurlencodepath($file)."&amp;newdir=".rawurlencodepath($dir)."\">".preg_replace("/^\.\//","",$dir)."</a><br>";
		else {
			echo "<span style=\"color : #ff0000; text-decoration : line-through;\">".preg_replace("/^\.\//","",$dir)."</span><br>";
		}
	}

	echo "<br><hr><a href=\"javascript:history.back()\">&lt;&lt; "._FDCANC."</a>";

}

/**
 * Chiede conferma prima di spostare effettivamente il file
 * @param string $file il file da spotare
 * @param string $newdir la cartella in cui spostare il file
 * @author Aldo Boccacci
 * @since 0.7
 */
function move_file_confirm($file, $newdir){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(!fd_check_path($newdir,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (!file_exists($file.".description")) fd_die(_FDRENAMEFILE." $file"._FDRENAMENOFD." FDadmin: ".__LINE__);

	echo "<br>"._FDMOVECONFIRM." <b>".preg_replace("/^sections\//i","",$file)."</b> <br>"._FDMOVEFILESECTION." <b>".preg_replace("/^sections\//i","",$newdir)."</b>?<br>";

	echo "<br><br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
<input type=\"hidden\" name=\"fdfile\" value=\"$file\" readonly=\"readonly\">
<input type=\"hidden\" name=\"fdaction\" value=\"movefile\" readonly=\"readonly\">
<input type=\"hidden\" name=\"newdir\" value=\"$newdir\" readonly=\"readonly\">
<input type=\"SUBMIT\" value=\""._FDMOVE."\"></form>";

	echo "<br><hr><a href=\"javascript:history.back()\">&lt;&lt; "._FDCANC."</a>";

}

/**
 * Sposta il file $file nella cartella $newdir
 * @param string $file il file da spotare
 * @param string $newdir la cartella in cui spostare il file
 * @author Aldo Boccacci
 * @since 0.7
 */
function fd_move_file($file, $newdir){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(!fd_check_path($file,"sections/","false")) die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if(!fd_check_path($newdir,"sections/","false")) die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	//il file deve essere gestito da fd+!
	if (!file_exists($file.".description")) fd_die(_FDRENAMEFILE." $file"._FDRENAMENOFD." FDadmin: ".__LINE__);

	$newdir = preg_replace("/^\.\//","",$newdir);

	if (!is_writable($newdir)) {
		echo _FDDIR." <b>$newdir</b> "._FDNOTWRITE;
		echo "<br>"._FDCHECKPERM;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a>";
		return;
	}

	if (!is_fd_sect($newdir)){
		echo "The dir $newdir is not managed by FD+.";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a>";
		return;
	}

	if (file_exists($newdir."/".basename($file))){
		echo _FDRENAMEEXISTS1." <b>".basename($file)."</b> "._FDRENAMEEXISTS2." <b>$newdir</b>";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
		return;

	}
	if (file_exists($newdir."/".basename($file).".description")){
		echo _FDRENAMEEXISTS1." <b>".basename($file).".description</b> "._FDRENAMEEXISTS2." <b>$newdir</b>";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
		return;
	}

	global $extsig;
	if ($extsig=="") $extsig="sig";
	if (file_exists($newdir."/".basename($file).".$extsig")){
		echo _FDRENAMEEXISTS1." <b>".basename($file).".$extsig</b> "._FDRENAMEEXISTS2." <b>$newdir</b>";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
		return;
	}

	global $extscreenshot;
	if ($extscreenshot=="") $extscreenshot="png";
	if (file_exists($newdir."/".basename($file).".$extscreenshot")){
		echo _FDRENAMEEXISTS1." <b>".basename($file).".$extscreenshot</b> "._FDRENAMEEXISTS2." <b>$newdir</b>";
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br>";
		return;
	}

	if (rename($file,$newdir."/".basename($file)) and rename($file.".description",$newdir."/".basename($file).".description")){
		echo "File <b>".preg_replace("/^sections\//i","",$file)."</b> "._FDMOVESUCCESS." <b>".preg_replace("/^sections\//i","",$newdir)."</b>";
	}
	else {
		echo _FDMOVEFAIL."<b>$file</b>";
	}

	if (file_exists($file.".$extsig")){
		if (rename($file.".$extsig",$newdir."/".basename($file).".$extsig")){
			//ok
		}
		else {
			echo "Attention: I cannot move the gpg sign of the file!";
			fdlogf("I cannot move the gpg sign of the file: $file to the dir: $newdir");
		}
	}

	if (file_exists($file.".$extscreenshot")){
		if (rename($file.".$extscreenshot",$newdir."/".basename($file).".$extscreenshot")){
			//ok
		}
		else {
			echo "Attention: I cannot move the gpg sign of the file!";
			fdlogf("I cannot move the gpg sign of the file: $file to the dir: $newdir");
		}
	}
	//inserisco in $max_download
	$desc = array();
	$desc = load_description($newdir."/".basename($file));
	insert_in_max_download($newdir."/".basename($file),$desc['hits']);

	$olddir = preg_replace("/sections\//i","",dirname($file));
	echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($olddir)."\"><b>"._FDARCHIVERETURN."</b></a> | ";

	$newdir = preg_replace("/sections\//i","",$newdir);
	echo "<a href=\"index.php?mod=".rawurlencodepath($newdir)."\" title=\""._GOTOSECTION."\"><b>"._GOTOSECTION." $newdir</b></a></div>";
}


/**
 * Interfaccia per l'eliminazione della firma gpg associata a un file gestito da FD+.
 *
 * Questa funzione crea l'interfaccia web dalla quale sarà possibile eliminare
 * la firma gpg associata a un file gestito da FD+.
 *
 * @param string $file il file di cui eliminare la firma
 * @author Aldo Boccacci
 * @since 0.8
 */
function delete_sign_interface($file){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!check_path($file,"sections/","false")) fd_die("\$file (".strip_tags($file).") is not valid! FDadmin: ".__LINE__);

	if (!file_exists($file.".description")) fd_die("\$file(".strip_tags($file).") is not managed by FD+! FDadmin: ".__LINE__);

	global $extsig;
	if (!file_exists($file.".$extsig")) fd_die("There isn't a sign file for ".strip_tags($file)."! FDadmin: ".__LINE__);
	?>
	<div  style="text-align : center;">
	<br><?php echo _FDDELSURE; ?><b><?php echo $file.".$extsig"; ?></b>?
	<br><br>
	<a href="index.php?mod=none_Fdplus&amp;fdaction=deletesign&amp;fdfile=<?php echo rawurlencodepath($file); ?>"><?php echo _FDDEL; ?></a>
	<a href="javascript:history.back()"><?php echo _FDCANC; ?></a>
	</div>
	<?php
}

/**
 * Elimina la firma indicata come parametro.
 *
 * Questa funzione elimina la firma associata ad un file gestito da FD+.
 * @param string $file Il file di cui eliminare la stringa
 * @author Aldo Boccacci
 * @since 0.8
 */
function delete_sign($file){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!check_path($file,"sections/","false")) fd_die("\$file (".strip_tags($file).") is not valid! FDadmin: ".__LINE__);

	if (!file_exists($file.".description")) fd_die("\$file(".strip_tags($file).") is not managed by FD+! FDadmin: ".__LINE__);

	global $extsig;
	if (!file_exists($file.".$extsig")) fd_die("There isn't a sign file for ".strip_tags($file)."! FDadmin: ".__LINE__);

	if (!is_writable($file.".$extsig"))fd_die("the gpg sign of the file ".strip_tags($file)." is not writeable! FDadmin: ".__LINE__);

	if (unlink($file.".$extsig")){
		echo _FDDELOK."$file.$extsig";
		fdlogf("succesfully deleted gpg sign: $file.$extsig");

	}
	else {
		echo "Non ho eliminato il file";
		fdlogf("I was not able to delete the gpg sign: $file.$extsig");

	}

	//permetti di ritornare
	$path = preg_replace("/http:\/\//i","",dirname($file));
	$path = preg_replace("/\/sections\//i", "", $path);
	$path = preg_replace("/sections\//i", "", $path);
	echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDRETURN."</b></a></div>";

}

/**
 * Interfaccia per l'eliminazione dello screenshot associato a un file gestito da FD+.
 *
 * Questa funzione crea l'interfaccia web dalla quale sarà possibile eliminare
 * lo screenshot associato a un file gestito da FD+.
 *
 * @param string $file il file di cui eliminare lo screenshot
 * @author Aldo Boccacci
 * @since 0.8
 */
function delete_screenshot_interface($file){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!check_path($file,"sections/","false")) fd_die("\$file (".strip_tags($file).") is not valid! FDadmin: ".__LINE__);

	if (!file_exists($file.".description")) fd_die("\$file(".strip_tags($file).") is not managed by FD+! FDadmin: ".__LINE__);

	global $extscreenshot;
	if (!file_exists($file.".$extscreenshot")) fd_die("There isn't a screenshot for ".strip_tags($file)."! FDadmin: ".__LINE__);
	?>
	<div  style="text-align : center;">
	<br><?php echo _FDDELSURE; ?><b><?php echo $file.".$extscreenshot"; ?></b>?
	<br><br>
	<a href="index.php?mod=none_Fdplus&amp;fdaction=deletescreenshot&amp;fdfile=<?php echo rawurlencodepath($file); ?>"><?php echo _FDDEL; ?></a>
	<a href="javascript:history.back()"><?php echo _FDCANC; ?></a>
	</div>
	<?php
}

/**
 * Elimina lo screenshot indicato come parametro.
 *
 * Questa funzione elimina lo screenshot associato ad un file gestito da FD+.
 *
 * @param string $file Il file di cui eliminare la stringa
 * @author Aldo Boccacci
 * @since 0.8
 */
function delete_screenshot($file){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!check_path($file,"sections/","false")) fd_die("\$file (".strip_tags($file).") is not valid! FDadmin: ".__LINE__);

	if (!file_exists($file.".description")) fd_die("\$file(".strip_tags($file).") is not managed by FD+! FDadmin: ".__LINE__);

	global $extscreenshot;
	if (!file_exists($file.".$extscreenshot")) fd_die("There isn't a screenshot for ".strip_tags($file)."! FDadmin: ".__LINE__);

	if (!is_writable($file.".$extscreenshot"))fd_die("the screenshot of the file ".strip_tags($file)." is not writeable! FDadmin: ".__LINE__);

	if (unlink($file.".$extscreenshot")){
		echo _FDDELOK."$file.$extscreenshot";
		fdlogf("succesfully deleted gpg sign: $file.$extscreenshot");

	}
	else {
		echo "Non ho eliminato il file";
		fdlogf("I was not able to delete the gpg sign: $file.$extscreenshot");

	}

	//permetti di ritornare
	$path = preg_replace("/http:\/\//i","",dirname($file));
	$path = preg_replace("/\/sections\//i", "", $path);
	$path = preg_replace("/sections\//i", "", $path);
	echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($path)."\"><b>"._FDRETURN."</b></a></div>";

}

/**
 * Funzione deputata al caricamento dello screenshot.
 *
 * Questa funzione si occupa di caricare lo screenshot associato al file gestito da FD+.
 * @param string $dir la cartella in cui caricare lo screenshot
 * @param string $mode "upload" se stiamo caricando un nuovo file, "edit" se ne stiamo modificando
 *                     uno già presente
 * @author Aldo Boccacci
 * @since 0.8
 */

function fd_upload_screenshot($file,$mode="upload"){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	//CONTROLLO PARAMETRI
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!preg_match("/^upload$|^edit$/i",$mode)) {
		fd_die("Error: \$mode value is invalid: must be \"upload\" or \"edit\". FDadmin: ".__LINE__);
	}
	global $extscreenshot;
	if ($extscreenshot=="") $extscreenshot="png";

	if (check_file_array($_FILES['fdscreenshot'],"200000","false")) $files_screenshot = check_file_array($_FILES['fdscreenshot'],"200000","false");
	else {
		fdlogf("\$_FILES['fdscreenshot'] is not valid! FDadmin: ".__LINE__);
		return;
	}

	if (preg_match("/\.php*$|\.php$/i",$files_screenshot['name'])){
		fd_die("The extension of the screenshot is \".php\", not \".$extscreenshot\" FDadmin: ".__LINE__);
	}

	if (isset($files_screenshot['name']) and trim($files_screenshot['name'])==""){
		echo "<br><br><div align=\"center\">"._FDFILENOTSELECT;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a><br><br></div>";
			fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	}

	if (!preg_match("/$extscreenshot$/i",$files_screenshot['name'])){
		echo "<br><div align=\"center\"><b>ERROR</b>The screenshot must be a <b>$extscreenshot</b> file. The file will not be uploaded!</div><br>";
		return;
	}

	if ($files_screenshot['size']>10000000) {
		fd_die("The sig file is too big! ".$dir."/".$files_screenshot['name'].".$extsig"."FDadmin: ".__LINE__);
		die_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	}

	if (!file_exists($file)){
	echo "<b>Attenzione!</b> il file $file non esiste!";
	fdlogf("Error uploading screenshot. The corresponding file $file does not exists!");
	return;
	}

	if ($files_screenshot['error']!=0){
		if ($files_screenshot['error']==1) echo _FDERROR1;
		else if ($files_screenshot['error']==2) echo _FDERROR2;
		else if ($files_screenshot['error']==3) echo _FDERROR3;
		else if ($files_screenshot['error']==4) echo _FDERROR4;
		else if ($files_screenshot['error']==6) echo _FDERROR6;
		else if ($files_screenshot['error']==7) echo _FDERROR7;

		fd_die("<br><br><b>FDadmin:</b> upload error.");


	}
// 	if ($files_screenshot['size']==0) fd_die("<br><b>Error! </b>"._FDSIZE." file: 0 kb");

	//eventualmente elimino il vecchio screenshots
	if (file_exists("$file.$extscreenshot")){
		unlink("$file.$extscreenshot");
	}

	if (!fd_check_path("$file.$extscreenshot","sections/","false")) fd_die("The screenshot path is not valid!: $file.$extscreenshot"."FDadmin:".__LINE__);

	//controllo per codice php
	if (preg_match("/\<\?|\?\>/",get_file($files_screenshot['tmp_name']))){
		fd_die("The screenshot file contains php tags! "._FDNONPUOI.basename(__FILE__).": ".__LINE__);
	}

	$dir ="";
	$dir = dirname($file);
	//carico il file
	fd_upload_file($files_screenshot,"$file.$extscreenshot");
}


/**
 * Funzione che si occupa di effettuare l'upload dei file sul server
 *
 * Questa è la funzione centralizzata deputata al caricamento sul server dei file degli utenti.
 *
 * @param array $files_array l'array $_FILES con i dati da caricare
 * @param string $filepath il percorso che dovrà avere il file
 * @author Aldo Boccacci
 * @since 0.8
 */
function fd_upload_file($files_array,$filepath){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	global $maxFileSize;
	if (!check_file_array($files_array,$maxFileSize)){
		fdlogf("\$files_array is not valid! FDadmin".__LINE__);
		return false;
	}

	if (!check_path($filepath,"sections/","false")){
		fdlogf("\$filepath is not valid! (".strip_tags($filepath).") FDadmin".__LINE__);
		return false;
	}

	if (!is_writable(dirname($filepath))){
		fdlogf(strip_tags(dirname($filepath))." is not writable! FDadmin".__LINE__);
		return false;
	}


	if (move_uploaded_file($files_array['tmp_name'],$filepath)){
		fdlogf("Uploaded file: ".basename($filepath)." in the dir ".dirname($filepath));
	}
	else {
		echo "I was not able to upload the file :".basename($filepath)." in the dir ".dirname($filepath);
		fdlogf("I was not able to upload the file :".basename($filepath)." in the dir ".dirname($filepath)." FDadmin: ".__LINE__);
		return;
	}
}

/**
 * Crea nella sezione indicata dal parametro $mod il file fduserupload.
 * In questo modogli utenti potranno proporre file in quella sezione
 *
 * @param string $mod il $mod della sezione nella quale permettere l'upload
 * @author Aldo Boccacci
 * @since 0.8
 */
function allow_user_upload($mod){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$mod =getparam($mod,PAR_NULL,SAN_FLAT);
	$mod = trim($mod);
	if (!fd_check_path($mod,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$path="";
	$path="sections/$mod";

	if (file_exists("sections/$mod/fduserupload")){
		echo "<br><div align=\"center\"><b>Users already can upload files in this section</div>";
		echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";
	}
	else {
		if (!is_writeable("sections/$mod/")){
			echo _FDDIR." sections/$mod"._FDNOTWRITE;
			echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";
		}
		else {
			fnwrite("sections/$mod/fduserupload"," ","w",array());
			fdlogf("user can upload files in the section $mod");
			echo _FDUSERUPLOADPERMSADDED." <b>$mod</b>";
			echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";
		}
	}



}

/**
 * Rimuove dalla sezione indicata dal parametro $mod il file fduserupload.
 *
 * @param string $mod il $mod della sezione
 * @author Aldo Boccacci
 * @since 0.8
 */
function remove_user_upload($mod){
	if (!fd_is_admin()) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$mod =getparam($mod,PAR_NULL,SAN_FLAT);
	$mod = trim($mod);
	if (!fd_check_path($mod,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$path="";
	$path="sections/$mod";

	if (file_exists("sections/$mod/fduserupload")){
		if (!is_writeable("sections/$mod/fduserupload")){
			echo _FDRENAMEFILE." sections/$mod/fduserupload"._FDNOTWRITE;
			echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";
		}
		else {
			if (unlink("sections/$mod/fduserupload")){
				fdlogf("user cannot upload files in the section $mod");
				echo _FDUSERUPLOADPERMSREMOVED." <b>$mod</b>";
				echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";

			}
			else {
				echo _FILENOTDELETED." <b>sections/$mod/fduserupload</b>";
				echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";
			}
		}
	}
	else {
		echo "<br><div align=\"center\"><b>Users cannot upload files in this section</div>";
		echo "<br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._FDRETURN."</b></a></div>";
	}
}
?>
