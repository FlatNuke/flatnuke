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
 * Autore    Marco Segato  <segatom@yahoo.it>
 * Website   http://www.marcosegato.tk
 * Versione  2.5
 * Data      05/08/2004
 */


$mod1 = "none_Sondaggio";				// mod per index.php
$mod2 = "none_Sondaggio/Vecchi_sondaggi";		// mod per index.php

$mod3 = get_fn_dir("var")."/flatpoll";				// directory con gli archivi

$sondaggio_file_dati = "$mod3/sondaggio.xml";		// file in cui e` salvato il sondaggio corrente
$sondaggio_ip_file = "$mod3/ip.xml";			// file in cui sono salvati gli IP dei votanti
$percorso_vecchi = $mod3;				// directory che contiene i sondaggi archiviati

$sondaggio_immagine = "sections/$mod1/xcento.png";	// immagine della percentuale dei voti

$sondaggio_ip_scadenza = 2;				// intervallo di ore tra un voto e l'altro dello stesso IP

?>
