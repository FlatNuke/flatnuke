<?php
/**
 * Su piattaforma Windows possono esserci problemi nel download
 * di file contenenti caratteri speciali (lettere accentate, simboli, ...).
 * Questo plugin inserisce una pagina intermedia di download che risolve il
 * problema.
 * Per attivare rimuovere il prefisso "WINDOWS_DOWNLOAD_FIX_" dal nome
 * del file. Per disattivare aggiungere un prefisso a scelta al nome
 * del file.
 *
 * @author Aldo Boccacci
 * @since Flatnuke 3.0
 */

//I files gestiti devono essere inseriti in sections/
//non posso salire
if (!fd_check_path($url,"sections/","false")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

//deve esistere anche il file .description
if (!file_exists($url.".description")) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);


//il contatore
$track_count = 0;
$track_count = getDownloads($url);

if(fd_is_admin()){

//in questo caso mostro unicamente il numero di visite della pagina senza
//incrementare il contatore.
//Questo per non falsare i risultati del numero di visite inserendo anche quelle dell'admin.

}

else {
//se l'utente non è loggato come admin incremento il contatore
	$track_count++;
}

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
// $url=rawurlencode($url);


// Must be fresh start
if( headers_sent() )
die('Headers Sent');

// Required for some browsers
if(ini_get('zlib.output_compression'))
	ini_set('zlib.output_compression', 'Off');
// print $url;
// File Exists?
// echo $url;die();
if(!preg_match("/[^(\x20-\x7F)]*/",urldecode($url))){
	header("Location: $url");
	return;
	// Parse Info / Get Extension
	$fsize = filesize($url);
	$path_parts = pathinfo($url);
	$ext = strtolower($path_parts["extension"]);

	// Determine Content Type
	switch ($ext) {
		case "pdf": $ctype="application/pdf"; break;
		case "exe": $ctype="application/octet-stream"; break;
		case "zip": $ctype="application/zip"; break;
		case "doc": $ctype="application/msword"; break;
		case "xls": $ctype="application/vnd.ms-excel"; break;
		case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpeg":
		case "jpg": $ctype="image/jpg"; break;
		default: $ctype="application/force-download";
	}

	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false); // required for certain browsers
	header("Content-Type: $ctype");
	header("Content-Disposition: attachment; filename=\"".basename($url)."\";" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$fsize);
	ob_clean();
	flush();
	readfile($url);

}
else{
	echo "<div style=\"text-align: center;\">
	Il download dovrebbe partire automaticamente.<br>
	In caso contrario cliccate sul seguente collegamento:<br>
	<a href=\"".rawurlencodepath($url)."\">".basename($url)."</a><br><br>
	<b><a href=\"javascript:history.back()\">"._FDARCHIVERETURN."</a></b>
	</div>";

	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=$url\">";


}

?>