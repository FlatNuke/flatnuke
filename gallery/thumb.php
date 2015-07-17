<?php

/**
 * Funzioni del modulo FlatImageGallery
 *
 * Le funzioni presenti in questo modulo di {@link http://www.flatnuke.org FlatNuke}
 * permettono di creare automaticamente una galleria di immagini.
 *
 * @package Funzioni_di_sistema
 *
 * @author Marco Segato <segatom@users.sourceforge.net> {@link http://marcosegato.altervista.org}
 * @version 20130210
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

/**
 * Genera l'anteprima di un'immagine
 *
 * Richiamata su un'immagine, ne genera un'anteprima in formato JPEG.
 *
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @since 2.5
 *
 * @param string $image Percorso dell'immagine relativo alla root
 * @param string $thumbsize Dimensione massima (px) dell'anteprima da realizzare
 */
function create_thumbnail($image, $thumbsize) {
	$size = getimagesize($image);

	// verifico di che tipo e' l'immagine d'origine
	switch ($size[2]) {
		case 1:
			$tmb = ImageCreateFromGif($image);
		break;
		case 2:
			$tmb = ImageCreateFromJpeg($image);
		break;
		case 3:
			$tmb = ImageCreateFromPng($image);
		break;
		default:
			$size[0] = $size[1] = 150;
			$tmb = ImageCreateTrueColor(150,150);
			$rosso = ImageColorAllocate($tmb,255,0,0);
			ImageString($tmb,5,10,10,"Not a valid",$rosso);
			ImageString($tmb,5,10,30,"GIF, JPEG or PNG",$rosso);
			ImageString($tmb,5,10,50,"image.",$rosso);
	}

	// calcolo delle dimensioni dell'immagine da creare
	if($size[0]<$thumbsize and $size[1]<$thumbsize) {
		$new_w = $size[0];
		$new_h = $size[1];
	}
	elseif($size[0]>$size[1]) {
		$new_w = $thumbsize;
		$new_h = $thumbsize * $size[1] / $size[0];
	}
	else {
		$new_w = $thumbsize * $size[0] / $size[1];
		$new_h = $thumbsize;
	}

	// creazione nuova immagine vuota
	$print_tmb = ImageCreateTrueColor($new_w,$new_h);

	// gestione trasparenze su sfondo bianco (RBG = 255-255-255)
	$trasparenza = ImageColorAllocate($print_tmb, 255, 255, 255);
	ImageFill($print_tmb, 0, 0, $trasparenza);

	// copio immagine di partenza sulla nuova con trasparenze su sfondo bianco
	ImageCopyResampled($print_tmb,$tmb,0,0,0,0,$new_w,$new_h,$size[0],$size[1]);

	// rilascio dei dati nel browser e pulizia della memoria
	ImageJpeg($print_tmb,null,'80');
	ImageDestroy($tmb);
	ImageDestroy($print_tmb);
}

/* --------------------------------------------------------------------------------------------*/

/*
 * Verifiche di sicurezza sui dati richiesti
 */
if(isset($_GET['image'])) $image = $_GET['image'];
	else $image = "";
if(isset($_GET['hw'])) $hw = $_GET['hw'];
	else $hw = "";

/*
 * Elaborazione dei dati
 */
include_once("../shared.php");
if(file_exists("../$image") AND !stristr($image,"..") AND (file_exists(dirname("../$image")."/gallery")) AND preg_match("/(jpg|jpeg|png|gif)/i",get_file_extension("../$image"))) {
	/* porto memoria (limitatamente a questo script) a 20mb per superare
	   impostazione di default nelle installazioni di PHP che e' 8mb */
	ini_set ("memory_limit","20M");
	create_thumbnail("../$image", $hw);
}

?>
