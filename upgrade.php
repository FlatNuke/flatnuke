<?php

/**
* Flatnuke http://www.flatnuke.org/
* Upgrade script from 3.1.2 to 4.0.0 version
*
* Author/s: Roberto Balbi <roberto.balbi@email.it>
*           Marco Segato <segatom@users.sourceforge.net>
*/

// include Flatnuke APIs
include_once "functions.php";
include_once "forum/include/ff_functions.php";
include_once "shared.php";
create_fn_constants();

// start upgrade
$fnversion = get_fn_version();
echo "<h1>FLATNUKE $fnversion UPGRADE</h1>";


/**
** Aggiornamento profili utenti attivati dopo l'upgrade alla 3.1.0 (bugfix)
**/

echo "<h4>1) Correzione profili utente</h4>";

$array = array();
$usersdir = get_fn_dir("users");
$handle = opendir($usersdir);

while($file = readdir($handle)) {
    if(preg_match("/php$/i",get_file_extension($file))) {
        array_push($array, $file);
    }
}

closedir($handle);

// controllo array vuoto non booleano
if(!$array) $array = array();

$bom_removed = 0;
$num_converted = 0;

for($count=0; $count<count($array); $count++) {
    $stringorig = get_file($usersdir."/".$array[$count]);

/* aggiornamento profili utenti attivati dopo l'upgrade alla 3.1.0.
lo script di upgrade della 3.1.0 non convertiva i profili utenti in attesa nella
subdir specificata in waitingusersdir.php (c'era un abbozzo di conversione, ma
non funzionante). e se in seguito tali utenti sono stati attivati, quindi
semplicemente spostati nella usersdir, sono tuttora da convertire.*/
    $string = preg_replace("/fn\:/", "", $stringorig);

    if($stringorig != $string) $num_converted++;

    if(hasBom($string)) {
        $string = substr($string, 3); // remove BOM
        $bom_removed++;
    }

    if($stringorig != $string) {
        fnwrite($usersdir."/".$array[$count], $string, "w", array("nonull"));
        echo "<br>Aggiornato profilo utente <b>".$array[$count]."</b>";
    }
}

if($bom_removed == 0) echo "<br>Nessun BOM da rimuovere.";
else echo "<br>Rimossi $bom_removed BOM.";

if($num_converted == 0) echo "<br>Nessun profilo da correggere.";
else echo "<br>Corretti $num_converted profili.";

echo "<h5>- Correzione profili utente ultimata -</h5>";


/**
** Aggiornamento profili utenti in attesa di attivazione
**/

echo "<h4>2) Aggiornamento profili utente in attesa</h4>";

$vardir = get_fn_dir("var");
$waitingusersdirfile = $vardir."/waitingusersdir.php";

if(file_exists($waitingusersdirfile)) {
    $string = get_file($waitingusersdirfile);
    $waitingusersdir = get_xml_element("waitingusersdir", $string);
    $array = array();
    $handle = opendir($usersdir."/".$waitingusersdir);

    while($file = readdir($handle)) {
        if(preg_match("/php$/i", get_file_extension($file))) {
            array_push($array, $file);
        }
    }

    closedir($handle);

    // controllo array vuoto non booleano
    if(!$array) $array = array();

    $bom_removed = 0;
    $num_converted = 0;

    for($count=0; $count<count($array); $count++) {
        $stringorig = get_file($usersdir."/".$waitingusersdir."/".$array[$count]);

        /* adeguamento alla 3.1.0
        la conversione che appunto non è stata fatta dallo script di upgrade
        della 3.1.0 */
        $string = preg_replace("/fn\:/", "", $stringorig);

        if($stringorig != $string) $num_converted++;

        if(hasBom($string)) {
            $string = substr($string, 3); // remove BOM
            $bom_removed++;
        }

        if($stringorig != $string) {
            fnwrite($usersdir."/".$waitingusersdir."/".$array[$count], $string, "w", array("nonull"));
            echo "<br>Aggiornato profilo in attesa <b>".$array[$count]."</b>";
        }
    }

    if($bom_removed == 0) echo "<br>Nessun BOM da rimuovere.";
    else echo "<br>Rimossi $bom_removed BOM.";

    if($num_converted == 0) echo "<br>Nessun profilo da convertire.";
    else echo "<br>Convertiti $num_converted profili.";

}
else echo "<br>Nessun utente in attesa di attivazione.";
echo "<h5>- Aggiornamento profili utente in attesa ultimato -</h5>";


/**
** Conversione di intestazioni e contenuto file alla codifica UTF-8
**/

include_once "include/filesystem/DeepDir.php";

echo "<h4>3) Conversione a UTF-8</h4>";

echo "Acquisizione elenco directory...";
$dir = new DeepDir();
$dir->setDir(".");
$dir->load();
$dirs = array();
$dirs = $dir->dirs;

// aggiungo directory root non inclusa da DeepDir
array_push($dirs,".");

echo "<br>&nbsp;&nbsp;...fatto: acquisite ".count($dirs)." directory.";

// conversione intestazioni e codifica
$nuovaint = "<?xml version='1.0' encoding='UTF-8'?>\n";

// per ogni directory
for($countdir=0; $countdir<count($dirs); $countdir++) {
    $currentdir = $dirs[$countdir];
    $handle = opendir($currentdir);

    // per ogni file della directory
    while($file = readdir($handle)) {
        $currentfile = $currentdir."/".$file;

        /* se il file ha una di queste estensioni
        (VERIFICARE CHE NON MANCHI NULLA) */
        if(is_file($currentfile) and preg_match("/php|txt|css|js|xml|html|htm|description|^$/i", get_file_extension($file))) {
            $changed = false;

            // leggi il file
            $stringorig = get_file($currentfile);

            // conversione globale (per tutte le news, i log, i profili utenti, ...)
            // delle intestazioni vecchie...
            $string = preg_replace("/<\?xml version='1.0'\?>\n/", $nuovaint, $stringorig);
			// ...e nuove
            $string = preg_replace("/<\?xml version='1.0' encoding='ISO-8859-1'\?>\n/", $nuovaint, $stringorig);

            if($stringorig != $string) $changed = true;
            if($changed) echo "<br>File <b>".$currentfile."</b>: aggiornamento intestazione...";

            // conversione codifica:
            // se non compatibile con utf-8
            if(!mb_check_encoding($string, "UTF-8")) {
                // lo converte
                echo "<br>File <b>".$currentfile."</b>: conversione...";
                $string = mb_convert_encoding($string, "UTF-8");
                $changed = true;
            }

            // se la stringa è stata modificata
            if($changed) {

                // crea copia di backup del file
                // (I FILE DI BACKUP ANDREBBERO POSTI TUTTI IN UNA NUOVA SUBDIR
                // DELLA ROOT COI PERCORSI COMPLETI DEI FILE ORIGINALI)
                copy($currentfile, "none_".$currentfile.".ante_conv_to_UTF-8.orig");
                echo "<br>&nbsp;&nbsp;...backup effettuato...";

                // scrivi il file convertito
                fnwrite($currentfile, $string, "w");
                echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;...modifiche ultimate.<br>";
            }
        }
    }

    closedir($handle);
}

echo "<h5>- Conversione a UTF-8 ultimata -</h5>";

/**
** Correzione file tags_list.php (rigenerazione)
** Il conteggio dei tag di una sezione non veniva correttamente aggiornato in
** caso di modifica delle notizie. I vecchi file tags_list.php sono quindi da
** correggere.
**/

echo "<h4>4) Rigenerazione file tags_list.php</h4>";

$tags_globali = array();
$sezioni_news = array();
$sezioni_news = list_news_sections(); // elenco sezioni da considerare

foreach ($sezioni_news as $sezione) {
	echo "<p>Esame dei tag della sezione di notizie <b>$sezione</b>.<br>";
	$tags_sezione = array();
	$news = array();
	$news = list_news($sezione); // elenco notizie della sezione

	foreach ($news as $notizia) {
		$array = load_news_header($sezione,$notizia); // carico il campo tags

		foreach ($array["tags"] as $tag) {

			if ($tags_sezione[$tag]>0) $tags_sezione[$tag]++;
			else $tags_sezione[$tag] = 1;

			if ($tags_globali[$tag]>0) $tags_globali[$tag]++;
			else $tags_globali[$tag] = 1;

		}

	} // ho finito le notizie della sezione quindi...
	save_tags_list($tags_sezione,$sezione); // scrivo i tag della sezione
	echo "Elenco tag della sezione di notizie <b>$sezione</b> aggiornato.</p>";

} // ho finito le sezioni di notizie quindi...
echo "<p>Elenco tag globali aggiornato.</p>";
save_tags_list($tags_globali); // scrivo i tag globali
echo "<h5>- Conrrezione elenco tag ultimata -</h5>";

// end of upgrade
echo "<h1>FLATNUKE $fnversion UPGRADE OK</h1>";
echo "<p>Si consiglia di salvare questo file di log (CTRL+S). Potrebbe essere utile per risolvere eventuali problemi.</p>";
echo "<div style='text-align: center'><a href='index.php'>Visita il sito aggiornato</a></div>";

/**
* Checks if the string has BOM
* @param string $string
* @return bool
*
* @author BOM CLEANER by Emrah Gunduz
*/
function hasBom($string) {
    return(substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf));
}

?>
