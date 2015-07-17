<?php

/**
 * Flatnews: il nuovo gestore di news di Flatnuke 3.0
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

if (preg_match("/news_view\.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    flatnews_die("You cannot call news_view.php!",__FILE,__LINE);
}

/**
 * Visualizza l'header di una singola news. Da usare nel riepilogo presente nella sezione news
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news
 * @author Aldo Boccacci
 * @since 0.1
 *
 */
function view_news_header($section,$news){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	$news = getparam($news,PAR_NULL,SAN_NULL);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_file($newsfile)) return;

// 	if (!user_can_view_news($section,$news)) return;

	$data = load_news_header($section,$news);

	if(defined('_THEME_VER')) {
			if(_THEME_VER > 0)
				$ntitle = $data['title'];
	} else $ntitle = "<img src=\"themes/$theme/images/news.png\" alt=\"News\" />&nbsp;".$data['title'];

	//controllo se è nascosta
	if (news_is_hidden($news))
		$ntitle = "<span style=\"color : #ff0000; text-decoration : line-through;\">$ntitle</span>";
	//se la news è in evidenza metto un'icona accanto al titolo
	$ontopstring="";
	if (news_is_ontop($news))
		$ontopstring = _ICONONTOP."&nbsp;";
	//se esiste uso la nuova funzione per aprire le news in home
	if (function_exists("OpenNewsHeader"))
		OpenNewsHeader($ontopstring.$ntitle);
	else OpenTableTitle($ontopstring.$ntitle);
	$w3c_title = _ARGOMENTO.": ".preg_replace("/\.png$|\.gif$|\.jpeg$|\.jpg$/i","",$data['category']);
	echo "<a href=\"index.php?mod=none_Search&amp;where=news&amp;category=".$data['category']."\">";
	if(file_exists("images/news/".$data['category'])) {
		echo "<img src=\"images/news/".$data['category']."\" style='padding-left:10px;padding-bottom:10px;float:right;' alt=\"$w3c_title\" title=\"$w3c_title\" />";
	}
	else if ($data['category']=="nonews.png")
		echo "<img src=\"images/nonews.png\" style='padding-left:10px;padding-bottom:10px; float:right' alt=\"$w3c_title\" title=\"$w3c_title\" />";
	else echo "<div style='text-align:right;padding-left:10px;padding-bottom:10px;'>$w3c_title</div>";
	echo "</a>";
	//se ci sono dei tag da mostrare
	if (count($data['tags'])>0){
		echo "<b>Tags:</b> ";
	// 	print_r($data['tags']);
		for ($n=0;$n<count($data['tags']);$n++){
			if (trim($data['tags'][$n])!=""){
				echo " <a href=\"index.php?mod=none_Search&amp;where=news&amp;tags=".$data['tags'][$n]."\" title=\""._SEARCHTAG."\">".$data['tags'][$n]."</a>";
				if ($n<(count($data['tags'])-1))
					echo ",";
			}
		}
		echo "<br><br>";
	}
// 	echo "<br>";
// 	if (count($data['tags'])>0)
// 		echo "<div style=\" height: auto !important;height: 30px; min-height: 30px;\">";
// 	else echo "<div style=\" height: auto !important;height: 60px; min-height: 60px;\">";
	echo stripslashes(tag2html($data['header']));

	//creo i link per i siti sociali
	echo "<div class='social-links' style='text-align:right;margin-left:10px;margin-bottom:10px;/*border:1px;border-left-style: solid; border-bottom-style: solid;border-color: #d5d6d7;*/'>";
// 	echo "<div class='social-links'>";
	create_social_links($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']."?action=viewnews&news=".$news,_FN_TITLE.": ".$data['title']);
	echo "</div>";

// 	echo "</div>";
	create_footer_news($section,$news,$data);
	if (function_exists("CloseNewsHeader"))
		CloseNewsHeader();
	else CloseTableTitle($newsfile);
}

/**
 * Visualizza la sezione news.
 *
 * @param string $section la sezione di cui visualizzare la news
 * @author Aldo Boccacci
 * @since 0.1
 *
 */
function view_news_section($section){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!check_path($section,"",false)) flatnews_die("\$section is not valid!".strip_tags($section),__FILE__,__LINE__);
	$pag = getparam("pag",PAR_GET,SAN_FLAT);
	global $newspp, $theme;
	// redefine this function
	if (file_exists("include/redefine/view_news_section.php")){
		include("include/redefine/view_news_section.php");
		return;
	}
	$mod = _FN_MOD;
	if ((_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR) and $mod!="" and $mod!="none_News"){
		if (is_writable(get_news_dir($mod))){
			echo "<div style=\"text-align:center;\"><a href=\"index.php?";
			if (_FN_MOD!="") echo "mod="._FN_MOD."&amp;";
			echo "action=addnewsinterface\" title=\""._INSNOTIZIA."\"><b>"._INSNOTIZIA."</b></a></div>";

		 }
		 else {
			echo "<div style=\"text-align:center; color : #ff0000; text-decoration : line-through;\"><b>"._INSNOTIZIA."</b></div>";
		 }
	}

// 	echo "SECTION $section";
	if (_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR)
		$newslist = load_news_list($section,TRUE);
	else $newslist = load_news_list($section,FALSE);
	//uso load_news_list per incrementare le prestazioni
// 		$newslist = list_news($section,"true","true");
// 	else $newslist = list_news($section,"true","false");

	// calculate current page limits
	$totpages = (count($newslist)%$newspp!=0) ? (intval(count($newslist)/($newspp)+1)) : (intval(count($newslist)/$newspp));
	if( !is_numeric($pag) OR $pag<1 OR $pag>$totpages ) {
		$pag   = 1;
		$first = 0;
	} else $first = ($pag * $newspp) - ($newspp);


	for ($count=$first;  $count<count($newslist) AND $count<($first+$newspp); $count++){
// 		$data = load_news(preg_replace("/\.php$/i","",$newslist[$count]));
		view_news_header($section,$newslist[$count]);
// 	echo preg_replace("/\.php$/i","",$newslist[$count])."<br>";
	}


	$mod = _FN_MOD;
	// print links to other pages
	if($totpages > 1) {
		echo "<div style='font-size:0.8em; padding: 30px 5px 10px 5px;'>";
		if($pag > 1) {
			echo "<div style='float:left'>";
			echo "<a href=\"index.php?";
			if ($mod!="") echo "mod=$mod&amp;";
			echo "pag=".($pag-1)."\" title=\""._GOTOTHEPREVIOUSPAGE."\">&#171; "._GOTOTHEPREVIOUSPAGE."</a>";
			echo "</div>";
		}
		if($pag < $totpages) {
			echo "<div style='float:right'>";
			echo "<a href=\"index.php?";
			if ($mod!="") echo "mod=$mod&amp;";
			echo "pag=".($pag+1)."\" title=\""._GOTOTHENEXTPAGE."\">"._GOTOTHENEXTPAGE." &#187;</a>";
			echo "</div>";
		}
		echo "</div>";
	}
	if (($mod!="" or ($mod!="none_News" and _FN_MOD!=""))
		and file_exists(_FN_SECTIONS_DIR."/".$mod."/feed.xml"))
	echo "<div style=\"text-align:right;margin:5px 0 5px 0;\">
		<a href=\""._FN_SECTIONS_DIR."/".$mod."/feed.xml\" target=\"_blank\" title=\"News feed\"><img src=\"images/rss.png\" alt=\"News feed\" height=\"32\" width=\"32\"/></a>
	</div>";
}

/**
 * Visualizza una singola news completa.
 *
 * @param string $section la sezione della news
 * @param string $news il file contenente la news
 * @param boolean $proposed se settato a TRUE visualizza una notizia proposta da un utente, FALSE
 * 		  una notizia normale
 * @author Aldo Boccacci
 * @since 0.1
 *
 */
function view_news($section, $news, $proposed=FALSE){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$proposed = getparam($proposed,PAR_NULL,SAN_FLAT);
	if (!check_var($proposed,"boolean")){
		$proposed = FALSE;
		flatnews_logf("\$proposed must be boolean!",__FILE__,__LINE__);
	}
	//se $proposed è settato a TRUE carico il file dalla cartella dei file proposti
	if ($proposed==TRUE)
		$newsfile = get_proposed_news_file($section,$news);
	else $newsfile = get_news_file($section,$news);

	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_file($newsfile)) return;

	if (!user_can_view_news($section,$news)) {
		global $theme;
		OpenTable("");
		print("<div class=\"centeredDiv\"><b>"._NOLEVELSECT."</b></div><br><br>");
		if (file_exists("themes/$theme/fn_denied.php")) {
			include(stripslashes("themes/$theme/fn_denied.php"));
		} else {
			echo "<div style=\"text-align:center;\"><img src=\"images/fn_denied.png\" alt=\""._NOLEVELSECT."\" title=\""._NOLEVELSECT."\"/></div>";
		}
		CloseTable();
		return;
	}

	$mod= _FN_MOD;
	if ($proposed==TRUE)
		$data = load_news($section,$news,TRUE);
	else $data = load_news($section,$news);

	$modstring ="";
	if ($mod!="") $modstring="mod=$mod&amp;";

	global $theme, $mesi, $giorni, $fuso_orario, $newspp;
	$news    = getparam($news,     PAR_NULL,   SAN_FLAT);
	// check if the news really exists
	if(!file_exists($newsfile)) {
		print("<div class=\"centeredDiv\"><b>"._NORESULT."</b></div>");
		return;
	}
	// add 1 visit to the news (if you aren't admin or moderator)
	if (!_FN_IS_ADMIN and !_FN_IS_NEWS_MODERATOR)
		news_add_read($section,$news);
	// read news contents
	$title    = $data['title'];
	$category = $data['category'];
	$header   = $data['header'];
	$body     = $data['body'];
	// print news
	if (preg_match("/^hide_/i",$news))
		$title = "<span style=\"color : #ff0000; text-decoration : line-through;\">$title</span>";
	//controllo se la news è nascosta
	if(defined('_THEME_VER')) {
		if(_THEME_VER > 0){
			//se esiste uso la nuova funzione per aprire le news
			if (function_exists("OpenNews"))
				OpenNews(news_is_ontop($news) ? _ICONONTOP."&nbsp;".$title : _ICONREAD."&nbsp;".$title);
			else OpenTableTitle(news_is_ontop($news) ? _ICONONTOP."&nbsp;".$title : _ICONREAD."&nbsp;".$title);
		}
	}
	else {
		//se esiste uso la nuova funzione per aprire le news
		if (function_exists("OpenNews"))
			OpenNews(news_is_ontop($news) ? _ICONONTOP."&nbsp;".$title : _ICONREAD."&nbsp;".$title);
		else OpenTableTitle(news_is_ontop($news) ? _ICONONTOP."&nbsp;".$title : _ICONREAD."&nbsp;".$title);

	}
	// topic
	$w3c_title = _ARGOMENTO.": ".preg_replace("/\.png$|\.gif$|\.jpeg$|\.jpg$/i","",$category);
	echo "<a href=\"index.php?mod=none_Search&amp;where=news&amp;category=".$data['category']."\">";
	if(file_exists("images/news/".$data['category'])) {
		echo "<img src=\"images/news/".$data['category']."\" alt=\"$w3c_title\" title=\"$w3c_title\" style=\"float:right\" />";
	}
	else if ($data['category']=="nonews.png")
		echo "<img src=\"images/nonews.png\" alt=\"$w3c_title\" title=\"$w3c_title\" style=\"float:right\" />";
	else echo "<div style='text-align:right;padding-bottom:5px;'>$w3c_title</div>";
	echo "</a>";
	//se ci sono dei tag da mostrare
	if (count($data['tags'])>0){
		echo "<b>Tags:</b> ";
	// 	print_r($data['tags']);
		for ($n=0;$n<count($data['tags']);$n++){
			if (trim($data['tags'][$n])!=""){
				echo " <a href=\"index.php?mod=none_Search&amp;where=news&amp;tags=".$data['tags'][$n]."\" title=\""._SEARCHTAG."\">".$data['tags'][$n]."</a>";
				if ($n<(count($data['tags'])-1))
					echo ",";
			}
		}
	}
echo "<br><br>";


	print stripslashes(tag2html($header))."<br><br>".stripslashes(tag2html($body));
	echo "<div class='social-links' style='text-align:right;margin-left:10px;margin-bottom:10px;/*border:1px;
border-left-style: solid; border-bottom-style: solid;border-color: #d5d6d7;*/'>";
// 	echo "<div class='social-links'>";
	create_social_links($_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI'],_FN_TITLE);
	echo "</div>";
	// footer
	echo "<div class='footnews'>\n";
	$news_link = get_news_link_array($section,$news,$data);
	echo $news_link['news_infos']."<br>";
	if ($proposed==FALSE){
		echo $news_link['link_comment']." ";
		echo $news_link['link_print']." ";
	}
	if (_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR) {
		if ($proposed==FALSE){
			echo $news_link['link_modify']." ";
			echo $news_link['link_delete']." ";
			if (news_is_ontop($news)) echo $news_link['link_normal']." ";
			else echo $news_link['link_ontop']." ";
			if (news_is_hidden($news)) echo $news_link['link_show']." ";
			else echo $news_link['link_hide']." ";
			echo $news_link['link_move'];
		}
		else if ($proposed==TRUE){
			$modstringpost="";
			if (_FN_MOD!="") $modstringpost = "?mod="._FN_MOD;
			else $modstringpost = "?mod=none_News";
			echo "<div align=\"center\"><br><form action=\"index.php$modstringpost\" method=\"POST\">
			<input type=\"hidden\" name=\"newsaction\" value=\"publishproposednews\" readonly=\"readonly\">
			<input type=\"hidden\" name=\"section\" value=\"$section\" readonly=\"readonly\">
			<input type=\"hidden\" name=\"news\" value=\"$news\" readonly=\"readonly\">
			<input type=\"SUBMIT\" value=\""._FDPUBLISH."\"></form></div>";

			echo "<div align=\"center\"><br><form action=\"index.php$modstringpost\" method=\"POST\">
			<input type=\"hidden\" name=\"newsaction\" value=\"deleteproposednews\" readonly=\"readonly\">
			<input type=\"hidden\" name=\"section\" value=\"$section\" readonly=\"readonly\">
			<input type=\"hidden\" name=\"news\" value=\"$news\" readonly=\"readonly\">
			<input type=\"SUBMIT\" value=\""._FDDELETE."\"></form></div>";

		}
	}
	echo "</div>\n";
	// comments
	echo "<h3 id=\"comments\" >"._COMMENTI."</h3>";
	echo $news_link['link_addcomment'];
// 	echo "<a href=\"#addcomment\" title=\""._ADDCOMM."\">"._ADDCOMM."</a><br>";
	$comments=$data['comments'];
	for($count_comment=0;$count_comment<count($comments);$count_comment++){
		print "<br>";
		$user=$comments[$count_comment]['cmby'];
		print "<div class='comment' style='height: auto !important;height: 100px; min-height: 100px;'>";
		// avatar into the comment
		if(file_exists(_FN_USERS_DIR."/$user.php")){
			$userdata = array();
			$userdata = load_user_profile($user);
			$img=$userdata['avatar'];
			if($img!=""){
				if(!stristr($img,"http://"))
					echo "<img src='forum/".$img."' alt='avatar' class=\"avatar\" />";
				else
					echo "<img src='".$img."' alt='avatar' class=\"avatar\" />";
			} else echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
		} else echo "<img src='forum/images/blank.png' alt='avatar' class=\"avatar\" />";
		// comment author
		if (trim($user!="")){
			if (file_exists(_FN_USERS_DIR."/$user.php"))
				print "<b>"._DA."</b> <a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=$user\" title=\""._VIEW_USERPROFILE."\">$user</a>  ";
			else print "<b>"._DA."</b> $user  ";
		}
		else print "<b>"._DA."</b> <i>"._GUEST."</i>  ";
		// comment time
		$date_comment = $comments[$count_comment]['cmdate'];
// 		echo $date_comment; print_r($comments[$count_comment]);
		if(is_numeric($date_comment)){
			print "<b>"._DATA.":</b> ";
			echo $giorni[date("w",$date_comment+(3600*$fuso_orario))].date(" d ",$date_comment+(3600*$fuso_orario));
			$tmp_c = date("m",$date_comment+(3600*$fuso_orario));
			print $mesi[$tmp_c-1];
			$date_comment = date(" Y - ",$date_comment+(3600*$fuso_orario)).date("H:",$date_comment+(3600*$fuso_orario)).date("i",$date_comment+(3600*$fuso_orario));
		}
		else $date_comment = "/";
		echo "$date_comment<br><br>";

		//edit?
		if ($comments[$count_comment]['cmlastedit']!=""){
			echo "<i>"._LASTEDITBY." <b>".$comments[$count_comment]['cmlasteditby']."</b> (";
			$date_comment = $comments[$count_comment]['cmlastedit'];
			if(is_numeric($date_comment)){
			echo $giorni[date("w",$date_comment+(3600*$fuso_orario))].date(" d ",$date_comment+(3600*$fuso_orario));
			$tmp_c = date("m",$date_comment+(3600*$fuso_orario));
			print $mesi[$tmp_c-1];
			$date_comment = date(" Y - ",$date_comment+(3600*$fuso_orario)).date("H:",$date_comment+(3600*$fuso_orario)).date("i",$date_comment+(3600*$fuso_orario));
			}
			else $date_comment = "/";
			echo "$date_comment";

			echo ")</i><br><br>";
		}
		// comment
		print stripslashes(tag2html($comments[$count_comment]['cmpost']));

		// link to manage the current comment
		if(_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR) {
			echo "<div style='margin-top:0.5em;text-align:right;'>";
			echo "<a href=\"index.php?";
			if ($mod!="") echo "mod=$mod&amp;";
			echo "action=editcommentinterface&amp;news=$news&amp;comment=".($count_comment+1)."\" title='"._MODIFICA."'>"._ICONMODIFY._MODIFICA."</a>";
			$delcomment_param = "&amp;file=news/$news.xml&amp;comment_date=".$comments[$count_comment]['cmdate'];
			echo "&nbsp;<a href='#' onclick=\"check('index.php?$modstring"."action=deletecomment&amp;news=$news&amp;comment=".($count_comment+1)."')\" title='"._ELIMINA."'>"._ICONDELETE._ELIMINA."</a>";
			echo "</div>";
		}
		print "</div>";
	}
	//se ci sono commenti stampo il link anche alla fine
	if (count($comments)>0)
		echo "<br>".$news_link['link_addcomment'];
	echo "<br><hr>";

	// search previous and next news
	if (_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR)
		$newslist = load_news_list($section,TRUE);
	else $newslist = load_news_list($section,FALSE);

// 	$newslist = list_news($section,"false","false");
	//fix by Roberto Balbi
	$newslist=array_reverse($newslist);
	$key = array_search($news,$newslist);

	if($key-1 < 0)
		$prev = _NOPREVNEWS;
	else $prev = "<a href='index.php?$modstring"."action=viewnews&amp;news=".str_replace(".php","",$newslist[$key-1])."' title='"._PREVNEWS."'>&#171; "._PREVNEWS."</a>";
	if($key+1 > count($newslist)-1)
		$next = _NONEXTNEWS;
	else $next = "<a href='index.php?$modstring"."action=viewnews&amp;news=".str_replace(".php","",$newslist[$key+1])."' title='"._NEXTNEWS."'>"._NEXTNEWS." &#187;</a>";
	echo "<div class=\"centeredDiv\">$prev | $next</div>";

	//reimposto l'array per visualizzare prima le notizie più recenti
	rsort($newslist);
	// list of last news about the same topic
	echo "<hr><b>"._RELATEDNEWSLAST."</b><br><br>";
	include("include/search/01_News.php");
	$no_result=TRUE;
	$user = _FN_USERNAME;
	$arrayok=array();
	for ($i=0;$i<count($newslist);$i++){
		//se ci sono più di 300 news interrompo per motivi di prestazioni
		if ($i>300) break;
		//non devo controllare i permessi di visione perchè se ne occupa una
		//volta sola la funzione load_news_list più sopra
// 		if (!user_can_view_news($section,$news,$user,FALSE))
// 		continue;
// 		riscrivo il codice per cercare la categoria per esteso in modo
// 		da aumentare le prestazioni.
		$newsfile = get_news_file($section,$newslist[$i]);

		if (!is_file($newsfile)) continue;

		$string = "";
		$string = get_file($newsfile);

		//categoria news
		$newscategory = "";
		$newscategory = get_xml_element("category",$string);
		if (!check_var($newscategory,"text"))
			continue;
// 		$newscategory = get_news_category($section,$newslist[$i]);
		if ($newscategory==$data['category']){
// 		if (find_news_by_category($section,$newslist[$i],$data['category'],$user)){
			$no_result=FALSE;
			$arrayok[]=$newslist[$i];
			if (count($arrayok)>4) break;
		}
	}
// 		print_r($arrayok);
	show_related_news($section,$arrayok);


	// link to read all the news for the same topic
	$category=$data['category'];
	echo "<hr>";
	echo "<b>"._RELATEDNEWSALL."</b><div><div style='margin:1em'>";
	echo "<a href='index.php?mod=none_Search&amp;where=news&amp;category=$category' title='$w3c_title'>";
	if ($category=="nonews.png")
		echo "<img src='images/$category' alt='$category' style=\"border:0\" /></a>";
	else echo "<img src='images/news/$category' alt='$category' style=\"border:0\" /></a>";
	echo "</div></div>";
	//se esiste uso la nuova funzione per chiudere le news
	if (function_exists("CloseNews"))
		CloseNews();
	else CloseTableTitle();
}


/**
 * Crea il footer della news
 *
 * @param string $section la sezione della news
 * @param string $news la news
 * @param array $data l'array di dati caricato tramite load_news(): utile per evitare di ricaricare
 *              i dati della news
 * @author Aldo Boccacci
 *
 */
function create_footer_news($section,$news,$data){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	$news = getparam($news,PAR_NULL,SAN_NULL);
	$newsfile = get_news_file($section,$news);
// 	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if ($section=="") return;
	if ($news=="") return;

	if (!is_file($newsfile)) return;

// 	if (!user_can_view_news($section,$news,"",FALSE)) return;
// 	layout fix by ZEBDEMON
	echo "<div class='footnews' style=\"clear: both;\">\n";
	$news_link = get_news_link_array($section,$news,$data);
	echo $news_link['news_infos']."<br>";
	//only if there is a body
	//Al momento non è possibile nascondere il link "leggi tutto"
	//perchè non vi è un titolo cliccabile alternativo per leggere
	//la news
// 	if (trim($data['body'])!="")
		echo $news_link['link_read']." ";
	echo $news_link['link_comment']." ";
	echo $news_link['link_print']." ";
	if (_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR) {
// 		echo "<br><div style=\"text-align: left;\">";
		echo $news_link['link_modify']." ";
		echo $news_link['link_delete']." ";
// 		echo "<br>";
		if (news_is_ontop($news)) echo $news_link['link_normal']." ";
		else echo $news_link['link_ontop']." ";
		if (news_is_hidden($news)) echo $news_link['link_show']." ";
		else echo $news_link['link_hide']." ";
		echo $news_link['link_move'];
// 		echo "</div>";
	}

// 	print_r($news_link);
	echo "</div>";
}




/**
 * Restituisce un array con alcuni link utili alle news
 *
 * Restituisce un array contenente alcuni link che possono essere utilizzati
 * per accedere alle funzioni di gestione delle news.
 * La struttura dell'array restituito è:
 * $ret_strings['news_infos']      = data di pubblicazione della news e numero di letture
 * $ret_strings['link_read']       = link per leggere la news completa
 * $ret_strings['link_addcomment'] = link per visualizzare form dei commenti alla news
 * $ret_strings['link_comment']    = link per commentare la news
 * $ret_strings['link_print']      = link per stampare la news
 * $ret_strings['link_modify']     = link per modificare la news (solo amministratori)
 * $ret_strings['link_delete']     = link per eliminare la news (solo amministratori)
 *
 * @author Simone Vellei <simone_vellei@users.sourceforge.net>
 * @author Aldo Boccacci
 * @since 2.6
 *
 * @param string $section la sezione della news
 * @param string $news l'indirizzo assoluto della news da processare
 * @param array $newsdata i dati della news (utile per evitare caricamenti multipli)
 * @return array un array con dati/link per la news
 */
function get_news_link_array($section,$news,$newsdata=""){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	$news = getparam($news,PAR_NULL,SAN_NULL);
	$newsfile = get_news_file($section,$news);
// 	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

// 	if (!is_file($newsfile)) return NULL;
	//è doppia per eliminare anche le doppie occorrenze come "top_hide_"
	$newstime = get_news_time($news);
// 	if (!user_can_view_news($section,$news)) return;

	global $theme, $giorni, $mesi, $fuso_orario;
	if ($newsdata!="") $data = $newsdata;
	else $data = load_news_header($section,$news);
	// publishing date
	if ($data['by']==""){
		$ret_strings['news_infos'] = _POSTATO.$giorni[date("w",$newstime+(3600*$fuso_orario))].date(" d ",$newstime+(3600*$fuso_orario));
		$tmp = date("m", $newstime+(3600*$fuso_orario));
		$ret_strings['news_infos'] .= $mesi[$tmp - 1];
		$ret_strings['news_infos'] .= date(" Y - ",$newstime+(3600*$fuso_orario)).date("H:",$newstime+(3600*$fuso_orario)).date("i",$newstime+(3600*$fuso_orario));
		// times read

		$ret_strings['news_infos'] .= " ("._LETTO." ".$data['reads']." "._VOLTE.")";
	}
	else {
		$ret_strings['news_infos'] = _POSTEDBY.
		" <a href=\"index.php?mod=none_Login&amp;action=viewprofile&amp;user=".$data['by']."\" title=\""._VIEW_USERPROFILE."\">".$data['by']."</a>, ".
		$giorni[date("w",$newstime+(3600*$fuso_orario))].date(" d ",$newstime+(3600*$fuso_orario));
		$tmp = date("m", $newstime+(3600*$fuso_orario));
		$ret_strings['news_infos'] .= $mesi[$tmp - 1];
		$ret_strings['news_infos'] .= date(" Y - ",$newstime+(3600*$fuso_orario)).date("H:",$newstime+(3600*$fuso_orario)).date("i",$newstime+(3600*$fuso_orario));
		// times read

		$ret_strings['news_infos'] .= " ("._LETTO."&nbsp;".$data['reads']."&nbsp;"._VOLTE.")";

	}
	// news title
	$title = $data['title'];
	$title = str_replace("\"","&quot;",$title);

	$modstring="";
	$mod = _FN_MOD;
	if ($mod!=""){
		$modstring="mod=".rawurlencodepath($mod)."&amp;";
	}

	// link to read the full news
	$ret_strings['link_read'] = "<a href='".CleanString($title).".".$news."' title=\""._FLEGGI." news: $title\">"._ICONREAD._LEGGITUTTO."</a>";
//vecchio link  $ret_strings['link_read'] = "<a href='index.php?$modstring"."action=viewnews&amp;news=$news' title=\""._FLEGGI." news: $title\">"._ICONREAD._LEGGITUTTO."</a>";

	// link to comment the news
	$ret_strings['link_addcomment'] = "<a href='index.php?$modstring"."action=addcommentinterface&amp;news=$news' title=\""._ADDCOMM." news: $title\">"._ICONCOMMENT."&nbsp;"._ADDCOMM."</a><br>";

	// link to view comments of the news
	$ret_strings['link_comment'] = "<a href='index.php?$modstring"."action=viewnews&amp;news=$news#comments' title=\""._VIEWCOMMENTS." news: $title\">"._ICONCOMMENT;

	$commenti = count($data['comments']);
	if($commenti == 0)
		$ret_strings['link_comment'] .= _COMMENTI."?";
	else
		$ret_strings['link_comment'] .= "<b>"._COMMENTI."($commenti)</b>";
	$ret_strings['link_comment'] .= "</a>";

	// link to print the news
	$ret_strings['link_print'] = "<a href='print.php?$modstring"."news=$news' target='new' title=\""._STAMPA." news: $title\">"._ICONPRINT._STAMPA."</a>";

	// administration functions
	if(_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR) {

		// link to modify the news
		$ret_strings['link_modify'] = "<a href='index.php?$modstring"."action=editnewsinterface&amp;news=$news' title=\""._EDITNEWS.": $title\">"._ICONMODIFY._MODIFICA."</a>";

		// link to delete the news
		$ret_strings['link_delete'] = "<a href='index.php?$modstring"."action=deletenewsinterface&amp;news=$news' title=\""._DELETENEWS.": $title\">"._ICONDELETE._ELIMINA."</a>";

		// link to hide the news
		$ret_strings['link_hide'] = "<a href='index.php?$modstring"."action=hidenews&amp;news=$news' title=\""._HIDE." news: $title\">"._ICONHIDE._HIDE."</a>";

		// link to un-hide the news
		$ret_strings['link_show'] = "<a href='index.php?$modstring"."action=shownews&amp;news=$news' title=\""._SHOW." news: $title\">"._ICONSHOW._SHOW."</a>";

		// link to set on top the news
		$ret_strings['link_ontop'] = "<a href='index.php?$modstring"."action=ontopnews&amp;news=$news' title=\""._STICKYNEWS.": $title\">"._ICONONTOP._STICKY."</a>";

		// link to hide the news
		$ret_strings['link_normal'] = "<a href='index.php?$modstring"."action=normalnews&amp;news=$news' title=\""._UNSTICKYNEWS.": $title\">"._ICONNORMAL._NORMAL."</a>";

		// link to move the news
		$ret_strings['link_move'] = "<a href='index.php?$modstring"."action=movenewsinterface&amp;news=$news' title=\""._MOVENEWS.": $title\">"._ICONMOVE._MOVE."</a>";
	}

	return($ret_strings);
}

/**
 * L'interfaccia per inviare un nuovo commento ad una news
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news cui inviare il commento
 * @author Aldo Boccacci
 */
function add_comment_interface($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	global $sitename,$guestcomment;

	$mod = _FN_MOD;
// 	$req     = getparam("REQUEST_URI", PAR_SERVER, SAN_FLAT);
	if (!is_file($newsfile)){
		echo "<div style=\"text-align: center\"><b>"._NORESULT."</b></div>";
		return;
	}

	// print interface
	if ($mod!="" and $mod!="none_News")
		OpenTableTitle(_ADDCOMM);
	if((_FN_USERNAME!="") or ($guestcomment==1)){
		?>
		<script type="text/javascript">
	function validate_comment_form()
		{
			if(document.getElementById('newscomment').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FMESS?>');
					document.getElementById('newscomment').focus();
					document.getElementById('newscomment').value='';
					return false;
				}
			<?php
			if (_FN_IS_GUEST){
			?>
				if(document.getElementById('captcha').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CN_ANTISPAM?>');
					document.getElementById('captcha').focus();
					document.getElementById('captcha').value='';
					return false;
				}
			<?php
			}
			?>
		}
	</script>
		<?php
		echo "<form action=\"index.php?mod=$mod\" method=\"post\" onsubmit=\"return validate_comment_form()\">
		<input type=\"hidden\" name=\"newsaction\" value=\"fnaddnewscomment\" />
		<input type=\"hidden\" name=\"section\" value=\"$section\" />
		<input type=\"hidden\" name=\"news\" value=\"$news\" />
		<input type=\"hidden\" name=\"fnmod\" value=\""._FN_MOD."\" />";
		// bbcodes panel
		bbcodes_panel("newscomment", "home", "formatting"); echo "<br>";
		bbcodes_panel("newscomment", "home", "emoticons"); echo "<br>";
		bbcodes_panel("newscomment","home","images"); echo "<br>";
		echo "<textarea cols=\"50\" rows=\"20\" name=\"newscomment\" id=\"newscomment\" style=\"width: 95%\" ></textarea><br><br>";

		//se sono un guest
		if(_FN_IS_GUEST){
			include("include/captcha/fncaptcha.php");
			$fncaptcha = new fncaptcha();
			$fncaptcha->generateCode();
			$fncaptcha->printCaptcha("captcha","captcha");

		}
		echo "<input type=\"submit\" value=\""._FINVIA."\" />
		</form><br>";
		view_news($section,$news);
	}
	else{
		print "<div style=\"text-align: center;\">"._DEVIREG." <b>".$sitename."</b> "._DEVIREG2;
		echo "<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
	}
	if ($mod!="" and $mod!="none_News")
		CloseTableTitle();

}

/**
 * Interfaccia per editare una news
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news
 * @param string $mode la modalità: "edit", "new", "editmoderator","newmoderator", "propose"
 * @author Marco Segato <segatom@users.sourceforge.net>
 * @author Aldo Boccacci
 */
function edit_news_interface($section,$news,$mode) {
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (($mode=="propose" and $news!="") and !_FN_IS_ADMIN)
		flatnews_die("user has no permissions to edit news",__FILE__,__LINE__);

	if ((_FN_IS_GUEST or _FN_IS_USER) and $news!="" and !_FN_IS_NEWS_MODERATOR)
		flatnews_die("user has no permissions to edit news",__FILE__,__LINE__);

	if ($mode!="propose" and !_FN_IS_ADMIN and !_FN_IS_NEWS_MODERATOR)
		flatnews_die("user has no permissions to edit news",__FILE__,__LINE__);

	global $guestnews,$sitename;
	if ($mode=="propose" and _FN_IS_GUEST and $guestnews==0){
		echo "<div style=\"text-align: center;\">"._DEVIREG." <b>\"$sitename\"</b>"._DEVIREG3."<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
		return;
	}
	$modstring="";
	if (_FN_MOD!="" and _FN_MOD!="none_News") $modstring = "?mod="._FN_MOD;

	$edit_header = "";
	$edit_body   = "";
	$edit_cat    = "";
	$edit_tags   = array();
	global $sitename, $news_editor;
	if (!preg_match("/^ckeditor$|^fckeditor$|^bbcode$/i",$news_editor)) $news_editor="bbcode";

	// edit an old news
	if($mode=="edit" AND file_exists($newsfile)) {
		$newsdata = load_news($section,$news);
		$reftitle    = $newsdata['title'];
		$edit_header = $newsdata['header'];
		$edit_body   = $newsdata['body'];
		$edit_cat    = $newsdata['category'];
		$edit_tags   = $newsdata['tags'];
	} else {
		// report a news with Flatnuke-Fast-News
		$reftitle = stripslashes(getparam("reftitle", PAR_GET, SAN_FLAT));
		$refbody  = stripslashes(getparam("refbody",  PAR_GET, SAN_FLAT));
		$refurl   = getparam("refurl", PAR_GET, SAN_FLAT);
	}
	$mod = _FN_MOD;
	if ($mod!="" and $mod!="none_News"){
		if ($mode=="add")
			OpenTableTitle(_ADDNEWS);
		else if ($mode=="edit")
			OpenTableTitle(_EDITNEWS);
	}
	?>
		<script type="text/javascript">
	function validate_news()
		{
			if(document.getElementById('news_title').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._TITNOTIZIA?>');
					document.getElementById('news_title').focus();
					document.getElementById('news_title').value='';
					return false;
				}
			<?php
			//questo controllo javascript sembra avere problemi se è
			//utilizzato un editor visuale come FCKEditor
// 			global $news_editor;
			if ($mode=="propose"){
			?>
			else if(document.getElementById('news_header').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._INTMESSAGGIO?>');
					document.getElementById('news_header').focus();
					document.getElementById('news_header').value='';
					return false;
				}
			<?php
			}//fine if ($news_editor=="bbcode")
			if ($mode=="propose" and _FN_IS_GUEST){
			?>
				if(document.getElementById('captcha').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._CN_ANTISPAM?>');
					document.getElementById('captcha').focus();
					document.getElementById('captcha').value='';
					return false;
				}
			<?php
			}
			?>
			else return true;
		}
	</script>

	<?php

	// print html form to write the news
	echo "<form action=\"index.php$modstring\" method=\"post\" onsubmit=\"return validate_news()\">";
	?><b><?php echo _TITNOTIZIA ?></b><br>
	<input type="text" id="news_title" name="news_title" style="width:100%" value="<?php echo $reftitle ?>" /><br><br>
	<b><?php echo _ARGOMENTO ?></b><br>
	<?php $print_cat = ($edit_cat=="" or $edit_cat=="nonews.png") ? ("images/nonews.png") : ("images/news/$edit_cat"); ?>
	<img id="category_icon" src="<?php echo $print_cat?>" alt="Category" /><br>
	<select style="max-width:100%" id="news_category" name="news_category" onchange="document.category_icon.src='images/news/'+this.options[this.selectedIndex].value"><?php
		// list topic arguments
		echo "\n<option value=\"../nonews\"> --- </option>\n";
		$icon_array = array();
		$icon_dir = opendir('images/news');
		while($icon_file=readdir($icon_dir)) {
			if(!( $icon_file=="." OR $icon_file==".." ) AND (!preg_match("/^\./",$icon_file) AND ($icon_file!="CVS")) ) {
				array_push($icon_array,$icon_file);
// 				array_push($icon_array, preg_replace("/\.png$/i","",$icon_file));
			}
		}
		closedir($icon_dir);
		if(count($icon_array)>0)
			sort($icon_array);
		for ($i=0;$i<count($icon_array);$i++) {
			echo "<option value=\"$icon_array[$i]\"";
			if($edit_cat == $icon_array[$i]) echo " selected=\"selected\"";
			echo ">".str_replace("_"," ", preg_replace("/\..*/","",$icon_array[$i]))."</option>\n";
		}
	echo "</select><br><br>";

	echo "<b>Tags</b><br>";
	//barra per inserire nuovi tag con un click
	$alltags = load_tags_list();
	$tags = array();
	foreach ($alltags as $tag => $count){
		if (!isset($edit_tags[$tag])){
			$tags[]=$tag;
		}
	}
	unset($tag);
	if (count($tags)>0)
		echo "<span style=\"font-size: 80%;\">";
	for ($n=0;$n<count($tags);$n++){
		if (trim($tags[$n])=="") continue;
		echo "<a href=\"javascript:void(0)\" onclick=\"javascript:insertTag('".$tags[$n]."', 'news_tags')\"  title=\""._INSERTTAG.": ".$tags[$n]."\">".$tags[$n]."</a>";
		if ($n<(count($tags)-1))
			echo ", ";
		else echo "<br>";
	}
	if (count($tags)>0)
		echo "</span>";
	echo "<input type=\"text\" id=\"news_tags\" name=\"news_tags\" style=\"width:100%\" value=\"";
	for ($n=0;$n<count($edit_tags);$n++){
		if (trim($edit_tags[$n])!=""){
			echo $edit_tags[$n];
			if ($n!=(count($edit_tags)-1)) echo ", ";
		}
	}
	echo "\"><br><br>";
	//il controllo sulla variabile $mode serve per evitare che venga stampato FCKeditor o ckeditor se la
	//news viene proposta da un utente comune (gli utenti comuni non possono proporre news
	//contenenti html per motivi di sicurezza)
	if ($news_editor=="fckeditor" AND file_exists("include/plugins/editors/FCKeditor/fckeditor.php") AND $mode!="propose" AND (_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR)) {
		$fck_installed = TRUE;
		if(isset($refbody) AND $refbody != "") {
			$text = "$refbody\n\nLink: <a href=\"$refurl\">$refurl</a>";	// Flatnuke-Fast-News link
		} else $text = "$edit_header";	// edit old news
		echo "<b>"._INTMESSAGGIO."</b><br>";
		// fckeditor panel news HEADER
		fn_textarea("fckeditor",tag2html($text),array("allow_php"=>TRUE,"BasePath"=>"include/plugins/editors/FCKeditor/", "Width"=>"100%","Height"=>"400","name"=>"news_header","id"=>"news_header","rows"=>"20","style"=>"width: 100%"));
		// fckeditor panel news BODY
		echo "<b>"._CORPOMESSAGGIO."</b><br>";
		fn_textarea("fckeditor",tag2html($edit_body),array("allow_php"=>TRUE,"BasePath"=>"include/plugins/editors/FCKeditor/", "Width"=>"100%","Height"=>"400","name"=>"news_body","id"=>"news_body","rows"=>"20","style"=>"width: 100%"));
	}
	else if ($news_editor=="ckeditor" AND file_exists("include/plugins/editors/ckeditor/ckeditor.php") AND $mode!="propose" AND (_FN_IS_ADMIN or _FN_IS_NEWS_MODERATOR)) {
		$fck_installed = TRUE;
		if(isset($refbody) AND $refbody != "") {
			$text = "$refbody\n\nLink: <a href=\"$refurl\">$refurl</a>";	// Flatnuke-Fast-News link
		} else $text = "$edit_header";	// edit old news
		echo "<b>"._INTMESSAGGIO."</b><br>";
		// fckeditor panel news HEADER
		fn_textarea("ckeditor",tag2html($text),array("allow_php"=>TRUE,"BasePath"=>"include/plugins/editors/ckeditor/", "Width"=>"100%","Height"=>"400","name"=>"news_header","id"=>"news_header","rows"=>"20","style"=>"width: 100%"));
		// fckeditor panel news BODY
		echo "<b>"._CORPOMESSAGGIO."</b><br>";
		fn_textarea("ckeditor",tag2html($edit_body),array("allow_php"=>TRUE,"BasePath"=>"include/plugins/editors/ckeditor/", "Width"=>"100%","Height"=>"400","name"=>"news_body","id"=>"news_body","rows"=>"20","style"=>"width: 100%"));

	}
	else {
		$fck_installed = FALSE;
		// bbcodes panel news HEADER
		bbcodes_panel("news_header", "home", "formatting"); echo "<br>";
		bbcodes_panel("news_header", "home", "emoticons"); echo "<br>";
		bbcodes_panel("news_header", "home", "images"); echo "<br>";
		echo "<b>"._INTMESSAGGIO."</b><br>";
		echo "<textarea id='news_header' rows='20' style='width:100%;' name='news_header'>";
		if(isset($refbody) AND $refbody != "") {
			echo "$refbody\n\nLink: <a href=\"$refurl\">$refurl</a>";	// Flatnuke-Fast-News link
		} else echo html2tag($edit_header);	// edit old news
		echo "</textarea>\n<br><br>";
		// bbcodes panel news BODY
		bbcodes_panel("news_body", "home", "formatting"); echo "<br>";
		bbcodes_panel("news_body", "home", "emoticons"); echo "<br>";
		bbcodes_panel("news_body", "home", "images"); echo "<br>";
		echo "<b>"._CORPOMESSAGGIO."</b><br>";
		echo "<textarea id='news_body' rows='20' style='width:100%;' name='news_body'>".html2tag($edit_body)."</textarea>\n";
	}

	// submit buttons
	// innanzitutto controllo quale azione andrà compiuta
	if ($mode=="propose")
		echo "<input type=\"hidden\" name=\"newsaction\" value=\"saveproposednews\" />";
	else echo "<input type=\"hidden\" name=\"newsaction\" value=\"savenews\" />";

	if ($mode=="add"){
		echo "<br><br><img src='forum/icons/hide.png' alt='Hide' />&nbsp;<input type=\"checkbox\" name=\"hidden\" value=\"hidden\">"._FDHIDE." news<br><br>";
		echo "<img src='forum/icons/ontop.png' alt='On top' />&nbsp;<input type=\"checkbox\" name=\"ontop\" value=\"ontop\">"._STICKY." news<br><br>";
	}

	?>
	<input type="hidden" name="news" value="<?php echo $news?>" />
	<input type="hidden" name="section" value="<?php echo $section?>" />
<!-- 	<input type="hidden" name="rewrite" value="<?php //echo $rewrite?>" /><br><br> -->
	<?php
	//se devo proporre una notizia e sono un guest
	if($mode=="propose" and _FN_IS_GUEST){
		include("include/captcha/fncaptcha.php");
		$fncaptcha = new fncaptcha();
		$fncaptcha->generateCode();
		$fncaptcha->printCaptcha("captcha","captcha");
	}
	?>
	<input type="submit" value="<?php echo _INSNOTIZIA?>" /><?php
	//rimosso il pulsante di anteprima perchè non funziona a partire da Flatnuke 3.0
	//DA SISTEMARE
// 	if (!$fck_installed) {
// 		echo "&nbsp;<input type=\"button\" value=\""._ANTEPRIMA."\" onclick='ShowHideDiv(\"fnpreview\");' />";
// 	}
	?></form><?php

	// preview news with bbcodes
	if (!$fck_installed) {
		?><script type="text/javascript">
			getElement("news_title").onkeyup     = news_preview;
			getElement("news_title").onmousemove = news_preview;
			getElement("news_header").onkeyup      = news_preview;
			getElement("news_header").onmousemove  = news_preview;
			getElement("news_body").onkeyup      = news_preview;
			getElement("news_body").onmousemove  = news_preview;
			news_preview();
		</script>
		<br><div id="fnpreview"></div><?php
	}

	//TOLGO FLATNUKE FAST-NEWS
	/*
	// Flatnuke-Fast-News link
	$protocol = (isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS']=="on") ? ("https://") : ("http://");
	$dn = DirName($_SERVER['PHP_SELF'])."/";
	$dn = str_replace("//", "/", $dn);
	$siteurl = "$protocol".$_SERVER["HTTP_HOST"]."$dn";
	echo "<br>"._FASTNEWSSTR."<br><a href=\"javascript:if(navigator.userAgent.indexOf('Safari') >= 0){Q=getSelection();}else{Q=document.selection?document.selection.createRange().text:document.getSelection();}location.href='".$siteurl."index.php?mod=none_Admin&amp;op=fnccnews&amp;refbody='+encodeURIComponent(Q)+'&amp;refurl='+encodeURIComponent(location.href)+'&amp;reftitle='+encodeURIComponent(document.title);\">$sitename &raquo; Flatnuke Fast News</a>";
	*/
	//FINE FLATNUKE FAST-NEWS

	if ($mod!="" and $mod!="none_News"){
		if ($mode=="add" or $mode=="edit")
			CloseTableTitle();
	}
}

/**
 * Crea una textarea con i parametri passati
 *
 * @param string $type il tipo di textarea da creare
 * @param string $text il testo da includere nella textarea
 * @param array $options le varie opzioni per la creazione della textarea
 */
function create_textarea($type,$text,$options){
	$type = getparam($type,PARN_NULL,SAN_FLAT);
	$text = getparam($text,PARN_NULL,SAN_FLAT);

	if (!preg_match("/^bbcode$|^fckeditor$/i",$type))
		$type = "bbcode";

	$id = trim(strip_tags(getparam($options['id'],PAR_NULL,SAN_FLAT)));
	$name = trim(strip_tags(getparam($options['name'],PAR_NULL,SAN_FLAT)));
	$rows = trim(strip_tags(getparam($options['rows'],PAR_NULL,SAN_FLAT)));
	$cols = trim(strip_tags(getparam($options['cols'],PAR_NULL,SAN_FLAT)));
	$style = trim(strip_tags(getparam($options['style'],PAR_NULL,SAN_FLAT)));
	$allow_php = trim(strip_tags(getparam($options['allow_php'],PAR_NULL,SAN_FLAT)));
	$allow_html = trim(strip_tags(getparam($options['allow_html'],PAR_NULL,SAN_FLAT)));

	if ($allow_php!=TRUE) $allow_php = FALSE;
	if ($allow_html!=TRUE) $allow_html = FALSE;

	//FCKEditor options
	$BasePath = strip_tags(getparam($options['BasePath'],PAR_NULL,SAN_FLAT));
	$Value = strip_tags(getparam($options['Value'],PAR_NULL,SAN_FLAT));
	$Width = strip_tags(getparam($options['Width'],PAR_NULL,SAN_FLAT));
	$Heigth = strip_tags(getparam($options['Heigth'],PAR_NULL,SAN_FLAT));
	$ToolbarSet = strip_tags(getparam($options['ToolbarSet'],PAR_NULL,SAN_FLAT));

	if ($type=="bbcode" OR $type=="standard"){
		echo "<textarea";
		if ($name!="") echo " name=\"$name\"";
		if ($id!="") echo " id=\"$id\"";
		if ($rows!="") echo " rows=\"$rows\"";
		if ($cols!="") echo " cols=\"$cols\"";
		if ($style!="") echo " style=\"$style\"";
		echo ">";
		if ($allow_php!=TRUE)
			$text = preg_replace("/\<\?.*\?\>/","",$text);
		if ($allow_html!=TRUE)
			$text = strip_tags($text);
		echo $text;
		echo "</textarea>";
	}
	else if ($type=="fckeditor"){
		if ($allow_php!=TRUE) $text = strip_tags($text);
		$oFCKeditor = new FCKeditor($id);
		$oFCKeditor->BasePath = $basepath;
		$oFCKeditor->Value = $text;
		$oFCKeditor->Width = $Width;
		$oFCKeditor->Height = $Heigth;
		$oFCKeditor->ToolbarSet = $toolbarSet;
		$oFCKeditor->Create();
	}
}

/**
 * Mostra le news correlate in base all'elenco fornito
 *
 * @param string $section la sezione contenente le notizie
 * @param array $news_array l'array con le notizie
 * @author Aldo Boccacci
 */
function show_related_news($section, $news_array){
	global $theme;
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!check_path($section,"","false"))
		flatnews_die("\$section is not valid",__FILE__,__LINE__);
	if (!is_array($news_array))
		flatnews_die("\$news_array must be an array!",__FILE__,__LINE__);

	if ($section!="" and $section!="none_News") $modstring = "mod=$section&amp;";
		else $modstring="";

	if (count($news_array)>0){
		for ($i=0;$i<count($news_array);$i++){
			$data = load_news_header($section,$news_array[$i]);
			echo "<a href=\"index.php?$modstring"."action=viewnews&amp;news=".$news_array[$i]."\" title=\"visualizza news\">"._ICONREAD."&nbsp;".$data['title']."</a> (".date("d/m/Y - H:i", get_news_time($news_array[$i])).", "._LETTO.$data['reads']." "._VOLTE.")<br>";
		}
	}
}

?>
