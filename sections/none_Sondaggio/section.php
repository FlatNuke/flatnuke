<?php

/*
 * FlatPoll
 * Copyright (C) 2003-2005 Marco Segato
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
 * Data      30/08/2005
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
	{ Header("Location: ../../index.php");
	die();
	}

include ("sections/none_Sondaggio/config.php");

// security declarations
$myforum = getparam("myforum",PAR_COOKIE,SAN_FLAT);
$ip_indirizzo = getparam("REMOTE_ADDR",PAR_SERVER,SAN_FLAT);
$risultati = getparam("risultati",PAR_GET,SAN_FLAT);
$vota = getparam("vota",PAR_POST,SAN_FLAT);
$modifica = getparam("modifica",PAR_GET,SAN_FLAT);
$mod = getparam("mod", PAR_POST, SAN_FLAT);
$arc = getparam("arc", PAR_POST, SAN_FLAT);
$inscomm = getparam("inscomm", PAR_GET, SAN_FLAT);
$writecomm = getparam("writecomm", PAR_POST, SAN_FLAT);

global $guestcomment, $sitename;


/*---------------------------------------------------------
	questa sezione visualizza i risultati del sondaggio
-----------------------------------------------------------*/

if ($risultati!="")
	{ // stampa la situazione attuale del sondaggio
	print_poll();
	// stampa link finestra copyright
	module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza);
	}


/*----------------------------------------------------------------------------------
	la pagina richiama se stessa dopo aver votato il sondaggio e aggiorna i dati
------------------------------------------------------------------------------------*/
elseif ($vota!="")
	{ /*------------------ controllo ip ---------------------*/
	$ip_valido = 1;
	$ip_file_dati = get_file($sondaggio_ip_file);
	$votanti = get_xml_element("votanti",$ip_file_dati);
	$votante = get_xml_array("votante",$votanti);

	for($n=0; $n<count($votante); $n++)
		{ if($ip_indirizzo == get_xml_element("ip",$votante[$n]) and time() < (get_xml_element("time",$votante[$n])+3600*$sondaggio_ip_scadenza))
			{ echo "<script>
					alert(\""._FP_GIAVOTATO." $sondaggio_ip_scadenza "._FP_ORE.".\");
					window.location='index.php?mod=$mod1&risultati=1';
				</script>";
			$ip_valido = 0;
			}
		}
	$risposta = getparam("risposta",PAR_POST,SAN_FLAT);
	if($ip_valido != 0 and $risposta != "") // ip e` valido, ed e` stata scelta almeno un'opzione di voto
		{ // accede in modalità esclusiva
		$sem = lock($sondaggio_ip_file);
		// è il primo IP che viene inserito
		if(!stristr($ip_file_dati,"<votanti>"))
			{ $ip_file_dati = "<votanti>\n\t<votante>\n\t\t<ip>$ip_indirizzo</ip>\n\t\t<time>".time()."</time>\n\t</votante>\n</votanti>";
			}
		// c'è almeno un IP registrato
		else	{ $ip_file_dati = str_replace("</votanti>", "\t<votante>\n\t\t<ip>$ip_indirizzo</ip>\n\t\t<time>".time()."</time>\n\t</votante>\n</votanti>",$ip_file_dati);
			}
		$file_ip_w = fopen($sondaggio_ip_file, "w");
		fwrite($file_ip_w,"$ip_file_dati");
		fclose($file_ip_w);
		//fine modalità esclusiva
		unlock($sem);
	/*----------------- fine controllo ip -------------------*/
		$file_dati = get_file($sondaggio_file_dati);
		$opzioni = get_xml_element("opzioni",$file_dati);		// ricerca opzione sondaggio da aggiornare
		$opzione = get_xml_array("opzione",$opzioni);
		$new_voto = get_xml_element("voto",$opzione[$risposta]) + 1; // calcolo voto
		$file_dati = str_replace("<testo>".get_xml_element("testo",$opzione[$risposta])."</testo>\n\t\t\t<voto>".get_xml_element("voto",$opzione[$risposta])."</voto>", "<testo>".get_xml_element("testo",$opzione[$risposta])."</testo>\n\t\t\t<voto>$new_voto</voto>",$file_dati);
		$file_dati_w = fopen($sondaggio_file_dati, "w+");		// aggiornamento file sondaggio
		// modalità esclusiva
		$sem = lock($sondaggio_file_dati);
		fwrite($file_dati_w,"$file_dati");
		fclose($file_dati_w);
		//fine modalità esclusiva
		unlock($sem);
		fnlog($zone, "$ip_indirizzo||$myforum||Vote added.");
		}
	else echo "<div align=\"center\"><strong><font color=\"red\">"._FP_VOTONONVALIDO."</font></strong></div><br>";
	// stampa la situazione attuale del sondaggio
	print_poll();
	// stampa link finestra copyright
	module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza);
	}


/*-------------------------------------------------------------------------------------------
	la pagina richiama se stessa per visualizzare dati del sondaggio prima della modifica
---------------------------------------------------------------------------------------------*/
elseif($modifica!="")
	{ if(/*!(isset($myforum) and (getlevel($myforum,"home")==10) and versecid($myforum))*/ !is_admin())
		{ ?><SCRIPT>location="index.php"</SCRIPT><?php
		}

	echo "<form action=\"index.php?mod=$mod1\" method=\"post\">";

	$file_xml = get_file($sondaggio_file_dati);
	$attivo = get_xml_element("attivo",$file_xml);
	$opzioni = get_xml_element("opzioni",$file_xml);
	$opzione = get_xml_array("opzione",$opzioni);

	echo "<strong>"._FP_STATOSONDAGGIO."</strong>";	// stato del sondaggio - aperto/chiuso
	if($attivo=="y")
		{ echo "<input type=\"radio\" name=\"fp_stato\" value=\"y\" checked>"._FP_APERTO."</input>";
		echo "<input type=\"radio\" name=\"fp_stato\" value=\"n\">"._FP_CHIUSO."</input><br>";
		}
	else { echo "<input type=\"radio\" name=\"fp_stato\" value=\"y\">"._FP_APERTO."</input>";
		echo "<input type=\"radio\" name=\"fp_stato\" value=\"n\" checked>"._FP_CHIUSO."</input><br>";
		}
	echo "<br><strong>"._FP_DOMANDASONDAGGIO."</strong> ";	// domanda sondaggio
	echo "<input type=\"text\" name=\"salva_domanda\" value=\"".get_xml_element("domanda",$file_xml)."\" /><br><br>";
	echo "<div align=\"justify\">"._FP_ISTRUZIONIMODIFICA."</div><br>";
	echo "<table><tbody>";
	for($n=0; $n<count($opzione); $n++)			// stampa risposte possibili e voti raccolti (max 9)
		{ echo "<tr>";
		echo "<td><strong>"._FP_OPZIONENUM." "; echo $n+1; echo "</strong></td>";
		echo "<td><input type=\"text\" name=\"salva_opzioni".$n."\" value=\"".get_xml_element("testo",$opzione[$n])."\" /></td>";
		echo "<td><strong>"._FP_VOTI."</strong></td>";
		echo "<td><input type=\"text\" name=\"salva_voti".$n."\" value=\"".get_xml_element("voto",$opzione[$n])."\" size=\"5\" /></td>";
		echo "</tr>";
		}
	for($n=count($opzione); $n<9; $n++)			// nel caso in cui non tutte e 9 fossero inizializzate
		{ echo "<tr>";
		echo "<td><strong>"._FP_OPZIONENUM." "; echo $n+1; echo "</strong></td>";
		echo "<td><input type=\"text\" name=\"salva_opzioni".$n."\" value=\"\" /></td>";
		echo "<td><strong>"._FP_VOTI."</strong></td>";
		echo "<td><input type=\"text\" name=\"salva_voti".$n."\" value=\"\" size=\"5\" /></td>";
		echo "</tr>";
		}
	echo "</tbody></table>";
	// autorizzazione admin per modificare sondaggio
	if(/*isset($myforum) and (getlevel($myforum,"home")==10) and versecid($myforum)*/ is_admin())
		{ echo "<br><div align=\"center\"><input type=\"submit\" value=\""._FP_MODIFICA."\" name=\"mod\" /> ";
		echo "<input type=\"submit\" value=\""._FP_CHIUDIARCHIVIA."\" name=\"arc\" /></div>";
		}
	echo "</form>";
	// stampa link finestra copyright
	module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza);
	}


/*--------------------------------------------------------------------------
	la pagina richiama se stessa per salvare modifica dati del sondaggio
----------------------------------------------------------------------------*/
elseif($mod!="")
	{ $file_xml = get_file($sondaggio_file_dati);
	$opzioni = get_xml_element("opzioni",$file_xml);
	$opzione = get_xml_array("opzione",$opzioni);
	$commenti = get_xml_element("commenti",$file_xml);
	$commento = get_xml_array("commento",$commenti);

	$fp_stato = getparam("fp_stato",PAR_POST,SAN_FLAT);
	if($fp_stato!="") $attivo = $fp_stato;
		else $attivo = "n";

	$array_domanda = getparam("salva_domanda",PAR_POST,SAN_FLAT);
	$array_opzioni[0] = getparam("salva_opzioni0",PAR_POST,SAN_FLAT); $array_voti[0] = getparam("salva_voti0",PAR_POST,SAN_FLAT);
	$array_opzioni[1] = getparam("salva_opzioni1",PAR_POST,SAN_FLAT); $array_voti[1] = getparam("salva_voti1",PAR_POST,SAN_FLAT);
	$array_opzioni[2] = getparam("salva_opzioni2",PAR_POST,SAN_FLAT); $array_voti[2] = getparam("salva_voti2",PAR_POST,SAN_FLAT);
	$array_opzioni[3] = getparam("salva_opzioni3",PAR_POST,SAN_FLAT); $array_voti[3] = getparam("salva_voti3",PAR_POST,SAN_FLAT);
	$array_opzioni[4] = getparam("salva_opzioni4",PAR_POST,SAN_FLAT); $array_voti[4] = getparam("salva_voti4",PAR_POST,SAN_FLAT);
	$array_opzioni[5] = getparam("salva_opzioni5",PAR_POST,SAN_FLAT); $array_voti[5] = getparam("salva_voti5",PAR_POST,SAN_FLAT);
	$array_opzioni[6] = getparam("salva_opzioni6",PAR_POST,SAN_FLAT); $array_voti[6] = getparam("salva_voti6",PAR_POST,SAN_FLAT);
	$array_opzioni[7] = getparam("salva_opzioni7",PAR_POST,SAN_FLAT); $array_voti[7] = getparam("salva_voti7",PAR_POST,SAN_FLAT);
	$array_opzioni[8] = getparam("salva_opzioni8",PAR_POST,SAN_FLAT); $array_voti[8] = getparam("salva_voti8",PAR_POST,SAN_FLAT);

	$file_xml = "<?xml version='1.0' encoding='UTF-8'?>\n<sondaggio>\n";
	$file_xml .= "\t<attivo>$attivo</attivo>\n";
	$file_xml .= "\t<domanda>$array_domanda</domanda>\n";
	$file_xml .= "\t<opzioni>\n";
	for($n=0; $n<9; $n++)
		{ if(!is_numeric($array_voti[$n]))
			$array_voti[$n] = "0";
		if($array_opzioni[$n]!="" and $array_voti[$n]!="")
			{ $file_xml .= "\t\t<opzione>\n";
			$file_xml .= "\t\t\t<testo>".$array_opzioni[$n]."</testo>\n";
			$file_xml .= "\t\t\t<voto>".$array_voti[$n]."</voto>\n";
			$file_xml .= "\t\t</opzione>\n";
			}
		}
	$file_xml .= "\t</opzioni>\n";
	if($commenti != "")
		{ $file_xml .= "\t<commenti>\n";
		for($n=0; $n<count($commento); $n++)
			{ $file_xml .= "\t\t<commento>\n";
			$file_xml .= "\t\t\t<by>".get_xml_element("by",$commento[$n])."</by>\n";
			$file_xml .= "\t\t\t<what>".get_xml_element("what",$commento[$n])."</what>\n";
			$file_xml .= "\t\t</commento>\n";
			}
		$file_xml .= "\t</commenti>\n";
		}
	$file_xml .= "</sondaggio>\n";

	$file_dati_w = fopen($sondaggio_file_dati, "w");
	// accesso modalità esclusiva
	$sem = lock($sondaggio_file_dati);
	fwrite($file_dati_w, stripslashes($file_xml));
	fclose($file_dati_w);
	// fine modalità esclusiva
	unlock($sem);
	fnlog($zone,"$ip_indirizzo||$myforum||Configuration changed.");
	echo "<script>
			alert(\""._FP_MODIFICAOK."\");
			window.location='index.php';
		</script>";
	}


/*----------------------------------------------------------------------------------------
	la pagina richiama se stessa per archiviare un sondaggio e crearne uno nuovo vuoto
------------------------------------------------------------------------------------------*/
elseif($arc!="")
	{ copy($sondaggio_file_dati, $percorso_vecchi."/".time().".xml"); // archiviazione sondaggio
	$file_w = fopen($sondaggio_file_dati, "w");	// crea nuovo sondaggio vuoto
	//accesso modalità esclusiva
	$sem = lock($sondaggio_file_dati);
	fwrite($file_w,"<?xml version='1.0' encoding='UTF-8'?>\n<sondaggio>\n\t<attivo>n</attivo>\n\t<domanda>"._FP_NUOVOSONDAGGIO."</domanda>\n\t<opzioni>\n");
	for($riga=1; $riga<4; $riga++)
		fwrite($file_w, "\t\t<opzione>\n\t\t\t<testo>"._FP_OPZIONE."$riga</testo>\n\t\t\t<voto>$riga</voto>\n\t\t</opzione>\n");
	fwrite($file_w,"\t</opzioni>\n</sondaggio>\n");
	fclose($file_w);
	// fine modalità esclusiva
	unlock($sem);
	$file_ip_w = fopen($sondaggio_ip_file, "w");	// azzeramento IP registrati
	// accesso modalità esclusiva
	$sem = lock($sondaggio_ip_file);
	fwrite($file_ip_w,"<?xml version='1.0' encoding='UTF-8'?>\n");
	fclose($file_ip_w);
	// fine modalità esclusiva
	unlock($sem);
	fnlog($zone, "$ip_indirizzo||$myforum||Poll stored, new one created.");
	echo "<script>
			alert(\""._FP_ARCHIVEOK."\");
			window.location='index.php';
		</script>";
	}


/*--------------------------------------------------------------
	questa sezione visualizza maschera per inserire commenti
----------------------------------------------------------------*/
elseif($inscomm!="")
	{ OpenTableTitle(_FP_ADDCOMM);

	if(($myforum!="") or ($guestcomment==1))			// controllo se utente non registrato può postare o no
		{ echo "<form action=\"index.php?mod=$mod1\" method=\"post\">
			<input type=\"hidden\" name=\"writecomm\" value=\"writecomm\" />
			<strong>"._FP_COMMENTI."</strong><br>
			<textarea cols=\"50\" rows=\"7\" name=\"body\"></textarea><br><br>
			<input type=\"submit\" value=\""._FP_FINVIA."\" />
		</form><br>";
		}
	else echo _FP_DEVIREG." <strong>".$sitename."</strong> "._FP_DEVIREG2; // utenti non registrati non possono postare

	CloseTableTitle();
	// stampa link finestra copyright
	module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza);
	}


/*---------------------------
	inserisce il commento
-----------------------------*/
elseif($writecomm!="")
	{ $by = (get_username()=="") ? (_FP_SCON) : (get_username());
	$what = getparam("body",PAR_POST,SAN_HTML);
	$what = str_replace("\r","",$what);
	$what = str_replace("\n","<br>",$what);
	// controllo sull'accesso concorrente
	$lockfile = $sondaggio_file_dati;
	// accesso esclusivo alla risorsa
	$sem = lock($lockfile);
	$string = get_file($lockfile);

	// si tratta del primo commento inserito
	if(!stristr($string, "<commenti>"))
		{ $string = str_replace("</sondaggio>", "\t<commenti>\n\t\t<commento>\n\t\t\t<by>$by</by>\n\t\t\t<what>$what</what>\n\t\t</commento>\n\t</commenti>\n</sondaggio>",$string);
	}
	// c'è almeno un commento già inserito
	else { $string = str_replace("</commenti>", "\t<commento>\n\t\t\t<by>$by</by>\n\t\t\t<what>$what</what>\n\t\t</commento>\n\t</commenti>",$string);
	}

	$fp = fopen($lockfile, "w");
	fwrite($fp, stripslashes("$string"));
	fclose($fp);
	// fine modalità esclusiva
	unlock($sem);
	fnlog($zone, "$ip_indirizzo||$myforum||Comment inserted.");

	echo "<script>
			window.location='index.php?mod=$mod1&risultati=1';
		</script>";
	}

/*---------------------------------------------------------------------------------
  se nessuna opzione è stata scelta e sondaggio è aperto visualizza risultati
---------------------------------------------------------------------------------*/
else { $file_xml = get_file($sondaggio_file_dati);
	// controllo che il sondaggio sia attivo
	if(get_xml_element("attivo",$file_xml)=="y")
		print_poll();
	else echo _FP_NOACTIVE." <a href='index.php?mod=$mod2' title='"._FP_VECCHI."'>"._FP_VECCHI."</a>";
	// stampa link finestra copyright
	module_copyright($modulo, $versione, $autore, $email, $homepage, $licenza);
	}


/*
 * Funzione che stampa un grafico della situazione attuale del sondaggio
 */
function print_poll()
	{ include ("sections/none_Sondaggio/config.php");
	$file_xml = get_file($sondaggio_file_dati);
	$opzioni = get_xml_element("opzioni",$file_xml);
	$opzione = get_xml_array("opzione",$opzioni);

	$voti_tot = 0;
	for($n=0; $n<count($opzione); $n++)				// conteggio voti totali
		$voti_tot += get_xml_element("voto",$opzione[$n]);

	echo "<div align=\"center\"><strong>".get_xml_element("domanda",$file_xml)." ($voti_tot "._FP_VOTITOTALI.")</strong><br><br>";

	echo "<table><tbody>";
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
	echo "</tbody></table>";

	echo "</div><br><br>";

	OpenTable();
	echo "<b>"._FP_COMMENTI."</b> | <a href=\"index.php?mod=$mod1&amp;inscomm=1\" title=\""._FP_ADDCOMM."\">"._FP_ADDCOMM."</a>";
	CloseTable();
	echo "<br>";
	$commenti = get_xml_element("commenti",$file_xml);
	$commento = get_xml_array("commento",$commenti);
	for($n=0; $n<count($commento); $n++)				// stampa commenti a sondaggio
		{ $user = get_xml_element("by",$commento[$n]);
		print "<div class='comment' style='min-height:105px;'>";
		if($user == _FP_SCON)
			echo "<strong>"._FP_DA."</strong> "._FP_SCON;
		else { // inserisco l'avatar nei commenti
			if(file_exists(get_fn_dir("users")."/$user.php"))
				{

				$userdata = load_user_profile($user);
				$img = $userdata['avatar'];
				if($img!="")
					{ if(!stristr($img,"http://"))
						echo "<img src='forum/".$img."' alt='avatar' class=\"avatar\" />";
					else
						echo "<img src='".$img."' alt='avatar' class=\"avatar\" />";
					}
				else echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
				}
			else echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
			// fine avatar
			print "<b>"._FP_DA."</b> $user";
			}
		echo "<br><br>".get_xml_element("what",$commento[$n]);
		print "</div>";
		echo "<br>";
		}
  }

?>
