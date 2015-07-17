<?php
/**
 * Questo script deve le sue origini a FD di Detronizator - aka Ivan De Marino
 * Modifiche di Aldo Boccacci
 * e-mail: zorba_ (AT) tin.it
 * sito web: www.aldoboccacci.it
 * versione: 0.8
 *
 * (Da usare con Flatnuke >= 2.5.8)
 *
 * Il codice del metodo getIcon proviene quasi interamente dal progetto Autoindex
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

//lo script non può essere richiamato direttamente

if (preg_match("/fd+.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../index.php");
//     fd_die("You cannot call fd+.php!");
}

//CONFIGURAZIONE AVANZATA: NON MODIFICARE!
//Il file che conterrà l'elenco dei file più scaricati
$max_download_file = "fd_top_download.php";

//Il nome della cartella di archivio
$archivedir = "none_archivedir";

//L'estensione dei file di descrizione
//questa variabile non è ancora usata
$desc_extension=".description";

//il file di log
$fdlogfile = get_fn_dir("var")."/log/fdpluslog.php";

//importo la configurazione
require("download/fdconfig.php");

//ci sono prolemi con register_globals=off
$GLOBALS['archivedir'] = $archivedir;
$GLOBALS['extensions'] = $extensions;
$GLOBALS['icon_style'] = $icon_style;
$GLOBALS['newfiletime'] = $newfiletime;
$GLOBALS['automd5'] = $automd5;
$GLOBALS['autosha1'] = $autosha1;
$GLOBALS['max_download_file'] = $max_download_file;
$GLOBALS['maxFileSize']= $maxFileSize;
$GLOBALS['desc_extension'] = $desc_extension;
$GLOBALS['fdlogfile'] = $fdlogfile;
$GLOBALS['showuploader'] = $showuploader;
$GLOBALS['extsig'] = trim($extsig);
$GLOBALS['extscreenshot'] = $extscreenshot;
$GLOBALS['admins'] = trim($admins);
$GLOBALS['enable_admin_options'] = trim($enable_admin_options);
$GLOBALS['section_show_header'] = trim($section_show_header);
$GLOBALS['defaultvoteon'] = trim($defaultvoteon);
//UTENTI
//ci sono prolemi con register_globals=off
$GLOBALS['usermaxFileSize'] = $usermaxFileSize;
$GLOBALS['userwaitingfile'] = $userwaitingfile;
$GLOBALS['userfilelimit'] = $userfilelimit;
$GLOBALS['userblacklist'] = $userblacklist;
$GLOBALS['minlevel'] = $minlevel;
$GLOBALS['showdownloadlink'] = $showdownloadlink;
$GLOBALS['overview_show_files'] = $overview_show_files;

include_once("config.php");
include_once("functions.php");
include_once("shared.php");

//impone l'inclusione dei file di supporto
require_once("download/include/fdview.php");
require_once("download/include/fdfunctions.php");
require_once("download/include/fdurl.php");
include_once("download/include/fduser.php");
if (fd_is_admin()) require_once("download/include/fdadmin.php");

//inclusione moduli esterni
fd_load_php_code("download/include/phpfunctions/");

//A seconda dei parametri passati si intraprenderà l'azione opportuna
//PARAMETRI GET
if (isset($_GET['fdaction'])){

	$_get_fdaction = "";
	$_get_fdaction = trim(getparam("fdaction",PAR_GET,SAN_FLAT));
	$file="";
	$file = getparam("fdfile",PAR_GET,SAN_NULL);
	$file = trim($file);

	if (!fd_check_path($file,"sections/","false") and $file!="")
		fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if ($_get_fdaction=="confirmDelete"){
		if ($file=="") return;
		confermaElimina($file);
	}

	else if ($_get_fdaction=="delete"){
		if ($file=="") return;
		elimina($file);
	}

	else if ($_get_fdaction=="confirmDeleteSign"){
		if ($file=="") return;
		delete_sign_interface($file);
	}

	else if ($_get_fdaction=="deletesign"){
		if ($file=="") return;
		delete_sign($file);
	}

	else if ($_get_fdaction=="confirmDeleteScreenshot"){
		if ($file=="") return;
		delete_screenshot_interface($file);
	}

	else if ($_get_fdaction=="deletescreenshot"){
		if ($file=="") return;
		delete_screenshot($file);
	}

	else if ($_get_fdaction=="hide"){
		if ($file=="") return;
		$value = getparam("value",PAR_GET,SAN_FLAT);
		if (!preg_match("/^false$|^true$/i",$value)) fd_die("<b>Value</b> must be true or false! FD+: ".__LINE__);
		hide_file($file,$value);
	}

	else if ($_get_fdaction=="archive"){
		if ($file=="") return;
		archivia($file,$archivedir);
	}

	else if ($_get_fdaction=="renameinterface"){
		if ($file=="") return;
		rename_interface($file);
	}

	else if ($_get_fdaction=="modify"){
		if ($file=="") return;
		edit_description_interface($file,load_description($file),"edit");
	}

	else if ($_get_fdaction=="ripristina"){
		if ($file=="") return;
		ripristina($file,$archivedir);
	}

	else if ($_get_fdaction=="download"){
		$url = getparam("url",PAR_GET,SAN_NULL);
		$url = trim($url);
		if ($url=="") return;
		if (!fd_check_path($url,"sections/","false"))
			fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
		track($url);
	}
	else if ($_get_fdaction=="moveinterface"){
		if ($file=="") return;
		move_file_interface($file);
	}
	else if ($_get_fdaction=="movefileconfirm"){
		if ($file=="") return;
		$newdir = "";
		$newdir = getparam("newdir",PAR_GET,SAN_NULL);
		$newdir = trim($newdir);
		if ($newdir=="")return;
		if (!fd_check_path($newdir,"./sections/","false"))
			fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
		move_file_confirm($file,preg_replace("/^\.\//","",$newdir));
	}
	else fdlogf("\$_GET['fdaction'] value is invalid: \"".strip_tags($_get_fdaction)."\". FD+: ".__LINE__,"ERROR");
}

//PARAMETRI POST
if (isset($_POST['fdaction'])){
	$_post_fdaction = "";
	$_post_fdaction = getparam("fdaction",PAR_POST,SAN_FLAT);
	if ($_post_fdaction=="createsectinterface"){
		mksection_interface();
	}
	else if ($_post_fdaction=="addfile"){
		edit_description_interface("",array(),"upload");
	}
	else if ($_post_fdaction=="addurl"){
		edit_description_interface("",array(),"addurl");
	}
	else if ($_post_fdaction=="createsect"){
		if (isset($_POST['fdmod'])) $fdmod = $_POST['fdmod'];
		else return;
		if (isset($_POST['fdnewsect'])) $fdnewsect = $_POST['fdnewsect'];
		else return;
		if (isset($_POST['fdsecthidden'])) $fdsecthidden = $_POST['fdsecthidden'];
		else $fdsecthidden="false";
		if (isset($_POST['fdsectallowuserupload']))
			$fdsectallowuserupload = $_POST['fdsectallowuserupload'];
		else $fdsectallowuserupload="false";
		mksection($fdmod,$fdnewsect,$fdsecthidden,$fdsectallowuserupload);
	}
	else if ($_post_fdaction=="renamefile"){
		if (isset($_POST['oldfilename'])) $oldfinename = $_POST['oldfilename'];
		else return;
		if (isset($_POST['newfilename'])) $newfilename = $_POST['newfilename'];
		else return;
		if (isset($_POST['fdpath'])) $fdpath = $_POST['fdpath'];
		else return;
		rename_file($oldfinename,$newfilename,$fdpath);
	}
	else if ($_post_fdaction=="movefile"){
		if (isset($_POST['fdfile'])) $fdfile = $_POST['fdfile'];
		else return;
		if (isset($_POST['newdir'])) $newdir = preg_replace("/^\.\//","",$_POST['newdir']);
		else return;
		fd_move_file($fdfile,$newdir);
	}
	else if ($_post_fdaction=="upload"){
		upload();
	}
	else if ($_post_fdaction=="uploadurl"){
		uploadurl();
	}
	else if ($_post_fdaction=="save"){
		save_changes();
	}
	else if ($_post_fdaction=="saveurl"){
		saveurl();
	}

	//User upload
	else if ($_post_fdaction=="useraddfile"){
		edit_description_interface("",array(),"userupload");
	}
	else if ($_post_fdaction=="userupload"){
		user_upload();
	}
	else if ($_post_fdaction=="publishuserfileinterface"){
		publish_interface();
	}
	else if ($_post_fdaction=="publishuserfile"){
		publish_file($_POST['fdfile']);
	}
	else if ($_post_fdaction=="deleteuserfile"){
		confermaElimina($_POST['fdfile']);
	}
	else if ($_post_fdaction=="allowuserupload"){
		$fdmod = getparam("fdmod",PAR_POST,SAN_FLAT);
		allow_user_upload($fdmod);
	}
	else if ($_post_fdaction=="removeuserupload"){
		$fdmod = getparam("fdmod",PAR_POST,SAN_FLAT);
		remove_user_upload($fdmod);
	}
	else fdlogf("\$_POST['fdaction'] value is invalid: \"".strip_tags($_post_fdaction)."\" FD+: ".__LINE__,"ERROR");
}

//le funzioni che prima erano presenti in questo file sono ora in fdfunctions.php

?>