<?php 
$modname = getparam("mod",PAR_GET,SAN_FLAT);
$myurl   = "index.php?mod=$modname&amp;file=";
?>

<b>Se sei entrato qui sei un amministratore</b>! Controlla il file level.php in questa sezione!!
<br/><br/>
Questa e' una sottosezione, anche qui e' possibile utilizzare la variabile <b>$myurl</b>.
<br/>
Il metodo da utilizzare e' sempre lo stesso: <a href="<?php echo $myurl?>none_paperino.txt">paperino</a>

