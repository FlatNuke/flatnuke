<?php

/*
 * ZipGallery
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
 * Script che permette di esportare tutto il contenuto di una sezione di tipo
 * "Galleria" in un file ZIP da salvare comodamente sul proprio computer.
 *
 * Autore    Ignazio Coco
 * Website   http://taison.altervista.org
 * Versione  0.3
 *
 * Autore    Marco Segato <segatom@users.sourceforge.net>
 * Website   http://marcosegato.altervista.org
 * Versione  20130210 - Integrazione in Flatnuke v3.x.x
 */

// intercept direct access to this file &
if (preg_match("/zipgallery.php/i",$_SERVER['PHP_SELF'])) {
	// rebuild the right path
	chdir("../");
	// include Flatnuke API
	include_once "functions.php";
	// get the name of the gallery section you want to download
	$source_mod = getparam("source_mod", PAR_GET, SAN_FLAT);
	// prevent using this script for other sections except Gallery
	if(file_exists("sections/$source_mod/gallery") and is_file("sections/$source_mod/gallery")) {
		// include Flatnuke API to create archives
		include_once "forum/include/archive.php";
	} else fn_die("GALLERY","Security: Tried to zip the section \"$source_mod\" using zipgallery.php script",__FILE__,__LINE__);
}

// generate the name of the .zip file. Ex: Gallery/Summer --> Gallery-Summer.zip
$nome_d = str_replace("/", "-", $source_mod . ".zip");

// generate the zip file
$zip = new zip_file("/".$nome_d);
$zip->set_options(array('inmemory'=>"1",'overwrite'=>1,'recurse' => 0, 'storepaths' => 0, 'prepend','level','type'=>"zip"));

// add files to the zip archive
$zip->add_files("sections/" . $source_mod . "/*.jp*g");
$zip->add_files("sections/" . $source_mod . "/*.png");
$zip->add_files("sections/" . $source_mod . "/*.gif");
$zip->add_files("sections/" . $source_mod . "/*.bmp");

// create the new archive and send output to the browser
$zip->create_archive();
$zip->download_file();

?>
