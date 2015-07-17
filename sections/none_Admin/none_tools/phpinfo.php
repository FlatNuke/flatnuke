<?php 

// rebuild path and include FN APIs
chdir("../../../");
include_once("functions.php");

// only admin can access these infos
if(is_admin()) {
	phpinfo();
} else {
	// log the attempt
	$ip = getparam("REMOTE_ADDR", PAR_SERVER, SAN_NULL);
	fnlog("Security", "$ip||".get_username()."||Tried to access PHPINFO page.");
	echo "<div><h2>Reserved area: keep out!</h2></div>";
	return;
}

?>
