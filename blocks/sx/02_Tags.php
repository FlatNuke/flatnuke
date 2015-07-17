<?php 
include_once("flatnews/include/news_functions.php");

/* INIZIO CONFIGURAZIONE */

// numero massimo di tag da mostrare oltre il quale vengono tagliati i tag meno utilizzati
// (0 = nessun limite ai tag da mostrare)
$taglimit = 0;

// Valori:
// size: i tag più utilizzati hanno dimensione maggiore (default)
// list: i tag sono mostrati in un elenco con a fianco il numero di utilizzi
$tags_show = "size";

// Valori:
// ab: mostra i tag in ordine alfabetico (default)
// 12: mostra i tag dal più utilizzato a scendere
$sort_tags = "ab";

/* FINE CONFIGURAZIONE */

$tagssection = "none_News"; // set default news section

if (file_exists(_FN_SECTIONS_DIR."/".get_mod()."/news")) // if in a news section...
	$tagssection=get_mod(); // ... store current section instead

if (!file_exists(_FN_SECTIONS_DIR."/".$tagssection."/tags_list.php")){ // search tags_list of current news section
	echo "Tag non presenti"; // localization needed
	return;
}


$tags = load_tags_list($tagssection);

// Questo codice è parzialmente tratto da Wordpress 2.2
// WP CODE
	$largest = 25;
	$smallest = 8;
	if (count($tags)>0)
		$min_count = min( $tags );
	else $min_count = 1;
	if (count($tags)>0)
		$spread = max( $tags ) - $min_count;
	else $spread = 1;
	if ( $spread <= 0 )
		$spread = 1;
	$font_spread = $largest - $smallest;
	if ( $font_spread < 0 )
		$font_spread = 1;
	//il +1 al denominatore è stato aggiunto per evitare di avere caratteri
	//troppo grandi
	$font_step = $font_spread / ($spread+1);
//FINE WP CODE
//se il numero di tag è superiore al limite impostato
if (count($tags)>$taglimit AND $taglimit!=0){
	//ordino l'array dal value più grande al più piccolo
	//(senza perdere l'associazione con la kay)
	arsort($tags);
	$tagcount=0;
	foreach ($tags as $tag => $count){
		//se raggiungo o supero il limite dei tag
		if ($tagcount>=$taglimit)
			break;
		//ricostruisco la struttura dell'array
		$newarray[$tag] = $count;
		$tagcount++;
	}
	//reimposto l'elenco dei tags
	$tags = $newarray;
	//elimino l'array temporaneo
	unset($newarray);
	//riordino sul nome dei tag
	ksort($tags);
}
//se è impostato l'ordinamento secondo il numero di utilizzi eseguo un asort
if ($sort_tags=="12")
	arsort($tags);

foreach ($tags as $tag => $count){
	$fontsize = $smallest + ( ( $count - $min_count ) * $font_step );

	if ($count>0){
		if ($tags_show=="list"){
			if (strlen($tag)>11) $tagshow=substr($tag,0,8)."...";
			else $tagshow = $tag;
			echo "&#187;&nbsp;<a href=\"index.php?mod=none_Search&amp;where=news&amp;tags=$tag\" title=\"$tag: $count  news\">$tagshow ($count)</a><br>";
		}
		else {
			if ($fontsize> 25) $fontsize=25;
			if (strlen($tag)>8 and $fontsize>20) $tagshow=substr($tag,0,6)."...";
			else if (strlen($tag)>10 and $fontsize>16) $tagshow=substr($tag,0,7)."...";
			else if (strlen($tag)>14 and $fontsize>14) $tagshow=substr($tag,0,11)."...";
			else if (strlen($tag)>18) $tagshow=substr($tag,0,15)."...";
			else $tagshow = $tag;
			echo "<span style=\"font-size: ".$fontsize."pt;\"><a href=\"index.php?mod=none_Search&amp;where=news&amp;tags=$tag\" title=\"$tag: $count  news\">$tagshow</a> </span>";
	
		}
	}
}

?>