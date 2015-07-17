<?php
if (preg_match("/fdview.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call fdview.php!",__FILE,__LINE);
}


/**
 * Il metodo base: mostra la sezione con tutti i download
 * @author Aldo Boccacci
 */
function fd_view_section(){
global $extensions,$icon_style,$archivedir,$newfiletime,$extsig,$theme,$section_show_header;

if (!_FN_IS_GUEST)
//ripulisco il file di log dai dati vecchi
	fd_check_ip_log_data();


if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
}

	$mod ="";
	$mod = _FN_MOD;
	$mod = trim($mod);
	if ($mod=="") return;
// 	if (!fd_check_path($mod,"","false")) return;

	$path = "sections/$mod";

	if (!fd_check_path($path,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	//se c'è bisogno rinomino la cartella di archivio
	rename_archivedir($archivedir);
	$extensions_array=array();
	$extensions_array = explode(",",strtolower($extensions));
	if ($section_show_header==1)
		fd_show_section_header($mod);
		fd_load_php_code("download/include/autoinclude/fd_view_section/header/");


	if (file_exists("sections/$mod/fduserupload")and file_exists("download/include/fduser.php")){
	user_upload_interface();

	}

	$files=array();
	$tmpfiles=scandir($path);
	natcasesort($tmpfiles);
	if ($tmpfiles) {
		$files[] = $path."/".current($tmpfiles);
		while (next($tmpfiles)){
			$files[] = $path."/".current($tmpfiles);
		}
	}

	/* Per tutti i file*/
	foreach ($files as $filename) {
		//visualizzo ciascun file
		if (!preg_match("/\.php.$|\.php$/i",$filename) and !preg_match("/\.description/i",$filename)) {
			//se si tratta di una firma gpg ed esiste il file corrispondente
			//non devo visualizzare il file (sarà integrato nel box del file corrispondente)
			if (preg_match("/\.$extsig$/i",$filename) and file_exists(preg_replace("/\.$extsig$/i","",$filename))) continue;
			//se si tratta di uno screenshot
			global $extscreenshot;
			if (preg_match("/\.$extscreenshot$/i",$filename) and file_exists(preg_replace("/\.$extscreenshot$/i","",$filename))) continue;
				if (basename($filename)=="section.png") continue;
				fd_view_file($filename);

		}
	}

	fd_load_php_code("download/include/autoinclude/fd_view_section/footer/");


	//amministrazione della sezione
	echo "<br>";
	echo "<div align=center>";
	if (is_dir("sections/none_Statistiche/download_stats/")){
		echo "<a href=\"index.php?mod=none_Statistiche/download_stats\" title=\""._FDSTATTITLE."\">"._FDSTAT."</a>";
		echo "<br>";
	}


	if(fd_is_admin()){
		//controllo se ci troviamo in una dir di archivio
		$lastdir = strrchr(dirname($filename),"/");
		if (!preg_match("/$archivedir/i",$lastdir)){
			if (is_writable(dirname($filename))){
				//echo "<br><a href=\"index.php?mod=none_Fdplus&amp;fdaction=addfile&amp;fdfile=new&amp;path=$path\" title=\""._FDADDFILETITLE."\"><b>"._FDADDFILE."</b></a>";

				//pulsante per aggiungere un nuovo file
				echo "<br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
				<input type=\"hidden\" name=\"path\" value=\"$path\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdaction\" value=\"addfile\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdfile\" value=\"new\" readonly=\"readonly\">
				<input type=\"SUBMIT\" value=\""._FDADDFILE."\"></form>";

				//pulsante per aggiungere un nuovo file
				echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
				<input type=\"hidden\" name=\"path\" value=\"$path\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdaction\" value=\"addurl\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdfile\" value=\"new\" readonly=\"readonly\">
				<input type=\"SUBMIT\" value=\""._FDADDURL."\"></form>";

				//pulsante per creare una nuova sezione
				echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
				<input type=\"hidden\" name=\"fdmod\" value=\"$mod\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdaction\" value=\"createsectinterface\" readonly=\"readonly\">
				<input type=\"SUBMIT\" value=\""._FDCREATESECTDOWNLOAD."\"></form>";

				if (!file_exists("sections/$mod/fduserupload")){
				//pulsante per permettere agli utenti di caricare files in questa sezione
				echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
				<input type=\"hidden\" name=\"fdmod\" value=\"$mod\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdaction\" value=\"allowuserupload\" readonly=\"readonly\">
				<input type=\"SUBMIT\" value=\""._FDALLOWUSERUPLOAD."\"></form>";
				}
				else {
				//pulsante per permettere agli utenti di caricare files in questa sezione
				echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
				<input type=\"hidden\" name=\"fdmod\" value=\"$mod\" readonly=\"readonly\">
				<input type=\"hidden\" name=\"fdaction\" value=\"removeuserupload\" readonly=\"readonly\">
				<input type=\"SUBMIT\" value=\""._FDDONOTALLOWUSERUPLOAD."\"></form>";
				}
			}
			else echo "<br><span style=\"color : #ff0000;\">"._FDREADONLYDIR."</span>";
		}
	}
	echo "</div><br>";

// 	module_copyright("FDplus",get_fd_version(),"<b>Aldo Boccacci</b> aka Zorba","zorba_(AT)tin.it", "http://www.aldoboccacci.it", "Gpl version 2.0");

	global $theme;
	if (file_exists("sections/$mod/$archivedir/section.php")){
		opentable();
		echo "<img src=\"themes/$theme/images/subsection.png\" alt=\"Subsection\">&nbsp;<a href=\"index.php?mod=".rawurlencodepath($mod."/".$archivedir)."\" title=\""._FDARCHIVEGO."\">"._FDARCHIVEDIR."</a>";
		closetable();
	}



}

/**
 * Questa funzione crea e visualizza la tabella con le informazioni
 * per il file indicato come argomento
 * @param string $filename il file da mostrare
 * @since 0.7
 * @author Aldo Boccacci
 */
function fd_view_file($filename){
$GLOBALS['filename'] = $filename;

if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
}
	if (!fd_check_path($filename,"sections/","")) {
		fdlogf("\$filename \"".strip_tags($filename)."\" is not valid! FDview: ".__LINE__);
		return;
	}
	global $extensions,$icon_style,$archivedir,$newfiletime,$extsig,$showdownloadlink;
	if ($extsig=="") $extsig="sig";
	$extensions_array=array();
	$extensions_array = explode(",",strtolower($extensions));
//estraggo l'estensione
		$fileinfo = array();
		$fileinfo = pathinfo($filename);
		$ext ="";
		if (isset($fileinfo['extension'])) $ext = $fileinfo['extension'];
		else return;

		if (!in_array(strtolower($ext),$extensions_array)){
			return;
		}

		//se è un file php ritorno
		if (preg_match("/php/i",$ext)) {
			return;
		}

		/*
		 * Apre il relativo file di descrizione "<$filename>.descrizione"
		 * se non esiste lo creo io.
		 */
		if (!file_exists($filename.".description")){
			$data = array();
			$data['time'] = filemtime($filename);
			save_description($filename,$data);

		}


		/* Calcola la grandezza del file */
		$size="";
		$size = round(filesize($filename)/1024)."Kb";


		//NUOVA FUNZIONE PER IL CARICAMENTO DELLA DESCRIZIONE
		$description=array();
		$description= load_description($filename);

		//solo gli amministratori possono vedere i file nascosti
		if ($description['hide']=="true" and !fd_is_admin()) return;
		//controllo se il livello dell'utente è adeguato a quello del file
		$myforum="";
		$myforum=_FN_USERNAME;
		if ($description['level']!="-1"){
			if (trim($myforum)!="" and versecid($myforum)){
				if ($description['level']>_FN_USERLEVEL){
					return;
				}
			}
			else return;

		}
		$newfilestring="";
		//Calcolo se inserire l'icona per il nuovo file
		if (file_exists("images/mime/new.gif")){
			if ((time()-$description['time'])<$newfiletime*3600) {
				if (strrchr($fileinfo['dirname'],"/")!="/".$archivedir){
				$newfilestring = "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file!\">";
				}
			}
		}

		else $newfilestring = "";

		$string_title="";
		/* Generazione Tabella */
		echo "<br><br>";
		if (ltrim($description['name'])==""){
			$string_title = "<a id=\"".create_id($filename)."\" title=\"mime icon\">".getIcon($ext,$icon_style)."</a>".basename($filename);
			if ($description['hide']=="true") $string_title = "<span style=\"color : #ff0000; text-decoration : line-through;\">".$string_title."</span>";
			OpenTableTitle($string_title.$newfilestring);
		}

		else if (ltrim($description['name'])!=""){
			$string_title = "<a id=\"".create_id($filename)."\">".getIcon($ext,$icon_style)."</a>".$description['name'];
			if ($description['hide']=="true") $string_title = "<span style=\"color : #ff0000; text-decoration : line-through;\">".$string_title."</span>";
			OpenTableTitle("$string_title".$newfilestring);
		}

		?>
		<p align="left">
		<table style="border-style : hidden;" width="100%" cellpadding="0" cellspacing="4" class="download">
			<?php fd_load_php_code("download/include/autoinclude/fd_view_file/header"); ?>
			<tr>
				<td align="left" width="20%"><b><?php echo _FDNAME;?></b></td>
				<td><?php

				//ora è possibile scaricare il file anche cliccando sul suo nome
				if (ltrim($description['name'])==""){
// 					echo basename($filename);
					echo "<b><u><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=".rawurlencodepath($filename)."\" title=\""._FDDOWNLOADFILE.basename($filename)."\">".basename($filename)."</a></u></b>";
					//inserisco la chiave
					if (file_exists($filename.".$extsig")){
						echo " | (<b><a href=\"$filename.$extsig\" title=\""._FDGPGSIGNTITLE.basename($filename)."\">"._FDGPGSIGN."</a></b>)";
					}
				}

				else if (ltrim($description['name'])!=""){
// 					echo $description['name'];
					echo "<b><u><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=$filename\" title=\""._FDDOWNLOADFILE.basename($filename)."\">".$description['name']."</a></u></b>";
					//inserisco la chiave
					if (file_exists($filename.".$extsig")){
						echo " | (<b><a href=\"$filename.$extsig\" title=\""._FDGPGSIGNTITLE."\">"._FDGPGSIGN."</a></b>)";
					}
					}
				?></td>
			</tr>
			<?php
			global $showuploader;
			if ($showuploader =="1" and trim($description['uploadedby'])!=""){
				if (is_alphanumeric(trim($description['uploadedby']))){
					if (file_exists(get_fn_dir("users")."/".trim($description['uploadedby']).".php")){
						echo "<tr><td align=\"left\" valign=\"top\"><b>"._FDUPLOADER."</b></td>
						<td><a href=\"index.php?mod=none_Login&action=viewprofile&user=".$description['uploadedby']."\" title=\""._FDUPLOADERTITLE."\">".$description['uploadedby']."</a></td></tr>";
					}
					else {
						echo "<tr><td align=\"left\" valign=\"top\"><b>"._FDUPLOADER."</b></td>
						<td>".$description['uploadedby']."</td></tr>";
					}
				}
				else if (trim($description['uploadedby'])!=""){
					fdlogf("Uploader field is invalid (".$description['uploadedby'].")FDview: ".__LINE__);
				}

			}

			$desc = preg_replace("/<br \/>/i","",$description['desc']);
			$desc = preg_replace("/<br \/>/i","", $desc);
			if (ltrim($desc)!=""){
			echo "<tr><td align=\"left\" valign=\"top\"><b>"._FDDESC."</b></td>
				<td>".$description['desc']."</td>
			</tr>";
			}
			//se non è nullo mostro anche il campo "versione"
			if (trim($description['version'])!=""){
				echo "<tr><td align=\"left\"><b>"._FDVERSION."</b></td>";
				echo "<td>".$description['version']."</td></tr>";
			}

			//se sono entrambi settati mostro i campi personalizzati
			if (trim($description['userlabel'])!="" and trim($description['uservalue'])!=""){
				echo "<tr><td align=\"left\"><b>".$description['userlabel']."</b></td>";
				echo "<td>".$description['uservalue']."</td></tr>";

			}

			//se non è nullo mostro anche il campo "md5"
			if (ltrim($description['md5'])!=""){
				echo "<tr><td align=\"left\"><b>md5</b></td>";
				echo "<td>".$description['md5']."</td></tr>";
			}

			//se non è nullo mostro anche il campo "sha1"
			if (ltrim($description['sha1'])!=""){
				echo "<tr><td align=\"left\"><b>sha1</b></td>";
				echo "<td>".$description['sha1']."</td></tr>";
			}

			//se esiste lo screenshot
			global $extscreenshot;
			if (file_exists("$filename.$extscreenshot")){
				echo "<tr><td style=\"vertical-align : top;\"><b>Screenshot</b></td><td>";

				fd_show_screenshot($filename);
// 				echo "<a rel=\"lightbox\" href=\"$filename.$extscreenshot\" title=\""._FDSCREENSHOT.basename($filename)."\"><img src=\"$filename.$extscreenshot\" style=\"max-height : 100px;max-width : 100px;\"></a>";
				echo "</td></tr>";
			}



			//se si tratta di un file immagine:
			if (preg_match("/\.gif$|\.jpeg$|\.jpg$|\.png$|\.bmp$/i",$filename)){
				//se contemporaneamente non esiste uno screenshot:
				if (!file_exists("$filename.$extscreenshot")){
					echo "<tr><td style=\"vertical-align : top;\"><b>Screenshot</b></td><td>";
					echo "<a rel=\"lightbox\" href=\"$filename\" title=\""._FDSCREENSHOT.basename($filename)."\"><img src=\"$filename\" style=\"max-height : 100px;\" alt=\"thumb\"></a>";
					echo "</td></tr>";
				}
			}

			?>
			<?php if (trim($description['url'])==""){ ?>
			<tr>
				<td align="left"><b><?php echo _FDSIZE; ?></b></td>
				<td><?php echo $size; ?></td>
			</tr>
			<?php
			}//fine controllo size
			?>
			<tr>
				<td align="left" nowrap><b><?php echo _FDDATE; ?></b></td>
				<td><?php echo date(_FDDATEFORMAT, $description['time']) ?></td>
			</tr>

			<?php
			$track="";
			$track = $description['hits'];
			//se esiste un contatore
			if ($track!=""){
				?>
				<tr>
				<td align="left"><b><?php echo _FDHITS; ?></b></td>
				<td><?php echo $track; ?></td>
				</tr>

				<?php
			}

			if (isset($_POST['fdvote'])){
				if (function_exists("fd_add_vote"))
					fd_add_vote();
				//ricarico l'array con i dati in modo che vengano mostrati quelli aggiornati
				$description = load_description($filename);
				// se ho aggiunto un voto devo ricaricare la pagina per mostrarlo
				// -> (non è più necessario essendo stato inserito il codice prima che vengano
				// mostrati i dati
// 				echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=$mod\">";
			}

			echo "<tr>";
			echo "<td style=\"vertical-align : top;\">";
			echo "<b>"._FDRATING."&nbsp;</b>";//$voteaverage";
			echo "<br>(<i>".$description['totalvote']." "._FDVOTES."</i>)";
			echo "</td>";

			echo "<td style=\"vertical-align : top;\">";

			fd_show_vote($filename,$description);

			echo "</td>";

			echo "</tr>";

			//inserisco i plugin
			fd_load_php_code("download/include/autoinclude/fd_view_file/footer/"); ?>



			<?php
// 				echo "<tr><td></td><td></td></tr>";
				if ($showdownloadlink=="1")echo "<tr><th colspan=\"2\"><div align='center'><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=".rawurlencodepath($filename)."\" title=\""._FDDOWNLOADFILE.basename($filename)."\">"._FDDOWNLOAD."</a></div></th></tr>";

			//controlla se l'utente è admin
			if(fd_is_admin()){
				file_admin_panel($filename,$description);
			}
			?>
		</table>
		<?php
		CloseTableTitle();
}

/**
 * Crea una sezione che riepiloga tutti i file gestiti
 * da fd+ presenti nelle sottosezioni.
 * @since 0.7
 * @author Aldo Boccacci
 */
function fd_overview(){
global $archivedir,$theme,$overview_show_files;

if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
}

global $bgcolor2,$bgcolor1,$bgcolor3,$newfiletime;


//controllo $mod
$mod ="";
$mod = getparam("mod",PAR_GET,SAN_FLAT);
$mod= trim($mod);
if ($mod=="") return;
if (!fd_check_path($mod,"","false")) return;


$myforum = getparam("myforum", PAR_COOKIE, SAN_FLAT);
if ($myforum!="" and !is_alphanumeric($myforum)) return;

// fd_header();
	//elenco delle sottocartelle
	include_once("include/filesystem/DeepDir.php");
	$dirstemp = NULL;
	$dirstemp = new DeepDir();
	$dirstemp ->setDir("sections/$mod");
	$dirstemp ->load();

	$dirs = array();
	if (count($dirstemp -> dirs)>0){
		$dir = "";
		foreach ($dirstemp -> dirs as $dir){
			if (preg_match("/^none_/i",basename($dir)) and !fd_is_admin()) continue;
			if (preg_match("/none_archivedir/i",$dir) and !fd_is_admin()) continue;
			$dirs[] = $dir;
		}

	}
	if (count($dirs)>0)
		asort($dirs);


	if (fd_is_admin()){
	echo "<div align=\"center\"><br><form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
		<input type=\"hidden\" name=\"fdaction\" value=\"publishuserfileinterface\" readonly=\"readonly\">
		<input type=\"SUBMIT\" value=\""._FDPUBLISHFILES." (".get_n_waiting_files().")\"></form></div>";

	}

	if (count($dirs)>0){
		echo "<table style=\"width:100%; border-collapse: collapse;border:1px solid $bgcolor2;\" border=\"1\" cellspacing=\"0\">";
	}

	if (count($dirs)>0){
		$dir = "";
		foreach ($dirs as $dir){

		if (_FN_USERLEVEL<getsectlevel(preg_replace("/^sections\//i","",$dir))) continue;

		echo "<tr>";

		$files = array();

		$files = list_fd_files($dir);

		$thereisnewfile = FALSE;

		if (count($files)>0){
			$file = "";
			for($tmpcount = 0;$tmpcount<count($files);$tmpcount++){
				$file = $files[$tmpcount];
				$file = preg_replace("/\.description$/i","",$file);
				$desc = load_description($file);

				if ((time()-$desc['time'])<$newfiletime*3600) {
					$thereisnewfile = TRUE;
				}
			}
		}

		$subsection_image="";
		if(file_exists("$dir/section.png")) {
			$subsection_image = "$dir/section.png";
		} else $subsection_image = "themes/$theme/images/subsection.png";

		echo "<td bgcolor=\"$bgcolor3\"><h3><img src=\"$subsection_image\" alt=\"Subsection\">&nbsp;<a href=\"index.php?mod=".preg_replace("/^sections\//i","",$dir)."\" title=\""._GOTOSECTION.": ".preg_replace("/^sections\//i","",$dir)." \">".preg_replace("/^[0-9]*_/i","",preg_replace("/^$mod\//i","",preg_replace("/^sections\//i","",$dir)))."</a>";

		if (file_exists("images/mime/new.gif")){
			if ($thereisnewfile){
				echo "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file!\">";
			}
		}


		echo "</h3>";

		$purgedfiles = array();
		$proposedfiles = array();
		if (count($files)>0){
			$tmpfile = "";
			for ($tmpcount=0;$tmpcount<count($files);$tmpcount++){
				$tmpfile = $files[$tmpcount];
				if (!fd_user_can_view_file(preg_replace("/\.description$/i","",$tmpfile))) continue;
				if (preg_match("/section\.png$/i",$tmpfile)) continue;
				$purgedfiles[] = $tmpfile;
				if (user_uploaded(preg_replace("/\.description$/i","",$tmpfile))) $proposedfiles[]= $tmpfile;
			}

		}

		echo count($purgedfiles)." files";

		$subdirstemp = array();
		$subdirstemp = list_subdirs($dir);
		$subdirs = array();
		if (count($subdirstemp)>0){
			$subdirtemp = "";
			foreach($subdirstemp as $subdirtemp){
				if (preg_match("/^none/i",basename($subdirtemp))) continue;
				if (!is_fd_sect($subdirtemp)) continue;
				if (!user_can_view_section(preg_replace("/^sections\//i","",$subdirtemp))) continue;
				$subdirs[] = $subdirtemp;
			}

		}

		if (count($subdirs)>0){
			echo "&nbsp;-&nbsp;".count($subdirs). " dirs";
		}

		if (fd_is_admin() and count($proposedfiles)>0){
			echo "&nbsp;-&nbsp;<span style=\"color : #ff0000; text-decoration : underline;\">".count($proposedfiles)." "._FDWAITINGFILES."</span>";
		}

		if (count($files)>0 and $overview_show_files=="1"){
			$file = "";
			for ($tmpcount=0;$tmpcount<count($files);$tmpcount++){
				$file = $files[$tmpcount];
				$file = preg_replace("/\.description$/i","",$file);
				if (!fd_user_can_view_file($file)) continue;
				$desc = load_description($file);
				echo "<br>&nbsp;&nbsp;&nbsp;&#187;&nbsp;<a href=\"index.php?mod=".rawurlencodepath(preg_replace("/^sections\//i","",$dir))."#".rawurlencodepath(create_id($file))."\" title=\""._FDDOWNLOADFILE.basename($file)."\">".basename($file)."</a>";
				if ((time()-$desc['time'])<$newfiletime*3600) {
					echo "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file!\">";
				}
			}
		}

		echo "</td>";

		echo "</tr>";

		}

	}

	if (count($dirs)>0){
		echo "</table>";
	}


	if(fd_is_admin()){
		if (is_writable("sections/$mod")){
			//pulsante per creare una nuova sezione
			echo "<div align=\"center\"><br><br><form action=\"index.php?mod=none_Fdplus&amp;\" method=\"POST\">
			<input type=\"hidden\" name=\"fdmod\" value=\"$mod\" readonly=\"readonly\">
			<input type=\"hidden\" name=\"fdaction\" value=\"createsectinterface\" readonly=\"readonly\">
			<input type=\"SUBMIT\" value=\""._FDCREATESECTDOWNLOAD."\"></form></div>";
		}
		else echo "<br><span style=\"color : #ff0000;\">"._FDREADONLYDIR."</span>";
	}

// 	module_copyright("FDplus",get_fd_version(),"<b>Aldo Boccacci</b> aka Zorba","zorba_(AT)tin.it", "http://www.aldoboccacci.it", "Gpl version 2.0");

/**
 * Al momento inutilizzata. Servirà per creare un header nella sezione di riepilogo (ce n'è la necessità?)
 */
function fd_header(){
return;
global $bgcolor2,$bgcolor1,$bgcolor3,$newfiletime;

//controllo $mod
$mod ="";
$mod = getparam("mod",PAR_GET,SAN_FLAT);
$mod= trim($mod);
if ($mod=="") return;
if (!fd_check_path($mod,"","false")) return;


//cerca la root
$rootsection = "";
if (file_exists("sections/$mod/download")){
	$rootsection = $mod;
}

echo "<table bgcolor=\"$bgcolor3\" style=\"width:100%; border-collapse: collapse;border:1px solid $bgcolor2;\" border=\"1\" cellspacing=\"0\">";
echo "<tr>";
echo "<td style=\"width:50%;\">[<a href=\"index.php?mod=$rootsection\" title=\""._GOTOSECTION.": $rootsection\">Main</a>]</td>";
echo "<td style=\"width:50%;\">";
echo "<div align=\"center\">
<form action=\"index.php?mod=none_Search\" method=\"post\">
<input type=\"hidden\" name=\"mod\" value=\"none_Search\" />
<label for=\"textsect\" >"._CERCA.":</label>
<input type=\"text\" name=\"find\" size=\"16\" id=\"textsect\" />
<input type=\"hidden\" name=\"where\" value=\"04_Download\">
<input type=\"radio\" value=\"AND\" id=\"AND\" name=\"method\" alt=\"AND search\" checked=\"checked\" /><label for=\"AND\">AND</label>
<input type=\"radio\" value=\"OR\" id=\"OR\" name=\"method\" alt=\"OR search\" /><label for=\"OR\">OR</label>
<input type=\"submit\" value=\""._CERCA."\" />
</form>
</div>
";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "<br>";

}
}

/**
 * Mostra l'intestazione della sezione di download singola
 *
 */
function fd_show_section_header($mod){

// funzione ridefinibile
if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
}

//controllo $mod
$mod = getparam($mod, PAR_NULL,SAN_FLAT);
if ($mod!=""){

	if (!fd_check_path($mod,"","false")) return;
}
else return;

$myforum = _FN_USERNAME;

global $bgcolor3,$bgcolor2,$icon_style,$extensions,$theme,$newfiletime,$archivedir;

echo "<table bgcolor=\"$bgcolor3\" style=\"width:100%; border-collapse: collapse;border:1px solid; border-color: $bgcolor2;\" border=\"1\" cellspacing=\"0\">";
echo "<tr><td colspan=\"2\" style=\"border-color: $bgcolor2;\"><b>"._FDSUMMARY.":</b></td></tr>";
echo "<tr>";
echo "<td style=\"border-color: $bgcolor2;\"><b>Files:</b></td>";
echo "<td style=\"border-color: $bgcolor2;\"><b>"._FDSUBDIRS.":</b></td>";
echo "</tr>";

$files = array();
$files = list_fd_files("sections/$mod/");
echo "<tr>";
echo "<td style=\"width: 50%;vertical-align : top; border-color: $bgcolor2;\">";

if (count($files)>0){
	$file = "";
	for ($tmpcount=0;$tmpcount<count($files);$tmpcount++){
		$file = $files[$tmpcount];
		$file = preg_replace("/\.description$/i","",$file);
		$desc = array();
		$desc = load_description($file);
		if (fd_user_can_view_file(preg_replace("/\.description$/i","",$file),$desc)){
			$fileinfo = array();
			$fileinfo = pathinfo($file);
			$ext ="";
			if (isset($fileinfo['extension'])) $ext = $fileinfo['extension'];
			else continue;
			$extensions_array= array();
			$extensions_array = explode(",",strtolower($extensions));
			if (!in_array(strtolower($ext),$extensions_array)){
				continue;
			}
			//se è un file php ritorno
			if (preg_match("/php/i",$ext)) {
				continue;
			}

			$name = "";
			if (trim($desc['name']=="")){
				$name = basename($file);
			}
			else $name = $desc['name'];



			echo getIcon($ext,$icon_style)."<a href=\"index.php?mod=".rawurlencodepath($mod)."#".create_id(basename($file))."\" title=\""._FDDOWNLOADFILE."$name\">$name</a>";

			if (file_exists("images/mime/new.gif")){
			if ((time()-$desc['time'])<$newfiletime*3600) {
				if (file_exists("images/mime/new.gif")){
					if (strrchr($fileinfo['dirname'],"/")!="/".$archivedir){
					echo "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file!\">";
					}
				}
			}
		}

			echo "<br/>";
		}

	}

}

//fine files
echo "</td>";

//inizio sezioni
echo "<td style=\"width: 50%;vertical-align : top; border-color: $bgcolor2;\">";
$dirstemp = array();
$dirstemp = list_subdirs("sections/$mod/");
$dirs = array();
if (count($dirstemp)>0){
	$dirtemp= "";
	foreach ($dirstemp as $dirtemp){
		if (preg_match("/^none_/i",basename($dirtemp))) continue;
		if (preg_match("/none_archivedir/i",$dirtemp) AND !fd_is_admin()) continue;
		if (!user_can_view_section(preg_replace("/^sections\//i","",$dirtemp))) continue;
		$dirs[] = $dirtemp;
	}

}

if (count($dirs)>0){
	$dir = "";
	foreach ($dirs as $dir){

		$tempfiles = array();

		$tempfiles = list_fd_files("$dir");

		$thereisnewfile = FALSE;

		if (count($tempfiles)>0){
			$file = "";
			foreach ($tempfiles as $file){
				$file = preg_replace("/\.description$/i","",$file);
				$desc = load_description($file);

				if ((time()-$desc['time'])<$newfiletime*3600) {
					$thereisnewfile = TRUE;
				}
			}
		}

		$subsection_image="";
		if(file_exists("$dir/section.png")) {
			$subsection_image = "$dir/section.png";
		} else $subsection_image = "themes/$theme/images/subsection.png";

		echo "<img src=\"$subsection_image\" alt=\"Subsection\">&nbsp;<a href=\"index.php?mod=".preg_replace("/^sections\//i","",$dir)."\" title=\""._GOTOSECTION.": ".preg_replace("/^sections\//i","",$dir)."\">".basename($dir)."</a>";

		if ($thereisnewfile and file_exists("images/mime/new.gif")) echo "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file\">";

		echo "<br>";
	}

}

if (is_dir("sections/$mod/none_archivedir")){
	echo "<img src=\"themes/$theme/images/subsection.png\" alt=\"Subsection\">&nbsp;<i><a href=\"index.php?mod=$mod/none_archivedir\" title=\""._GOTOSECTION.": $mod/none_archivedir\">"._FDARCHIVEDIR."</a></i><br>";
}

//fine sezioni
echo "</td>";

//fine di files/sezioni
echo "</tr>";
if (fd_is_admin()){
	echo "<tr>";
	echo "<td colspan=\"2\"><b>Amministrazione:</b></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td style=\"width: 50%;vertical-align : top;\">";
	//pulsante per aggiungere un nuovo file
	echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
	<input type=\"hidden\" name=\"path\" value=\"sections/$mod\" readonly=\"readonly\" />
	<input type=\"hidden\" name=\"fdaction\" value=\"addfile\" readonly=\"readonly\" />
	<input type=\"hidden\" name=\"fdfile\" value=\"new\" readonly=\"readonly\" />
	<input type=\"SUBMIT\" value=\""._FDADDFILE."\" /></form>";

	//pulsante per aggiungere un nuovo file
	echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
	<input type=\"hidden\" name=\"path\" value=\"sections/$mod\" readonly=\"readonly\" />
	<input type=\"hidden\" name=\"fdaction\" value=\"addurl\" readonly=\"readonly\" />
	<input type=\"hidden\" name=\"fdfile\" value=\"new\" readonly=\"readonly\" />
	<input type=\"SUBMIT\" value=\""._FDADDURL."\" /></form>";

	echo "</td>";

	echo "<td style=\"width: 50%;vertical-align : top;\">";
	echo "<form action=\"index.php?mod=none_Fdplus\" method=\"POST\">
	<input type=\"hidden\" name=\"fdmod\" value=\"$mod\" readonly=\"readonly\">
	<input type=\"hidden\" name=\"fdaction\" value=\"createsectinterface\" readonly=\"readonly\">
	<input type=\"SUBMIT\" value=\""._FDCREATESECTDOWNLOAD."\"></form>";
	echo "</td>";
	echo "</tr>";
}
echo "</table>";
}

/**
 * Mostra lo screenshot per il file indicato come parametro
 * Sono state prese parti di codice della gallery.
 *
 * @param string $file il file di cui mostrare lo screenshot.
 * @since 0.8
 * @author Aldo Boccacci
 */
function fd_show_screenshot($file){
	$file = getparam($file,PAR_NULL,SAN_FLAT);
	if (trim($file=="")) return;
	global $extscreenshot;
	if (!file_exists($file.".$extscreenshot")) return;

	// controlla se il web server ha le librerie GD
	if(!function_exists("ImageJpeg")) {
		$gd_flag = FALSE;
		//return; // se le librerie non sono installate, al posto della miniatura sarà visualizzata l'immagine originale rimpicciolita
	} else $gd_flag = TRUE;

	//DEVO DISABILITARE GD PERCHÉ DÀ PROBLEMI CON ALCUNE IMMAGINI
	$gd_flag=FALSE;

	echo "<a rel=\"lightbox\" href=\"$file.$extscreenshot\" title=\"".basename($file)."\">";
	// se dimensioni < di quelle scelte, stampo immagine, altrimenti creo thumbnail
	$size = getimagesize("$file.$extscreenshot");
	$hw_thumbs = 150;

	if($size[0]<$hw_thumbs AND $size[1]<$hw_thumbs) {
		echo "<img border=\"1\" src=\"$file.$extscreenshot\" alt=\"thumb\" />";
	}
	elseif($gd_flag==FALSE) {
		if($size[0]>$size[1]) {
			$new_w = $hw_thumbs;
			$new_h = intval($hw_thumbs * $size[1] / $size[0]);
		} else {
			$new_w = intval($hw_thumbs * $size[0] / $size[1]);
			$new_h = $hw_thumbs;
		}
		echo "<img border=\"1\" src=\"$file.$extscreenshot\" height=\"$new_h\" width=\"$new_w\" alt=\"thumb\" />";
	}
	else {
		echo "<img border=\"1\" src=\"gallery/thumb.php?image=$file.$extscreenshot&amp;hw=$hw_thumbs\" alt=\"thumb\" />";
	}
	echo "</a>";

}
?>