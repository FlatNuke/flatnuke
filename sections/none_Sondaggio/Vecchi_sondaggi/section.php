<?php

/*
 * FlatPoll
 * Copyright (C) 2003-2006 Marco Segato
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
 * Blocco per FlatNuke (http://flatnuke.sourceforge.net) di Simone Vellei
 *
 * Autore    Marco Segato  <segatom@users.sourceforge.net>
 * Website   http://www.marcosegato.tk
 * Versione  2.5
 * Data      15/10/2006
 */


// dati per finestra copyrights
$modulo   = "FlatPoll";
$versione = "2.5";
$autore   = "Marco Segato";
$email    = "segatom@users.sourceforge.net";
$homepage = "http://marcosegato.altervista.org";
$licenza  = "GNU General Public License 2";
// dati per sistema di log
$zone = "Poll";

# previene che blocco sia eseguito direttamente e redirige a index.php
if (preg_match("/section.php/i",$_SERVER['PHP_SELF']))
	{ Header("Location: ../../../index.php");
	die();
	}

include_once ("functions.php");

include ("sections/none_Sondaggio/config.php");

// security declarations
$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
$ip_indirizzo = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
$canc = getparam("canc",PAR_POST,SAN_FLAT);

/*------------------------------------------------------------------------
	la pagina richiama se stessa per eliminare un sondaggio archiviato
--------------------------------------------------------------------------*/
if($canc!="" AND is_admin()) {
	$file_da_canc = getparam("file_da_canc",PAR_POST,SAN_FLAT);
	unlink($percorso_vecchi."/".$file_da_canc);	// elimina sondaggio selezionato
	fnlog($zone, "$ip_indirizzo||$myforum||Old poll deleted.");
	echo "<script>
			alert(\""._FP_DELETEOK1." "
			.date("j/n/Y, H:i:s", substr($file_da_canc,0,strpos($file_da_canc,'.'))).
			" "._FP_DELETEOK2."\");
		</script>";
}


/*---------------------------------------------------
	stampa a video di tutti i sondaggi archiviati
-----------------------------------------------------*/
$d = opendir($percorso_vecchi);
$n_file = 0;
while ($file = readdir($d))	{				// carico tutti i files in un array
	if(!preg_match("/ip/i",$file) AND !preg_match("/sondaggio/i",$file) AND !($file==".") AND !($file=="..")) {
		$array_dir[$n_file] = $file;
		$n_file++;
	}
}
closedir($d);

if($n_file>0)
{ rsort($array_dir);						// ordina l'array in ordine decrescente
for ($i=0; $i<count($array_dir); $i++)				// stampa sondaggi
	{ if( !( $array_dir[$i]=="." or $array_dir[$i]==".." ) and (!preg_match("/^\./",$array_dir[$i])) and ($file!="CVS") )
		{ $file_xml = stripslashes(get_file($percorso_vecchi."/".$array_dir[$i]));
		$opzioni = get_xml_element("opzioni",$file_xml);
		$opzione = get_xml_array("opzione",$opzioni);

		$voti_tot = 0;
		for($n=0; $n<count($opzione); $n++)				// conteggio voti totali
			$voti_tot += get_xml_element("voto",$opzione[$n]);

		OpenTableTitle("<img src=\"themes/$theme/images/news.png\" alt=\"News\" />&nbsp;".get_xml_element("domanda",$file_xml)." ($voti_tot "._FP_VOTITOTALI.")");

		echo "<br><table align=\"center\"><tbody>";
		for($n=0; $n<count($opzione); $n++)				// stampa risultati sondaggio
			{ if(get_xml_element("voto",$opzione[$n])==0)	// calcolo %
				$perc = 0;
			else $perc = get_xml_element("voto",$opzione[$n]) * 100 / $voti_tot;
			$perc_neg = 100 - $perc;				// calcolo -%
			echo "<tr>";
			echo "<td align=\"left\">".get_xml_element("testo",$opzione[$n])."</td>";
			echo "<td align=\"left\">";
				echo "<img src=\"$sondaggio_immagine\" alt=\"%\" width=\"".intval($perc)."\" height=\"10\" />"; // stampa immagine %
			echo "</td>";
			echo "<td align=\"left\" width=\"".intval($perc_neg)."\" height=\"10\"></td>";  // stampa -%
			printf("<td align=\"right\"> %01.1f",$perc); echo "%</td>";
			echo "<td align=\"right\">(".get_xml_element("voto",$opzione[$n])." "._FP_VOTI.")</td>";
			echo "</tr>";
			}
		echo "</tbody></table><br>";

		echo "<table width=\"100%\"><tbody><tr>";
		echo "<td>"._FP_CHIUSO." ".date("j/n/Y, H:i:s", substr($array_dir[$i],0,strpos($array_dir[$i],'.')))."</td>";
		// autorizzazione admin per visualizzare pulsante di cancellazione
		if(is_admin())
			{ echo "<td align=\"right\">";
			echo "<form action=\"index.php?mod=$mod2\" method=\"post\">";
			echo "<input type=\"checkbox\" name=\"canc\" /> <input type=\"submit\" value=\""._FP_ELIMINA."\" />";
			echo "<input type=\"hidden\" name=\"file_da_canc\" value=\"".$array_dir[$i]."\" /";
			echo "</form>";
			echo "</td>";
			}
		echo "</tr></tbody></table><br>";

		$commenti = get_xml_element("commenti",$file_xml);
		$commento = get_xml_array("commento",$commenti);
		for($n=0; $n<count($commento); $n++)				// stampa commenti a sondaggio
			{ OpenTable();
			if(get_xml_element("by",$commento[$n]) == "")
				echo "<b>"._FP_DA."</b> "._FP_SCON;
			else echo "<b>"._FP_DA."</b> ".get_xml_element("by",$commento[$n]);
			echo "<br><br>".get_xml_element("what",$commento[$n]);
			CloseTable();
			echo "<br>";
			}
		CloseTableTitle();
		}
	}
} else echo _FP_NONEOLD;

// stampa link finestra copyright
module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza);
?>
