<?php
/*
 * FlatStat
 * Copyright (C) 2003 Massimo Sandolo
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
 * Blocco per FlatNuke (http://flatnuke.homelinux.net) di Simone Vellei
 *
 * Autore    Massimo Sandolo  <bastilani@supereva.it>
 * Versione  1.2
 * Data      23/02/2004
 */

if (preg_match("/stat.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}

function stats()  {

if (file_exists("sections/none_Statistiche/referblacklist.php")){
	//controllo la presenza di spam nei refer
	$spamstring="";
	$spamarray=array();
	$spamstring = get_file("sections/none_Statistiche/referblacklist.php");
	$spamarray = explode("\n",$spamstring);
	// print_r($spamarray);
	$spamrule="";
	foreach ($spamarray as $spamrule){
		if (preg_match("/\<\?/",$spamrule)) continue;
		if (trim($spamrule)=="") continue;
		if (preg_match("/^#/",$spamrule)) continue;

		$from = getparam("HTTP_REFERER", PAR_SERVER, SAN_FLAT);

		//se è spam ritorno
		if (preg_match("/".trim($spamrule)."/",$from)) return;
	}
}

// Crea la cartella var/flatstat
// Solo la prima volta che viene eseguito flatnuke, dopo la sua installazione
if (!file_exists(_FN_VAR_DIR."/flatstat")){
  if (!fn_mkdir(_FN_VAR_DIR."/flatstat",0777)){
    echo "<b>Errore!</b><br>Directory per le statistiche non creata, verificare i permessi di scrittura<br>";
    die();
  }
  $tmp="0";
  $lockfile=_FN_VAR_DIR."/flatstat/totale.php";
  $sem=lock($lockfile);
  fnwrite($lockfile,$tmp,"w",array("nonull"));
  unlock($sem);
}


$modlist ="";
// Legge la directory dedicata alle statistiche
$handle=opendir(_FN_VAR_DIR."/flatstat");
while ($file = readdir($handle)) {
  if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file)and ($file!="CVS"))) {
    $modlist .= "$file ";
    }
}
closedir($handle);
$modlist = explode(" ", $modlist);
sort($modlist);
$currentYear= date("Y");
// Ricerca la cartella corrispondente all'anno in corso
$search = array_search($currentYear,$modlist);
// Verifica l'esito della ricerca
if ($search == false || $search == null){
  // Se la ricerca non ha prodotto risultati
  // Prova a creare la cartella dell'anno
  if (fn_mkdir(_FN_VAR_DIR."/flatstat/$currentYear",0777)){
    // La cartella è stata creata
    // Genera un file per ogni mese
    for ($month=1;$month<=12;$month++)  {
      if ($month==12) {$nextMonth=1;}
      else {$nextMonth=$month+1;}
      $daysInMonth= date("j",mktime(0,0,0,$nextMonth,0,$currentYear));
      $lockfile=_FN_VAR_DIR."/flatstat/$currentYear/$month.php";
      // accesso esclusivo alla risorsa
      $sem=lock($lockfile);
      $init = "";
      for ($i=1; $i<=$daysInMonth; $i++) {$init.="$i|0\n";}
      fnwrite($lockfile,"$init\n","w+",array("nonull"));
      unlock($sem);
    }
    // Genera il file generale
    $lockfile=_FN_VAR_DIR."/flatstat/$currentYear/generale.php";
    // accesso esclusivo alla risorsa
    $sem=lock($lockfile);
    $init="";
    for ($i=1; $i<=12; $i++){
      $init.="$i|0\n";
    }
    fnwrite($lockfile,"$init\n","w+",array("nonull"));
    unlock($sem);
  }
  else {
    // La cartella non è stata creata
    // Stampa un errore
    echo "<b>Errore!</b><br>Directory non creata, verificare i permessi di scrittura<br>";
  }
}
else {
  // La ricerca è andata a buon fine
}

// Può aggiungere una visita
$month = date ("n");
$day = date ("j");
// Inserisce visita nel file generale
$fd=file(_FN_VAR_DIR."/flatstat/$currentYear/generale.php");
$tmp ="";
for($i=0; $i<count($fd);$i++){
  if ($i == $month-1) {
  $string=explode("|",$fd[$i]);
  $string[1] = str_replace("\n","",$string[1]);
  if (!_FN_IS_ADMIN) $string[1] += 1;
  $tmp.="$string[0]|$string[1]\n";
  }
  else {
  $tmp.=$fd[$i];
  }
}
$lockfile=_FN_VAR_DIR."/flatstat/$currentYear/generale.php";
// accesso esclusivo alla risorsa
if (!_FN_IS_ADMIN){
	$sem=lock($lockfile);
	fnwrite($lockfile,$tmp,"w",array("nonull"));
	unlock($sem);
}
// Inserisce visita nel file del mese
$fd=file(_FN_VAR_DIR."/flatstat/$currentYear/$month.php");
$tmp ="";
for($i=0; $i<count($fd);$i++){
  if ($i == $day-1) {
  $string=explode("|",$fd[$i]);
  $string[1] = str_replace("\n","",$string[1]);
  if (!_FN_IS_ADMIN) $string[1] += 1;
  $tmp.="$string[0]|$string[1]\n";
  }
  else {
  $tmp.=$fd[$i];
  }
}
$lockfile=_FN_VAR_DIR."/flatstat/$currentYear/$month.php";
// accesso esclusivo alla risorsa
if (!_FN_IS_ADMIN){
	$sem=lock($lockfile);
	fnwrite($lockfile,$tmp,"w",array("nonull"));
	unlock($sem);
}

//Aggiorna file visite totali
$fd = file (_FN_VAR_DIR."/flatstat/totale.php");
$tmp = str_replace("\n","",$fd[0]);
if (!_FN_IS_ADMIN) $tmp++;
$lockfile=_FN_VAR_DIR."/flatstat/totale.php";
// accesso esclusivo alla risorsa
if (!_FN_IS_ADMIN){
	$sem=lock($lockfile);
	fnwrite($lockfile,$tmp,"w",array("nonull"));
	unlock($sem);
}

// Aggiorna il file con i refer
$from = getparam("HTTP_REFERER", PAR_SERVER, SAN_FLAT);

// evita di inserire codice maligno all'interno dei referer
$from = str_replace("<","",$from);
$from = str_replace(">","",$from);

if ($from!=""){
// Se il referer non proviene da una url inserita direttamente nel browser aggiorna i referer
$updateReferer = false;
$tmp="";
// Controlla l'esistenza del file referer
if (file_exists(_FN_VAR_DIR."/flatstat/referer.dat")) {
  // Il file esiste
  // Legge il file
  $fd=file(_FN_VAR_DIR."/flatstat/referer.dat");

  // Controlla l'assenza di PHPSESSID nel referer
  // Nel caso vi sia lo elimina
  $from_temp=explode("&PHPSESSID=",$from);
  if ($from_temp[0]!=$from){
    $exp_temp = explode("&",$from_temp[1]);
    if ($exp_temp[0]!=$from_temp[1]){
      $from_temp[1]="&".$exp_temp[1];
    }
    else {
      $from_temp[1]="";
    }
    $from=$from_temp[0];
    $from.=$from_temp[1];
  }

  // Cerca l'esistenza del referer
  for($i=0; $i<count($fd);$i++){
    $string=explode("|",$fd[$i]);
    if ($string[0] == $from) {
      // Corrispondenza trovata, aggiungie una provenienza
      $string[1] = str_replace("\n","",$string[1]);
      $string[1] += 1;
      $tmp.="$string[0]|$string[1]\n";
      $updateReferer = true;
    }
    else {
      // Altrimenti riscrive il file in $tmp
      $tmp.=$fd[$i];
    }
  }
  // Se alla fine del ciclo non è stato trovato il referer viene accodato
  if (!$updateReferer)
  $tmp.="$from|1\n";
}
else {
  // Il file non esiste
  // Lo crea accodando il referer
  $tmp.="$from|1\n";
}
// Apre il file e lo riscrive aggiornato
$lockfile=_FN_VAR_DIR."/flatstat/referer.dat";
// accesso esclusivo alla risorsa
if (!_FN_IS_ADMIN){
	$sem=lock($lockfile);
	fnwrite($lockfile,$tmp,"w+",array("nonull"));
	unlock($sem);
}
}

}
?>
