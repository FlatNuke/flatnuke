<?php

/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003 by Simone Vellei                                  */
/* http://flatnuke.sourceforge.net                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/


if (preg_match("/cerca.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}

// configuration
$search_plugins_dir = "include/search/";
$GLOBALS['search_plugins_dir'] = $search_plugins_dir;
$search_section = "none_Search";

?>
	<script type="text/javascript">
	function validate_search_block()
		{
			if(document.getElementById('findblock').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CERCA?>');
					document.getElementById('findblock').focus();
					document.getElementById('findblock').value='';
					return false;
				}
			else return true;
		}
	</script>

<div style="text-align:center; margin-bottom:0.5em;">
<form action="index.php?mod=<?php echo $search_section?>" method="post" onsubmit="return validate_search_block()">
	<input type="hidden" name="method" value="AND" />
	<input type="hidden" name="mod" value="<?php echo $search_section?>" />
	<input type="hidden" name="where" value="allsite" />
	<input type="text" name="find" size="10" id="findblock" />
	<input type="submit" value="<?php echo _CERCA?>" />
</form>
</div>
<div style="margin-bottom:0.5em;">
<?php echo _SEARCHDESC?>
</div>
<div style="text-align:center">
<a href="index.php?mod=<?php echo $search_section?>" title="<?php echo _GOTOSECTION?>: <?php echo _CERCA?>"><?php echo _ADVSEARCH?></a>
</div>
