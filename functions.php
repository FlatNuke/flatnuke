<?php

/**
 * File delle funzioni di sistema
 *
 * Questo file contiene le procedure di sistema necessarie al funzionamento di FlatNuke.
 *
 * @package Funzioni_di_sistema
 * Prova {@tutorial Funzioni_di_sistema/docapi.pkg}
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */


/**
 * Contiene funzioni condivise tra il forum e il core di FlatNuke
 */
include_once("shared.php");
include_once("flatnews/include/news_functions.php");

// automatically load PHP code in files contained in include/phpfunctions
load_php_code("include/phpfunctions");


/**
 * Funzione che inizializza le costanti legate a Flatnuke.
 *
 * @author Aldo Boccacci
 * @since 3.0
 */
function create_fn_constants(){
	// flatnuke main engine
	define("_FN_MOD",      get_mod());
	define("_FN_FILE",     get_file_var());
	// flatnuke directories
	define("_FN_VAR_DIR",      get_fn_dir("var"));
	define("_FN_USERS_DIR",    get_fn_dir("users"));
	define("_FN_BLOCKS_DIR",   get_fn_dir("blocks"));
	define("_FN_THEME_DIR",    get_fn_dir("theme"));
	define("_FN_SECTIONS_DIR", get_fn_dir("sections"));
	// user profile informations
	define("_FN_USERNAME", get_username());//this has to be after every fnlog call
	$userprofile = load_user_profile(_FN_USERNAME);
	define("_FN_USERPASSWORD", $userprofile['password']);
	define("_FN_USERAVATAR",   $userprofile['avatar']);
	// user profile grants
	define("_FN_IS_ADMIN", is_admin());
	define("_FN_IS_USER",  is_user());
	if (_FN_IS_ADMIN or _FN_IS_USER){
		define("_FN_IS_GUEST",  FALSE);
		define("_FN_USERLEVEL", $userprofile['level']);
	}
	else {
		define("_FN_IS_GUEST",  TRUE);
		define("_FN_USERLEVEL", "-1");
	}
	// sections management
	define("_FN_SECT_LEVEL",            getsectlevel(_FN_MOD));
	define("_FN_USER_CAN_VIEW_SECTION", user_can_view_section(_FN_MOD));
	// news management
	define("_FN_IS_NEWS_MODERATOR", is_news_moderator(_FN_USERNAME));
}


/**
 * Visualizza una sezione
 *
 * Visualizza il contenuto della sezione passata come parametro.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $section Nome assoluto della sezione da visualizzare
 */
function view_section($section){

	if (file_exists("include/redefine/view_section.php")){
		include("include/redefine/view_section.php");
		return;
	}

	global $theme;

	// security checks
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$file = get_file_var();
	$mode = getparam("mode", PAR_GET, SAN_FLAT);

	// prevents to go up/down to other directories
	if(stristr($section,"..")) die(_NONPUOI);
	if(stristr($file,"..")) die(_NONPUOI);

	// check if section really exists
	if(!file_exists("sections/$section")) {
		OpenTable();
		print("<div class=\"centeredDiv\"><b>"._SECTNOTEXIST."</b></div><br><br>");
		if (file_exists("themes/$theme/fn_404.php")) {
			include(stripslashes("themes/$theme/fn_404.php"));
		} else {
			echo "<div style=\"text-align:center;\"><img src=\"images/fn_404.png\" alt=\""._SECTNOTEXIST."\" title=\""._SECTNOTEXIST."\"/></div>";
		}
		CloseTable();
		return;
	}

	// CHECKING SECTION PERMISSIONS -->
	if (!user_can_view_section($section,_FN_USERNAME)){
		OpenTable();
		print("<div class=\"centeredDiv\"><b>"._NOLEVELSECT."</b></div><br><br>");
		if (file_exists("themes/$theme/fn_denied.php")) {
			include(stripslashes("themes/$theme/fn_denied.php"));
		} else {
			echo "<div style=\"text-align:center;\"><img src=\"images/fn_denied.png\" alt=\""._NOLEVELSECT."\" title=\""._NOLEVELSECT."\"/></div>";
		}
		CloseTable();
		return;
	}
	// <-- END CHECKING SECTION PERMISSIONS

	if($file!="")
		$sect = $section."/$file";
	else
		$sect = $section;

	// Build navigation bar of the section
	$tit = "";
	$albero = explode("/",$sect);
	for($i=0;$i<sizeof($albero);$i++){
		$mypath = "";
		for($j=0;$j<=$i;$j++){
			if($i>0 and ($j!=$i)){
				$mypath .= $albero[$j]."/";
			} else {
				$mypath .= $albero[$j];
			}
		}
		$tmp = str_replace("none_","",$albero[$i]);
		/* deprecated: PHP 5.3 upgrade
		$tmp = eregi_replace("^[0-9]*_","",$tmp);*/
		$tmp = preg_replace("/^[0-9]*_/","",$tmp);
		if($i!=(sizeof($albero)-1)){
			$accesskey="";
			if (get_access_key($mypath)!=""){
				$accesskey = "accesskey=\"".get_access_key($mypath)."\"";
			}
			$tit .= "<a href='index.php?mod=".rawurlencodepath($mypath)."' title=\""._GOTOSECTION.": ".str_replace("_"," ",$tmp)."\" $accesskey>".str_replace("_"," ",$tmp)."</a> / ";
		}
		else {
			$tit .= str_replace("_"," ",$tmp);
			/* deprecated: PHP 5.3 upgrade
			$tit = ereg_replace("\.[a-z0-9]{1,4}$","",$tit); // delete file extension*/
			// $tit = preg_replace("/\.[a-z0-9]{1,4}$/","",$tit); // delete file extension - useless for directories
		}
	}

	// Find the image that identifies the current section; if not find, it takes the default one by the theme
	if(file_exists("sections/$section/section.png")) {
		$section_image = "sections/$section/section.png";
	} else $section_image = "themes/$theme/images/section.png";

	// Start printing section
	if(defined('_THEME_VER')) {
		if(_THEME_VER > 0 and _FN_MOD!="") {
			OpenTableTitle("<a title=\"\" accesskey=\"0\"></a>".stripslashes($tit));
		}
	} else {
		if (_FN_MOD!="")
		OpenTableTitle("<a title=\"\" accesskey=\"0\"><img src='$section_image' alt='Section' /></a>&nbsp;".stripslashes($tit));
	}

	//creo i link per i siti sociali (se non siamo in una sezione di notizie)
	if (!file_exists(_FN_SECTIONS_DIR."/"._FN_MOD."/news")){
	echo "<div class='social-links' style='float:right;margin-left:10px;margin-bottom:10px;/*border:1px;border-left-style: solid; border-bottom-style: solid;border-color: #d5d6d7;*/'>";
// 	echo "<div class='social-links'>";
	create_social_links($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI'],_FN_TITLE);
	echo "</div>";
	}

	// Include code for section's header
	load_php_code("include/section/header");

	if($file==""){
		if(file_exists("sections/$section/section.php")){
			include("sections/$section/section.php");
			echo "<br>";
		}
		/* Main download section with Fdplus */
		if(file_exists("sections/$section/download") and is_file("sections/$section/download")) {
			echo "<br>";
			include_once("download/fd+.php");
			fd_overview();
		}
		/* Single download section with Fdplus */
		if(file_exists("sections/$section/downloadsection") and is_file("sections/$section/downloadsection")) {
			echo "<br>";
			include_once("download/fd+.php");
			fd_view_section();
		}
		/* Forum section with Flatforum */
		if(file_exists("sections/$section/forum") and is_file("sections/$section/forum")) {
			echo "<br>";
			include_once("forum/flatforum.php");
		}
		/* Gallery section */
		if(file_exists("sections/$section/gallery") and is_file("sections/$section/gallery")) {
			echo "<br>";
			include("gallery/gallery.php");
		}
		/* News section */
		if(file_exists("sections/$section/news") and is_file("sections/$section/news")) {
			echo "<br>";
			include("flatnews/flatnews.php");
		}
	} else {
		include(stripslashes('sections/'.$section.'/'.$file));
	}

	// Include code for section's footer
	load_php_code("include/section/footer");

	// link to print current section
	$options = "?";
	foreach ($_GET as $key => $value) {
		$value   .= getparam($value, PAR_GET, SAN_FLAT);
		$options .= "$key=$value&amp;";
	}
	$options = getparam($options,PAR_NULL, SAN_HTML);
	echo "<div style=\"text-align:right\"><a href='print.php".$options."' target='new' title=\""._STAMPA."\">"._ICONPRINT."</a></div>";

	// Administration of the section (visible only to administrtaors)
	echo "<hr>";
	if(_FN_IS_ADMIN AND is_writeable("sections/$section") AND !fn_is_system_dir($section)) {
		section_admin_panel();
	}
	if(user_can_edit_section($section,_FN_USERNAME) AND is_writeable("sections/$section") AND !fn_is_system_dir($section)) {
		section_user_edit_panel();
	}
	if (_FN_MOD!="")
		CloseTableTitle();
}


/**
 * Elenca le sezioni ed i link ad esse associati
 *
 * Restituisce un array contenente le sezioni ed un secondo
 * array contenente i link preformattati alle sezioni stesse.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @since 2.5.8
 *
 * @param string $path Directory delle sezioni da scorrere
 * @param string $result Specifica se restituire i nomi delle sezioni ('names') oppure i loro link ('links')
 * @return array Lista dei nomi o lista dei link delle sezioni trovate
 */
function list_sections($path, $result){
	$path   = getparam($path,   PAR_NULL, SAN_FLAT);
	$result = getparam($result, PAR_NULL, SAN_FLAT);
	$modlist   = array();
	$link_list = array();

	$handle = opendir($path);
	while ($file = readdir($handle)) {
		/* deprecated: PHP 5.3 upgrade
		if (!( $file=="." or $file==".." ) and (!ereg("^\.",$file) and ($file!="CVS") and !stristr($file,"none_") )) {
			if (!user_can_view_section(eregi_replace("^sections/","","$path/$file"))) continue;*/
		if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file) and ($file!="CVS") and !stristr($file,"none_") )) {
			if (!user_can_view_section(preg_replace("/^sections\//i","","$path/$file"))) continue;
			array_push($modlist, $file);
		}
	}
	closedir($handle);

	if(count($modlist)<=0){
		return(null);
	}

	sort($modlist);

	if ($result == "links"){
		$j=0;
		for ($item_num=0; $item_num < count($modlist); $item_num++) {
			if(!stristr($modlist[$item_num],"none_")){
				if(stristr($modlist[$item_num],"_")){
					/* deprecated: PHP 5.3 upgrade
					$tmp=eregi_replace("^[0-9]*_","",$modlist[$item_num]);*/
					$tmp=preg_replace("/^[0-9]*_/","",$modlist[$item_num]);
					$tmp=str_replace("_"," ",$tmp);
				}
				else
					$tmp=$modlist[$item_num];
			$link_list[$j] = "<a href=\"index.php?mod=".rawurlencodepath($modlist[$item_num])."\" title=\" "._GOTOSECTION.": $tmp\"";
			$access_key = get_access_key($modlist[$item_num]);
			if ($access_key!=""){
				$link_list[$j] = $link_list[$j]." accesskey=\"$access_key\"";
			}
			$link_list[$j] = $link_list[$j].">$tmp</a>";
			$j++;
			}
		}
	}

	switch($result) {
		case "names":
			return($modlist);
		break;
		case "links":
			return($link_list);
		break;
	}
}


/**
 * Visualizza i blocchi laterali
 *
 * Visualizza il contenuto dei blocchi laterali presenti nelle directories
 * <i>/blocks/dx/</i> oppure <i>/blocks/sx/</i> a seconda del parametro passato;
 * non vengono stampati i blocchi che iniziano per "none_".
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $edge Lato dei blocchi da stampare
 */
function create_blocks($edge){
	global $theme;

	if (file_exists("include/redefine/create_blocks.php")){
		include("include/redefine/create_blocks.php");
		return;
	}

	$edge=getparam($edge,PAR_NULL,SAN_FLAT);
	$modlist = array();

	$handle=opendir('blocks/'.$edge);
	while ($file = readdir($handle)) {
		/* deprecated: PHP 5.3 upgrade
		if (!is_dir("blocks/$edge/$file") and (!ereg("^\.",$file)) and !stristr($file,"none_")) {*/
		if (!is_dir("blocks/$edge/$file") and (!preg_match("/^\./",$file)) and !stristr($file,"none_")) {
			array_push($modlist, $file);
		}
	}
	closedir($handle);

	if(count($modlist)>0)
		sort($modlist);
	for ($item_num=0; $item_num < count($modlist); $item_num++) {
		/* deprecated: PHP 5.3 upgrade
		$tmp=eregi_replace("^[0-9]*_","",$modlist[$item_num]);*/
		$tmp=preg_replace("/^[0-9]*_/","",$modlist[$item_num]);
		$title=str_replace("_"," ",str_replace(".php","",$tmp));
		/* deprecated: PHP 5.3 upgrade
		if (_FN_IS_ADMIN and !eregi("login\.php",$modlist[$item_num])){
			if (eregi("\<\?",get_file("blocks/$edge/".$modlist[$item_num])))*/
		if (_FN_IS_ADMIN and !preg_match("/login\.php/",$modlist[$item_num])){
			if (preg_match("/\<\?/",get_file("blocks/$edge/".$modlist[$item_num])))
				$php_free = FALSE;
			else $php_free = TRUE;
			global $news_editor;
			$title .= "&nbsp;<a href=\"index.php?mod=modcont&amp;from=index.php&amp;file=blocks/$edge/".$modlist[$item_num];
			if ($news_editor=="fckeditor" AND file_exists("include/plugins/editors/FCKeditor/fckeditor.php") and $php_free==TRUE)
				$title .= "&amp;fneditor=fckeditor";
			else if ($news_editor=="ckeditor" AND file_exists("include/plugins/editors/ckeditor/ckeditor.php") and $php_free==TRUE)
				$title .= "&amp;fneditor=ckeditor";
			$title .= " \" title=\""._MODIFICA."\">"._ICONMODIFY."</a>";
		}
		// backward theme compatibility
		if(function_exists("OpenBlock")){
			OpenBlock("themes/$theme/images/block.png",$title);
		}
		else {
			OpenTableTitle("<img src=\"themes/$theme/images/menu.png\" alt=\"$title\" />&nbsp;$title");
		}
		include ("blocks/$edge/$modlist[$item_num]");
		if(function_exists("CloseBlock"))
			CloseBlock();
		else
			CloseTableTitle();
	}
}


/**
 * Visualizza una textarea per la modifica di un file
 *
 * Permette di modificare direttamente online il file passato come parametro,
 * tramite una comoda textarea che ne visualizza il contenuto.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $filename Nome del file da modificare
 * @param string $editor l'editor da usare (attualmente solo "fckeditor")
 */
function edit_content($filename,$editor="html"){
	if (file_exists("include/redefine/edit_content.php")){
		include("include/redefine/edit_content.php");
		return;
	}

	$filename=rawurldecode(getparam($filename,PAR_NULL,SAN_FLAT));
	$from=getparam("from",PAR_GET,SAN_FLAT);
	$from=stripslashes($from);
	$editor = getparam($editor,PAR_NULL,SAN_FLAT);
	$editor= trim($editor);

	if(stristr($filename,"..") or !file_exists($filename)) {
		OpenTable();
		print("<div class=\"centeredDiv\"><b>"._NORESULT."</b></div>");
		CloseTable();
		return;
	}

	OpenTableTitle(_MODIFICA." ".$filename);
	echo "<form action=\"verify.php\" method=\"post\">
	<input type=\"hidden\" name=\"mod\" value=\"modcont\" />
	<input type=\"hidden\" name=\"from\" value=\"".$from."\" />";

	//Testo da modificare
	$text = get_unprotected_text(file_get_contents($filename));

// 	echo "EDITOR: $editor";
	if ($editor=="fckeditor"){
		fn_textarea("fckeditor",$text,array("BasePath"=>"include/plugins/editors/FCKeditor/", "Width"=>"100%","Height"=>"800","name"=>"body","id"=>"body","cols"=>"80","rows"=>"30","style"=>"width: 95%"));

	}
	else if ($editor=="ckeditor"){
		fn_textarea("ckeditor",$text,array("BasePath"=>"include/plugins/editors/ckeditor/", "Width"=>"100%","Height"=>"800","name"=>"body","id"=>"body","cols"=>"80","rows"=>"30","style"=>"width: 95%"));

	}
	else {
	echo "<textarea cols=\"80\" rows=\"30\" name=\"body\" style=\"width: 95%\">";
	echo htmlspecialchars($text);
	echo "</textarea>";
	}

	echo "<br><br>
	<input type=\"hidden\" name=\"file\" value=\"".$filename."\" />
	<input type=\"submit\" value=\""._MODIFICA."\" />
	</form>";
	CloseTableTitle();
}

/**
 * Visualizza una textarea per la modifica di un file da parte di un utente
 *
 * Permette di modificare direttamente online il file passato come parametro,
 * tramite una comoda textarea che ne visualizza il contenuto.
 *
 * @author Aldo Boccacci
 *
 * @param string $filename Nome del file da modificare
 * @param string $editor l'editor da usare (attualmente solo "fckeditor")
 */
function user_edit_content($filename,$editor="html"){

	$filename=getparam($filename,PAR_NULL,SAN_FLAT);
	$from=getparam("from",PAR_GET,SAN_FLAT);
	$from=stripslashes($from);
	$editor = getparam($editor,PAR_NULL,SAN_FLAT);
	$editor= trim($editor);
	$username = get_username();

	if (!check_path($filename,"sections/","true")) fn_die("USERMODCONT","Error: The path ".strip_tags($filename)." is not valid!",__FILE__,__LINE__);

	$mod = _FN_MOD;
	if (trim($mod)!="usermodcont") fn_die("USERMODCONT","Error: \$mod must be \"usermodcont\"",__FILE__,__LINE__);

	/* deprecated: PHP 5.3 upgrade
	$sectmod = eregi_replace("sections\/","",dirname($filename));*/
	$sectmod = preg_replace("/sections\//","",dirname($filename));

	if (!user_can_view_section($sectmod)) fn_die("USERMODCONT","Error: the user ".strip_tags($username)." cannot view the section ".strip_tags($sectmod)."",__FILE__,__LINE__);

	if (!user_can_edit_section($sectmod)) fn_die("USERMODCONT","Error: the user ".strip_tags($username)." cannot edit the section ".strip_tags($sectmod)."",__FILE__,__LINE__);

	//Testo da modificare
	$text = get_unprotected_text(file_get_contents($filename));
	/* deprecated: PHP 5.3 upgrade
	if (eregi("\<\?|\?\>",$text)) fn_die("USERMODCONT","Error, user".strip_tags($username)." cannot edit the section ".strip_tags($sectmod)." because contains php code",__FILE__,__LINE__);*/
	if (preg_match("/\<\?|\?\/>",$text)) fn_die("USERMODCONT","Error, user".strip_tags($username)." cannot edit the section ".strip_tags($sectmod)." because contains php code",__FILE__,__LINE__);

	if(stristr($filename,"..") or !file_exists($filename)) {
		OpenTable();
		print("<div class=\"centeredDiv\"><b>"._NORESULT."</b></div>");
		CloseTable();
		return;
	}

	OpenTableTitle(_MODIFICA." ".$filename);
	echo "<form action=\"verify.php\" method=\"post\">
	<input type=\"hidden\" name=\"mod\" value=\"usermodcont\" />
	<input type=\"hidden\" name=\"from\" value=\"".$from."\" />";

	//Testo da modificare
	$text = get_unprotected_text(file_get_contents($filename));

// 	echo "EDITOR: $editor";
	if ($editor=="fckeditor"){
		if (file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
			include("include/plugins/editors/FCKeditor/fckeditor.php");
			$oFCKeditor = new FCKeditor('body') ;
			$oFCKeditor->BasePath	= "include/plugins/editors/FCKeditor/" ;
			$oFCKeditor->Value= $text;
			$oFCKeditor->Width="100%";
			$oFCKeditor->Height="800";
			$oFCKeditor->ToolbarSet= "Default";
			$oFCKeditor->Create() ;
		}
		else {
		echo "<textarea cols=\"80\" rows=\"30\" name=\"body\" style=\"width: 95%\">";
		echo htmlspecialchars($text);
		echo "</textarea>";
		}
	}
	else {
	echo "<textarea cols=\"80\" rows=\"30\" name=\"body\" style=\"width: 95%\">";
	echo htmlspecialchars($text);
	echo "</textarea>";
	}

	echo "<br><br>
	<input type=\"hidden\" name=\"file\" value=\"".$filename."\" />
	<input type=\"submit\" value=\""._MODIFICA."\" />
	</form>";
	CloseTableTitle();
}

/**
 * Effettua il parsing di un feed RSS esterno
 *
 * Legge il file di un feed RSS esterno al sito e ne genera una stampa
 * leggibile e ordinata con le notizie segnalate, completa di link
 * per raggiungerle sul sito di origine.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $url Indirizzo del feed RSS
 * @return null|string Nulla se non trova il file, oppure il contenuto del feed RSS
 */
function parse_RSS($url){

	$url=getparam($url,PAR_NULL,SAN_FLAT);

	if ($url != "") {
		/* deprecated: PHP 5.3 upgrade
		if (!ereg("http://",$url)) {*/
		if (!preg_match("/http:\/\//",$url)) {
			$url = "http://$url";
		}
		$rdf = parse_url($url);
		$fp = fsockopen($rdf['host'], 80, $errno, $errstr, 15);
		if (!$fp) {
			print(_NORSS);
			return;
		}
		if ($fp) {
			@fputs($fp, "GET " . $rdf['path'] . "?" . $rdf['query'] . " HTTP/1.0\r\n");
			@fputs($fp, "HOST: " . $rdf['host'] . "\r\n\r\n");
			$string = "";
			while(!feof($fp)) {
				$pagetext = fgets($fp,228);
				$string .= chop($pagetext);
			}
			fputs($fp,"Connection: close\r\n\r\n");
			fclose($fp);
			$items = explode("</item>",$string);

			$content = "";
			$cont = 0;
			for ($i=0;$i<sizeof($items)-1;$i++) {
				/* deprecated: PHP 5.3 upgrade
				$link = ereg_replace(".*<link>","",$items[$i]);
				$link = ereg_replace("</link>.*","",$link);
				$title2 = ereg_replace(".*<title>","",$items[$i]);
				$title2 = ereg_replace("</title>.*","",$title2);*/
				$link = preg_replace("/.*<link>/","",$items[$i]);
				$link = preg_replace("/<\/link>.*/","",$link);
				$title2 = preg_replace("/.*<title>/","",$items[$i]);
				$title2 = preg_replace("/<\/title>.*/","",$title2);
				// some feeds are exposed with CDATA format
				$title2 = str_replace("<![CDATA[","",$title2);
				$title2 = str_replace("]]>","",trim($title2));
				if ($items[$i] == "" AND $cont != 1) {
					$content = "";
				} else {
					if (strcmp($link,$title2) AND $items[$i] != "") {
						$cont = 1;
						$content .= "<b><big>&middot;</big></b>&nbsp;<a href=\"$link\" target=\"new\">$title2</a><br>\n";
					}
				}
			}
		}
	}
	return $content;
}


/**
 * Restituisce il livello di una sezione
 *
 * Restituisce il livello della sezione passata come parametro, leggendolo dal
 * file <i>/section/nomesezione/level.php</i>:
 *  - -1 se non esiste il file level.php, la sezione sarà visibile da tutti;
 *  - 0 per una sezione visibile solo da utenti registrati;
 *  - da 1 a 9 per una sezione visibile solo da utenti di livello intermedio;
 *  - 10 per una sezione visibile solo da utenti amministratore.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 *
 * @param string $section Nome della sezione
 * @return int -1 se la sezione non ha livello, oppure un numero di livello da 0 a 10
 */
function getsectlevel($section){
	$section=getparam($section,PAR_NULL,SAN_FLAT);
	if(!file_exists("sections/$section/level.php"))
		return(-1);
	$fp=file("sections/$section/level.php");
	return((int)str_replace("\n","",$fp[0]));
}


/**
 * Genera un log di attività
 *
 * Aggiunge al file <i>/var/log/log.php</i> una riga che contiene
 * la data e l'ora di produzione della segnalazione di log, la zona
 * di origine e una descrizione dell'attività effettuata.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @since 2.5.4
 *
 * @param string $zone Nome della sezione cui si riferisce il log
 * @param string $txt Testo del log da salvare
 */
function fnlog($zone, $txt){
	$zone=getparam($zone,PAR_NULL,SAN_FLAT);
	$txt=getparam($txt,PAR_NULL,SAN_FLAT);

	if (!defined("_FN_VAR_DIR"))
		create_fn_constants();

	if($zone == "Forum") {
		$pref = "../";
		$flog = $pref._FN_VAR_DIR."/log";
	}
	else {
		$pref = "";
		$flog = _FN_VAR_DIR."/log";
	}
	// prepare environment if not present
	if(!is_dir($flog))
		fn_mkdir($flog, 0777);
	// log rotate every 0.5 MB
	if(file_exists("$flog/log.php") AND (filesize("$flog/log.php") >= 524288)) {
		rename("$flog/log.php",$pref._FN_VAR_DIR."/log/log-".time().".php");
		$fp=fopen("$flog/log.php","a");
		fwrite($fp, "<?php exit(1);?>\n");
		fclose($fp);
	}
	// check file existance and write an empty file
	if(!file_exists("$flog/log.php")) {
		$fp=fopen("$flog/log.php","a");
		fwrite($fp, "<?php exit(1);?>\n");
		fclose($fp);
	}
	// write log file
	if (file_exists("$flog/log.php")){
		$fp=fopen("$flog/log.php","a");
		$string =  date ("d/m/Y H:i:s");
		$string = $string." $zone: $txt\n";
		fwrite($fp,$string);
		fclose($fp);
	}
}


/**
 * Mostra le informazioni sul copyright di un modulo
 *
 * Crea un link da cui è possibile visualizzare una finestra popup
 * con informazioni dettagliate sul copyright del modulo corrente.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @since 2.5.4
 *
 * @param string $modulo Nome del modulo installato
 * @param string $versione Versione del modulo installato
 * @param string $autore Nome dell'autore del modulo
 * @param string $email E-mail dell'autore del modulo
 * @param string $homepage Homepage dell'autore del modulo
 * @param string $licenza Licenza di rilascio del modulo
 */
function module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza) {
	$modulo   = getparam($modulo,   PAR_NULL, SAN_FLAT);
	$versione = getparam($versione, PAR_NULL, SAN_FLAT);
	$autore   = getparam($autore,   PAR_NULL, SAN_FLAT);
	$email    = getparam($email,    PAR_NULL, SAN_FLAT);
	$email    = email_mask($email);
	$homepage = getparam($homepage, PAR_NULL, SAN_FLAT);
	$licenza  = getparam($licenza,  PAR_NULL, SAN_FLAT);

	// preview link
	echo "<br><div style=\"text-align:right\"><a href='javascript:;' onclick='copyrightshow();' title='Copyright info'>Copyright &copy; <b>$modulo</b></a></div><br>";
	// preview box
	echo "<div id='fncopyright'>";
	echo "<p class=\"centeredDiv\"><b>Copyright informations</b></p>Module developed for the <b><a href='http://flatnuke.sf.net'>Flatnuke</a></b> CMS<br><br>";
	echo "<b>Module name</b>: $modulo<br><b>Version</b>: $versione<br><b>License</b>: $licenza<br>";
	echo "<b>Author</b>: $autore<br><b>E-mail</b>: <a href='mailto:$email'>".str_replace("%20"," ",$email)."</a><br><b>Home page</b>: <a href='$homepage' target='_blank' title='$homepage'>$homepage</a> <br>";
	echo "<p class=\"centeredDiv\"><b><a href='javascript:;' onclick='copyrightshow();'>Close</a></b></p></div>";
}


/**
 * Gestione tag per codice html
 *
 * Rimpiazza gli pseudo-tag ([b], [i], ecc.) con i tag html
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20050916: Unificato la funzione per l'uso nel sito e nel forum
 * @since 2.5.7
 *
 * @param string $string Stringa da verificare
 * @param string $where Riferimento alla root per l'esecuzione del codice: 'home' per le news, 'forum' per il forum
 * @return string Codice HTML
 */
function tag2html($string) {
	// verifico provenienza della chiamata e adatto i richiami alle directories
	$string=getparam($string,PAR_NULL,SAN_NULL);
// 	$where=getparam($where,PAR_NULL,SAN_FLAT);

	$prepath="forum/";
	// solo l'amministratore può usare codice HTML
	//da Flatnuke 3.0 la funzione tag2html viene usata in fase di visualizzazione
	//e non in fase di salvataggio. Dunque il controllo sul livello dell'utente
	//e sul codice da salvare avviene al di fuori di questa funzione
	$myforum=getparam("myforum", PAR_COOKIE, SAN_FLAT);
// 	if(getlevel($myforum,$where) < 10) {
// 		$string = str_replace("<", "&lt;", $string);
// 		$string = str_replace(">", "&gt;", $string);
// 	}
	// formatting fixes
	$string = str_replace("&lt;br/&gt;", "<br>", $string);
	$string = str_replace("&lt;br /&gt;", "<br>", $string);
	$string = str_replace("&lt;br&gt;", "<br>", $string);
	$string = str_replace("<br>", "<br>", $string);
	$string = str_replace("&#91;", "[", $string);
	$string = str_replace("&#93;", "]", $string);
	$string = str_replace("|", "", $string);
	// emoticons
	$string = str_replace("[:)]", "<img src='".$prepath."emoticon/01.png' alt=':)' />", $string);
	$string = str_replace("[:(]", "<img src='".$prepath."emoticon/02.png' alt=':(' />", $string);
	$string = str_replace("[:o]", "<img src='".$prepath."emoticon/03.png' alt=':o' />", $string);
	$string = str_replace("[:p]", "<img src='".$prepath."emoticon/04.png' alt=':p' />", $string);
	$string = str_replace("[:D]", "<img src='".$prepath."emoticon/05.png' alt=':D' />", $string);
	$string = str_replace("[:!]", "<img src='".$prepath."emoticon/06.png' alt=':!' />", $string);
	$string = str_replace("[:O]", "<img src='".$prepath."emoticon/07.png' alt=':O' />", $string);
	$string = str_replace("[8)]", "<img src='".$prepath."emoticon/08.png' alt='8)' />", $string);
	$string = str_replace("[;)]", "<img src='".$prepath."emoticon/09.png' alt=';)' />", $string);
	$string = str_replace("[rolleyes]", "<img src='".$prepath."emoticon/rolleyes.png' alt=':rolleyes:' />", $string);
	$string = str_replace("[neutral]", "<img src='".$prepath."emoticon/neutral.png' alt=':|' />", $string);
	$string = str_replace("[:x]", "<img src='".$prepath."emoticon/mad.png' alt=':x' />", $string);
	$string = str_replace("[O:)]", "<img src='".$prepath."emoticon/angel.png' alt='O:)' />", $string);
	$string = str_replace("[whistle]", "<img src='".$prepath."emoticon/whistle.png' alt='whistle' />", $string);
	$string = str_replace("[eh]", "<img src='".$prepath."emoticon/eh.png' alt='eh' />", $string);
	$string = str_replace("[evil]", "<img src='".$prepath."emoticon/evil.png' alt=':evil:' />", $string);
	$string = str_replace("[idea]", "<img src='".$prepath."emoticon/idea.png' alt=':idea:' />", $string);
	$string = str_replace("[bier]", "<img src='".$prepath."emoticon/bier.png' alt=':bier:' />", $string);
	$string = str_replace("[flower]", "<img src='".$prepath."emoticon/flower.png' alt=':flower:' />", $string);
	$string = str_replace("[sboing]", "<img src='".$prepath."emoticon/sboing.png' alt=':sboing:' />", $string);

	// formattazione testo
	$string = str_replace("\n", "<br>", $string);
	$string = str_replace("\r", "", $string);
	$string = str_replace("[b]", "<b>", $string);
	$string = str_replace("[u]", "<u>", $string);
	$string = str_replace("[/u]", "</u>", $string);
	$string = str_replace("[/b]", "</b>", $string);
	$string = str_replace("[i]", "<i>", $string);
	$string = str_replace("[/i]", "</i>", $string);
	$string = str_replace("[strike]","<span style=\"text-decoration : line-through;\">",$string);
	$string = str_replace("[/strike]","</span>",$string);
	$string = preg_replace("/\[quote\=(.+?)\]/s",'<blockquote><b><a href="index.php?mod=none_Login&amp;action=viewprofile&amp;user=$1" title="'._VIEW_USERPROFILE.'">$1</a> '._HASCRITTO.':</b><br>',$string);
	$string = str_replace("[quote]", "<blockquote>", $string);
	$string = str_replace("[/quote]", "</blockquote>", $string);
	$string = str_replace("[code]", "<pre>", $string);
	$string = str_replace("[/code]", "</pre>", $string);
	$string = preg_replace("/\[url\=(.+?)\](.+?)\[\/url\]/s",'<a title="$2" href="$1" target="blank_">$2</a>',$string);

	if (_FN_IS_GUEST){
		/* deprecated: PHP 5.3 upgrade
		$string = eregi_replace("\[mail\].*\[\/mail\]","<span style=\"text-decoration : line-through;\" title=\"only users can view mail addresses\">[e-mail]</span>",$string);*/
		$string = preg_replace("/\[mail\].*\[\/mail\]/i","<span style=\"text-decoration : line-through;\" title=\"only users can view mail addresses\">[e-mail]</span>",$string);
	}
	else {
		$string = preg_replace("/\[mail\](.+?)\[\/mail\]/s",'<a title="mail to $1" href="mailto:$1">$1</a>',$string);
	}

	// immagini
	if(preg_match("/\[img\](.*?)\[\/img\]/s", $string, $img_match)>0) {
		if(preg_match("/(\.php|\.js)/i",$img_match[1])) {
			$string = preg_replace("/\[img\](.*?)\[\/img\]/s","",$string);
		} else {
			if(@getimagesize($img_match[1])!=FALSE) {
				$string = str_replace("[img]", "<br /><img src=\"", $string);
				$string = str_replace("[/img]", "\" alt=\"uploaded_image\" /><br />", $string);
			} else $string = preg_replace("/\[img\](.*?)\[\/img\]/s","",$string);
		}
	}

	//posizione
	$string = str_replace("[left]", "<div style=\"text-align : left;\">", $string);
	$string = str_replace("[right]", "<div style=\"text-align : right;\">", $string);
	$string = str_replace("[center]", "<div style=\"text-align : center;\">", $string);
	$string = str_replace("[justify]", "<div style=\"text-align : justify;\">", $string);
	$string = str_replace("[/left]", "</div>", $string);
	$string = str_replace("[/right]", "</div>", $string);
	$string = str_replace("[/center]", "</div>", $string);
	$string = str_replace("[/justify]", "</div>", $string);

	// colori del testo
	$string = str_replace("[red]", "<span style=\"color : #ff0000\">", $string);
	$string = str_replace("[green]", "<span style=\"color : #00ff00\">", $string);
	$string = str_replace("[blue]", "<span style=\"color : #0000ff\">", $string);
	$string = str_replace("[pink]", "<span style=\"color : #ff00ff\">", $string);
	$string = str_replace("[yellow]", "<span style=\"color : #ffff00\">", $string);
	$string = str_replace("[cyan]", "<span style=\"color : #00ffff\">", $string);
	$string = str_replace("[/red]", "</span>", $string);
	$string = str_replace("[/blue]", "</span>", $string);
	$string = str_replace("[/green]", "</span>", $string);
	$string = str_replace("[/pink]", "</span>", $string);
	$string = str_replace("[/yellow]", "</span>", $string);
	$string = str_replace("[/cyan]", "</span>", $string);

	//dimensione
	$string = str_replace("[size=50%]", "<span style=\"font-size: 50%;\">", $string);
	$string = str_replace("[size=75%]", "<span style=\"font-size: 75%;\">", $string);
	$string = str_replace("[size=100%]", "<span style=\"font-size: 100%;\">", $string);
	$string = str_replace("[size=150%]", "<span style=\"font-size: 150%;\">", $string);
	$string = str_replace("[size=200%]", "<span style=\"font-size: 200%;\">", $string);
	$string = str_replace("[/size]", "</span>", $string);

	//elenchi
	$string = str_replace("[ol]<br>", "<ol>", $string);
	$string = str_replace("[ol]", "<ol>", $string);
	$string = str_replace("[/ol]", "</ol>", $string);
	$string = str_replace("[*]", "<li>", $string);
	//per risolvere il problema dell'"a capo"
	/* deprecated: PHP 5.3 upgrade
	$string = eregi_replace("\[\/\*\]\<br>", "</li>", $string);
	$string = eregi_replace("\[\/\*\]\n", "</li>", $string);*/
	$string = preg_replace("/\[\/\*\]\<br \/>/i", "</li>", $string);
	$string = preg_replace("/\[\/\*\]\n/i", "</li>", $string);
	$string = str_replace("[/*]", "</li>", $string);
	$string = str_replace("[ul]<br>", "<ul>", $string);
	$string = str_replace("[ul]", "<ul>", $string);
	$string = str_replace("[/ul]", "</ul>", $string);

	// WIKIPEDIA
	$items = explode("[/wp]",$string);
	for ($i = 0; $i < count($items); $i++) {
		$wp="";
		if(stristr($items[$i],"[wp")){
			/* deprecated: PHP 5.3 upgrade
			$wp_lang = ereg_replace(".*\[wp lang=","",$items[$i]);
			$wp_lang = ereg_replace("\].*","",$wp_lang);
			$wp = ereg_replace(".*\[wp.*\]", "", $items[$i]);
			$wp = ereg_replace("\[/wp\].*", "", $wp);*/
			$wp_lang = preg_replace("/.*\[wp lang=/","",$items[$i]);
			$wp_lang = preg_replace("/\].*/","",$wp_lang);
			$wp = preg_replace("/.*\[wp.*\]/", "", $items[$i]);
			$wp = preg_replace("/\[\/wp\].*/", "", $wp);
			if ($wp != "") {
				$nuovowp="<a style=\"text-decoration: none; border-bottom: 1px dashed; color: blue;\" target=\"new\" href=\"http://$wp_lang.wikipedia.org/wiki/$wp\">$wp</a>";
			$string=str_replace("[wp lang=$wp_lang]".$wp."[/wp]", $nuovowp, $string);
			}
		}
	}

	$items = "";
	// URLs
	$items = explode("[/url]",$string);
	for ($i = 0; $i < count($items); $i++) {
		$url="";
		if(stristr($items[$i],"[url]")){
			/* deprecated: PHP 5.3 upgrade
			$url = ereg_replace(".*\[url\]", "", $items[$i]);
			$url = ereg_replace("\[/url\].*", "", $url);*/
			$url = preg_replace("/.*\[url\]/", "", $items[$i]);
			$url = preg_replace("/\[\/url\].*/", "", $url);
			if ($url != "") {
				if (stristr($url, "http://") == FALSE) {
					$nuovourl="<a target=\"new\" href=\"http://$url\">$url</a>";
				} else {
					$nuovourl="<a target=\"new\" href=\"$url\">$url</a>";
				}
			$string=str_replace("[url]".$url."[/url]", $nuovourl, $string);
			}
		}
	}

	$items = "";
	// youtube
	$items = explode("[/youtube]",$string);
	for ($i = 0; $i < count($items); $i++) {
		$url="";
		if(stristr($items[$i],"[youtube]")){
			/* deprecated: PHP 5.3 upgrade
			$url = ereg_replace(".*\[youtube\]", "", $items[$i]);
			$url = ereg_replace("\[/youtube\].*", "", $url);*/
			$url = preg_replace("/.*\[youtube\]/", "", $items[$i]);
			$url = preg_replace("/\[\/youtube\].*/", "", $url);
			if ($url != "") {
				if (stristr($url, "youtube.com") == FALSE) {
					continue;
				} else {
					$link = preg_replace("/.+?youtube.com.+v=([a-zA-Z0-9]*).*/s",'<iframe class="youtube-player" width="430" height="259" src="http://www.youtube.com/embed/$1" frame></iframe>',$url);
				}
				$string=str_replace("[youtube]".$url."[/youtube]", $link, $string);
			}
		}
	}

	return ($string);
}


/**
 * Crea il pannello dei pulsanti BBCODES
 *
 * Crea il pannello dei pulsanti BBCODES per inserire il testo corretto
 * nell'area di testo indicata come parametro.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net> | 20050906: added parameter 'area' in JS function
 * @author Bjorn Splinter <insites@gmail.com> | 20050912: added variable 'prepath', changed some '' to "" to prevent IE script errors
 * @since 2.5.7
 *
 * @param string $area Area di testo in cui inserire i BBCODES
 * @param string $where Riferimento alla root per l'esecuzione del codice: 'home' per le news, 'forum' per il forum
 * @param string $what 'formatting' per creare il pannello con i tasti di formattazione testo, 'emoticons' per le emoticons
 */
function bbcodes_panel($area, $where, $what) {
	$area    = getparam($area,PAR_NULL,SAN_FLAT);
	$where   = getparam($where,PAR_NULL,SAN_FLAT);
	$what    = getparam($what,PAR_NULL,SAN_FLAT);
	$mod    = getparam("mod",PAR_GET,SAN_FLAT);
	if (!check_path($mod,"","false")) $mod="";
	global $lang;
	// verifico provenienza della chiamata e adatto i richiami alle directories
	switch($where) {
		case "home":
			$prepath = "forum/";
		break;
		case "forum":
			$prepath = "";
		break;
	}
	// stampo la parte di pannello selezionata
	switch($what) {
		case "emoticons":
		?><a href="javascript:void(0)" onclick="javascript:insertTags('[:)]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":)"><img src="<?php echo $prepath?>emoticon/01.png"  alt="Happy" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:(]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":("><img src="<?php echo $prepath?>emoticon/02.png"  alt="Triste"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:o]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":o"><img src="<?php echo $prepath?>emoticon/03.png"  alt="sorpresa" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:p]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":p"><img src="<?php echo $prepath?>emoticon/04.png"  alt="linguaccia"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:D]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":D"><img src="<?php echo $prepath?>emoticon/05.png"  alt="risata" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:!]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":!"><img src="<?php echo $prepath?>emoticon/06.png"  alt="indifferente"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:O]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=":O"><img src="<?php echo $prepath?>emoticon/07.png"  alt="sbalordito"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[8)]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="8)"><img src="<?php echo $prepath?>emoticon/08.png"  alt="fighetto"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[;)]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title=";)"><img src="<?php echo $prepath?>emoticon/09.png"  alt="occhiolino" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[rolleyes]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="rolleyes"><img src="<?php echo $prepath?>emoticon/rolleyes.png"  alt="rolleyes" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[eh]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="eh"><img src="<?php echo $prepath?>emoticon/eh.png"  alt="eh" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[neutral]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="neutral"><img src="<?php echo $prepath?>emoticon/neutral.png"  alt="neutral"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[:x]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="mad"><img src="<?php echo $prepath?>emoticon/mad.png"  alt="mad"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[O:)]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="angel"><img src="<?php echo $prepath?>emoticon/angel.png"  alt="angel"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[evil]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="evil"><img src="<?php echo $prepath?>emoticon/evil.png"  alt="evil" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[idea]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="idea"><img src="<?php echo $prepath?>emoticon/idea.png"  alt="idea"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[bier]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="bier"><img src="<?php echo $prepath?>emoticon/bier.png"  alt="bier"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[whistle]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="whistle"><img src="<?php echo $prepath?>emoticon/whistle.png"  alt="whistle" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[flower]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="flower"><img src="<?php echo $prepath?>emoticon/flower.png"  alt="flower"  /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[sboing]', '', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="sboing"><img src="<?php echo $prepath?>emoticon/sboing.png"  alt="sboing" /></a><?php
		break;

		case "formatting":
		?>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[b]', '[/b]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="bold"><img src="<?php echo $prepath?>emoticon/bold.png"  alt="bold" title="bold" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[i]', '[/i]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="italic"><img src="<?php echo $prepath?>emoticon/italic.png"  alt="italic" title="italic" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[u]', '[/u]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="underline"><img src="<?php echo $prepath?>emoticon/underline.png"  alt="underscore" title="underscore" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[strike]', '[/strike]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="strike"><img src="<?php echo $prepath?>emoticon/strike.png"  alt="strike" title="strike" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[left]', '[/left]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="left"><img src="<?php echo $prepath?>emoticon/left.png"  alt="left" title="left" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[center]', '[/center]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="center"><img src="<?php echo $prepath?>emoticon/center.png"  alt="center" title="center" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[right]', '[/right]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="right"><img src="<?php echo $prepath?>emoticon/right.png"  alt="right" title="right" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[justify]', '[/justify]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="justify"><img src="<?php echo $prepath?>emoticon/justify.png"  alt="justify" title="justify" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[quote]', '[/quote]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="quote"><img src="<?php echo $prepath?>emoticon/quote.png"  alt="quote" title="quote" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[code]', '[/code]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="quote"><img src="<?php echo $prepath?>emoticon/code.png"  alt="code" title="code" /></a>
		<?php if (_FN_IS_ADMIN){ ?>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[img]', '[/img]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="image"><img src="<?php echo $prepath?>emoticon/image.png"  alt="image" title="image" /></a>
		<?php }//fine if immagini
		if (!_FN_IS_GUEST){
		?>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[mail]', '[/mail]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="mail"><img src="<?php echo $prepath?>emoticon/mail.png"  alt="mail" title="mail" /></a>
		<?php }//fine if mail ?>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[url]', '[/url]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="url"><img src="<?php echo $prepath?>emoticon/url.png"  alt="url" title="url" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[wp lang=<?php echo $lang?>]', '[/wp]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="wikipedia page"><img src="<?php echo $prepath?>emoticon/wikipedia.png"  alt="wikipedia" title="wikipedia" /></a>
		<br>
		<select name="<?php echo $area?>fontsize" onchange="javascript:insertTags('[size=' + this.form.<?php echo $area?>fontsize.options[this.form.<?php echo $area?>fontsize.selectedIndex].value + ']', '[/size]', '<?php echo $area?>'); this.form.<?php echo $area?>fontsize.selectedIndex=0;" >
			<option value="" selected="selected" disabled="disabled"><?php echo _SIZE?></option>
			<option value="50%" >50%</option>
			<option value="75%" >75%</option>
			<option value="100%" >100%</option>
			<option value="150%" >150%</option>
			<option value="200%" >200%</option>
		</select>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[red]', '[/red]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="red"><img src="<?php echo $prepath?>emoticon/red.png"  alt="red" title="red" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[green]', '[/green]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="green"><img src="<?php echo $prepath?>emoticon/green.png"  alt="green" title="green" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[blue]', '[/blue]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="blue"><img src="<?php echo $prepath?>emoticon/blue.png"  alt="blue" title="blue" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[pink]', '[/pink]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="pink"><img src="<?php echo $prepath?>emoticon/pink.png"  alt="pink" title="pink" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[yellow]', '[/yellow]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()"  title="yellow"><img src="<?php echo $prepath?>emoticon/yellow.png"  alt="yellow" title="yellow" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[cyan]', '[/cyan]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="cyan"><img src="<?php echo $prepath?>emoticon/cyan.png"  alt="cyan" title="cyan" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[ol]\n[*][/*]\n[*][/*]\n[*][/*]\n', '[/ol]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="olist"><img src="<?php echo $prepath?>emoticon/olist.png"  alt="olist" title="olist" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[ul]\n[*][/*]\n[*][/*]\n[*][/*]\n', '[/ul]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="ulist"><img src="forum/emoticon/ulist.png"  alt="ulist" title="ulist" /></a>
		<a href="javascript:void(0)" onclick="javascript:insertTags('[youtube]', '[/youtube]', '<?php echo $area?>')" onmouseover="document.getElementsByName('<?php echo $area?>')[0].focus()" title="url"><img src="<?php echo $prepath?>emoticon/youtube.png"  alt="youtube" title="youtube" /></a>
		<?php
		echo "<a href=\"#\" onclick=\"Helpwindow=window.open('forum/help.php','Help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=700,height=600,left=200,top=100')\"><img src=\"forum/emoticon/help.png\" alt=\"help\" title=\"help\" /></a>";
		break;

		case "images":
		if (!_FN_IS_ADMIN) return;
		if (!is_dir("sections/none_Images/")) return;
		$file = "";
		$images = array();
		$dir="";
		$dir = opendir("sections/none_Images/");
		while (($file = readdir($dir)) !== false) {
			/* deprecated: PHP 5.3 upgrade
			if (eregi("\.png$|\.gif$|\.jpeg$|\.jpg$|\.bmp$",trim($file)))*/
			if (preg_match("/\.png$|\.gif$|\.jpeg$|\.jpg$|\.bmp$/i",trim($file)))
			array_push($images, $file);
		}

		if (count($images)>0){
			?><select name="<?php echo $area?>images" onchange="javascript:insertTags('[img]' + this.form.<?php echo $area?>images.options[this.form.<?php echo $area?>images.selectedIndex].value, '[/img]', '<?php echo $area?>');this.form.<?php echo $area?>images.selectedIndex=0" ><?php
			$image="";
			echo "<option value=\"\" selected=\"selected\" disabled=\"disabled\">"._ADDIMAGE."</option>";
			foreach ($images as $image){
				echo "<option value=\"sections/none_Images/$image\">".basename($image)."</option>";
			}
			?></select><?php
		}
		echo "&nbsp;<a href=\"index.php?mod=none_Images\" title=\""._IMAGESUPLOADTITLE."\">"._IMAGESUPLOAD."</a>";
		break;
	}
}

/**
 * Funzione per convertire codice html nell'equivalente (per quanto possibile)
 * scritto in bbcode per Flatnuke
 *
 * @param string $text il testo da trattare
 * @param boolean $strip_tags se impostato a TRUE esegue la funzione strip_tags()
 *                DOPO aver convertito il convertibile in bbcode
 * @return il codice convertito in bbcode
 * @author Aldo Boccacci
 */
function html2tag($text,$strip_tags=FALSE){
	$text = getparam($text,PAR_NULL,SAN_NULL); //removed SAN_FLAT, otherwise it strips all \n

	// html tags
	$text = preg_replace("/<br>/i","\n",$text);
	$text = preg_replace("/<br \/>/i","\n",$text);
	$text = preg_replace("/<div style='margin-left:1.5em;'><pre>/i","[code]",$text);
	$text = preg_replace("/<\/pre><\/div>/i","[/code]",$text);
	$text = preg_replace("/<pre>/i","[code]",$text);
	$text = preg_replace("/<\/pre>/i","[/code]",$text);
	$text = preg_replace("/<blockquote>/i","[quote]",$text);
	$text = preg_replace("/<\/blockquote>/i","[/quote]",$text);
	$text = preg_replace("/<div style='margin-left:1.5em;'><hr><i>/i","[quote]",$text);
	$text = preg_replace("/<hr><\/div>/i","[/quote]",$text);
	$text = preg_replace("/<b>/i","[b]",$text);
	$text = preg_replace("/<\/b>/i","[/b]",$text);
	$text = preg_replace("/<strong>/i","[b]",$text);
	$text = preg_replace("/<\/strong>/i","[/b]",$text);
	$text = preg_replace("/<i>/i","[i]",$text);
	$text = preg_replace("/<\/i>/i","[/i]",$text);
	$text = preg_replace("/<em>/i","[i]",$text);
	$text = preg_replace("/<\/em>/i","[/i]",$text);
	$text = preg_replace("/<u>/i","[u]",$text);
	$text = preg_replace("/<\/u>/i","[/u]",$text);
	$text = preg_replace("/\<span style\=\"text\-decoration : line-through;\"\>(.+?)\<\/span\>/s","[strike]$1[/strike]",$text);

	//colors
	$text= preg_replace("/<span style=\"color.*:.*#ff0000;\">(.+?)<\/span>/i","[red]$1[/red]",$text);
	$text= preg_replace("/<span style=\"color.*:.*#0000ff;\">(.+?)<\/span>/i","[blue]$1[/blue]",$text);
	$text= preg_replace("/<span style=\"color.*:.*#00ff00;\">(.+?)<\/span>/i","[green]$1[/green]",$text);
	$text= preg_replace("/<span style=\"color.*:.*#ff00ff;\">(.+?)<\/span>/i","[pink]$1[/pink]",$text);
	$text= preg_replace("/<span style=\"color.*:.*#ffff00;\">(.+?)<\/span>/i","[yellow]$1[/yellow]",$text);
	$text= preg_replace("/<span style=\"color.*:.*#00ffff;\">(.+?)<\/span>/i","[cyan]$1[/cyan]",$text);

	//emoticons
	$text = preg_replace("/<img src='forum\/emoticon\/01.png' alt=':\)' \/>/i","[:)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/02.png' alt=':\(' \/>/i","[:(]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/03.png' alt=':o' \/>/i","[:o]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/04.png' alt=':p' \/>/i","[:p]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/05.png' alt=':D' \/>/i","[:D]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/06.png' alt=':!' \/>/i","[:!]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/07.png' alt=':O' \/>/i","[:O]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/08.png' alt='8\)' \/>/i","[8)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/09.png' alt=';\)' \/>/i","[;)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/rolleyes.png' alt=':rolleyes:' \/>/i","[rolleyes]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/neutral.png' alt=':\|' \/>/i","[neutral]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/mad.png' alt=':x' \/>/i","[:x]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/angel.png' alt='O:\)' \/>/i","[O:)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/whistle.png' alt='whistle' \/>/i","[whistle]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/eh.png' alt='eh' \/>/i","[eh]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/evil.png' alt=':evil:' \/>/i","[evil]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/idea.png' alt=':idea:' \/>/i","[idea]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/bier.png' alt=':bier:' \/>/i","[bier]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/flower.png' alt=':flower:' \/>/i","[flower]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/sboing.png' alt=':sboing:' \/>/i","[sboing]",$text);
	//emoticons without the final slash
	$text = preg_replace("/<img src='forum\/emoticon\/01.png' alt=':\)'>/i","[:)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/02.png' alt=':\('>/i","[:(]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/03.png' alt=':o'>/i","[:o]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/04.png' alt=':p'>/i","[:p]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/05.png' alt=':D'>/i","[:D]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/06.png' alt=':!'>/i","[:!]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/07.png' alt=':O'>/i","[:O]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/08.png' alt='8\)'>/i","[8)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/09.png' alt=';\)'>/i","[;)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/rolleyes.png' alt=':rolleyes:'>/i","[rolleyes]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/neutral.png' alt=':\|'>/i","[neutral]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/mad.png' alt=':x'>/i","[:x]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/angel.png' alt='O:\)'>/i","[O:)]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/whistle.png' alt='whistle'>/i","[whistle]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/eh.png' alt='eh'>/i","[eh]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/evil.png' alt=':evil:'>/i","[evil]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/idea.png' alt=':idea:'>/i","[idea]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/bier.png' alt=':bier:'>/i","[bier]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/flower.png' alt=':flower:'>/i","[flower]",$text);
	$text = preg_replace("/<img src='forum\/emoticon\/sboing.png' alt=':sboing:'>/i","[sboing]",$text);

	//positions
	$text = preg_replace("/<div style=\"text-align: left;\">(.+?)<\/div>/s", "[left]$1[/left]", $text);
	$text = preg_replace("/<div style=\"text-align: right;\">(.+?)<\/div>/s", "[right]$1[/right]", $text);
	$text = preg_replace("/<div style=\"text-align: center;\">(.+?)<\/div>/s", "[center]$1[/center]", $text);
	$text = preg_replace("/<div style=\"text-align: justify;\">(.+?)<\/div>/s", "[justify]$1[/justify]", $text);
	$text = preg_replace("/<p style=\"text-align: left;\">(.+?)<\/p>/s", "[left]$1[/left]\n", $text);
	$text = preg_replace("/<p style=\"text-align: right;\">(.+?)<\/p>/s", "[right]$1[/right]\n", $text);
	$text = preg_replace("/<p style=\"text-align: center;\">(.+?)<\/p>/s", "[center]$1[/center]\n", $text);
	$text = preg_replace("/<p style=\"text-align: justify;\">(.+?)<\/p>/s", "[justify]$1[/justify]\n", $text);

	//text size
	$text = preg_replace("/<span style=\"font-size: 50%;\">(.+?)<\/span>/s", "[size=50%]$1[/size]", $text);
	$text = preg_replace("/<span style=\"font-size: 75%;\">(.+?)<\/span>/s", "[size=75%]$1[/size]", $text);
	$text = preg_replace("/<span style=\"font-size: 100%;\">(.+?)<\/span>/s", "[size=100%]$1[/size]", $text);
	$text = preg_replace("/<span style=\"font-size: 150%;\">(.+?)<\/span>/s", "[size=150%]$1[/size]", $text);
	$text = preg_replace("/<span style=\"font-size: 200%;\">(.+?)<\/span>/s", "[size=200%]$1[/size]", $text);

	//lists
	$text = str_replace("<ol>", "\n[ol]\n", $text);
	$text = str_replace("</ol>", "[/ol]\n", $text);
	$text = str_replace("<ul>", "\n[ul]\n", $text);
	$text = str_replace("</ul>", "[/ul]\n", $text);
	$text = str_replace("<li>", "[*]", $text);
	$text = str_replace("</li>", "[/*]\n", $text);

	//images
	//$text = preg_replace("/<img src='/","[img]",$text);
	//$text = preg_replace("/alt='..'>/","[/img]",$text);

	//wikipedia
	$text = preg_replace("/\<a style\=\"text\-decoration\: none\; border\-bottom\: 1px dashed\; color\: blue\;\" target\=\"new\" href\=\"http\:\/\/(.+?)\.wikipedia\.org\/wiki\/(.+?)\"\>(.+?)\<\/a\>/","[wp lang=$1]$3[/wp]",$text);
	$text = preg_replace("/target=\"new\" |target=\"blank_\"/","",$text);
	$text = preg_replace("/title=\".*\" /","",$text);
	$text = preg_replace("/title=\".*\"/","",$text);

	//mail
	$text = preg_replace("/<a href\=\"mailto\:(.+?)\">(.+?)<\/a>/","[mail]$1[/mail]",$text);
	$text = preg_replace("/<a href\=\"mailto\:(.+?)\" >(.+?)<\/a>/","[mail]$1[/mail]",$text);

	//URLs
	$text = preg_replace("/<a style\=\"text\-decoration\: none\; border\-bottom\: 1px dashed\; color\: blue;\" /","<a ",$text);
	$text = preg_replace("/<a href\=\"(.+?)\">(.+?)<\/a>/","[url=$1]$2[/url]",$text);
	$text = preg_replace("/<a href\=\"(.+?)\" >(.+?)<\/a>/","[url=$1]$2[/url]",$text);


	if ($strip_tags==TRUE)
		$text = strip_tags($text);

	return $text;
}

/**
 * Verifica se l'utente è amministratore
 *
 * Funzione che verifica se l'utente che sta visualizzando la pagina è
 * in possesso o meno delle credenziali di amministratore del sito.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @return boolean VERO o FALSO
 */
function is_admin(){
	$myforum = getparam("myforum", PAR_COOKIE, SAN_FLAT);
	if ($myforum == "") {
		return FALSE;
	}
	if ((getlevel($myforum, "home"))=="10" AND versecid($myforum)) {
		return TRUE;
	}
	else return FALSE;
}


/**
 * Restituisce TRUE se il visitatore è un utente regolarmente loggato
 *
 * Funzione che restituisce TRUE se il visitatore collegato al portale è un utente regolarmente
 * registrato. Restituisce FALSE in caso contrario
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @return TRUE se l'utente collegato è un utente normale, FALSE se è un ospite o un amministratore
 */
function is_user(){
	$myforum = getparam("myforum", PAR_COOKIE, SAN_FLAT);
	if ($myforum == "") {
		return FALSE;
	}
	$level = getlevel($myforum, "home");
	if ($level>"-1" AND $level<"10" AND versecid($myforum)) {
		return TRUE;
	}
	else return FALSE;
}


/**
 * Restituisce TRUE se l'utente collegato è un ospite non loggato
 *
 * Questa funzione restituisce TRUE se il visitatore collegato al sito è un semplice ospite.
 * Restituisce FALSE se si tratta di un utente o un amministratore regolarmente loggati.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @return TRUE se l'utente collegato è un ospite, FALSE se è un utente normale o un amministratore
 */
function is_guest(){
	if (is_admin() or is_user())
		return FALSE;
	else return TRUE;
}


/**
 * Restituisce lo username dell'utente collegato (se valido)
 *
 * Funzione che restituisce lo username dell'utente collegato al portale. (se valido e regolarmente loggato)
 * Se l'utente collegato è in realtà un ospite o se non è regolarmente loggato restituisce
 * una stringa vuota.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @return lo username dell'utente collegato
 */
function get_username(){
	if (is_guest())
		return "";
	$myforum = getparam("myforum", PAR_COOKIE, SAN_FLAT);

	if (is_alphanumeric($myforum))
		return $myforum;
	else return "";
}


/**
 * Restistuisce TRUE se l'utente collegato può vedere la sezione specificata da $mod
 *
 * Questa funzione restituisce TRUE se l'utente collegato al portale ha i permessi per visualizzare la sezione
 * richiesta.
 * Indicando anche il secondo parametro si possono verificare i permessi di un determinato utente.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $mod  il mod della sezione di cui verificare i permessi di visione
 * @param string $user l'utente di cui verificare i permessi. Se non viene specificato questo parametro,
 *                     o se la stringa è vuota si verifica l'utente attualmente collegato al portale
 * @return TRUE se l'utente può vedere la sezione, FALSE in caso contrario
 */
function user_can_view_section($mod,$user=""){
	$mod = getparam($mod,PAR_NULL,SAN_NULL);
	if (!check_path($mod,"","false")) return FALSE;
	if ($user==""){
		$user = _FN_USERNAME;
	}
	else $user = getparam($user,PAR_NULL,SAN_FLAT);

	if (!check_username($user) and trim($user)!="") return FALSE;

	if (!is_dir("sections/$mod")) return FALSE;

	$sectlevel = getsectlevel($mod);
	$userlevel = _FN_USERLEVEL;

	if ($userlevel=="10") return TRUE;
	//sarà settata a true se viene rispettato il livello
	$level_ok = "";
	//sarà settata true se viene rispettato il nome utente
	$user_ok ="";


	//se non esistono n il file view.php né il file level.php
	//e la funzione load_user_view_permissions() restituisce NULL
	//allora non esistono restrizioni alla visione e l'utente può visualizzare
	//la sezione indicata da $mod
	if (!file_exists("sections/$mod/view.php") and !file_exists("sections/$mod/level.php")) return true;

	//se il primo if restituisce false, ovvero se esistono restrizioni, allora devo
	//controllare i permessi a livello utente.

	//VERIFICARE BENE!
	//controllo il livello
	if (!file_exists("sections/$mod/level.php"))  $level_ok = true;
	else if (file_exists("sections/$mod/level.php") and $userlevel >= $sectlevel and versecid(get_username())) $level_ok = true;
	else $level_ok = false;

	//controllo il permesso a livello utente
	if (!file_exists("sections/$mod/view.php"))  $user_ok = true;
	else if (in_array($user,load_user_view_permissions($mod))) $user_ok = true;
	else $user_ok = false;

	//tiro le somme...
	if ($level_ok == true and $user_ok == true) return true;
	else return false;

}


/**
 * Restistuisce TRUE se l'utente collegato può vedere la sezione specificata da $mod
 *
 * Questa funzione verifica se l'utente collegato ha i permessi di scrittura nella sezione
 * passata come parametro.
 * Indicando anche il secondo parametro si possono verificare i permessi di un determinato utente.
 * La funzione non controlla i permessi di scrittura sui file a livello di filesystem: questi andranno
 * controllati a parte.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $mod  il mod della sezione di cui verificare i permessi di scrittura
 * @param string $user l'utente di cui verificare i permessi. Se non viene specificato questo parametro,
 *                     o se la stringa è vuota si verifica l'utente attualmente collegato al portale
 * @return TRUE se l'utente può modificare la sezione, FALSE in caso contrario
 * NOTA: attualmente restituisce TRUE soltanto se l'utente interessato è un amministratore
 */
function user_can_edit_section($mod,$user=""){
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	if (!check_path($mod,"","false")) return FALSE;
	if ($user==""){
		$user = get_username();
	}
// 	else $user = get_username();
	if (!check_username($user)) return FALSE;

	if (!is_dir("sections/$mod")) return FALSE;

	if (_FN_IS_ADMIN) return TRUE;

	if (!file_exists("sections/$mod/edit.php"))  return FALSE;
	else if (in_array($user,load_user_edit_permissions($mod))) return TRUE;
	else return FALSE;


	return FALSE;
}


/**
 * Restituisce l'accesskey della sezione specificata
 *
 * Funzione che restituisce l'accesskey della sezione specificata come parametro.
 * L'accesskey deve essere un singolo carattere alfanumerico e deve essere specificato nel file
 * accesskey.php eventualmente presente nella sezione specificata.
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $mod il mod della sezione di cui restituire l'accesskey
 * @return l'accesskey della sezione specificata
 */
function get_access_key($mod){
	$mod = getparam($mod,PAR_NULL,SAN_NULL);
	if (!check_path($mod,"","false")) return "";

	if (!is_dir("sections/$mod")) return "";
	if (!file_exists("sections/$mod/accesskey.php")) return "";

	$accesskey="";
	$accesskey = trim(get_file("sections/$mod/accesskey.php"));

	if (strlen($accesskey)!=1) return "";

	if (!is_alphanumeric($accesskey)) return "";

	return $accesskey;
}


/**
 * carica il profilo dell'utente specificato
 *
 * Carica il profilo dell'utente specificato e restituisce un array con i dati.
 * La struttura dell'array restituito è:
 *   $userprofile['password'] = la password codificata in md5 dell'utente
 *   $userprofile['name']     = il nome scelto dal'utente
 *   $userprofile['mail']     = l'indirizzo e-mail specificato
 *   $userprofile['homepage'] = l'home page
 *   $userprofile['work']     = il lavoro
 *   $userprofile['from']     = la provenienza geografica
 *   $userprofile['avatar']   = l'avatar scelto dall'utente
 *   $userprofile['hiddenmail'] = se impostato a "1" (default)
 *                                nasconde la mail agli altri utenti (non agli amministratori)
 *   $userprofile['regdate']  = la data di registrazione sul portale
 *   $userprofile['regcode']  = il codice di registrazione generato dal portale per la registrazione via mail
 *   $userprofile['regmail']  = la mail utilizzata per registrarsi sul portale
 *   $userprofile['lastedit'] = la data di ultima modifica del profilo
 *   $userprofile['lasteditby'] = l'utente che ha fatto l'ultima modifica al profilo
 *   $userprofile['sign']     = la firma che sarà accodata ai messaggi
 *   $userprofile['jabber']   = il nome-utente jabber
 *   $userprofile['skype']    = il nome-utente skype
 *   $userprofile['icq']      = il contatto icq
 *   $userprofile['msn']      = il nome-utente msn
 *   $userprofile['presentation'] = la presentazione dell'utente
 *   $userprofile['level']    = il livello dell'utente
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $user il nome dell'utente di cui caricare il profilo
 * @return array $userprofile un array con i dati dell'utente
 */
function load_user_profile_old($user, $mail_activation=0){
	$user = getparam($user,PAR_NULL, SAN_FLAT);
	$addr = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

	if (!is_alphanumeric($user)){
		if (trim($user)=="") return NULL;
		fnlog("load_user_profile", "$addr||".get_username()."||Username not valid!");
		return NULL;
	}

	if ($mail_activation!=0 and $mail_activation!=1) $mail_activation=0;

	if ($mail_activation==0){
		$filetoload = get_fn_dir("users")."/$user.php";
	} else if ($mail_activation==1){
		$filetoload = get_waiting_users_dir()."/$user.php";
	} else return NULL;

	$userprofile = array();
	if (file_exists($filetoload)){
		$xmltring = get_file($filetoload);
		//la password
		if (ctype_alnum(trim(get_xml_element("password",$xmltring)))){
			$userprofile['password'] = trim(get_xml_element("password",$xmltring));
		} else {
			fnlog("load_user_profile", "$addr||".get_username()."||MD5 password not valid!");
			die("md5 password is not valid!");
		}
		//il nome
		$userprofile['name'] = strip_tags(get_xml_element("name",$xmltring));
		//la mail
		$userprofile['mail'] = strip_tags(get_xml_element("mail",$xmltring));
		//home page
		$userprofile['homepage'] = strip_tags(get_xml_element("homepage",$xmltring));
		//lavoro
		$userprofile['work'] = strip_tags(get_xml_element("work",$xmltring));
		//provenienza
		$userprofile['from'] = strip_tags(get_xml_element("from",$xmltring));
		//avatar
		$userprofile['avatar'] = strip_tags(get_xml_element("avatar",$xmltring));
		//avatar
		$userprofile['hiddenmail'] = strip_tags(trim(get_xml_element("hiddenmail",$xmltring)));
		if (!preg_match("/0|1/",$userprofile['hiddenmail']))
			$userprofile['hiddenmail'] = "1";
		//regdate
		$userprofile['regdate'] = strip_tags(trim(get_xml_element("regdate",$xmltring)));
		if (!check_var($userprofile['regdate'],"digit"))
			$userprofile['regdate'] = "0";
		//regcode
		$userprofile['regcode'] = strip_tags(trim(get_xml_element("regcode",$xmltring)));
		if (!check_var($userprofile['regcode'],"digit"))
			$userprofile['regcode'] = "0";
		//la mail utilizzata per registrarsi
		$userprofile['regmail'] = strip_tags(get_xml_element("regmail",$xmltring));
		//lastedit
		$userprofile['lastedit'] = strip_tags(trim(get_xml_element("lastedit",$xmltring)));
		if (!check_var($userprofile['lastedit'],"digit"))
			$userprofile['lastedit'] = "0";
		//lasteditby
		$userprofile['lasteditby'] = strip_tags(trim(get_xml_element("lasteditby",$xmltring)));
		if (!is_alphanumeric($userprofile['lasteditby']))
			$userprofile['lasteditby'] = "";
		//firma (ora bbcode)
		$userprofile['sign'] = strip_tags(stripslashes(get_xml_element("sign",$xmltring)));
		//jabber
		$userprofile['jabber'] = strip_tags(get_xml_element("jabber",$xmltring));
		//skype
		$userprofile['skype'] = strip_tags(get_xml_element("skype",$xmltring));
		//icq
		$userprofile['icq'] = strip_tags(get_xml_element("icq",$xmltring));
		//msn
		$userprofile['msn'] = strip_tags(get_xml_element("msn",$xmltring));
		//presentazione utente
		$presentation = trim(get_xml_element("presentation",$xmltring));
		$presentation = strip_tags($presentation);
		if (strlen($presentation)<500) {
			$presentation = str_replace("[img]","",$presentation);
			$presentation = str_replace("[/img]","",$presentation);
			$userprofile['presentation']=$presentation;
		}
		else $userprofile['presentation']="";

		//livello
		$check_level = trim(get_xml_element("level",$xmltring));
		if (ctype_digit($check_level) and ($check_level>=0) and ($check_level<=10)){
			$userprofile['level'] = strip_tags(get_xml_element("level",$xmltring));
		} else $userprofile['level'] = "-1";
	}
	else return NULL;

	return $userprofile;
}

/**
 * carica il profilo dell'utente specificato
 *
 * Carica il profilo dell'utente specificato e restituisce un array con i dati.
 * La struttura dell'array restituito è:
 *   $userprofile['password'] = la password codificata in md5 dell'utente
 *   $userprofile['name']     = il nome scelto dal'utente
 *   $userprofile['mail']     = l'indirizzo e-mail specificato
 *   $userprofile['homepage'] = l'home page
 *   $userprofile['work']     = il lavoro
 *   $userprofile['from']     = la provenienza geografica
 *   $userprofile['avatar']   = l'avatar scelto dall'utente
 *   $userprofile['hiddenmail'] = se impostato a "1" (default)
 *                                nasconde la mail agli altri utenti (non agli amministratori)
 *   $userprofile['regdate']  = la data di registrazione sul portale
 *   $userprofile['regcode']  = il codice di registrazione generato dal portale per la registrazione via mail
 *   $userprofile['regmail']  = la mail utilizzata per registrarsi sul portale
 *   $userprofile['lastedit'] = la data di ultima modifica del profilo
 *   $userprofile['lasteditby'] = l'utente che ha fatto l'ultima modifica al profilo
 *   $userprofile['sign']     = la firma che sarà accodata ai messaggi
 *   $userprofile['jabber']   = il nome-utente jabber
 *   $userprofile['skype']    = il nome-utente skype
 *   $userprofile['icq']      = il contatto icq
 *   $userprofile['msn']      = il nome-utente msn
 *   $userprofile['presentation'] = la presentazione dell'utente
 *   $userprofile['level']    = il livello dell'utente
 *
 * @author Aldo Boccacci <zorba_@tin.it>
 * @since 2.5.8
 *
 * @param string $user il nome dell'utente di cui caricare il profilo
 * @return array $userprofile un array con i dati dell'utente
 */
function load_user_profile($user, $mail_activation=0){
	$user = getparam($user,PAR_NULL, SAN_FLAT);
	$addr = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

	if (!is_alphanumeric($user)){
		if (trim($user)=="") return NULL;
		fnlog("load_user_profile", "$addr||".get_username()."||Username not valid!");
		return NULL;
	}

	if ($mail_activation!=0 and $mail_activation!=1) $mail_activation=0;

	if ($mail_activation==0){
		$filetoload = get_fn_dir("users")."/$user.php";
	} else if ($mail_activation==1){
		$filetoload = get_waiting_users_dir()."/$user.php";
	} else return NULL;

	$userprofile = array();
	if (file_exists($filetoload)){
	$string = get_file($filetoload);
	$string = preg_replace("/^<\?php exit\(1\)\;\?>\n/","",$string);
		if (function_exists("simplexml_load_string"))
			$xml = @simplexml_load_string($string);
		else $xml=FALSE;
		if (!$xml){
			return load_user_profile_old($user);
		}
		//la password
		$password = trim(strip_tags($xml->password));
		if (ctype_alnum($password)){
			$userprofile['password'] = $password;
		} else {
			fnlog("load_user_profile", "$addr||"._FN_USERNAME."||MD5 password not valid!");
			die("md5 password is not valid!");
		}
		//il nome
		$userprofile['name'] = strip_tags($xml->name);
		//la mail
		$userprofile['mail'] = strip_tags($xml->mail);
		//home page
		$homepage = strip_tags($xml->homepage);
		if (!preg_match("/^http\:\/\//s",$homepage) and $homepage!="")
			$homepage = "http://".$homepage;
		$userprofile['homepage'] = $homepage;
		//lavoro
		$userprofile['work'] = strip_tags($xml->work);
		//provenienza
		$userprofile['from'] = strip_tags($xml->from);
		//avatar
		$userprofile['avatar'] = strip_tags($xml->avatar);
		//avatar
		$userprofile['hiddenmail'] = strip_tags($xml->hiddenmail);
		if (!preg_match("/0|1/",$userprofile['hiddenmail']))
			$userprofile['hiddenmail'] = "1";
		//regdate
		$userprofile['regdate'] = strip_tags($xml->regdate);
		if (!check_var($userprofile['regdate'],"digit"))
			$userprofile['regdate'] = "0";
		//regcode
		$userprofile['regcode'] = strip_tags($xml->regcode);
		if (!check_var($userprofile['regcode'],"digit"))
			$userprofile['regcode'] = "0";
		//la mail utilizzata per registrarsi
		$userprofile['regmail'] = strip_tags($xml->regmail);
		//lastedit
		$userprofile['lastedit'] = strip_tags($xml->lastedit);
		if (!check_var($userprofile['lastedit'],"digit"))
			$userprofile['lastedit'] = "0";
		//lasteditby
		$userprofile['lasteditby'] = strip_tags($xml->lasteditby);
		if (!is_alphanumeric($userprofile['lasteditby']))
			$userprofile['lasteditby'] = "";
		//firma (ora bbcode)
		$userprofile['sign'] = strip_tags($xml->sign);
		//jabber
		$userprofile['jabber'] = strip_tags($xml->jabber);
		//skype
		$userprofile['skype'] = strip_tags($xml->skype);
		//icq
		$userprofile['icq'] = strip_tags($xml->icq);
		//msn
		$userprofile['msn'] = strip_tags($xml->msn);
		//presentazione utente
		$presentation = strip_tags($xml->presentation);
		$presentation = strip_tags($presentation);
		if (strlen($presentation)<500) {
			$presentation = str_replace("[img]","",$presentation);
			$presentation = str_replace("[/img]","",$presentation);
			$userprofile['presentation']=$presentation;
		}
		else $userprofile['presentation']="";

		//livello
		$check_level = strip_tags($xml->level);
		if (ctype_digit($check_level) and ($check_level>=0) and ($check_level<=10)){
			$userprofile['level'] = $check_level;
		} else $userprofile['level'] = "-1";
	}
	else return NULL;

	return $userprofile;
}

/**
 * Salva il profilo dell'utente indicato con i dati passati
 *
 * L'array ha la seguente struttura:
 *   $data['password']   = password;
 *   $data['name']       = nome;
 *   $data['mail']       = mail;
 *   $data['homepage']   = url del sito web;
 *   $data['work']       = lavoro;
 *   $data['from']       = provenienza;
 *   $data['avatar']     = avatar;
 *   $data['hiddenmail'] = se impostato a "1" (default)
 *                         nasconde la mail agli altri utenti (non agli amministratori)
 *   $data['regdate']    = la data di registrazione sul portale
 *   $data['regcode']    = il codice di registrazione generato dal portale per la registrazione via mail
 *   $data['regmail']    = la mail utilizzata per registrarsi sul portale
 *   $data['lastedit']   = la data di ultima modifica del profilo
 *   $data['lasteditby'] = l'utente che ha fatto l'ultima modifica al profilo
 *   $data['sign']       = firma;
 *   $data['jabber']     = il nome-utente jabber
 *   $data['skype']      = il nome-utente skype
 *   $data['icq']        = il contatto icq
 *   $data['msn']        = il nome-utente msn
 *   $data['presentation'] = la presentazione dell'utente
 *   $data['level']      = livello;
 *
 * @author Aldo Boccacci
 * @since 2.6
 *
 * @param string $user l'utente di cui salvare il profilo
 * @param array $data_array l'array contenente i dati dell'utente
 */
function save_user_profile($user, $data_array,$mail_activation=0){
	$user = getparam($user,PAR_NULL, SAN_FLAT);

	if ($mail_activation!=0 and $mail_activation!=1) $mail_activation=0;

	//per salvare il log
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

	if (!is_alphanumeric($user)){
		fnlog("save_user_profile", "$addr||".get_username()."||Username not valid!");
		die("username is not valid!");
	}

	if (!is_array($data_array)){
		fnlog("save_user_profile", "$addr||".get_username()."||The second argument must be an array!");
		die("the second argoment of the function save_user_profile is not valid!");
	}

	if (!is_dir(get_fn_dir("users"))){
		fn_mkdir(get_fn_dir("users"),0777);
	}

	//controllo tutti i parametri
	$clean_data = array();

	//password
	if (isset($data_array['password'])){
		if (ctype_alnum(trim($data_array['password']))){
			$clean_data['password'] = trim($data_array['password']);
		}
		else {
			fnlog("save_user_profile", "$addr||".get_username()."||The password isn't valid!");
// 			echo "password: ".$data_array['password'];
			die("the password passed to the function save_user_profile is not valid!");
		}
	}
	else {
		fnlog("save_user_profile", "$addr||".get_username()."||The password isn't set!");
		die("the password passed to the function save_user_profile is not set!");
	}

	//nome
	if (isset($data_array['name'])){
		if (strlen($data_array['name'])>30)
			$data_array['name'] = substr($data_array['name'],0,31);
		$clean_data['name'] = strip_tags(fnsanitize($data_array['name']));
		$clean_data['name'] = str_replace("\n","",$clean_data['name']);
		$clean_data['name'] = str_replace("\r","",$clean_data['name']);
		if (is_spam($clean_data['name'],"words",TRUE)){
			echo "<div align=\"center\"><b>"._FNOME."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['name'] = "";

	//mail
	if (isset($data_array['mail'])){
		$clean_data['mail'] = strip_tags(fnsanitize($data_array['mail']));
		$clean_data['mail'] = str_replace("\n","",$clean_data['mail']);
		$clean_data['mail'] = str_replace("\r","",$clean_data['mail']);
		//Spam check
		if (is_spam($clean_data['mail'],"emails")){
			echo "<div align=\"center\"><b>"._FEMAIL."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['mail'] = "";

	//mail
	if (isset($data_array['regmail'])){
		$clean_data['regmail'] = strip_tags(fnsanitize($data_array['regmail']));
		$clean_data['regmail'] = str_replace("\n","",$clean_data['regmail']);
		$clean_data['regmail'] = str_replace("\r","",$clean_data['regmail']);
		//Spam check
		if (is_spam($clean_data['regmail'],"emails")){
			echo "<div align=\"center\"><b>"._FEMAIL."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['regmail'] = "";


	//homepage
	if (isset($data_array['homepage'])){
		if (strlen($data_array['homepage'])>50)
			$data_array['homepage'] = substr($data_array['homepage'],0,51);
		$clean_data['homepage'] = strip_tags(fnsanitize($data_array['homepage']));
		//Spam check
		if (is_spam($clean_data['homepage'],"words",TRUE)){
			echo "<div align=\"center\"><b>"._FHOME."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['homepage'] = "";

	//work
	if (isset($data_array['work'])){
		if (strlen($data_array['work'])>40)
			$data_array['work'] = substr($data_array['work'],0,41);
		$clean_data['work'] = strip_tags(fnsanitize($data_array['work']));
		$clean_data['work'] = str_replace("\n","",$clean_data['work']);
		$clean_data['work'] = str_replace("\r","",$clean_data['work']);
		//Spam check
		if (is_spam($clean_data['work'],"words",TRUE)){
			echo "<div align=\"center\"><b>"._FPROFES."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['work'] = "";

	//from
	if (isset($data_array['from'])){
		if (strlen($data_array['from'])>40)
			$data_array['from'] = substr($data_array['from'],0,41);
		$clean_data['from'] = strip_tags(fnsanitize($data_array['from']));
		$clean_data['from'] = str_replace("\n","",$clean_data['from']);
		$clean_data['from'] = str_replace("\r","",$clean_data['from']);
		//Spam check
		if (is_spam($clean_data['from'],"words",TRUE)){
			echo "<div align=\"center\"><b>"._FPROV."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['from'] = "";

	//avatar
	if (isset($data_array['avatar']) AND $data_array['avatar']!=''){
		$parseUrl = parse_url(trim($data_array['avatar']));
		if(isset($parseUrl['host'])) {
			$url_host = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
		} else $url_host = '';

		if (preg_match("/[\.]jpg$|[\.]jpeg$|[\.]png$|[\.]gif$/i",$data_array['avatar'])){
			$clean_data['avatar'] = strip_tags(fnsanitize($data_array['avatar']));
			$clean_data['avatar'] = str_replace("\n","",$clean_data['avatar']);
			$clean_data['avatar'] = str_replace("\r","",$clean_data['avatar']);
			//Spam check
			if (is_spam($clean_data['avatar'],"words",TRUE)){
				echo "<div align=\"center\"><b>"._FAVAT."</b> "._ISSPAM;
				echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
					die();
			}
		}
		else if(strcmp($url_host, "cdn.libravatar.org") == 0) {
			$clean_data['avatar'] = strip_tags(fnsanitize($data_array['avatar']));
			$clean_data['avatar'] = str_replace("\n","",$clean_data['avatar']);
			$clean_data['avatar'] = str_replace("\r","",$clean_data['avatar']);
                }
		else $clean_data['avatar'] = "images/blank.png";
	}
	else $clean_data['avatar'] = "images/blank.png";


	if (isset($data_array['hiddenmail'])){
		if (preg_match("/0|1/",strip_tags(trim($data_array['hiddenmail']))))
			$clean_data['hiddenmail'] = strip_tags(trim($data_array['hiddenmail']));
		else $clean_data['hiddenmail'] = 1;

	}
	else $clean_data['hiddenmail'] = 1;

	//regdate
	if (isset($data_array['regdate'])){
		if (check_var(trim($data_array['regdate']),"digit")){
			$clean_data['regdate'] = strip_tags(trim($data_array['regdate']));
		}
		else $clean_data['regdate'] = "0";
	}
	else $clean_data['regdate'] = "0";

	//regcode
	if (isset($data_array['regcode'])){
		if (check_var(trim($data_array['regcode']),"digit")){
			$clean_data['regcode'] = strip_tags(trim($data_array['regcode']));
		}
		else $clean_data['regcode'] = "0";
	}
	else $clean_data['regcode'] = "0";

	//lastedit
	if (isset($data_array['lastedit'])){
		if (check_var(trim($data_array['lastedit']),"digit")){
			$clean_data['lastedit'] = strip_tags(trim($data_array['lastedit']));
		}
		else $clean_data['lastedit'] = "0";
	}
	else $clean_data['lastedit'] = "0";

	//lasteditby
	if (isset($data_array['lasteditby'])){
		if (is_alphanumeric(trim($data_array['lasteditby']))){
			$clean_data['lasteditby'] = strip_tags(trim($data_array['lasteditby']));
		}
		else $clean_data['lasteditby'] = "";
	}
	else $clean_data['lasteditby'] = "";

	//sign
	if (isset($data_array['sign'])){
		if (strlen($data_array['sign'])>120)
			$data_array['sign'] = substr($data_array['sign'],0,121);
		$clean_data['sign'] = strip_tags($data_array['sign']);
		//Spam check
		if (is_spam($clean_data['sign'],"words",TRUE)){
			echo "<div align=\"center\"><b>"._FFIRMA."</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	}
	else $clean_data['sign'] = "";

	//jabber
	if (isset($data_array['jabber'])){
		if (strlen($data_array['jabber'])<30){
			$clean_data['jabber'] = trim(strip_tags($data_array['jabber']));
		} else $clean_data['jabber'] = "";
		//Spam check
		if (is_spam($clean_data['jabber'],"words",TRUE)){
			echo "<div align=\"center\"><b>Jabber</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	} else $clean_data['jabber'] = "";
	//skype
	if (isset($data_array['skype'])){
		if (6<strlen($data_array['skype']) and strlen($data_array['skype'])<32){
			$clean_data['skype'] = trim(strip_tags($data_array['skype']));
		} else $clean_data['skype'] = "";
		//Spam check
		if (is_spam($clean_data['skype'],"words",TRUE)){
			echo "<div align=\"center\"><b>Skype</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	} else $clean_data['skype'] = "";
	//icq
	if (isset($data_array['icq'])){
		if (strlen($data_array['icq'])<30){
			$clean_data['icq'] = trim(strip_tags($data_array['icq']));
		} else $clean_data['icq'] = "";
		//Spam check
		if (is_spam($clean_data['icq'],"words",TRUE)){
			echo "<div align=\"center\"><b>Icq</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	} else $clean_data['icq'] = "";
	//msn
	if (isset($data_array['msn'])){
		if (strlen($data_array['msn'])<30){
			$clean_data['msn'] = trim(strip_tags($data_array['msn']));
		} else $clean_data['msn'] = "";
		//Spam check
		if (is_spam($clean_data['msn'],"words",TRUE)){
			echo "<div align=\"center\"><b>Msn</b> "._ISSPAM;
			echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
			die();
		}
	} else $clean_data['msn'] = "";

	//presentation
	if (isset($data_array['presentation'])){
		if (strlen($data_array['presentation'])<500){
			$clean_data['presentation']=strip_tags(str_replace("[/img]","",str_replace("[img]","",$data_array['presentation'])));
			//Spam check
			if (is_spam($clean_data['presentation'],"words",TRUE)){
				echo "<div align=\"center\"><b>"._FNPRESENTATION."</b> "._ISSPAM;
				echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
				die();
			}
		}
		else $clean_data['presentation']="";

	}
	else $clean_data['presentation']="";

	//level
	if (isset($data_array['level'])){
		$data_array['level'] = trim($data_array['level']);
		if (ctype_digit($data_array['level']) and ($data_array['level']>=0) and ($data_array['level']<=10)){
			$clean_data['level'] = strip_tags(fnsanitize($data_array['level']));
		}
		else $clean_data['level'] = "0";
	}
	else $clean_data['level'] = "0";

// 	echo "nome:".$clean_data['name'];
//ora scrivo tutto
	$xmlstring = "<userprofile>
	<password>".$clean_data['password']."</password>
	<name>".$clean_data['name']."</name>
	<mail>".$clean_data['mail']."</mail>
	<hiddenmail>".$clean_data['hiddenmail']."</hiddenmail>
	<homepage>".$clean_data['homepage']."</homepage>
	<work>".$clean_data['work']."</work>
	<from>".$clean_data['from']."</from>
	<avatar>".$clean_data['avatar']."</avatar>
	<regdate>".$clean_data['regdate']."</regdate>
	<regcode>".$clean_data['regcode']."</regcode>
	<regmail>".$clean_data['regmail']."</regmail>
	<lastedit>".$clean_data['lastedit']."</lastedit>
	<lasteditby>".$clean_data['lasteditby']."</lasteditby>
	<sign>".$clean_data['sign']."</sign>
	<jabber>".$clean_data['jabber']."</jabber>
	<skype>".$clean_data['skype']."</skype>
	<icq>".$clean_data['icq']."</icq>
	<msn>".$clean_data['msn']."</msn>
	<presentation>".$clean_data['presentation']."</presentation>
	<level>".$clean_data['level']."</level>
</userprofile>";

	if (preg_match("/\<\?/",$xmlstring) or preg_match("/\?\>/",$xmlstring)){
		fnlog("save_user_profile", "$addr||".get_username()."||The xml profile cannot contains php tags!");
		die("data passed to the function save_user_profile contains php tags!");
	}

	//se è prevista l'attivazione via mail salvo il tutto nella cartella dedicata
	if ($mail_activation==1)
		$userfile = fopen(get_waiting_users_dir()."/$user.php","w");
	else $userfile = fopen(get_fn_dir("users")."/$user.php","w");
	if (fwrite($userfile,"<?php exit(1);?>\n<?xml version='1.0' encoding='UTF-8'?>\n$xmlstring")){ // ISO-8859-1 to UTF-8
		fnlog("save_user_profile", "$addr||".get_username()."||Saved profile of the user $user");
	}
	fclose($userfile);

}


/**
 * Restituisce un array con i componenti del footer
 *
 * Restituisce un array contenente alcuni link e stringhe che possono essere
 * utilizzati per stampare il footer del sito.
 * La struttura dell'array restituito è:
 * $ret_strings['img_fn']   = immagine con link al sito di Flatnuke
 * $ret_strings['img_w3c']  = immagine con link al sito di validazione HTML
 * $ret_strings['img_css']  = immagine con link al sito di validazione CSS
 * $ret_strings['img_rss']  = immagine con link al file RSS
 * $ret_strings['img_mail'] = immagine con link alla mail dell'amministratore
 * $ret_strings['legal']    = nota legale
 * $ret_strings['time']     = tempo di generazione della pagina
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @since 2.6
 *
 * @return array un array con dati/link per il footer
 */
function get_footer_array(){
	global $time1, $admin_mail, $admin, $theme;
	if(file_exists("themes/$theme/images/validate/flatnuke_powered.png")){
		$img_fn = "themes/$theme/images/validate/flatnuke_powered.png";
	} else $img_fn = "images/validate/flatnuke_powered.png";
	if(file_exists("themes/$theme/images/validate/valid_html401.png")){
		$img_w3c = "themes/$theme/images/validate/valid_html401.png";
	} else $img_w3c = "images/validate/valid_html401.png";
	if(file_exists("themes/$theme/images/validate/valid_css.png")){
		$img_css = "themes/$theme/images/validate/valid_css.png";
	} else $img_css = "images/validate/valid_css.png";
	if(file_exists("themes/$theme/images/validate/rss20_powered.png")){
		$img_rss = "themes/$theme/images/validate/rss20_powered.png";
	} else $img_rss = "images/validate/rss20_powered.png";
	if(file_exists("themes/$theme/images/validate/email.png")){
		$img_mail = "themes/$theme/images/validate/email.png";
	} else $img_mail = "images/validate/email.png";
	$ret_strings['img_fn'] = "<a href=\"http://www.flatnuke.org/\" target=\"_blank\" title=\"FlatNuke\">";
	$ret_strings['img_fn'] .= "<img class=\"footerimg\" src=\"$img_fn\" alt=\"FlatNuke\" /></a>";
	$ret_strings['img_w3c'] = "<a href=\"http://validator.w3.org/check/referer\" target=\"_blank\" title=\"Valid HTML 4.01!\">";
	$ret_strings['img_w3c'] .= "<img class=\"footerimg\" src=\"$img_w3c\" alt=\"Valid HTML 4.01!\" /></a>";
	$ret_strings['img_css'] = "<a href=\"http://jigsaw.w3.org/css-validator/check/referer\" target=\"_blank\" title=\"Valid CSS!\">";
	$ret_strings['img_css'] .= "<img class=\"footerimg\" src=\"$img_css\" alt=\"Valid CSS!\" /></a>";
	$ret_strings['img_rss'] = "<a href=\""._FN_VAR_DIR."/backend.xml\" target=\"_blank\" title=\"Get RSS 2.0 Feed\">";
	$ret_strings['img_rss'] .= "<img class=\"footerimg\" src=\"$img_rss\" alt=\"Get RSS 2.0 Feed\" /></a>";
	global $admin_mail;
	if ($admin_mail!="" and check_mail($admin_mail)){
		if (is_dir("sections/none_Email")){
			$ret_strings['img_mail'] = "<a href=\"index.php?mod=none_Email\" title=\""._GOTOSECTION.": Email\">";
			$ret_strings['img_mail'] .= "<img src=\"$img_mail\" alt=\"Mail me!\" /></a>";
		}
		else {
			$ret_strings['img_mail'] = "<a href=\"mailto:$admin_mail\" title=\"Site admin: ".$admin."\">";
			$ret_strings['img_mail'] .= "<img src=\"$img_mail\" alt=\"Mail me!\" /></a>";
		}
	}
	else $ret_strings['img_mail'] ="";
	$ret_strings['legal'] = _LEGAL;
	$time2 = get_microtime();
	$ret_strings['time'] = "Page generated in ".sprintf("%.4f", abs($time2 - $time1))." seconds.";

	return $ret_strings;
}


/**
 * Stampa la lista delle sottosezioni della sezione visualizzata
 *
 * Stampa la lista formattata in una cornice delle sottosezioni della sezione corrente
 *
 * @param string $mod la sezione di cui stampare l'indice
 * @author Aldo Boccacci (dal codice inserito in view_section())
 * @since 2.6.1
 */
function print_subsections($mod){

	if (file_exists("include/redefine/print_subsections.php")){
		include("include/redefine/print_subsections.php");
		return;
	}

	$section = getparam($mod,PAR_NULL,SAN_FLAT);
	if (!check_path($mod,"","false")) return;

	global $theme;

	// Build the list of sub-sections and the list of files
	$modlist = array();
	$fileslist = array();
	$handle = opendir('sections/'.$section);
	while ($tmpfile = readdir($handle)) {
		if(!stristr($tmpfile,"none_")){
			if ( (!preg_match("/^[.]$/i",$tmpfile)) and is_dir("sections/".$section."/".$tmpfile)) {
				if (!user_can_view_section("$section/$tmpfile")) continue;
				if ($tmpfile=="CVS") continue;
				array_push($modlist, $tmpfile);
			}
			if ( (!preg_match("/^\./i",$tmpfile)) and preg_match("/\.txt$|\.htm$|\.html$/i",trim($tmpfile))) {
				array_push($fileslist, $tmpfile);
			}
		}
	}
	closedir($handle);

	if(count($modlist)>0 OR count($fileslist)>0) {
		if(count($modlist)>0) sort($modlist);
		if(count($fileslist)>0) sort($fileslist);
		OpenTable();
		// print subsections
		for ($i=0; $i < sizeof($modlist); $i++) {
			if(stristr($modlist[$i],"_")){
				$tmp = preg_replace("/^[0-9]*_/","",$modlist[$i]);
				$tmp = str_replace("_"," ",$tmp);
			}
			// compatibility with old Flatnuke versions...
			else $tmp = $modlist[$i];
			// Find the image that identifies the current subsection; if not found, takes the default one by the theme
			if(file_exists("sections/$section/$modlist[$i]/section.png")) {
				$subsection_image = "sections/$section/$modlist[$i]/section.png";
			} else $subsection_image = "themes/$theme/images/subsection.png";
			// print link
			echo "<img src='$subsection_image' alt='Subsection' />&nbsp;<a href='index.php?mod=".rawurlencodepath($section."/".$modlist[$i])."' title=\""._GOTOSECTION.": $tmp\"";
			if (get_access_key($section."/".$modlist[$i])!=""){
				echo " accesskey=\"".get_access_key($section."/".$modlist[$i])."\"";
			}
			echo ">$tmp</a><br>";
		}
		// print files
		for ($i=0;$i<count($fileslist);$i++){
			if (file_exists(_FN_SECTIONS_DIR."/$section/".$fileslist[$i].".description") and file_exists(_FN_SECTIONS_DIR."/$section/downloadsection")) continue;
			if (preg_match("/\.html$|\.htm$/i",$fileslist[$i])){
				echo "<img src=\"images/mime/kde/web.png\" alt=\"-\">&nbsp;";
			} else if (preg_match("/\.txt$/i",$fileslist[$i])){
				echo "<img src=\"images/mime/kde/text.png\" alt=\"-\">&nbsp;";
			}
			echo "<a href=\"index.php?mod=".rawurlencodepath($section)."&amp;file=".$fileslist[$i]."\" title=\""._GOTOFILE." ".$fileslist[$i]."\">".$fileslist[$i]."</a><br>";
		}
		CloseTable();
		echo "<br>";
	}
}


/**
 * Restituisce un array con i nomi di tutti gli utenti registrati sul portale indipendentemente dal livello
 *
 * @return un array contenente i nomi degli utenti registrati sul portale
 * @author Aldo Boccacci
 * @since 2.7
 *
 */
function list_users(){
	$files = array();
	$users= array();
	$files = glob(get_fn_dir("users")."/*.php");
	if (!$files) return array(); // glob may returns boolean false instead of an empty array on some systems
	natcasesort($files);
	$username = "";
	$username = preg_replace("/\.php$/i","",basename(current($files)));
	if (is_alphanumeric($username)) $users[] = $username;
	while (next($files)){
		$username = "";
		$username = preg_replace("/\.php$/i","",basename(current($files)));
		if (!is_alphanumeric($username)) continue;
		$users[] = $username;
	}
	return $users;
}

/**
 * Restituisce TRUE se il nome utente è già stato scelto,
 * FALSE in caso contrario
 *
 * @param string $username il nome utente da controllare
 * @author Aldo Boccacci
 * @since 3.0
 */
function user_exists($username,$check_waiting=TRUE){
	if (!check_username($username))
		return FALSE;
	if (check_var($check_waiting,"boolean"))

	$username = strtolower($username);
	$usersarray = list_users();
	for ($count=0;$count<count($usersarray);$count++){
		if ($username==strtolower($usersarray[$count]))
			return TRUE;
	}

	if ($check_waiting){
		$usersarray = list_waiting_users();
		for ($count=0;$count<count($usersarray);$count++){
			if ($username==strtolower($usersarray[$count]))
				return TRUE;
		}
	}


	//se non trovo nulla restituisco FALSE
	return FALSE;

}
/**
 * Elenca gli utenti di livello 10 registrati nel portale.
 * @author Aldo Boccacci
 * @since flatforum 0.1 / flatnuke 2.6.0
 */
function list_admins(){
	$admins = array();
	$users = array();
	$users = glob(get_fn_dir("users")."/*.php");
	if (!$users) return array(); // glob may returns boolean false instead of an empty array on some systems
	for ($count=0;$count<count($users);$count++){
		$username = "";
		$username = preg_replace("/\.php$/i","",basename($users[$count]));
		if (getlevel($username, "home")=="10")
			$admins[] = $username;
	}
	return $admins;
}


/**
 * Carica l'elenco degli utenti autorizzati a visualizzare una certa sezione
 *
 * @param string $mod la sezione di cui caricare i permessi
 * @return una array contenente i nomi degli utenti che possono visualizzare la sezione
 */
function load_user_view_permissions($mod){
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	if  (!check_path($mod,"","false")) die();

	if (!is_dir("sections/$mod")) return NULL;
	if (!file_exists("sections/$mod/view.php")) return array();

	$string = get_file("sections/$mod/view.php");
	$string = get_xml_element("view",$string);

	$usersarray = array();
	$checkedusers = array();
	$usersarray = get_xml_array("user",$string);

	$cu = 0;
	for ($cu; $cu<count($usersarray);$cu++){
		$user = "";
		$user = strip_tags(trim($usersarray[$cu]));
		if (is_alphanumeric($user))
			$checkedusers[] = $user;
	}

	return $checkedusers;
}


/**
 * Carica l'elenco degli utenti autorizzati a modificare una certa sezione
 *
 * @param string $mod la sezione di cui caricare i permessi
 * @return una array contenente i nomi degli utenti che possono modificare la sezione
 */
function load_user_edit_permissions($mod){
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	if  (!check_path($mod,"","false")) die();

	if (!is_dir("sections/$mod")) return NULL;
	if (!file_exists("sections/$mod/edit.php")) return array();

	$string = get_file("sections/$mod/edit.php");
	$string = get_xml_element("edit",$string);

	$usersarray = array();
	$checkedusers = array();
	$usersarray = get_xml_array("user",$string);

	$cu = 0;
	for ($cu; $cu<count($usersarray);$cu++){
		$user = "";
		$user = strip_tags(trim($usersarray[$cu]));
		if (is_alphanumeric($user))
			$checkedusers[] = $user;
	}

	return $checkedusers;
}

/**
 * Salva la lista degli utenti autorizzati a modificare una sezione
 *
 * @param string $mod la sezione di cui savlare i permessi
 * @param array $users l'elenco degli utenti autorizzati a modificare la sezione
 * @return una array contenente i nomi degli utenti che possono modificare la sezione
 */
function save_user_edit_permissions($mod,$users){
	if (!_FN_IS_ADMIN) return;
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

	if  (!check_path($mod,"","false")) die();
	if (!is_array($users)) die("\$users must be an array!");

	if (!is_dir("sections/$mod")) return NULL;
	if (!is_writable("sections/$mod/")){
		die(_THEDIR." sections/$mod/"._NOTWRITABLE);
	}

	$checkedusers = array();
	$cu =0;
	for ($cu;$cu<count($users);$cu++){
		$user = strip_tags(trim($users[$cu]));
		if (file_exists(get_fn_dir("users")."/$user.php")){
			if (is_alphanumeric($user)){
				if (!in_array($user,$checkedusers)) $checkedusers[] = $user;
			}
		}
	}

	if (count($checkedusers)==0){
		if (file_exists(get_fn_dir("sections")."/$mod/edit.php"))
			if (unlink(get_fn_dir("sections")."/$mod/edit.php")){
				fnlog("Section manage:","$addr||".get_username()."||Section ".strip_tags($mod)." with no more user edit permissions");
				return;
			}
			else fnlog("Section manage","$addr||".get_username()."||I'm not able to delete the file: ".get_fn_dir("sections")."/$mod/edit.php");
	}

	$string = "<edit>\n";
	$cu = 0;
	for ($cu; $cu<count($checkedusers);$cu++){
		$string .= "\t<user>".$checkedusers[$cu]."</user>\n";
	}
	$string .= "</edit>";

	if (preg_match("/\<\?/",$string) or preg_match("/\?\>/",$string))
		die(_NONPUOI);

	fnwrite("sections/$mod/edit.php","<?xml version='1.0' encoding='UTF-8'?>\n".$string,"w",array("nonull"));
	fnlog("Section manage","$addr||".get_username()."||Save edit permission for section ".strip_tags($mod));
}


/**
 * Salva la lista degli utenti autorizzati a visualizzare una sezione
 *
 * @param string $mod la sezione di cui salvare i permessi
 * @param array $users l'elenco degli utenti autorizzati a visualizzare la sezione
 * @return una array contenente i nomi degli utenti che possono visualizzare la sezione
 */
function save_user_view_permissions($mod,$users){
	if (!_FN_IS_ADMIN) return;
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	if  (!check_path($mod,"","false")) die();
	if (!is_array($users)) die("\$users must be an array!");

	if (!is_dir("sections/$mod")) return NULL;
	if (!is_writable("sections/$mod/")){
		die(_THEDIR." sections/$mod/"._NOTWRITEBLE);
	}

	$checkedusers = array();
	$cu =0;
	for ($cu;$cu<count($users);$cu++){
		$user = strip_tags(trim($users[$cu]));
		if (file_exists(get_fn_dir("users")."/$user.php")){
			if (is_alphanumeric($user)){
				if (!in_array($user,$checkedusers)) $checkedusers[] = $user;
			}
		}
	}

	if (count($checkedusers)==0){
		if (file_exists(get_fn_dir("sections")."/$mod/view.php"))
			if (unlink(get_fn_dir("sections")."/$mod/view.php")){
				fnlog("Section manage","$addr||".get_username()."||Section ".strip_tags($mod)." with no more user view restrictions");
				return;
			}
			else fnlog("Section manage","$addr||".get_username()."||I'm not able to delete the file: ".get_fn_dir("sections")."/$mod/view.php");
	}

	$string = "<view>\n";
	$cu = 0;
	for ($cu; $cu<count($checkedusers);$cu++){
		$string .= "\t<user>".$checkedusers[$cu]."</user>\n";
	}
	$string .= "</view>";

	if (preg_match("/\<\?/",$string) or preg_match("/\?\>/",$string))
		die(_NONPUOI);

	fnwrite("sections/$mod/view.php","<?xml version='1.0' encoding='UTF-8'?>\n".$string,"w",array("nonull"));
	fnlog("Section manage","$addr||".get_username()."||Save edit permission for section ".strip_tags($mod));
}


/**
 * Restituisce il percorso della cartella che contiene i profili in attesa di validazione via mail
 * e nel caso non esista la crea al volo.
 *
 * @author Aldo Boccacci
 * @since 2.7
 * @return il percorso della cartella contenente i profili degli utenti in attesa di validazione
 */
function get_waiting_users_dir(){

	if (!is_dir(_FN_USERS_DIR)){
		fn_mkdir(_FN_USERS_DIR,0777);
	}

	if (!file_exists(_FN_USERS_DIR."/index.html")){
		fnwrite(_FN_USERS_DIR."/index.html"," ","w",array("nonull"));
	}

	if (file_exists(_FN_VAR_DIR."/waitingusersdir.php")){
		$code = get_xml_element("waitingusersdir",get_file(_FN_VAR_DIR."/waitingusersdir.php"));
		if (check_var($code,"digit")){
			//se non esiste la cartella la creo
			if (!file_exists(_FN_USERS_DIR."/$code"))
				fn_mkdir(_FN_USERS_DIR."/$code",0777);

			//creo anche il file index.html per evitare di far vedere i file contenuti
			if (!file_exists(_FN_USERS_DIR."/$code/index.html")){
				fnwrite(_FN_USERS_DIR."/$code/index.html"," ","w",array());
			}
			return _FN_USERS_DIR."/$code/";
		}
		else return FALSE;
	}
	else {
		//se non esite il file /var/waitingusersdir.php lo creo assieme alla cartella associata
		$random = mt_rand(0,9999999999);

		if (!file_exists(_FN_USERS_DIR."/$random")){
			fn_mkdir(_FN_USERS_DIR."/$random",0777);
			fnwrite(_FN_USERS_DIR."/index.html"," ","w",array());
			fnwrite(_FN_VAR_DIR."/waitingusersdir.php","<?xml version='1.0' encoding='UTF-8'?>
	<waitingusersdir>$random</waitingusersdir>","w",array("nonull"));
		}

		if (!file_exists(_FN_USERS_DIR."/$random/index.html")){
		fnwrite(_FN_USERS_DIR."/$random/index.html"," ","w",array("nonull"));
		}

		return _FN_USERS_DIR."/$random/";
	}

}


/**
 * Elenco email degli utenti registrati
 *
 * La funzione restituisce un array con l'elenco
 * delle email di tutti gli utenti registrati.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.7.1
 *
 * @return array Elenco delle email
 */
function list_users_emails(){
	$emails = array();
	$users  = list_users();
	for ($count=0;$count<count($users);$count++) {
		$data = load_user_profile($users[$count]);
		$user = $users[$count];
		if(trim($data['mail'])!="") {
			$emails[$user] = $data['mail'];
		}
	}
	return $emails;
}


/**
 * Restituisce un array con le email utilizzate per l'attivazione dei profili utente
 *
 * @param int $waitingusers se impostato a 0 restituisce le mail degli utenti già registrati,
 *            se impostata a 1 restituisce le email degli utenti in attesa di validazione
 * @author Aldo Boccacci
 * @since 2.7
 */
function list_reg_emails($waitingusers=0){
	if (!preg_match("/1|0/",$waitingusers)) $waitingusers=0;
	$emails = array();
	$files = array();
        $users = array();
	if ($waitingusers==0){
		$users = list_users();
		for ($count=0;$count<count($users);$count++){
			$data = load_user_profile($users[$count]);
			$user = $users[$count];
			$emails[$user] = $data['mail'];
		}
	}
	else if($waitingusers==1){
		$users = list_waiting_users();
		for ($count=0;$count<count($users);$count++){
			$data = load_user_profile($users[$count],1);
			$user = $users[$count];
			$emails[$user] = $data['regmail'];
		}
	}
	return $emails;
}


/**
 * Restituisce un array con i nomi di tutti gli utenti in attesa di validazione via mail
 * sul portale
 *
 * @return un array contenente i nomi degli utenti in attesa di validazione sul portale
 * @author Aldo Boccacci
 * @since 2.7
 *
 */
function list_waiting_users(){
	$files = array();
	$users= array();
	$files = glob(get_waiting_users_dir()."/*.php");
	if (!$files) return array(); // glob may returns boolean false instead of an empty array on some systems
	for ($count=0;$count<count($files);$count++){
		$username = "";
		$username = preg_replace("/\.php$/i","",basename($files[$count]));
		if (!is_alphanumeric($username)) continue;
		$users[] = $username;
	}
	return $users;
}


/**
 * Funzione che controlla la validità di un indirizzo e-mail
 *
 * @param string $mail la mail da controllare
 * @return TRUE se la mail è valida o FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 2.7
 */
function check_mail($mail){
	$mail = getparam($mail, PAR_NULL,SAN_NULL);
	return preg_match("/^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)$/i",$mail);
}


/**
 * Controlla la validità di un nome utente
 * @param string $username la mail da controllare
 * @return TRUE se il nome utente è valido o FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 2.7
 *
 */
function check_username($username){
	$username = getparam($username, PAR_NULL,SAN_NULL);
	return is_alphanumeric($username);
}


/**
 * Interfaccia che permette di rinominare una sezione
 *
 * @param string $mod il mod della sezione da rinominare
 * @author Aldo Boccacci
 * @since 2.7
 */
function rename_sect_interface($mod){
	if (!_FN_IS_ADMIN) die();
	$mod = getparam($mod,PAR_NULL,SAN_NULL);
	if (!check_path($mod,"","false")) fn_die("RENAMESECTINTERFACE","\$mod is not valid!",__FILE__,__LINE__);
	$sectname = "";
	$sectname = basename($mod);
	$sectnamedecoded = rawurldecode($sectname);

	echo "<b>"._FNRENAMESECTION.":</b>";
	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fnrenamesect\" />";
	echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"".dirname($mod)."\" />";
	echo "<input type=\"hidden\" readonly name=\"fnoldsectname\" value=\"".rawurlencodepath($sectnamedecoded)."\" />";
	echo "<input type=\"text\" name=\"fnnewsectname\" size=\"20\" value=\"$sectnamedecoded\" /><br/><br/>
	<input type=\"submit\" name=\"fnok\" value=\""._RENAME."\" />";
	echo "</form>";
}


/**
 * Funzione che permette di rinominare una sezione
 * @author Aldo Boccacci
 * @since 2.7
 */
function rename_section(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnoldsectname = getparam("fnoldsectname",PAR_POST,SAN_FLAT);
	$fnnewsectname = getparam("fnnewsectname",PAR_POST,SAN_FLAT);
	$fnnewsectname = preg_replace("/ /","_",$fnnewsectname);
	$fnsectpath = rawurldecode($fnsectpath);
	$fnnewsectname = rawurldecode($fnnewsectname);
	$fnoldsectname = rawurldecode($fnoldsectname);

	if (!check_path($fnsectpath,"","false")) fn_die("RENAMESECTION","\$fnsectpath is not valid!",__FILE__,__LINE__);
	if (!check_path($fnoldsectname,"","false")) fn_die("RENAMESECTION","\$fnoldsectname is not valid!",__FILE__,__LINE__);
	if (!check_path($fnnewsectname,"","false")) fn_die("RENAMESECTION","\$fnnewsectname is not valid!",__FILE__,__LINE__);

	if (!is_dir(get_fn_dir("sections")."/$fnsectpath/$fnoldsectname")){
		echo "<div style=\"text-align: center;\">"._THEDIR." <b>$fnsectpath/$fnoldsectname</b> "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></dir>";
		return;
	}

	if (!is_writable(get_fn_dir("sections")."/$fnsectpath/$fnoldsectname")){
		echo _FIG_ALERTNOTWR.": ".strip_tags("$fnsectpath/$fnoldsectname");
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (is_dir(get_fn_dir("sections")."/$fnsectpath/$fnnewsectname")){
		echo "<div style=\"text-align: center;\">"._THEDIR." <b>$fnsectpath/$fnnewsectname</b> "._ALREADYEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (rename(get_fn_dir("sections")."/$fnsectpath/$fnoldsectname",get_fn_dir("sections")."/$fnsectpath/$fnnewsectname")){
		if (file_exists(get_fn_dir("sections")."/$fnsectpath/$fnnewsectname/news"))
			save_news_sections_list(list_news_sections());
			rebuild_proposed_news_list();
		echo "<div style=\"text-align: center;\"><b>"._FNSECTIONRENAMED."</b>";
		echo "<br><a href=\"index.php?mod=$fnsectpath/$fnnewsectname\" title=\""._GOTOSECTION."\">"._GOTOSECTION." <b>$fnnewsectname</b></a></div>";
		fnlog("Section manage","$addr||".get_username()."||Section ".strip_tags($fnoldsectname)." renamed in ".strip_tags($fnnewsectname));
	}
}


/**
 * Interfaccia che permette di creare una sezione
 *
 * @param string $mod il mod della sezione nella quale creare una sottosezione
 * @author Aldo Boccacci
 * @since 2.7
 */
function create_sect_interface($mod){
	if (!_FN_IS_ADMIN) die();
	$urldecodedmod = rawurldecode($mod);
	if (!check_path($urldecodedmod,"","false")) fndie("\$mod is not valid!",__FILE__,__LINE__);
	$sectname = "";
	$sectname = basename($urldecodedmod);
	?>
	<script type="text/javascript">
	function validate_sect_form()
		{
			if(document.getElementById('fnnewsectname').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FDSECT?>');
					document.getElementById('fnnewsectname').focus();
					document.getElementById('fnnewsectname').value='';
					return false;
				}
			else return true;
	}
	</script>
	<?php
	//controllo se sto creando una toplevel section o una sottosezione
	if (trim($mod)!="") echo "<b>"._FNCREATESUBSECTION.":</b>";
	else echo "<b>"._FNCREATESECTION.":</b>";
	echo "<form action=\"index.php\" method=\"POST\" onsubmit=\"return validate_sect_form()\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fncreatesect\" />";
	echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"$mod\" />";
	echo "<input type=\"text\" name=\"fnnewsectname\" id=\"fnnewsectname\" size=\"20\" value=\"\" /><br/><br/>";

	echo "<b>"._SECTIONTYPE."</b>&nbsp;";
	echo "<select id=\"fnsecttype\" name=\"fnsecttype\">
		<option value=\"standard\">Standard</option>
		<option value=\"download\" title=\""._DOWNLOADMAINTITLE."\">"._DOWNLOADMAIN."</option>
		<option value=\"downloadsection\" title=\""._DOWNLOADSINGLETITLE."\">"._DOWNLOADSINGLE."</option>
		<option value=\"forum\">Forum</option>
		<option value=\"gallery\">Gallery</option>
		<option value=\"news\">News</option>";
	echo "</select><br><br>";
	echo "<input type=\"submit\" name=\"fnok\" value=\""._CREATE."\" />";
	echo "</form>";
}


/**
 * Interfaccia che permette di creare una sezione
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function create_section(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnsectpath = rawurldecode($fnsectpath);
	$fnnewsectname = getparam("fnnewsectname",PAR_POST,SAN_FLAT);
	$fnnewsectname = rawurldecode($fnnewsectname);
	$fnnewsectname = preg_replace("/ /","_",$fnnewsectname);
	$fnsecttype = getparam("fnsecttype",PAR_POST,SAN_FLAT);
	//fix slashes in name
	$fnsectpath = stripslashes($fnsectpath);
	$fnnewsectname = stripslashes($fnnewsectname);

	if (!check_path($fnsectpath,"","false")) fn_die("RENAMESECTION","\$fnsectpath is not valid!",__FILE__,__LINE__);
	if (!check_path($fnnewsectname,"","false")) fn_die("RENAMESECTION","\$fnnewsectname is not valid!",__FILE__,__LINE__);

	if (!is_writable(get_fn_dir("sections")."/$fnsectpath")){
		echo _FIG_ALERTNOTWR.": ".strip_tags($fnsectpath);
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (!is_dir(get_fn_dir("sections")."/$fnsectpath")){
		echo _THEDIR." <b>$fnsectpath</b> "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}
	if (is_dir(get_fn_dir("sections")."/$fnsectpath/$fnnewsectname") or
		file_exists(get_fn_dir("sections")."/$fnsectpath/$fnnewsectname")){
		echo _THEDIR." <b>$fnnewsectname</b> "._ALREADYEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (fn_mkdir(get_fn_dir("sections")."/$fnsectpath/$fnnewsectname",0777)){
		fnwrite(get_fn_dir("sections")."/$fnsectpath/$fnnewsectname/section.php"," ","w",array());
		echo "<div style=\"text-align: center;\"><b>"._FNSECTIONCREATED."</b>";
		echo "<br><a href=\"index.php?mod=".rawurlencode($fnsectpath."/".$fnnewsectname)."\" title=\""._GOTOSECTION."\">"._GOTOSECTION." <b>$fnnewsectname</b></a></div>";
		fnlog("Section manage","$addr||".get_username()."||Section created: ".strip_tags("$fnsectpath/$fnnewsectname"));

		if ($fnsecttype=="forum"){
			set_section_type("$fnsectpath/$fnnewsectname","forum");
		}
		else if ($fnsecttype=="gallery"){
			set_section_type("$fnsectpath/$fnnewsectname","gallery");
		}
		else if ($fnsecttype=="download"){
			set_section_type("$fnsectpath/$fnnewsectname","download");
		}
		else if ($fnsecttype=="downloadsection"){
			set_section_type("$fnsectpath/$fnnewsectname","downloadsection");
		}
		else if ($fnsecttype=="news"){
			set_section_type("$fnsectpath/$fnnewsectname","news");
		}
	}
	else {
		echo "I was not able to create the section: ".strip_tags("$fnsectpath/$fnnewsectname");
	}
}


/**
 * Interfaccia per confermare l'eliminazione della sezione
 * (solo amministratori)
 *
 * @param string $mod il $mod della sezione da eliminare
 * @author Aldo Boccacci
 * @since 2.7
 */
function delete_sect_interface($mod){
	if (!_FN_IS_ADMIN) die();
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	$decodedmod = rawurldecode($mod);
	if (!check_path($decodedmod,"","false")) fndie("\$mod is not valid!",__FILE__,__LINE__);
	$shownmod= preg_replace("/^\//s","",$decodedmod);
	$shownmod = preg_replace("/^[0-9][0-9]_/s","",$shownmod);
	echo _FNDELETESECTION.": <b>$shownmod</b><br><br>";

	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fndeletesect\" />";
	echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"$mod\" />
	<input type=\"submit\" name=\"fnok\" value=\""._FNDELETESECTION."\" />";
	echo "</form>";
}


/**
 * Funzione che si occupa di eliminare una sezione
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function delete_section(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnsectpath = urldecode($fnsectpath);
	$updir = dirname($fnsectpath);

	//controllo se si tratta di una sezione di news
	$is_news_dir = FALSE;
	if (file_exists("sections/$fnsectpath/news"))
		$is_news_dir = TRUE;

	if (!is_dir(get_fn_dir("sections")."/$updir")){
		echo _THEDIR." <b>$updir</b> "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (!is_writable(get_fn_dir("sections")."/$updir")){
		echo _FIG_ALERTNOTWR.": ".strip_tags($updir);
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	$subdirs = array();
	$handle = opendir("sections/$fnsectpath");
	while ($tmpfile = readdir($handle)) {
		if (!( $tmpfile=="." or $tmpfile==".." ) and is_dir("sections/$fnsectpath/$tmpfile")) {
		array_push($subdirs, $tmpfile);
		}
	}
	closedir($handle);

	if (count($subdirs)!=0){
		$shownmod= preg_replace("/^\//s","",$fnsectpath);
		$shownmod = preg_replace("/^[0-9][0-9]_/s","",$shownmod);
		echo "<b>"._THESECTION." $fnsectpath "._ISNTEMPTY."!</b><br><br>";

		echo _FNDELETESECTION.": <b>$shownmod</b><br><br>";

		echo "<form action=\"index.php\" method=\"POST\">";
		echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fnerasesect\" />";
		echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"$fnsectpath\" />
		<input type=\"checkbox\" name=\"confirmdelete\" id=\"confirmdelete\" value=\"true\">"._CONFIRM."<br><br>
		<input type=\"submit\" name=\"fnok\" value=\""._FNDELETESECTION."\" />";
		echo "</form>";

		//print section content
		$files_in_dir = scandir("sections/$fnsectpath");
		global $theme;
		include_once("download/include/fdfunctions.php");
		include("download/fdconfig.php");
		foreach ($files_in_dir as $file){
			if (is_dir("sections/$fnsectpath/$file") and $file!="." and $file!=".."){
				$dirs[] = $file;
			}
			else {
				if ($file!="." and $file!="..")
					$files[] = $file;
			}
		}

		echo "<br>";
		echo "<h3>"._CONTENT.":</h3>";
		//if there aren't dirs return
		if (count($dirs)!=0){
			echo "<b>"._FDSUBDIRS."</b>: <br>";
			foreach ($dirs as $dir){
				echo "<img src=\"themes/$theme/images/subsection.png\" alt=\"Subsection\">&nbsp;".basename($dir);
				if (!is_writable("sections/$fnsectpath/$dir")) echo " <span style=\"color : #ff0000;\">("._FIG_ALERTNOTWR."!)</span>";
				echo "<br>";
			}
		}
		//if there aren't files return
		if (count($files)==0) return;
		echo "<br>";
		echo "<b>Files</b>: <br>";
		//for each file
		foreach ($files as $file){
			//if is a Flatnuke system file
			if ($file=="section.php" or $file=="level.php"
				or $file=="view.php" or $file=="edit.php"
				or $file=="accesskey.php" or $file=="download"
				or $file=="downloadsection" or $file=="forum"
				or $file=="gallery" or $file=="news"
				or $file=="fduserupload"
				or preg_match("/\.description$/",$file)
				or preg_match("/\.sig$/",$file))
				continue;
			$fileinfo = pathinfo($file);
// 			echo $file;
			$ext = $fileinfo['extension'];
			echo getIcon($ext,$icon_style)."&nbsp;$file";
			if (!is_writable("sections/$fnsectpath/$file"))
				echo " <span style=\"color : #ff0000;\">("._NOTWRITABLE."!)</span>";
			echo "<br>";
		}

		return;
	}

	//carico l'elenco dei tags che sarà poi eliminato se la sezione verrà effettivamente
	//rimossa
	$tags_list = load_tags_list($fnsectpath);
	$tags_list = array_keys($tags_list);

	if (rmdirr("sections/$fnsectpath")){
		//se si tratta di una sezione di notizie ricreo il file
		//news_sections_list.php
		if ($is_news_dir)
			save_news_sections_list(list_news_sections());
		rebuild_proposed_news_list();

		//e tolgo i tag dalla lista generale
		remove_tags_from_tags_list($tags_list);
		// la lista dei tag della sezione verrà eliminata con la sezione

		echo "<div style=\"text-align: center;\"><b>"._FNSECTIONDELETED."</b>";
		if ($updir==".")
			echo "<br><br><a href=\"index.php\" title=\"Home page\">Home page</a></div>";
		else echo "<br><br><a href=\"index.php?mod=$updir\" title=\""._GOTOSECTION." $updir\">"._GOTOSECTION." $updir</a></div>";

		fnlog("Section manage","$addr||".get_username()."||Deleted dir $fnsectpath");
	}
	else {
		echo "It was not possible to delete the dir $fnsectpath";
		fnlog("Section manage","$addr||".get_username()."||Error deleting dir $fnsectpath");
	}
}

/**
 * Funzione che si occupa di eliminare una sezione senza controllare se è vuota o meno
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function erase_section(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnsectpath = urldecode($fnsectpath);
	$updir = dirname($fnsectpath);
	$confirmdelete = getparam("confirmdelete",PAR_POST,SAN_FLAT);
	if ($confirmdelete!="true")
		$confirmdelete="false";

	if ($confirmdelete!="true"){
		echo "<div style=\"text-align: center;\">";
		echo "<br><b>"._DELETESECTNOCONFIRM."</b>";
		echo "<br><br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	//controllo se si tratta di una sezione di news
	$is_news_dir = FALSE;
	if (file_exists("sections/$fnsectpath/news"))
		$is_news_dir = TRUE;

	if (!is_dir(get_fn_dir("sections")."/$updir")){
		echo _THEDIR." <b>$updir</b> "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (!is_writable(get_fn_dir("sections")."/$updir")){
		echo _FIG_ALERTNOTWR.": ".strip_tags($updir);
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	//carico l'elenco dei tags che sarà poi eliminato se la sezione verrà effettivamente
	//rimossa
	$tags_list = load_tags_list($fnsectpath);

	if (rmdirr("sections/$fnsectpath")){
		//se si tratta di una sezione di notizie ricreo il file
		//news_sections_list.php
		if ($is_news_dir)
			save_news_sections_list(list_news_sections());
		rebuild_proposed_news_list();

		//e tolgo i tag dalla lista generale
		remove_section_tags_from_tags_list($tags_list);
		// la lista tags della sezione viene eliminata con la sezione

		echo "<div style=\"text-align: center;\"><b>"._FNSECTIONDELETED."</b>";
		if ($updir==".")
			echo "<br><br><a href=\"index.php\" title=\"Home page\">Home page</a></div>";
		else echo "<br><br><a href=\"index.php?mod=$updir\" title=\""._GOTOSECTION." $updir\">"._GOTOSECTION." $updir</a></div>";

		fnlog("Section manage","$addr||".get_username()."||Deleted dir $fnsectpath");
	}
	else {
		echo "It was not possible to delete the dir $fnsectpath";
		fnlog("Section manage","$addr||".get_username()."||Error deleting dir $fnsectpath");
	}
}

/**
 * Interfaccia per creare nuovi file nelle sezioni
 *
 * @param string $mod la sezione nella quale creare il file
 * @author Aldo Boccacci
 * @since 2.7
 */
function create_file_interface($mod){
	if (!_FN_IS_ADMIN) die();
	$mod = getparam($mod,PAR_NULL,SAN_NULL);
	$mod = rawurldecode($mod);
	if (!check_path($mod,"","false")) fndie("\$mod is not valid!",__FILE__,__LINE__);
	$sectname = "";
	$sectname = basename($mod);

	echo "<b>"._FNCREATEFILE.":</b><br>";
	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fncreatefile\" />";
	echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"".rawurlencodepath($mod)."\" />";
	echo "<input type=\"text\" name=\"fnnewfilename\" size=\"20\" value=\"\" />.<select name=\"fnfileext\" id=\"fnfileext\">
	<option>html</option>
	<option>htm</option>
	<option>txt</option>
	</select>
	<br/><br/>
	<input type=\"submit\" name=\"fnok\" value=\""._CREATE."\" />";
	echo "</form>";
}


/**
 * Funzione per creare nuovi file nelle sezioni
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function create_file(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnsectpath = rawurldecode($fnsectpath);
	$fnnewfilename = getparam("fnnewfilename",PAR_POST,SAN_FLAT);
	$fnnewfilename = rawurldecode($fnnewfilename);
	$fnnewfilename = preg_replace("/ /","_",$fnnewfilename);
	$fnfileext = trim(getparam("fnfileext",PAR_POST,SAN_FLAT));
	if (!preg_match("/^html$|^html$|^txt$|^php$/i",$fnfileext)) $fnfileext = "html";

	$fnnewfilename = "$fnnewfilename.$fnfileext";

	if (!check_path($fnsectpath,"","false")) fn_die("NEWFILE","\$fnsectpath is not valid!",__FILE__,__LINE__);
	if (!check_path($fnnewfilename,"","true")) fn_die("NEWFILE","\$fnnewfilename is not valid!",__FILE__,__LINE__);

	if (!is_writable(get_fn_dir("sections")."/$fnsectpath")){
		echo _FIG_ALERTNOTWR.": ".strip_tags($fnsectpath);
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (file_exists(get_fn_dir("sections")."/$fnsectpath/$fnnewfilename")){
		echo _ERROR.": "._THEFILE." <b>$fnnewfilename</b> "._ALREADYEXISTS;
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	fnwrite(get_fn_dir("sections")."/$fnsectpath/$fnnewfilename","text","w",array());
	if (file_exists(get_fn_dir("sections")."/$fnsectpath/$fnnewfilename")){
		echo "<div style=\"text-align: center;\"><b>"._FNFILECREATED."</b>";
		echo "<br><a href=\"index.php?mod=$fnsectpath&amp;file=$fnnewfilename\" title=\""._GOTOFILE."\">"._GOTOFILE." <b>$fnnewfilename</b></a></div>";
		fnlog("Section manage","$addr||".get_username()."||File created: ".strip_tags("$fnsectpath/$fnnewfilename"));
	}
	else {
		echo "I was not able to create the file: ".strip_tags("$fnsectpath/$fnnewfilename");
	}
}


/**
 * Interfaccia per confermare l'eliminazione di un file
 * (solo amministratori)
 *
 * @param string $file il file da eliminare
 * @author Aldo Boccacci
 * @since 2.7
 */
function delete_file_interface($file){
	if (!_FN_IS_ADMIN) die();
	$file = getparam($file,PAR_NULL,SAN_FLAT);
	$file = rawurldecode($file);
	if (!check_path($file,"","true")) fndie("\$file is not valid!",__FILE__,__LINE__);
	echo _FNDELETEFILE.": <b>$file</b><br><br>";

	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fndeletefile\" />";
	echo "<input type=\"hidden\" readonly name=\"fnfilepath\" value=\"".rawurlencodepath($file)."\" />
	<input type=\"submit\" name=\"fnok\" value=\""._FNDELETEFILE."\" />";
	echo "</form>";
}


/**
 * Funzione che si occupa di eliminare un file presente nelle sezioni
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function delete_file(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnfilepath = getparam("fnfilepath",PAR_POST,SAN_FLAT);
	$fnfilepath = rawurldecode($fnfilepath);
	if (!check_path($fnfilepath,"","false")) fn_die("DELETEFILE",_NONPUOI,__FILE__,__LINE__);
	$dir = dirname($fnfilepath);

	if (!file_exists("sections/$fnfilepath")){
		echo "<div style=\"text-align: center;\">"._THEFILE." <b>sections/$fnfilepath</b> "._DOESNTEXISTS;
		echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
	}

	if (is_writable("sections/$fnfilepath")){
		if (unlink("sections/$fnfilepath")){
			echo "<div style=\"text-align: center;\"><b>"._FNFILEDELETED."</b>";
			echo "<br><br><a href=\"index.php?mod=$dir\" title=\""._GOTOSECTION." $dir\">"._GOTOSECTION." $dir</a></div>";

			fnlog("Section manage","$addr||".get_username()."||File sections/$fnfilepath deleted");
		}
		else {
			echo "I was not able to delete the file";
		}
	}
	else {
		echo "<div align=\"center\">"._THEFILE." <b>section/$fnfilepath</b> "._NOTWRITABLE;
		echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
	}
}


/**
 * Interfaccia che permette di rinominare un file gestito da Flatnuke
 *
 * @param string $file il file per cui mostrare l'interfaccia
 * @author Aldo Boccacci
 * @since 2.7
 */
function rename_file_interface($file){
	if (!_FN_IS_ADMIN) die();
	$file = getparam($file,PAR_NULL,SAN_FLAT);
	$file = rawurldecode($file);
	if (!check_path($file,"","true")) fndie("\$file is not valid!",__FILE__,__LINE__);
	if (basename($file)=="section.php") {
		echo "You cannot rename the file section.php";
		return;
	}
	$pathinfo = array();
	$pathinfo = pathinfo("$file");
// 	print_r($pathinfo);

	$filename= "";
	$filename = preg_replace("/\./".$pathinfo['extension']."$","",basename($file));

	echo "<b>"._FNRENAMEFILE.":</b><br><br>";
	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fnrenamefile\" />";
	echo "<input type=\"hidden\" readonly name=\"fnoldfilename\" value=\"".rawurlencodepath($file)."\" />";
	echo "<input type=\"text\" name=\"fnnewfilename\" size=\"20\" value=\"$filename\" />.<select name=\"fnfileext\" id=\"fnfileext\">
	<option";
	if ($pathinfo['extension']=="html") echo " selected=\"selected\"";
	echo ">html</option>
	<option";
	if ($pathinfo['extension']=="htm") echo " selected=\"selected\"";
	echo ">htm</option>
	<option";
	if ($pathinfo['extension']=="txt") echo " selected=\"selected\"";
	echo ">txt</option>
	</select>
	<br/><br/>";
	echo "<input type=\"hidden\" readonly name=\"fnoldfilename\" value=\"$file\" />
	<input type=\"submit\" name=\"fnok\" value=\""._FNRENAMEFILE."\" />";
	echo "</form>";
}


/**
 * Funzione che rinomina un file presente all'interno delle sezioni
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_rename_file(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnoldfilename = getparam("fnoldfilename",PAR_POST,SAN_FLAT);
	$fnoldfilename = rawurldecode($fnoldfilename);
	$fnnewfilename = getparam("fnnewfilename",PAR_POST,SAN_FLAT);
	$fnnewfilename = rawurldecode($fnnewfilename);
	$fnfileext = getparam("fnfileext",PAR_POST,SAN_FLAT);
	if (!preg_match("/^html$|^htm$|^txt$/i",$fnfileext)) $fnfileext = "html";
	$fnoldfilename = preg_replace("/^sections\//","",$fnoldfilename);
	$fnfilepath = dirname($fnoldfilename);
	$fnnewfilename = preg_replace("/ /","_",$fnnewfilename);

	if (!check_path($fnfilepath,"","false")) fn_die("RENAMEFILE","\$fnfilepath is not valid!",__FILE__,__LINE__);
	if (!check_path($fnoldfilename,"","false")) fn_die("RENAMEFILE","\$fnoldfilename is not valid!",__FILE__,__LINE__);
	if (!check_path($fnnewfilename,"","false")) fn_die("RENAMEFILE","\$fnnewfilename is not valid!",__FILE__,__LINE__);
// 	print (get_fn_dir("sections")."/$fnfilepath"); die();
	if (!is_dir(get_fn_dir("sections")."/$fnfilepath")){
		echo _THEDIR." <b>$fnfilepath</b> "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a>";
		return;
	}

	if (!file_exists(get_fn_dir("sections")."/$fnoldfilename")){
		echo "<div style=\"text-align: center;\">"._THEFILE." <b>$fnoldfilename</b> "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (file_exists(get_fn_dir("sections")."/$fnfilepath/$fnnewfilename.$fnfileext")){
		echo "<div style=\"text-align: center;\">"._THEFILE." <b>$fnnewfilename.$fnfileext</b> "._ALREADYEXISTS."!";
		echo "<br><br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (rename(get_fn_dir("sections")."/$fnoldfilename",get_fn_dir("sections")."/$fnfilepath/$fnnewfilename.$fnfileext")){
		echo "<div style=\"text-align: center;\"><b>"._FNFILERENAMED."</b>";
		echo "<br><a href=\"index.php?mod=$fnfilepath&amp;file=$fnnewfilename.$fnfileext\" title=\""._GOTOFILE."\">"._GOTOFILE." <b>$fnnewfilename</b></a></div>";
		fnlog("Section manage","$addr||".get_username()."||Section ".strip_tags($fnoldfilename)." renamed in ".strip_tags($fnnewfilename));
	}
}


/**
 * Interfaccia che permette di spostare una sezione
 *
 * @param string $mod la sezione per cui mostrare l'interfaccia
 * @author Aldo Boccacci
 * @since 2.7
 */
function move_sect_interface($mod){
	if (!_FN_IS_ADMIN) die();
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	$mod = rawurldecode($mod);
	if (!check_path($mod,"","false")) fndie("\$mod is not valid!",__FILE__,__LINE__);

	echo _FNMOVESECTION.": <b>$mod</b><br><br>";
	echo _FNCHOOSEDEST.":<br><br>";

	//elenco le sezioni
	include_once("include/filesystem/DeepDir.php");

	$dir = new DeepDir();
	$dir->setDir(get_fn_dir("sections"));
	$dir->load();
	$dirs = array();
	$dirs = $dir->dirs;
	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fnmovesect\" />";
	echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"".rawurlencodepath($mod)."\" />";
	echo "<select id=\"fnsectdest\" name=\"fnsectdest\">";
	echo "<option";
	if (!is_writable(get_fn_dir("sections"))) echo " disabled=\"disabled\"";
	if (file_exists(get_fn_dir("sections")."/".basename($mod)) or is_dir(get_fn_dir("sections")."/".basename($mod))) echo " disabled=\"disabled\"";
	echo ">/</option>";
	//foreach a volte sembra dare dei problemi...
	for ($count=0;$count<count($dirs);$count++){
		$sect = $dirs[$count];
		$sect = preg_replace("/^sections\//i","",$sect);
		if (fn_is_system_dir($sect)) continue;
		if (preg_match("/\/CVS$/i",$sect) or preg_match("/^CVS$/i",$sect)) continue;
		if ($mod==$sect) continue;
		echo "<option";
		if (!is_writeable("sections/$sect")) echo " disabled=\"disabled\"";
		if (file_exists(get_fn_dir("sections")."/$sect/".basename($mod)) or is_dir(get_fn_dir("sections")."/$sect/".basename($mod))) echo " disabled=\"disabled\"";
		echo ">$sect</option>";
	}
	echo "</select><br><br>";

	echo "<input type=\"submit\" name=\"fnok\" value=\""._FNMOVESECTION."\" />";
	echo "</form>";
}


/**
 * Funzione che si occupa di spostare la sezione specificata nel form
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function move_section(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnsectpath = rawurldecode($fnsectpath);
	$fnsectdest = getparam("fnsectdest",PAR_POST,SAN_FLAT);
	$fnsectdest = rawurldecode($fnsectdest);
	if (!check_path($fnsectpath,"","false")) fn_die("MOVESECTION","\$fnsectpath is not valid!",__FILE__,__LINE__);
	if (!check_path($fnsectdest,"","false")) fn_die("MOVESECTION","\$fnsectdest is not valid!",__FILE__,__LINE__);

	if (fn_is_system_dir($fnsectpath)) fn_die("MOVESECTION","\$fnsectpath is a system dir of Flatnuke!",__FILE__,__LINE__);
	if (fn_is_system_dir($fnsectdest)) fn_die("MOVESECTION","\$fnsectdest is a system dir of Flatnuke!",__FILE__,__LINE__);

	if (!is_writable("sections/$fnsectpath")) fn_die("MOVESECTION","\$fnsectpath is not writable!",__FILE__,__LINE__);
	if (!is_writable("sections/$fnsectdest")) fn_die("MOVESECTION","\$fnsectdest is not writable!",__FILE__,__LINE__);

	if (file_exists("sections/$fnsectdest/".basename($fnsectpath))){
		if (is_file("sections/$fnsectdest/".basename($fnsectpath))) {
			echo "<div style=\"text-align: center;\">There is a file with the same name in the destination dir!";
			echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
			return;
		}
		else if (is_dir("sections/$fnsectdest/".basename($fnsectpath))) {
			echo "<div style=\"text-align: center;\">"._THEDIR." <b>sections/$fnsectdest/".basename($fnsectpath)."</b> "._ALREADYEXISTS;
			echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
			return;
		}
	}

	if (rename("sections/$fnsectpath","sections/$fnsectdest/".basename($fnsectpath))){
		echo "<div style=\"text-align: center;\"><b>"._FNSECTIONMOVED."</b><br><br>";
		echo "<a href=\"index.php?mod=".rawurlencodepath("$fnsectdest/".basename($fnsectpath))."\" title=\""._GOTOSECTION." ".basename($fnsectpath)."\">"._GOTOSECTION." ".basename($fnsectpath)."</a></div>";
		fnlog("Section manage","$addr||".get_username()."||Section $fnsectpath moved to $fnsectdest");
		if (file_exists("sections/$fnsectdest/".basename($fnsectpath)."/news"))
			save_news_sections_list(list_news_sections());
		rebuild_proposed_news_list();
	}
	else {
		echo "I was not able to move the section $fnsectpath to $fnsectdest";
		fnlog("Section manage","$addr||".get_username()."||I was not able to move the section $fnsectpath to $fnsectdest");
	}
}


/**
 * Interfaccia che permette di spostare una sezione
 *
 * @param string $file il file per cui mostrare l'interfaccia
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_move_file_interface($file){
	if (!_FN_IS_ADMIN) die();
	$file = getparam($file,PAR_NULL,SAN_FLAT);
	$file = rawurldecode($file);
	if (!check_path($file,"","true")) fn_die("MOVEFILE","\$file is not valid!",__FILE__,__LINE__);

	echo _FNMOVEFILE.": <b>$file</b><br><br>";
	echo _FNCHOOSEDEST.":<br><br>";

	//elenco le sezioni
	include_once("include/filesystem/DeepDir.php");

	$dir = new DeepDir();
	$dir->setDir(get_fn_dir("sections"));
	$dir->load();
	$dirs = array();
	$dirs = $dir->dirs;
	echo "<form action=\"index.php\" method=\"POST\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fnmovefile\" />";
	echo "<input type=\"hidden\" readonly name=\"fnfilepath\" value=\"".rawurlencodepath($file)."\" />";
	echo "<select id=\"fnsectdest\" name=\"fnsectdest\">";
	//foreach a volte sembra dare dei problemi...
	for ($count=0;$count<count($dirs);$count++){
		$sect = $dirs[$count];
		$sect = preg_replace("/^sections\//i","",$sect);
		if (fn_is_system_dir($sect)) continue;
		if (preg_match("/\/CVS$/i",$sect) or preg_match("/^CVS$/i",$sect)) continue;

		echo "<option";
		if (!is_writeable("sections/$sect")) echo " disabled=\"disabled\"";
		if (file_exists(get_fn_dir("sections")."/$sect/".basename($file)) or is_dir(get_fn_dir("sections")."/$sect/".basename($file))) echo " disabled=\"disabled\"";
		echo ">$sect</option>";
	}
	echo "</select><br><br>";
	echo "<input type=\"submit\" name=\"fnok\" value=\""._FNMOVEFILE."\" />";
	echo "</form>";
}


/**
 * Funzione che sposta un file da una sezione all'altra
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_move_file(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$fnfilepath = getparam("fnfilepath",PAR_POST,SAN_FLAT);
	$fnsectdest = getparam("fnsectdest",PAR_POST,SAN_FLAT);
	$fnfilepath = rawurldecode($fnfilepath);
	$fnsectdest = rawurldecode($fnsectdest);
	if (!check_path($fnfilepath,"","true")) fn_die("MOVEFILE","\$fnsectpath is not valid!",__FILE__,__LINE__);
	if (!check_path($fnsectdest,"","false")) fn_die("MOVESFILE","\$fnsectdest is not valid!",__FILE__,__LINE__);

	if (fn_is_system_dir(dirname($fnfilepath))) fn_die("MOVEFILE","\$fnfilepath is in a system dir of Flatnuke!",__FILE__,__LINE__);
	if (fn_is_system_dir($fnsectdest)) fn_die("MOVEFILE","\$fnsectdest is a system dir of Flatnuke!",__FILE__,__LINE__);

	if (!is_writable("sections/$fnfilepath")) fn_die("MOVEFILE","\$fnfilepath is not writable!",__FILE__,__LINE__);
	if (!is_writable("sections/$fnsectdest")) fn_die("MOVEFILE","\$fnsectdest is not writable!",__FILE__,__LINE__);

	if (file_exists("sections/$fnsectdest/".basename($fnfilepath))){
		if (is_dir("sections/$fnsectdest/".basename($fnsectpath))) {
			echo "<div style=\"text-align: center;\">There is a file with the same name in the destination dir!";
			echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
			return;
		}
		else if (is_file("sections/$fnsectdest/".basename($fnsectpath))) {
			echo "<div style=\"text-align: center;\">"._THEFILE." <b>sections/$fnsectdest/".basename($fnsectpath)."</b> "._ALREADYEXISTS;
			echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
			return;
		}
	}

	if (rename("sections/$fnfilepath","sections/$fnsectdest/".basename($fnfilepath))){
		echo "<div style=\"text-align: center;\"><b>"._FNFILEMOVED."</b><br><br>";
		echo "<a href=\"index.php?mod=".rawurlencodepath("$fnsectdest")."&amp;file=".rawurlencode(basename($fnfilepath))."\" title=\""._GOTOFILE." ".basename($fnfilepath)."\">"._GOTOFILE." ".basename($fnfilepath)."</a></div>";
		fnlog("Section manage","$addr||".get_username()."||File $fnfilepath successfully moved to $fnsectdest");
	}
	else {
		echo "I was not able to move the file $fnfilepath to $fnsectdest";
		fnlog("Section manage","$addr||".get_username()."||I was not able to move the file $fnfilepath to $fnsectdest");
	}
}


/**
 * Interfaccia che permette di confermare la volontà di dare all'utente selezionato il permesso
 * di modificare la sezione specificata nel form.
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_add_edit_perm_confirm(){
	if (!_FN_IS_ADMIN) return;
	$mod = getparam("mod",PAR_POST,SAN_FLAT);
	$user = getparam("fnadduseredit",PAR_POST,SAN_FLAT);

	if ($user=="---") {
		echo "<div align=\"center\"><b>"._FNCHOOSEUSER."</b><br><br>";
		echo "<a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!check_path($mod,"","false")) fn_die("FNADDUSERVIEWPERM","\$mod is not valid!",__FILE__,__LINE__);
	if (!check_username($user)) fn_die("FNADDUSERVIEWPERM","\$user is not valid!",__FILE__,__LINE__);

	if (!is_dir(get_fn_dir("sections")."/$mod")) fn_die("FNADDUSERVIEWPERM","\$mod is not a valid directory!",__FILE__,__LINE__);
	echo "<div align=\"center\"><b>"._ATTENTION."!</b><br><br>";
	echo _FNEDITALLOW1." <b>$user</b> "._FNEDITALLOW2." <b>$mod</b>?";
	echo "<form action=\"index.php\" method=\"post\">
	<input type=\"hidden\" name=\"mod\" value=\"".rawurlencodepath($mod)."\">
	<input type=\"hidden\" name=\"user\" value=\"$user\">
	<input type=\"hidden\" name=\"fnaction\" value=\"fnaddusereditsectperm\"><br>
	<input type=\"submit\" value=\"OK (!)\" />
	<input type=\"button\" value=\""._CANCEL."\" onclick=\"javascript:history.back();\" />
	</form>
	";
	echo "</div>";
}


/**
 * Questa funzione imposta i permessi di scrittura per la sezione indicata nel form precedente
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_add_user_edit_perm(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$mod = getparam("mod",PAR_POST,SAN_FLAT);
	$mod = rawurldecode($mod);
	$user = getparam("user",PAR_POST,SAN_FLAT);

	if ($user=="---") {
		echo "<div align=\"center\"><b>"._FNCHOOSEUSER."</b><br><br>";
		echo "<a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!check_path($mod,"","false")) fn_die("FNADDUSEREDITPERM","\$mod is not valid!",__FILE__,__LINE__);
	if (!check_username($user)) fn_die("FNADDUSEREDITPERM","\$user is not valid!",__FILE__,__LINE__);
	echo "";
	if (!is_dir(get_fn_dir("sections")."/$mod")) fn_die("FNADDUSEREDITPERM","\$mod is not a valid directory!",__FILE__,__LINE__);

	$userseditperms = array();
	$userseditperms = load_user_edit_permissions($mod);
	if (!in_array($user,$userseditperms)){
		$userseditperms[] = $user;
// 		print_r($usersviewperms);
		save_user_edit_permissions($mod,$userseditperms);
		fnlog("Section manage","$addr||".get_username()."||Added edit permission in section $mod for user $user ");

		//ora proteggi il file section.php per evitare accessi diretti alla sezione senza passare da index.php
		protect_file(get_fn_dir("sections")."/$mod/section.php");

		echo "<div align=\"center\">";
		echo  _FNUSERADDED.": <b>$user</b><br><br>";
		echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."\" title=\""._GOTOSECTION.": ".rawurlencodepath($mod)."\">"._GOTOSECTION.": <b>$mod</b></a>";
		echo "</div>";
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
	}
	else {
		return;
	}
}


/**
 * Rimuove l'utente indicato nel form dall'elenco degli utenti autorizzati a modificare la sezione
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_remove_user_edit_perm(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$mod = getparam("mod",PAR_POST,SAN_FLAT);
	$mod = rawurldecode($mod);
	$user = getparam("fnremoveuseredit",PAR_POST,SAN_FLAT);

	if ($user=="---") {
		echo "<div align=\"center\"><b>"._FNCHOOSEUSER."</b><br><br>";
		echo "<a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!check_path($mod,"","false")) fn_die("FNREMOVEUSEREDITPERM","\$mod is not valid!",__FILE__,__LINE__);
	if (!check_username($user)) fn_die("FNREMOVEUSEREDITPERM","\$user is not valid!",__FILE__,__LINE__);

	if (!is_dir(get_fn_dir("sections")."/$mod")) fn_die("FNREMOVEUSEREDITPERM","\$mod is not a valid directory!",__FILE__,__LINE__);

	$userseditperms = array();
	$userseditperms = load_user_edit_permissions($mod);
	if (in_array($user,$userseditperms)){
		$newperms=array();
		for ($count=0;$count<count($userseditperms);$count++){
			if ($userseditperms[$count]==$user) continue;
			$newperms[]=$userseditperms[$count];
		}

// 		print_r($usersviewperms);
		save_user_edit_permissions($mod,$newperms);
		fnlog("Section manage","$addr||".get_username()."||Removed permission in section $mod for user $user ");
		echo "<div align=\"center\">";
		echo  _FNUSERREMOVED.": <b>$user</b><br><br>";
		echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."\" title=\""._GOTOSECTION.": ".rawurlencodepath($mod)."\">"._GOTOSECTION.": <b>$mod</b></a>";
		echo "</div>";
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
	}
	else {
		return;
	}
}


/**
 * Aggiunge l'utente specificato nel form alla lista degli utenti autorizzati a visualizzare la sezione
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_add_user_view_perm(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$mod = getparam("mod",PAR_POST,SAN_FLAT);
	$mod = rawurldecode($mod);
	$user = getparam("fnadduser",PAR_POST,SAN_FLAT);

	if ($user=="---") {
		echo "<div align=\"center\"><b>"._FNCHOOSEUSER."</b><br><br>";
		echo "<a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!check_path($mod,"","false")) fn_die("FNADDUSERVIEWPERM","\$mod is not valid!",__FILE__,__LINE__);
	if (!check_username($user)) fn_die("FNADDUSERVIEWPERM","\$user is not valid!",__FILE__,__LINE__);

	if (!is_dir(get_fn_dir("sections")."/$mod")) fn_die("FNADDUSERVIEWPERM","\$mod is not a valid directory!",__FILE__,__LINE__);

	$usersviewperms = array();
	$usersviewperms = load_user_view_permissions($mod);
	if (!in_array($user,$usersviewperms)){
		$usersviewperms[] = $user;
// 		print_r($usersviewperms);
		save_user_view_permissions($mod,$usersviewperms);
		fnlog("Section manage","$addr||".get_username()."||Added view permission in section $mod for user $user ");

		//ora proteggi il file section.php per evitare accessi diretti alla sezione senza passare da index.php
		protect_file(get_fn_dir("sections")."/$mod/section.php");

		echo "<div align=\"center\">";
		echo  _FNUSERADDED.": <b>$user</b><br><br>";
		echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."\" title=\""._GOTOSECTION.": ".rawurlencodepath($mod)."\">"._GOTOSECTION.": <b>$mod</b></a>";
		echo "</div>";
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
	}
	else {
		return;
	}
}


/**
 * Rimuove l'utente indicato nel form dall'elenco degli utenti autorizzati a visualizzare la sezione
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_remove_user_view_perm(){
	if (!_FN_IS_ADMIN) return;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	$mod = getparam("mod",PAR_POST,SAN_FLAT);
	$mod = rawurldecode($mod);
	$user = getparam("fnremoveuser",PAR_POST,SAN_FLAT);

	if ($user=="---") {
		echo "<div align=\"center\"><b>"._FNCHOOSEUSER."</b><br><br>";
		echo "<a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!check_path($mod,"","false")) fn_die("FNREMOVEUSERVIEWPERM","\$mod is not valid!",__FILE__,__LINE__);
	if (!check_username($user)) fn_die("FNREMOVEUSERVIEWPERM","\$user is not valid!",__FILE__,__LINE__);

	if (!is_dir(get_fn_dir("sections")."/$mod")) fn_die("FNREMOVEUSERVIEWPERM","\$mod is not a valid directory!",__FILE__,__LINE__);

	$usersviewperms = array();
	$usersviewperms = load_user_view_permissions($mod);
	if (in_array($user,$usersviewperms)){
		$newperms=array();
		for ($count=0;$count<count($usersviewperms);$count++){
			if ($usersviewperms[$count]==$user) continue;
			$newperms[]=$usersviewperms[$count];
		}

// 		print_r($usersviewperms);
		save_user_view_permissions($mod,$newperms);
		fnlog("Section manage","$addr||".get_username()."||Removed view permission in section $mod for user $user ");
		echo "<div align=\"center\">";
		echo  _FNUSERREMOVED.": <b>$user</b><br><br>";
		echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."\" title=\""._GOTOSECTION.": ".rawurlencodepath($mod)."\">"._GOTOSECTION.": <b>$mod</b></a>";
		echo "</div>";
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
	}
	else {
		return;
	}
}


/**
 * Maschera che permette di cambiare il tipo di sezione specificata
 *
 * @param string $mod il $mod della sezione di cui modificare il tipo
 * @author Aldo Boccacci
 * @since 2.7
 */
function choose_sect_type_interface($mod){
	if (!_FN_IS_ADMIN) die();
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	$mod = rawurldecode($mod);
	if (!check_path($mod,"","false")) fndie("\$mod is not valid!",__FILE__,__LINE__);

	if (!is_dir(get_fn_dir("sections")."/$mod")){
		echo _THEDIR." <b>".strip_tags($mod)."</b> "._DOESNTEXISTS."!";
		return;
	}
	$modshow = preg_replace("/\/\//s","/",$mod);
	$modshow = preg_replace("/^\//s","",$modshow);
	echo "<b>".strip_tags($modshow)."</b>: cambia il tipo di sezione<br><br>";
	echo "<form action=\"index.php\" method=\"post\">";
	echo "<input type=\"hidden\" readonly name=\"fnaction\" value=\"fnchangesecttype\" />";
	echo "<input type=\"hidden\" readonly name=\"fnsectpath\" value=\"".rawurlencodepath($mod)."\" />";
	echo "<select id=\"fnsecttype\" name=\"fnsecttype\">
		<option value=\"standard\">Standard</option>
		<option value=\"download\" title=\""._DOWNLOADMAINTITLE."\"";
		if (file_exists(get_fn_dir("sections")."/$mod/download"))
			echo " selected=\"selected\"";
		echo ">"._DOWNLOADMAIN."</option>
		<option value=\"downloadsection\" title=\""._DOWNLOADSINGLETITLE."\"";
		//fine funzione create_social_linksif (file_exists(get_fn_dir("sections")."/$mod/downloadsection"))
			echo " selected=\"selected\"";
		echo ">"._DOWNLOADSINGLE."</option>
		<option value=\"forum\"";
		if (file_exists(get_fn_dir("sections")."/$mod/forum"))
			echo " selected=\"selected\"";
		echo ">Forum</option>
		<option value=\"gallery\"";
		if (file_exists(get_fn_dir("sections")."/$mod/gallery"))
			echo " selected=\"selected\"";
		echo ">Gallery</option>
		<option value=\"news\"";
		if (file_exists(get_fn_dir("sections")."/$mod/news"))
			echo " selected=\"selected\"";
		echo ">News</option>";
	echo "</select><br><br>";
	echo "<input type=\"submit\" name=\"fnok\" value=\"OK\" />";
	echo "</form>";
}


/**
 * Modifica il tipo di sezione
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function change_section_type(){
	if (!_FN_IS_ADMIN) die();

	$fnsectpath = getparam("fnsectpath",PAR_POST,SAN_FLAT);
	$fnsectpath = rawurldecode($fnsectpath);
	if (!check_path($fnsectpath,"","false")) fn_die("CHANGESECTTYPE","The section's name is not valid! (".strip_tags($fnsectpath).")",__FILE__,__LINE__);

	$secttype = getparam("fnsecttype",PAR_POST,SAN_FLAT);
	if (!preg_match("/^standard$|^download$|^downloadsection$|^forum$|^gallery$|^news$/i",$secttype)) fn_die("CHANGESECTTYPE","\$secttype is not valid!",__FILE__,__LINE__);

	if (!file_exists(get_fn_dir("sections")."/$fnsectpath")){
		echo "<div style=\"text-align:center;\"><b>"._ATTENTION."</b>: "._THEDIR." ".strip_tags($fnsectpath)." "._DOESNTEXISTS."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!is_dir(get_fn_dir("sections")."/$fnsectpath")){
		echo "<div style=\"text-align:center;\"><b>"._ATTENTION."</b>: ".strip_tags($fnsectpath)." "._ISNOTADIR."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}

	if (!is_writable(get_fn_dir("sections")."/$fnsectpath")){
		echo "<div style=\"text-align:center;\"><b>"._ATTENTION."</b>: "._THEDIR." ".strip_tags($fnsectpath)." "._NOTWRITABLE."!";
		echo "<br><a href=\"javascript:history.back();\">&lt;&lt; "._INDIETRO."</a></div>";
		return;
	}
	//gestisce lui la scrittura e gli errori
	set_section_type($fnsectpath,$secttype);
	rebuild_proposed_news_list();
	$fnsectpathshow = preg_replace("/\/\//s","/",$fnsectpath);
	$fnsectpathshow = preg_replace("/^\//s","",$fnsectpathshow);
	echo "<br><div style=\"text-align:center;\"><a href=\"index.php?mod=".
		rawurlencodepath($fnsectpath)."\" title=\""._GOTOSECTION.": ".
		rawurlencodepath($fnsectpath)."\">"._GOTOSECTION." <b>$fnsectpathshow</b></a></div>";
}


/**
 * Questa funzione mostra il pannello per amministrare la sezione corrente
 * (solo amministratori)
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function section_admin_panel(){
	if (!_FN_IS_ADMIN) return;
	$mod = _FN_MOD;
	$file = _FN_FILE;
	//if ($mod=="") return;
	global $home_section;
	if ($mod=="") $mod = $home_section;

	if (!check_path($mod,"","false")) return;
	if (!check_path($file,"","true") and $file !="") return;
	$urlencodedmod = rawurlencodepath($mod);
	$urlencodedfile = rawurlencodepath($file);
	echo "<input type=\"button\" value=\""._MANAGESECTION."\" onclick=\"ShowHideDiv('sectadminpanel');\" />";
	echo "<div id=\"sectadminpanel\" style=\"display:none;\" >";
	echo "<fieldset title=\""._FNMANAGESECTTITLE."\"><legend><b>"._MANAGESECTION."</b></legend>";
	echo "<i>"._MANAGESECTNOTE."</i><br><br>";

	if ($file==""){
		if(is_writable("sections/$mod/section.php") and !fn_is_system_dir($mod)){
			print "<div style='float:left;padding:2px;'><form action='index.php' method='get'>
			<input type='hidden' name='mod' value='modcont' />
			<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
			<input type='hidden' name='file' value='sections/$urlencodedmod/section.php' />
			<input type='submit' value='"._FNEDITSECTION."' title=\""._FNEDITSECTION."\" />";

			//SCELTA DELL'EDITOR
			if ((file_exists("include/plugins/editors/FCKeditor/fckeditor.php") or file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js") or file_exists("include/plugins/editors/ckeditor/ckeditor.php")) and file_exists("sections/$mod/section.php") and is_writable("sections/$mod/section.php")){
				echo "&nbsp;<select name=\"fneditor\" title=\""._FNCHOOSEEDITOR."\">";

				$checkstring = get_file("sections/$mod/section.php");
				$thereisphp = preg_match("/\<\?/",get_unprotected_text($checkstring));

				if (file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
					if (!$thereisphp)
						echo "<option value=\"fckeditor\" title=\""._EDITFCKEDITOR."\" selected=\"selected\">FCKeditor</option>";
					else echo "<option value=\"fckeditor\" disabled=\"disabled\">FCKeditor</option>";
				}

				if (file_exists("include/plugins/editors/ckeditor/ckeditor.php")){
					if (!$thereisphp)
						echo "<option value=\"ckeditor\" title=\""._EDITFCKEDITOR."\" >ckeditor</option>";
					else echo "<option value=\"ckeditor\" disabled=\"disabled\">ckeditor</option>";
				}

				if (file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")){
					if (!$thereisphp)
						echo "<option value=\"tinymce\">Tinymce</option>";
					else echo "<option value=\"tinymce\" disabled=\"disabled\">Tinymce</option>";
				}
				echo "<option value=\"html\" title=\""._EDITHTML."\">html</option>";
				echo "</select>";
			}
			echo "</form></div>";
		}
	}
	else if ($file!=""){
		if(is_writable(_FN_SECTIONS_DIR."/$mod/$file") and !fn_is_system_dir($mod)){
			echo "<div style='float:left;padding:2px;'><form action='index.php' method='get'>
			<input type='hidden' name='mod' value='modcont' />
			<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
			<input type='hidden' name='file' value='"._FN_SECTIONS_DIR."/$urlencodedmod/$urlencodedfile' />
			<input type='submit' value='"._MODIFICA."' title=\""._FNEDITFILE."\" />";

			//SCELTA DELL'EDITOR
			if ((file_exists("include/plugins/editors/FCKeditor/fckeditor.php")
				or file_exists("include/plugins/editors/ckeditor/ckeditor.php")
				or file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")) and file_exists("sections/$mod/$file") and is_writable("sections/$mod/$file")){
				echo "&nbsp;<select name=\"fneditor\" title=\""._FNCHOOSEEDITOR."\">";
				$checkstring = get_file("sections/$mod/$file");
				$thereisphp = preg_match("/\<\?/",get_unprotected_text($checkstring));

				if (file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
					if (!$thereisphp)
						echo "<option value=\"fckeditor\" title=\""._EDITFCKEDITOR."\" selected=\"selected\">FCKeditor</option>";
					else echo "<option value=\"fckeditor\" disabled=\"disabled\">FCKeditor</option>";
				}

				if (file_exists("include/plugins/editors/ckeditor/ckeditor.php")){
					if (!$thereisphp)
						echo "<option value=\"ckeditor\" title=\""._EDITFCKEDITOR."\">ckeditor</option>";
					else echo "<option value=\"ckeditor\" disabled=\"disabled\">ckeditor</option>";
				}

				if (file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")){
					if (!$thereisphp)
						echo "<option value=\"tinymce\">Tinymce</option>";
					else echo "<option value=\"tinymce\" disabled=\"disabled\">Tinymce</option>";
				}
				//editor html
				echo "<option value=\"html\" title=\""._EDITHTML."\">html</option>";
				echo "</select>";
			}
			echo "</form></div>";
		}
	}

	echo "<form action=\"index.php\" method=\"get\" style=\"float:right;padding:4px;\">";
	echo "<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />";
	echo "<input type='hidden' name='sect' value='".rawurlencodepath($mod)."' />";
	echo "<input type='hidden' name='fnfile' value='$urlencodedmod/$urlencodedfile' />";
	echo "<select id=\"mod\" name=\"mod\" >";
	echo "<option disabled=\"disabled\" selected=\"selected\" >"._CHOOSEACTION."</option>";
	if (!fn_is_system_dir($mod) and trim($file)==""){
		echo "<option value=\"fnnewsectinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._FNCREATESUBSECTION."</option>";

		echo "<option value=\"fndeletesectinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._FNDELETESECTION."</option>";

		echo "<option value=\"fnrenamesectinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._FNRENAMESECTION."</option>";

		echo "<option value=\"fnmovesectinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._FNMOVESECTION."</option>";

		echo "<option value=\"fnchoosesecttypeinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._CHANGESECTTYPE."</option>";

		echo "<option disabled=\"disabled\">---</option>";

		echo "<option value=\"fnnewfileinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._FNCREATEFILE." (html/txt)</option>";
	}

	if (!fn_is_system_dir($mod) and trim($file)!=""){
		echo "<option value=\"fnnewfileinterface\"";
		if (!is_writable("sections/$mod")) echo " disabled=\"disabled\"";
		echo ">"._FNCREATEFILE."</option>";

		echo "<option value=\"fnrenamefileinterface\"";
		if (!is_writable("sections/$mod/$file")) echo " disabled=\"disabled\"";
		echo ">"._FNRENAMEFILE."</option>";

		echo "<option value=\"fndeletefileinterface\"";
		if (!is_writable("sections/$mod/$file")) echo " disabled=\"disabled\"";
		echo ">"._FNDELETEFILE."</option>";

		echo "<option value=\"fnmovefileinterface\"";
		if (!is_writable("sections/$mod/$file")) echo " disabled=\"disabled\"";
		echo ">"._FNMOVEFILE."</option>";
	}
	echo "</select>";
	echo "&nbsp;<input type=\"submit\" value=\""._EXEC."\" />";
	echo "</form></fieldset>";

	//------------------------------------------------------
	//PERMESSI VISIONE SEZIONI
	//------------------------------------------------------
	if (!fn_is_system_dir($mod)){
		echo "<fieldset title=\""._FNMANAGEVIEWPERMSTITLE."\"><legend><b>"._FNVIEWPERMS."</b></legend>";
		//stampa elenco utenti abilitati alla visione
		$usersperms = load_user_view_permissions($mod);
		if (count($usersperms)>0){
			echo "<b>"._USERS."</b>: ";
			for ($count=0;$count<count($usersperms);$count++){
				if ($count!=0) echo ", ";
				echo "<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$usersperms[$count]."\" title=\""._VIEW_USERPROFILE."\">".$usersperms[$count]."</a>";
			}
			echo "<br><br>";
		}

		//aggiungi/togli utenti
		echo "<form action='index.php' method='post'>
		<input type='hidden' name='fnaction' value='fnaddusersectperm' />
		<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
		<input type='hidden' name='mod' value='$urlencodedmod' />";
		echo "<input type='submit' value='"._FNADDUSER."' title=\""._FNADDUSER."\"/>&nbsp;";
		$allusers = array();
		$allusers = list_users();
		echo "<select id=\"fnadduser\" name=\"fnadduser\">";
		echo "<option>---</option>";
		for ($count=0;$count<count($allusers);$count++){
			if (getlevel($allusers[$count],"home")=="10") continue;
			if (in_array($allusers[$count],$usersperms)) continue;
			echo "<option>".$allusers[$count]."</option>";
		}
		echo "</select>";
		echo "</form><br>";

		echo "<form action='index.php' method='post'>
		<input type='hidden' name='fnaction' value='fnremoveusersectperm' />
		<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
		<input type='hidden' name='mod' value='$urlencodedmod' />";
		echo "<input type='submit' value='"._FNREMOVEUSER."' title=\""._FNREMOVEUSER."\"";
		if (count($usersperms)==0) echo " disabled=\"disabled\" ";
		echo "/>&nbsp;";
		echo "<select id=\"fnremoveuser\" name=\"fnremoveuser\"";
		if (count($usersperms)==0) echo " disabled=\"disabled\" ";
		echo ">";
		echo "<option>---</option>";
		for ($count=0;$count<count($usersperms);$count++){
			echo "<option>".$usersperms[$count]."</option>";
		}
		echo "</select>";
		echo "</form><br>";

		// manage section's level
		$level = getsectlevel($mod);
		print "<div style='float:left;'><form action='verify.php' method='post'>";
		print "<input type='hidden' name='mod' value='modlevel' />";
		print "<input type='hidden' name='section' value='$urlencodedmod' />";
		print "<b>"._LEVEL."</b>: ";
		if(!is_writeable("sections/$mod")) {
			print "<select name='level' disabled></select> "._FIG_ALERTNOTWR;
			print "</form></div>";
		} else {
			print "<select name='level'>";
			print "<option value='-1'>---</option>";
			for($i=0;$i<11;$i++){
				if($level==$i)
					print "<option value='$i' selected='selected'>$i</option>";
				else
					print "<option value='$i' >$i</option>";
			}
			print "</select>";
			print "&nbsp;<input type='submit' value='OK' />";
			print "</form></div>";
		}
		echo "</fieldset>";
	}


	//------------------------------------------------------
	//SCRITTURA SEZIONI
	//------------------------------------------------------
	if (!fn_is_system_dir($mod)){
		echo "<fieldset title=\""._FNMANAGEEDITPERMSTITLE."\"><legend><b>"._FNEDITPERMS."</b></legend>";
		//stampa elenco utenti abilitati alla visione
		$usersperms = load_user_edit_permissions($mod);
		if (count($usersperms)>0){
			echo "<b>"._USERS."</b>: ";
			for ($count=0;$count<count($usersperms);$count++){
				if ($count!=0) echo ", ";
				echo "<a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$usersperms[$count]."\" title=\""._VIEW_USERPROFILE."\">".$usersperms[$count]."</a>";
			}
			echo "<br><br>";
		}

		//aggiungi/togli utenti
		echo "<form action='index.php' method='post'>
		<input type='hidden' name='fnaction' value='fnaddusereditsectpermconfirm' />
		<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
		<input type='hidden' name='mod' value='$urlencodedmod' />";
		echo "<input type='submit' value='"._FNADDUSER."' title=\""._FNADDUSER."\"/>&nbsp;";
		$allusers = array();
		$allusers = list_users();
		echo "<select id=\"fnadduseredit\" name=\"fnadduseredit\">";
		echo "<option>---</option>";
		for ($count=0;$count<count($allusers);$count++){
			if (getlevel($allusers[$count],"home")=="10") continue;
			if (!user_can_view_section($mod,$allusers[$count])) continue;
			if (in_array($allusers[$count],$usersperms)) continue;
			echo "<option>".$allusers[$count]."</option>";
		}
		echo "</select>";
		echo "</form><br>";

		echo "<form action='index.php' method='post'>
		<input type='hidden' name='fnaction' value='fnremoveusereditsectperm' />
		<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
		<input type='hidden' name='mod' value='$urlencodedmod' />";
		echo "<input type='submit' value='"._FNREMOVEUSER."' title=\""._FNREMOVEUSER."\"";
		if (count($usersperms)==0) echo " disabled=\"disabled\" ";
		echo "/>&nbsp;";
		echo "<select id=\"fnremoveuseredit\" name=\"fnremoveuseredit\"";
		if (count($usersperms)==0) echo " disabled=\"disabled\" ";
		echo ">";
		echo "<option>---</option>";
		for ($count=0;$count<count($usersperms);$count++){
			echo "<option>".$usersperms[$count]."</option>";
		}
		echo "</select>";
		echo "</form><br>";
		echo "</fieldset>";
	}

	//div finale
	echo "</div>";
}


/**
 * Questa funzione mostra il pulsante modifica se l'utente è abilitato a modificare la sezione corrente
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function section_user_edit_panel(){
	if (!is_user()) return;
	$mod = _FN_MOD;
	$file = get_file_var();

	if (!user_can_view_section($mod,get_username())) return;
	if (!user_can_edit_section($mod,get_username())) return;

	if ($file==""){
		if(is_writable("sections/$mod/section.php") and !fn_is_system_dir($mod)){
			print "<div style='float:left;padding:2px;'><form action='index.php' method='get'>
			<input type='hidden' name='mod' value='usermodcont' />
			<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
			<input type='hidden' name='file' value='sections/$mod/section.php' />
			<input type='submit' value='"._FNEDITSECTION."' title=\""._FNEDITSECTION."\" ";
			$text = get_unprotected_text(get_file("sections/$mod/section.php"));
			if (preg_match("/\<\?|\?\>/",$text)) echo " disabled = disabled ";
			echo "/>";

			//SCELTA DELL'EDITOR
			if ((file_exists("include/plugins/editors/FCKeditor/fckeditor.php") or file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")) and file_exists("sections/$mod/section.php") and is_writable("sections/$mod/section.php")){
				echo "&nbsp;<select name=\"fneditor\" title=\""._FNCHOOSEEDITOR."\">";
				echo "<option value=\"html\" title=\""._EDITHTML."\">html</option>";
				$checkstring = get_file("sections/$mod/section.php");
				$thereisphp = preg_match("/\<\?/",get_unprotected_text($checkstring));

				if (file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
					if (!$thereisphp)
						echo "<option value=\"fckeditor\" title=\""._EDITFCKEDITOR."\">FCKeditor</option>";
					else echo "<option value=\"fckeditor\" disabled=\"disabled\">FCKeditor</option>";
				}

				if (file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")){
					if (!$thereisphp)
						echo "<option value=\"tinymce\">Tinymce</option>";
					else echo "<option value=\"tinymce\" disabled=\"disabled\">Tinymce</option>";
				}
				echo "</select>";
			}
			echo "</form></div>";
		}
	}
	else if ($file!=""){
		if(is_writable("sections/$mod/$file") and !fn_is_system_dir($mod)){
			print "<div style='float:left;padding:2px;'><form action='index.php' method='get'>
			<input type='hidden' name='mod' value='usermodcont' />
			<input type='hidden' name='from' value='".str_replace("&","&amp;",getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT))."' />
			<input type='hidden' name='file' value='sections/$mod/$file' />
			<input type='submit' value='"._MODIFICA."' title=\""._FNEDITFILE."\" ";
			$text = get_unprotected_text(get_file("sections/$mod/section.php"));
			if (preg_match("/\<\?|\?\>/",$text)) echo " disabled = disabled ";
			echo "/>";

			//SCELTA DELL'EDITOR
			if ((file_exists("include/plugins/editors/FCKeditor/fckeditor.php") or file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")) and file_exists("sections/$mod/$file") and is_writable("sections/$mod/$file")){
				echo "&nbsp;<select name=\"fneditor\" title=\""._FNCHOOSEEDITOR."\">";
				echo "<option value=\"html\" title=\""._EDITHTML."\">html</option>";
				$checkstring = get_file("sections/$mod/$file");
				$thereisphp = preg_match("/\<\?/",get_unprotected_text($checkstring));

				if (file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
					if (!$thereisphp)
						echo "<option value=\"fckeditor\" title=\""._EDITFCKEDITOR."\">FCKeditor</option>";
					else echo "<option value=\"fckeditor\" disabled=\"disabled\">FCKeditor</option>";
				}

				if (file_exists("include/plugins/editors/tiny_mce/tiny_mce_gzip.js")){
					if (!$thereisphp)
						echo "<option value=\"tinymce\">Tinymce</option>";
					else echo "<option value=\"tinymce\" disabled=\"disabled\">Tinymce</option>";
				}
				echo "</select>";
			}
			echo "</form></div>";
		}
	}
}


/**
 * Questa funzione restituisce TRUE se il $mod passato come parametro è una sezione protetta del sistema
 * che non deve essere rinominata/eliminata/spostata/modificata
 *
 * @param string $mod il $mod della sezione da verificare
 * @return TRUE se il $mod appartiene a una sezione di sistema, FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_is_system_dir($mod){
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	if (!check_path($mod,"","false")) return TRUE;
	$mod = preg_replace("/^\//","",$mod);
	$mod = preg_replace("/\/$/","",$mod);
	if (preg_match("/^none_Login$|^none_Admin$|^none_Admin\/none_.*$|^none_Calendar$|^none_Search$|^none_Fdplus$|^none_Images$|^none_News$|none_newsdata|^none_Email$/i",$mod)){
		return TRUE;
	}
	else return FALSE;
}


/**
 * Questa funzione inserisce nelle sezioni il codice php
 * adeguato ad impedire che i file vengano visualizzati direttamente,
 * scavalcando i meccanismi di protezione di flatnuke
 *
 * @param string $path il percorso del file da proteggere
 * @author Aldo Boccacci
 * @since 2.7
 */
function protect_file($path){
	if (!check_path($path,get_fn_dir("sections"),"true")) return;
	if (!is_file($path)) return;
	if (!preg_match("/\.php$/i",$path)) return;
	$text = get_file($path);
	$string = '<?php
	if (preg_match("/'.basename($path).'/",$_SERVER[\'PHP_SELF\'])) die();
?>';
	//solo se il file non contiene già il codice di controllo...lo aggiungo!
	if (!preg_match("/\<\?.*preg_match\(.*die().*\?\>/",$text)){
		fnwrite($path, $string.$text,"w",array("nonull"));
	}
}


/**
 * Restituisce la stringa passata come parametro dopo aver rimosso
 * il codice di protezione inserito dalla funzione protect_file($path)
 * Funzione creata da Aldo Boccacci
 *
 * @param string $test_protected il testo protetto
 * @return il testo senza la protezione iniziale
 * @author Aldo Boccacci
 * @since 2.7
 */
function get_unprotected_text($text_protected){
	$text_unprotected = preg_replace("/\<\?php\n\tif \(preg_match\(.*die\(\);\n\?\>/i","",$text_protected);
	return $text_unprotected;
}


/**
 * Purifica la stringa passata e la restituisce una volta eliminati tutti i codici non esplicitamente permessi
 *
 * @param string $text il testo da purificare
 * @param string $mode la modalità di purificazione:
 * 		 admin: se l'utente è admin (attualmente non usata)
 * 		 user: è permesso un numero minore di tag html
 * @author Aldo Boccacci
 * @since 2.7
 */
function fn_purge_html_string($text,$mode="user"){
	$text = getparam($text,PAR_NULL,SAN_NULL);
	$mode = getparam($mode,PAR_NULL,SAN_FLAT);
	if (!preg_match("/^admin$|^user$/i",$mode)) return;

	$userallowed = array('a' => array ('href' => array (), 'title' => array (), 'rel' => array (), 'rev' => array (), 'name' => array ()),'b' => array (), 'big' => array (), 'blockquote' => array ('cite' => array ()), 'br' => array (),'div' => array ('align' => array (),'style' => array ()),'h1' => array ('align' => array ()), 'h2' => array ('align' => array ()), 'h3' => array ('align' => array ()), 'h4' => array ('align' => array ()), 'h5' => array ('align' => array ()), 'h6' => array ('align' => array ()), 'hr' => array ('align' => array (), 'noshade' => array (), 'size' => array (), 'width' => array ()), 'i' => array (),'p' => array ('align' => array ()), 'pre' => array ('width' => array ()),'strike' => array (), 'strong' => array (),'u' => array (),'ul' => array (), 'ol' => array (),'li' =>array());
	//'img'=>array('src'=>array(),'alt'=>array(),'style'=>array(),'width'=>array(),'height'=>array()),
	if (file_exists("include/php_filters/kses.php")){
		include_once("include/php_filters/kses.php");
	return kses(stripslashes($text), $userallowed);
	}
	else return strip_tags($text,"br");
}


/**
 * Imposta il tipo di sezione da visualizzare
 *
 * @param string $mod il mod della sezione da impostare
 * @param string $type il tipo di sezione. Può essere: standard, download, downloadsection, forum, gallery
 *
 * @author Aldo Boccacci
 * @since 2.7
 */
function set_section_type($mod,$type){
	$mod = getparam($mod,PAR_NULL,SAN_FLAT);
	if (!check_path($mod,"","false")) return FALSE;
	$type = getparam($type,PAR_NULL,SAN_FLAT);
	if (!preg_match("/^standard$|^forum$|^download$|^downloadsection$|^gallery$|^news$/i",$type)) return FALSE;
	$addr=getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
	//prima devo azzerare la sezione
	@unlink("sections/$mod/forum");
	@unlink("sections/$mod/download");
	@unlink("sections/$mod/downloadsection");
	@unlink("sections/$mod/gallery");
	@unlink("sections/$mod/news");

	if ($type=="standard"){
		//ho già rimosso tutto più sopra
		fnlog("Section manage","$addr||".get_username()."||The section ".strip_tags("$mod")." now is standard");
	}
	else if ($type=="forum"){
		fnwrite("sections/$mod/forum"," ","w",array());
		fnlog("Section manage","$addr||".get_username()."||Created forum in section ".strip_tags("$mod"));
	}
	else if ($type=="gallery"){
		fnwrite("sections/$mod/gallery"," ","w",array());
		fnlog("Section manage","$addr||".get_username()."||Created gallery in section ".strip_tags("$mod"));
	}
	else if ($type=="download"){
		fnwrite("sections/$mod/download"," ","w",array());
		fnlog("Section manage","$addr||".get_username()."||Created main download section in section ".strip_tags("$mod"));
	}
	else if ($type=="downloadsection"){
		fnwrite("sections/$mod/downloadsection"," ","w",array());
		fnlog("Section manage","$addr||".get_username()."||Created single download section in section ".strip_tags("$mod"));
	}
	else if ($type=="news"){
		include_once("flatnews/include/news_functions.php");
		fnwrite("sections/$mod/news"," ","w",array());
		//lo imposto alla fine della funzione
// 		add_section_in_news_section_list($mod);
		fnlog("Section manage","$addr||".get_username()."||Created news section in section ".strip_tags("$mod"));
	}
	//in ogni caso per sicurezza rigenero la lista delle sezioni news
	save_news_sections_list(list_news_sections());
}


/**
 * XML-RPC check on Flatnuke version - Client
 *
 * This code builds the XML-RPC client that can call a
 * remote server to know (almost) everything about the
 * version it's in use, even getting some useful hints
 * on actions to perform.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 3.0
 *
 * @return string Return code [NULL|UNKNOWN|WARNING|OLD|CURRENT|DEVEL]
 */
function fn_chkversion_client() {
	// include XML-RPC protocol
	include_once("include/xmlrpc/IXR.php");
	// build XML-RPC client
	$client = new IXR_Client('http://flatnuke.sourceforge.net/fn_chkversion_server.php');
	// call the server and tell it which version you're using
	if (!$client->query('flatnuke.getFnVersion', get_fn_version())) {
		$addr = getparam("REMOTE_ADDR", PAR_SERVER, SAN_FLAT);
		//fnlog("Check Flatnuke updates", $addr."||".get_username()."||An error occurred (".$client->getErrorCode()."): ".$client->getErrorMessage()); //-> DEBUG
		return "NULL";
	}
	return $client->getResponse();
}

/**
 * Crea una textarea con i parametri passati
 *
 * @param string $type il tipo di textarea da creare
 * @param string $text il testo da includere nella textarea
 * @param array $options le varie opzioni per la creazione della textarea
 * @author Aldo Boccacci
 * @since 3.0
 */
function fn_textarea($type,$text,$options){
	global $lang;
	$type = getparam($type,PAR_NULL,SAN_FLAT);
	$text = getparam($text,PAR_NULL,SAN_FLAT);

	if (!preg_match("/^html$|^fckeditor$|^ckeditor$/i",$type))
		$type = "html";

	$id = trim(strip_tags(getparam($options['id'],PAR_NULL,SAN_FLAT)));
	$name = trim(strip_tags(getparam($options['name'],PAR_NULL,SAN_FLAT)));
	$rows = trim(strip_tags(getparam($options['rows'],PAR_NULL,SAN_FLAT)));
	$cols = trim(strip_tags(getparam($options['cols'],PAR_NULL,SAN_FLAT)));
	$style = trim(strip_tags(getparam($options['style'],PAR_NULL,SAN_FLAT)));
	$allow_php = trim(strip_tags(getparam($options['allow_php'],PAR_NULL,SAN_FLAT)));
	$allow_html = trim(strip_tags(getparam($options['allow_html'],PAR_NULL,SAN_FLAT)));

	if ($allow_php!=TRUE) $allow_php = FALSE;
	if ($allow_html!=TRUE) $allow_html = FALSE;

	//FCKEditor options
	$BasePath = strip_tags(getparam($options['BasePath'],PAR_NULL,SAN_FLAT));
	$Value = strip_tags(getparam($options['Value'],PAR_NULL,SAN_FLAT));
	$Width = strip_tags(getparam($options['Width'],PAR_NULL,SAN_FLAT));
	$Height = strip_tags(getparam($options['Height'],PAR_NULL,SAN_FLAT));
	$ToolbarSet = strip_tags(getparam($options['ToolbarSet'],PAR_NULL,SAN_FLAT));


	if ($type=="ckeditor" and file_exists("include/plugins/editors/ckeditor/ckeditor.php")){
		if ($allow_php!=TRUE) $text = strip_tags($text);
		include_once("include/plugins/editors/ckeditor/ckeditor.php");
		$CKEditor = new CKEditor();
		$CKEditor->basePath = $BasePath;
		$config['width'] = $Width;
		$CKEditor->config['height'] = $Height;
		$config['language'] = $lang;
		$CKEditor->config['enterMode'] = 'CKEDITOR.ENTER_BR';
		$CKEditor->config['shiftEnterMode'] = 'CKEDITOR.ENTER_P';
		$config['toolbar'] = array(
			array( 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ),
			array( 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo'),
			array( 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt'),
// 			Non metto i form perché tendenzialmente non utilizzati in Flatnuke
// 			array('Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton','HiddenField'),
// 			array(	'/'),
			array('Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat'),
			array('NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
				'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl'),
			array('Link','Unlink','Anchor'),
			array('Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'),
// 			array('/'),
			array('TextColor','BGColor'),
			array('Maximize', 'ShowBlocks','-','About'),
			array( 'Image', 'Link', 'Unlink', 'Anchor' ),
			array('Styles','Format','Font','FontSize')
		);
		$CKEditor->editor($id, $text,$config);

	}
	else if ($type=="fckeditor"){
include_once("include/plugins/editors/FCKeditor/fckeditor.php");
			$oFCKeditor = new FCKeditor($id) ;
			$oFCKeditor->BasePath	= $BasePath ;
			$oFCKeditor->Value= $text;
			$oFCKeditor->Width=$Width;
			$oFCKeditor->Height=$Height;
			$oFCKeditor->ToolbarSet= $ToolbarSet;
			$oFCKeditor->Create() ;
	}
	else {
		echo "<textarea";
		if ($name!="") echo " name=\"$name\"";
		if ($id!="") echo " id=\"$id\"";
		if ($rows!="") echo " rows=\"$rows\"";
		if ($cols!="") echo " cols=\"$cols\"";
		if ($style!="") echo " style=\"$style\"";
		echo ">";
		if ($allow_php!=TRUE)
			$text = preg_replace("/\<\?.*\?\>/","",$text);
		if ($allow_html!=TRUE)
			$text = strip_tags($text);
		echo $text;
		echo "</textarea>";
	}
}

/**
 * Se magic_quotes_gpc è settato a On restituisce la stringa
 * passata con stripslashes()
 *
 * @author Aldo Boccacci
 * @since 3.0
 * @return la stringa passata con stripslashes se magic_quotes_gpc è settato a On
 *
 */
function fn_stripslashes($string){
	$string = getparam($string,PAR_NULL,SAN_NULL);

	if (get_magic_quotes_gpc())
		return stripslashes($string);
	else return $string;
}

/**
 * Crea i link con le icone per segnalre la risorsa specificata nei siti sociali
 * @param string $link il link da segnalare
 * @param string $title il titolo (non viene utilizzato da tutti i siti)
 * @author Aldo Boccacci
 * @since 3.0.1
 * Edited by Alfredo Cosco (05/2014): 
 * valid HTML5 $link
 * 
 */
function create_social_links($link,$title){
	// you can redefine create_social_links() function
	if (file_exists("include/redefine/create_social_links.php")){
		include("include/redefine/create_social_links.php");
		return;
	}
	// security checks
	$link  = getparam($link,PAR_NULL,SAN_NULL);
	$title = getparam($title,PAR_NULL,SAN_NULL);
	$link  = strip_tags(stripslashes($link));
	$title = str_replace(" &raquo;", ":", $title);
	$title = urlencode(strip_tags(stripslashes($title)));
	// link validation
	$link  = str_replace("&", "&amp;", $link);
	// build URL
	$protocol = (isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS']=="on") ? ("https://") : ("http://");
	$link     = $protocol.$link;
	// show sharing buttons
	$social = "";
	// share content with Facebook
	if (file_exists("images/social/facebook.png"))
		$social = "<a href=\"http://www.facebook.com/share.php?u=$link&amp;t='$title'\" title=\"Facebook\" target=\"_blank\"><i class=\"fa fa-facebook-square fa-2x\"></i></a>&nbsp;";
	// share content with Twitter
	if (file_exists("images/social/twitter.png"))
		$social .= "<a href=\"https://twitter.com/home?status=$title%20-%20$link\" title=\"Twitter\" target=\"_blank\"><i class=\"fa fa-twitter-square fa-2x\"></i></a>&nbsp;";
	// share content with LinkedIn
	if (file_exists("images/social/linkedin.png"))
		$social .= "<a href=\"https://www.linkedin.com/shareArticle?summary=$title&amp;url=$link&amp;source=$link&amp;title=$title&amp;mini=true\" title=\"LinkedIn\" target=\"_blank\"><i class=\"fa fa-linkedin-square fa-2x\"></i></a>&nbsp;";
	// share content with Google+
	if (file_exists("images/social/google.png")) {
		$social .= "<script type=\"text/javascript\">document.write(' <g:plusone annotation=\"none\"><\/g:plusone>');</script>";
		?><!-- Code from Google for adding +1 button -->
		<script type="text/javascript">
			window.___gcfg = {lang: 'it'};
			(function() {
			var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
			po.src = 'https://apis.google.com/js/plusone.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
			})();
		</script>
		<?php
	}
	// print sharing buttons
	echo $social;
}

?>
