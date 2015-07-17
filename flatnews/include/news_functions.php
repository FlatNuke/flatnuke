<?php

include_once("functions.php");
include_once("download/include/fdfunctions.php");

/**
 * Restituisce la versione di Flatnews
 *
 * @return la versione di FlatForum
 * @author Aldo Boccacci
 * @since 0.1
 */
function get_flatnews_version(){
	return "0.1";
}

/**
 * Funzione di die() personalizzata per flatnews
 *
 * @param string $message il messaggio
 * @param string $file il file nel quale si è manifestato l'errore
 * @param string $line la linea dell'errore
 * @author Aldo Boccacci
 * @since 0.1
 */
function flatnews_die($message="",$file="",$line=""){
	if ($file!="" and check_path($file,"","true")) $file=strip_tags(basename(trim($file)));
	else $file="";
	if (check_var(trim($line),"digit")) $line=strip_tags(trim($line));
	else $line="";

	if ($file!="" and $line!="")
		$message = "$message $file: $line";

	flatnews_logf($message,"ERROR");
	die("Flatnews error: ".$message);

}

/**
 * Questa funzione serve per salvare il log di flatnews
 * Il messaggio viene formattato aggiungendo campi di interesse.
 *
 * @param string $message il messaggio da salvare
 * @param string $type il tipo di messaggio. Può essere lasciato vuoto o
 *               impostato a "ERROR"
 * @author Aldo Boccacci
 * @since 0.1
 */
function flatnews_logf($message,$type="") {
	$fflogfile=_FN_VAR_DIR."/log/flatnewslog.php";
// 	if (!isset($fflogfile)) $fflogfile="misc/log/flatnewslog.php";

	if (preg_match("/\<\?/",$message) or preg_match("/\?\>/",$message)) flatnews_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if ($type=="ERROR"){
		$fflogfile = preg_replace("/\.php$/i","error.php",$fflogfile);
	}

	if (!is_dir(_FN_VAR_DIR."/log/")){
		fn_mkdir(_FN_VAR_DIR."/log/",0777);

	}

	if (!file_exists("$fflogfile")){
		fnwrite($fflogfile,"<?php exit(1);?>\n","w",array("nonull"));
	}
	else {
		$logtext="";
		$logtext = get_file($fflogfile);
		if (!preg_match("/\<\?php exit\(1\);\?\>/i",$logtext)){
			fnwrite($fflogfile,"<?php exit(1);?>\n$logtext","w",array("nonull"));
		}
	}


	//l'utente collegato
	$myforum="";
	if (isset($_COOKIE['myforum'])) $myforum = strip_tags($_COOKIE['myforum']);
// 	if (!is_alphanumeric($myforum)) $myforum ="";
	if (!versecid($myforum)) $myforum .= "(NOT VALID!)";
	$REMOTE_ADDR="";
	if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];
	else $REMOTE_ADDR="";
	if (isset($_GET['mod'])) $mod=$_GET['mod'];
	else $mod="";

	$messageok = date(_FDDATEFORMAT)."
	flatnews version: ".get_flatnews_version()."
	user: $myforum
	remoteaddr: $REMOTE_ADDR
	section: $mod
	message: $message";

	$fl=fopen("$fflogfile","a");
	fwrite($fl, strip_tags("$messageok\n"));
	fclose($fl);

}

/**
 * Carica la news e restituisce un array con i dati controllati
 * struttura dell'array:
 *
 * @param string $section la sezione conenente la news
 * @param string $news la news da caricare (il nome del file senza percorso o estensione)
 * @param boolean $proposed se settato a TRUE carica una notizia proposta da un utente, FALSE
 * 		  una notizia normale
 * @return un array con i dati della news
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_news($section,$news,$proposed=FALSE){
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

	if ($section=="") return NULL;
	if ($news=="") return NULL;

	if (!is_file($newsfile)) return NULL;

	$data = array();
	$string = "";
	$string = get_file($newsfile);

	//CARICO LE PROPRIETÀ DELLA NEWS

	//titolo della news
	$newstitle = "";
	$newstitle = get_xml_element("title",$string);
	if (check_var($newstitle,"text"))
		$data['title'] = $newstitle;
	else $data['title'] = "";

	//categoria news
	$newscategory = "";
	$newscategory = get_xml_element("category",$string);
	if (check_var($newscategory,"text"))
		$data['category'] = $newscategory;
	else $data['category'] = "nonews.png";


	//Tags
	$data['tags'] = array();
	$newstagsok = array();
	$tags_string="";
	$tags_string = get_xml_element("tags",$string);
	$newstags = get_xml_array("tag",$tags_string);

	$count_tags=0;
	for ($count_tags=0;$count_tags<count($newstags);$count_tags++){
		if (check_var($newstags[$count_tags],"text")){
			if (trim($newstags[$count_tags])!="")
				$data['tags'][]= trim($newstags[$count_tags]);
		}
	}


	//letture news
	$newsreads = "";
	$newsreads = get_xml_element("reads",$string);
	$newsreads=trim($newsreads);
	if (check_var($newsreads,"digit"))
		$data['reads'] = $newsreads;
	else $data['reads'] = "0";

	//categoria news
	$newsby = "";
	$newsby = get_xml_element("by",$string);
	if (check_var($newsby,"text"))
		$data['by'] = $newsby;
	else $data['by'] = "";

	//DATA
	//al momento la leggo dal nome del file
	$newsdate ="";
	$newsdate = get_news_time($newsfile);
	if (!check_var($newsdate,"digit")) $newsdate="0";
	$data['date'] =$newsdate;

	//autore ultima modifica
	$newslasteditby = "";
	$newslasteditby = get_xml_element("lasteditby",$string);
	if (check_var($newslasteditby,"text"))
		$data['lasteditby'] = $newslasteditby;
	else $data['lasteditby'] = "";

	//data ultima modifica
	$newslastedit = "";
	$newslastedit = get_xml_element("lastedit",$string);
	if (check_var($newslastedit,"digit"))
		$data['lastedit'] = $newslastedit;
	else $data['lastedit'] = "";


	//la news è nascosta?
// 	$newshidden = "false";
// 	$newshidden = get_xml_element("hidden",$string);
// 	if (check_var($newshidden,"boolean"))
// 		$data['hidden'] = $newshidden;
// 	else $data['hidden'] = "false";

	//livello della news
	$newslevel = "";
	$newslevel = get_xml_element("level",$string);
	if ($newslevel>-1 and $newslevel<11)
		$data['level'] = $newslevel;
	else $data['level'] = "-1";

	//totale voti
	$totalvote = "";
	$totalvote = get_xml_element("totalvote",$string);
	$totalvote=trim($totalvote);
	if (check_var($totalvote,"digit"))
		$data['totalvote'] = $totalvote;
	else $data['totalvote'] = "0";

	//punteggio totale dei voti
	$totalscore = "";
	$totalscore = get_xml_element("totalscore",$string);
	$totalscore=trim($totalscore);
	if (check_var($totalscore,"digit"))
		$data['totalscore'] = $totalscore;
	else $data['totalscore'] = "0";

	//header della news
	$newsheader = "";
	$newsheader = get_xml_element("header",$string);
// 	$newsheader = fn_purge_html_string($newsheader);
	$data['header'] = $newsheader;

	//header della news
	$newsbody = "";
	$newsbody = get_xml_element("body",$string);
// 	$newsbody = fn_purge_html_string($newsbody);
	$data['body'] = $newsbody;

	//COMMENTI
	$data['comments']=array();
	$comments_string= "";
	$comments_string = get_xml_element("comments",$string);
// 	echo $comments_string;
	$comments_array = get_xml_array("comment",$comments_string);

	for ($count_comments=0;$count_comments<count($comments_array);$count_comments++){
		$comment = $comments_array[$count_comments];

		//Autore del commento
		$cmby = "";
		$cmby = get_xml_element("cmby",$comment);
		if (check_var($cmby,"text"))
			$data['comments'][$count_comments]['cmby'] = $cmby;
		else $data['comments'][$count_comments]['cmby'] = "";

		//data del commento
		$cmdate = "";
		$cmdate = trim(get_xml_element("cmdate",$comment));
		if (check_var($cmdate,"digit"))
			$data['comments'][$count_comments]['cmdate'] = $cmdate;
		else $data['comments'][$count_comments]['cmdate'] = "";

		//Autore dell'ultima modifica al commento
		$cmlasteditby = "";
		$cmlasteditby = get_xml_element("cmlasteditby",$comment);
		if (check_var($cmlasteditby,"text"))
			$data['comments'][$count_comments]['cmlasteditby'] = $cmlasteditby;
		else $data['comments'][$count_comments]['cmlasteditby'] = "";

		//data dell'ultima modifica al commento
		$cmlastedit = "";
		$cmlastedit = trim(get_xml_element("cmlastedit",$comment));
		if (check_var($cmlastedit,"digit"))
			$data['comments'][$count_comments]['cmlastedit'] = $cmlastedit;
		else $data['comments'][$count_comments]['cmlastedit'] = "";

		//il commento
		$cmpost = "";
		$cmpost = get_xml_element("cmpost",$comment);
		$cmpost= strip_tags($cmpost);
		$data['comments'][$count_comments]['cmpost'] = $cmpost;

	}

	return $data;
}

/**
 * Carica la news (soltanto i dati utili per l'header) e restituisce un array con i dati controllati
 * struttura dell'array:
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da caricare (il nome del file senza percorso o estensione)
 * @return un array con i dati della news (solo quelli necessari per la sezione news generale)
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_news_header($section,$news){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	$news = getparam($news,PAR_NULL,SAN_NULL);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_file($newsfile)) return NULL;

	$data = array();
	$datastring = get_file($newsfile);
	if (function_exists("simplexml_load_string"))
		$xml = @simplexml_load_string($datastring);
	else $xml=FALSE;
	if (!$xml){
// 		fdlogf("SIMPLEXML: I was not able to load the file ".
// 			strip_tags(basename($file.".description"))." using simplexml_load_file.","ERROR");
// 		return NULL;
		return load_news_header_old($section,$news);
	}

	//CARICO LE PROPRIETÀ DELLA NEWS

	//titolo della news
	$newstitle = "";
	$newstitle = $xml->title;
	if (check_var($newstitle,"text"))
		$data['title'] = $newstitle;
	else $data['title'] = "nonews.png";

	//categoria news
	$newscategory = "";
	$newscategory = $xml->category;
	if (check_var($newscategory,"text"))
		$data['category'] = $newscategory;
	else $data['category'] = "";

	//Tags
	$data['tags'] = array();
	$newstagsok = array();
	$tags="";

	$tags = $xml->tags;
// 	print_r($tags);die();
	foreach ($tags->tag as $tag){
		$newstags[]=$tag;
	}
// 	$newstags = get_xml_array("tag",$tags_string);

	$count_tags=0;
	for ($count_tags=0;$count_tags<count($newstags);$count_tags++){
		if (check_var($newstags[$count_tags],"text")){
			if (trim($newstags[$count_tags])!="")
				$data['tags'][]= trim($newstags[$count_tags]);
		}
	}

	//letture news
	$newsreads = "";
	$newsreads = $xml->reads;
	$newsreads=trim($newsreads);
	if (check_var($newsreads,"digit"))
		$data['reads'] = $newsreads;
	else $data['reads'] = "0";

	//categoria news
	$newsby = "";
	$newsby = $xml->by;
	if (check_var($newsby,"text"))
		$data['by'] = $newsby;
	else $data['by'] = "";

	//DATA
	//al momento la leggo dal nome del file
	$newsdate ="";
	$newsdate = get_news_time($newsfile);
	if (!$newsdate) $newsdate="0";
	$data['date'] =$newsdate;
	//autore ultima modifica
	$newslasteditby = "";
	$newslasteditby = $xml->lasteditby;
	if (check_var($newslasteditby,"text"))
		$data['lasteditby'] = $newslasteditby;
	else $data['lasteditby'] = "";

	//data ultima modifica
	$newslastedit = "";
	$newslastedit = $xml->lastedit;
	if (check_var($newslastedit,"digit"))
		$data['lastedit'] = $newslastedit;
	else $data['lastedit'] = "";



	//livello della news
	$newslevel = "";
	$newslevel = $xml->level;
	if ($newslevel>-1 and $newslevel<11)
		$data['level'] = $newslevel;
	else $data['level'] = "-1";

	//totale voti
	$totalvote = "";
	$totalvote = $xml->totalvote;
	$totalvote=trim($totalvote);
	if (check_var($totalvote,"digit"))
		$data['totalvote'] = $totalvote;
	else $data['totalvote'] = "0";

	//punteggio totale dei voti
	$totalscore = "";
	$totalscore = $xml->totalscore;
	$totalscore=trim($totalscore);
	if (check_var($totalscore,"digit"))
		$data['totalscore'] = $totalscore;
	else $data['totalscore'] = "0";

	//header della news
	$newsheader = "";
	$newsheader = $xml->header->asXML();
	$oktmp = preg_match("/\<header\>(.*?)\<\/header\>/s",$newsheader,$newsheader);
	//i seguenti non funzionano a dovere...
// 	$newsheader = str_replace("<header>","",$newsheader);
// 	$newsheader = str_replace("</header>","",$newsheader);
// 	$newsheader = fn_purge_html_string($newsheader);
	$data['header'] = $newsheader[1];

	//body della news
	$newsbody = "";
	$newsbody = $xml->body->asXML();
	$oktmp = preg_match("/\<body\>(.*?)\<\/body\>/s",$newsbody,$newsbody);

	$data['body'] = (empty($newsbody[1])) ? ("") : ($newsbody[1]);

	//COMMENTI
	$data['comments']=array();
	$comments= "";
	$comments = $xml->comments;
	foreach ($comments->comment as $comment){
		$cmby = "";
		$cmby = $comment->cmby;
		if (check_var($cmby,"text"))
			$data['comments'][]['cmby'] = $cmby;
		else $data['comments'][]['cmby'] = "";
	}

	return $data;
}

/**
 * Carica la news (soltanto i dati utili per l'header) e restituisce un array con i dati controllati
 * struttura dell'array:
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da caricare (il nome del file senza percorso o estensione)
 * @return un array con i dati della news (solo quelli necessari per la sezione news generale)
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_news_header_old($section,$news){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	$news = getparam($news,PAR_NULL,SAN_NULL);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_file($newsfile)) return NULL;

	$data = array();
	$string = "";
	$string = get_file($newsfile);

	//CARICO LE PROPRIETÀ DELLA NEWS

	//titolo della news
	$newstitle = "";
	$newstitle = get_xml_element("title",$string);
	if (check_var($newstitle,"text"))
		$data['title'] = $newstitle;
	else $data['title'] = "nonews.png";

	//categoria news
	$newscategory = "";
	$newscategory = get_xml_element("category",$string);
	if (check_var($newscategory,"text"))
		$data['category'] = $newscategory;
	else $data['category'] = "";

	//Tags
	$data['tags'] = array();
	$newstagsok = array();
	$tags_string="";
	$tags_string = get_xml_element("tags",$string);
	$newstags = get_xml_array("tag",$tags_string);

	$count_tags=0;
	for ($count_tags=0;$count_tags<count($newstags);$count_tags++){
		if (check_var($newstags[$count_tags],"text")){
			if (trim($newstags[$count_tags])!="")
				$data['tags'][]= trim($newstags[$count_tags]);
		}
	}

	//letture news
	$newsreads = "";
	$newsreads = get_xml_element("reads",$string);
	$newsreads=trim($newsreads);
	if (check_var($newsreads,"digit"))
		$data['reads'] = $newsreads;
	else $data['reads'] = "0";

	//categoria news
	$newsby = "";
	$newsby = get_xml_element("by",$string);
	if (check_var($newsby,"text"))
		$data['by'] = $newsby;
	else $data['by'] = "";

	//DATA
	//al momento la leggo dal nome del file
	$newsdate ="";
	$newsdate = get_news_time($newsfile);
	if (!$newsdate) $newsdate="0";
	$data['date'] =$newsdate;
	//autore ultima modifica
	$newslasteditby = "";
	$newslasteditby = get_xml_element("lasteditby",$string);
	if (check_var($newslasteditby,"text"))
		$data['lasteditby'] = $newslasteditby;
	else $data['lasteditby'] = "";

	//data ultima modifica
	$newslastedit = "";
	$newslastedit = get_xml_element("lastedit",$string);
	if (check_var($newslastedit,"digit"))
		$data['lastedit'] = $newslastedit;
	else $data['lastedit'] = "";



	//livello della news
	$newslevel = "";
	$newslevel = get_xml_element("level",$string);
	if ($newslevel>-1 and $newslevel<11)
		$data['level'] = $newslevel;
	else $data['level'] = "-1";

	//totale voti
	$totalvote = "";
	$totalvote = get_xml_element("totalvote",$string);
	$totalvote=trim($totalvote);
	if (check_var($totalvote,"digit"))
		$data['totalvote'] = $totalvote;
	else $data['totalvote'] = "0";

	//punteggio totale dei voti
	$totalscore = "";
	$totalscore = get_xml_element("totalscore",$string);
	$totalscore=trim($totalscore);
	if (check_var($totalscore,"digit"))
		$data['totalscore'] = $totalscore;
	else $data['totalscore'] = "0";

	//header della news
	$newsheader = "";
	$newsheader = get_xml_element("header",$string);
// 	$newsheader = fn_purge_html_string($newsheader);
	$data['header'] = $newsheader;


	//COMMENTI
	$data['comments']=array();
	$comments_string= "";
	$comments_string = get_xml_element("comments",$string);
// 	echo $comments_string;
	$comments_array = get_xml_array("comment",$comments_string);

	for ($count_comments=0;$count_comments<count($comments_array);$count_comments++){
		$comment = $comments_array[$count_comments];

		//Autore del commento
		$cmby = "";
		$cmby = get_xml_element("cmby",$comment);
		if (check_var($cmby,"text"))
			$data['comments'][$count_comments]['cmby'] = $cmby;
		else $data['comments'][$count_comments]['cmby'] = "";



	}

	return $data;
}

/**
 * Carica la news (soltanto i dati utili per l'header) e restituisce un array con i dati controllati
 * struttura dell'array:
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da caricare (il nome del file senza percorso o estensione)
 * @return un array con i tags della news
 * @author Aldo Boccacci
 * @since 0.1
 */
function get_news_tags($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_file($newsfile)) return NULL;

	$data = array();
	$string = "";
	$string = get_file($newsfile);

	//CARICO LE PROPRIETÀ DELLA NEWS


	//Tags
	$data['tags'] = array();
	$newstagsok = array();
	$tags_string="";
	$tags_string = get_xml_element("tags",$string);
	$newstags = get_xml_array("tag",$tags_string);

	$count_tags=0;
	for ($count_tags=0;$count_tags<count($newstags);$count_tags++){
		if (check_var($newstags[$count_tags],"text")){
			if (trim($newstags[$count_tags])!="")
				$data['tags'][]= trim($newstags[$count_tags]);
		}
	}

	return $data;
}

/**
 * Funzione di salvataggio della news
 *
 * @param string $section la sezione contenente la news
 * @param string $news il numero identificativo della news
 * @param array $data l'array con tutti i dati della news
 * @param boolean $proposed indica se la news è stata proposta da un utente
 * @author Aldo Boccacci
 * @since 0.1
 */
function save_news($section,$news,$data,$proposed=FALSE){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$proposed= getparam($proposed,PAR_NULL,SAN_FLAT);
	if ($proposed==FALSE)
		$newsfile = get_news_file($section,$news);
	else $newsfile = get_proposed_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_array($data)) fn_die("NEWS","\$data must be an array()! ",__FILE__,__LINE__);

	//controllo i dati
	//Il titolo della news
	if (isset($data['title'])){
		$title = stripslashes(strip_tags($data['title']));
	}
	else $title = "";

	if (isset($data['category'])){
		$category = stripslashes(strip_tags($data['category']));
		if (!check_path($category,"","false")) $category = "nonews.png";
	}
	else $category = "nonews.png";

	//evito che lo stesso tag venga inserito più di una volta
	$tags_ok = array();
	for ($ct=0;$ct<count($data['tags']);$ct++){
		if (!in_array(trim($data['tags'][$ct]),$tags_ok)){
			$tags_ok[] = trim($data['tags'][$ct]);
		}
	}
	//reimposto l'array con i tag una volta rimossi eventuali doppioni
	$data['tags'] = $tags_ok;

	$tags ="";
	if (isset($data['tags'])){
		for ($count_tags=0;$count_tags<count($data['tags']);$count_tags++){
			$tag = getparam(stripslashes(strip_tags($data['tags'][$count_tags])),PAR_NULL,SAN_HTML);
// 			if ($count_tags<count($data['tags'])-1)
				$tags .= "\t\t<tag>$tag</tag>\n";
		}

	}



	if (isset($data['reads'])){
// 		echo $data['reads'];
		$reads = stripslashes(strip_tags($data['reads']));
		if (!check_var($reads,"digit")) $reads=0;
	}
	else $reads = "0";

	if (isset($data['by'])){
		$by = stripslashes(strip_tags($data['by']));
		if (!check_username($by)) $by = "";
	}
	else $by = "";

	if (isset($data['date'])){
		$date = stripslashes(strip_tags($data['date']));
		if (!check_var($date,"digit")) {
			if (!$date!="")
				fnlog("NEWS", "ERROR: \$date(".
					strip_tags($date).") is not valid. Set date to \$news variable");
			$date = $news;
		}
	}
	else {
		$date = $news;
// 		fnlog("NEWS", "ERROR: \$date (".strip_tags($date).") is not set. Set date to \$news variable");
	}


	if (isset($data['lasteditby'])){
		$lasteditby = stripslashes(strip_tags($data['lasteditby']));
		if (!check_username($lasteditby)) $lasteditby = "";
	}
	else $lasteditby = "";


	if (isset($data['lastedit'])){
		$lastedit = stripslashes(strip_tags($data['lastedit']));
		if (!check_var($lastedit,"digit")) {
			if ($lastedit!="")
				fnlog("NEWS", "ERROR: \$lastedit (".
				strip_tags($lastedit).") is not valid. Set lastedit to \$news variable");
			$lastedit = $news;
		}
	}
	else {
		$lastedit = $news;
// 		fnlog("NEWS", "ERROR: \$lastedit (".strip_tags($lastedit).") is not set. Set lastedit to \$news variable");
	}


// 	if (isset($data['hidden'])){
// 		$hidden = stripslashes(strip_tags($data['hidden']));
// 		if (!check_var($hidden,"boolean")) $hidden = "0";
// 	}
// 	else $hidden = "0";

	if (isset($data['level'])){
		$level = stripslashes(strip_tags($data['level']));
		if (check_var($level,"digit") and $level<=10 and $level>=0) {//OK
		}
		else if ($level == "-1"){//OK
		}
		else $level="-1";
	}
	else $level = "-1";

	if (isset($data['totalvote'])){
		$totalvote = stripslashes(strip_tags($data['totalvote']));
		if (!check_var($totalvote,"digit")) $totalvote=0;
	}
	else $totalvote = "0";

	if (isset($data['totalscore'])){
		$totalscore = stripslashes(strip_tags($data['totalscore']));
		if (!check_var($totalscore,"digit")) $totalscore=0;
	}
	else $totalscore = "0";

	if (isset($data['header'])){
// 		$header = stripslashes(fn_purge_html_string($data['header'],"user"));
		$header = stripslashes($data['header']);
	}
	else $header="";

	if (isset($data['body'])){
// 		$body = stripslashes(fn_purge_html_string($data['body'],"user"));
		$body = stripslashes($data['body']);
	}
	else $body="";

	$datastring = "";
	$datastring = "<news xmlns:fn=\"http://flatnuke.sourceforge.net/news\">
	<title>$title</title>
	<category>$category</category>
	<tags>\n$tags\t</tags>
	<reads>$reads</reads>
	<by>$by</by>
	<date>$date</date>
	<lasteditby>$lasteditby</lasteditby>
	<lastedit>$lastedit</lastedit>
	<level>$level</level>
	<vote>
		<totalvote>$totalvote</totalvote>
		<totalscore>$totalscore</totalscore>
	</vote>
	<header>$header</header>
	<body>$body</body>
	<comments>";

	//COMMENTI
	if (!isset($data['comments']))
		$data['comments'] = array();
	foreach ($data['comments'] as $comment){
// 	for ($n=0;$n<count($data['comments']);$n++){
// 		if (!isset($data['comments'][$n])){
// 			$n++;
// 			continue;
// 		}
// 		else $comment = $data['comments'][$n];

		if (isset($comment['cmby'])){
			$cmby = stripslashes(strip_tags($comment['cmby']));
			if (!check_username($cmby)){
				if ($cmby!="")
					fnlog("NEWS","Variable \$cmby (".$cmby.") is not valid!");
				$cmby="";
			}
		}
		else $cmby="";

		if (isset($comment['cmdate'])){
			$cmdate = stripslashes(strip_tags($comment['cmdate']));
			if (!check_var($cmdate,"digit")){
				$cmdate="";
				fnlog("NEWS","Variable \$cmdate (".$cmdate.") is not valid!");
			}
		}
		else $cmdate="";

		if (isset($comment['cmlasteditby'])){
			$cmlasteditby = stripslashes(strip_tags($comment['cmlasteditby']));
			if (!check_username($cmlasteditby)){
				if ($cmlasteditby!="")
					fnlog("NEWS","Variable \$cmlasteditby (".$cmlasteditby.") is not valid!");
				$cmlasteditby="";
			}
		}
		else $cmlasteditby="";

		if (isset($comment['cmlastedit'])){
			$cmlastedit = stripslashes(strip_tags($comment['cmlastedit']));
			if (!check_var($cmlastedit,"digit")){
				if ($cmlastedit!="")
					fnlog("NEWS","Variable \$cmlastedit (".
						$cmlastedit.") is not valid!");
				$cmlastedit="";
			}
		}
		else $cmlastedit="";

		if (isset($comment['cmpost'])){
			$cmpost = stripslashes(strip_tags($comment['cmpost']));
		}
		else $cmpost="";

		$datastring .= "\n\t\t<comment>
			<cmby>$cmby</cmby>
			<cmdate>$cmdate</cmdate>
			<cmlasteditby>$cmlasteditby</cmlasteditby>
			<cmlastedit>$cmlastedit</cmlastedit>
			<cmpost>$cmpost</cmpost>
		</comment>";

	}
	//Completo la stringa da scrivere
	$datastring .= "\n\t</comments>
</news>";
	if (preg_match("/\<\?/",$datastring) or preg_match("/\?\>/",$datastring)) fn_die("NEWS","\$datastring cannot contains php tags! ",__FILE__,__LINE__);
	fnwrite($newsfile,"<?php die();?>\n<?xml version='1.0' encoding='UTF-8'?>\n".$datastring,"w",array("nonull")); // ISO8859-1 to UTF-8

	//non salvo il log, altrimenti ad ogni visione si avrebbe una nuova entry nel logfile
// 	flatnews_logf("news $news saved in section $section");

}


/**
 * Carica la news e restituisce la categoria della news
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da caricare
 * @return la categoria della news
 * @author Aldo Boccacci
 * @since 0.1
 */
function get_news_category($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
// 	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	if (!is_file($newsfile)) return NULL;

	$string = "";
	$string = get_file($newsfile);

	//categoria news
	$newscategory = "";
	$newscategory = get_xml_element("category",$string);
	if (check_var($newscategory,"text"))
		return $newscategory;
	else return "";
}


/**
 * restituisce true se la news è nascosta e false se è visibile dall'utente collegato
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news da caricare (il nome del file senza percorso o estensione)
 * @param boolean $dochecks se impostato a TRUE esegue i controlli sulle variabili $section e $news
 *                (utile se abbiamo la certezza che i parametri sono già stati controllati
 *                in precedenza e dunque sono già sicuri)
 * @return TRUE se la news è visibile FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 0.1
 */
function user_can_view_news($section, $news,$user="",$dochecks=TRUE){
	$dochecks = getparam($dochecks,PAR_NULL,SAN_FLAT);
	if ($dochecks==TRUE){
		$section = getparam($section,PAR_NULL,SAN_FLAT);
		$news = getparam($news,PAR_NULL,SAN_FLAT);
		$newsfile = get_news_file($section,$news);
		if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);
	}

	if (!check_username($user) and $user!="") return FALSE;
	$user = trim($user);
	if ($user==""){
		$user = _FN_USERNAME;
	}

	if (!preg_match("/hide_/",$news)){
		return TRUE;
	}
	else {
		if (_FN_IS_ADMIN or is_news_moderator($user))
			return TRUE;
		else return FALSE;
	}
	//**************************************
	//per il momento restituisco sempre TRUE
	//**************************************
	return TRUE;

	$data = array();
	$string = "";
	$string = get_file($newsfile);
// 	return TRUE;
	//la news è nascosta?
	$newshidden = "false";
	$newshidden = get_xml_element("hidden",$string);
	if (!check_var($newshidden,"boolean")) $newshidden = "false";

	//livello della news
	$newslevel = "";
	$newslevel = get_xml_element("level",$string);
	if ($newslevel>-1 and $newslevel<11 and $newslevel!="") {
	//OK
	}
	else $newslevel = "-1";

	if ($newshidden!="false"){
		$userlevel = FN_USERLEVEL;
// 		echo $userlevel;
		if ($userlevel>=$newslevel) return TRUE;
		else return FALSE;
	}

}

/**
 * Elenca le news della sezione specificata
 *
 * @param string $section la sezione
 * @param boolean $first_on_top indica se elencare prima le news in evidenza
 * @param booean $show_hidden indica se mostrare le news
 * @author Aldo Boccacci
 * @return un array contenente l'elenco delle news della sezione spedificata
 */
function list_news($section,$first_on_top="false",$show_hidden="true"){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	if (!check_var($first_on_top,"boolean")) flatnews_die("\$first_on_top must be a boolean! ",__FILE__,__LINE__);
	if (!check_var($show_hidden,"boolean")) flatnews_die("\$show_hidden must be a boolean! ",__FILE__,__LINE__);

	if (!is_dir(get_news_dir($section))) return array();

	$sortednews = array();
	//news on top
	if ($first_on_top=="true" or $first_on_top==TRUE){
		$important_news = array();
		$important_news = glob(get_news_dir($section)."top_*.fn.php");
		if (!$important_news) $important_news = array(); // glob may returns boolean false instead of an empty array on some systems
		$isortednews=array();
// 		print_r($important_news);
		$inews="";
		if (count($important_news)>0){
// 			foreach ($important_news as $inews){
			for ($n=0;$n<count($important_news);$n++){
				$inews=$important_news[$n];
				$inews = basename($inews);
				$inews = preg_replace("/\.fn\.php$/","",$inews);
				$time = "";
				$newsdata = array();
				$time = get_news_time($inews);
				if ($show_hidden=="false" and preg_match("/hide_/",basename($inews))) continue;
				$isortednews[$time] = $inews;
			}
		}
// 		print_r($isortednews[$time]);
		if (count($isortednews)!=0)
			krsort($isortednews);

		$normal_news = array();
		$normal_news = glob(get_news_dir($section)."*.fn.php");
		if (!$normal_news) $normal_news = array(); // glob may returns boolean false instead of an empty array on some systems
// 		$nnews = "";
		$nsortednews = array();
		//news normali
		if (count($normal_news)>0){
// 			foreach ($nnews as $nnews){
			for ($n=0;$n<count($normal_news);$n++){
				$nnews=$normal_news[$n];
				$nnews = basename($nnews);
				$nnews = preg_replace("/\.fn\.php$/","",$nnews);
				if (preg_match("/top_/",basename($nnews))) continue;
				$time = get_news_time($nnews);

				if ($show_hidden=="false" and preg_match("/hide_/",basename($nnews))) continue;
				$nsortednews[$time] = $nnews;
			}
		}
		if (count($nsortednews)!=0)
			krsort($nsortednews);

		if (count($isortednews)!=0){
			foreach ($isortednews as $news){
				$sortednews[] = $news;
			}
		}
		if (count($nsortednews)!=0){
			foreach ($nsortednews as $news){
				$sortednews[] = $news;
			}
		}

	}
	else {
// 		$news = array();
		$fdirectory = opendir(get_news_dir($section));
		$tmpfile="";
		$newsarray = array();
		while ($tmpfile = readdir($fdirectory)) {
			if (preg_match("/\.fn\.php$/",$tmpfile)) {
				$tmpfile = preg_replace("/\.fn\.php$/","",$tmpfile);
// 				echo "$tmpfile<br>";
// 				$news[]="$root/$group/$argument/".$tmpfile;
				array_push($newsarray, $tmpfile);
			}
		}

// 		$news = "";
		$sortednews = array();
		for ($count=0;$count<count($newsarray);$count++){
			$news = $newsarray[$count];
			//controllo se e' nascosto

			if (($show_hidden=="false" or $show_hidden==FALSE) and preg_match("/hide_/",basename($news)))
				continue;

			$time = get_news_time($news);

			$sortednewstmp[$time] = $news;
		}
// 		print_r($sortednewstmp);
		if (count($sortednewstmp)>0)
			krsort($sortednewstmp);

		for ($i=0;$i<count($sortednewstmp);$i++){
			//controlla
			$sortednewskey[] = $i;
		}
		//per avere un array avente per key i numeri da 0 a n
		if (count($sortednewstmp)==0)return array();
		$sortednews = array_combine($sortednewskey,$sortednewstmp);

	}

	//restituisco l'array
	return $sortednews;


}

/**
 * Elenca le news controllando anche che l'utente collegato abbia i permessi per vederle.
 *
 * @param string $section la sezione di cui elencare le news
 * @param boolean $all se impostato a TRUE non esegue i controlli sui permessi di visione.
 * @return un array contenente l'elenco delle news
 * @author Aldo Boccacci
 * @since 0.1
 */
function list_newsold($section,$all=FALSE){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!check_var($all,"boolean")) flatnews_die("\$all must be boolean",__FILE__,__LINE__);
	if ($section=="") {
		$section="none_News";
		flatnews_logf("\$section==\"\" in ".__FILE__.", ".__LINE__."","ERROR");
	}

	$newsarray = array();
	$handle  = opendir(get_news_dir($section));
	while ($file = readdir($handle)) {
		if (!( $file=="." or $file==".." ) and (!preg_match("/^\./",$file) and ($file!="CVS")) and preg_match("/\.fn\.php$/i",$file)) {
			if ($all==TRUE){
				array_push($newsarray,preg_replace("/\.fn\.php$/i","",$file));
			}
			else{
				if (user_can_view_news($section,preg_replace("/\.fn\.php$/i","",$file)))
					array_push($newsarray,preg_replace("/\.fn\.php$/i","",$file));
			}
// 				else echo "no $file<br>";
		}
	}
	closedir($handle);
	rsort($newsarray);

	return $newsarray;
}

/**
 * Restituisce TRUE se l'utente passato come parametro è un moderatore delle news.
 * Se non vengono passati parametri viene valutato l'utente attualmente collegato
 * al portale.
 *
 * @param string $user l'utente di cui verificare i permessi
 * @return TRUE se l'utente è un moderatore delle news, FALSE in caso contrario
 * @author Aldo Boccacci
 */
function is_news_moderator($user=""){
	if (_FN_IS_ADMIN) return TRUE;
	if (!check_username($user) and $user!="") return FALSE;
	$user = trim($user);
	if ($user==""){
		$user = _FN_USERNAME;
	}

	if (in_array($user,list_news_moderators())) return TRUE;

}

/**
 * Elenca i moderatori che possono agire sulle news.
 * GLi amministratori non vengono elencati da questa funzione.
 * @return un array contenente gli amministratori delle news.
 * @author Aldo Boccacci
 */
function list_news_moderatorsOLD(){
return array();
	$file = "";
	$file = _FN_VAR_DIR."/news_moderators.php";
	if (!file_exists($file)){
		fnwrite($file, "<?xml version='1.0' encoding='UTF-8'?>","w",array("nonull"));
		return FALSE;
	}

	$datastring ="";
	$datastring = get_file($file);
	$moderators_string= "";
	$moderators_string = get_xml_element("news_moderators",$datastring);
	$moderators_array= array();
	$moderators_array = get_xml_array("user",$moderators_string);

	$moderators_array_ok=array();
	for ($n=0;$n<count($moderators_array);$n++){
		if (file_exists(_FN_USERS_DIR."/".$moderators_array[$n].".php"))
			$moderators_array_ok[] = $moderators_array[$n];
	}

	return $moderators_array_ok;

}

/**
 * La funzione che aggiunge i commenti alle notizie
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news cui aggiungere il commento
 * @param string $post il commento da aggiungere
 * @author Aldo Boccacci
 */
function news_add_comment($section,$news,$post){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);
	if (!check_path($newsfile,_FN_SECTIONS_DIR,TRUE)) flatnews_die("\$newsfile is not valid!".strip_tags($newsfile),__FILE__,__LINE__);

	$post = trim(getparam($post,PAR_NULL,SAN_NULL));

	if (!is_file($newsfile)){
		echo "<div style=\"text-align: center\"><b>"._NORESULT."</b></div>";
		return;
	}

	if ($post==""){
		echo "<div style=\"text-align: center\"><b>The post is empty!</b><br></div>";
		echo "<a href=\"javascript:history.back()\">"._INDIETRO."</a>";
		return;
	}

	global $guestcomment;
	if ($guestcomment==0 and is_guest()){
		flatnews_die("Guests cannot add comments to news",__FILE__,__LINE__);
	}

	//only if news is proposed by a guest
	if (_FN_IS_GUEST){
		//fetch the captcha
		$captcha = strip_tags(getparam("captcha",PAR_POST,SAN_FLAT));
		// checking the value of anti-spam code inserted
		include("include/captcha/fncaptcha.php");
		$fncaptcha = new fncaptcha();
		$captchaok = $fncaptcha->checkCode($captcha);

		if(!$captchaok) {
			// anti-spam code is NOT right

			// back
			?><div style="text-align:center;"><?php
			echo "<br><b>"._CN_CODERROR."</b><br><br>";
			echo "<a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";

			return;
		}
	}

	if (is_spam($post,"words") AND !(_FN_IS_ADMIN OR _FN_IS_NEWS_MODERATOR)){
		//only for print spam word alert
		is_spam($post,"words",TRUE);
		echo "<div style=\"text-align: center;\"><br><b>"._SPAMALERT."</b><br><br>";
		echo "<a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
		return;
	}

	$data = load_news($section,$news);
	$thiscomment = count($data['comments']);
	$data['comments'][$thiscomment]['cmby']=_FN_USERNAME;
	$data['comments'][$thiscomment]['cmdate']=time();
	$data['comments'][$thiscomment]['cmlasteditby']="";
	$data['comments'][$thiscomment]['cmlastedit']="";
	$data['comments'][$thiscomment]['cmpost'] = strip_tags($post);
// 	print_r($data);
	save_news($section,$news,$data);
	flatnews_logf("ADDPOST:"._FN_USERNAME." adds a post in the news $news in the section $section");
	echo "Commento inserito";
	$fnmod= getparam("fnmod",PAR_POST,SAN_FLAT);
	$modstring ="";
	if ($fnmod!="") $modstring="mod=$fnmod&";
	header("Location: index.php?$modstring"."action=viewnews&news=$news#comments");
}

/**
 * Incrementa di una unità il contatore di visione della news indicata come parametro
 *
 * @param string $section la sezione contenente la news
 * @param string $news la news
 * @author Aldo Boccacci
 */
function news_add_read($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_news_file($section,$news);

	if (!is_file($newsfile)){
		return;
	}

	$data = load_news($section, $news);
	$data['reads']= $data['reads']+1;
	save_news($section,$news, $data);

}


/**
 * Restituisce il percorso della cartella contenente le news per la sezione indicata come parametro
 *
 * @param string $section la sezione
 * @author Aldo Boccacci
 */
function get_news_dir($section){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!is_dir(_FN_SECTIONS_DIR."/$section/none_newsdata/")){
		if (fn_mkdir(_FN_SECTIONS_DIR."/$section/none_newsdata/",0777))
			flatnews_logf("Dir "._FN_SECTIONS_DIR."/$section/none_newsdata/"." created");
		else flatnews_logf("I'm not able to create the dir "._FN_SECTIONS_DIR."/$section/none_newsdata/");
	}
	return _FN_SECTIONS_DIR."/$section/none_newsdata/";
}

/**
 * Restituisce il percorso del file della news caratterizzata dalla sezione e dalla data specificata
 *
 * @param string $section la sezione
 * @param string $news la news
 * @author Aldo Boccacci
 */
function get_news_file($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	return get_news_dir($section)."$news.fn.php";
}

/**
 * Elenca i file delle news andandoli a leggere dal file newslist.php
 *
 * @param string $section la sezione di cui restituire le news
 * @author Aldo Boccacci
 */
function load_news_list($section,$all=FALSE){
	$section = getparam($section,PAR_NULL,SAN_NULL);
	if (!check_path($section,"",FALSE)) flatnews_die("\$section is  not valid!",__FILE__,__LINE__);
	if (!check_var($all,"boolean")) flatnews_die("\$all must be boolean",__FILE__,__LINE__);

	$user = _FN_USERNAME;

	if (file_exists(get_news_dir($section)."newslist.php")){
		$datastring = get_file(get_news_dir($section)."newslist.php");
		$datastring = preg_replace("/\<\?php die\(\);\?>\n/","",$datastring);
		$xmldata = new SimpleXMLElement($datastring);

		$newslist=array();
		foreach ($xmldata->news as $news){
// 			echo $newslisttmp[$n];
			$news = (string)$news;
			if ($all==TRUE){
				$newslist[]=$news;
			}
			else {
// 			if (user_can_view_news($section,$news,$user))
				if (!preg_match("/hide_/",$news))
					$newslist[]=$news;
			}
		}
	}
	else {
		$newslist = list_news($section);
		save_news_list($section,$newslist);

	}
	return $newslist;
}


/**
 * Salva nel file newslist.php l'elenco delle news presenti nella sezione specificata come primo parametro
 * @param string $section la stringa che indica la sezione
 * @param array $news_array l'elenco delle news
 */
function save_news_list($section,$news_array=NULL){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!check_path($section,"",FALSE)) flatnews_die("\$section is  not valid!",__FILE__,__LINE__);
	if ($news_array==NULL)
		$news_array = list_news($section,"true","true");

	$datastring="<newslist>";
	for ($n=0;$n<count($news_array);$n++){
		$datastring .= "\n\t<news>".$news_array[$n]."</news>";
	}
	$datastring .="\n</newslist>";
	if (preg_match("/\<\?/",$datastring) or preg_match("/\?\>/",$datastring)) flatnews_die("\$datastring cannot contains php tags! ",__FILE__,__LINE__);
	fnwrite(get_news_dir($section)."newslist.php","<?php die();?>\n<?xml version='1.0' encoding='UTF-8'?>\n".$datastring,"w",array("nonull"));
// echo _FN_SECTIONS_DIR."/$section/none_newsdata/newslist.php";
}

/**
 * Restituisce la data della news nel formato restituito dalla funzione time()
 *
 * @param string $news la news
 * @return la data della news
 * @author Aldo Boccacci
 */
function get_news_time($news){
	//remove getparam for performance improvements
// 	$news = getparam($news,PAR_NULL,SAN_NULL);
	//rimuovo le parti che non indicano la data della news
	$search = array("hide_", "top_",".fn.php");
	$replace = array("","","");
	$time = str_replace($search,$replace,basename($news));

	if (ctype_digit("$time"))
		return $time;
	else return NULL;
}

/**
 * Restituisce TRUE se la news è nascosta, FALSE in caso contrario
 *
 * @param string $news la news
 * @return TRUE se è nascosta, FALSE in caso contrario
 */
function news_is_hidden($news){
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	if (preg_match("/^hide_|^top_hide_/i",$news))
		return TRUE;
	else return FALSE;
}

/**
 * Restituisce TRUE se la news è in evidenza, FALSE in caso contrario
 *
 * @param string $news la news
 * @return TRUE se è in evidenza, FALSE in caso contrario
 */
function news_is_ontop($news){
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	if (preg_match("/^top_/i",$news))
		return TRUE;
	else return FALSE;
}

/**
 * Salva la news proposta da un utente
 *
 * @author Aldo Boccacci
 */
function save_proposed_news(){
	$section= getparam("section",PAR_POST,SAN_FLAT);
	$news= time();
	if ($section!="") $modstring = "mod=$section";
	else $modstring="";
	if ($section=="") $section= "none_News";
	$newsfile = get_news_file($section,$news);
	if (file_exists($newsfile)){
		//se il file esiste già devo aggiornare la data della news
		//(improbabile ma possibile)
		$news = time();
	}

	global $guestnews;
	if (is_guest() and $guestnews==0){
		flatnews_die("Guests cannot propose news!",__FILE__,__LINE__);
	}
	//only if news is proposed by a guest
	if (_FN_IS_GUEST){
		//fetch che captcha
		$captcha = strip_tags(getparam("captcha",PAR_POST,SAN_FLAT));
		// checking the value of anti-spam code inserted
		include("include/captcha/fncaptcha.php");
		$fncaptcha = new fncaptcha();
		$captchaok = $fncaptcha->checkCode($captcha);

		if(!$captchaok) {
			// anti-spam code is NOT right

			// back or automatic redirect to the index after 2 seconds
			?><div style="text-align:center;"><?php
			echo "<br><b>"._CN_CODERROR."</b><br><br>";
			echo "<a href=\"javascript:history.back()\">&lt;&lt; "._FDBACK."</a></div>";

			return;
		}
	}

	$data = array();
	$data['title'] = strip_tags(getparam("news_title",PAR_POST,SAN_FLAT));
	$data['tags'] = split(",",strip_tags(getparam("news_tags",PAR_POST,SAN_HTML)));
	$data['category'] = strip_tags(getparam("news_category",PAR_POST,SAN_FLAT));
	$data['header'] = strip_tags(getparam("news_header",PAR_POST,SAN_FLAT));
	$data['body'] = strip_tags(getparam("news_body",PAR_POST,SAN_FLAT));
	$data['by'] = _FN_USERNAME;
	$data['date'] = strip_tags($news);

	//CONTROLLI ANTISPAM
	if ((is_spam($data['header'],"words") OR is_spam($data['body'],"words")) AND !(_FN_IS_ADMIN OR _FN_IS_NEWS_MODERATOR)){
		//only for print spam word alert
		is_spam($data['header'],"words",TRUE);
		//only for print spam word alert
		is_spam($data['body'],"words",TRUE);
		echo "<div style=\"text-align: center;\"><br><b>"._SPAMALERT."</b><br><br>";
		echo "<a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
		return;
	}

	if ((is_spam($data['category'],"words")) AND !(_FN_IS_ADMIN OR _FN_IS_NEWS_MODERATOR)){
		//only for print spam word alert
		is_spam($data['category'],"words",TRUE);
		echo "<div style=\"text-align: center;\"><br><b>"._CATEGORYSPAM."</b><br><br>";
		echo "<a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
		return;
	}

	if ((is_spam(getparam("news_tags",PAR_POST,SAN_FLAT),"words")) AND !(_FN_IS_ADMIN OR _FN_IS_NEWS_MODERATOR)){
		//only for print spam word alert
		is_spam(getparam("news_tags",PAR_POST,SAN_FLAT),"words",TRUE);
		echo "<div style=\"text-align: center;\"><br><b>"._TAGSPAM."</b><br><br>";
		echo "<a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
		return;
	}

	if ((is_spam($data['title'],"words")) AND !(_FN_IS_ADMIN OR _FN_IS_NEWS_MODERATOR)){
		//only for print spam word alert
		is_spam($data['title'],"words",TRUE);
		echo "<div style=\"text-align: center;\"><br><b>"._TITLESPAM."</b><br><br>";
		echo "<a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";
		return;
	}

	//salvo la news come "proposta"
	save_news($section,$news,$data,TRUE);
	echo "<br><div style=\" text-align: center;\"><b>"._NEWSSAVED."</b>. "._NEWSWAITING.".";

	//permetto di ritornare
	$modstring = "";
	if ($section=="none_News")
		$modstring = "";
	else $modstring = "?mod=".rawurlencodepath($section);

	echo "<br> <br><a href=\"index.php$modstring\"><b>"._GOTOSECTION."</b></a></div>";
	add_news_in_proposed_news_list($section,$news);

	flatnews_logf("News $news proposed in section $section");

}

/**
 * Restituisce il percorso della cartella contenente le news proposte dagli utenti
 * per la sezione indicata come parametro
 *
 * @param string $section la sezione
 * @author Aldo Boccacci
 */
function get_proposed_news_dir($section){

	if (!is_dir(_FN_VAR_DIR."/news/")){
		if (is_writable(_FN_VAR_DIR)){
			fn_mkdir(_FN_VAR_DIR."/news/",0777);
			if (is_dir(_FN_VAR_DIR."/news/"))
				flatnews_logf("Dir "._FN_VAR_DIR."/news/ created");
		}
		else flatnews_die("I'm not able to create the dir "._FN_VAR_DIR."/news/. Check permissions of the dir "._FN_VAR_DIR,__FILE__,__LINE__);
	}

	$section = getparam($section,PAR_NULL,SAN_FLAT);

	$hiddenstring="";
	//Se esiste restituisco il valore
	if (file_exists(_FN_VAR_DIR."/news/proposed_news_dir.php")){
		$hiddenstring = "none_".trim(preg_replace("/\<\?php die\(\);\?\>/i","", get_file(_FN_VAR_DIR."/news/proposed_news_dir.php")));
	}
	//altrimento creo il file
	else {
		fnwrite(_FN_VAR_DIR."/news/proposed_news_dir.php","<?php die();?>".mt_rand(),"w",array("nonull"));
		flatnews_logf("Ho creato il file contenente il nome della cartella contenente le news proposte dagli utenti");
		if (file_exists(_FN_VAR_DIR."/news/proposed_news_dir.php"))
			$hiddenstring = "none_".trim(preg_replace("/\<\?php die\(\);\?\>/i","", get_file(_FN_VAR_DIR."/news/proposed_news_dir.php")));
		else flatnews_die("Non riesco a creare il file col nome della cartella nascosta contenente le news proposte",__FILE__,__LINE__);
	}

	if (!is_dir(_FN_SECTIONS_DIR."/$section/none_newsdata/$hiddenstring/")){
		if (fn_mkdir(_FN_SECTIONS_DIR."/$section/none_newsdata/$hiddenstring",0777))
			flatnews_logf("Dir "._FN_SECTIONS_DIR."/$section/none_newsdata/$hiddenstring"." created");
		else flatnews_logf("I'm not able to create the dir "._FN_SECTIONS_DIR."/$section/none_newsdata/$hiddenstring");
	}
	return _FN_SECTIONS_DIR."/$section/none_newsdata/$hiddenstring/";
}

/**
 * Restituisce il percorso del file della news proposta dagli utenti
 * caratterizzata dalla sezione e dalla data specificata
 *
 * @param string $section la sezione
 * @param string $news la news
 * @author Aldo Boccacci
 */
function get_proposed_news_file($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	return get_proposed_news_dir($section)."$news.fn.php";
}

/**
 * Carica l'elenco delle news in attesa di approvazione da parte dell'amministratore
 *
 * @return un array contenente l'elenco delle news in attesa di approvazione
 * @author Aldo Boccacci
 */
function load_proposed_news_list(){
// 	if (!_FN_IS_ADMIN) return array();
	$data = array();
	if (file_exists(_FN_VAR_DIR."/news/proposed_news_list.php")){
		$string = get_file(_FN_VAR_DIR."/news/proposed_news_list.php");
		$string = preg_replace("/\<\?php die\(\);\?\>\n/","",$string);
		$xmldata = new SimpleXMLElement($string);
// 		print_r($xmldata);
// 		echo count($xmldata->proposednewslist);
		$n=0;
		foreach ($xmldata->proposednews as $proposednews){
			$section = (string)$proposednews->section;
			$news = (string)$proposednews->news;
			//controllo che la news esista effettivamente
			if (!file_exists(get_proposed_news_file($section,$news)))
				continue;
			$data[$n]['section'] = $section;
			$data[$n]['news'] = $news;
			$n++;
		}
	}
	else return array();
// 	print_r($data);
	return $data;
}

/**
 * Salva l'elenco delle news in attesa di approvazione
 *
 * @param array $news_array l'array contenente l'elenco delle news in attesa di approvazione
 * @author Aldo Boccacci
 */
function save_proposed_news_list($news_array){

	if (!is_array($news_array))
		flatnews_die("\$news_array must be an array",__FILE__,__LINE__);

	if (!is_dir(_FN_VAR_DIR."/news/")){
		if (is_writable(_FN_VAR_DIR)){
			fn_mkdir(_FN_VAR_DIR."/news/",0777);
			if (is_dir(_FN_VAR_DIR."/news/"))
				flatnews_logf("Dir "._FN_VAR_DIR."/news/ created");
		}
		else flatnews_die("I'm not able to create the dir "._FN_VAR_DIR."/news/. Check permissions of the dir "._FN_VAR_DIR,__FILE__,__LINE__);
	}

// 	print_r($news_array);die();
	$xmlstring = "<proposednewslist>";
	for ($n=0; $n<count($news_array);$n++){
		$section = strip_tags($news_array[$n]['section']);
		$news = strip_tags($news_array[$n]['news']);

		if (!file_exists(get_proposed_news_file($section,$news)))
			continue;
		$xmlstring .="\n\t<proposednews>
		<section>$section</section>
		<news>$news</news>
	</proposednews>";
	}
	$xmlstring .= "\n</proposednewslist>";
// 	print_r($xmlstring);die();
	if (preg_match("/\<\?/",$xmlstring) or preg_match("/\?\>/",$xmlstring)) fn_die("\$xmlstring cannot contains php tags! ",__FILE__,__LINE__);
	fnwrite(_FN_VAR_DIR."/news/proposed_news_list.php","<?php die();?>\n<?xml version='1.0' encoding='UTF-8'?>\n".$xmlstring,"w",array("nonull")); // ISO8859-1 to UTF-8

}

/**
 * Aggiunge un elemento all'elenco dei file in attesa di appprovazione
 *
 * @param array $array l'array descrittivo dell'elemento che si vuole aggiungere
 * @author Aldo Boccacci
 */
function add_news_in_proposed_news_list($section,$news){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	$news = getparam($news,PAR_NULL,SAN_FLAT);
	$newsfile = get_proposed_news_file($section,$news);

	if (!check_path($newsfile,"","true")) flatnews_die("\$newsfile is not valid!",__FILE__,__LINE__);

	if (!is_file($newsfile)){
		return;
	}

	$proposednewsarray = load_proposed_news_list();

	$n = count($proposednewsarray);
	//non è necessario incrementare il contatore!
// 	$n = $n+1;
	$proposednewsarray[$n]['section'] = $section;
	$proposednewsarray[$n]['news'] = $news;
// 	print_r($proposednewsarray);die();
	save_proposed_news_list($proposednewsarray);
}


/**
 * Ricostruisce, dopo averlo azzerato, il file con l'elenco delle news proposte
 *
 * @author Aldo Boccacci
 * @since 3.0
 */
function rebuild_proposed_news_list(){
	//deleting old entries
	fnwrite("var/news/proposed_news_list.php", "<?xml version='1.0' encoding='UTF-8'?>
<proposednewslist>\n</proposednewslist>","w",array("nonull")); // ISO8859-1 to UTF-8

	//listing news sections
	$news_sections = list_news_sections();
	//for each news section
	while ($news_section = current($news_sections)){
		//retrieve the proposed news dir
		$dir = get_proposed_news_dir($news_section);
		//listing all proposed news
		$files = scandir($dir);
		while ($newsfile = current($files)){
			// only if the file exists and is a news
			if (file_exists("$dir/$newsfile") and preg_match("/\.fn\.php$/s",trim($newsfile)))
				add_news_in_proposed_news_list($news_section, preg_replace("/\.fn\.php$/s","",trim($newsfile)));
			//go to he next news
			next($files);
		}
		//go to the next news section
		next($news_sections);
	}
}

/**
 * Carica l'elenco delle sezioni news del portale
 *
 * @return un array contenente l'elenco delle sezioni news del portale
 * @author Aldo Boccacci
 */
function load_news_sections_list(){
// 	if (!_FN_IS_ADMIN) return array();
	$data = array();
	if (file_exists(_FN_VAR_DIR."/news/news_sections_list.php")){
		$string = get_file(_FN_VAR_DIR."/news/news_sections_list.php");
		$string = preg_replace("/\<\?php die\(\);\?>\n/","",$string);
		$xmldata = new SimpleXMLElement($string);

		foreach ($xmldata->section as $tmpsection){
			$section = (string)$tmpsection;
			$section = preg_replace("/^\//s","",$section);
			$section = preg_replace("/\/$/s","",$section);
			//controllo che la sezione esista effettivamente
			if (!is_dir(_FN_SECTIONS_DIR."/$section"))
				continue;
			if (!in_array($section,$data))
				$data[]= $section;
		}
	}
	else {
		$array = list_news_sections();
		save_news_sections_list($array);
		return $array;
	}
	return $data;
}

/**
 * Salva l'elenco delle sezioni news del portale
 *
 * @param array $news_array l'array contenente l'elenco delle sezioni news
 * @author Aldo Boccacci
 */
function save_news_sections_list($news_array){

	if (!is_array($news_array))
		flatnews_die("\$news_array must be an array",__FILE__,__LINE__);

	if (!is_dir(_FN_VAR_DIR."/news/")){
		if (is_writable(_FN_VAR_DIR)){
			fn_mkdir(_FN_VAR_DIR."/news/",0777);
			if (is_dir(_FN_VAR_DIR."/news/"))
				flatnews_logf("Dir "._FN_VAR_DIR."/news/ created");
		}
		else flatnews_die("I'm not able to create the dir "._FN_VAR_DIR."/news/. Check permissions of the dir "._FN_VAR_DIR,__FILE__,__LINE__);
	}
// 	print_r($news_array);die();
	$xmlstring = "<sections>";
	for ($n=0; $n<count($news_array);$n++){
		$section = strip_tags($news_array[$n]);

		if (!file_exists(_FN_SECTIONS_DIR."/$section/news"))
			continue;
		$section = preg_replace("/^\//s","",$section);
		$section = preg_replace("/\/$/s","",$section);
		$xmlstring .="\n\t<section>$section</section>";
	}
	$xmlstring .= "\n</sections>";
// 	print_r($xmlstring);die();
	if (preg_match("/\<\?/",$xmlstring) or preg_match("/\?\>/",$xmlstring)) fn_die("\$xmlstring cannot contains php tags! ",__FILE__,__LINE__);

	fnwrite(_FN_VAR_DIR."/news/news_sections_list.php","<?php die();?>\n<?xml version='1.0' encoding='UTF-8'?>\n".$xmlstring,"w",array("nonull")); // ISO8859-1 to UTF-8

}

/**
 * Aggiunge un elemento all'elenco delle sezioni news del portale
 *
 * @param string $section la sezione che si vuole aggiungere
 * @author Aldo Boccacci
 */
function add_section_in_news_section_list($section){
	$section = getparam($section,PAR_NULL,SAN_FLAT);

	if (!check_path($section,"","false")) flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	$news_section_list = load_news_sections_list();
	$section = preg_replace("/^\//s","",$section);
	$section = preg_replace("/\/$/s","",$section);
	if (!in_array($section,$news_section_list));
		$news_section_list[] = $section;

	save_news_sections_list($news_section_list);
}

/**
 * Elenca le sezioni news presenti nel portale, andando a cercarle scorrendo ricorsivamente le sezioni
 *
 * @author Aldo Boccacci
 */
function list_news_sections(){

	include_once("include/filesystem/DeepDir.php");
	$files = NULL;
	$files = new DeepDir();
	$files ->setDir(_FN_SECTIONS_DIR);
	$files ->load();
	if (!count($files->dirs)==0){
		$dir ="";
		foreach($files->dirs as $dir){
			if (file_exists($dir."/news") and is_file($dir."/news")) {
				$dir = preg_replace("/^"._FN_SECTIONS_DIR."/i","",$dir);
				$dirlist[] = preg_replace("/^\//","",$dir);
			}
		}
	}
	return $dirlist;
}


/**
 * Salva l'elenco dei tag col contatore
 * struttura dell'array passato come parametro:
 * data[$tag]= 'numero occorrenze del tag'
 *
 * @param array $news_array l'array contenente l'elenco delle sezioni news
 * @param string $section se settata salva l'elenco nella sezione specificata anzichè
 *               nell'elenco generale
 * @author Aldo Boccacci
 */
function save_tags_list($tags_array,$section=""){

	if (!is_array($tags_array))
		flatnews_die("\$news_array must be an array",__FILE__,__LINE__);

	if ($section!="" and !check_path($section,"","false"))
		flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	if (!is_dir(_FN_VAR_DIR."/news/")){
		if (is_writable(_FN_VAR_DIR)){
			fn_mkdir(_FN_VAR_DIR."/news/",0777);
			if (is_dir(_FN_VAR_DIR."/news/"))
				flatnews_logf("Dir "._FN_VAR_DIR."/news/ created");
		}
		else flatnews_die("I'm not able to create the dir "._FN_VAR_DIR."/news/. Check permissions of the dir "._FN_VAR_DIR,__FILE__,__LINE__);
	}

	//eseguo il sorting sulle keys
	ksort($tags_array);

	$xmlstring = "<tags>";

	foreach ($tags_array as $tag => $count){
		if ($count==0) continue;
// 		echo "$tag: $count <br>";
		$tag = getparam(strip_tags($tag),PAR_NULL,SAN_HTML);
		if (trim($tag)=="") continue;
		$count = strip_tags($count);
		$xmlstring .= "\n\t<tag>\n\t\t<name>$tag</name>\n\t\t<count>$count</count>\n\t</tag>";
	}

	$xmlstring .= "\n</tags>";

	if (preg_match("/\<\?/",$xmlstring) or preg_match("/\?\>/",$xmlstring))
		fn_die("\$xmlstring cannot contains php tags! ",__FILE__,__LINE__);

	if ($section!="")
		$file = _FN_SECTIONS_DIR."/$section/tags_list.php";
	else
		$file = _FN_VAR_DIR."/news/tags_list.php";

	fnwrite($file,"<?php die();?>\n<?xml version='1.0' encoding='UTF-8'?>\n".$xmlstring,"w",array("nonull")); // ISO8859-1 to UTF-8
}

/**
 * Carica l'elenco dei tag coi rispettivi contatori
 *
 * @param string $section se settata salva l'elenco nella sezione specificata anzichè
 *               nell'elenco generale
 * @return un array contenente l'elenco dei tag e dei contatori corrispondenti
 * @author Aldo Boccacci
 */
function load_tags_list($section=""){

	if ($section!="" and !check_path($section,"","false"))
		flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	if ($section!="")
		$file = _FN_SECTIONS_DIR."/$section/tags_list.php";
	else
		$file = _FN_VAR_DIR."/news/tags_list.php";

	$data = array();

	if (file_exists($file)){
		$string = get_file($file);
		$string = preg_replace("/\<\?php die\(\);\?>\n/","",$string);
		if(preg_match("/<name>(.*?)<\/name>/i",$string,$tagout)>0) {
			$string = str_replace("<name>$tagout[1]</name>","<name>".getparam($tagout[1],PAR_NULL,SAN_HTML)."</name>",$string); // sanitize
		}
		$xmldata = new SimpleXMLElement($string);

		$n=0;
		foreach ($xmldata->tag as $tag){
			$name = (string) $tag->name;
			$count = (string) $tag->count;

			if($count>0) {	// discard old unused tags
				//ATTENZIONE: controllare per eventuali problemi con caratteri
				//speciali nelle key
				if ($name!="")
					$data[$name] = $count;
	 			//$data[$n]['tag'] = $name;
	 			//$data[$n]['count'] = $count;
				$n++;
			}
		}
	}
	else
		return array();

	return $data;
}

/**
 * Aggiunge gli elementi contenuti nell'array $tags all'elenco dei tag del portale, creando
 * o incrementando di un'unità il contatore. Viene effettuato un controllo per depurare
 * l'array da evenutali tag ripetuti
 *
 * @param string $section se settata salva l'elenco nella sezione specificata anzichè
 *               nell'elenco generale
 * @param array $tags l'array contenente i tag da aggiungere alla lista
 * @author Aldo Boccacci
 */
function add_tags_in_tags_list($tags_array,$section=""){

	if (!is_array($tags_array))
		flatnews_die("\$tags_array must be an array",__FILE__,__LINE__);

	if ($section!="" and !check_path($section,"","false"))
		flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	$arrayok = array();

	for ($n=0;$n<count($tags_array);$n++){
		$tag = trim(strip_tags($tags_array[$n]));
		if (!in_array($tag,$arrayok))
			$arrayok[]=$tag;
	}

	$tags_list = load_tags_list($section);

	for ($n=0;$n<count($arrayok);$n++){
		$tag = $arrayok[$n];
		if (isset($tags_list[$tag]))
			$tags_list[$tag] = ($tags_list[$tag]+1);
		else
			$tags_list[$tag] = 1;
	}

	save_tags_list($tags_list,$section);
}

/**
 * Rimuove gli elementi contenuti nell'array $tags_array dall'elenco dei tag del portale, rimuovendoli
 * o decrementando di un'unità il contatore. Viene effettuato un controllo per depurare
 * l'array da evenutali tag ripetuti
 *
 * @param string $section se settata salva l'elenco nella sezione specificata anzichè
 *               nell'elenco generale
 * @param array $tags_array l'array contenente i tag da rimuovere dalla lista
 * @author Aldo Boccacci
 */
function remove_section_tags_from_tags_list($tags_array,$section=""){

	if (!is_array($tags_array))
		flatnews_die("\$tags_array must be an array",__FILE__,__LINE__);

	if ($section!="" and !check_path($section,"","false"))
		flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	$arrayok = array();

	while ($element = current($tags_array)){
		$tag = key($tags_array);
		$count = current($tags_array);
		if (!isset($arrayok[$tag])){
			$arrayok[$tag]=$count;
		}
		next($tags_array);
	}

	//se $section==0 carica l'elenco dei tag generale
	$tags_list = load_tags_list($section);

	while ($element = current($arrayok)){
		$tag = key($arrayok);
		$value = current($arrayok);
		if ($tag=="")
			next($arrayok);
		if (isset($tags_list[$tag])){
			if (($tags_list[$tag]-$value)>0)
				$tags_list[$tag] = ($tags_list[$tag]-$value);
			else unset($tags_list[$tag]);
		}

		next($arrayok);
	}

	save_tags_list($tags_list,$section);
}


/**
 * Rimuove gli elementi contenuti nell'array $tags_array dall'elenco dei tag del portale, rimuovendoli
 * o decrementando il contatore del numero indicato dall'array.
 * Viene effettuato un controllo per depurare l'array da evenutali tag ripetuti
 * Struttura dell'array:
 * $tags['tag1'] = $count1
 * $tags['tag2'] = $count2
 * @param string $section se settata salva l'elenco nella sezione specificata anzichè
 *               nell'elenco generale
 * @param array $tags_array l'array contenente i tag da rimuovere dalla lista
 * @author Aldo Boccacci
 */
function remove_tags_from_tags_list($tags_array,$section=""){

	if (!is_array($tags_array))
		flatnews_die("\$tags_array must be an array",__FILE__,__LINE__);

	if ($section!="" and !check_path($section,"","false"))
		flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	$arrayok = array();

	for ($n=0;$n<count($tags_array);$n++){
		$tag = trim(strip_tags($tags_array[$n]));
		if (!in_array($tag,$arrayok))
			$arrayok[]=$tag;
	}

	$tags_list = load_tags_list($section);
// 	print_r($arrayok);die();

	for ($n=0;$n<count($arrayok);$n++){
		$tag = $arrayok[$n];

		if (isset($tags_list[$tag])){
			if ($tags_list[$tag]>0)
				$tags_list[$tag] = ($tags_list[$tag]-1);
			else
				unset($tags_list[$tag]);
		}
	}

	save_tags_list($tags_list,$section);
}

/**
 * Elenca le categorie delle news
 *
 * @return un array contenente le categorie delle news
 * @author Aldo Boccacci
 */
function list_news_categories(){
	$array = array();
	$handle = opendir('images/news');
	while ($file = readdir($handle)) {
		if (!($file == "." or $file == ".." or preg_match("/^\./",$file) or $file=="CVS")) {
// 			if (is_file($file))
				$array[]=$file;
		}
	}

	closedir($handle);
	return $array;
}

/**
 * Restituisce un array con i nomi degli utenti che sono moderatori delle news
 *
 * @return un array con i nomi utente dei moderatori delle news
 * @author Aldo Boccacci
 */
function list_news_moderators(){
// 	global $news_moderators;
	include "config.php";
	if ($news_moderators=="") return array();
	$moderators = explode(",",$news_moderators);
	$arrayok = array();
	for ($n=0;$n<count($moderators);$n++){
		$moderator = trim(strip_tags($moderators[$n]));
		if (file_exists(_FN_USERS_DIR."/$moderator.php"))
			$arrayok[] = $moderator;
	}
	return $arrayok;
}

/**
 * Funzione che restituisce la data di una news togliendo all'occorrenza
 * il prefisso top_ o hide_
 *
 * @param string $news la notizia di cui restituire la data
 * @return la data corretta per la notizia
 * @author Aldo Boccacci
 * @since Flatnuke 3.0
 *
 */
/*function get_news_date($news){ // funzione sostituita da get_news_time
	$news = basename($news);
	$news = str_replace("top_","",$news);
	$news = str_replace("hide_","",$news);
	$news = str_replace(".fn.php","",$news);
	return $news;

}*/

/**
 * Crea il feed xml per la sezione $section e lo salva all'interno della
 * sezione stessa col nome feed.xml
 *
 * @param string $section la sezione di cui generare il feed
 * @author Aldo Boccacci
 * @since 3.0
 */
function create_feed($section){
	$section = getparam($section,PAR_NULL,SAN_FLAT);
	if (!check_path($section,"","false")) flatnews_die("\$section is not valid!",__FILE__,__LINE__);

	$news_limit = 20;

	//includo la libreria
	require_once 'include/rss/rss_generator.inc.php';
	include "config.php";

	$url = "http://".getparam("HTTP_HOST", PAR_SERVER, SAN_FLAT).getparam("PHP_SELF", PAR_SERVER, SAN_FLAT);

	//parte generale
	$rss_channel = new rssGenerator_channel();
	$rss_channel->atomLinkHref = '';
	$rss_channel->title = "$sitename - ".preg_replace("/^none_/i","",basename($section));
	$rss_channel->link = "$url?mod=$section";
	$rss_channel->description = '';
	$rss_channel->language = '';
	$rss_channel->generator = 'PHP RSS Feed Generator - Flatnuke';
	$rss_channel->managingEditor = '';
	$rss_channel->webMaster = "$admin_mail ($admin)";

	$rss_feed = new rssGenerator_rss();
	$rss_feed->encoding = _CHARSET;
	$rss_feed->version = '2.0';

	//inserisco le singole notizie
	$news_list = list_news($section);

	for ($i=0;$i<count($news_list);$i++){
		if ($i>$news_limit) break;

		$news = load_news($section,$news_list[$i]);
		if (news_is_hidden($news_list[$i])) continue;
// 		print_r($news);
		$item = new rssGenerator_item();
		$item->title = tag2html($news['title']);
		$item->description = $rss_feed->cdata(tag2html($news['header']));
		$item->link = "$url?mod=$section&amp;action=viewnews&amp;news=".$news_list[$i];
		$item->guid = "$url?mod=$section&amp;action=viewnews&amp;news=".$news_list[$i];
// 		$item->author = $news['by'];
		$item->pubDate = date("D, d M Y H:i:s",get_news_time($news_list[$i]))." GMT";
		$rss_channel->items[] = $item;

	}

	fnwrite(_FN_SECTIONS_DIR."/$section/feed.xml",$rss_feed->createFeed($rss_channel),"w");

}

/**
 * Crea il feed xml generale del sito
 *
 * @param string $section la sezione di cui generare il feed
 * @author Aldo Boccacci
 * @since 3.0
 */
function create_general_feed(){
	$news_limit = 20;

	//includo la libreria
	require_once 'include/rss/rss_generator.inc.php';
	include "config.php";

	$url = "http://".getparam("HTTP_HOST", PAR_SERVER, SAN_FLAT).getparam("PHP_SELF", PAR_SERVER, SAN_FLAT);

	//parte generale
	$rss_channel = new rssGenerator_channel();
	$rss_channel->atomLinkHref = '';
	$rss_channel->title = "$sitename";
	$rss_channel->link = "$url";
	$rss_channel->description = '';
	$rss_channel->language = '';
	$rss_channel->generator = 'PHP RSS Feed Generator - Flatnuke';
	$rss_channel->managingEditor = '';
	$rss_channel->webMaster = "$admin_mail ($admin)";

	$rss_feed = new rssGenerator_rss();
	$rss_feed->encoding = _CHARSET;
	$rss_feed->version = '2.0';

	//inserisco le singole notizie
	$news_sections_list = list_news_sections();

	$news_list_total = array();

	for ($n=0; $n<count($news_sections_list);$n++){
		$section = $news_sections_list[$n];

		$news_list = list_news($section);

		for ($i=0;$i<count($news_list);$i++){
			$index = $news_list[$i];
			$news_list_total[$index] = $section;
		}

	}//fine elenco sezioni
	krsort($news_list_total);
// 	print_r($news_list_total); die();
	for ($i=0;$i<count($news_list_total);$i++){

		if ($i>$news_limit) break;

		$section = current($news_list_total);
		$news_name =key($news_list_total);
		next($news_list_total);

		$news = load_news($section,$news_name);
		if (news_is_hidden($news_name)) continue;
// 		print_r($news);
		$item = new rssGenerator_item();
		$item->title = tag2html($news['title']);
		$item->description = $rss_feed->cdata(tag2html($news['header']));
		$item->link = "$url?mod=$section&amp;action=viewnews&amp;news=".$news_name;
		$item->guid = "$url?mod=$section&amp;action=viewnews&amp;news=".$news_name;
// 		$item->author = $news['by'];
		$item->pubDate = date("D, d M Y H:i:s",get_news_time($news_name))." GMT";
		$rss_channel->items[] = $item;

	}

	fnwrite(_FN_VAR_DIR."/backend.xml",$rss_feed->createFeed($rss_channel),"w");
}

/**
 * Ripulisce la stringa da caratteri speciali per poterla usare come permalink
 *
 * @param string $string il titolo della news da pulire
 * @return la stringa pulita da usare come permalink
 * @author dan
 * @since 4.0.0
 */
function CleanString($string)
{
    $strResult = str_ireplace("à", "a", $string);
    $strResult  = str_ireplace("á", "a", $strResult);
    $strResult =  str_ireplace("è", "e", $strResult);
    $strResult =  str_ireplace("é", "e", $strResult);
    $strResult =  str_ireplace("ì", "i", $strResult);
    $strResult =  str_ireplace("í", "i", $strResult);
    $strResult =  str_ireplace("ò", "o", $strResult);
    $strResult =  str_ireplace("ó", "o", $strResult);
    $strResult =  str_ireplace("ù", "u", $strResult);
    $strResult =  str_ireplace("ú", "u", $strResult);
    $strResult =  str_ireplace("ç", "c", $strResult);
    $strResult =  str_ireplace("ö", "o", $strResult);
    $strResult =  str_ireplace("û", "u", $strResult);
    $strResult =  str_ireplace("ê", "e", $strResult);
    $strResult =  str_ireplace("ü", "u", $strResult);
    $strResult =  str_ireplace("ë", "e", $strResult);
    $strResult =  str_ireplace("ä", "a", $strResult);
    $strResult =  str_ireplace("'", "-", $strResult);

    $strResult = preg_replace('/[^A-Za-z0-9 ]/', "", $strResult);
    $strResult = trim($strResult);
    $strResult =  preg_replace('/[ ]{2,}/', "-", $strResult);

    $strResult = str_replace(" ", "-", $strResult);

    return strtolower($strResult);
}

?>
