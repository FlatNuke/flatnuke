<?php 
/*
 * Flatnews: sistema di gestione delle news per Flatnuke
 *
 * Autore: Aldo Boccacci
 * sito web: www.aldoboccacci.it
 *
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
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


include_once("functions.php");
include_once("download/include/fdfunctions.php");

include_once("flatnews/include/news_functions.php");
include_once("flatnews/include/news_view.php");
if (_FN_IS_ADMIN or is_news_moderator()) include_once("flatnews/include/news_admin.php");

//POST action
$postaction = "";
$postaction = getparam("newsaction",PAR_POST,SAN_FLAT);
if ($postaction=="fnaddnewscomment"){
	$news = getparam("news",PAR_POST,SAN_FLAT);
	$post = getparam("newscomment",PAR_POST,SAN_NULL);
	$section = getparam("section",PAR_POST,SAN_FLAT);
	news_add_comment($section,$news,$post);
	return;
}
else if ($postaction=="savenews"){
// 	$news = getparam("news",PAR_POST,SAN_FLAT);
// 	$section = getparam("section",PAR_POST,SAN_FLAT);
	save_news_admin();
	return;
}
else if ($postaction=="deletenews"){
	$news = getparam("news",PAR_POST,SAN_FLAT);
	$section = getparam("section",PAR_POST,SAN_FLAT);
	delete_news($section,$news);
	return;
}
else if ($postaction=="movenews"){
	$section = getparam("section",PAR_POST,SAN_FLAT);
	$news = getparam("news",PAR_POST,SAN_FLAT);
	$destsection = getparam("destsection",PAR_POST,SAN_FLAT);
	move_news($section,$news,$destsection);
	return;
}
else if ($postaction=="fneditnewscomment"){
	$news = getparam("news",PAR_POST,SAN_FLAT);
	$section = getparam("section",PAR_POST,SAN_FLAT);
	$number = getparam("number",PAR_POST,SAN_FLAT);
	$newscomment = getparam("newscomment",PAR_POST,SAN_NULL);
	news_edit_comment($section,$news,$number,$newscomment);
	return;
}
else if ($postaction=="saveproposednews"){
	save_proposed_news();
	return;
}
else if ($postaction=="publishproposednews"){
	$section = getparam("section",PAR_POST,SAN_FLAT);
	$news = getparam("news",PAR_POST,SAN_FLAT);
	publish_proposed_news($section,$news);
	return;
}
else if ($postaction=="deleteproposednews"){
	$news = getparam("news",PAR_POST,SAN_FLAT);
	$section = getparam("section",PAR_POST,SAN_FLAT);
	delete_proposed_news($section,$news);
	return;
}

//GET action
$action="";
$action = getparam("action",PAR_GET,SAN_FLAT);
if ($action=="viewnews"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	view_news(get_mod(),$news);
}
else if ($action=="addcommentinterface"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	add_comment_interface(get_mod(),$news);
}
else if ($action=="editcommentinterface"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	$comment = getparam("comment",PAR_GET,SAN_FLAT);
	edit_comment_interface(get_mod(),$news,$comment);
}
else if ($action=="editnewsinterface"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	edit_news_interface(get_mod(),$news,"edit");
}
else if ($action=="addnewsinterface"){
	edit_news_interface(get_mod(),"","add");
}
else if ($action=="deletenewsinterface"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	delete_news_interface(get_mod(),$news);
}
else if ($action=="deletecomment"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	$comment = getparam("comment",PAR_GET,SAN_FLAT);
	delete_comment(get_mod(),$news,$comment);
}
else if ($action=="ontopnews"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	set_news_ontop(get_mod(),$news,TRUE);
}
else if ($action=="normalnews"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	set_news_ontop(get_mod(),$news,FALSE);
}
else if ($action=="hidenews"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	hide_news(get_mod(),$news,TRUE);
}
else if ($action=="shownews"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	hide_news(get_mod(),$news,FALSE);
}
else if ($action=="proposenewsinterface"){
	edit_news_interface(get_mod(),"","propose");
}
else if ($action=="manageproposednews"){
	manage_proposed_news_interface();
}
else if ($action=="viewproposednews"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	view_news(get_mod(),$news,TRUE);
}
else if ($action=="movenewsinterface"){
	$news = getparam("news",PAR_GET,SAN_FLAT);
	move_news_interface(get_mod(),$news);
}
else {
	if (get_mod()!="")
		view_news_section(get_mod());
	else view_news_section("none_News");
}


/**
 * Restituisce la cartella che contiene le news che devono essere visualizzate
 * in questo momento in relazione alla cartella in cui ci troviamo
 * 
 * @author Aldo Boccacci
 * @return la cartella da cui prendere le news in relazione alla sezione corrente
 */
function get_current_news_dir(){
	if (get_mod()!="")
		return get_fn_dir("sections")."/".get_mod()."none_newsdata/";
	else return get_fn_dir("sections")."/none_News/none_newsdata/";
}

?>