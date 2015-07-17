<?php 
//Configurazione di FD+

/**
 * imposta le estenzioni riconosciute
 * NOTA: i files con estensioni divise da un punto come "tar.gz", vengono riconosciuti
 * solo grazie alla parte dopo l'ultimo punto.
 * Se vuoi che i files tar.gz vengano riconosciuti, dovrai inserire come estensione "gz"
 * Non lasciate spazi tra le estensioni
 */
$extensions = "gz,tgz,tar,rar,zip,bz2,7z,rpm,deb,tar,doc,dot,rtf,sxw,stw,sxi,csv,sxc,sxd,pot,xls,pdf,ps,gzip,html,htm,txt,jpeg,jpg,gif,png,tiff,bmp,odt,ott,odm,oth,ods,ots,odg,otg,odp,otp,odf,odb,sig,asc,java,jar,mid,midi";

/**
 * La massima dimensione dei files da trasferire
 * (2000000 = circa 2 Mbyte)
 */
$maxFileSize = "2000000";

/**
 * Scegli lo stile delle icone da associare ai file.
 * I valori possibili sono: kde e redmond
 */
$icon_style = "kde";

/**
 * per quante ore devo considerare nuovo un file?
 * (192 ore = 1 settimana)
 */
$newfiletime = 192;

/**
 * se impostato a 1 calcola automaticamente la somma md5 se non viene
 * impostata dall'utente
 */
$automd5 = 0;

/**
 * se impostato a 1 calcola automaticamente la somma sha1 se non viene
 * impostata dall'utente
 */
$autosha1 = 1;

/**
 * Indica se mostrare o meno il nome dell'utente che ha caricato il file
 * 0 = non viene mostrato | 1 = viene mostrato
 */
$showuploader = "0";

/**
 * Indica l'estensione che devono avere le firme gpg dei file
 */
$extsig = "sig";

/**
 * Indica l'estensione degli screenshot associati ai file
 */
$extscreenshot = "png";

/**
 * Indica un elenco di utenti che potranno compiere tutte le azioni consentite agli amministratori.
 * I nomi degli utenti abilitati devono essere separati da una virgola (,).
 * Es: $admins = "utente,utente1,utente2";
 */
$admins = "";

/**
 * Specifica se abilitare o meno le opzioni di amministrazione via web.
 * 1: abilita tutte le opzioni di amministrazione
 * 0: disabilita tutte le opzioni di amministrazione
 */
$enable_admin_options = "1";

/**
 * Mostra il link "Download" in fondo ad ogni file
 * 1 = il link viene mostrato
 * 0 = il link non viene mostrato
 */
$showdownloadlink = "1";

/**
 * Indica se elencare i file nella pagina di riepilogo
 * 1 = i file vengono mostrati
 * 0 = i file non vengono mostrati
 */
$overview_show_files = "1";

/**
 * Indica se mostrare o meno un sommario all'inizio delle sezioni di download
 * 1 = viene mostrato
 * 0 = non viene mostrato
 */
$section_show_header = "1";

/**
 * Se impostato a 1 attiva di default la votazione per i file
 * 1 = la votazione è attivata di default
 * 0 = la votazione deve essere esplicitamente richiesta
 */
$defaultvoteon = "1";

//IMPOSTAZIONI PER I CARICAMENTO DEI FILE DA PARTE DEGLI UTENTI

/**
 * La dimensione massima dei file caricabili da un utente comune.
 */
$usermaxFileSize = 1000000;


/**
 * Il numero di massimo di file caricabili dagli utenti che possono trovarsi
 * in attesa di essere approvati dall'amministratore.
 */
$userfilelimit ="5";

/**
 * Il nome del file che conterrà la lista dei file in attesa di approvazione
 */
$userwaitingfile ="fdwaiting.php";

/**
 * La lista degli utenti che *non* possono caricare file.
 * Separare i nomi degli utenti con una virgola. Non inserite spazi tra un nome utente e l'altro.
 * Es:
 * $userblacklist = "pippo,topolino,paperino";
 */
$userblacklist ="";

/**
 * Il livello minimo che deve avere l'utente per caricare file
 */
$minlevel="0";

?>