<?php 
$modname = getparam("mod", PAR_GET, SAN_FLAT);
$myurl   = "index.php?mod=$modname&amp;file=";
?>

Questa e' una sottosezione, anche qui e' possibile utilizzare la variabile <b>$myurl</b>.
<br/>
Il metodo da utilizzare e' sempre lo stesso: <a href="<?php echo $myurl?>none_pluto.txt">altro file</a>

