<?php 
$modname = getparam("mod", PAR_GET, SAN_FLAT);
$myurl   = "index.php?mod=$modname&amp;file=";
?>

Questa e' una sezione di prova: per visualizzare un file allo stesso livello
della directory si usa il link <a href="<?php echo $myurl?>none_pippo.txt">il mio link</a>.
<br/>
Viene infatti utilizzata la variabile <b>$myurl</b> per far puntare
l'indirizzo di riferimento alla rispettiva sezione.
<br/><br/>
Come al solito, e' presente anche la lista delle sottosezioni.
<br/>
<?php 
//esempio di verifica di un amministratore
if(is_admin()) {
   print "<br><b>Sei amministratore!!!</b>";
}
?>
