<?php

/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003-2004 by Simone Vellei                             */
/* http://flatnuke.sourceforge.net                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

//torno alla root
chdir("../");
include "config.php";


// language definition by configuration or by cookie
include_once("functions.php");
$userlang = getparam("userlang", PAR_COOKIE, SAN_FLAT);
if ($userlang!="" AND is_alphanumeric($userlang) AND file_exists("languages/$userlang.php")) {
	$lang = $userlang;
}
switch($lang) {
	case "de" OR "es" OR "fr" OR "it" OR "pt":
		include_once ("languages/$lang.php");
	break;
	default:
		include_once ("languages/en.php");
}

// theme definition by configuration or by cookie
$usertheme = getparam("usertheme", PAR_COOKIE, SAN_FLAT);
if ($usertheme!="" AND !stristr("..",$usertheme) AND is_dir("themes/$usertheme")) {
	$theme = $usertheme;
}

include "themes/$theme/theme.php";


// start HTML headers
if(function_exists("theme_doctype")) {
	$doctype = theme_doctype();
} else $doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";	// default HTML 4.01 doctype
// define close tag for XHTML doctype
if(preg_match("/DTD HTML/i", $doctype)) {
	$close_tag = "";
} elseif(preg_match("/DTD XHTML/i", $doctype)) {
	$close_tag = " /";
}

echo $doctype;

?>

<html lang="<?php echo $lang?>">
<head><title>Help bbcode</title>
<?php

// start HTML headers
if(function_exists("theme_doctype")) {
	$doctype = theme_doctype();
} else $doctype = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";	// default HTML 4.01 doctype
// define close tag for XHTML doctype
if(preg_match("/DTD HTML/i", $doctype)) {
	$close_tag = "";
} elseif(preg_match("/DTD XHTML/i", $doctype)) {
	$close_tag = " /";
}
// chdir("../");
// declaration of all default StyleSheets provided by the system
$path_css_sys = "include/css";
if(file_exists($path_css_sys)) {
	$dir_css_sys = opendir($path_css_sys);
	$file_css_sys = 0;
	while ($filename_css_sys = readdir($dir_css_sys)) {
		if(preg_match('/[\.]css$/', $filename_css_sys) AND $filename_css_sys!="." AND $filename_css_sys!=".." AND !preg_match("/^none_/i", $filename_css_sys)) {
			$array_css_sys[$file_css_sys] = $filename_css_sys;
			$file_css_sys++;
		}
	}
	closedir($dir_css_sys);
	for($i=0; $i<$file_css_sys; $i++) {
		echo "\n<link rel='StyleSheet' type='text/css' href='$path_css_sys/$array_css_sys[$i]'$close_tag>";
	}
}
// declaration of all StyleSheets provided by the theme in use
$path_css_thm = "themes/$theme";
if(file_exists($path_css_thm)) {
	$dir_css_thm = opendir($path_css_thm);
	$file_css_thm = 0;
	while ($filename_css_thm = readdir($dir_css_thm)) {
		if(preg_match('/[\.]css$/', $filename_css_thm) AND $filename_css_thm!="." AND $filename_css_thm!=".." AND !preg_match("/^none_/i", $filename_css_thm)) {
			$array_css_thm[$file_css_thm] = $filename_css_thm;
			$file_css_thm++;
		}
	}
	closedir($dir_css_thm);
	for($i=0; $i<$file_css_thm; $i++) {
		echo "\n<link rel='StyleSheet' type='text/css' href='../$path_css_thm/$array_css_thm[$i]'$close_tag>";
	}
}


?>


</head>
<body style="background-color: <?php echo $bgcolor1; ?>">
<!-- <br><br> -->

<div style="text-align:left;">
<div class="content">


<h3>Flatnuke Forum Help</h3>

<h4>Emoticons</h4>

<p>
Le emoticon sono una sequenza di caratteri che vogliono simboleggiare gli stati d'animo della persona che scrive. Sono il frutto della fantasia e della necessit&agrave; di rappresentare in maniera semplice, intuitiva e diretta le emozioni, da qui l'origine della parola: <b>emoticon = emotion + icon</b>.<br>
&Egrave; possibile utilizzare le famose emoticon all'interno dei messaggi del forum e nelle news, cliccando semplicemente sulla emoticon che ci interessa, nella barra superiore al corpo del messaggio, oppure utilizzando i caratteri corrispondenti alle icone:
</p>

<br>

<ul style="list-style:none;float:left; width:40%;">
<li>
<img src="emoticon/01.png" alt="felice" />&nbsp;<b>felice</b><br>
&nbsp;[:)]<br><br>
</li>
<li>
<img src="emoticon/02.png" alt="triste" />&nbsp;<b>triste</b><br>
&nbsp;[:(]<br><br>
</li>
<li>
<img src="emoticon/03.png" alt="sorpresa" />&nbsp;<b>sorpresa</b><br>
&nbsp;[:o]<br><br>
</li>
<li>
<img src="emoticon/04.png" alt="linguaccia" />&nbsp;<b>linguaccia</b><br>
&nbsp;[:p]<br><br>
</li>
<li>
<img src="emoticon/05.png" alt="risata" />&nbsp;<b>risata</b><br>
&nbsp;[:D]<br><br>
</li>
</ul>

<ul style="list-style:none;float:right; width:40%;">
<li>
<img src="emoticon/06.png" alt="indifferente" />&nbsp;<b>indifferente</b><br>
&nbsp;[:!]<br><br>
</li>
<li>
<img src="emoticon/07.png" alt="sbalordito" />&nbsp;<b>sbalordito</b><br>
&nbsp;[:O]<br><br>
</li>
<li>
<img src="emoticon/08.png" alt="fighetto" />&nbsp;<b>fighetto</b><br>
&nbsp;[8)]<br><br>
</li>
<li>
<img src="emoticon/09.png" alt="occhiolino" />&nbsp;<b>occhiolino</b><br>
&nbsp;[;)]<br><br>
</li>
</ul>

<div style="clear:both;"></div>

<h4>Formattazione del testo</h4>
<p>&Egrave; inoltre possibile formattare e personalizzare il colore del nostro messaggio. Vediamo come:<br>se clicchiamo sulle icone colorate poste nella barra superiore del corpo del messaggio, appariranno dei codici chiusi tra parentesi quadra, baster&agrave; digitare il nostro testo in mezzo ai due codici e otterremmo la nostra preferenza, come mostra l'esempio sottostante:</p>

<ul style="list-style:none;float:left; width:40%;">
<li>
<img alt="Bold" style="float:left" src="emoticon/bold.png" />&nbsp;<b>Bold</b><br>
[b][/b]<br><br>
</li>
<li>
<img alt="Italic" style="float:left" src="emoticon/italic.png" />&nbsp;<b>Italic</b><br>
[i][/i]<br><br>
</li>
<li>
<img alt="Underline" style="float:left" src="emoticon/underline.png" />&nbsp;<b>Underline</b><br>
[u][/u]<br><br>
</li>
<li>
<img alt="Strike" style="float:left" src="emoticon/strike.png" />&nbsp;<b>Strike</b><br>
[strike][/strike]<br><br>
</li>
<li>
<img alt="Left" style="float:left" src="emoticon/left.png" />&nbsp;<b>Left</b><br>
[left][/left]<br><br>
</li>
<li>
<img alt="Center" style="float:left" src="emoticon/center.png" />&nbsp;<b>Center</b><br>
[center][/center]<br><br>
</li>
<li>
<img alt="Right" style="float:left" src="emoticon/right.png" />&nbsp;<b>Right</b><br>
[right][/right]<br><br>
</li>
<li>
<img alt="Justify" style="float:left" src="emoticon/justify.png" />&nbsp;<b>Justify</b><br>
[justify][/justify]<br><br>
</li>
</ul>



<ul style="list-style:none; float:right;width:40%;">
<li>
<img alt="Red" style="float:left" src="emoticon/red.png" />&nbsp;<b>Red</b><br>
[red]ciao[/red] = <span style="color : #ff0000">ciao</span><br><br>
</li>
<li>
<img alt="Green" style="float:left" src="emoticon/green.png" />&nbsp;<b>Green</b><br>
[green]ciao[/green] = <span style="color : #00ff00">ciao</span><br><br>
</li>
<li>
<img alt="Blue" style="float:left" src="emoticon/blue.png" />&nbsp;<b>Blue</b><br>
[blue]ciao[/blue] = <span style="color : #0000ff">ciao</span><br><br>
</li>
<li>
<img alt="Pink" style="float:left" src="emoticon/pink.png" />&nbsp;<b>pink</b><br>
[pink]ciao[/pink] = <span style="color : #ff00ff">ciao</span><br><br>
</li>
<li>
<img alt="Yellow" style="float:left" src="emoticon/yellow.png" />&nbsp;<b>Yellow</b><br>
[yellow]ciao[/yellow] = <span style="color : #ffff00">ciao</span><br><br>
</li>
<li>
<img alt="Cyan" style="float:left" src="emoticon/cyan.png" />&nbsp;<b>Cyan</b><br>
[cyan]ciao[/cyan] = <span style="color : #00ffff">ciao</span><br><br>
</li>
</ul>

<div style="clear:both;"></div>

<ul style="list-style:none; float:left;width:40%;">
<li>
<img alt="Olist" style="float:left" src="emoticon/olist.png" />&nbsp;<b>Olist</b><br>
Crea una lista ordinata di elementi<br>
[ol]<br>
[*]Elemento1[/*]<br>
[*]Elemento2[/*]<br>
[*]Elemento3[/*]<br>
[/ol]<br><br>
</li>
<li>
<img alt="Ulist" style="float:left" src="emoticon/ulist.png" />&nbsp;<b>Ulist</b><br>
Crea una lista non ordinata di elementi<br>
[ul]<br>
[*]Elemento1[/*]<br>
[*]Elemento2[/*]<br>
[*]Elemento3[/*]<br>
[/ul]<br><br>
</li>
</ul>

<ul style="list-style:none; float:right;width:40%;">
<li>
<select>
<option value="50%" >50%</option>
<option value="75%" >75%</option>
<option value="100%" selected="selected">100%</option>
<option value="150%" >150%</option>
<option value="200%" >200%</option>
</select>&nbsp;<b>Dimensione</b><br>
Permette di impostare le dimensioni del testo selezionato.<br>
[size=50%]testo[/size]<br>
[size=75%]testo[/size]<br>
[size=100%]testo[/size]<br>
[size=150%]testo[/size]<br>
[size=200%]testo[/size]<br>
<br><br>
</li>
</ul>

<div style="clear:both;"></div>

<br>
...o fare combinazioni:
<br><br>
[red][b][i]ciao[/i][/b][/red] = <b><i><span style="color : #ff0000">ciao</span></i></b>
<br><br>


<h4>Tag speciali</h4>
<ul style="list-style:none;">
<li><p>Oltre alla formattazione del testo, abbiamo altre possibilit&agrave; di rendere ancora pi&ugrave; ricchi i nostri post con le seguenti funzioni:<br><br></li>
<li>
<img alt="Quote" style="float:left" src="emoticon/quote.png" />&nbsp;<b>Quote</b><br>
&nbsp;[quote]Testo da quotare[/quote] = Viene utilizzato per riportare parte del post di un altro utente<br><br>
</li>
<li>
<img alt="Code" style="float:left" src="emoticon/code.png" />&nbsp;<b>Code</b><br>
&nbsp;[code]code[/code] = Permette di inserire del codice di programmi, mantenendo la formattazione del testo<br><br>
</li>
<li>
<img alt="Image" style="float:left" src="emoticon/image.png" />&nbsp;<b>Image</b><br>
&nbsp;[img]http://url.to.image.ext[/img] = Permette di inserire una immagine esterna<br><br>
</li>
<li>
<img alt="Email" style="float:left" src="emoticon/mail.png" />&nbsp;<b>Email</b><br>
&nbsp;[mail]me@provider.ext[/mail] = Crea un link HTML dell'indirizzo email specificato<br><br>
</li>
<li>
<img alt="Url" style="float:left" src="emoticon/url.png" />&nbsp;<b>Url</b><br>
&nbsp;[url=http://www.flatnuke.org] Nome del link[/url] = Crea un link HTML dell'indirizzo specificato<br><br>
</li>
<li>
<img alt="Wikipedia" style="float:left" src="emoticon/wikipedia.png" />&nbsp;<b>Wikipedia</b><br>
&nbsp;[wp lang=it]parola[/wp] = Permette di inserire un link a Wikipedia per la parola inserita<br><br>
</li>
</ul>


<h4>Amministrazione</h4>
<p>
Oltre a tutte le possibilit&agrave; sopra descritte, gli amministratori del sito hanno la possibilit&agrave; di usare anche codice HTML (solo per le news).
</p>

</div>
</div>
</body>
</html>