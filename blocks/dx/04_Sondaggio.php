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
 * Data      12/08/2006
 */


# previene che blocco sia eseguito direttamente e redirige a index.php
if (preg_match("/Sondaggio.php/i",$_SERVER['PHP_SELF']))
	{ Header("Location: ../../index.php");
	die();
	}

include ("sections/none_Sondaggio/config.php");

// Security convertions
$myforum = _FN_USERNAME;


/*----------------------------------------------------------------------------
	controllo esistenza files, eventuale creazione e impostazione premessi
------------------------------------------------------------------------------*/
if(!file_exists($mod3))					// directory flatpoll
	{ fn_mkdir($mod3, 0777);
 	}
if(!file_exists($sondaggio_file_dati))			// file sondaggio
	{ $file_w = fopen($sondaggio_file_dati, "w+");
	//accesso modalità esclusiva
	$sem = lock($sondaggio_file_dati);
	fwrite($file_w,"<?xml version='1.0' encoding='UTF-8'?>\n<sondaggio>\n\t<attivo>n</attivo>\n\t<domanda>"._FP_NUOVOSONDAGGIO."</domanda>\n\t<opzioni>\n");
	for($riga=1; $riga<4; $riga++)
		fwrite($file_w, "\t\t<opzione>\n\t\t\t<testo>"._FP_OPZIONE."$riga</testo>\n\t\t\t<voto>$riga</voto>\n\t\t</opzione>\n");
	fwrite($file_w,"\t</opzioni>\n</sondaggio>\n");
	// fine modalità esclusiva
	unlock($sem);
	fclose($file_w);
	//chmod($sondaggio_file_dati, 0777);
	}
if(!file_exists($sondaggio_ip_file))			// file IP
	{ $file_w = fopen($sondaggio_ip_file, "w+");
	//accesso modalità esclusiva
	$sem = lock($sondaggio_file_dati);
	fwrite($file_w,"<?xml version='1.0' encoding='UTF-8'?>\n");
	// fine modalità esclusiva
	unlock($sem);
	fclose($file_w);
	//chmod($sondaggio_ip_file, 0777);
	}


/*--------------------------
	creazione del blocco
----------------------------*/
$file_xml = get_file($sondaggio_file_dati);

// controllo che il sondaggio sia attivo
if(get_xml_element("attivo",$file_xml)=="y")
	{ echo "<b>".get_xml_element("domanda",$file_xml)."</b>";	// stampa domanda del sondaggio

	?><form method="post" action="index.php?mod=<?php echo $mod1?>"><?php
	$opzioni = get_xml_element("opzioni",$file_xml);
	$opzione = get_xml_array("opzione",$opzioni);
	for($n=0; $n<count($opzione); $n++)					// stampa radio buttons per opzioni di voto e pulsante voto
		{ ?><input type="radio" name="risposta" value="<?php echo $n?>" alt="opzione<?php echo $n?>" id="opt<?php echo $n?>" /><?php
		echo "<label for=\"opt$n\">".get_xml_element("testo",$opzione[$n])."</label><br>";
		}
	?><div style="text-align:center"><br><input type="submit" name="vota" value="<?php echo _FP_VOTA?>" /></div>
	</form>
	<div style="text-align:center">
	[ <a href="index.php?mod=<?php echo $mod1?>&amp;risultati=1" title="<?php echo _FP_RISULTATI?>"><b><?php echo _FP_RISULTATI?></b></a>
	| <a href="index.php?mod=<?php echo $mod2?>" title="<?php echo _FP_SONDAGGI?>"><b><?php echo _FP_SONDAGGI?></b></a> ]
	</div><?php

	$voti_tot = 0;
	for($n=0; $n<count($opzione); $n++)					// conteggio voti totali
		$voti_tot += get_xml_element("voto",$opzione[$n]);
	$commenti = get_xml_element("commenti",$file_xml);			// conteggio commenti
	$commento = get_xml_array("commento",$commenti);
	?><div style="text-align:center"><?php echo _FP_VOTI?>: <b><?php echo $voti_tot?></b> | <?php echo _FP_COMMENTI?>: <b><?php echo count($commento)?></b></div><?php
	}
// sondaggio non è attivo
else { ?><div style="text-align:center"><?php echo _FP_NOACTIVE?><br><a href="index.php?mod=<?php echo $mod2?>" title="<?php echo _FP_VECCHI?>"><?php echo _FP_VECCHI?></a></div><?php }

?>
