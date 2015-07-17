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

if (preg_match("/Statistiche.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}

if(file_exists(_FN_VAR_DIR."/flatstat/totale.php")){
	$fd = file(_FN_VAR_DIR."/flatstat/totale.php");
	echo _VISITS.": $fd[0] <br>";
	echo "<div style=\"text-align : center;\"><a href=\"index.php?mod=none_Statistiche\" title=\""._GOTOSECTION.": "._STATISTICS."\">"._STATISTICS."</a></div>";
}
?>