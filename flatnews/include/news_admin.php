<?php

if (preg_match("/news_admin\.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    flatnews_die("You cannot call news_admin.php!",__FILE,__LINE);
}

if (!is_admin() and !is_news_moderator()) flatnews_die("You cannot include the file ".__FILE__." since you are not an administrator or a news moderator.");

/**
 * Elimina la news indicata come parametro
 *
 * @param string $section la sezione conenente la news da eliminare
 * @param string $news la news da eliminare
 * @author Aldo Boccacci
 * @since 3.0
 */
function delete_news($section,$news){
	if (!_FN_IS_ADMIN and !_FN_IS_NEWS_MODERATOR) flatnews_die("DELETE: the user is not an administrator or a moderator",__LINE__,__FILE__);
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$modstring="";
	if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

	if (file_exists($newsfile)){
		$data = load_news($section,$news);
// 		print_r($data);die();
		if (is_writable($newsfile)){
			if (unlink($newsfile)){
				//rimuovo i tag dall'elenco generale
				remove_tags_from_tags_list($data['tags']);
				//rimuovo i tag dall'elenco della sezione
				remove_tags_from_tags_list($data['tags'],$section);
				echo "<br><div style=\"text-align: center\">"._THENEWS." <b>".$data['title']."</b> "._NEWSDELETED.".<br><br><a href=\"index.php$modstring\">"._RETURN."</a></div>";
				flatnews_logf("DELETE: the news ".$data['title']." in the section ".strip_tags($section)." was deleted");
				//creo il feed rss della sezione
				create_feed($section);
				//aggiorno il file newslist.php
				save_news_list($section);
			}
		}
		else {
			echo "<div style=\"text-align: center\">"._NEWSNOTDELETED." ".$data['title']." "._NEWSNOTDELETED2.". "._FDCHECKPERM.".<br><br><a href=\"index.php$modstring\">"._RETURN."</a></div>";

			flatnews_logf("DELETE: the file ".strip_tags($news)." in the section ".strip_tags($section)." is not writable",__LINE__,__FILE__);
		}
	}
}

/**
 * Elimina la news indicata come parametro
 *
 * @param string $section la sezione conenente la news da eliminare
 * @param string $news la news da eliminare
 * @author Aldo Boccacci
 * @since 3.0
 */
function delete_proposed_news($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_proposed_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$modstring="";
	if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

	if (file_exists($newsfile)){
		$data = load_news_header($section,$news);
		if (is_writable($newsfile)){
			if (unlink($newsfile)){
				echo "<div style=\"text-align: center\"><b>"._THENEWS." ".$data['title']."</b> "._NEWSDELETED.".<br><br><a href=\"index.php$modstring\" title=\""._GOTOSECTION."\">"._GOTOSECTION."</a></div>";
				//aggiorno la lista delle news proposte dagli utenti
				save_proposed_news_list(load_proposed_news_list());
				$count = count(load_proposed_news_list());
				if ($count>0){
					echo "<br><br><a href=\"index.php?$modstring"."&amp;action=manageproposednews\" title=\""._SEGNNOTIZIE."\">&lt;&lt; "._MANAGEOTHERPROPOSEDNEWS.". ($count)</a>";
				}
				flatnews_logf("DELETE: the proposed news ".strip_tags($news)." in the section ".strip_tags($section)." was deleted");
			}
		}
		else {
			echo "<div style=\"text-align: center\">"._NEWSNOTDELETED." $news "._NEWSNOTDELETED2.". "._FDCHECKPERM.".<br><br><a href=\"index.php$modstring\">"._RETURN."</a></div>";

			flatnews_logf("DELETE: the file ".strip_tags($news)." in the section ".strip_tags($section)." is not writable",__LINE__,__FILE__);
		}
	}
}

/**
 * Interfaccia per eliminare una news
 *
 * @param string $section la sezione contenente la news da eliminare
 * @param string $news la news da eliminare
 * @author Aldo Boccacci
 */
function delete_news_interface($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$modstring="";
	if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

	$data = load_news($section,$news);

	echo "<br><div style=\"text-align: center\">"._DELETENEWSALERT." <b>".$data['title']."</b> ";
	if ($section=="none_News" or $section=="") echo _INHOMEPAGE;
	else echo _INTHESECT." <b>".strip_tags($section);
	echo "</b>?";
	echo "<form action=\"index.php$modstring\" method=\"POST\">";
	echo "<input type=\"hidden\" name=\"newsaction\" readonly=\"readonly\" value=\"deletenews\" />";
	echo "<input type=\"hidden\" name=\"section\" readonly=\"readonly\" value=\"".rawurlencodepath($section)."\" />";
	echo "<input type=\"hidden\" name=\"news\" readonly=\"readonly\" value=\"$news\" />";
	echo "<br><br><input type=\"submit\" name=\"fnok\" value=\""._ELIMINA."\" />";
	echo "</form></div>";
}

/**
 * Interfaccia per modificare un commento a una news
 *
 * @param string $news la news contenente il commento da modificare
 * @param string $comment il numero del commento da modificare
 */
function edit_comment_interface($section,$news,$comment){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$mod = _FN_MOD;
	$strmod="?mod=none_News";
	if ($mod!="") $strmod = "?mod=$mod";
	$newsdata= load_news($section,$news);
	$commentindex= ($comment-1);

	echo "<h2>Modifica il commento:</h2>";
	echo "<form action=\"index.php$strmod\" method=\"post\">
	<input type=\"hidden\" name=\"newsaction\" value=\"fneditnewscomment\" />
	<input type=\"hidden\" name=\"section\" value=\"$section\" />
	<input type=\"hidden\" name=\"news\" value=\"$news\" />
	<input type=\"hidden\" name=\"number\" value=\"$comment\" />
	<input type=\"hidden\" name=\"fnmod\" value=\""._FN_MOD."\" />";
	// bbcodes panel
	bbcodes_panel("newscomment", "home", "formatting"); echo "<br>";
	bbcodes_panel("newscomment", "home", "emoticons"); echo "<br>";
	bbcodes_panel("newscomment","home","images"); echo "<br>";

	echo "<textarea cols=\"50\" rows=\"20\" name=\"newscomment\" id=\"newscomment\" style=\"width: 95%\" >".$newsdata['comments'][$commentindex]['cmpost']."</textarea><br><br>
	<input type=\"submit\" value=\""._FINVIA."\" />
	</form><br>";
	view_news($section,$news);

}


/**
 * Salva la news creata/modificata dall'amministratore
 *
 * @author Aldo Boccacci
 */
function save_news_admin(){
	global $news_editor;

	$section = getparam("section",PAR_POST,SAN_FLAT);
	$news    = getparam("news",PAR_POST,SAN_FLAT);
	$hidden  = getparam("hidden",PAR_POST,SAN_FLAT);
	$ontop   = getparam("ontop",PAR_POST,SAN_FLAT);

	// echo "hidden $hidden"; die();
	if ($section!="" and $section!="none_News")
		$modstring = "mod=$section";
	else $modstring="";
	if ($section=="")
		$section= "none_News";
	if ($news=="")
		$news=time();

	$newsfile = get_news_file($section,$news);
	if (is_file($newsfile)) {
		$new = false;
		$data = load_news($section,$news);
		// rimuovo i tag dalla lista globale, saranno riaggiunti al salvataggio con eventuali modifiche
		remove_tags_from_tags_list($data['tags']);
		// rimuovo i tag dalla lista della sezione, saranno riaggiunti al salvataggio con eventuali modifiche
		remove_tags_from_tags_list($data['tags'],$section);
	} else {
		$new = true;
		$data = array();
	}

	// print_r($_POST);
	$data['title'] = getparam("news_title",PAR_POST,SAN_NULL);
	$data['tags'] = preg_split("/,/",getparam("news_tags",PAR_POST,SAN_FLAT));
	$data['category'] = getparam("news_category",PAR_POST,SAN_FLAT);
	$data['header'] = getparam("news_header",PAR_POST,SAN_NULL);
	$data['body'] = getparam("news_body",PAR_POST,SAN_NULL);
	//se è in uso un editor visuale elimino tutti gli \n per evitare che vengano convertiti
	//in <br> in fase di visualizzazione
	//Con la funzione preg_replace_callback riesco a sostituire i \n presenti all'interno di
	//<pre></pre> con degli <br> prima della sostituzione di tutti gli \n successivi
	//MODIFICATO INTEGRALMENTE QUESTO CODICE PERCHÈ NON COMPATIBILE CON PHP 5.2
	//MODIFICATO SENZA USARE LA FUNZIONE CALLBACK
	if (($news_editor=="ckeditor" AND file_exists("include/plugins/editors/ckeditor/ckeditor.php"))
		or ($news_editor=="fckeditor" AND file_exists("include/plugins/editors/FCKeditor/fckeditor.php"))){
			preg_match_all("/<pre>(.+?)<\/pre>/s",$data['header'],$prematch);
			for ($countreplace=0;$countreplace<count($prematch[0]);$countreplace++){
				$replace = preg_replace("/\n/","<br>",$prematch[0][$countreplace]);
				$replace = preg_replace("/\r/s","",$replace);
				$data['header'] = preg_replace("/".preg_quote($prematch[0][$countreplace],'/')."/s",$replace,$data['header']);
	// 			$data['header'] = preg_replace("/\r/s","",$data['header']);
			}
	// 		print_r($prematch);
	// 		echo "FINALE:";
	// 		print_r($data['header']);
	// 		die();
	//	CODICE RIMOSSO PERCHÈ NON COMPATIBILE CON PHP 5.2
	// 	$data['header'] = preg_replace_callback("/<pre>(.+?)<\/pre>/s",function ($match) {
	// 	return "<pre>".preg_replace("/\n/","<br>",$match[1])."</pre>";}, $data['header']);
	//	Al termine di tutto rimuovo gli a capo
		$data['header'] = preg_replace("/\n/s","",$data['header']);
		$data['header'] = preg_replace("/\r/s","",$data['header']);
	}

	if (($news_editor=="ckeditor" AND file_exists("include/plugins/editors/ckeditor/ckeditor.php"))
		or ($news_editor=="fckeditor" AND file_exists("include/plugins/editors/FCKeditor/fckeditor.php"))){
			preg_match_all("/<pre>(.+?)<\/pre>/s",$data['body'],$prematch);
			for ($countreplace=0;$countreplace<count($prematch[0]);$countreplace++){
				$replace = preg_replace("/\n/","<br>",$prematch[0][$countreplace]);
				$replace = preg_replace("/\r/s","",$replace);
				$data['body'] = preg_replace("/".preg_quote($prematch[0][$countreplace],'/')."/s",$replace,$data['body']);
	// 			$data['body'] = preg_replace("/\r/s","",$data['body']);
			}
	// 	$data['body'] = preg_replace_callback("/<pre>(.+?)<\/pre>/s",function ($match) {
	// 	return "<pre>".preg_replace("/\n/","<br>",$match[1])."</pre>";}, $data['body']);
		$data['body'] = preg_replace("/\n/s","",$data['body']);
		$data['body'] = preg_replace("/\r/s","",$data['body']);
	}

	if (is_file($newsfile)){
		//stiamo modificando una news
		$data['lasteditby'] = _FN_USERNAME;
		$data['lastedit'] = time();
	} else {
		//è una nuova news
		$data['by'] = _FN_USERNAME;
		$data['date'] = $news;
	}

	if ($hidden == "hidden")
		$news = "hide_$news";
	if ($ontop == "ontop")
		$news = "top_$news";

	//salvo la news
	save_news($section,$news,$data);
	//aggiungo i tag all'elenco globale
	add_tags_in_tags_list($data['tags']);
	//aggiungo i tag nella lista della sezione
	add_tags_in_tags_list($data['tags'],$section);
	// echo "<div style=\" text-align: center;\"><b>News salvata correttamente</b></div>";

	//aggiorno il file newslist.php
	save_news_list($section);
	//aggiorno il feed rss della sezione
	create_feed($section);

	if ($new) {
		create_general_feed(); // aggiorno il feed rss del sito
		flatnews_logf("News $news added in section $section");
	} else flatnews_logf("News $news edited in section $section");

	header("Location: index.php?$modstring"."&action=viewnews&news=$news");
}

/**
 * Elimina il commento indicato dai parametri alla funzione
 *
 * @param string $section la sezione contenente il commento
 * @param string $news la news contenente il commento
 * @param string $comment il numero del commento da eliminare
 * @author Aldo Boccacci
 */
function delete_comment($section,$news,$comment){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);
	$comment = getparam($comment,PAR_NULL,SAN_FLAT);
	if (!check_var($comment, "digit")) flatnews_die("\$comment must be a digit",__FILE__,__LINE__);

	$modstring="";
	if (_FN_MOD!="") $modstring = "mod="._FN_MOD."&amp;";

	//riporto a base 0
	$comment = ($comment-1);

	$data = load_news($section,$news);
	if ($comment>count($data['comments'])){
		flatnew_die("Attenzione, il commento ".strip_tags($comment)." non esiste",__FILE__,__LINE__);
	}
	else {
		unset($data['comments'][$comment]);
		save_news($section,$news,$data);
		echo "<div style=\"text-align: center\"><b>"._COMMENTDELETED."!</b><br>";
// 		echo "</div>";
		//ritorno
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?$modstring"."action=viewnews&amp;news=$news\"><br><a href=\"index.php?$modstring"."action=viewnews&amp;news=$news\" title=\""._RETURN."\">"._RETURN."</a></div>";
		flatnews_logf("DELETE COMMENT: comment $comment deleted in the news $news in the section $section");
	}
}

/**
 * Salva le modifiche al commento nella news
 *
 * @param string $section la sezione
 * @param string $news la notizia
 * @param integer $number il numero del commento
 * @param string $comment il testo del commento da salvare
 */
function news_edit_comment($section,$news,$number,$comment){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);
	$number = getparam($number,PAR_NULL,SAN_FLAT);
	if (!check_var($number, "digit")) flatnews_die("\$comment must be a digit",__FILE__,__LINE__);
	$comment = getparam($comment,PAR_NULL,SAN_NULL);

	$modstring="";
	if (_FN_MOD!="") $modstring = "mod="._FN_MOD."&amp;";

	$number = ($number-1);
	$data = load_news($section,$news);

	if (isset($data['comments'][$number])){
		$data['comments'][$number]['cmpost'] = strip_tags($comment);
		$data['comments'][$number]['cmlasteditby'] = _FN_USERNAME;
		$data['comments'][$number]['cmlastedit'] = time();
	}
	else {
		echo "<div style=\"text-align: center;\"><b>"._ERROR."! "._COMMENTNOTEXISTS."!</b><br>";
		//ritorno
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?$modstring"."action=viewnews&amp;news=$news\"><br><a href=\"index.php?$modstring"."action=viewnews&amp;news=$news\" title=\""._RETURN."\">"._RETURN."</a></div>";

		flatnews_logf("EDIT COMMENT: comment $number doesn't exists in the news $news in the section $section");

	}

	save_news($section,$news,$data);

	echo "<div style=\"text-align: center;\"><b>"._COMMENT_EDITED.".</b><br>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?$modstring"."action=viewnews&amp;news=$news\"><br><a href=\"index.php?$modstring"."action=viewnews&amp;news=$news\" title=\""._RETURN."\">"._RETURN."</a></div>";

	flatnews_logf("EDIT COMMENT: comment $number doesn't exists in the news $news in the section $section");
}

/**
 * Mette le news in evidenza
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da mettere in evidenza o meno
 * @param booelan $ontop true se la news deve essere in evidenza, false in caso contrario
 * @author Aldo Boccacci
 */
function set_news_ontop($section,$news,$ontop){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!check_var($ontop,"boolean")) flatnews_die("\$sticky must be a boolean value!",__FILE__,__LINE__);

	if (file_exists($newsfile)){
		if (!is_writeable($newsfile))
			flatnews_die("File ".strip_tags($newsfile)." is not writable",__FILE__,__LINE__);
		if ($ontop==TRUE){
			if (!preg_match("/top_/i",$news)){
				rename($newsfile,get_news_dir($section)."top_$news.fn.php");
				flatnews_logf("news $news in the section $section is set on top.");

				$modstring="";
				if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

				echo "<div align=\"center\"><b>"._NEWSONTOP."</b>.<br><br><a href=\"index.php$modstring\" >"._RETURN."</a>
				</div>";
				echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php$modstring\" >";
			}
		}
		else if ($ontop==FALSE){
			if (preg_match("/top_/i",$news)){
				rename($newsfile,get_news_dir($section).preg_replace("/top_/i","",$news).".fn.php");
				flatnews_logf("news $news in the section $section is set normal.");

				$modstring="";
				if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

				echo "<div align=\"center\"><b>"._NEWSNORMAL."</b>.<br><br><a href=\"index.php$modstring\" >"._RETURN."</a>
				</div>";
				echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php$modstring\" >";
			}
		}
	}
	else flatnews_die("File ".strip_tags($newsfile)." doesn't exists",__FILE__,__LINE__);
	//aggiorno il feed rss della sezione
	create_feed($section);
	//aggiorno il file newslist.php
	save_news_list($section);
}

/**
 * Nasconde o mostra la news
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da nascondere o meno
 * @param booelan $sticky true se la news deve essere nascosta, false in caso contrario
 * @author Aldo Boccacci
 */
function hide_news($section,$news,$hide){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!check_var($hide,"boolean")) flatnews_die("\$hide must be a boolean value!",__FILE__,__LINE__);

	if (file_exists($newsfile)){
		if (!is_writeable($newsfile))
			flatnews_die("File ".strip_tags($newsfile)." is not writable",__FILE__,__LINE__);
		if ($hide==TRUE){
			if (!preg_match("/hide_/i",$news)){
				//Il top_ deve essere all'inizio
				if (preg_match("/^top_/",$news)){
					rename($newsfile,get_news_dir($section)."top_hide_".preg_replace("/^top_/","",$news).".fn.php");
				}
				else rename($newsfile,get_news_dir($section)."hide_$news.fn.php");
				flatnews_logf("news $news in the section $section is hidden.");

				$modstring="";
				if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

				echo "<div align=\"center\"><b>"._NEWSHIDDEN.".</b><br><br><a href=\"index.php$modstring\" >"._RETURN."</a>
				</div>";
				echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php$modstring\" >";
			}
		}
		else if ($hide==FALSE){
			if (preg_match("/hide_/i",$news)){
				rename($newsfile,get_news_dir($section).preg_replace("/hide_/i","",$news).".fn.php");
				flatnews_logf("news $news in the section $section is visible.");

				$modstring="";
				if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;

				echo "<div align=\"center\"><b>"._NEWSSHOWN.".</b><br><br><a href=\"index.php$modstring\" >"._RETURN."</a>
				</div>";
				echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php$modstring\" >";
			}
		}
	}
	else flatnews_die("File ".strip_tags($newsfile)." doesn't exists",__FILE__,__LINE__);
	//creo il feed rss della sezione
	create_feed($section);
	//aggiorno il file newslist.php
	save_news_list($section);
}

/**
 * Interfaccia per gestire le news proposte dagli utenti
 *
 * @author Aldo Boccacci
 */
function manage_proposed_news_interface(){
	$proposednewslist = load_proposed_news_list();
	if (count($proposednewslist)==0){
		echo "<div align=\"center\"><b>"._NOPUBNEWS.".</b><br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
	}


	$key = array();
	for ($n=0;$n<count($proposednewslist);$n++){
		$section = $proposednewslist[$n]['section'];
		$ordered[$section][] = $proposednewslist[$n]['news'];
		if (!in_array($section,$key))
			$key[]=$section;
// 		$data = load_news($proposednewslist[$n]['section'],$proposednewslist[$n]['news']);
// 		echo "» ";
	}

	for ($n=0;$n<count($ordered);$n++){
		$currentkey=$key[$n];
		if ($currentkey=="none_News")
			echo "<h2>Home Page</h2>";
		else echo "<h2>$currentkey</h2>";
		for ($i=0;$i<count($ordered[$currentkey]);$i++){
			$data = load_news($currentkey,$ordered[$currentkey][$i],TRUE);
			echo "» <a href=\"index.php?mod=$currentkey&amp;action=viewproposednews&amp;section=$currentkey&amp;news=".$ordered[$currentkey][$i]."\" title=\""._FLEGGI."\">".$data['title']."</a> by ".$data['by']."<br/>";
		}
	}
// print_r($ordered);
}

/**
 * Pubblica la news proposta dagli utenti
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da pubblicare
 * @author Aldo Boccacci
 */
function publish_proposed_news($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);

	$proposednewsfile = get_proposed_news_file($section,$news);
	if (!check_path($proposednewsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$proposednewsfile is not valid!".strip_tags($proposednewsfile),__FILE__,__LINE__);

	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!file_exists($proposednewsfile))
		flatnews_die("The news ".strip_tags($news)." in the section ".strip_tags($section)." doesn't exists!",__FILE__,__LINE__);

	if (!is_writable(get_news_dir($section))){
		echo "<div align=\"center\"><b>"._THESECTION." ".strip_tags($section)." "._FDNOTWRITE.".</b> "._FDCHECKPERM.".<br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
		return;
	}

	$modstring ="";
	if (_FN_MOD=="")
		$modstring = "mod=none_News";
	else $modstring = "mod="._FN_MOD;

	//controllo se riesco a pubblicare la news
	if (rename($proposednewsfile,$newsfile)){
		$data = load_news_header($section,$news);
		//aggiungo i tag alla lista generale
		add_tags_in_tags_list($data['tags']);
		//aggiungo i tag alla lista della sezione
		add_tags_in_tags_list($data['tags'],$section);

		echo "<div align=\"center\"><b>"._THENEWS." ".strip_tags($news)."  "._NEWSPUBLISHED." ".strip_tags($section)."</b>.<br><br>";

		echo "<a href=\"index.php?$modstring\"><b>"._GOTOSECTION."</b></a> | <a href=\"index.php?$modstring&amp;action=viewnews&amp;news=$news\"><b>"._READNEWS."</b></a>";

		//aggiorno la lista delle news proposte dagli utenti
		save_proposed_news_list(load_proposed_news_list());

		//fix by Roberto Balbi
		//aggiorno il file newslist.php
		save_news_list($section);

		//creo il feed rss della sezione
		create_feed($section);
		//aggiorno il feed rss del sito
		create_general_feed();

		//salvo il file di log
		if ($section=="none_News")
			$sectionshow="Home";
		else $sectionshow = $section;
		flatnews_logf("News ".strip_tags($news)." was published in the section ".strip_tags($sectionshow));
		//controllo se ci sono ancora news da approvare
		$count = count(load_proposed_news_list());
		if ($count>0){
			//se sì, stampo i link per amministrarle
			echo "<br><br><a href=\"index.php?$modstring"."&amp;action=manageproposednews\" title=\""._SEGNNOTIZIE."\">"._MANAGEOTHERPROPOSEDNEWS.". ($count)</a>";
		}
		echo "</div>";
	}
	else {
		//se non riesco a pubblicare la news stampo un avviso
		echo "<div align=\"center\"><br><br>"._NOTPOSSIBLEPUBLISHNEWS.".";
		$count = count(load_proposed_news_list());
		if ($count>0){
			echo "<a href=\"index.php?$modstring"."&amp;action=manageproposednews\" title=\""._SEGNNOTIZIE."\">&lt;&lt; "._MANAGEOTHERPROPOSEDNEWS.". ($count)</a></div>";
		}
		echo "</div>";
	}
}

/**
 * Interfaccia per spostare una news
 *
 * @param string $section la sezione della news
 * @param string $news la news da spostare
 * @author Aldo Boccacci
 */
function move_news_interface($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);
	$mod = _FN_MOD;
	$news_sections = load_news_sections_list();
	if (count($news_sections)<2){
		echo "<div align=\"center\"><b>"._NOOTHERNEWSSECT."</b><br><br><a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";
		return;
	}
	$data = load_news($section,$news);

	$modstring="";
	if (_FN_MOD!="") $modstring = "?mod="._FN_MOD;
	else $modstring = "?mod=none_News";

	echo "<form action=\"index.php$modstring\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"newsaction\" readonly=\"readonly\" value=\"movenews\" />";
	echo "<input type=\"hidden\" name=\"section\" readonly=\"readonly\" value=\"$section\" />";
	echo "<input type=\"hidden\" name=\"news\" readonly=\"readonly\" value=\"$news\" />";
	echo "Sposta la notizia <b>".$data['title']."</b>:";
	echo "<br><br><select name=\"destsection\">";
	echo "<option selected=\"selected\" disabled=\"disabled\">"._FNCHOOSEDEST."</option>";
	$destsection="";
	foreach ($news_sections as $destsection){
		$destpath = get_news_dir($destsection);
		if ($destsection=="none_News")
			$destsectionshow = "Home";
		else $destsectionshow = preg_replace("/^none_/","",$destsection);

		if (is_dir($destpath) and is_writable($destpath)){
			if (!file_exists($destpath."/".get_news_file($news)))
				if ($destsection!=$section)
					echo "<option value=\"".rawurlencodepath($destsection)."\">$destsectionshow</option>";
				//se è la cartella di partenza non mostro nulla
// 				else echo "<option disabled=\"disabled\" selected=\"selected\">$destsectionshow</option>";
		}
		else echo "<option disabled=\"disabled\" title=\""._FIG_ALERTNOTWR."\">$destsectionshow</option>";
	}
	echo "</select>";
	echo "<br><br><input type=\"submit\" name=\"ffok\" value=\""._MOVE."\" />";
	echo "</form>";
}

/**
 * Funzione che sposta una news in un'altra sezione
 *
 * @param string $section la sezione della news
 * @param string $news la news da spostare
 * @param string $dectsection la sezione in cui spostare la notizia
 * @author Aldo Boccacci
 */
function move_news($section,$news,$destsection){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$section = rawurldecode($section);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);

	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$destsection = getparam($destsection,PAR_NULL,SAN_FLAT);
	$destsection = rawurldecode($destsection);
	$destnewsfile = get_news_file($destsection,$news);
	$destnewsfile = rawurldecode($destnewsfile);
	if (!check_path($destnewsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$destnewsfile is not valid!".strip_tags($destnewsfile),__FILE__,__LINE__);

	if (!file_exists($newsfile)){
		flatnews_die("<b>".ATTENTION."</b>: the topic ".strip_tags($newsfile)." doesn't exists!");
	}

	if (!is_writable($newsfile)){
		flatnews_die("<b>".ATTENTION."</b>: the news ".strip_tags($newsfile)." is not writeable. It's impossible to move it!");
	}

	$destpath = get_news_dir($destsection);
	if (!is_dir($destpath)){
		flatnews_die("<b>".ATTENTION."</b>: the section ".strip_tags($destpath)." is not a valid directory!");
	}

	if (!is_writable($destpath)){
		flatnews_die("<b>".ATTENTION."</b>: the directory ".strip_tags($destpath)." is not writeable! Please check permissions!");
	}

	if (rename($newsfile,$destnewsfile)){
		echo "<div style=\"text-align: center;\"><b>"._NEWSMOVED."!</b>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($section)."\"><b>"._RETURN."</b></a>";
		echo " | <a href=\"index.php?mod=".rawurlencodepath($destsection)."\"><b>"._GOTOSECTION."</b></a>";
		echo "</div>";
		//aggiorno i feed nelle sezioni di partenza e di arrivo
		create_feed($section);
		create_feed($destsection);
		//aggiorno i file newslist nelle sezioni di partenza e di arrivo
		save_news_list($section);
		save_news_list($destsection);
		$newsdata = load_news($destsection,$news);
		//rimuovo i tag dalla sezione di partenza
		remove_tags_from_tags_list($newsdata['tags'],$section);
		//aggiungo i tags alla sezione di arrivo
		add_tags_in_tags_list($newsdata['tags'],$destsection);
		// i tags globali restano invariati
	}
	else{
		echo "<div style=\"align: center;\"><b>".NEWSNOTMOVED."!</b>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($section)."\"><b>"._RETURN."</b></a></div>";
	}
}


/**
 * Funzione che cambia la data di una news
 *
 * @param string $section la sezione della news
 * @param string $news la news da rinominare
 * @param string $newdate la nuova data della news
 * @author Aldo Boccacci
 */
function change_news_date($section,$news,$newdate){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$newdate = getparam($newdate,PAR_NULL,SAN_FLAT);
	$destnewsfile = get_news_file($section,$newdate);
	if (!check_path($destnewsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$destnewsfile is not valid!".strip_tags($destnewsfile),__FILE__,__LINE__);

	if (!file_exists($newsfile)){
		flatnews_die("<b>".ATTENTION."</b>: the topic ".strip_tags($newsfile)." doesn't exists!");
	}

	if (!is_writable($newsfile)){
		flatnews_die("<b>".ATTENTION."</b>: the news ".strip_tags($newsfile)." is not writeable. It's impossible to change the date!");
	}


	if (!is_writable($section)){
		flatnews_die("<b>".ATTENTION."</b>: the directory ".strip_tags($section)." is not writeable! Please check permissions!");
	}

	if (rename($newsfile,$destnewsfile)){
		echo "<div style=\"text-align: center;\"><b>"._NEWSDATECHANGED."!</b>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($section)."&amp;action=viewnews&amp;news=$news\"><b>"._READNEWS."</b></a>";
		echo " | <a href=\"index.php?mod=".rawurlencodepath($section)."\"><b>"._GOTOSECTION."</b></a>";
		echo "</div>";
		//aggiorno il feed rss della sezione
		create_feed($section);
		//aggiorno il file newslist.php
		save_news_list($section);
	}
	else{
		echo "<div style=\"align: center;\"><b>"._NEWSDATENOTCHANGED."!</b>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($section)."\"><b>"._RETURN."</b></a></div>";
	}
}

?>