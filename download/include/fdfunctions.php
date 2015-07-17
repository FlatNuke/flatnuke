<?php

/**
 * Per purificare una stringa html da tutto quello che non è permesso
 * Questa funzione usa la libreria kses
 * @param string $string la stringa da purificare
 * @return la stringa purificata
 * @author Aldo Boccacci
 * @since 0.7
 */
function purge_html_string($string){
	if (file_exists("include/php_filters/kses.php")){
		include_once("include/php_filters/kses.php");
		$allowed = array ('address' => array (), 'a' => array ('href' => array (), 'title' => array (), 'rel' => array (), 'rev' => array (), 'name' => array ()), 'abbr' => array ('title' => array ()), 'acronym' => array ('title' => array ()), 'b' => array (), 'big' => array (), 'blockquote' => array ('cite' => array ()), 'br' => array (), 'button' => array ('disabled' => array (), 'name' => array (), 'type' => array (), 'value' => array ()), 'caption' => array ('align' => array ()), 'code' => array (), 'col' => array ('align' => array (), 'char' => array (), 'charoff' => array (), 'span' => array (), 'valign' => array (), 'width' => array ()), 'del' => array ('datetime' => array ()), 'dd' => array (), 'div' => array ('align' => array ()), 'dl' => array (), 'dt' => array (), 'em' => array (), 'fieldset' => array (), 'font' => array ('color' => array (), 'face' => array (), 'size' => array ()), 'form' => array ('action' => array (), 'accept' => array (), 'accept-charset' => array (), 'enctype' => array (), 'method' => array (), 'name' => array (), 'target' => array ()), 'h1' => array ('align' => array ()), 'h2' => array ('align' => array ()), 'h3' => array ('align' => array ()), 'h4' => array ('align' => array ()), 'h5' => array ('align' => array ()), 'h6' => array ('align' => array ()), 'hr' => array ('align' => array (), 'noshade' => array (), 'size' => array (), 'width' => array ()), 'i' => array (), 'img' => array ('alt' => array (), 'align' => array (), 'border' => array (), 'height' => array (), 'hspace' => array (), 'longdesc' => array (), 'vspace' => array (), 'src' => array (), 'width' => array ()), 'ins' => array ('datetime' => array (), 'cite' => array ()), 'kbd' => array (), 'label' => array ('for' => array ()), 'legend' => array ('align' => array ()), 'li' => array (), 'p' => array ('align' => array ()), 'pre' => array ('width' => array ()), 'q' => array ('cite' => array ()), 's' => array (), 'strike' => array (), 'strong' => array (), 'sub' => array (), 'sup' => array (), 'table' => array ('align' => array (), 'bgcolor' => array (), 'border' => array (), 'cellpadding' => array (), 'cellspacing' => array (), 'rules' => array (), 'summary' => array (), 'width' => array ()), 'tbody' => array ('align' => array (), 'char' => array (), 'charoff' => array (), 'valign' => array ()), 'td' => array ('abbr' => array (), 'align' => array (), 'axis' => array (), 'bgcolor' => array (), 'char' => array (), 'charoff' => array (), 'colspan' => array (), 'headers' => array (), 'height' => array (), 'nowrap' => array (), 'rowspan' => array (), 'scope' => array (), 'valign' => array (), 'width' => array ()), 'textarea' => array ('cols' => array (), 'rows' => array (), 'disabled' => array (), 'name' => array (), 'readonly' => array ()), 'tfoot' => array ('align' => array (), 'char' => array (), 'charoff' => array (), 'valign' => array ()), 'th' => array ('abbr' => array (), 'align' => array (), 'axis' => array (), 'bgcolor' => array (), 'char' => array (), 'charoff' => array (), 'colspan' => array (), 'headers' => array (), 'height' => array (), 'nowrap' => array (), 'rowspan' => array (), 'scope' => array (), 'valign' => array (), 'width' => array ()), 'thead' => array ('align' => array (), 'char' => array (), 'charoff' => array (), 'valign' => array ()), 'title' => array (), 'tr' => array ('align' => array (), 'bgcolor' => array (), 'char' => array (), 'charoff' => array (), 'valign' => array ()), 'tt' => array (), 'u' => array (), 'ul' => array (), 'ol' => array (), 'var' => array () );
		return kses(stripslashes($string), $allowed);
	}
	else return $string;
}

/**
 * Restituisce TRUE se la sezione passata come parametro è gestita dal metodo fd_view_section()
 * Attenzione: non la ritengo affidabile al 100%. Potrebbero essere segnalate sezioni
 * che contengono il codice richiesto come semplice testo, e non come codice eseguibile.
 * @param string $sect la sezione da verificare
 * @return TRUE se la sezione passata come parametro è gestita dal metodo fd_view_section, FALSE
 *         in caso contrario
 * @author Aldo Boccacci
 * @since 0.7
 */
function is_fd_sect($sect){
	if(!fd_check_path($sect,"sections/","false")) {
		fdlogf("the sect name \"$sect\" is not valid! FDfunctions: ".__LINE__);
		return FALSE;
// 		fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	}
	if (file_exists($sect."/downloadsection")) return TRUE;
	if (file_exists($sect."/section.php")){
		$text="";
		$text=get_file($sect."/section.php");
		if (preg_match("/\<\?.*fd_view_section().*\?\>/i",$text)){
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * Buona parte del codice presente in questo metodo (praticamente tutto)
 * proviene dal progetto autoindex
 * @param string $ext l'estensione del file di cui cercare l'icona
 * @param string $icon_style indica quale set di icone utilizzare
 */
function getIcon($ext,$icon_style) {
//find the appropriate icon depending on the extension (returns a link to the image file)
	if (!check_var(trim($ext),"alnum")) return;
	if (!check_var(trim($icon_style),"alnum")) return;
	$ext=strtolower($ext);
	if ($icon_style == '')
	{
		return '';
	}
	if ($ext == '')
	{
		$icon = 'generic';
	}
	else
	{
		$icon = 'unknown';
		static $icon_types = array(
		'binary' => array('bat', 'bin', 'com', 'exe', 'msi', 'msp', 'pif',
			'pyd', 'scr'),
		'binhex' => array('hqx'),
		'cd' => array('bwi', 'bws', 'bwt', 'ccd', 'cdi', 'cue', 'img',
			'iso', 'mdf', 'mds', 'nrg', 'nri', 'sub', 'vcd'),
		'comp' => array('cfg', 'conf', 'inf', 'ini', 'log', 'nfo', 'reg'),
		'compressed' => array('7z', 'a', 'ace', 'ain', 'alz', 'amg', 'arc',
			'ari', 'arj', 'bh', 'bz', 'bz2', 'cab', 'deb', 'dz', 'gz',
			'io', 'ish', 'lha', 'lzh', 'lzs', 'lzw', 'lzx', 'msx', 'pak',
			'rar', 'rpm', 'sar', 'sea', 'sit', 'taz', 'tbz', 'tbz2',
			'tgz', 'tz', 'tzb', 'uc2', 'xxe', 'yz', 'z', 'zip', 'zoo'),
		'dll' => array('386', 'db', 'dll', 'ocx', 'sdb', 'vxd'),
		'doc' => array('abw', 'ans', 'chm', 'cwk', 'dif', 'doc', 'dot',
			'mcw', 'msw', 'rtf', 'sdw', 'sxw', 'vor', 'wk4', 'wkb', 'wpd',
			'wps', 'wpw', 'wri', 'wsd', 'odt','ott'),
		'image' => array('adc', 'art', 'bmp', 'dib', 'gif', 'ico', 'jfif',
			'jif', 'jp2', 'jpc', 'jpe', 'jpeg', 'jpg', 'jpx', 'mng',
			'pcx', 'png', 'psd', 'psp', 'swc', 'sxd', 'tga', 'tif',
			'tiff', 'wmf', 'wpg', 'xcf', 'xif', 'yuv','odg','otg'),
		'java' => array('class', 'jar', 'jav', 'java', 'jtk'),
		'js' => array('ebs', 'js', 'jse', 'vbe', 'vbs', 'wsc', 'wsf',
			'wsh'),
		'key' => array('aex', 'asc', 'gpg', 'key', 'pgp', 'ppk'),
		'mov' => array('amc', 'dv', 'm4v', 'mac', 'mov', 'mp4v', 'mpg4',
			'pct', 'pic', 'pict', 'pnt', 'pntg', 'qpx', 'qt', 'qti',
			'qtif', 'qtl', 'qtp', 'qts', 'qtx'),
		'movie' => array('asf', 'asx', 'avi', 'div', 'divx', 'm1v', 'm2v',
			'mkv', 'mp2v', 'mpa', 'mpe', 'mpeg', 'mpg', 'mps', 'mpv',
			'mpv2', 'ogm', 'ram', 'rmvb', 'rnx', 'rp', 'rv', 'vivo',
			'vob', 'wmv', 'xvid'),
		'pdf' => array('edn', 'fdf', 'pdf', 'pdp', 'pdx'),
		'php' => array('inc', 'php', 'php3', 'php4', 'php5', 'phps',
			'phtml'),
		'ppt' => array('emf', 'pot', 'ppa', 'pps', 'ppt', 'shw', 'sxi','odp', 'otp'),
		'ps' => array('ps'),
		'sound' => array('aac', 'ac3', 'aif', 'aifc', 'aiff', 'ape', 'apl',
			'au', 'ay', 'bonk', 'cda', 'cdda', 'cpc', 'fla', 'flac',
			'gbs', 'gym', 'hes', 'iff', 'it', 'itz', 'kar', 'kss', 'la',
			'lpac', 'lqt', 'm4a', 'm4p', 'mdz', 'mid', 'midi', 'mka',
			'mo3', 'mod', 'mp+', 'mp1', 'mp2', 'mp3', 'mp4', 'mpc',
			'mpga', 'mpm', 'mpp', 'nsf', 'ofr', 'ogg', 'pac', 'pce',
			'pcm', 'psf', 'psf2', 'ra', 'rm', 'rmi', 'rmjb', 'rmm', 'sb',
			'shn', 'sid', 'snd', 'spc', 'spx', 'svx', 'tfm', 'tfmx',
			'voc', 'vox', 'vqf', 'wav', 'wave', 'wma', 'wv', 'wvx', 'xa',
			'xm', 'xmz'),
		'tar' => array('tar'),
		'text' => array('c', 'cc', 'cp', 'cpp', 'cxx', 'diff', 'h', 'hpp',
			'hxx', 'm3u', 'md5', 'patch', 'pls', 'py', 'sfv', 'sh',
			'txt'),
		'uu' => array('uu', 'uud', 'uue'),
		'web' => array('asa', 'asp', 'aspx', 'cfm', 'cgi', 'css', 'dhtml',
			'dtd', 'htc', 'htm', 'html', 'htt', 'htx', 'jsp', 'lnk',
			'mht', 'mhtml', 'perl', 'pl', 'plg', 'rss', 'shtm', 'shtml',
			'stm', 'swf', 'tpl', 'xht', 'xhtml', 'xml', 'xsl'),
		'xls' => array('csv', 'dbf', 'prn', 'sdc', 'sxc', 'xla', 'xlb',
			'xlc', 'xld', 'xlr', 'xls', 'xlt', 'xlw','ods','ots'));
		foreach ($icon_types as $png_name => $exts)
		{
			if (in_array($ext, $exts))
			{
				$icon = $png_name;
				break;
			}
		}
	}

	if (file_exists("images/mime/$icon_style/$icon.png")){
		return "<img alt=\"[$ext]\" height=\"16\" width=\"16\" src=\"images/mime/$icon_style/$icon.png\" /> ";
	}
	else return "";
}


/**
 * restituisce il numero di download del file
 * @param string $file il file di cui restituire il numero di download
 */
function getDownloads($file){
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$string="";
	$string = get_file($file.".description");

	if (stristr($string,"<hits>")){
		return get_xml_element("hits",$string);
	}

	$url = preg_replace("/-/",  "", $file);
	$url = preg_replace("/_/",  "", $url);
	$url = preg_replace("/\//", "", $url);
	$url = preg_replace("/%/",  "", $url);
	$url = preg_replace("/ /",  "", $url);

	//se il file di tracking non esiste restituisco una stringa vuota
	if (!file_exists(get_fn_dir("var")."/ftrack/".$url)){
		return "";
	}
	elseif (file_exists(get_fn_dir("var")."/ftrack/".$url)){
		$trackfile = fopen(get_fn_dir("var")."/ftrack/".$url,"r");
		$count = fgets($trackfile);
		return $count;

	}
}

/**
 * Contatore dei download
 * @param string $url il percorso del file di cui effettuare il tracking
 * @since 0.6
 * @uthor Aldo Boccacci
 */
function track($url){

include_once("config.php");
include_once("shared.php");

if (file_exists("include/redefine/track.php")){
	include("include/redefine/track.php");
	return;
}

//I files gestiti devono essere inseriti in sections/
//non posso salire
if (!fd_check_path($url,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

//deve esistere anche il file .description
if (!file_exists($url.".description")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);


//il contatore
$track_count = 0;
$track_count = getDownloads($url);

//se l'utente non è loggato come admin incremento il contatore
if (!fd_is_admin()) $track_count++;

insert_in_max_download($url,$track_count);

//inserisco anche nel file di descrizione
// $descstring = get_file($url_orig.".description");
//SISTEMA!!!
if ($track_count=="") $track_count=0;
$description=array();
$description = load_description($url);
$description['hits'] = $track_count;
save_description($url, $description);

//e finalmente scarico il file
$url_orig = preg_replace("/http:\/\//i", "", $url_orig);
// global $siteurl;
//echo "$siteurl//$url_orig";

//gestisco eventuali file remoti
if (isset($description['url']) and trim($description['url'])!="")
	$url = $description['url'];

header("Location: $url");
}

/**
 * salva la descrizione nel file relativo.
 * @param string $file il nome del file nel qule salvare le modifiche
 * @param string $data['name'] il nome del file
 * @param string $data['desc'] la descrizione del file
 * @param string $data['version'] la versione del file
 * @param string $data['md5'] md5 del file
 * @param string $data['sha1'] sha1 del file
 * @param string $data['hits'] il numero di downloads
 * @param string $data['hide'] indica se nascondere il file ai visitatori
 * @param string $data['level'] il livello del file
 * @param boolean $data['showinblocks'] true se il file deve essere mostrato nei blocchi
 * @param string $data['uploadedby'] l'utente che ha uploadato il file
 * @param string $data['time'] il timestamp del momento in cui il file è stato caricato
 * @param string $data['url'] url di file remoto
 * @param string $data['userlabel'] l'etichetta personalizzabile dall'utente
 * @param string $data['uservalue'] il valore associato all'etichetta personalizzabile dall'utente
 * @param string $data['totalscore'] il valore associato all'etichetta personalizzabile dall'utente
 * @param string $data['totalvote'] l'etichetta personalizzabile dall'utente
 * @param string $data['enablerating'] indica se abilitare o meno il voto per il file specificato
 * @since 0.6
 * @author Aldo Boccacci
 */
function save_description($file, $data=array()){

$file = getparam($file,PAR_NULL,SAN_FLAT);
if (!check_path($file,"sections/","false")) fd_die("\$file ".strip_tags($file)." is not valid! FDfunctions: ".__LINE__);

global $defaultvoteon;

//check data
//name
if (isset($data['name'])){
	$name = getparam($data['name'],PAR_NULL,SAN_FLAT);
	if (!check_var($name,"text")) {
		$name = "";
		fdlogf("\$name (".strip_tags($name).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$name = "";
}

//desc
if (isset($data['desc'])){
	$desc = getparam($data['desc'],PAR_NULL,SAN_FLAT);
	$desc = purge_html_string($desc);
}
else {
	$desc = "";
}

//version
if (isset($data['version'])){
	$version = getparam($data['version'],PAR_NULL,SAN_FLAT);
	if (!check_var($version,"text")) {
		$version = "";
		fdlogf("\$version (".strip_tags($version).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$version = "";
}

//md5
if (isset($data['md5'])){
	$md5 = getparam($data['md5'],PAR_NULL,SAN_FLAT);
	if (!check_var($md5,"alnum")) {
		$md5 = "";
		fdlogf("\$md5 (".strip_tags($md5).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$md5 = "";
}

//sha1
if (isset($data['sha1'])){
	$sha1 = getparam($data['sha1'],PAR_NULL,SAN_FLAT);
	if (!check_var($sha1,"alnum")) {
		$sha1 = "";
		fdlogf("\$sha1 (".strip_tags($sha1).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$sha1 = "";
}

//hits
if (isset($data['hits'])){
	$hits = getparam($data['hits'],PAR_NULL,SAN_FLAT);
	if (!check_var($hits,"digit") and $hits!="" and $hits!=0) {
		$hits = "0";
		fdlogf("\$hits (".strip_tags($hits).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$hits = "0";
}

//hide
if (isset($data['hide'])){
	$hide = getparam($data['hide'],PAR_NULL,SAN_FLAT);
	if (!check_var($hide,"boolean")) {
		$hide = "false";
		fdlogf("\$hide (".strip_tags($hide).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$hide = "false";
}

//showinblocks
if (isset($data['showinblocks'])){
	$showinblocks = getparam($data['showinblocks'],PAR_NULL,SAN_FLAT);
	if (!check_var($showinblocks,"boolean")) {
		$showinblocks = "true";
		fdlogf("\$showinblocks (".strip_tags($showinblocks).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$showinblocks = "true";
}

//level
if (isset($data['level'])){
	$level = getparam($data['level'],PAR_NULL,SAN_FLAT);
	if ($level=="-1"){ }
	else if (!check_var($level,"digit") or ($level< -1) or ($level>10)) {
		$level = "-1";
		fdlogf("\$level (".strip_tags($level).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$level = "-1";
}

//uploadedby
if (isset($data['uploadedby'])){
	$uploadedby = getparam($data['uploadedby'],PAR_NULL,SAN_FLAT);
	if (!check_var($uploadedby,"alnum")) {
		$uploadedby = "";
		fdlogf("\$uploadedby (".strip_tags($uploadedby).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$uploadedby = "";
}

//time
if (isset($data['time'])){
	$time = getparam($data['time'],PAR_NULL,SAN_FLAT);
	if (!check_var($time,"digit")) {
		$time = filectime($file);
		fdlogf("\$time (".strip_tags($time).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$time = "";
}

//url
if (isset($data['url'])){
	$url = getparam($data['url'],PAR_NULL,SAN_FLAT);
	if (!check_var($url,"text")) {
		$url = "";
		fdlogf("\$url (".strip_tags($url).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$url = "";
}

//userlabel
if (isset($data['userlabel'])){
	$userlabel = strip_tags(getparam($data['userlabel'],PAR_NULL,SAN_FLAT));
	if (!check_var($userlabel,"text")) {
		$userlabel = "";
		fdlogf("\$userlabel (".strip_tags($userlabel).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$userlabel = "";
}

//uservalue
if (isset($data['uservalue'])){
	$uservalue = getparam($data['uservalue'],PAR_NULL,SAN_FLAT);
	$uservalue = purge_html_string($uservalue);
}
else {
	$uservalue = "";
}

//totalscore
if (isset($data['totalscore'])){
	$totalscore = strip_tags(getparam($data['totalscore'],PAR_NULL,SAN_FLAT));
	if (!check_var($totalscore,"digit")) {
		$totalscore = "0";
		fdlogf("\$totalscore (".strip_tags($totalscore).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$totalscore = "0";
}

//totalvote
if (isset($data['totalvote'])){
	$totalvote = strip_tags(getparam($data['totalvote'],PAR_NULL,SAN_FLAT));
	if (!check_var($totalvote,"digit")) {
		$totalvote = "0";
		fdlogf("\$totalvote (".strip_tags($totalvote).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$totalvote = "0";
}

//enablerating
if (isset($data['enablerating'])){
	$enablerating = getparam($data['enablerating'],PAR_NULL,SAN_FLAT);
	if (!check_var($enablerating,"boolean")) {
		$enablerating = "$defaultvoteon";
		fdlogf("\$enablerating (".strip_tags($enablerating).") is not valid! FDfunctions: ".__LINE__);
	}
}
else {
	$enablerating = "$defaultvoteon";
}


	//controlli di sicurezza
	if (!fd_check_path($file,"sections/","false")) fd_die("\$file ".strip_tags($file)." is not valid! FDfunctions: ".__LINE__);

// 	if (!file_exists($file)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$string="<fd>
	<name>".purge_string(strip_tags($name))."</name>
	<desc>".purge_string(purge_html_string($desc))."</desc>
	<version>".purge_string(trim(strip_tags($version)))."</version>
	<md5>".purge_string(trim(strip_tags($md5)))."</md5>
	<sha1>".purge_string(trim(strip_tags($sha1)))."</sha1>
	<hits>".purge_string(trim(strip_tags($hits)))."</hits>
	<hide>".purge_string(trim(strip_tags($hide)))."</hide>
	<showinblocks>".purge_string(trim(strip_tags($showinblocks)))."</showinblocks>
	<level>".purge_string(trim(strip_tags($level)))."</level>
	<uploadedby>".purge_string(trim(strip_tags($uploadedby)))."</uploadedby>
	<time>".purge_string(trim(strip_tags($time)))."</time>
	<url>".purge_string(trim(strip_tags($url)))."</url>
	<enablerating>".purge_string(trim(strip_tags($enablerating)))."</enablerating>
	<userfield>
		<userlabel>".purge_string(trim(strip_tags($userlabel)))."</userlabel>
		<uservalue>".purge_string(trim(purge_html_string($uservalue)))."</uservalue>
	</userfield>
	<vote>
		<totalvote>".purge_string(trim(strip_tags($totalvote)))."</totalvote>
		<totalscore>".purge_string(trim(strip_tags($totalscore)))."</totalscore>
	</vote>
</fd>";
	if (preg_match("/\<\?/",$string) or preg_match("/\?\>/",$string)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	fnwrite("$file.description","<?xml version='1.0' encoding='UTF-8'?>\n".$string,"w",array("nonull"));
 }

/**
 * Aggiorna la descrizione del file
 */
function update_description(){

}

/**
 * Carica la descrizione del file dal corrisptettivo .description
 * Lo script è in grado di riconoscere se il file contenente la descrizione
 * è nel vecchio formato con i separatori o nel nuovo formato xml.
 * @param string $file il file da cui daricare la descrizione
 * @return un array contenente la descrizione del file. La struttura dell'array è:
 * array['name'] = il nome del file
 * array['desc'] = la descrizione
 * array['version'] = la versione
 * array['md5'] = la somma md5
 * array['sha1'] = la somma sha1
 * array['hits'] = il numero di download del file
 * array['hide'] = true se il file è nascosto, false se è visibile
 * array['showinblocks'] = true se il file deve essere mostrato nei blocchi di statistica, false in caso contrario
 * array['level'] = il livello del file: da -1 (visibile a tutti) a 10
 * array['uploadedby'] = l'utente che ha caricato il file
 * array['time'] il timestamp del momento in cui il file è stato caricato
 * array['enablerating'] = attiva il voto del file
 * array['url'] url di file remoto
 * array['userlabel'] l'etichetta personalizzabile dall'utente
 * array['uservalue'] il valore associato all'etichetta personalizzabile dall'utente
 * @since 0.6
 * @author Aldo Boccacci
 */
function load_description($file){
	include_once("shared.php");
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	global $defaultvoteon;

	$description=array();
	if (function_exists("simplexml_load_file"))
		$xml = @simplexml_load_file($file.".description");
	else $xml=FALSE;
	if (!$xml){
// 		fdlogf("SIMPLEXML: I was not able to load the file ".
// 			strip_tags(basename($file.".description"))." using simplexml_load_file.","ERROR");
		return load_description_old($file);
	}

	$description['name'] = strip_tags($xml->name);
	$description['desc'] = $xml->desc->asXML();
	if ($description['desc']=="<desc/>")
		$description['desc']="";
	$description['version'] = strip_tags($xml->version);
	$description['md5'] = strip_tags($xml->md5);
	$description['hits'] = strip_tags($xml->hits);
	$description['hide'] = $xml->hide;
	if (!($description['hide']=="false" or $description['hide']=="true"))
		$description['hide']="0";
	$description['sha1'] = strip_tags($xml->sha1);
	$description['level'] = strip_tags($xml->level);
	//fix
	if ($description['level']=="") $description['level']="-1";

	if (preg_match("/^false$|^true$/i",$xml->showinblocks)){
		$description['showinblocks'] = $xml->showinblocks;
	}
	else $description['showinblocks'] ="true";

	$description['uploadedby'] = strip_tags($xml->uploadedby);
	$description['time'] = strip_tags($xml->time);
	$description['url'] = strip_tags($xml->url);


	if (preg_match("/1|0/",$xml->enablerating)){
		$description['enablerating'] = strip_tags($xml->enablerating);
	}
	else $description['enablerating'] ="$defaultvoteon";

	$user_defined = $xml->userfield;
	$description['userlabel'] = strip_tags($user_defined->userlabel);
	$description['uservalue'] = strip_tags($user_defined->uservalue);

	$votestring = "";
	$vote_data = $xml->vote;

	$description['totalscore'] = strip_tags($vote_data->totalscore);
	if ($description['totalscore']=="") $description['totalscore'] ="0";
	$description['totalvote'] = strip_tags($vote_data->totalvote);
	if ($description['totalvote']=="") $description['totalvote'] ="0";

	return $description;
}

/**
 * Carica la descrizione del file dal corrisptettivo .description
 * Lo script è in grado di riconoscere se il file contenente la descrizione
 * è nel vecchio formato con i separatori o nel nuovo formato xml.
 * @param string $file il file da cui daricare la descrizione
 * @return un array contenente la descrizione del file. La struttura dell'array è:
 * array['name'] = il nome del file
 * array['desc'] = la descrizione
 * array['version'] = la versione
 * array['md5'] = la somma md5
 * array['sha1'] = la somma sha1
 * array['hits'] = il numero di download del file
 * array['hide'] = true se il file è nascosto, false se è visibile
 * array['showinblocks'] = true se il file deve essere mostrato nei blocchi di statistica, false in caso contrario
 * array['level'] = il livello del file: da -1 (visibile a tutti) a 10
 * array['uploadedby'] = l'utente che ha caricato il file
 * array['time'] il timestamp del momento in cui il file è stato caricato
 * array['enablerating'] = attiva il voto del file
 * array['url'] url di file remoto
 * array['userlabel'] l'etichetta personalizzabile dall'utente
 * array['uservalue'] il valore associato all'etichetta personalizzabile dall'utente
 * @since 0.6
 * @author Aldo Boccacci
 */
function load_description_old($file){
	include_once("shared.php");
	if (!fd_check_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (!preg_match("/^sections/i",$file)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	global $defaultvoteon;

	$descriptions="";
	$descriptions = get_file($file.".description");
// echo $descriptions;
	$description=array();
	if (preg_match("/^<\?xml version=\'1.0\' encoding=\'UTF\-8\'\?\>/i",$descriptions)){
// 	echo "nuovo formato";
		$description['name'] = get_xml_element("name",$descriptions);
		$description['desc'] = get_xml_element("desc",$descriptions);
		$description['version'] = get_xml_element("version",$descriptions);
		$description['md5'] = get_xml_element("md5",$descriptions);
		$description['hits'] = str_replace("<br>","",get_xml_element("hits",$descriptions));
		$description['hide'] = get_xml_element("hide",$descriptions);
		//non è detto che sia presente il campo sha1
		if (preg_match("/\<sha1\>.*\<\/sha1\>/i",$descriptions)){
			$description['sha1'] = get_xml_element("sha1",$descriptions);
		}
		else $description['sha1'] ="";
		//non è detto che sia presente il campo level
		if (preg_match("/\<level\>.*\<\/level\>/i",$descriptions)){
			$description['level'] = get_xml_element("level",$descriptions);
		}
		else $description['level'] ="-1";
		//non è detto che sia presente il campo showinblocks
		if (preg_match("/\<showinblocks\>.*\<\/showinblocks\>/i",$descriptions)){
			if (preg_match("/false|true/i",get_xml_element("showinblocks",$descriptions))){
				$description['showinblocks'] = get_xml_element("showinblocks",$descriptions);
			}
			else $description['showinblocks'] ="true";
		}
		else $description['showinblocks'] ="true";
		//non è detto che sia presente il campo uploadedby
		if (preg_match("/\<uploadedby\>.*\<\/uploadedby\>/i",$descriptions)){
			$description['uploadedby'] = get_xml_element("uploadedby",$descriptions);
		}
		else $description['uploadedby'] = "";
		//non è detto che sia presente il campo time
		if (preg_match("/\<time\>.*\<\/time\>/i",$descriptions)){
			$description['time'] = get_xml_element("time",$descriptions);
		}
		else $description['time'] = filectime($file);
		//non è detto che sia presente il campo url
		if (preg_match("/\<url\>.*\<\/url\>/i",$descriptions)){
			$description['url'] = get_xml_element("url",$descriptions);
		}
		else $description['url'] = "";

		//non è detto che sia presente il campo enablerating
		if (preg_match("/\<enablerating\>.*\<\/enablerating\>/i",$descriptions)){
			if (preg_match("/1|0/",get_xml_element("enablerating",$descriptions))){
				$description['enablerating'] = get_xml_element("enablerating",$descriptions);
			}
			else $description['enablerating'] ="$defaultvoteon";
		}
		else $description['enablerating'] ="$defaultvoteon";


		$userstring = "";
		$userstring = get_xml_element("userfield",$descriptions);

		$description['userlabel'] = get_xml_element("userlabel",$descriptions);
		$description['uservalue'] = get_xml_element("uservalue",$descriptions);

		$votestring = "";
		$votestring = get_xml_element("vote",$descriptions);

		$description['totalscore'] = get_xml_element("totalscore",$votestring);
		if ($description['totalscore']=="") $description['totalscore'] ="0";
		$description['totalvote'] = get_xml_element("totalvote",$votestring);
		if ($description['totalvote']=="") $description['totalvote'] ="0";
	}

	else {
// 	echo "vecchio formato";
		//è nel vecchio formato
		$descriptionold = explode("||",$descriptions);
		$description['name'] = $descriptionold[0];
		$description['desc'] = $descriptionold[1];
		$description['version'] = $descriptionold[2];
		$description['md5'] = "";
		$description['sha1'] = "";
		$description['hits'] = "0";
		$description['hide'] = "false";
		$description['level'] = "-1";
		$description['showinblocks'] = "true";
		$description['uploadedby'] = "";
		$description['time'] = "1";
		$description['enablerating'] = "$defaultvoteon";
		$description['url'] = "";
		$description['userlabel'] = "";
		$description['uservalue'] = "";
		$description['totalscore'] = "0";
		$description['totalvote'] = "0";

	}

	return $description;
}


/**
 * Purifica la stringa da inserire nel file .description
 * @param string $text il testo da controllare
 * @return la stringa purificata pronta da inserire nel file .description
 * @since 0.6
 * @author Aldo Boccacci
 */
function purge_string($text){

	//se la stringa è vuota ritorno subito
	if (trim($text)=="") return "";

	//controllo la lunghezza della stringa
	if (strlen($text)>6000){
		echo _FDDESCTOOLONG;
		echo "<br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a>";
		fd_die();
	}

// 	if (stristr($text,"<") or stristr($text,">")){
		//impedisco l'inserimento di combinazioni che possano creare collisioni con
		//la struttura del file xml
		$text = str_replace("<fd>","&#060;fd&#062;",$text);
		$text = str_replace("</fd>","&#060;/fd&#062;",$text);
		$text = str_replace("<name>","&#060;name&#062;",$text);
		$text = str_replace("</name>","&#060;/name&#062;",$text);
		$text = str_replace("<desc>","&#060;desc&#062;",$text);
		$text = str_replace("</desc>","&#060;/desc&#062;",$text);
		$text = str_replace("<version>","&#060;version&#062;",$text);
		$text = str_replace("</version>","&#060;/version&#062;",$text);
		$text = str_replace("<md5>","&#060;md5&#062;",$text);
		$text = str_replace("</md5>","&#060;/md5&#062;",$text);
		$text = str_replace("<sha1>","&#060;sha1&#062;",$text);
		$text = str_replace("</sha1>","&#060;/sha1&#062;",$text);
		$text = str_replace("<hits>","&#060;hits&#062;",$text);
		$text = str_replace("</hits>","&#060;/hits&#062;",$text);
		$text = str_replace("<hide>","&#060;hide&#062;",$text);
		$text = str_replace("</hide>","&#060;/hide&#062;",$text);
		$text = str_replace("<level>","&#060;level&#062;",$text);
		$text = str_replace("</level>","&#060;/level&#062;",$text);
		$text = str_replace("<showinblocks>","&#060;showinblocks&#062;",$text);
		$text = str_replace("</showinblocks>","&#060;/showinblocks&#062;",$text);
		$text = str_replace("<uploadedby>","&#060;uploadedby&#062;",$text);
		$text = str_replace("</uploadedby>","&#060;/uploadedby&#062;",$text);
		$text = str_replace("<script>","&#060;script&#062;",$text);
		$text = str_replace("</script>","&#060;/script&#062;",$text);
		$text = str_replace("<script","&#060;script",$text);
		$text = str_replace("(","&#040;",$text);
		$text = str_replace(")","&#041;",$text);
// 		$text = str_replace(";","&#059;",$text);
		$text = str_replace("$","&#036;",$text);
		$text = str_replace("%","&#037;",$text);
		$text = str_replace("+","&#043;",$text);
// 		$text = str_replace("/","&#047;",$text);
		//impedisco l'inserimento di codice php
		$text = str_replace("<?php ","&#060;&#063;",$text);
		$text = str_replace("?>", "&#063;&#062;", $text);

// 	}
	//imposto per andare a capo
	$text = str_replace("\n", "", $text);
	$text = str_replace("\r", "", $text);

	//ritorno la stringa purificata
	return stripslashes($text);
}

/**
 * Inserisce il percorso del file e il numero di download nel file contenente
 * le statistiche di download.
 * @param string $filepath il path del file da inserire nelle statistiche
 * @param int $hits in numero di download
 * @author Aldo Boccacci
 * @since 0.6
 */
function insert_in_max_download($filepath,$hits){
// 	include("shared.php");
	global $max_download_file;
	if (!fd_check_path($filepath,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if ($hits!= "" and !check_var(trim($hits),"digit")) fd_die(_NONPUOI.__LINE__);
	if (!fd_check_path($max_download_file,"","true")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	//se non esiste la cartella di archivio la creo
	if (!file_exists(get_fn_dir("var")."/fdplus/")) fn_mkdir(get_fn_dir("var")."/fdplus/",0777);

	//idem per il file con le statistiche
	if (!file_exists(get_fn_dir("var")."/fdplus/".$max_download_file)){
		$string= "<?xml version='1.0' encoding='UTF-8'?>\n<topdownloads>\n</topdownloads>";

		fnwrite(get_fn_dir("var")."/fdplus/".$max_download_file,$string,"w",array("nonull"));
	}

	//carico la vecchia stringa
	$oldstring="";
	$oldfile = fopen(get_fn_dir("var")."/fdplus/$max_download_file","r");
	$oldstring = fread($oldfile,filesize(get_fn_dir("var")."/fdplus/$max_download_file"));
	fclose($oldfile);

	$array=array();
	$array = get_xml_array("file",$oldstring);
	$newstring = "<topdownloads>";
// print_r($array);echo "<hr>";
	//per ciascun elemento contenuto nel file con le statistiche
	for ($int=0;$int<count($array);$int++){
		$element = $array[$int];
		//creo un array avente per indice il path del file
		//e per valore il numero di downloads
		$index="";
		$value="";
		$index = get_xml_element("path",$element);
		$value = get_xml_element("hits",$element);
		$stats[$index] = $value;
	}
// print_r($stats);print"<br><hr>";
	//inserico il nuovo valore
	$stats[$filepath] = $hits;

	//effettuo il sorting dei dati
	if (count($stats)!=0){
		arsort($stats,SORT_NUMERIC);
// print_r($stats);print"<br><hr>";
	reset($stats);
	$first=TRUE;
		foreach ($stats as $element){
			if ($first) reset($stats);
			$first=FALSE;
			$key = key($stats);
			next($stats);
// print "CONTO: ".$key."  $element<hr>";
		//controlla esistenza  dei file (alcuni potrebbero essere stati spostati)
		if (!file_exists($key)) continue;

		//normalizzo il percorso del file
		//rimuovo i doppi slash
		$key = str_replace("//","/",$key);
		$filepath = str_replace("//","/",$filepath);
		//rimuovo un eventuale slash iniziale
		$key = preg_replace("/^\//","",$key);
		$filepath = preg_replace("/^\//","",$filepath);

		//sembra che a volte sia presente un \n o un <br> (?)
		$hits = preg_replace("/\n/","",$hits);
		$hits = preg_replace("/<br>/i","",$hits);

			if ($key == $filepath){

			$newstring .="\n\t<file>
		<path>$filepath</path>
		<hits>$hits</hits>
	</file>";
			}

			else {

				$newstring .="\n\t<file>
		<path>$key</path>
		<hits>$element</hits>
	</file>";

			}

		}
	}
	if (!stristr($newstring,$filepath)){
// 		echo "non è contenuta";
		$newstring .= "\n\t<file>
		<path>$filepath</path>
		<hits>$hits</hits>
	</file>";
	}

	//per sicurezza
	if (preg_match("/\<\?/",$newstring) or preg_match("/\?\>/",$newstring)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$newstring ="<?xml version='1.0' encoding='UTF-8'?>\n".$newstring."\n</topdownloads>";
// 	global $max_download_file;

	fnwrite(get_fn_dir("var")."/fdplus/$max_download_file",$newstring,"w",array("nonull"));
// print($newstring);
// die();
}

/**
 * Restituisce il livello del file messo in download
 * @since 0.7
 * @author Aldo Boccacci
 * @return string il livello del file
 * @param string $filepath il percorso del file di cui restituire il livello
 */
function get_file_level($filepath){
if (!fd_check_path($filepath,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
$desc = load_description($filepath);
return $desc['level'];


}

/**
 * Controlla il percorso di file che gli viene passato come parametro.
 * @param string $filename il nome del file
 * @param string $dirbase il file deve essere all'interno di questa directory o di
 * una sua sottodirectory
 * @param string $allow_php se settato a "true" permette che il file abbia estensione .php e simili
 * @author Aldo Boccacci
 * @since 0.7
 */
function fd_check_path($filename,$dirbase,$allow_php){
// echo $filename;
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
// 	if (stristr($filename,"+")) return FALSE;
// 	if (stristr($filename,"\"")) return FALSE;


 if ($dirbase!=""){
  $dir = "";
  $path = $filename;
  $limit = 0;
  //controlla / iniziale
  while (preg_match("/^\//",$path)){
   $path = preg_replace("/^\//","",$path);
   if ($limit==5) return FALSE;
   $limit++;
  }

  /* deprecated in PHP 5.3:
  if (!eregi("^$dirbase",$path)) return FALSE;*/
  if ($dirbase != substr($path, 0, strlen($dirbase))) return FALSE;
  if (preg_match("/^forum/i",$path)) return FALSE;
 }

 if ($allow_php!="true"){
//  echo "controllo php";
  if (preg_match("/\.php.$|\.php$/i",$filename)) return FALSE;
 }

 return TRUE;
}
/**
 * Restituisce un array con l'elenco delle sezioni gestite dal metodo fd_view_section
 * @param string $basedir la dir da cui effettuare le ricerche
 * @param string $list_archivedir se settato a true include nei risultati anche le dir di archivio
 * @return un array contenente i percorsi delle sezioni gestite dal metodo fd_view_section
 * @author Aldo Boccacci
 * @since 0.7
 */
function list_fd_sections($basedir,$list_archivedirs=FALSE){
	if(!fd_check_path($basedir,"","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	if (preg_match("/^FALSE$|^TRUE$/i",$list_archivedirs)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);
	$dirlist = array();

	include_once("include/filesystem/DeepDir.php");
	$files = NULL;
	$files = new DeepDir();
	$files ->setDir($basedir);
	$files ->load();
	if (!count($files->dirs)==0){
		$dir ="";
		foreach($files->dirs as $dir){
			if (file_exists($dir."/section.php")) {
				if (is_fd_sect(preg_replace("/^\.\//","",$dir))){
					if (preg_match("/none_archivedir$/i",$dir) or preg_match("/none_archivedir.\/$/i",$dir)) continue;
					$dirlist[] = $dir;
				}
			}
		}
	}
	return $dirlist;
}

/**
 * Funzione per includere automaticamente codice PHP
 *
 * Permette di includere tutti i files con estensione .php che sono
 * presenti all'interno di una cartella del sito.
 * (esclusi quelli che cominciano con un punto o con none_)
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Aldo Boccacci
 * @since 0.7
 *
 * @param string $path_phpcode	directory contenente i files da includere
 */
function fd_load_php_code($path_phpcode) {
	$path_phpcode = getparam($path_phpcode, PAR_NULL, SAN_FLAT);
	if(file_exists($path_phpcode)) {
		$dir_phpcode = opendir($path_phpcode);
		$file_phpcode = 0;
		$array_phpcode = array();
		while ($filename_phpcode = readdir($dir_phpcode)) {
			if(preg_match('/[\.]php$/', $filename_phpcode)) {
				if (!preg_match("/^\./",$filename_phpcode) and !preg_match("/^none_/i",$filename_phpcode))
					$array_phpcode[$file_phpcode] = $filename_phpcode;
				$file_phpcode++;
			}
		}
		closedir($dir_phpcode);

		if(count($array_phpcode)>0) {
			sort($array_phpcode);
		}
// 		print_r($array_phpcode);
		for($i=0; $i<count($array_phpcode); $i++) {
// 		echo "$path_phpcode/$array_phpcode[$i]";
			if (file_exists("$path_phpcode/$array_phpcode[$i]"))
				include("$path_phpcode/$array_phpcode[$i]");
		}
	}
}

/**
 * Controlla e restituisce l'array $_FILES.
 *
 * Questa funzione controlla e restiuisce l'array $_FILES utilizzato per caricare i file sul server.
 *
 * @param array $file_array l'array contenente i dati del file da uploadare
 * @param string $maxFileSize la dimensione massima del file da caricare
 * @param boolean $allow_php indica se permettere il caricamento di file con estensione php
 * @author Aldo Boccacci
 * @since 0.8
 */
function check_file_array($file_array,$maxFileSize,$allow_php="false"){
	//controllo che sia un array
	if (!is_array($file_array)){
		fdlogf("error uploading file: \$file_array is not an array. FDfunctions:".__LINE__);
		return FALSE;
	}
	if (!check_var(trim($maxFileSize),"digit")){
		fdlogf("error uploading file: \$maxFileSize is not a digit. FDfunctions: ".__LINE__);
		return FALSE;
	}
	if (!preg_match("/^true$|^false$/i",$allow_php)){
		fdlogf("error uploading file: \$allow_php is not boolean. FDfunctions: ".__LINE__);
		return FALSE;
	}

	//tmp_name
	if (isset($file_array['tmp_name']) and $file_array['tmp_name']!=""){
		if (!check_path($file_array['tmp_name'],"","")){
			fdlogf("error uploading file: \$file_array[\'tmp_name\'] is not valid. FDfunctions: ".__LINE__);
			return FALSE;
		}
	}
	else {
		fdlogf("error uploading file: \$file_array[\'tmp_name\'] is not set. FDfunctions: ".__LINE__);
		return FALSE;
	}

	if (!is_uploaded_file($file_array['tmp_name'])){
		fdlogf("error uploading file: \$file_array[\'tmp_name\'] is not an uploaded file. FDfunctions: ".__LINE__);
		return FALSE;
	}

	//name
	if (isset($file_array['name'])){
		if (!fd_check_path($file_array['name'],"","$allow_php")){
			fdlogf("error uploading file: \$file_array[\'name\'] is not valid. FDfunctions: ".__LINE__);
			return FALSE;
		}
	}
	else {
		fdlogf("error uploading file: \$file_array[\'name\'] is not set. FDfunctions: ".__LINE__);
		return FALSE;
	}

	//size
	if (isset($file_array['size'])){
		if (!check_var(trim($file_array['size']),"digit")){
			fdlogf("error uploading file: \$file_array[\'size\'] isn't a digit. FDfunctions: ".__LINE__);
			return FALSE;
		}
	}
	else {
		fdlogf("error uploading file: \$file_array[\'size\'] isn't set. FDfunctions: ".__LINE__);
		return FALSE;
	}

	if ($file_array['size']=="0"){
		fdlogf("error uploading file: \$file_array[\'size\'] ==0. FDfunctions: ".__LINE__);

		return FALSE;
	}

	//error
	if (isset($file_array['error'])){
		if ($file_array['error']!="0"){
			fdlogf("error uploading file: \$file_array[\'error\'] is".$file_array['error'].". FDfunctions: ".__LINE__);
		}
	}
	else {
		fdlogf("error uploading file: \$file_array[\'error\'] isnot set! FDfunctions: ".__LINE__);
		return FALSE;
	}

	//controllare anche mime type???

	return $file_array;

}

/**
 * Restituisce la data di upload del file
 *
 * @param string $file il percorso del file
 * @return string la data di upload del file
 * @since 0.8
 */
function getfiletime($file){
	if (!checK_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	$desc = array();
	$desc = load_description($file);
	if (trim($desc['time'])!="" and $desc['time']>1){
		return $desc['time'];
	}
	else return filectime($file);
}

/**
 *
 */
function fd_user_can_view_file($file,$description=""){
	if (!checK_path($file,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (fd_is_admin()) return TRUE;

	$myforum = _FN_USERNAME;
	if ($myforum!="" and !is_alphanumeric($myforum)) return FALSE;

	if (!is_array($description)){
		$description = array();
		$description = load_description($file);
	}
	$section = preg_replace("/^sections\//i","",$file);


	if (!_FN_IS_ADMIN and $description['hide']=="true") return FALSE;
// 	echo "livello: "._FN_USERLEVEL."e sezione "._FN_SECT_LEVEL;
	if (_FN_USERLEVEL<$description['level']) return FALSE;
	if (_FN_USERLEVEL<_FN_SECT_LEVEL) return FALSE;
	return TRUE;
}




/**
 * Funzione che crea l'interfaccia per gestire i file.
 *
 * Questa funzione si occupa di creare l'interfaccia web per la gestione
 * delle informazioni associate ai file gestiti da FD+.
 *
 * L'array contiene tutte le proprietà del file
 * (non è obbligatorio inserire tutti questi valori, anzi l'array passato come parametro può essere vuoto)
 * array['name'] = il nome del file
 * array['desc'] = la descrizione
 * array['version'] = la versione
 * array['md5'] = la somma md5
 * array['sha1'] = la somma sha1
 * array['hits'] = il numero di download del file
 * array['hide'] = true se il file è nascosto, false se è visibile
 * array['showinblocks'] = true se il file deve essere mostrato nei blocchi di statistica, false in caso contrario
 * array['level'] = il livello del file: da -1 (visibile a tutti) a 10
 * array['user'] = l'utente che ha caricato il file
 * array['url'] = l'url del file remoto
 * @param string $filepath il path del file
 * @param array $descarray (descritto qui sopra)
 * @param string $action l'azione da compiere
 * @author Aldo Boccacci
 * @since 0.8
 */
function edit_description_interface($filepath,$descarray,$action){
	if(!fd_is_admin() and $action!="userupload") fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (file_exists("include/redefine/".__FUNCTION__.".php")){
	include("include/redefine/".__FUNCTION__.".php");
	return;
	}

global $automd5,$autosha1,$defaultvoteon;

	if (isset($filepath) and $filepath!=""){
		if (fd_check_path($filepath,"sections","false")) $path= $filepath;
		else fd_die("\$filepath is not valid! FDadmin: ".__LINE__);
	}

	if (isset($_POST['path'])) $path =getparam("path",PAR_POST,SAN_FLAT);
	else $path=dirname($filepath);

	if (!check_path($path,"sections/","false")) fd_die("\$path is not valid!: ".strip_tags($path),__FILE__,__LINE__);

	//CONTROLLO L'ARRAY
	if (!is_array($descarray)) fd_die("\$descarray must be an array! FDadmin: ".__LINE__);

	if (isset($descarray['name'])){
		if (fd_check_path($descarray['name'],"","false")) $name = $descarray['name'];
		else fd_die("\$descarray[\'name\'] is not valid. FDadmin: ".__LINE__);
	}
	else $name="";

	if (isset($descarray['desc'])){
		$desc = purge_html_string($descarray['desc']);
	}
	else $desc="";

	if (isset($descarray['version'])){
		if (check_var($descarray['version'],"text")) $version = strip_tags($descarray['version']);
		else fd_die("\$descarray[\'version\'] is not valid. FDadmin: ".__LINE__);
	}
	else $version="";

	if (isset($descarray['userlabel'])){
		if (check_var($descarray['userlabel'],"text")) $userlabel = strip_tags($descarray['userlabel']);
		else fd_die("\$descarray[\'userlabel\'] is not valid. FDadmin: ".__LINE__);
	}
	else $userlabel="";

	if (isset($descarray['uservalue'])){
		$uservalue = purge_html_string($descarray['uservalue']);
	}
	else $uservalue="";

	if (isset($descarray['md5'])){
		if (check_var($descarray['md5'],"alnum")) $md5 = strip_tags($descarray['md5']);
		else fd_die("\$descarray[\'md5\'] is not valid. FDadmin: ".__LINE__);
	}
	else $md5="";

	if (isset($descarray['sha1'])){
		if (check_var($descarray['sha1'],"alnum")) $sha1 = strip_tags($descarray['sha1']);
		else fd_die("\$descarray[\'sha1\'] is not valid. FDadmin: ".__LINE__);
	}
	else $sha1="";

	if (isset($descarray['hits'])){
		if (check_var($descarray['hits'],"digit")) $hits = strip_tags($descarray['hits']);
		else fd_die("\$descarray[\'hits\'] is not valid. FDadmin: ".__LINE__);
	}
	else $hits="0";

	if (isset($descarray['hide'])){
		if (check_var($descarray['hide'],"boolean")) $hide = strip_tags($descarray['hide']);
		else fd_die("\$descarray[\'hide\'] is not valid. FDadmin: ".__LINE__);
	}
	else $hide = "false";

	if (isset($descarray['showinblocks'])){
		if (check_var($descarray['showinblocks'],"boolean")) $showinblocks = strip_tags($descarray['showinblocks']);
		else fd_die("\$descarray[\'showinblocks\'] is not valid. FDadmin: ".__LINE__);
	}
	else $showinblocks="true";

	if (isset($descarray['level'])){
		if (check_var($descarray['level'],"digit") or $descarray['level']=="-1") $level = strip_tags($descarray['level']);
		else fd_die("\$descarray[\'level\'] is not valid. FDadmin: ".__LINE__);
	}
	else $level="-1";

	if (isset($descarray['user'])){
		if (check_var($descarray['user'],"alnum")) $user = strip_tags($descarray['user']);
		else fd_die("\$descarray[\'user\'] is not valid. FDadmin: ".__LINE__);
	}
	else $user = _FN_USERNAME;

	if (isset($descarray['url'])){
		$url = strip_tags($descarray['url']);
// 		if (check_var($descarray['user'],"alnum")) $level = $descarray['alnum'];
// 		else fd_die("\$descarray[\'alnum\'] is not valid. FDadmin: ".__LINE__);
	}
	else $url="";
	//Fine controllo array

	if (isset($descarray['enablerating'])){
		if (check_var($descarray['enablerating'],"boolean")) $enablerating = strip_tags($descarray['enablerating']);
		else fd_die("\$descarray[\'enablerating\'] is not valid. FDadmin: ".__LINE__);
	}
	else $enablerating="$defaultvoteon";

	//imposto la variabile relativa all'utente
	$myforum=_FN_USERNAME;
	if (trim($myforum)=="") return FALSE;

	if (!isset($level)) $level=-1;

	//Controllo $action
	if (!preg_match("/^upload$|^edit$|^addurl$|^userupload$/i",trim($action))) fd_die("\$action must be \"upload\", \"edit\", \"adurl\ or \"userupload\" FDadmin: ".__LINE__);

	global $maxFileSize,$usermaxFileSize;
	if (!ctype_digit("$maxFileSize")) fd_die("\$maxFileSize ($maxFileSize) must be a digit! FDadmin: ".__LINE__);
	if (!ctype_digit("$usermaxFileSize")) fd_die("\$usermaxFileSize ($usermaxFileSize) must be a digit! FDadmin: ".__LINE__);
	//aggiusto gli "a capo" (solo se non trovo fckeditor)
	if (!file_exists("include/plugins/editors/FCKeditor/fckeditor.php") and isset($desc)){
		if (preg_match("/gecko/i",$_SERVER['HTTP_USER_AGENT'])||preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])){
			$desc =str_replace("<br>", "\n", $desc);
		}
	}
	else if (!preg_match("/gecko/i",$_SERVER['HTTP_USER_AGENT']) and !preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])){
		$desc =str_replace("<br>", "\n", $desc);
	}

//crea interfaccia web
if ($action =="edit"){
?>
<div style="text-align:center"><b><?php echo _FDEDITDESC; ?><?php echo $filepath; ?></b></div>

<?php
} //fine edit
else if ($action=="upload" or $action=="addurl"){
	echo "
<div align=\"center\"><b>"._FDADDWHERE."$path</b></div>";
// <br><br>"._FDADDHEADER."<br><br>";
}
else if ($action=="userupload"){
echo "
<div align=\"center\"><b>"._FDADDWHERE."$path</b></div>";
}
if ($action=="upload" or $action=="userupload"){
?>

	<script type="text/javascript">
	function validatefile()
		{
			if(document.getElementsByName('fdfile')[1].value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": File"?>');
					document.getElementsByName('fdfile')[1].focus();
					document.getElementsByName('fdfile')[1].value='';
					return false;
				}
			else return true;
		}
	</script>
<?php
}//fine if ($action=="upload" or $action=="userupload")
?>
<form enctype="multipart/form-data" action="index.php?mod=none_Fdplus" method="POST" onsubmit="return validatefile();">
<input type="hidden" name="fdaction" value="<?php

if ($action=="upload") echo "upload";
else if ($action=="edit") {
	if (isset($url) and trim($url!="")) echo "saveurl";
	else echo "save";
}
else if ($action=="addurl") echo "uploadurl";
else if ($action=="userupload") echo "userupload";
?>" readonly="readonly">
<input type="hidden" name="fdfile" value="<?php echo $filepath; ?>" readonly="readonly">
<input type="hidden" name="path" value="<?php echo $path; ?>" readonly="readonly">


<br><div style="text-align:center;"><?php
	if ($action=="edit"){
// 		echo _FDEDITFOOTER;
	}
	else if ($action=="upload"){
		echo _FDADDFOOTER;
	}

 ?><br><br>

<div align="center">
<?php

if ($action=="userupload"){
	echo "<input type=\"hidden\" name=\"max_file_size\" value=\"$usermaxFileSize\">";
}
else echo "<input type=\"hidden\" name=\"max_file_size\" value=\"$maxFileSize\">";

if ($action=="addurl") {
	echo _FDCHOOSEURL."<br><br>";
	echo "<b>Url: </b> ";
	echo "<input type=\"text\" name=\"fdurl\" value=\"\" size=\"50\">";

}
else if ($action=="upload" or $action=="userupload"){
	echo "<input type=\"file\" name=\"fdfile\">";
}
else if ($action =="edit"){
	if (!isset($url) or trim($url=="")){
		echo "<input type=\"file\" name=\"newfile\">";
	}
	else {
		echo "<b>Url: </b><input type=\"text\" name=\"fdurl\" value=\"$url\" size=\"50\">";
	}
}

?>
<br><input type="SUBMIT" value="<?php
	if ($action=="edit"){
		echo _FDEDITSAVE;
	}
	else if ($action=="upload" or $action=="addurl" or $action=="userupload"){
		echo "UPLOAD";
	}

?>">
</div>
</div>

<br>
<fieldset><legend><b><?php echo _FDBASIC; ?></b></legend>
<table width="100%">

  <tbody>
    <tr>
      <td style="text-align:right; vertical-align:top;"><b><?php echo _FDNAME ?></b></td>
      <td><input type="text" name="filename" value="<?php if (isset($name)) echo $name; ?>" size="42"></td>
    </tr>
    <tr>
      <td style="text-align:right; vertical-align:top;"><b><?php echo _FDDESC ?></b></td>
      <td>
      <?php
      //verifico $desc
      if (!isset($desc)) $desc="";
      //codice per FCKEditor
      if (file_exists("include/plugins/editors/FCKeditor/fckeditor.php")){
      	include("include/plugins/editors/FCKeditor/fckeditor.php");
      	$oFCKeditor = new FCKeditor('description') ;
	$oFCKeditor->BasePath	= "include/plugins/editors/FCKeditor/" ;
	$oFCKeditor->Value= $desc;
	$oFCKeditor->Width="100%";
	$oFCKeditor->ToolbarSet= "FD";
	$oFCKeditor->Create() ;
      }
      else echo "<textarea name=\"description\" rows=\"12\" cols=\"42\" style=\"width: 100%;\">".$desc."</textarea>";
      ?>

    </tr>
    <tr>
      <td style="text-align:right; vertical-align:top;"><b><?php echo _FDVERSION ?></b></td>
      <td><input type="text" name="version" value="<?php if (isset($version)) echo $version; ?>"></td>
    </tr>
    <tr><td style="text-align:right; vertical-align:top;"><br><b><?php echo _FDUSERLABEL; ?>&nbsp;(*)</b><br><input type="text" name="userlabel" value="<?php echo $userlabel; ?>" size="8"></td>
    <td><b><br><?php echo _FDUSERVALUE; ?> (*)</b><br><textarea name="uservalue" rows="1" cols="40"><?php echo $uservalue; ?></textarea></td></tr>
    <tr><td></td><td><i>(*) <?php echo _FDUSERBOTH; ?></i></td></tr>
    <?php if ($action!="userupload"){ ?>
    <tr>
      <td style="text-align:right; vertical-align:top;"><br /><b>Screenshot</b></td>
      <td><br><input name="fdscreenshot" type="file"></td>
    </tr>
    <?php }//fine if userupload ?>

  </tbody>
</table>
</fieldset>
<br>
<fieldset><legend><b><?php echo _FDADVANCED; ?></b></legend>

<table width="100%">
<tr>
      <td style="text-align:right; vertical-align:top;"><b>md5</b></td>
      <td><input type="text" name="md5" value="<?php if (isset($md5)) echo $md5; ?>" size="32">
      <input type="checkbox" name="automd5post" <?php
      if ($automd5=="1") echo "checked";
      ?>> auto</td>
    </tr>
    <tr>
      <td style="text-align:right; vertical-align:top;"><b>sha1</b></td>
      <td><input type="text" name="sha1" value="<?php if (isset($sha1)) echo $sha1; ?>" size="42">
      <input type="checkbox" name="autosha1post" <?php
      if ($autosha1=="1") echo "checked";
      ?>> auto</td>
    </tr>
    <tr>
      <td style="text-align:right; vertical-align:top;"><b><?php echo _FDGPGSIGN; ?></b></td>
      <td><input name="fdsig" type="file">
      </td>
    </tr>
    <tr>
      <td style="text-align:right; vertical-align:top;"><br /><b><?php echo _LEVEL; ?></b></td>
      <td><br><select name="fdfilelevel"><?php
      echo "<option value=\"-1\">---</option>";
      $countlevel=0;

      for ($countlevel;$countlevel<(_FN_USERLEVEL+1);$countlevel++){
      	if ($level==$countlevel){
		echo "<option value=\"$countlevel\" selected >$countlevel</option>";
	}
	else echo "<option value=\"$countlevel\" >$countlevel</option>";
      }
      ?></select></td>
    </tr>
    <tr>
      <td  style="text-align:right; vertical-align:top;"><br><b><?php echo _FDBLOCKS; ?></b></td>
      <td><?php
      if (!isset($showinblocks)) $showinblocks="true";
      if ($showinblocks=="true"){
	echo "<br><input type=\"checkbox\" name=\"showinblocks\" value=\"true\" checked=\"checked\" />";
      }
      else echo "<br><input type=\"checkbox\" name=\"showinblocks\" value=\"true\" />";

      echo _FDSHOWINBLOCKS;
      ?>
<!--       <input type="checkbox" name="prova" value="true" /> -->
      </td>
    </tr>
    <tr>
      <td  style="text-align:right; vertical-align:top;"><b><?php echo _ENABLEVOTE; ?></b></td>
      <td>
      <input type="checkbox" name="enablerating" <?php
      if ($enablerating=="1") echo "checked";
      ?>></td>
    </tr>

<!--     <tr><td colspan="2"><hr></td></tr> -->

</table>
</fieldset>
<div align="center">
<br><input type="SUBMIT" value="<?php
	if ($action=="edit"){
		echo _FDEDITSAVE;
	}
	else if ($action=="upload" or $action=="addurl" or $action=="userupload"){
		echo "UPLOAD";
	}

?>">
</div>
</div>
</form>

<?php

}

/**
 * Restituisce un array con i file di descrizione di fdplus
 *
 * @param string $path il percorso nel quale cercare i file
 * @author Aldo Boccacci
 * @since 0.8
 */
function list_fd_files($path){
$path = getparam($path,PAR_NULL,SAN_FLAT);
$files = array();
if (!check_path($path,"sections/","false"))
	fd_die("Path is not valid! (".strip_tags($path).")",__FILE__,__LINE__);
	$tmpfiles=scandir($path);
	natcasesort($tmpfiles);
	if ($tmpfiles) {
		if (preg_match("/\.description$/i",current($tmpfiles)))
			$files[] = $path."/".current($tmpfiles);
		while (next($tmpfiles)){
			if (preg_match("/\.description$/i",current($tmpfiles)))
				$files[] = $path."/".current($tmpfiles);
		}
	}

	return $files;
}

/**
 * Elenca le sottocartelle (non ricorsivamente)
 * @param string $path il percorso del quale elencare le sottocartelle
 * @author Aldo Boccacci
 * @since 0.8
 */
function list_subdirs($path){
	$path = getparam($path,PAR_NULL,SAN_FLAT);
	if (!check_path($path,"","false")) fd_die("Path is not valid! (".strip_tags($path).")",__FILE__,__LINE__);

	$subdirs=array();
	$handle = opendir($path);
	while ($tmpfile = readdir($handle)) {
		if (!( $tmpfile=="." or $tmpfile==".." ) and is_dir("$path/$tmpfile")) {
		array_push($subdirs, "$path/$tmpfile");
		}
	}
	closedir($handle);
	sort($subdirs);
	return $subdirs;
}

/**
 * Questa funzione restituisce un ID valido da inserire nelle "anchor" e negli ID dei form
 *
 * @param string $filename il nome del file di cui creare un ID
 * @return string un ID valido
 * @author Aldo Boccacci
 * @since 0.8
 */
function create_id($filename){
	$filename = getparam($filename,PAR_NULL,SAN_FLAT);
	$filename = basename($filename);
	strip_tags($filename);
// 	if (!check_path($filename,"","false")) fd_die("Filename is not valid! (".strip_tags($filename).")",__FILE__,__LINE__);
	$filename = preg_replace("/'/","",$filename);
	$filename = preg_replace("/\"/","",$filename);
	$filename = preg_replace("/ /","_",$filename);

	$filename = preg_replace("/^[0-9][0-9]_/","",$filename);

	return $filename;

}

//Le seguenti funzioni in precedenza erano in fd+.php
/**
 * Restituisce true se l'utente è di livello 10
 * (e dunque possiede i privilegi di amministrazione)
 *
 * @return TRUE se l'utente collegato è di livello 10, FALSE se non lo è
 * @author Aldo Boccacci
 */
function fd_is_admin(){

//blocco admin
global $enable_admin_options;
if (trim($enable_admin_options)!="1") return FALSE;

if (_FN_IS_ADMIN) return TRUE;

global $admins;
$powerusers= array();
$powerusers = explode(",",preg_replace("/ /","",$admins));

if (_FN_USERNAME!="" and in_array(_FN_USERNAME,$powerusers)){
	if (versecid(_FN_USERNAME,"home") and _FN_IS_USERNAME) return TRUE;
}

}


/**
 * converte la cartella di archivio eventualmente presente con il vecchio nome
 * @param string $archivedir il nuovo nome della cartella di archivio
 * @author Aldo Boccacci
 * @since 0.6
 */
function rename_archivedir($archivedir){
	$mod="";
	$mod =getparam("mod",PAR_GET,SAN_FLAT);
	$mod = trim($mod);
	if ($mod=="") return;
	if (!fd_check_path($mod,"","false")) return;
	if (!fd_check_path($archivedir,"","false")) return;
	if (is_dir("sections/$mod/vecchie_versioni/") and (!file_exists("sections/$mod/$archivedir"))){
		rename("sections/$mod/vecchie_versioni","sections/$mod/$archivedir");
	}

}


/**
 * Questa funzione serve per salvare il log di fd+.
 * Il messaggio viene formattato aggiungendo campi di interesse.
 *
 * Dalla versione 0.8 sono presenti due file di log:
 * 1. è quello impostato dalla variabile $logfile. Viene utilizzato se non viene impostato il secondo parametro.
 * 2. se viene impostato come secondo parametro "ERROR" il file di log avrà nome fdlogerror.php (di default)
 *
 * @param string $message il messaggio da salvare
 * @param string $type il tipo di messaggio. Può essere lasciato vuoto o
 *               impostato a "ERROR"
 * @author Aldo Boccacci
 * @since 0.7
 */
function fdlogf($message,$type="") {
	global $fdlogfile;
	$fdlogfile_originale = $fdlogfile;
	if (!isset($fdlogfile)) $fdlogfile = get_fn_dir("var")."/log/fdpluslog.php";

	if (preg_match("/\<\?/",$message) or preg_match("/\?\>/",$message)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if ($type=="ERROR"){
		$fdlogfile = preg_replace("/\.php$/i","error.php",$fdlogfile);
	}

	if (!is_dir(get_fn_dir("var")."/log/")){
		fn_mkdir(get_fn_dir("var")."/log","0777");

	}

	if (!file_exists("$fdlogfile")){
		fnwrite($fdlogfile,"<?php exit(1);?>\n","w",array("nonull"));
	}
	else {
		$logtext="";
		$logtext = get_file($fdlogfile);
		if (!preg_match("/^\<\?php exit\(1\);\?\>/i",$logtext)){
// 		echo "no codice controllo";
			fnwrite($fdlogfile,"<?php exit(1);?>\n$logtext","w",array("nonull"));
		}
	}


	//l'utente collegato
	$myforum="";
	if (isset($_COOKIE['myforum'])) $myforum = $_COOKIE['myforum'];
// 	if (!is_alphanumeric($myforum)) $myforum ="";
	if (!versecid($myforum)) $myforum .= "(NOT VALID!)";
	$REMOTE_ADDR="";
	$REMOTE_ADDR = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

	$mod=getparam("mod",PAR_GET,SAN_FLAT);

	$messageok = date(_FDDATEFORMAT)."
	fd version: ".get_fd_version()."
	user: $myforum
	remoteaddr: $REMOTE_ADDR
	section: $mod
	message: $message";

	//prima richiede modifica a fnwrite
// 	fnwrite($fdlogfile,strip_tags("$messageok\n"),"a",array("nonull"));
	$fl=fopen("$fdlogfile","a");
	fwrite($fl, strip_tags("$messageok\n"));
	fclose($fl);

	//resetto il fdlogfile_originale
	$fdlogfile = $fdlogfile_originale;
}

/**
 * Questa funzione serve per salvare il log di fd+
 * @param string $message il messaggio da salvare
 * @author Aldo Boccacci
 * @since 0.7
 */
function fdlog($message) {
	global $fdlogfile;

	if (preg_match("/\<\?/",$message) or preg_match("/\?\>/",$message)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if (!is_dir(get_fn_dir("var")."/log/")){
		fn_mkdir(get_fn_dir("var")."/log","0777");

	}

	if (!file_exists("$fdlogfile")){
		$fl = NULL;
		$fl=fopen("$fdlogfile","a");
		fwrite($fl, "<?php exit(1);?>\n");
		fclose($fl);
	}
	else {
		$logtext="";
		$logtext = get_file($fdlogfile);
		if (!preg_match("/^\<\?php exit\(1\);\?\>/i",$logtext)){
// 		echo "no codice controllo";
			$flogtemp = fopen($fdlogfile,"w");
			fwrite($flogtemp,"<?php exit(1);?>\n".strip_tags($logtext));
			fclose($flogtemp);
		}
	}

	$myforum = _FN_USERNAME;
	$REMOTE_ADDR = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);

// 	if (isset($_GET['mod']))
	$mod = getparam("mod",PAR_GET,SAN_FLAT);
// 	else $mod="";

	if (!is_alphanumeric($myforum)) die(_NONPUOI.__LINE__);
	if (!fd_check_path($mod,"","false")) die(_NONPUOI.__LINE__);
	if (!preg_match("/^[0-9\.]+$/i",$REMOTE_ADDR)) die(_NONPUOI.__LINE__);


	$fl=fopen("$fdlogfile","a");
	fwrite($fl, strip_tags(date(_FDDATEFORMAT)."\n\t$message\n"));
	fclose($fl);

}

/**
 * Funzione di die() personalizzata per fd+ che prima di uccidere il processo
 * salva un messaggio nel log
 * @param string $message il messaggio da stampare a schermo e da salvare nel log
 * @param string $file il file che ha generato l'errore
 * @param string $line la linea nella quale è stato generato l'errore
 * @author Aldo Boccacci
 * @since 0.7
 */
function fd_die($message="unset",$file="",$line=""){
	if ($file!="" and check_path($file,"","true")) $file=strip_tags(basename(trim($file)));
	else $file="";
	if (check_var(trim($line),"digit")) $line=strip_tags(trim($line));
	else $line="";

	if ($file!="" and $line!="")
		$message = "$message $file: $line";

	fdlogf($message,"ERROR");
	if (_FN_IS_ADMIN) echo $message;
	die();
}


/**
 * restituisce una stringa contenente la versione di fdplus
 * @return la versione di fdplus
 * @author Aldo Boccacci
 * @since 0.7rc4
 */
function get_fd_version(){
	return "0.8";
}

?>
