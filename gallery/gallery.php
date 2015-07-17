<?php

/*
 * FlatImageGallery
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the license, or any later version.
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

/*
 * Blocco per FlatNuke (http://flatnuke.sourceforge.net) di Simone Vellei che
 * permette di creare automaticamente una galleria di immagini a partire da
 * quelle che ci sono nella directory dove e' presente questo file.
 *
 * Autore    Marco Segato  <segatom@users.sourceforge.net>
 * Website   http://marcosegato.altervista.org
 * Versione  20130210
 *
 */

// informazione per log
$zone = "Gallery";

// previene che il blocco sia eseguito direttamente e redirige a index.php
if (preg_match("/gallery.php/i",$_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

// configurazione flatnuke
include ("config.php");

// security declarations
$myforum      = getparam("myforum", PAR_COOKIE, SAN_FLAT);
$ip_indirizzo = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
$mod          = getparam("mod",PAR_GET,SAN_FLAT);
$req          = getparam("REQUEST_URI", PAR_SERVER, SAN_NULL);

// configurazione galleria
$gallerycfgdir = get_fn_dir("var")."/gallery";										// directory
$galleryconf   = get_fn_dir("var")."/gallery/".str_replace("/","",$mod).".conf.xml";	// configurazione gallery
$gallerycomm   = get_fn_dir("var")."/gallery/".str_replace("/","",$mod).".comm.xml";	// commenti a galleria
$galleryfeed   = $gallerycfgdir."/".str_replace("/","_",$mod).".xml";	// rss feed

// colori
global $bgcolor2, $bgcolor3;

// opzione di sicurezza!
if(strstr($req,"myforum="))
	die(_NONPUOI);

// controlla se il web server ha le librerie GD
if(!function_exists("ImageJpeg")) {
	if(is_admin())
		print("<div class=\"centeredDiv\">"._FIG_TXT_NOGD."</div><br>");
	$gd_flag = FALSE;
	//return; // se le librerie non sono installate, al posto della miniatura sarà visualizzata l'immagine originale rimpicciolita
} else $gd_flag = TRUE;

// controlla esistenza directory con file di configurazione in /var, se non c'e' la crea
if(!file_exists($gallerycfgdir)) {
	fn_mkdir($gallerycfgdir, 0777);
}

// controlla esistenza file di configurazione in /var/gallery, se non c'e' lo crea
if(!file_exists($galleryconf)) {
	fnwrite($galleryconf, "<?xml version='1.0' encoding='UTF-8'?>\n<columns>2</columns>\n<rows>3</rows>\n<dim-thumbs>175</dim-thumbs>\n<commenti>N</commenti>\n<reg-uploads>N</reg-uploads>\n", "w+", array("nonull"));
}

// controlla esistenza file commenti in /var/gallery, se non c'e' lo crea
if(!file_exists($gallerycomm)) {
	fnwrite($gallerycomm, "<?xml version='1.0' encoding='UTF-8'?>\n", "w+", array("nonull"));
}

// controlla esistenza feed rss, se non c'e' lo crea
if(!file_exists($galleryfeed)) {
	generate_Gallery_RSS($mod);
}

// azioni in POST da configurazione admin
$action = getparam("action", PAR_POST, SAN_FLAT);
if (($action != "") AND is_admin()) {
	// configurazione numero colonne e righe tabella immagini
	if($action=="numcolsrows") {
		$num_cols=getparam("num_cols", PAR_POST, SAN_FLAT);
		$num_rows=getparam("num_rows", PAR_POST, SAN_FLAT);
		if(($num_cols!="") AND ($num_rows!="") AND is_numeric($num_cols) AND is_numeric($num_rows)) {
			$file_dati = get_file($galleryconf);
			$file_dati = str_replace("<columns>".get_xml_element("columns",$file_dati)."</columns>","<columns>".$num_cols."</columns>",$file_dati);
			$file_dati = str_replace("<rows>".get_xml_element("rows",$file_dati)."</rows>","<rows>".$num_rows."</rows>",$file_dati);
			fnwrite($galleryconf, "$file_dati", "w+", array("nonull"));
			fnlog($zone, "$ip_indirizzo||$myforum||Number of columns and rows saved.");
		}
	}
	// configurazione dimensione thumbs
	elseif($action=="dimthumbs") {
		$hw_thumbs = getparam("hw_thumbs", PAR_POST, SAN_FLAT);
		if($hw_thumbs!="") {
			$file_dati = get_file($galleryconf);
			$file_dati = str_replace("<dim-thumbs>".get_xml_element("dim-thumbs",$file_dati)."</dim-thumbs>","<dim-thumbs>".$hw_thumbs."</dim-thumbs>",$file_dati);
			fnwrite($galleryconf, "$file_dati", "w+", array("nonull"));
			fnlog($zone, "$ip_indirizzo||$myforum||Thumbs dimensions saved.");
		}
	}
	// configurazione commenti
	elseif($action=="confcommenti") {
		$commenti=getparam("commenti", PAR_POST, SAN_FLAT);
		if(($commenti!="") AND (($commenti=="Y") OR ($commenti=="N"))) {
			$file_dati = get_file($galleryconf);
			$file_dati = str_replace("<commenti>".get_xml_element("commenti",$file_dati)."</commenti>","<commenti>".$commenti."</commenti>",$file_dati);
			fnwrite($galleryconf, "$file_dati", "w+", array("nonull"));
			fnlog($zone, "$ip_indirizzo||$myforum||Comments configuration saved.");
		}
	}
	// configurazione upload da utenti registrati
	elseif($action=="upload_by_regusers") {
		$reguploads=getparam("reguploads", PAR_POST, SAN_FLAT);
		if(($reguploads!="") AND (($reguploads=="Y") OR ($reguploads=="N"))) {
			$file_dati = get_file($galleryconf);
			$file_dati = str_replace("<reg-uploads>".get_xml_element("reg-uploads",$file_dati)."</reg-uploads>","<reg-uploads>".$reguploads."</reg-uploads>",$file_dati);
			fnwrite($galleryconf, "$file_dati", "w+", array("nonull"));
			fnlog($zone, "$ip_indirizzo||$myforum||Registered users upload configuration saved.");
		}
	}
	// eliminazione file commenti e creazione di uno vuoto
	elseif($action=="delcomm") {
		unlink($gallerycomm);
		fnwrite($gallerycomm, "<?xml version='1.0' encoding='UTF-8'?>\n", "w+", array("nonull"));
		fnlog($zone, "$ip_indirizzo||$myforum||Comments deleted.");
	}
	// upload nuova immagine
	elseif($action=="upload") {
		if(!is_writeable(getparam("path", PAR_POST, SAN_FLAT))) {
			?><script>alert('<?php echo _FIG_ALERTNOTWR?>');</script><?php
		}
		// verifico che sia effettivamente un'immagine
		elseif(getimagesize($_FILES['file_up']['tmp_name']))
			fig_upload(getparam("path", PAR_POST, SAN_FLAT));
		else {
			fnlog($zone, "$ip_indirizzo||$myforum||Tried to upload a file that's not an image.");
			?><script>alert('<?php echo _FIG_ALERTNOTIMG?>');</script><?php
		}
		fnlog($zone, "$ip_indirizzo||$myforum||New image uploaded.");
		//Genera feed RSS della gallery
		generate_Gallery_RSS($mod);
	}
	// rinomina immagine
	elseif($action=="rename") {
		$rename_from=getparam("rename_from",PAR_POST,SAN_FLAT);
		$rename_to=getparam("rename_to",PAR_POST,SAN_FLAT);
		if($rename_from=="") {
			// nessuna scelta effettuata, non faccio nulla :)
		}
		elseif(preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($rename_to))) {
			if(rename($rename_from, getparam("path", PAR_POST,SAN_FLAT)."/".$rename_to)) {
				fnlog($zone, "$ip_indirizzo||$myforum||Image renamed.");
				?><script>alert('<?php echo _FIG_RENAMEOK?>');</script><?php
			        //Genera feed RSS della gallery
			        generate_Gallery_RSS($mod);
			} else {
				?><script>alert('<?php echo _FIG_RENAMENO?>');</script><?php
			}
		} else {
			fnlog($zone, "$ip_indirizzo||$myforum||Tried to rename the image $rename_from as $rename_to.");
			?><script>alert('<?php echo _FIG_RENAMENO?>');</script><?php
		}
	}
	// eliminazione immagine
	elseif($action=="delete") {
		$file_del=getparam("file_del",PAR_POST,SAN_FLAT);
		if($file_del=="" OR !preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($file_del))) {
			// nessuna scelta effettuata o non e' immagine, non faccio nulla :)
		} elseif(unlink($file_del)) {
			?><script>alert('<?php echo _FIG_ALERTIMGDEL?>');</script><?php
		} else {
			?><script>alert('<?php echo _FIG_ALERTNOTWR?>');</script><?php
		}
		fnlog($zone, "$ip_indirizzo||$myforum||Image deleted.");
		//Genera feed RSS della gallery
		generate_Gallery_RSS($mod);
	}
}

// upload nuova immagine da parte di un utente registrato
$path=getparam("path", PAR_POST,SAN_FLAT);

if($action=="regupload") {
	if(!is_writeable($path)) {
		?><script>alert('<?php echo _FIG_ALERTNOTWR?>');</script><?php
	}
	// verifico che sia effettivamente un'immagine
	elseif(getimagesize($_FILES['file_up']['tmp_name']))
		fig_upload($_POST['path']);
	else {
		?><script>alert('<?php echo _FIG_ALERTNOTIMG?>');</script><?php
	}
	fnlog($zone, "$ip_indirizzo||$myforum||New image uploaded by $myforum.");
	//Genera feed RSS della gallery
	generate_Gallery_RSS($mod);
}
// azioni in POST per salvataggio commenti
elseif(isset($_POST['writecomm'])) {
	// retrieve captcha code
	$captcha = getparam("captcha", PAR_POST, SAN_FLAT);
	$captcha = strip_tags($captcha);
	if($captcha!="") {
		// checking the value of anti-spam code inserted
		include("include/captcha/fncaptcha.php");
		$fncaptcha = new fncaptcha();
		$captchaok = $fncaptcha->checkCode($captcha);
		// anti-spam code is NOT right
		if(!$captchaok) {
			echo "<div style='text-align: center;'><b/>"._CAPTCHA_ERROR."</b><br>";
			// back or automatic redirect to the index after 2 seconds
			?><p><a href="javascript:history.back()">&lt;&lt;<?php echo _INDIETRO?></a></p></div><?php
			return;
		}
	} else {
		// no valid captcha value is passed, go away
		header("Location: index.php?mod=$mod");
		return;
	}
	// captcha ok, save the comment
	if(isset($_POST['by'])) $by = stripslashes(htmlspecialchars($_POST['by']));
		else $by = "";
	if(isset($_POST['body'])) $what = htmlspecialchars($_POST['body']);
		else $what = "";
	$what = str_replace("\r","",$what);
	$what = str_replace("\n","<br>",$what);
	$string = get_file($gallerycomm);

	// si tratta del primo commento inserito
	if(!stristr($string, "<comments>"))
		$string = "<?xml version='1.0' encoding='UTF-8'?>\n<comments>\n\t<comment>\n\t\t<by>$by</by>\n\t\t<what>$what</what>\n\t</comment>\n</comments>\n";
	// c'e' almeno un commento già inserito
	else $string = str_replace("</comments>", "\t<comment>\n\t\t<by>$by</by>\n\t\t<what>$what</what>\n\t</comment>\n</comments>",$string);

	fnwrite($gallerycomm, stripslashes("$string"), "w", array("nonull"));
	fnlog($zone, "$ip_indirizzo||$myforum||New comment.");

	//echo "<script>window.location='index.php?mod=$mod';</script>";
	header("Location: index.php?mod=$mod");
	return;
}

// directory dove sono presenti le immagini
$path = "sections/$mod";
// carico tutti i files immagine in un array
$dir = opendir($path);
$n_file = 0;
while ($filename = readdir($dir)) {
	if(@getimagesize($path."/".$filename)!=FALSE AND !stristr($filename,"none_")) {
		$array_dir[$n_file] = $filename;
		$n_file++;
	}
}
closedir($dir);
// ordinamento alfabetico dell'array
if($n_file!=0) {
	natcasesort($array_dir);
	$array_dir = array_values($array_dir);
}

// visualizzazione maschera per inserimento commenti
if (getparam("inscomm", PAR_GET, SAN_FLAT) !="") {
	OpenTableTitle(_FIG_ADDCOMM);
	if($myforum!="" OR $guestcomment==1) {			// controllo se utente non registrato può postare o no
		if($myforum=="")
			$by = _FIG_SCON;					// utente sconosciuto
		else $by = $myforum;				// utente registrato
		echo "<form action=\"index.php?mod=$mod\" method=\"post\">
			<input type=\"hidden\" name=\"writecomm\" />
			<input type=\"hidden\" name=\"by\" value=\"$by\" /><br>
			<strong>"._FIG_COMMENTI."</strong><br>
			<textarea rows=\"7\" name=\"body\" style=\"width:95%;\"></textarea><br>";
		// starting session for anti spam checks with captcha
		echo "<div style='padding:1em'>";
		include("include/captcha/fncaptcha.php");
		$fncaptcha = new fncaptcha();
		$fncaptcha->generateCode();
		$fncaptcha->printCaptcha("captcha","captcha");
		echo "</div>";
		// go
		echo "<input type=\"submit\" value=\""._FIG_FINVIA."\" />
		</form><br>";
	} else echo _FIG_DEVIREG." <strong>".$sitename."</strong> "._FIG_DEVIREG2; // utenti non registrati non possono postare
	CloseTableTitle();
	echo "<br>";
}

// carico la configurazione della galleria in variabili
$file_xml = get_file($galleryconf);
$cols = get_xml_element("columns",$file_xml);		// numero di colonne tabella immagini (2 di default)
if($cols=="")
	$cols = 2;
$rows = get_xml_element("rows",$file_xml);			// numero di righe tabella immagini (3 di default)
if($rows=="")
	$rows = 3;
$hw_thumbs = get_xml_element("dim-thumbs",$file_xml);	// dimensioni thumbnails (175 di default)
if($hw_thumbs=="")
	$hw_thumbs = 175;
$commenti =  get_xml_element("commenti",$file_xml);		// abilitazione commenti (abilitati di default)
if($commenti=="")
	$commenti = "Y";
$reguploads =  get_xml_element("reg-uploads",$file_xml);	// abilitazione uploads da utenti registrati (disabilitati di default)
if($reguploads=="")
	$reguploads = "N";

// menu_admin - gestione galleria
if(is_admin()) {
	?><div>
	<input type="button" value="<?php echo _CONFIG?>" onclick="ShowHideDiv('confgalleryadmin');" />
	</div><br>
	<div id="confgalleryadmin" style="display:none"><?php
	OpenTableTitle(_FIG_ADMIN_SECT);
	?><table style="border-spacing: 10px; border-collapse: separate;"><tbody>
	<!-- configurazione numero colonne e righe tabella immagini -->
	<tr><td>
	<script type="text/javascript">
	function validate_table_image_form()
		{
			if(document.getElementById('num_cols').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FIG_TXT_NUMCOLS?>');
					document.getElementById('num_cols').focus();
					document.getElementById('num_cols').value='';
					return false;
				}
			else if(document.getElementById('num_rows').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FIG_TXT_NUMROWS?>');
					document.getElementById('num_rows').focus();
					document.getElementById('num_rows').value='';
					return false;
				}
			else return true;
		}
	</script>

	<form action="index.php?mod=<?php echo $mod?>" method="post" onsubmit="return validate_table_image_form()">
	<input type="hidden" name="action" value="numcolsrows" readonly="readonly" />
	<?php echo _FIG_TXT_NUMCOLSROWS._FIG_TXT_NUMCOLS?><input type="text" name="num_cols" id="num_cols" size="3" maxlength="3" value="<?php echo $cols?>" />
	<?php echo _FIG_TXT_NUMROWS?><input type="text" name="num_rows" id="num_rows" size="3" maxlength="3" value="<?php echo $rows?>" />
	</td><td><?php if(is_writeable($galleryconf)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- configurazione dimensione thumbs -->
	<tr><td>
	<script type="text/javascript">
	function validate_size_image_form()
		{
			if(document.getElementById('hw_thumbs').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FIG_TXT_DIMTHUMBS?>');
					document.getElementById('hw_thumbs').focus();
					document.getElementById('hw_thumbs').value='';
					return false;
				}
			else return true;
		}
	</script>

	<form action="index.php?mod=<?php echo $mod?>" method="post" onsubmit="return validate_size_image_form()">
	<input type="hidden" name="action" value="dimthumbs" readonly="readonly" />
	<?php echo _FIG_TXT_DIMTHUMBS?><input type="text" name="hw_thumbs" id="hw_thumbs" size="8" maxlength="3" value="<?php echo $hw_thumbs?>" />
	</td><td><?php if(is_writeable($galleryconf)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- configurazione abilitazione uploads da utenti registrati -->
	<tr><td><form action="index.php?mod=<?php echo $mod?>" method="post">
	<input type="hidden" name="action" value="upload_by_regusers" readonly="readonly" />
	<?php echo _FIG_TXT_REGUPLOADS?>
	<select name="reguploads">
		<option value="<?php echo $reguploads?>"><?php echo $reguploads?></option><?php
		if($reguploads=="N")
			echo "<option value=\"Y\">Y</option>";
		else echo "<option value=\"N\">N</option>";
	?></select>
	</td><td><?php if(is_writeable($galleryconf) AND is_writeable($gallerycomm)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- configurazione abilitazione commenti a galleria -->
	<tr><td><form action="index.php?mod=<?php echo $mod?>" method="post">
	<input type="hidden" name="action" value="confcommenti" readonly="readonly" />
	<?php echo _FIG_TXT_COMMENTI?>
	<select name="commenti">
		<option value="<?php echo $commenti?>"><?php echo $commenti?></option><?php
		if($commenti=="N")
			echo "<option value=\"Y\">Y</option>";
		else echo "<option value=\"N\">N</option>";
	?></select>
	</td><td><?php if(is_writeable($galleryconf) AND is_writeable($gallerycomm)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- eliminazione commenti alla galleria -->
	<tr><td><form action="index.php?mod=<?php echo $mod?>" method="post">
	<input type="hidden" name="action" value="delcomm" readonly="readonly" />
	<?php echo _FIG_TXT_DELCOMM?>
	</td><td><?php if(is_writeable($gallerycfgdir)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- upload nuova immagine -->
	<tr><td>
	<script type="text/javascript">
	function validate_upload_image_form()
		{
			if(document.getElementById('file_up').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._IMAGE?>');
					document.getElementById('file_up').focus();
					document.getElementById('file_up').value='';
					return false;
				}
			else return true;
		}
	</script>

	<form enctype="multipart/form-data" action="index.php?mod=<?php echo $mod?>" method="post" onsubmit="return validate_upload_image_form()">
	<input type="hidden" name="action" value="upload" readonly="readonly" />
	<input type="hidden" name="path" value="sections/<?php echo $mod?>" readonly="readonly" />
	<?php echo _FIG_TXT_UPLOAD?><input name="file_up" id="file_up" type="file" />
	</td><td><?php if(is_writeable($path)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- rinomina immagine -->
	<tr><td>
	<script type="text/javascript">
	function validate_rename_image_form()
		{
			if(document.getElementById('rename_from').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._IMAGE?>');
					document.getElementById('rename_from').focus();
					document.getElementById('rename_from').value='';
					return false;
				}
			else if(document.getElementById('rename_to').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._NEWNAME?>');
					document.getElementById('rename_to').focus();
					document.getElementById('rename_to').value='';
					return false;
				}
			else return true;
		}
	</script>
	<form action="index.php?mod=<?php echo $mod?>" method="post" onsubmit="return validate_rename_image_form()">
	<input type="hidden" name="action" value="rename" readonly="readonly" />
	<input type="hidden" name="path" value="sections/<?php echo $mod?>" readonly="readonly" />
	<?php echo _FIG_TXT_RENAME?><select name="rename_from" id="rename_from">
		<option></option><?php
		for($i=0;$i<count($array_dir);$i++)
  			echo "<option value=\"$path/".$array_dir[$i]."\">".$array_dir[$i]."</option>";
	?></select>&nbsp;
	<input type="text" name="rename_to" id="rename_to" size="20" maxlength="50" />
	</td><td><?php if(is_writeable($path)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	<!-- eliminazione immagine caricata -->
	<tr><td>
		<script type="text/javascript">
	function validate_delete_image_form()
		{
			if(document.getElementById('file_del').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._IMAGE?>');
					document.getElementById('file_del').focus();
					document.getElementById('file_del').value='';
					return false;
				}
			else return true;
		}
	</script>
	<form action="index.php?mod=<?php echo $mod?>" method="post" onsubmit="return validate_delete_image_form()">
	<input type="hidden" name="action" value="delete" readonly="readonly" />
	<?php echo _FIG_TXT_DELETE?><select name="file_del" id="file_del">
		<option></option><?php
		for($i=0;$i<count($array_dir);$i++)
  			echo "<option value=\"$path/".$array_dir[$i]."\">".$array_dir[$i]."</option>";
	?></select>
	</td><td><?php if(is_writeable($path)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
	</form></td></tr>
	</tbody></table><?php
	CloseTableTitle();
	?></div><?php
}

// se non sono presenti immagini nella sezione, restituisce avviso e salta stampa galleria
if($n_file == 0) {
	echo "<div class=\"centeredDiv\">"._FIG_TXT_NOIMAGES."</div>";
	// upload nuova immagine se funzione e' abilitata per utenti registrati
	if($reguploads=="Y" AND ($myforum) AND versecid($myforum)) {
		OpenTable();
		?><form enctype="multipart/form-data" action="index.php?mod=<?php echo $mod?>" method="post">
		<input type="hidden" name="action" value="regupload" readonly="readonly" />
		<input type="hidden" name="path" value="sections/<?php echo $mod?>" readonly="readonly" />
		<?php echo _FIG_TXT_UPLOAD?><input name="file_up" type="file" />
		<?php if(is_writeable($path)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
		</form><?php
		CloseTable();
	}
	echo "<br>";
} else {
// INIZIO PROCEDURA STAMPA GALLERIA

$pag = getparam("pag",PAR_GET,SAN_FLAT);
if( !is_numeric($pag) OR $pag<1 OR $pag>($n_file/($rows*$cols)+1) ) {
	$pag = 1;
}
// stampa menu_top select per cambio pagina
menu_change_page("toppage", $n_file, $rows, $cols, $pag);

?><table style="text-align:center; width:100%; border-spacing: 10px; border-collapse: separate; background-color:<?php echo $bgcolor2?>"><tbody><?php
	if($pag!="")				// determina qual e' la prima foto della pagina
		$max_x_pag = ($pag * ($rows * $cols)) - ($rows * $cols - 1);
	else $max_x_pag = 1;

	for ($i=$max_x_pag; $i<$max_x_pag+($rows * $cols) and $i<=$n_file; $i++) {
		if($i%$cols == 1)				// apertura nuova riga
			echo "<tr style=\"background-gcolor:".$bgcolor3."\">";
		$nomefile = $array_dir[$i-1];
		$nomefile = preg_replace("/^[0-9]*_/i","",$nomefile); 	// elimina i numeri iniziali
		$nomefile = preg_replace("/_/"," ",$nomefile); 			// converte _ in spazi
		$nomefile = preg_replace("/[\.]*[a-z0-9]{1,4}$/i","",$nomefile); 	// elimina l'estensione
		$size = getimagesize($path."/".$array_dir[$i-1]);
		?><td style="padding: 5px; width:<?php echo intval(100/$cols)?>%; height:<?php echo intval(100/$rows)?>%; vertical-align:bottom;">	<!-- stampa casella con immagine -->
		  <div style="text-align:center">
		  <a rel="lightbox[fngallery]" href="<?php echo $path."/".$array_dir[$i-1]?>" title="<?php echo $nomefile?>"><?php
			// se dimensioni < di quelle scelte, stampo immagine, altrimenti creo thumbnail
			if($size[0]<$hw_thumbs AND $size[1]<$hw_thumbs) {
				?><img style="border:1" src="<?php echo "$path/".$array_dir[$i-1]?>" alt="thumb" />
				</a><?php
			}
			elseif($gd_flag==FALSE) {
				if($size[0]>$size[1]) {
					$new_w = $hw_thumbs;
					$new_h = $hw_thumbs * $size[1] / $size[0];
				} else {
					$new_w = $hw_thumbs * $size[0] / $size[1];
					$new_h = $hw_thumbs;
				}
				?><img style="border:1" src="<?php echo $path."/".$array_dir[$i-1]?>" height="<?php echo $new_h?>" width="<?php echo $new_w?>" alt="thumb" />
				</a><?php
			}
			else {
				?><img style="border:1" src="gallery/thumb.php?image=<?php echo $path."/".$array_dir[$i-1]?>&amp;hw=<?php echo $hw_thumbs?>" alt="thumb" />
				</a><?php
			}
		  ?></div>
		  <div style="text-align:center"><b><?php
			//echo $array_dir[$i-1];	// visualizza il nome completo del file
			echo $nomefile;			// visualizza il nome del file elaborandolo perche' compaia come descrizione
		  ?></b></div>
		  <div style="text-align:center"><?php echo round(filesize($path."/".$array_dir[$i-1]) / 1024, 1)." Kb - ".$size[0]."x".$size[1]." px"?></div>
		</td><?php
		if($i%$cols == 0)				// chiusura della riga
			echo "</tr>";
	}
	while (($i-1)%$cols != 0) {				// stampa casella vuota per terminare correttamente la riga
		echo "<td width=\"".(intval(100/$cols))."%\" height=\"".(intval(100/$rows))."%\"></td>";
		$i++;
	}
?></tbody></table><?php

// stampa menu_bottom select per cambio pagina
menu_change_page("bottompage", $n_file, $rows, $cols, $pag);

?><div style="text-align:center"><?php
	// upload nuova immagine se funzione e' abilitata per utenti registrati
	$myforum=getparam("myforum", PAR_COOKIE, SAN_FLAT);
	if($reguploads=="Y" AND ($myforum) AND versecid($myforum)) {
		OpenTable();
		?><form enctype="multipart/form-data" action="index.php?mod=<?php echo $mod?>" method="post">
		<input type="hidden" name="action" value="regupload" readonly="readonly" />
		<input type="hidden" name="path" value="sections/<?php echo $mod?>" readonly="readonly" />
		<?php echo _FIG_TXT_UPLOAD?><input name="file_up" type="file" />
		<?php if(is_writeable($path)) echo "<input type=\"SUBMIT\" value=\"OK\" />"; else echo "<font color=\"red\">"._FIG_ALERTNOTWR."</font>";?>
		</form><?php
		CloseTable();
	}
	//Stampa il bottone per il feed RSS della gallery
	?><div style="text-align:right;margin:5px 0 5px 0;">
		<a href="gallery/zipgallery.php?source_mod=<?php echo _FN_MOD?>" title="ZIP Gallery"><img style="border:0" src="gallery/folder-download.png" alt="ZIP Gallery"/></a>&nbsp;
		<a href="<?php echo $galleryfeed?>" target="_blank" title="<?php echo _BACKEND?>"><img style="border:0" src="images/rss.png" alt="<?php echo _BACKEND?>" height="32" width="32"/></a>
	</div><?php
	// menu inserimento commenti (se abilitati) + stampa commenti
	if($commenti=="Y") {
		OpenTable();
		echo "<b>"._FIG_COMMENTI."</b> | <a href=\"index.php?mod=$mod&amp;inscomm=1\" title=\""._FIG_ADDCOMM."\">"._FIG_ADDCOMM."</a>";
		CloseTable();
		$file_xml = get_file($gallerycomm);
		$comments = get_xml_element("comments",$file_xml);
		$comment = get_xml_array("comment",$comments);
		for($n=0; $n<count($comment); $n++)	{			// stampa commenti a sondaggio
			$user = get_xml_element("by",$comment[$n]);
			print "<div class='comment' style='min-height:105px;'>";
			if($user == _FIG_SCON) {
				echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
				echo "<b>"._FIG_DA."</b> "._FIG_SCON;
			} else {
				// inserisco l'avatar nei commenti
				if(file_exists(get_fn_dir("users")."/$user.php")) {
					$userdata = array();
					$userdata = load_user_profile($user);
					$img = $userdata['avatar'];
					if($img!="") {
						if(!stristr($img,"http://"))
							echo "<img src='forum/".$img."' alt='avatar' class=\"avatar\" />";
						else
							echo "<img src='".$img."' alt='avatar' class=\"avatar\" />";
					} else
						echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
				} else echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
				// fine avatar
				print "<b>"._FIG_DA."</b> $user";
			}
			echo "<br><br>".get_xml_element("what",$comment[$n]);
			print "</div>";
			echo "<br>";
		}
	}
?></div><?php

} // FINE PROCEDURA STAMPA GALLERIA


/*
 * Stampa menu per cambio pagina
 */
function menu_change_page($position, $n_file=0, $rows=0, $cols=0, $pag=0) {
	$position = getparam($position, PAR_NULL, SAN_FLAT);
	$mod      = getparam("mod", PAR_GET, SAN_FLAT);
	if($n_file > ($rows * $cols)) {
		?><div style="text-align:center" style="margin:1em 0 1em 0">
		<form name="<?php echo $position?>" action="">
		<span class="change_page" style="font-size:150%"><?php
		echo $start_left = ($pag > 1) ? ("<a href=\"index.php?mod=$mod&amp;pag=1\" title=\""._FIG_TXT_PAGINA." 1\">&#171;</a>") : ("&#171;");
		echo "&nbsp;&nbsp;";
		echo $arrow_left = ($pag > 1) ? ("<a href=\"index.php?mod=$mod&amp;pag=".($pag-1)."\" title=\""._FIG_TXT_PAGINA." ".($pag-1)."\">&#8249;</a>") : ("&#8249;");
		?>&nbsp;&nbsp;</span>
		<select name="select_<?php echo $position?>" onchange="self.location=document.<?php echo $position?>.select_<?php echo $position?>[document.<?php echo $position?>.select_<?php echo $position?>.selectedIndex].value"><?php
			for($i=0;$i<$n_file/($rows*$cols);$i++) {
				if($pag == "")
					echo "<option value=\"index.php?mod=$mod&amp;pag=".($i+1)."\">".($i+1)."</option>";
				else if($pag == $i+1)
						echo "<option value=\"index.php?mod=$mod&amp;pag=".($i+1)."\" selected='selected'>".($i+1)."</option>";
					else echo "<option value=\"index.php?mod=$mod&amp;pag=".($i+1)."\">".($i+1)."</option>";
			}
		?></select>
		<span class="change_page" style="font-size:150%">&nbsp;&nbsp;<?php
		$last_pag = (is_int($n_file/($rows*$cols))) ? ($n_file/($rows*$cols)) : (intval($n_file/($rows*$cols))+1);
		echo $arrow_right = ($pag < $last_pag) ? ("<a href=\"index.php?mod=$mod&amp;pag=".($pag+1)."\" title=\""._FIG_TXT_PAGINA." ".($pag+1)."\">&#8250;</a>") : ("&#8250;");
		echo "&nbsp;&nbsp;";
		echo $end_right = ($pag < $last_pag) ? ("<a href=\"index.php?mod=$mod&amp;pag=$last_pag\" title=\""._FIG_TXT_PAGINA." $last_pag\">&#187;</a>") : ("&#187;");
		?></span></form>
		</div><?php
	}
}

/*
 * Mette on-line il file selezionato
 */
function fig_upload($path) {
	$path=getparam($path,PAR_NULL, SAN_FLAT);
	switch($_POST['action']) {
		case("upload"):
			if(is_admin()) {
				if($_FILES['file_up']['name']<>"" AND preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($_FILES['file_up']['name']))) {
					//controllo che il file non sia già esistente
					if(file_exists("$path/".$_FILES['file_up']['name'])) {
						?><script>alert('<?php echo _FIG_ALERTEXIST?>');</script><?php
						return;
					}
					//upload del file
					if(move_uploaded_file($_FILES['file_up']['tmp_name'], "$path/".$_FILES['file_up']['name'])) {
						?><script>alert('<?php echo _FIG_ALERTUPOK?>');</script><?php
					}
					//se non riesce restituisci avviso sui permessi di scrittura
					else {
						?><script>alert('<?php echo _FIG_ALERTNOTWR?>');</script><?php
					}
				}
			}
		break;
		case("regupload"):
			$myforum=getparam("myforum", PAR_COOKIE, SAN_FLAT);
			if(($myforum!="") AND versecid($myforum)) {
				if($_FILES['file_up']['name']<>"" AND preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension($_FILES['file_up']['name']))) {
					$new_file = explode(".",basename($_FILES['file_up']['name']));
					$n = count($new_file) - 1;
					//controllo che il file non sia già esistente
					if(file_exists("$path/$new_file[0]_by_$myforum.$new_file[$n]")) {
						?><script>alert('<?php echo _FIG_ALERTEXIST?>');</script><?php
						return;
					}
					//upload del file
					if(move_uploaded_file($_FILES['file_up']['tmp_name'], "$path/$new_file[0]_by_$myforum.$new_file[$n]")) {
						?><script>alert('<?php echo _FIG_ALERTUPOK?>');</script><?php
					}
					//se non riesce restituisci avviso sui permessi di scrittura
					else {
						?><script>alert('<?php echo _FIG_ALERTNOTWR?>');</script><?php
					}
				}
			} else $myforum = "";
		break;
	}
}

/**
 * Genera il feed RSS della gallery
 *
 * Genera il file <i>/var/gallery.xml</i> che contiene le immagini presenti nella gallery; il file XML
 * e' strutturato per essere letto con un normale aggregator RSS, ed e' compatibile
 * con le {@link http://feedvalidator.org/docs/rss2.html specifiche RSS 2.0}.
 *
 * @param string $sectionName Nome della sezione contenente la Gallery di cui si vuole generare il feed RSS
 *
 * @author Michele Tartara <mikyt@users.sourceforge.net>
 */
function generate_Gallery_RSS($sectionName) {
	global $sitename, $admin_mail;
	$gallery_dir = 'sections/'.$sectionName;

	$url = "http://".getparam("HTTP_HOST", PAR_SERVER, SAN_FLAT).getparam("PHP_SELF", PAR_SERVER, SAN_FLAT);
	$url = str_replace(basename($url),"",$url);
	$gallery_url = $url."index.php?mod=".$sectionName;

	// tag apertura del feed
	$body = "<?xml version='1.0' encoding='"._CHARSET."'?>\n<rss version=\"2.0\">\n\t<channel>\n";
	// informazioni generali sul feed
	$body .= "\t\t<title>$sitename - Gallery</title>\n\t\t<link>$gallery_url</link>\n\t\t<description><![CDATA[\"$sitename\" $sectionName]]></description>\n";
	$body .= "\t\t<managingEditor>$admin_mail</managingEditor>\n\t\t<generator>FlatNuke RSS Generator - http://www.flatnuke.org</generator>\n";
	$body .= "\t\t<lastBuildDate>".date("D, d M Y H:i:s",time())." GMT</lastBuildDate>\n";
	// carico array con le news ordinate per data
	$handle = opendir($gallery_dir);
	$modlist = array();
	$count = 0;
	while ($file = readdir($handle)) {
		if (preg_match("/jpg$|jpeg$|png$|gif$/i", get_file_extension($file))) {
			$modlist[$count]['name'] = $file;
			$modlist[$count]['time'] = filemtime($gallery_dir."/".$file);
			$count++;
		}
	}
	closedir($handle);
	if(count($modlist)>0) {	// time descending order
		usort($modlist, create_function('$a, $b', "return strnatcasecmp(\$a['time'], \$b['time']);"));
		$modlist = array_reverse($modlist, FALSE);
	}
	// creazione tag per ognuna delle news
	for ($i=0; $i < count($modlist); $i++) {
		$filename = $gallery_dir."/".rawurlencode($modlist[$i]['name']);
		$link = $url.$filename;
		$mytitle = $modlist[$i]['name'];
		$mytitle = preg_replace("/^[0-9]*_/i","",$mytitle); 	// elimina i numeri iniziali
		$mytitle = preg_replace("/_/"," ",$mytitle); 			// converte _ in spazi
		$mytitle = preg_replace("/[\.]*[a-z0-9]{1,4}$/i","",$mytitle); 	// elimina l'estensione
		$body .= "\t\t<item>\n";
		$body .= "\t\t\t<title>$mytitle</title>\n";
		$body .= "\t\t\t<link>$link</link>\n";
		$body .= "\t\t\t<description><![CDATA[<img src=\"$link\" alt=\"$mytitle\"/>]]></description>\n";
		$body .= "\t\t\t<guid isPermaLink=\"true\">$link</guid>\n";
		$body .= "\t\t\t<pubDate>".date("D, d M Y H:i:s", filemtime(str_replace("%20"," ",$filename)))." GMT</pubDate>\n";
		$body .= "\t\t</item>\n";
	}
	// tag chiusura del feed
	$body.="\t</channel>\n</rss>";
	// scrittura del feed
	fnwrite(get_fn_dir("var")."/gallery/".str_replace("/","_",$sectionName).".xml", $body, "w", array("nonull"));
}

?>
