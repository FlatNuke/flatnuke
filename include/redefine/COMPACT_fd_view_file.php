<?php
/**
 * Visualizzazione compatta per le sezioni di donwload
 * Per attivare togliere il prefisso "COMPACT_".
 * Per disattivare aggiungere un prefisso a scelta al nome
 * del file.
 *
 * @author Aldo Boccacci
 * @since Flatnuke 3.0
 */

if (!fd_check_path($filename,"sections/","")) {
		fdlogf("\$filename \"".strip_tags($filename)."\" is not valid! FDview: ".__LINE__);
		return;
	}
	global $extensions,$icon_style,$archivedir,$newfiletime,$extsig,$showdownloadlink;
	if ($extsig=="") $extsig="sig";
	$extensions_array=array();
	$extensions_array = explode(",",$extensions);
//estraggo l'estensione
		$fileinfo = array();
		$fileinfo = pathinfo($filename);
		$ext ="";
		if (isset($fileinfo['extension'])) $ext = $fileinfo['extension'];
		else return;

		if (!in_array($ext,$extensions_array)){
			return;
		}

		//se e' un file php ritorno
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
		/* Calcola la data di ultimo aggiornamento */
		$lastupdate="";
		if (!defined("_FDDATEFORMAT2"))
		define("_FDDATEFORMAT2", "d/m/Y");
		$lastupdate = date(_FDDATEFORMAT2, getfiletime($filename) );

		//NUOVA FUNZIONE PER IL CARICAMENTO DELLA DESCRIZIONE
		$description=array();
		$description= load_description($filename);
		//solo gli amministratori possono vedere i file nascosti
		if ($description['hide']=="true" and !fd_is_admin()) return;
		//controllo se il livello dell'utente e' adeguato a quello del file
		$myforum="";
		$myforum=get_username();
		if ($description['level']!="-1"){
			if (trim($myforum)!="" and versecid($myforum)){
				if ($description['level']>getLevel($myforum,"home")){
					return;
				}
			}
			else return;

		}
		$newfilestring="";
		//Calcolo se inserire l'icona per il nuovo file
		if (file_exists("images/mime/new.gif")){
			if ((time()-getfiletime($filename))<$newfiletime*3600) {
// 				echo "dirname: ".strrchr($fileinfo['dirname'],"/");
				if (strrchr($fileinfo['dirname'],"/")!="/".$archivedir){
					$newfilestring = "&nbsp;<img src=\"images/mime/new.gif\" alt=\"new file!\">";
				}
			}
		}

		else $newfilestring = "";

		$string_title="";


		//VISUALIZZO IL FILE
		if (ltrim($description['name'])==""){
			$string_title = "<a id=\"".create_id($filename)."\" title=\"mime icon\">".getIcon($ext,$icon_style)."</a><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=".rawurlencodepath($filename)."\" title=\""._FDDOWNLOADFILE.basename($filename)."\">".basename($filename)."</a>";

			//se è nascosto
			if ($description['hide']=="true") $string_title = "<span style=\"color : #ff0000; text-decoration : line-through;\"><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=".rawurlencodepath($filename)."\" title=\""._FDDOWNLOADFILE.basename($filename)."\">".$string_title."</a></span>";


			echo $string_title.$newfilestring;
		}

		else if (ltrim($description['name'])!=""){
			$string_title = "<a id=\"".create_id($filename)."\">".getIcon($ext,$icon_style)."</a><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=".rawurlencodepath($filename)."\" title=\""._FDDOWNLOADFILE.basename($filename)."\">".$description['name']."</a>";


			//se il file è nascosto
			if ($description['hide']=="true") $string_title = "<span style=\"color : #ff0000; text-decoration : line-through;\"><a href=\"index.php?mod=none_Fdplus&amp;fdaction=download&amp;url=".rawurlencodepath($filename)."\" title=\""._FDDOWNLOADFILE.basename($filename)."\">".$string_title."</a></span>";


			echo $string_title.$newfilestring;
		}

		echo " ($size, $lastupdate) <a href=\"javascript:ShowHideDiv('fdfileinfo".create_id($filename)."');\" style=\"text-decoration: none;\">+</a><br>";



		//DESCRIZIONE
		$desc = preg_replace("/<br \/>/","",$description['desc']);
			$desc = preg_replace("/<br \/>/","", $desc);
			if (ltrim($desc)!=""){
			echo "<div style=\"margin-left : 20px;\"  id=\"fdfileinfodesc".create_id($filename)."\"><i>";
			echo "".$description['desc']."</i></div>";
		}

		echo "<div style=\"margin-left : 20px;display:none;\"  id=\"fdfileinfo".create_id($filename)."\"><i>";
		//UPLOADEDBY
		global $showuploader;
		if ($showuploader =="1" and trim($description['uploadedby'])!=""){
			if (is_alphanumeric(trim($description['uploadedby']))){
				if (file_exists(get_fn_dir("users")."/".trim($description['uploadedby']).".php")){
					echo "<b>"._FDUPLOADER."</b>:
					<a href=\"index.php?mod=none_Login&action=viewprofile&user=".$description['uploadedby']."\" title=\""._FDUPLOADERTITLE."\">".$description['uploadedby']."</a><br>";
				}
				else {
					echo "<b>"._FDUPLOADER."</b>: ".$description['uploadedby']."<br>";
				}
			}
			else if (trim($description['uploadedby'])!=""){
				fdlogf("Uploader field is invalid (".$description['uploadedby'].")FDview: ".__LINE__);
			}
		}

				//conteggio
		$track="";
		$track = getDownloads($filename);
		//se esiste un contatore
		if ($track!=""){
			echo "<b>"._FDHITS."</b>: $track<br>";
		}
		//VOTO
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
		echo "<b>"._FDRATING."&nbsp;</b>";//$voteaverage";
		echo "(<i>".$description['totalvote']." "._FDVOTES."</i>):";
		fd_show_vote($filename,$description);
		//inserisco la chiave
		if (file_exists($filename.".$extsig")){
			echo "(<b><a href=\"$filename.$extsig\" title=\""._FDGPGSIGNTITLE.basename($filename)."\">"._FDGPGSIGN."</a></b>)<br>";
		}

		//se esiste lo screenshot
		global $extscreenshot;
		if (file_exists("$filename.$extscreenshot")){
// 			echo "<b>Screenshot</b>:";
			fd_show_screenshot($filename);
			echo "<br>";
		}

		//se si tratta di un file immagine:
		if (preg_match("/\.gif$|\.jpeg$|\.jpg$|\.png$|\.bmp$/i",$filename)){
			//se contemporaneamente non esiste uno screenshot:
			if (!file_exists("$filename.$extscreenshot")){
// 				echo "<tr><td style=\"vertical-align : top;\"><b>Screenshot</b></td><td>";
				echo "<a rel=\"lightbox\" href=\"$filename\" title=\""._FDSCREENSHOT.basename($filename)."\"><img src=\"$filename\" style=\"max-height : 100px;\"></a><br>";
// 				echo "</td></tr>";
			}
		}

		//VERSIONE
		//se non e' nullo mostro anche il campo "versione"
		if (trim($description['version'])!=""){
			echo "<b>"._FDVERSION."</b>: ";
			echo $description['version']."<br>";
		}

		//CAMPI PERSONALIZZATI
		//se sono entrambi settati mostro i campi personalizzati
		if (trim($description['userlabel'])!="" and trim($description['uservalue'])!=""){
			echo "<b>".$description['userlabel']."</b>: ";
			echo $description['uservalue']."<br>";

		}

		//se non e' nullo mostro anche il campo "md5"
		if (ltrim($description['md5'])!=""){
			echo "<b>md5</b>: ";
			echo $description['md5']."<br>";
		}

		//se non e' nullo mostro anche il campo "sha1"
		if (ltrim($description['sha1'])!=""){
			echo "<b>sha1</b>: ";
			echo $description['sha1']."<br>";
		}


		//stampo la dimensione (se è un file locale)
// 		if (trim($description['url'])=="")
// 			echo "<b>"._FDSIZE."</b>: $size<br>";

		if(fd_is_admin()){
			echo "<div style=\"border: solid 1px;text-align: center;\"";
			file_admin_panel($filename,$description);
			echo "</div><br>";

		}
		else echo "<br>";
		//FINE PANNELLO NASCOSTO
		echo "</i></div>";


?>