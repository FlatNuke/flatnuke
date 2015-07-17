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


if (preg_match("/section.php/i",$_SERVER['PHP_SELF']))
	{ Header("Location: ../../index.php");
	die();
	}

stats_recovery();

$months=array("Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre");
$currentYear = date("Y");
$totalStat ="0";
$totalSel ="0";
$op = getparam("op",PAR_GET,SAN_FLAT);
$year = getparam("year",PAR_GET,SAN_FLAT);
if($year=="")
  $yearSel = $currentYear;
else
  $yearSel=$year;
$month = getparam("month",PAR_GET,SAN_FLAT);
if($month=="")
  $monthSel = "";
else
  $monthSel=$month;

$years="";
// Lettura directory "/var/flatstat"
// In $years, vengono salvate solo le directory
$handle=opendir(get_fn_dir("var")."/flatstat");
while ($file = readdir($handle)) {
  if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file)and ($file!="CVS"))) {
    if (is_dir(get_fn_dir("var")."/flatstat/$file"))
    $years .= "$file ";
    }
}
closedir($handle);
// $years diventa un'array contenente gli anni
$years = explode(" ", $years);
// $years viene ordinata in modo crescente
sort($years);
// Legge le statistiche generali di tutti gli anni
// Calcola il totale visite
// Inserisce le visite totali di ogni mese per ogni anno
for ($j=1 ;$j<count($years); $j++){
  // Lettura del file contenente le info generali
  if (file_exists(get_fn_dir("var")."/flatstat/$years[$j]/generale.php")){
    $fd = file (get_fn_dir("var")."/flatstat/$years[$j]/generale.php");
    for ($i =0 ; $i<count($fd); $i++){
      $tmp=explode("|",$fd[$i]);
      $tmp = str_replace("\n","",$tmp[1]);
      // Se il file generale che sta leggendo e' quello selezionato
      // Aggiunge le visite ad un'array contente il rispettivo mese
      // Aggiunge le visite al totale dell'anno
      if ($years[$j]==$yearSel) {
        $monthsStat[$i]= $tmp;
        $totalSel+= $tmp;
      }
      // Aggiunge le visite al totale delle visite globali
      $totalStat+=$tmp;
    }
  }
  else {
    echo "Errore...impossibile visualizzare le Statistiche!";
    die();
  }

}
  // Output
  echo "Visite totali: $totalStat<br><br>";
    switch($op){
      // Visualizza statistiche del mese selezionato
      case "m":
        $totalSel="0";
        openTable();
        // Legge le statistiche del mese selezionato
        if (file_exists(get_fn_dir("var")."/flatstat/$yearSel/$monthSel.php")){
          $fd = file (get_fn_dir("var")."/flatstat/$yearSel/$monthSel.php");
          for ($i =0; $i<count($fd)-1; $i++){
            $tmp=explode("|",$fd[$i]);
            $tmp = str_replace("\n","",$tmp[1]);
            $daysStat[$i]=$tmp;
            $totalSel+=$tmp;
          }
          // Stampa le statistiche del mese selezionato
          echo "
          <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
          <tbody>
          <tr>
            <td width=\"20%\" valign=\"top\">
              $yearSel:<br>";
              for ($i=0;$i < count($months); $i++){
                // Se e' il mese selezionato
                // Stampa il nome del mese
                // Altrimenti, stampa il nome del mese e lo linka alle relative statistiche
                if ($i+1==$monthSel){
                  echo "<br><i>$months[$i]</i>";}
                else{
                  if ($monthsStat[$i]==0){
                    echo "<br>$months[$i]";}
                  else {
                    echo "<br><a href=\"index.php?mod=none_Statistiche&amp;op=m&amp;year=$yearSel&amp;month=".($i+1)."\">$months[$i]</a>";}
                }
              }

          echo "</td>
          <td width=\"80%\">
            <table width=\"100%\" border=\"0\">
            <tbody>";
            // Stampa le visite di ogni giorno del mese
            for ($i=0;$i<count($daysStat);$i++){
               echo "<tr>
               <td width=\"25%\">";
               echo $i+1;
               echo "</td>
               <td>";
               if ($totalSel!=0 && $daysStat[$i]!=0) {
                 $percentage=($daysStat[$i]/$totalSel)*100;
                 echo "<hr style=\"width:".$percentage."%\">";}
               echo "</td>
               <td width=\"25%\">
               $daysStat[$i]
               </td>
               </tr>";
            }
            echo "
            <tr>
            <td width=\"25%\">
            </td>
            <td><div align=\"center\">Totale Mese</div>
            </td>
            <td width=\"25%\">
            $totalSel
            </td>
            </tr>
            </tbody>
            </table>
          </td>
        </tr>
        </tbody>
        </table>";
        }
        else {
        echo "Impossibile visualizzare le statistiche del mese selezionato";
        die();
        }
        echo "<br><a href=\"javascript:history.back()\">&lt;&lt; Indietro</a>";
        closeTable();
        break;

      // Visualizza tutti i referer
      case "r":
        openTable();
        $total = 0;
        if (file_exists(get_fn_dir("var")."/flatstat/referer.dat")){

          // Lettura file referer in array con ordinamento
          $fd = file (get_fn_dir("var")."/flatstat/referer.dat");
          for ($i=0 ; $i<count($fd); $i++){
            $tmp=explode("|",$fd[$i]);
            $tmp[1] = str_replace("\n","",$tmp[1]);
            $data_array["referer"][$i] = $tmp[0];
            $data_array["counter"][$i] = $tmp[1];
            $total+=$tmp[1];
          }
          if (count($data_array["counter"])>1)
		array_multisort($data_array["counter"], SORT_NUMERIC, SORT_DESC, $data_array["referer"]);
          echo "Referer:<br><br>";
          openTable();
          echo "<font class=\"content\">FILTRA</font>
          <br>- Visualizza referer appartenenti ad uno specificato dominio:
          <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
          <tbody>
          <tr>
          <td width=\"10%\">
          </td>
          <td height=\"30\"><form name=\"filtroDominio\" method=\"post\" action=\"index.php?mod=none_Statistiche&amp;op=f\">Nome dominio
          <input type=\"text\" name=\"dominio\" />
          <input type=\"submit\" name=\"Submit\" value=\"Filtra\" /></form>
          </td>
          </tr>
          </tbody>
          </table>
          ";
          closeTable();
          echo "<br><br>Totale: $total<br><br><table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">
          <tbody>
          ";
          for ($i=0; $i<count($data_array["referer"]);$i++){
            $string = $data_array["referer"][$i];
            $referer = "";
            // Se la stringa supera gli 80 caratteri, viene suddivisa in sottostringhe
            // per evitare una deformazione orizzontale del sito
            while (strlen($string)>80){
              $referer .= substr($string, 0, 80);
              $referer .="<br>";
              $string = substr($string, 80);
            }
            $referer .=$string;
            echo "
            <tr>
            <td width=\"20%\">".$data_array["counter"][$i]."</td>
            <td width=\"80%\"><a target=\"new\" href=\"".$data_array["referer"][$i]."\">$referer</a></td></tr>";
          }
        echo "</tbody></table>";
        }
        else {
          echo "Errore! File referer non esistente";
        }
        echo "<br><a href=\"javascript:history.back()\">&lt;&lt; Indietro</a>";
        closeTable();
        break;


      // Visualizza i referer filtrati, restituendo quelli che contengono la chiave di ricerca
      case "f":
		$dominio = getparam("dominio",PAR_POST,SAN_FLAT);
		$filter = getparam($dominio,PAR_NULL,SAN_HTML);
        /*$filter = $_POST["dominio"];*/
        $total=0;
        openTable();
        echo "<font class=\"content\">Referer filtrati (\"$filter\")</font>";
        if ($filter!=""){
          if (file_exists(get_fn_dir("var")."/flatstat/referer.dat")){

            // Lettura file referer e inserimento in array solo se soddisfa la ricerca
            $fd = file (get_fn_dir("var")."/flatstat/referer.dat");
            for ($i=0 ; $i<count($fd); $i++){
              $tmp=explode("|",$fd[$i]);
              if (preg_match("/".$filter."/",$tmp[0])){
                $tmp[1] = str_replace("\n","",$tmp[1]);
                $data_array["referer"][$i] = $tmp[0];
                $data_array["counter"][$i] = $tmp[1];
                $total +=$tmp[1];
              }
            }
          }
          else {
            echo "Errore! File referer non esistente";
          }
        }
        if ($total ==0){
          echo "<br>La ricerca non ha prodotto risultati";
        }
        else {
	  if (count($data_array["counter"])>1)
            array_multisort($data_array["counter"], SORT_NUMERIC, SORT_DESC, $data_array["referer"]);

          echo "<br><br>Totale: $total<br><br><table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">
          <tbody>
          ";
          for ($i=0; $i<count($data_array["referer"]);$i++){
            $string = $data_array["referer"][$i];
            $referer = "";
            // Se la stringa supera gli 80 caratteri, viene suddivisa in sottostringhe
            // per evitare una deformazione orizzontale del sito
            while (strlen($string)>80){
              $referer .= substr($string, 0, 80);
              $referer .="<br>";
              $string = substr($string, 80);
            }
            $referer .=$string;
            echo "
            <tr>
            <td width=\"20%\">".$data_array["counter"][$i]."</td>
            <td width=\"80%\"><a target=\"new\" href=\"".$data_array["referer"][$i]."\">$referer</a></td></tr>";
          }
          echo "</tbody></table>";


        }

        closeTable();
        echo "<br><a href=\"javascript:history.back()\">&lt;&lt; Indietro</a>";
        break;


      // Visualizza statistiche anno corrente o anno selezionato
      default:
        openTable();
        echo "
        <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
        <tbody>
        <tr>
          <td width=\"20%\" valign=\"top\">
            Anno:<br>";
            for ($i=1;$i < count($years); $i++){
              // Se e' l'anno selezionato
              // Stampa il nome dell'anno
              // Altrimenti, stampa il nome dell'anno e lo linka alle relative statistiche
              if ($years[$i]==$yearSel){
                echo "<br>$years[$i]";}
              else{
                echo "<br><a href=\"index.php?mod=none_Statistiche&amp;op=y&amp;year=$years[$i]\">$years[$i]</a>";
              }
            }

          echo "</td>
          <td width=\"80%\">
            <table width=\"100%\" border=\"0\">
            <tbody>";
            for ($i=0;$i<12;$i++){
              echo "<tr>
                <td width=\"20%\">";
                // Se le statistiche del mese sono 0
                // Stampa il nome del mese senza linkarlo alle relative statistiche
                // Altrimenti, stampa il nome del mese e lo linka alle relative statistiche
                if ($monthsStat[$i]==0){
                  echo $months[$i];}
                else {
                  echo "<a href=\"index.php?mod=none_Statistiche&amp;op=m&amp;year=$yearSel&amp;month=".($i+1)."\">$months[$i]</a>";}
                echo "</td>
                <td>";
               if ($totalSel!=0 && $monthsStat[$i]!=0) {
                 $percentage=($monthsStat[$i]/$totalSel)*100;
                 echo "<hr style=\"width:".$percentage."%\">";}
                echo "</td>
                <td width=\"20%\">
                  $monthsStat[$i]
                </td>
              </tr>";
            }
            echo "
            <tr>
            <td width=\"20%\">
            </td>
            <td><div align=\"center\">Totale Anno</div>
            </td>
            <td width=\"20%\">
            $totalSel
            </td>
            </tr>
            </tbody>
            </table>

          </td>
        </tr>
        </tbody>
        </table>";
        closeTable();
        echo "<br>";
        // Controlla l'esistenza del file dei referer
        if (file_exists(get_fn_dir("var")."/flatstat/referer.dat")){
          $data_array = array();
		  // Legge il file dei referer
          $fd = file (get_fn_dir("var")."/flatstat/referer.dat");
          for ($i=0 ; $i<count($fd); $i++){
            $tmp=explode("|",$fd[$i]);
            $tmp[1] = str_replace("\n","",$tmp[1]);
            $data_array["referer"][$i] = $tmp[0];
            $data_array["counter"][$i] = $tmp[1];
          }
          if (count($data_array)>1)
			array_multisort($data_array["counter"], SORT_NUMERIC, SORT_DESC, $data_array["referer"]);
          openTable();
          // Stampa i primi 10 referer
          echo "Top 10 Referer:<br><br>
          <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\">
          <tbody>
          ";
          for ($i=0; $i<10 AND $i<count($fd); $i++){
            $string = $data_array["referer"][$i];
            $referer = "";
            // Se la stringa supera gli 80 caratteri, viene suddivisa in sottostringhe
            // per evitare una deformazione orizzontale del sito
            while (strlen($string)>80){
              $referer .= substr($string, 0, 80);
              $referer .="<br>";
              $string = substr($string, 80);
            }
            $referer .=$string;
            echo "
            <tr>
            <td width=\"20%\">".$data_array["counter"][$i]."</td>
            <td width=\"80%\"><a target=\"new\" href=\"".$data_array["referer"][$i]."\">".$referer."</a></td></tr>";
          }
        echo "</tbody></table><br><a href=\"index.php?mod=none_Statistiche&amp;op=r\">Visualizza tutti i referer</a>";
        closeTable();
        }
        break;
    }

module_copyright("FlatStat", "1.3", "Massimo Sandolo", "bastilani [at] supereva.it", "http://flatnuke.org", "GNU/GPL");


/**
 * This function tries to recover files concerning
 * Flatnuke visitor statistics.
 * (Moved from include/autoexec.d/11_stats_recovery.php by Aldo Boccacci)
 *
 * @author Marco Segato
 * @since 3.0
 */
function stats_recovery(){
	// create main directory if it's not present
	if (!file_exists(get_fn_dir("var")."/flatstat")) {
		if (!fn_mkdir(get_fn_dir("var")."/flatstat", 0777)){
			exit();
		}
	}

	// create referers' file if it's not present
	if (!file_exists(get_fn_dir("var")."/flatstat/referer.dat")) {
		fnwrite(get_fn_dir("var")."/flatstat/referer.dat", "", "w");
	}

	// build array of years
	$years_list = array();
	$stats_dir  = opendir(get_fn_dir("var")."/flatstat");
	while($file=readdir($stats_dir)) {
		if(!preg_match("/[0-9a-zA-Z]+/i",get_file_extension($file)) AND !($file=="." OR $file=="..") AND (!preg_match("/^\./",$file) AND ($file!="CVS"))) {
			array_push($years_list, $file);
		}
	}
	closedir($stats_dir);
	if(count($years_list)>0) {
		sort($years_list);
		//echo "<pre>";print_r($years_list);echo "</pre>";	//-> TEST
	}

	// check if current year is ok; if not present, create all the files needed
	if(!in_array(date("Y"), $years_list)) {
		$cur_year  = date("Y");
		$init_year = "";
		if(fn_mkdir(get_fn_dir("var")."/flatstat/$cur_year", 0777)) {
			for($month=1; $month<=12; $month++) {
				$init_month = "";
				$init_year .= "0|0\n";
				$daysInMonth = date("j", mktime(0,0,0,$i,0,$cur_year));
				for ($i=1; $i<=$daysInMonth; $i++) {
					$init_month .= "$i|0\n";
				}
				fnwrite(get_fn_dir("var")."/flatstat/$cur_year/$month.php", "$init_month\n", "w", array("nonull"));
			}
			fnwrite(get_fn_dir("var")."/flatstat/$cur_year/generale.php", "$init_year\n", "w", array("nonull"));
		}
	}

	// re-build statistics for every single year
	foreach($years_list as $year) {
		$rebuild_month = "";
		for($month=1; $month<=12; $month++) {
			// create month file if it does not exists
			if(!file_exists(get_fn_dir("var")."/flatstat/$year/$month.php")) {
				$init = "";
				$daysInMonth = date("j", mktime(0,0,0,$i,0,$year));
				for ($i=1; $i<=$daysInMonth; $i++) {
					$init .= "$i|0\n";
				}
				fnwrite(get_fn_dir("var")."/flatstat/$year/$month.php", "$init\n", "w", array("nonull"));
			} else {
				// re-build year's stats
				$file_month = file(get_fn_dir("var")."/flatstat/$year/$month.php");
				$tot_month  = 0;
				for($i=0; $i<count($file_month); $i++) {
					if (trim($file_month[$i])!="") {
						$string = explode("|", $file_month[$i]);
						$tot_month += "$string[1]";
					}
				}
				$rebuild_month .= "$month|$tot_month\n";
				fnwrite(get_fn_dir("var")."/flatstat/$year/generale.php", $rebuild_month, "w+", array("nonull"));
			}
		}
	}

	// re-build general statistics for the main site
	$tot_site = 0;
	foreach($years_list as $year) {
		if(file_exists(get_fn_dir("var")."/flatstat/$year/generale.php")) {
			$file_year = file(get_fn_dir("var")."/flatstat/$year/generale.php");
			$tot_years = 0;
			for($i=0; $i<count($file_year); $i++) {
				if (trim($file_year[$i])!="") {
					$string = explode("|", $file_year[$i]);
					$tot_years += "$string[1]";
				}
			}
			$tot_site += $tot_years;
		}
	}
	fnwrite(get_fn_dir("var")."/flatstat/totale.php", $tot_site, "w+", array("nonull"));
}

?>
