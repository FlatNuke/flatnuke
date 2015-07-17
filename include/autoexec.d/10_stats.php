<?php

/**
 * This module activates functions required to build
 * Flatnuke report visitor statistics.
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Marco Segato <segatom@users.sourceforge.net>
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

if (preg_match("/stats.php/i", $_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}

$from  = getparam("HTTP_REFERER", PAR_SERVER, SAN_FLAT);
$host  = getparam("HTTP_HOST", PAR_SERVER, SAN_FLAT);
$self  = getparam("PHP_SELF", PAR_SERVER, SAN_FLAT);
$where = "http://".$host.$self;
$where = str_replace(basename($where),"",$where);
$url   = str_replace("http://","",$where);
$url   = str_replace("www.","",$url);
$from  = str_replace("http://","",$from);
$from  = str_replace("www.","",$from);
if (file_exists("sections/none_Statistiche/stat.php") AND !stristr($from,$url)) {
	include "sections/none_Statistiche/stat.php";
	stats();
}

?>
