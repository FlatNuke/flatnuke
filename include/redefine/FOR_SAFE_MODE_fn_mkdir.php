<?php 
//-----------------------------------------------------------------------------
//Se è impostato il safe mode nel php rinominate questo file togliendo
//la parte in maiuscolo (FOR_SAFE_MODE_) lasciando solo la parte in minuscolo
//(deve rimanere solo fn_mkdir.php).
//Inserire i parametri di accesso allo spazio ftp nei parametri qua sotto
//-----------------------------------------------------------------------------

//INIZIO CONFIGURAZIONE
//l'indirizzo dello spazio ftp
$ftp_address = "";
//username di accesso
$username = "";
//password di accesso
$password = "";
//il percorso della cartella contenente Flatnuke come visto 
//all'interno dello spazio ftp
$prepath = "";
//FINE CONFIGURAZIONE

if (!check_path($dirpath,"","false"))
	fn_die("Mkdir","the dir ".strip_tags($dirpath)." isn't valid! (include/redefine/fn_mkdir())");
$mode = trim($mode);
//se $mode non è valido metto quello di default del php
if (!check_var($mode,"digit"))
	$mode = 0777;


// set up basic connection
$conn_id = ftp_connect($ftp_address);
if (!$conn_id){
	fnlog("MkdirFTP","\$conn_id = false");
	return FALSE;
}

// login with username and password
$login_result = ftp_login($conn_id, $username, $password);
// print "risultato login: ".$login_result;
// try to create the directory $dir

if (ftp_mkdir($conn_id, $prepath."".$dirpath."/")) {
	fnlog("Mkdir","dir ".strip_tags($dirpath)." created (include/redefine/fn_mkdir())");
}
else {
	fnlog("Mkdir"," I'm not able to create the dir ".strip_tags($dirpath)." (include/redefine/fn_mkdir())");
}

ftp_chmod($conn_id, $mode,$prepath."".$dirpath."/");
// close the connection
ftp_close($conn_id);

?>