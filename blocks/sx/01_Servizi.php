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

if (preg_match("/servizi.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    die();
}

if (!(_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR)){
	//se siamo nella home
	if (_FN_MOD=="")
		echo "&#187;&nbsp;<a href=\"index.php?mod=none_News&amp;action=proposenewsinterface\" title=\""._GOTOSECTION.": "._SEGNEWS."\">"._SEGNEWS." (home&nbsp;page)</a><br>";
	else {
		$modshown = preg_replace("/^none_/","",basename(_FN_MOD));
		$modshown = preg_replace("/^[0-9][0-9]_/","",$modshown);
		//stampo il link solo se è una sezione news
		if (file_exists(_FN_SECTIONS_DIR."/"._FN_MOD."/news"))
			echo "&#187;&nbsp;<a href=\"index.php?mod="._FN_MOD."&amp;action=proposenewsinterface\" title=\""._GOTOSECTION.": "._SEGNEWS."\">"._SEGNEWS." ($modshown)</a><br>";
	}
}
if (user_can_view_section("none_Search"))
	echo "&#187;&nbsp;<a href=\"index.php?mod=none_Search\" title=\""._GOTOSECTION.": "._CERCA."\">"._CERCA."</a><br>";
if (!_FN_IS_GUEST)
	echo "&#187;&nbsp;<a href=\"index.php?mod=none_Login&amp;action=viewmembers\" title=\""._FUTENTI."\">"._FUTENTI."</a>";

?>