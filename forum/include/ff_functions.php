<?php

if (preg_match("/ff_functions.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call ff_functions.php!",__FILE,__LINE);
}

/**
 * Flatforum: un forum integrato nella struttura di Flatnuke
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


include_once("download/include/fdfunctions.php");

/**
 * Restituisce la root del forum
 *
 * Questa funzione restituisce il percorso della cartella principale del forum attivo.
 * (così diventa semplice definire dove deve trovarsi la root del forum)
 *
 * @return string il path della root del forum.
 * @since 0.1
 * @author Aldo Boccacci
 */
function get_forum_root(){
	if (!is_dir(_FN_VAR_DIR."/flatforum")){
		if (is_writable(_FN_VAR_DIR)){
			if (fn_mkdir(_FN_VAR_DIR."/flatforum/",0777)){
				echo "<b>/"._FN_VAR_DIR."/flatforum/</b> "._FDCREATESECTOK;

				fnwrite(_FN_VAR_DIR."/flatforum/ffmotd.php"," ","w",array());

			}
			else echo _FDCREATEDIRERROR." <b>/"._FN_VAR_DIR."/flatforum</b> "._FDCHECKPERM;
		}
		else echo _FDDIR." <b>/"._FN_VAR_DIR."</b> "._FDNOTWRITE." "._FDCHECKPERM;
	}
	return _FN_VAR_DIR."/flatforum/";
// 	$mod = getparam("mod",PAR_GET,SAN_FLAT);
// 		if (!check_path($mod,"","false")) ff_die("\$mod is not valid! (".strip_tags($mod).")",__FILE__,__LINE__);

// 	return "sections/$mod";
}

/**
 * Elenca i gruppi di categorie del forum a partire da $root
 *
 * Elenca i gruppi di categorie del forum indicato da $root.
 *
 * @param string $root la cartella dalla quale elencare le categorie
 * @since 0.1
 * @author Aldo Boccacci
 */
function list_forum_groups($root){
	$root=getparam($root,PAR_NULL,SAN_FLAT);
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	$groups = array();
	$group ="";
	$tempgroups=array();
	$tempgroups=glob("$root/*",GLOB_ONLYDIR);
	if (!$tempgroups) return array(); // glob may returns boolean false instead of an empty array on some systems
	if (count($tempgroups)>0 and is_array($tempgroups)){
		foreach ($tempgroups as $group){
			if (is_dir($group) and !preg_match("/^none_/i",$group)) $groups[] = basename($group);
		}
	}
	return $groups;
}


/**
 * Elenca i gruppi di categorie del forum a partire da $root
 *
 * Elenca i gruppi di categorie del forum indicato da $root.
 *
 * @param string $root la cartella dalla quale elencare le categorie
 * @since 0.1
 * @author Aldo Boccacci
 */
function list_group_arguments($root,$group){
	$root=getparam($root,PAR_NULL,SAN_FLAT);
	$group=getparam($group,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	$arguments = array();
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);
	$arguments = array();
	if (!check_path("$root/$group",get_forum_root(),"false")) ff_die("forum root+group is not valid!",__FILE__,__LINE__);
	$arguments = array();
	$argument="";
	$temparguments=array();
	$temparguments= glob("$root/$group/*",GLOB_ONLYDIR);
	if (!$temparguments) return array(); // glob may returns boolean false instead of an empty array on some systems
	if (count($temparguments>0) and is_array($temparguments)){
		foreach ($temparguments as $argument){
			if (is_dir($argument) and !preg_match("/^none_/i",$argument)) $arguments[] = basename($argument);
		}
	}
	return $arguments;
}


/**
 * Restituisce l'elenco dei topics della categoria indicata
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di cui restituire i topics
 * @param boolean $first_on_top se settato a "true" antepone nell'elenco i topic evidenziati
 * @author Aldo Boccacci
 * @since 0.1
 */
function list_argument_topics($root,$group,$argument,$first_on_top="false",$show_hidden="true"){
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);
	if (!check_path($argument,"","false")) ff_die("forum argument is not valid!",__FILE__,__LINE__);
	if (!check_path("$root/$group/$argument",get_forum_root(),"false")) ff_die("forum root+argument is not valid!",__FILE__,__LINE__);
	if (!check_var($first_on_top,"boolean")) ff_die("\$first_on_top must be a boolean! ",__FILE__,__LINE__);
	if (!check_var($show_hidden,"boolean")) ff_die("\$show_hidden must be a boolean! ",__FILE__,__LINE__);

	if (!is_dir("$root/$group/$argument")) return array();
	$sortedtopics = array();
	if ($first_on_top=="true"){
		$important_topics = array();
		$important_topics = glob("$root/$group/$argument/*top_*.ff.php");
		if (!$important_topics) $important_topics = array(); // glob may returns boolean false instead of an empty array on some systems
		$isortedtopics=array();
		$itopic="";
		if (count($important_topics)>0 and is_array($important_topics)){
			foreach ($important_topics as $itopic){
				$time = "";
				$topicdata = array();
// 				$topicdata = load_topic_properties($itopic);
				$time = latest_post_time($itopic);
				if ($show_hidden=="false" and preg_match("/hide_/",basename($itopic)))
				continue;
				$isortedtopics[$time] = $itopic;
			}
		}
		if (count($isortedtopics)!=0)
			krsort($isortedtopics);

		$ntopics = array();
		$ntopics = glob("$root/$group/$argument/*.ff.php");
		if (!$ntopics) $ntopics = array(); // glob may returns boolean false instead of an empty array on some systems
		$ntopic = "";
		$nsortedtopics = array();
		if (count($ntopics)>0 and is_array($ntopics)){
			foreach ($ntopics as $ntopic){
				if (preg_match("/top_/",basename($ntopic))) continue;

				//verifico se è nascosto
				$topicdata = array();
// 				$topicdata = load_topic_properties($ntopic);

				$time = latest_post_time($ntopic);

				if ($show_hidden=="false" and preg_match("/hide_/",basename($ntopic)))
				continue;
				$nsortedtopics[$time] = $ntopic;
			}
		}
		if (count($nsortedtopics)!=0)
			krsort($nsortedtopics);

		if (count($isortedtopics)!=0){
			foreach ($isortedtopics as $topic){
				$sortedtopics[] = $topic;
			}
		}
		if (count($nsortedtopics)!=0){
			foreach ($nsortedtopics as $topic){
				$sortedtopics[] = $topic;
			}
		}

	}
	else {
		$topics = array();

// 		$topics = glob("$root/$group/$argument/*.ff.php");

		$fdirectory = opendir( "$root/$group/$argument/" );
		$tmpfile="";

		while ($tmpfile = readdir($fdirectory)) {
			if (preg_match("/\.ff\.php$/",$tmpfile)) {
// 				echo "$tmpfile<br>";
// 				$topics[]="$root/$group/$argument/".$tmpfile;
				array_push($topics, "$root/$group/$argument/".$tmpfile);
			}
		}
// 		die();
// 		return $topics;
		$topic = "";
		$sortedtopics = array();
		for ($count=0;$count<count($topics);$count++){
			$topic = $topics[$count];
			//controllo se è nascosto
			$topicdata = array();
// 			$topicdata = load_topic_properties($topic);
			if ($show_hidden=="false" and preg_match("/hide_/",basename($topic)))
				continue;

			$time = latest_post_time($topic);
// echo $time."<br>";
			$sortedtopics[$time] = $topic;
		}
		krsort($sortedtopics);

	}
	return $sortedtopics;

}

/**
 * Restituisce l'elenco dei topics della categoria indicata senza ordinarli
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di cui restituire i topics
 * @param boolean $first_on_top se settato a "true" antepone nell'elenco i topic evidenziati
 * @author Aldo Boccacci
 * @since 0.1
 */
function fast_list_argument_topics($root,$group,$argument){
if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);
	if (!check_path($argument,"","false")) ff_die("forum argument is not valid!",__FILE__,__LINE__);
	if (!check_path("$root/$group/$argument",get_forum_root(),"false")) ff_die("forum root+argument is not valid!",__FILE__,__LINE__);

	if (!is_dir("$root/$group/$argument")) return array();

	$topics = array();
	$topics = glob("$root/$group/$argument/*.ff.php");
	if (!$topics) return array(); // glob may returns boolean false instead of an empty array on some systems
	return $topics;
}

/**
 * Carica in un array multidimensionale il file xml con i dati del topic
 *
 * Struttura dell'array restituito:
 * $data['properties']['topictitle'] = il titolo del topic
 * $data['properties']['icon'] = l'icona associata al topic
 * $data['properties']['locked'] = indica se il topic è bloccato
 * $data['properties']['hide'] = indica se il topic è nascosto
 * $data['properties']['hits'] = indica il numero di letture del topic
 * $data['properties']['important'] = indica se il topic deve essere messo in rilievo
 * $data['properties']['postontop'] = indica se un post deve essere sempre visualizzato per primo
 * $data['properties']['level'] = indica il livello del topic
 * $data['properties']['postscount'] = il numero di posts presenti
 * $data['properties']['emailalert'] = array con i nomi degli utenti da avvisare via mail
 *
 * $data['posts'][]['icon'] = l'icona associata al post
 * $data['posts'][]['uploader'] = l'utente che ha postato il commento
 * $data['posts'][]['postsbj'] = il titolo del post
 * $data['posts'][]['postbody'] = il corpo del post
 * $data['posts'][]['time'] = l'ora di invio del post
 * $data['posts'][]['lasteditposter'] = l'utente che ha modificato il post per ultimo
 * $data['posts'][]['lastedit'] = l'ora di invio dell'ultima modifica del post
 *
 * @param string $topicfile Il file di cui caricare le impostazioni
 * @return un array con i dati del topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_topic($topicfile){
	//x windows...
	if ($topicfile=="NULL") return;
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("Topic file path is not valid! (".strip_tags($topicfile).") ".basename(__FILE__).": ".__LINE__);

	if (!is_file($topicfile)) return NULL;

	$data = array();
	$string = "";
	$string = get_file($topicfile);

	global $theme;
	//CARICO LE PROPRIETÀ DEL TOPIC
	$properties = array();
	$properties = ff_get_xml_element("properties",$string);

	$topictitle = "";
	$topictitle = ff_get_xml_element("topictitle",$properties);
	if (check_var($topictitle,"text"))
		$data['properties']['topictitle'] = $topictitle;
	else $data['properties']['topictitle'] = "";

	$topic_icon = "";
	$topic_icon = ff_get_xml_element("icon",$properties);
	if (check_path($topic_icon,"","false") and trim($topic_icon)=="")
		$data['properties']['icon'] = $topic_icon;
	else $data['properties']['icon'] = "themes/$theme/images/section.png";

	$topiclocked = "";
	$topiclocked =ff_get_xml_element("locked",$properties);
	if (check_var($topiclocked,"boolean"))
		$data['properties']['locked'] = $topiclocked;
	else $data['properties']['locked'] = "false";

	$topichide = "";
	$topichide = ff_get_xml_element("hide",$properties);
	if (check_var($topichide,"boolean"))
		$data['properties']['hide'] = $topichide;
	else $data['properties']['hide'] = "false";

	$topichits = "";
	$topichits = ff_get_xml_element("hits",$properties);
	if (check_var($topichits,"digit"))
		$data['properties']['hits'] = $topichits;
	else $data['properties']['hits'] = "0";

	$topic_important = "";
	$topic_important = ff_get_xml_element("important",$properties);
	if (check_var($topic_important,"boolean"))
		$data['properties']['important'] = $topic_important;
	else $data['properties']['important'] = "false";

	$topicpostontop = "";
	$topicpostontop = ff_get_xml_element("postontop",$properties);
	if (check_var($topicpostontop,"digit"))
		$data['properties']['postontop'] = $topicpostontop;
	else $data['properties']['postontop'] = "";

	$topic_level = "";
	$topic_level = ff_get_xml_element("level",$properties);
	if (check_var($topic_level,"digit"))
		$data['properties']['level'] = $topic_level;
	else $data['properties']['level'] = "0";


	//utenti da avvertire via mail
	$userstoalert = get_xml_array("user",ff_get_xml_element("emailalert",$properties));
	$user="";
	$data['properties']['emailalert'] = array();
	if (count($userstoalert)>0){
		foreach ($userstoalert as $user){
			if (is_alphanumeric($user)) $data['properties']['emailalert'][]= $user;
		}
	}

	//CARICO I POST
	$posts = array();
	$posts = get_xml_array("post",ff_get_xml_element("posts",$string));

	$post = "";
	$count=0;
	foreach ($posts as $post){
		$countpost = $count++;

		//icona associata al post
		$posticon = "";
		$posticon = ff_get_xml_element("posticon",$post);
		if (check_path($posticon,"","false") and trim($posticon)!="")
			$data['posts'][$countpost]['posticon'] = $posticon;
		else $data['posts'][$countpost]['posticon'] = "themes/$theme/images/section.png";

		//l'utente che ha lasciato il post
		$poster = "";
		$poster = ff_get_xml_element("poster",$post);
		if (is_alphanumeric($poster))
			$data['posts'][$countpost]['poster'] = $poster;
		else $data['posts'][$countpost]['poster'] = "";

		//Subject del post
		$postsubj = "";
		$postsubj = ff_get_xml_element("postsubj",$post);
		if (check_var($postsubj,"text"))
			$data['posts'][$countpost]['postsubj'] = $postsubj;
		else $data['posts'][$countpost]['postsubj'] = "";

		//body del post
		$postbody = "";
		$postbody = ff_get_xml_element("postbody",$post);
		if (check_var($postbody,"text"))
			$data['posts'][$countpost]['postbody'] = $postbody;
		else $data['posts'][$countpost]['postbody'] = "";

		//data di caricamento del post
		$post_time = "";
		$post_time = ff_get_xml_element("time",$post);
		if (check_var($post_time,"digit"))
			$data['posts'][$countpost]['time'] = $post_time;
		else $data['posts'][$countpost]['time'] = "0";

		//l'utente che ha modificato per ultimo il post
		$lasteditposter = "";
		$lasteditposter = ff_get_xml_element("lasteditposter",$post);
		if (is_alphanumeric($lasteditposter))
			$data['posts'][$countpost]['lasteditposter'] = $lasteditposter;
		else $data['posts'][$countpost]['lasteditposter'] = "";

		//data di caricamento dell'ultima modifica del post
		$lastedit_time = "";
		$lastedit_time = ff_get_xml_element("lastedit",$post);
		if (check_var($lastedit_time,"digit"))
			$data['posts'][$countpost]['lastedit'] = $lastedit_time;
		else $data['posts'][$countpost]['lastedit'] = "0";
	}

		//conteggio dei posts
	if (preg_match("/\<postscount\>.*\<\/postscount\>/i",$properties)){
		$data['properties']['postscount'] = ff_get_xml_element("postscount",$properties);
	}
	else $data['properties']['postscount'] = count($data['posts']);

	return $data;
}

/**
 * Carica in un array multidimensionale il file xml con i dati del topic
 * (SOLTANTO PER LA VISUALIZZAZIONE DEI GRUPPI!!!!)
 *
 * @param string $topicfile Il file di cui caricare le impostazioni
 * @return un array con i dati del topic (VERSIONE RIDOTTA!)
 * @author Aldo Boccacci
 * @since 0.1
 */
function fast_load_topic($topicfile){
	//x windows...
	if ($topicfile=="NULL") return;
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("Topic file path is not valid! (".strip_tags($topicfile).") ".basename(__FILE__).": ".__LINE__);

	if (!is_file($topicfile)) return NULL;

	$data = array();
	$string = "";
	$string = get_file($topicfile);

	global $theme;
	//CARICO LE PROPRIETÀ DEL TOPIC
	$properties = array();
	$properties = ff_get_xml_element("properties",$string);

	$topictitle = "";
	$topictitle = ff_get_xml_element("topictitle",$properties);
	if (check_var($topictitle,"text"))
		$data['properties']['topictitle'] = $topictitle;
	else $data['properties']['topictitle'] = "";

	$topic_icon = "";
	$topic_icon = ff_get_xml_element("icon",$properties);
	if (check_path($topic_icon,"","false") and trim($topic_icon)=="")
		$data['properties']['icon'] = $topic_icon;
	else $data['properties']['icon'] = "themes/$theme/images/section.png";

	$topiclocked = "";
	$topiclocked =ff_get_xml_element("locked",$properties);
	if (check_var($topiclocked,"boolean"))
		$data['properties']['locked'] = $topiclocked;
	else $data['properties']['locked'] = "false";

	$topichits = "";
	$topichits = ff_get_xml_element("hits",$properties);
	if (check_var($topichits,"digit"))
		$data['properties']['hits'] = $topichits;
	else $data['properties']['hits'] = "0";

	$topic_important = "";
	$topic_important = ff_get_xml_element("important",$properties);
	if (check_var($topic_important,"boolean"))
		$data['properties']['important'] = $topic_important;
	else $data['properties']['important'] = "false";

	$topic_level = "";
	$topic_level = ff_get_xml_element("level",$properties);
	if (check_var($topic_level,"digit"))
		$data['properties']['level'] = $topic_level;
	else $data['properties']['level'] = "0";


	//CARICO I POST
	$posts = array();
	$posts = get_xml_array("post",ff_get_xml_element("posts",$string));

	$post = "";
	$count=0;
	foreach ($posts as $post){

		$countpost = $count++;
		//solo il primo e l'ultimo
		if (!($count==1 or $count==count($posts))){
			$data['posts'][$countpost]="";
			continue;
		}

		//l'utente che ha lasciato il post
		$poster = "";
		$poster = ff_get_xml_element("poster",$post);
		if (is_alphanumeric($poster))
			$data['posts'][$countpost]['poster'] = $poster;
		else $data['posts'][$countpost]['poster'] = "";

		//data di caricamento del post
		$post_time = "";
		$post_time = ff_get_xml_element("time",$post);
		if (check_var($post_time,"digit"))
			$data['posts'][$countpost]['time'] = $post_time;
		else $data['posts'][$countpost]['time'] = "0";

	}

		//conteggio dei posts
	if (preg_match("/\<postscount\>.*\<\/postscount\>/i",$properties)){
		$data['properties']['postscount'] = ff_get_xml_element("postscount",$properties);
	}
	else $data['properties']['postscount'] = count($data['posts']);

	return $data;
}

/**
 * Carica in un array multidimensionale il file xml con le proprietà del topic
 *
 * Struttura dell'array restituito:
 * $data['properties']['topictitle'] = il titolo del topic
 * $data['properties']['icon'] = l'icona associata al topic
 * $data['properties']['locked'] = indica se il topic è bloccato
 * $data['properties']['hide'] = indica se il topic è nascosto
 * $data['properties']['hits'] = indica il numero di letture del topic
 * $data['properties']['important'] = indica se il topic deve essere messo in rilievo
 * $data['properties']['postontop'] = indica se un post deve essere sempre visualizzato per primo
 * $data['properties']['level'] = indica il livello del topic
 * $data['properties']['emailalert'] = array con i nomi degli utenti da avvisare via mail
 *
 * @param string $topicfile Il file di cui caricare le impostazioni
 * @return un array con i dati del topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_topic_properties($topicfile){
	//x windows...
	if ($topicfile=="NULL") return;

	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("Topic file path is not valid! ".basename(__FILE__).": ".__LINE__);

	if (!is_file($topicfile)) return NULL;

	$data = array();
	$string = "";
	$string = get_file($topicfile);

	global $theme;
	//CARICO LE PROPRIETÀ DEL TOPIC
	$properties = array();
	$properties = ff_get_xml_element("properties",$string);

	$topictitle = "";
	$topictitle = ff_get_xml_element("topictitle",$properties);
	if (check_var($topictitle,"text"))
		$data['properties']['topictitle'] = $topictitle;
	else $data['properties']['topictitle'] = "";

	$topic_icon = "";
	$topic_icon = ff_get_xml_element("icon",$properties);
	if (check_path($topic_icon,"","false") and trim($topic_icon)=="")
		$data['properties']['icon'] = $topic_icon;
	else $data['properties']['icon'] = "themes/$theme/images/section.png";

	$topiclocked = "";
	$topiclocked =ff_get_xml_element("locked",$properties);
	if (check_var($topiclocked,"boolean"))
		$data['properties']['locked'] = $topiclocked;
	else $data['properties']['locked'] = "false";

	$topichide = "";
	$topichide = ff_get_xml_element("hide",$properties);
	if (check_var($topichide,"boolean"))
		$data['properties']['hide'] = $topichide;
	else $data['properties']['hide'] = "false";

	$topichits = "";
	$topichits = ff_get_xml_element("hits",$properties);
	if (check_var($topichits,"digit"))
		$data['properties']['hits'] = $topichits;
	else $data['properties']['hits'] = "0";

	$topic_important = "";
	$topic_important = ff_get_xml_element("important",$properties);
	if (check_var($topic_important,"boolean"))
		$data['properties']['important'] = $topic_important;
	else $data['properties']['important'] = "false";

	$topicpostontop = "";
	$topicpostontop = ff_get_xml_element("postontop",$properties);
	if (check_var($topicpostontop,"digit"))
		$data['properties']['postontop'] = $topicpostontop;
	else $data['properties']['postontop'] = "";

	$topic_level = "";
	$topic_level = ff_get_xml_element("level",$properties);
	if (check_var($topic_level,"digit"))
		$data['properties']['level'] = $topic_level;
	else $data['properties']['level'] = "0";

	//utenti da avvertire via mail
	$userstoalert = get_xml_array("user",ff_get_xml_element("emailalert",$properties));
	$user="";
	$data['properties']['emailalert'] = array();
	if (count($userstoalert)>0){
		foreach ($userstoalert as $user){
			if (is_alphanumeric($user)) $data['properties']['emailalert'][]= $user;
		}
	}

	//conteggio dei posts
	if (preg_match("/\<postscount\>.*\<\/postscount\>/i",$properties)){
		$data['properties']['postscount'] = ff_get_xml_element("postscount",$properties);
	}
	else $data['properties']['postscount'] = 0;

	//CARICO I POST
// 	$posts = array();
// 	$posts = get_xml_array("post",ff_get_xml_element("posts",$string));
//
// 	$post = "";
// 	$totposts = count($posts);
// 	$count=0;
// 	foreach ($posts as $post){
// 		$countpost = $count++;
//
// 		if ($countpost==$totposts){
// 			$data['posts'][$countpost]="";
//
// 		}
// 		else {
// 		//data di caricamento dell'ultimo post
// 			$post_time = "";
// 			$post_time = ff_get_xml_element("time",$post);
// 			if (check_var($post_time,"digit"))
// 				$data['posts'][$countpost]['time'] = $post_time;
// 			else $data['posts'][$countpost]['time'] = "0";
// 		}
// 	}

	return $data;
}

/**
 * Salva in un file xml tutti i dati relativi al topic.
 *
 * Questa funzione salva nel file $topicfile l'array di dati $data sotto
 * forma di xml.
 *
 * @param string $topicfile il file in cui salvare i dati
 * @param array $data la struttura dei dati da salvare
 * @author Aldo Boccacci
 * @since 0.1
 */
function save_topic($topicfile,$data){
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("\$topicfile is not valid! ".basename(__FILE__).": ".__LINE__);

	if (!is_array($data)) ff_die("\$data must be an array()! ".__FILE__.": ".__LINE__);

	//controllo properties
	if (isset($data['properties']['topictitle'])){
		$topictitle = fn_stripslashes(strip_tags($data['properties']['topictitle']));
	}
	else $topictitle = "";

	if (isset($data['properties']['icon'])){
		$icon = strip_tags($data['properties']['icon']);
		if (!check_path($icon,"","false")) $icon="";
	}
	else $icon = "";

	if (isset($data['properties']['locked'])){
		$locked = strip_tags($data['properties']['locked']);
		if (!check_var($locked,"boolean")) $locked="false";
	}
	else $locked = "false";

	if (isset($data['properties']['hide'])){
		$hide = strip_tags($data['properties']['hide']);
		if (!check_var($hide,"boolean")) $hide="false";
	}
	else $hide = "false";

	if (isset($data['properties']['hits'])){
		$hits = strip_tags($data['properties']['hits']);
		if (!check_var($hits,"digit")) $hits="0";
	}
	else $hits = "0";

	if (isset($data['properties']['important'])){
		$important = strip_tags($data['properties']['important']);
		if (!check_var($important,"boolean")) $important="false";
	}
	else $important = "false";

	if (isset($data['properties']['postontop'])){
		$postontop = strip_tags($data['properties']['postontop']);
		if (!check_var($postontop,"digit")) $postontop="";
	}
	else $postontop = "";

	if (isset($data['properties']['level'])){
		$level = strip_tags($data['properties']['level']);
		if (!check_var($level,"digit") and $level<=10 and $level>=0) $level="0";
	}
	else $level = "0";

	if (isset($data['properties']['postscount'])){
		$postscount = strip_tags($data['properties']['postscount']);
		if (!check_var($postscount,"digit")) $postscount = count($data['posts']);
	}
	else $postscount = count($data['posts']);

	$user ="";
	$useralert = array();
	if (isset($data['properties']['emailalert'])){
		if (is_array($data['properties']['emailalert']) and count($data['properties']['emailalert'])>0){
			foreach($data['properties']['emailalert'] as $user){
				if (is_alphanumeric(trim($user)))
					$useralert[] = trim($user);
			}
		}
		else $data['properties']['emailalert']=array();
	}
	else $data['properties']['emailalert'] = array();

	$datastring = "";
	$datastring = "<forum>
	<properties>
		<topictitle>$topictitle</topictitle>
		<icon>$icon</icon>
		<locked>$locked</locked>
		<hide>$hide</hide>
		<hits>$hits</hits>
		<important>$important</important>
		<postontop>$postontop</postontop>
		<level>$level</level>
		<emailalert>";

	if (count($useralert)>0){
		foreach ($useralert as $user){
			$datastring .= "\n\t\t\t<user>$user</user>";
		}
	}

	$datastring .="\n\t\t</emailalert>\n\t</properties>";
	$datastring .= "\n\t<posts>";

	//POSTS
	$post = array();
	foreach ($data['posts'] as $post){
		//CONTROLLO I DATI
		if (isset($post['posticon']) and check_path($post['posticon'],"","false")) $posticon = $post['posticon'];
		else $posticon="";

		if (isset($post['poster']) and is_alphanumeric(strip_tags($post['poster']))) $poster = strip_tags($post['poster']);
		else $poster="";

		if (isset($post['postsubj']) and check_var(strip_tags($post['postsubj']),"text")) $postsubj = fn_stripslashes(strip_tags($post['postsubj']));
		else $postsubj="";

		//per permettere la visualizzazione di codice html
		if (isset($post['postbody'])){

			$body = str_replace("<", "&lt;", $post['postbody']);
			$body = str_replace(">", "&gt;", $body);
			$body = strip_tags($body);
			if (check_var($body,"text")) $postbody = fn_stripslashes($body);
		}
		else $postbody="";

		if (isset($post['time']) and check_var(strip_tags(trim($post['time'])),"digit")) $time = strip_tags(trim($post['time']));
		else $time="0";

		if (isset($post['lasteditposter']) and is_alphanumeric(strip_tags($post['lasteditposter']))) $lasteditposter = strip_tags($post['lasteditposter']);
		else $lasteditposter="";

		if (isset($post['lastedit']) and check_var(strip_tags(trim($post['lastedit'])),"digit")) $lastedit = strip_tags(trim($post['lastedit']));
		else $lastedit="0";

		$datastring .= "
		<post>
			<posticon>$posticon</posticon>
			<poster>$poster</poster>
			<postsubj>$postsubj</postsubj>
			<postbody>$postbody</postbody>
			<time>$time</time>
			<lasteditposter>$lasteditposter</lasteditposter>
			<lastedit>$lastedit</lastedit>
		</post>";
	}

	//controllo se l'argomento è bloccato
	$dir ="";
	$dir = dirname($topicfile);
	if (file_exists("$dir/lock") and !_FN_IS_ADMIN and !is_forum_moderator()){
		echo "L'argomento e' bloccato";
		return;
	}
	//devo essere nella cartella di un argomento (deve esistere il file argument.php)
	if (!file_exists("$dir/argument.php")){
		echo "Non mi trovo all'interno di un argomento";
		return;
	}

	$datastring .= "\n\t</posts>\n</forum>";
	if (preg_match("/\<\?/",$datastring) or preg_match("/\?\>/",$datastring)) ff_die("\$datastring cannot contains php tags! ".__FILE__.": ".__LINE__);
	fnwrite($topicfile,"<?xml version='1.0' encoding='UTF-8'?>\n".$datastring,"w",array("nonull"));
}


/**
 * Funzione di die() personalizzata per flatforum che prima di uccidere il processo
 * salva un messaggio nel log
 * @param string $message il messaggio da stampare a schermo e da salvare nel log
 * @author Aldo Boccacci
 * @since 0.1
 */
function ff_die($message="",$file="",$line=""){
	if ($file!="" and check_path($file,"","true")) $file=strip_tags(basename(trim($file)));
	else $file="";
	if (check_var(trim($line),"digit")) $line=strip_tags(trim($line));
	else $line="";

	if ($file!="" and $line!="")
		$message = "$message $file: $line";
// 	fnlog("FlatForum",$message);
	fflogf($message,"ERROR");
	die($message);
}

/**
 * Carica le proprietà dell'argomento specificato
 *
 * $data['icon'] = l'icona associata all'argomento
 * $data['description'] = la descrizione dell'argomento
 * $data['level'] = il livello dell'argomento
 * $data['moderators'] = i moderatori dell'argomento
 *
 * @param string $root la root del forum
 * @param string $argument il nome dell'argomento
 * @return un array con le proprietà
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_argument_props_old($root,$group,$argument){
// 	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
// 	if (!check_path($argument,"","false")) ff_die("argument is not valid! ", __FILE__, __LINE__);
// 	if (!check_path($group,"","false")) ff_die("group is not valid! ", __FILE__, __LINE__);
	if (!check_path("$root/$group/$argument",get_forum_root(),"false")) ff_die("argument path is not valid! ", __FILE__, __LINE__);

	global $theme;

	$data = array();
	if (file_exists("$root/$group/$argument/argument.php")){
		$string = "";
		$string = get_file("$root/$group/$argument/argument.php");
		$string = ff_get_xml_element("argument",$string);

		if (checK_path(ff_get_xml_element("icon",$string),"","false") and ff_get_xml_element("icon",$string)!="")
			$data['icon'] = ff_get_xml_element("icon",$string);
		else $data['icon'] = "themes/$theme/images/section.png";

		if (checK_var(ff_get_xml_element("description",$string),"text"))
			$data['description'] = ff_get_xml_element("description",$string);
		else $data['description'] = "";

		if (checK_var(ff_get_xml_element("level",$string),"digit") or trim(ff_get_xml_element("level",$string))=="-1")
			$data['level'] = ff_get_xml_element("level",$string);
		else $data['level'] = "-1";
		if ($data['level']!="-1" and ($data['level']<0 or $data['level']>10))
			$data['level'] = "-1";


		//moderatori
		$string = ff_get_xml_element("moderators",$string);
		$moderators = array();
		$moderator="";
		$moderators = get_xml_array("user",$string);
		$data['moderators'] = array();
		if (count($moderators)>0){
			foreach ($moderators as $moderator){
				if (is_alphanumeric($moderator))
					$data['moderators'][] = $moderator;
			}
		}
	}
	else {
		$data['icon'] ="themes/$theme/images/section.png";
		$data['description'] = "";
		$data['moderators'] = array();
		$data['level'] = "-1";
	}

	return $data;
}

/**
 * Carica le proprietà dell'argomento specificato
 *
 * $data['icon'] = l'icona associata all'argomento
 * $data['description'] = la descrizione dell'argomento
 * $data['level'] = il livello dell'argomento
 * $data['moderators'] = i moderatori dell'argomento
 *
 * @param string $root la root del forum
 * @param string $argument il nome dell'argomento
 * @return un array con le proprietà
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_argument_props($root,$group,$argument){
// 	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
// 	if (!check_path($argument,"","false")) ff_die("argument is not valid! ", __FILE__, __LINE__);
// 	if (!check_path($group,"","false")) ff_die("group is not valid! ", __FILE__, __LINE__);
	if (!check_path("$root/$group/$argument",get_forum_root(),"false")) ff_die("argument path is not valid! ", __FILE__, __LINE__);

	global $theme;

	$data = array();
	if (file_exists("$root/$group/$argument/argument.php")){
		if (function_exists("simplexml_load_file"))
		$xml = @simplexml_load_file("$root/$group/$argument/argument.php");
		else $xml=FALSE;
		if (!$xml){
	// 		fdlogf("SIMPLEXML: I was not able to load the file ".
	// 			strip_tags(basename($file.".description"))." using simplexml_load_file.","ERROR");
			return load_argument_props_old($root,$group,$argument);
		}

		if (check_path(strip_tags($xml->icon),"","false") and strip_tags($xml->icon)!="")
			$data['icon'] = strip_tags($xml->icon);
		else $data['icon'] = "themes/$theme/images/section.png";

		if (check_var(strip_tags($xml->description),"text"))
			$data['description'] = strip_tags($xml->description);
		else $data['description'] = "";

		if (check_var(strip_tags($xml->level),"digit") or trim(strip_tags($xml->level))=="-1")
			$data['level'] = strip_tags($xml->level);
		else $data['level'] = "-1";
		if ($data['level']!="-1" and ($data['level']<0 or $data['level']>10))
			$data['level'] = "-1";


		//moderatori
		$moderators_data = $xml->moderators;

		$moderator="";
		$data['moderators'] = array();
		if (count($moderators_data)>0){
			foreach ($moderators_data as $moderator){
				if (is_alphanumeric($moderator))
					$data['moderators'][] = $moderator;
			}
		}
	}
	else {
		$data['icon'] ="themes/$theme/images/section.png";
		$data['description'] = "";
		$data['moderators'] = array();
		$data['level'] = "-1";
	}

	return $data;
}

/**
 * Salva le proprietà dell'argomento
 *
 * Salva le proprietà dell'argomento
 *
 * @param string $root la root del forum
 * @param string $argument il nome dell'argomento
 * @author Aldo Boccacci
 * @since 0.1
 */
function save_argument_props($root,$group,$argument,$data){
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	if (!check_path($group,"","false")) ff_die("forum group is not valid!",__FILE__,__LINE__);
	if (!check_path($argument,"","false")) ff_die("argument is not valid! ", __FILE__, __LINE__);
	if (!check_path("$root/$argument",get_forum_root(),"false")) ff_die("argument path is not valid! ", __FILE__, __LINE__);

	if (!is_array($data)) ff_die("\$data must be an array()!",__FILE__,__LINE__);

	if (isset($data['icon'])){
		if (check_path(trim($data['icon']),"","false"))
			$icon = trim(strip_tags($data['icon']));
		else $icon="";
	}
	else $icon = "";

	if (isset($data['description'])){
		if (check_var(trim($data['description']),"text"))
			$description = fn_stripslashes(trim(strip_tags($data['description'])));
		else $description="";
	}
	else $description = "";

	if (isset($data['level'])){
		if (check_var(trim($data['level']),"digit") or trim($data['level'])=="-1"){
			$level = trim(strip_tags($data['level']));
			if ($level!="-1" and ($level<0 or $level>10)) $level="-1";
		}
		else $level="-1";
	}
	else $level = "-1";

	$moderators=array();
	if (isset($data['moderators']) and is_array($data['moderators'])){
		$moderator="";
		foreach ($data['moderators'] as $moderator){
			if (is_alphanumeric(trim(strip_tags($moderator))))
				$moderators[] = trim(strip_tags($moderator));
		}
	}


	$datastring="<argument>
	<icon>$icon</icon>
	<description>$description</description>
	<level>$level</level>
	<moderators>";
	$moderator="";
	foreach ($moderators as $moderator){
		$datastring .="\n\t\t<user>$moderator</user>";
	}
	$datastring .= "\n\t</moderators>
</argument>";

	if (preg_match("/\<\?/",$datastring) or preg_match("/\?\>/",$datastring)) ff_die("\$datastring cannot contains php tags! ".__FILE__.": ".__LINE__);

	fnwrite("$root/$group/$argument/argument.php","<?xml version='1.0' encoding='UTF-8'?>\n".$datastring,"w",array("nonull"));

}
/**
 * L'interfaccia per operare sui post
 * Modalità consentite:
 * - newtopic
 * - newpost
 * - editpost
 *
 * @param string mode la modalità dell'interfaccia
 * @param string $file il file contenente i dati del topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function edit_post_interface($mode,$file=""){
	$mod = _FN_MOD;
	$group = getparam("group",PAR_GET,SAN_FLAT);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$argument = getparam("argument",PAR_GET,SAN_FLAT);

	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$post = getparam("quote",PAR_GET,SAN_FLAT);

	if (!check_var($post,"digit"))
		ff_die("\$post is not valid! (".strip_tags($post).")",__FILE__,__LINE__);

	if (!preg_match("/^newtopic$|^newpost$|^editpost$/i",$mode))
		ff_die("\$mode is not valid! (".strip_tags($mode).")",__FILE__,__LINE__);

	if ($file!="" and !check_path($file,get_forum_root(),"true"))
		ff_die("\$file is not valid! (".strip_tags($file).")",__FILE__,__LINE__);

	$ffaction="";
	if ($mode=="newtopic") $ffaction="createnewtopic";
	else if ($mode=="newpost") $ffaction="addpost";
	else if ($mode=="editpost") $ffaction="edpost";
	$subj ="";

	if ($mode=="newpost"){
		$tmpdata = array();
		$tmpdata = load_topic($file);
		$latest="";
		$latest = count($tmpdata['posts']);

		if (!preg_match("/^Re: /i",$tmpdata['posts'][$latest-1]['postsubj'])){
			$subj= "Re: ".$tmpdata['posts'][$latest-1]['postsubj'];
		}

		else $subj = $tmpdata['posts'][$latest-1]['postsubj'];

	}

	else if ($mode=="editpost"){
		$tmpdata = array();
		$tmpdata = load_topic($file);
		$subj = $tmpdata['posts'][$post]['postsubj'];
	}

	$body="";

	if ($mode=="newpost"){

		if (isset($tmpdata['posts'][$post])){
			$tmpdata = array();
			$tmpdata = load_topic($file);
			$body="[quote=".$tmpdata['posts'][$post]['poster']."]".
				$tmpdata['posts'][$post]['postbody']."[/quote]";
		}

	}

	else if ($mode=="editpost"){

		if (isset($tmpdata['posts'][$post])){
			$tmpdata = array();
			$tmpdata = load_topic($file);
			$body=$tmpdata['posts'][$post]['postbody'];
		}

	}

	?>
	<script type="text/javascript">
	function validate_topic_form()
		{
			if(document.getElementById('ffsubj').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FOGG?>');
					document.getElementById('ffsubj').focus();
					document.getElementById('ffsubj').value='';
					return false;
				}
			else if(document.getElementById('ffbody').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._FMESS?>');
					document.getElementById('ffbody').focus();
					document.getElementById('ffbody').value='';
					return false;
				}
			else return true;
		}
	</script>
	<?php
	echo "<div align=\"center\">";
	echo "<form action=\"index.php?mod=$mod\" method=\"post\" name=\"editpost\" onsubmit=\"return validate_topic_form()\">";

	if (trim($file)!=""){
		echo "<input type=\"hidden\" name=\"fftopic\" value=\"$file\" />";
	}

	echo "<input type=\"hidden\" name=\"ffgroup\" value=\"$group\" />";
	echo "<input type=\"hidden\" name=\"ffargument\" value=\"$argument\" />";
	echo "<input type=\"hidden\" name=\"ffaction\" value=\"$ffaction\" />";

	if ($mode=="editpost")
		echo "<input type=\"hidden\" name=\"ffpost\" value=\"$post\" />";

	echo "<table style=\"width:100%; text-align:center\">";

	if ($mode=="newtopic")
		echo "<tr><td colspan=\"2\" align=\"center\"><b>"._FNUOVOTOP."</b><br/><br/></td></tr>";

	else if ($mode=="newpost")
		echo "<tr><td colspan=\"2\" align=\"center\"><b>"._FNUOVOMESS."</b><br/><br/></td></tr>";

	echo "<tr><td colspan=\"2\" align=\"center\">";
	bbcodes_panel("ffbody", "home", "formatting"); echo "<br>";
	bbcodes_panel("ffbody", "home", "emoticons");
	echo "</td></tr>";

	//oggetto
	echo "<tr><td>"._FOGG.":</td>";
	echo "<td><input name=\"ffsubj\" id=\"ffsubj\" value=\"$subj\" tabindex=\"1\" /></td></tr>";

	//corpo
	echo "<tr><td>"._FMESS.":</td>";
	echo "<td><textarea name=\"ffbody\" id=\"ffbody\" rows=\"18\" cols=\"63\" tabindex=\"2\" >$body</textarea></td>";
	echo "<tr><td colspan=\"2\" align=\"center\"><br/>
	<input type=\"submit\" value=\""._FINVIA."\" tabindex=\"3\" />&nbsp;&nbsp;
	<input type=\"reset\" value=\""._CANCEL."\" onclick=\"javascript:history.back();\"/>
	<input type=\"button\" value=\""._ANTEPRIMA."\" onclick='prevshow();' />
	</td></tr>";

	echo "</table></form></div>";
	?>
	<script type="text/javascript">
		getElement("ffsubj").onkeyup     = forum_preview;
		getElement("ffsubj").onmousemove = forum_preview;
		getElement("ffbody").onkeyup      = forum_preview;
		getElement("ffbody").onmousemove  = forum_preview;
		forum_preview();
	</script>
	<div id="fnpreview"  style="overflow : auto;top: 15px; left: 15px; max-height: 90%; visibility: hidden; background-color: #F0F0F0; border: 2px solid; padding: 5px; border-top-color: #ffffff; border-left-color: #ffffff; border-bottom-color: #666666; border-right-color: #666666; width: 600px;"></div>
	<?php

	if ($mode!="newtopic")
		forum_view_topic_thread(get_forum_root());

}

/**
 * Crea un nuovo topic
 *
 * @param string $root la root del forum
 * @author Aldo Boccacci
 * @since 0.1
 */
function create_new_topic($root){
	if (_FN_IS_GUEST) ff_die("only user",__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false")) ff_die("forum root is not valid!",__FILE__,__LINE__);
	$mod = _FN_MOD;

	if (isset($_POST['ffgroup'])){
		$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
		if (check_path($group,"","")) $group = strip_tags(trim($group));
		else ff_die("\$group is not valid!",__FILE__,__LINE__);

	}
	else ff_die("\$group is not set!",__FILE__,__LINE__);

	if (isset($_POST['ffargument'])){
		$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
		if (check_path($argument,"","")) $argument = strip_tags(trim($argument));
		else ff_die("\$argument is not valid!",__FILE__,__LINE__);

	}
	else ff_die("\$argument is not set!",__FILE__,__LINE__);

	if (argument_is_locked($root,$group,$argument) and !_FN_IS_ADMIN and !is_forum_moderator()){
		ff_die("Argument locked!",__FILE__,__LINE__);
	}

	//soggetto
	if (isset($_POST['ffsubj']) and strlen(trim($_POST['ffsubj']))>0){
		$subj = getparam("ffsubj",PAR_POST,SAN_FLAT);
		if (check_var($subj,"text")) $subj = strip_tags($subj);
		else ff_die("\$subj is not valid!",__FILE__,__LINE__);

	}
	else ff_die("\$subj is not set!",__FILE__,__LINE__);

	//body
	if (isset($_POST['ffbody']) and strlen(trim($_POST['ffbody']))>0){
		$body = getparam("ffbody",PAR_POST,SAN_NULL);
		$body = htmlentities($body,ENT_COMPAT,_CHARSET);
		if (check_var($body,"text")) $body = strip_tags($body);
		else ff_die("\$body is not valid!",__FILE__,__LINE__);

	}
	else ff_die("\$body is not set!",__FILE__,__LINE__);


	if (!is_forum_moderator()){
		if (is_spam($subj,"words",TRUE)) {
			echo "<div style=\"text-align : center;\">"._TITLESPAM."<br><br><b><a href=\"javascript:history.back()\">"._INDIETRO."</a></b></div>";
			return;
		}
		if (is_spam($body,"words",TRUE)) {
			echo "<div style=\"text-align : center;\">"._SPAMALERT."<br><br><b><a href=\"javascript:history.back()\">"._INDIETRO."</a></b></div>";
			return;
		}
	}


	if (!user_can_view_argument(get_forum_root(),$group,$argument)){
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
		die();
	}

	$time = time();

	$data=array();
	$data['properties']['topictitle']=$subj;
	$data['posts'][0]['poster']=_FN_USERNAME;
	$data['posts'][0]['postsubj']=$subj;
	$data['posts'][0]['postbody']=$body;
	$data['posts'][0]['time']=$time;

	save_topic("$root/$group/$argument/$time.ff.php",$data);

	//aggiorno le statistiche dell'argomento
	$argumentstats = load_argument_stats($group,$argument);
	$argumentstats['topics']= ($argumentstats['topics']+1);
	$argumentstats['posts'] = ($argumentstats['posts']+1);
	$argumentstats['lastpost'] = "$root/$group/$argument/$time.ff.php";
	save_argument_stats($group,$argument,$argumentstats);

// 	add_topic_in_topics_list($group,$argument,"$root/$group/$argument/$time.ff.php");
	update_topics_list($group,$argument);

	fflogf("Created topic \"".strip_tags($subj)."\" -> file ".strip_tags("$root/$group/$argument/$time.ff.php"));
	echo "<div align=\"center\">"._FTOPOK;
	$_POST['fftopic'] = "$time.ff.php"; // impostazione nome parametro topic per alert_list_add
	alert_list_add(); // sottoscrizione automatica in caso di nuova discussione
	echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$time.ff.php\">"._FLEGGI."</a></div>";
echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$time.ff.php\" >";

}


/**
 * Restituisce la versione di Flatforum
 *
 * @return la versione di FlatForum
 * @author Aldo Boccacci
 * @since 0.1
 */
function get_ff_version(){
	return "0.2";
}

/**
 * Restituisce true se l'utente collegato ha i permessi di moderazione
 *
 * @return TRUE se l'utente collegato ha i permessi di moderazione
 * @author Aldo Boccacci
 * @since 0.1
 */
function is_forum_moderator(){
	if (_FN_IS_ADMIN) return TRUE;

	global $forum_moderators;
	$moderators= list_forum_moderators();

	if (in_array(_FN_USERNAME,$moderators)){
		if (versecid(_FN_USERNAME,"home") and is_user()) return TRUE;
	}
}

/**
 * restituisce un array contenente i moderatori del forum contenuti nella variabile
 * $forum_moderators
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function list_forum_moderators(){
	global $forum_moderators;
	$moderators = array();
	$moderatorstmp = explode(",",preg_replace("/ /","",$forum_moderators.","));

	for ($count=0; $count<count($moderatorstmp);$count++){
		if (check_username($moderatorstmp[$count])
			and file_exists(_FN_USERS_DIR."/".$moderatorstmp[$count].".php"))
			$moderators[]=$moderatorstmp[$count];
	}
	return $moderators;
}

/**
 * Incrementa il contatore delle visite al topic
 *
 * @param string $topicfile il topic di riferimento
 * @author Aldo Boccacci
 * @since 0.1
 */
function update_topic_hits($topicfile){
	if (is_forum_moderator()) return;
	if (!check_path($topicfile,"","true")) ff_die("\$topicfile is not valid!(".strip_tags($topicfile).")",__FILE__,__LINE__);

	$topicdata = load_topic($topicfile);

	$topicdata['properties']['hits'] = $topicdata['properties']['hits']+1;
	save_topic($topicfile,$topicdata);

}


/**
 * Restituisce TRUE se il topic specificato non è nascosto.
 *
 * @param string $topicfile il file contenente il topic
 * @since 0.1
 * @author Aldo Boccacci
 */
function topic_is_visible($topicfile){
	if (!check_path($topicfile,"","true")) ff_die("\$topicfile is not valid!(".strip_tags($topicfile).")",__FILE__,__LINE__);
	if (trim($topicfile)=="") return FALSE;

	$filename="";
	$filename = basename($topicfile);
	if (preg_match("/hide_/i",$filename)) return FALSE;
	else return TRUE;

}

/**
 * Restituisce TRUE se il topic specificato non è bloccato.
 *
 * @param string $topicfile il file contenente il topic
 * @since 0.1
 * @author Aldo Boccacci
 */
function topic_is_locked($topicfile){
	if (!check_path($topicfile,"","true")) ff_die("\$topicfile is not valid!(".strip_tags($topicfile).")",__FILE__,__LINE__);

	//prima controllo se è bloccato l'argomento
	$tmp = dirname($topicfile);
	$argument = basename($tmp);
	$tmp = dirname($tmp);
	$group = basename($tmp);

	if (argument_is_locked(get_forum_root(),$group,$argument))
		return TRUE;

	$topicdata = load_topic_properties($topicfile);

	if ($topicdata['properties']['locked']=="true") return TRUE;
	else return FALSE;
}


/**
 * Controlla i parametri GET
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function check_get_params(){

	if (isset($_GET['mod'])){
		$mod = getparam("mod",PAR_GET,SAN_FLAT);
		if (!check_path($mod,"","false")){
			echo "<meta http-equiv=\"Refresh\" content=\"1\"; URL=index.php\">";
			ff_die("\$mod is not valid! (".strip_tags($mod).")",__FILE__,__LINE__);
		}
	}

	if (isset($_GET['group'])){
		$group = getparam("group",PAR_GET,SAN_FLAT);
		if (!check_path($group,"","false")){
			echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php\">";
			ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
		}

		if (!is_dir(stripslashes(get_forum_root()."/$group"))){
			echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=$mod\">";
			ff_die("\$group doesn't exist! (".strip_tags($group).")",__FILE__,__LINE__);
		}

	}

	if (isset($_GET['argument'])){
		if (!isset($_GET['group']) or trim($_GET['group'])=="")
			ff_die("\$group must be set! (".strip_tags($group).")",__FILE__,__LINE__);
		$argument = getparam("argument",PAR_GET,SAN_FLAT);
		if (!check_path($argument,"","false")){
			echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php\">";
			ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);
		}

		if (!is_dir(get_forum_root()."/$group/$argument")){
			echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=$mod\">";
			ff_die("\$argument doesn't exist! (".strip_tags($argument).")",__FILE__,__LINE__);
		}

	}

	if (isset($_GET['topic'])){
		if (!isset($_GET['group']) or trim($_GET['group'])=="")
			ff_die("\$group must be set! (".strip_tags($group).")",__FILE__,__LINE__);
		if (!isset($_GET['argument']) or trim($_GET['argument'])=="")
			ff_die("\$argument must be set! (".strip_tags($argument).")",__FILE__,__LINE__);

		$topic = getparam("topic",PAR_GET,SAN_FLAT);
		if (!check_path($topic,"","true")){
			echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php\">";
			ff_die("\$topic is not valid! (".strip_tags($topic).")",__FILE__,__LINE__);
		}

		if (!is_file(get_forum_root()."/$group/$argument/$topic")){
			echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=$mod\">";
			ff_die("\$topic doesn't exist! (".strip_tags($topic).")",__FILE__,__LINE__);
		}

	}

}

/**
 * Aggiunge un post al file xml del topic
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function add_post(){
	if (_FN_IS_GUEST) ff_die("Only users can add posts to topics!",__FILE__,__LINE__);

	//controllo le variabili
	$mod = _FN_MOD;
	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
// 	if (!check_path($mod,"","false")) ff_die("\$mod is not valid! (".strip_tags($mod).")",__FILE__,__LINE__);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$topicfile = getparam("fftopic",PAR_POST,SAN_FLAT);
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("\$topicfile is not valid! ".basename(__FILE__).": ".__LINE__);

	if (!user_can_view_argument(get_forum_root(),$group,$argument)){
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
		die();
	}

	$topic = basename($topicfile);

	$ffsubj = strip_tags(getparam("ffsubj",PAR_POST,SAN_FLAT));
	if (!check_var($ffsubj,"text"))
		ff_die("\$ffsubj is not valid! (".strip_tags($ffsubj).")",__FILE__,__LINE__);

	$ffbody = strip_tags(htmlentities(getparam("ffbody",PAR_POST,SAN_NULL),ENT_COMPAT,_CHARSET));
	if (!check_var($ffbody,"text"))
		ff_die("\$ffbody is not valid! (".strip_tags($ffbody).")",__FILE__,__LINE__);

	if (trim($ffbody)=="" or trim($ffsubj)==""){
		echo _FERRCAMPO;
		echo "<br/><br/><a href=\"javascript:history.back()\">&#060;&#060;"._INDIETRO."</a>";
		return;
	}

	if (!_FN_IS_ADMIN){
		$ffbody = str_replace("[img]","",$ffbody);
		$ffbody = str_replace("[/img]","",$ffbody);

	}

	global $postperpage;

	$data = array();
	if (file_exists($topicfile))
	$data = load_topic($topicfile);

	//se è bloccato
	if (!is_forum_moderator() and topic_is_locked($topicfile))
		ff_die("Only admins and forum moderators can add posts to locked topics!");

	if (!is_forum_moderator()){
		if (is_spam($ffsubj,"words",TRUE)) {
			echo "<div style=\"text-align : center;\">"._TITLESPAM."<br><br><b><a href=\"javascript:history.back()\">"._INDIETRO."</a></b></div>";
			return;
		}
		if (is_spam($ffbody,"words",TRUE)) {
			echo "<div style=\"text-align : center;\">"._SPAMALERT."<br><br><b><a href=\"javascript:history.back()\">"._INDIETRO."</a></b></div>";
			return;
		}
	}

	$newpost=array();
	$newpost['postsubj'] = $ffsubj;
	$newpost['postbody'] = $ffbody;
	$newpost['poster'] = _FN_USERNAME;
	$newpost['time'] = time();

	$data['posts'][] = $newpost;
	save_topic($topicfile,$data);
	$data = load_topic($topicfile);
	$pagescount = ceil(count($data['posts'])/$postperpage);

	//avvisa gli utenti del nuovo post
	email_alert($group,$argument,$topic);

	//aggiorno le statistiche dell'argomento (se il topic non è nascosto!)
	if (topic_is_visible($topicfile)){
		$argumentstats = load_argument_stats($group,$argument);
		$argumentstats['posts'] = ($argumentstats['posts']+1);
		$argumentstats['lastpost'] = $topicfile;
		save_argument_stats($group,$argument,$argumentstats);
	}

// 	add_topic_in_topics_list($group,$argument,$topicfile);
	update_topics_list($group,$argument);
	fflogf("Added post \"".strip_tags($ffsubj). "\" in ".strip_tags($topicfile));
	//permetti di ritornare
	echo "<div align=\"center\">";
	echo _FMESSOK;
	$_POST['fftopic'] = basename($topic); // impostazione nome parametro topic per alert_list_add
	alert_list_add(); // sottoscrizione automatica in caso di aggiunta messaggio
	echo "<br/><br/>";
	echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;page=$pagescount\">"._FLEGGI."</a>";
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;page=$pagescount\" ></div>";
}

/**
 * Modifica un post del file xml del topic
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function edit_post(){
	if (_FN_IS_GUEST) ff_die("Only users can add posts to topics!",__FILE__,__LINE__);

	//controllo le variabili
	$mod = _FN_MOD;
	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
	$post = getparam("ffpost",PAR_POST,SAN_FLAT);
// 	if (!check_path($mod,"","false")) ff_die("\$mod is not valid! (".strip_tags($mod).")",__FILE__,__LINE__);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_var($post,"digit"))
		ff_die("\$post is not valid! (".strip_tags($post).")",__FILE__,__LINE__);

	if (!user_can_view_argument(get_forum_root(),$group,$argument)){
		echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."\" >";
		die();
	}

	$topicfile = getparam("fftopic",PAR_POST,SAN_FLAT);
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("\$topicfile is not valid! ".basename(__FILE__).": ".__LINE__);

	$topic = basename($topicfile);

	$ffsubj = strip_tags(getparam("ffsubj",PAR_POST,SAN_FLAT));
	if (!check_var($ffsubj,"text"))
		ff_die("\$ffsubj is not valid! (".strip_tags($ffsubj).")",__FILE__,__LINE__);

	$ffbody = strip_tags(htmlentities(getparam("ffbody",PAR_POST,SAN_NULL),ENT_COMPAT,_CHARSET));
	if (!check_var($ffbody,"text"))
		ff_die("\$ffbody is not valid! (".strip_tags($ffbody).")",__FILE__,__LINE__);

	if (trim($ffbody)=="" or trim($ffsubj)==""){
		echo _FERRCAMPO;
		echo "<br/><br/><a href=\"javascript:history.back()\">&#060;&#060;"._INDIETRO."</a>";
		return;
	}

	if (!_FN_IS_ADMIN){
		$ffbody = str_replace("[img]","",$ffbody);
		$ffbody = str_replace("[/img]","",$ffbody);

	}

	//controllo spam
	if (!is_forum_moderator()){
		if (is_spam($ffsubj,"words",TRUE)) {
			echo "<div style=\"text-align : center;\">"._TITLESPAM."<br><br><b><a href=\"javascript:history.back()\">"._INDIETRO."</a></b></div>";
			return;
		}
		if (is_spam($ffbody,"words",TRUE)) {
			echo "<div style=\"text-align : center;\">"._SPAMALERT."<br><br><b><a href=\"javascript:history.back()\">"._INDIETRO."</a></b></div>";
			return;
		}
	}

	global $postperpage;

	$data = array();
	if (file_exists($topicfile))
	$data = load_topic($topicfile);

	//se è bloccato
	if (!is_forum_moderator() and topic_is_locked($topicfile))
		ff_die("Only admins and forum moderators can edit locked posts!");
	if (!_FN_IS_ADMIN and (trim($data['posts'][$post]['poster'])!=_FN_USERNAME) and !is_forum_moderator()) ff_die("Only admins and owners can edit posts!",__FILE__,__LINE__);


	$newpost=array();
	$newpost['postsubj'] = $ffsubj;
	$newpost['postbody'] = $ffbody;
	$newpost['poster'] = $data['posts'][$post]['poster'];
	$newpost['time'] = $data['posts'][$post]['time'];
	$newpost['lasteditposter'] = _FN_USERNAME;
	$newpost['lastedit'] = time();

	$data['posts'][$post] = $newpost;


	save_topic($topicfile,$data);
	$data = load_topic($topicfile);

	fflogf("Edited post: \"".strip_tags($ffsubj)."\" in ".strip_tags($topicfile));

	$pagescount = ceil(count($data['posts'])/$postperpage);
	//permetti di ritornare
	echo "<div align=\"center\">"._FMESSOK;
	$_POST['fftopic'] = basename($topic); // impostazione nome parametro topic per alert_list_add
	alert_list_add(); // sottoscrizione automatica in caso di modifica messaggio
	echo "<br/><br/>";
	echo "<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;page=$pagescount\">"._FLEGGI."</a>";
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic&amp;page=$pagescount\" >";
	echo "</div>";
}

/**
 * Restituisce true se l'utente collegato può vedere l'argomento
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento di riferimento
 * @param array $argumentdata l'array contenente i dati dell'argomento
 * @author Aldo Boccacci
 * @since 0.1
 */
function user_can_view_argument($root,$group,$argument,$argumentdata=""){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	if (!is_array($argumentdata)){
		$argumentdata = array();
		$argumentdata = load_argument_props(get_forum_root(),$group,$argument);
	}

	if ($argumentdata['level']=="-1") return TRUE;
	else {
		if (_FN_IS_GUEST) return FALSE;

		if (getlevel(_FN_USERNAME,"home")<$argumentdata['level']) return FALSE;
		else if (!_FN_IS_GUEST) return TRUE;
	}
}

/**
 * Aggiunge l'utente collegato alla lista degli utenti da avvisare via mail per il topic
 * specificato.
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function alert_list_add(){

	if (_FN_IS_GUEST) ff_die("Only users can do this!",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
	$topic = getparam("fftopic",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;
	$topicfile = "";
	$topicfile= get_forum_root()."/$group/$argument/$topic";

	if (!check_path($topicfile,get_forum_root(),"true"))
		ff_die("\$topicfile is not valid! (".strip_tags($topicfile).")",__FILE__,__LINE__);

	$topicdata = array();

	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);

		if (!_FN_IS_GUEST and trim(_FN_USERNAME)!="" and !in_array(_FN_USERNAME,$topicdata['properties']['emailalert']))
			$topicdata['properties']['emailalert'][]=_FN_USERNAME;

		save_topic($topicfile,$topicdata);
	}

	else {
		fflogf("topic file doesn't exists! (".strip_tags($topicfile).")","ERROR");
	}

	//In caso di nuovi messaggi in questa discussione sarai avvisato con una e-mail
	echo "<div align=\"center\"><b>E-mail alert on</b></div>";
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic\" >";

}

/**
 * Rimuove l'utente collegato dalla lista degli utenti da avvisare via mail per il topic
 * specificato.
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function alert_list_remove(){
	if (_FN_IS_GUEST) ff_die("Only users can do this!",__FILE__,__LINE__);
	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
	$topic = getparam("fftopic",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = "";
	$topicfile= get_forum_root()."/$group/$argument/$topic";
	if (!check_path($topicfile,get_forum_root(),"true"))
		ff_die("\$topicfile is not valid! (".strip_tags($topicfile).")",__FILE__,__LINE__);



	$topicdata = array();
	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);
		if (!_FN_IS_GUEST and trim(_FN_USERNAME)!=""){
			for ($count=0;$count<count($topicdata['properties']['emailalert']);$count++){
				if ($topicdata['properties']['emailalert'][$count]==_FN_USERNAME)
					unset($topicdata['properties']['emailalert'][$count]);
			}
			save_topic($topicfile,$topicdata);
		}
	}
	else {
		fflogf("topic file doesn't exists! (".strip_tags($topicfile).")","ERROR");
	}
	//In caso di nuovi messaggi in questa discussione non sarai più avvisato con una e-mail.
	echo "<div align=\"center\"><b>E-mail alert off</b></div>";
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic\" >";

}

/**
 * Avvisa via mail tutti gli utenti che lo hanno richiesto per il topic specificato
 *
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento di riferimento
 * @param string $topic il topic di riferimento
 * @author Aldo Boccacci
 * @since 0.1
 */
function email_alert($group,$argument,$topic){
	if (_FN_IS_GUEST) ff_die("Only users can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is not valid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";
	if (!check_path($topicfile,get_forum_root(),"true"))
		ff_die("\$topicfile is not valid! (".strip_tags($topicfile).")",__FILE__,__LINE__);

	$topicdata = array();
	$topicdata = load_topic($topicfile);

	$poster = "";
	$count ="";
	$count = count($topicdata['posts']);
	$poster = $topicdata['posts'][$count-1]['poster'];

	if (function_exists("mail")){

		for ($count = 0; $count<count($topicdata['properties']['emailalert']);$count++){
			$user = "";
			if (!isset($topicdata['properties']['emailalert'][$count])) return;
			$user = trim($topicdata['properties']['emailalert'][$count]);
			if (!is_alphanumeric($user)) continue;
			if (!file_exists(get_fn_dir("users")."/$user.php")) continue;

			//se sono io ad aver spedito l'ultimo messaggio non mi autoavviso
			if ($user == _FN_USERNAME) return;


			$userprofile = array();
			$userprofile = load_user_profile($user);

			if (!isset($userprofile['mail'])) continue;

			//controllo la validità della mail
			if (!check_mail($userprofile['mail'])) continue;

			//url
			$url = "http://".$_SERVER['SERVER_NAME']."/".$_SERVER['SCRIPT_NAME']."?mod=$mod&group=$group&argument=$argument&topic=$topic&amp;page=last";

			global $sitename;

			$message=_NEW_MESSAGE_TOPIC." \"".$topicdata['properties']['topictitle']."\":\n$url\n\n"._POSTEDBY.": "._FN_USERNAME;

// 			echo "MAIL SPEDITA:<br/>$message";
			mail($userprofile['mail'],_NEW_MESSAGE_ON." $sitename", $message,"FROM: $sitename <noreply@noreply>\r\nX-Mailer: Flatnuke on PHP/".phpversion());
		}
	}

}

/**
 * Restituisce TRUE se l'argomento è bloccato, FALSE in caso contrario.
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento di riferimento
 * @return TRUE se l'argomento è bloccato, FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 0.1
 */
function argument_is_locked($root,$group,$argument){
	$root = getparam($root,PAR_NULL,SAN_NULL);
	$group = getparam($group,PAR_NULL,SAN_NULL);
	$argument = getparam($argument,PAR_NULL,SAN_NULL);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (file_exists("$root/$group/$argument/lock"))
		return TRUE;
	else return FALSE;
}


/**
 * Restituisce il time dell'ultimo post
 * @param string $topicfile il percorso del topic
 * @return il time dell'ultimo topic inserito
 * @author Aldo Boccacci
 * @since 0.1
 */
function latest_post_time($topicfile){
// return filemtime($topicfile);
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("Topic file path is not valid! ".basename(__FILE__).": ".__LINE__);

	$string = "";
	$string = get_file($topicfile);
// 	echo htmlentities($string);
	$ok = preg_match_all("/\<time\>(.*)\<\/time\>/",$string,$out);
	//incremento di prestazioni
	if ($ok){
		$index="";
		$index = (count($out[0])-1);
		return $out[0][$index];
	}
	else return filemtime($topicfile);

// 	print_r($out);die();
//vecchio meccanismo
	$posts = array();
	$posts = get_xml_array("posts",$string);


	$lastest = count($posts)-1;

	$posttime = "";
	$posttime = get_xml_element("time",$posts[$lastest]);
// 	return $posttime;
	if (check_var($posttime,"digit")){
		return $posttime;
	}
	else return 0;

}

/**
 * Restituisce il numero di posts presenti nel topic
 * @param string $topicfile il percorso del topic
 * @return il numero di posts presenti nel topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function count_posts($topicfile){
	if (!check_path($topicfile,get_forum_root(),"true")) ff_die("Topic file path is not valid! ".basename(__FILE__).": ".__LINE__);

	$string = "";
	$string = get_file($topicfile);
	$posts = array();
	$posts = get_xml_array("post",$string);

	return count($posts);
}

/**
 * Restituisce TRUE se la stringa specificata viene ritenuta spam
 * secondo i criteri contenuti nel file $spamfile
 * @param string $string la stringa da controllare
 * @param string $spamfile il nome senza estensione del file contenente i criteri di riconiscimento
 * @author Aldo Boccacci
 * @since 0.1
 *
 * DEPRECATED -> use is_spam instead
 */
function ff_is_spam($string,$spamfile){
	if (!check_path($spamfile,"","false")) ff_die("spam file path is not valid! ".basename(__FILE__).": ".__LINE__);

	if (trim($string)=="") return FALSE;

	$blstring ="";
	if (!file_exists("include/blacklists/$spamfile.php")){
// 		fflogf("Spamfilter file doesn't exists! (".strip_tags($spamfile).")");
		return TRUE;
	}
	$blstring=get_file("include/blacklists/$spamfile.php");

	$wordsarray=array();
	$wordsarray=explode("\n",$blstring);
	$item="";

	foreach ($wordsarray as $item){
		if (preg_match("/^#/",trim($item))) continue;
		if (trim($item)=="") continue;
		if (preg_match("/\b$item\b/",$string)) return TRUE;
	}

	return FALSE;
}

/**
 * Salva le statistiche dell'argomento indicato, in modo da visualizzarle velocemente in fase di lettura
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento di riferimento
 * @return TRUE se l'argomento è bloccato, FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 0.1
 */
function save_argument_stats($group,$argument,$data){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	if (!is_array($data))
		ff_die("\$data must be an array!",__FILE__,__LINE__);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);


	if (!is_dir(get_forum_root()."/$group/$argument")){
		echo "L'argomento ".strip_tags($argument)." non esiste nel gruppo ".strip_tags($group).". ".__FILE__." ".__LINE__;
		return;
	}

	$topics=0;
	if (isset($data['topics']) and check_var($data['topics'],"digit")){
		$topics = $data['topics'];
	}
	else $topics="0";

	$posts=0;
	if (isset($data['posts']) and check_var($data['posts'],"digit")){
		$posts = $data['posts'];
	}
	else $posts="0";

	$lastpost="";
	if (isset($data['lastpost']) and check_path($data['lastpost'],get_forum_root(),"true")){
		$lastpost = $data['lastpost'];
	}
	else $lastpost="";

	$string = "<argstats>
	<topics>$topics</topics>
	<posts>$posts</posts>
	<lastpost>$lastpost</lastpost>
</argstats>";

	if (preg_match("/\<\?/",$string) or preg_match("/\?\>/",$string)) ff_die("\$string cannot contains php tags! ".__FILE__.": ".__LINE__);
	fnwrite(get_forum_root()."/$group/$argument/stats.php","<?xml version='1.0' encoding='UTF-8'?>\n".$string,"w",array("nonull"));

}

/**
 * Carica le statistiche dell'argomento indicato, in modo da visualizzarle velocemente in fase di lettura
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento di riferimento
 * @return TRUE se l'argomento è bloccato, FALSE in caso contrario
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_argument_stats($group,$argument){
	$group = getparam($group,PAR_NULL,SAN_NULL);
	$argument = getparam($argument,PAR_NULL,SAN_NULL);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	if (!file_exists(get_forum_root()."/$group/$argument/stats.php"))
		update_argument_stats($group,$argument);

	$string = get_file(get_forum_root()."/$group/$argument/stats.php");

	$data = array();

	$topics="";
	$topics = ff_get_xml_element("topics",$string);
	if (check_var($topics,"digit")){
		$data['topics'] = $topics;
	}
	else $data['topics'] = 0;

	$posts = ff_get_xml_element("posts",$string);
	if (check_var($posts,"digit")){
		$data['posts'] = $posts;
	}
	else $data['posts'] = 0;

	$lastpost = ff_get_xml_element("lastpost",$string);
	if (check_path($lastpost,get_forum_root(),"true")){
		$data['lastpost'] = $lastpost;
	}
	else $data['lastpost'] = "";

	return $data;
	//TODO
}

/**
 * Aggiorna le statistiche dell'argomento indicato, in modo da visualizzarle velocemente in fase di lettura
 * @param string $group il gruppo di riferimento
 * @param string $argument l'argomento di riferimento
 * @author Aldo Boccacci
 * @since 0.1
 */
function update_argument_stats($group,$argument){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (!is_dir(get_forum_root()."/$group/$argument")){
		echo "L'argomento ".strip_tags($argument)." non esiste nel gruppo ".strip_tags($group).". ".__FILE__." ".__LINE__;
		return;
	}

	$data = array();

	$topics = array();
	$topics= list_argument_topics(get_forum_root(),$group,$argument,"false","false");
// 	print_r($topics);
	$data['topics'] = count($topics);

	$totpost=0;;
	if (count($topics)>0){
		foreach($topics as $topic){
			if ($topic!="NULL")
			$totpost = $totpost + count_posts($topic);
		}
	}

	$data['posts']= $totpost;

	reset($topics);
	$data['lastpost'] = current($topics);

	save_argument_stats($group,$argument,$data);

	update_topics_list($group,$argument);

}

/**
 * Aggiorna le statistiche di tutti gli argomenti del forum
 * @author Aldo Boccacci
 * @since 0.1
 */
function update_forum_stats(){
	$groups = array();
	$groups = list_forum_groups(get_forum_root());
	$group="";
	foreach($groups as $group){
		$arguments = array();
		$arguments = list_group_arguments(get_forum_root(),$group);
		$argument="";
		foreach ($arguments as $argument){
			update_argument_stats($group,$argument);
		}
	}
}

/**
 * Carica la lista dei topics dell'argomento specificato
 *
 * @param string $group il gruppo
 * @param string $argument l'argomento
 * @return l'array con l'elenco dei topics
 * @author Aldo Boccacci
 * @since 0.1
 */
function load_topics_list($group,$argument){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);


	if (!is_dir(get_forum_root()."/$group/$argument")){
		echo "L'argomento ".strip_tags($argument)." non esiste nel gruppo ".strip_tags($group).". ".__FILE__." ".__LINE__;
		return;
	}

	$topics = array();
	if (!file_exists(get_forum_root()."/$group/$argument/topicslist.php"))
		update_topics_list($group,$argument);

	$string="";
	$string=get_file(get_forum_root()."/$group/$argument/topicslist.php");
	$string= ff_get_xml_element("topics",$string);
	$topicstmp=array();
	$topicstmp = get_xml_array("topic",$string);

	$topics=array();
	$topic ="";
	foreach ($topicstmp as $topic){
		$topics[]=trim($topic);
	}

	return $topics;

}

/**
 * Salva la lista dei topics dell'argomento specificato
 *
 * @param string $group il gruppo
 * @param string $argument l'argomento
 * @param array $topics la lista dei topics
 * @author Aldo Boccacci
 * @since 0.1
 */
function save_topics_list($group,$argument,$topics){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;
	if (!is_dir(get_forum_root()."/$group/$argument")){
		echo "L'argomento ".strip_tags($argument)." non esiste nel gruppo ".strip_tags($group).". ".__FILE__." ".__LINE__;
		return;
	}

	if (!is_array($topics)) ff_die("\$data must be an array()! ".__FILE__.": ".__LINE__);

	$datastring="<topics>\n";
	$topic="";
	foreach($topics as $topic){
		if (file_exists($topic)) $datastring .= "\t<topic>".strip_tags($topic)."</topic>\n";
	}

	$datastring .= "</topics>";

	if (preg_match("/\<\?/",$datastring) or preg_match("/\?\>/",$datastring)) ff_die("\$datastring cannot contains php tags! ".__FILE__.": ".__LINE__);

	fnwrite(get_forum_root()."/$group/$argument/topicslist.php","<?php die(); ?>\n$datastring","w",array("nonull"));

}

/**
 * Aggiorna la lista dei topics per l'argomento specificato
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function update_topics_list($group,$argument){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (!is_dir(get_forum_root()."/$group/$argument")){
		echo "L'argomento ".strip_tags($argument)." non esiste nel gruppo ".strip_tags($group).". ".__FILE__." ".__LINE__;
		return;
	}

	save_topics_list($group,$argument,list_argument_topics(get_forum_root(),$group,$argument,"true","true"));

}

/**
 * Aggiunge il topic indicato alla lista dei topics dell'argomento specificato
 * (In cima)
 * @param string $group il gruppo
 * @param string $argument l'argomento
 * @param string $topic il topic da aggiungere
 * @author Aldo Boccacci
 * @since 0.1
 */
function add_topic_in_topics_list($group,$argument,$topic){
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is not valid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$topicstmp = array();
	$topicstmp = load_topics_list($group,$argument);
	$topics = array();

	//aggiungo l'elemento nuovo
	$topics[]= $topic;
	$topictmp="";
	foreach ($topicstmp as $topictmp){
		if (basename($topic)==basename($topictmp))
			continue;
		$topics[]=$topictmp;
	}

	save_topics_list($group,$argument,$topics);
}

/**
 *
 * Crea il pannello per navigare tra le pagine
 * Idea from Boyashi's BForum
 *
 * @param string $page la pagina attuale
 * @param string $pagescount il numero totale di pagine
 * @param string $link il link
 * @author Aldo Boccacci
 * @since 0.1
 */
function ff_page_selector($page,$pagescount,$link){
	$page=trim(strip_tags($page));
	$pagescount = trim(strip_tags($pagescount));
	$link = strip_tags($link);
	if (!check_var($page,"digit")) return;
	if (!check_var($pagescount,"digit")) return;
// 		$pagescount = ceil(count($topics)/$topicperpage);
		echo "<b>"._GOTOTHEPAGE.":</b><br>";
	if ($page>1){
		echo "<span class=\"forum-page-selector\"><a style=\"text-decoration: none;\" href=\"$link\" title=\""._GOTOTHEFIRSTPAGE."\">&#060;&#060;</a></span>&nbsp;";
	}

	if ($page>1){
		echo "<span class=\"forum-page-selector\"><a style=\"text-decoration: none;\" href=\"$link&amp;page=".($page-1)."\" title=\""._GOTOTHEPREVIOUSPAGE."\">&#060;</a></span>&nbsp;&nbsp;";
	}

	echo "<select name=\"ffpage\" onchange=\"window.location='$link&amp;page='+(this.selectedIndex+1)\">";
// 	echo "<select name=\"ffpage\" onchange=\"window.location='$link&amp;page='+this.options[this.selectedIndex].value\">";
	for ($count=1; $count<$pagescount+1;$count++){
		if ($count==$page)
			echo "<option selected=\"selected\">$count</option>";
		else echo "<option>$count</option>";
	}
	echo "</select>";

	if ($page<$pagescount){
		echo "&nbsp;&nbsp;<span class=\"forum-page-selector\"><a style=\"text-decoration: none;\" href=\"$link&amp;page=".($page+1)."\" title=\""._GOTOTHENEXTPAGE."\">&#062;</a></span>&nbsp;";
		echo "<span class=\"forum-page-selector\"><a style=\"text-decoration: none;\" href=\"$link&amp;page=$pagescount\" title=\""._GOTOTHELASTPAGE."\">&#062;&#062;</a></span>";

	}
}


function ff_get_xml_element($elem, $xml) {
	$elem = getparam($elem,PAR_NULL, SAN_FLAT);
	$xml  = getparam($xml, PAR_NULL, SAN_NULL);

	$ok=0;
	$ok=preg_match( "/\<$elem\>(.*?)\<\/$elem\>/s",$xml, $out );
	if ($ok) return $out[1];
	else return "";
	$buff = preg_replace("/.*<".$elem.">/i", "", $xml);
	$buff = preg_replace("/<\/".$elem.">.*/i", "", $buff);
	return $buff;
}

/**
 * Questa funzione serve per salvare il log di flatforum
 * Il messaggio viene formattato aggiungendo campi di interesse.
 *
 * @param string $message il messaggio da salvare
 * @param string $type il tipo di messaggio. Può essere lasciato vuoto o
 *               impostato a "ERROR"
 * @author Aldo Boccacci
 * @since 0.1
 */
function fflogf($message,$type="") {
// 	global $fflogfile;
	$fflogfile=get_fn_dir("var")."/log/forumlog.php";
	if (!isset($fflogfile)) $fflogfile=get_fn_dir("var")."/log/forumlog.php";

	if (preg_match("/\<\?/",$message) or preg_match("/\?\>/",$message)) fd_die(_FDNONPUOI.basename(__FILE__).": ".__LINE__);

	if ($type=="ERROR"){
		$fflogfile = preg_replace("/\.php$/i","error.php",$fflogfile);
	}

	if (!is_dir(get_fn_dir("var")."/log/")){
		fn_mkdir(get_fn_dir("var")."/log","0777");

	}

	if (!file_exists("$fflogfile")){
		fnwrite($fflogfile,"<?php exit(1);?>\n","w",array("nonull"));
	}
	else {
		$logtext="";
		$logtext = get_file($fflogfile);
		if (!preg_match("/\<\?php exit\(1\);\?\>/i",$logtext)){
// 		echo "no codice controllo";
			fnwrite($fflogfile,"<?php exit(1);?>\n$logtext","w",array("nonull"));
		}
	}


	//l'utente collegato
	$myforum="";
	if (isset($_COOKIE['myforum'])) $myforum = $_COOKIE['myforum'];
// 	if (!is_alphanumeric($myforum)) $myforum ="";
	if (!versecid($myforum)) $myforum .= "(NOT VALID!)";
	$REMOTE_ADDR="";
	if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];
	else $REMOTE_ADDR="";
	if (isset($_GET['mod'])) $mod=$_GET['mod'];
	else $mod="";

	$messageok = date(_FDDATEFORMAT)."
	ff version: ".get_ff_version()."
	user: $myforum
	remoteaddr: $REMOTE_ADDR
	section: $mod
	message: $message";

	//prima richiede modifica a fnwrite
// 	fnwrite($fflogfile,strip_tags("$messageok\n"),"a",array("nonull"));
	$fl=fopen("$fflogfile","a");
	fwrite($fl, strip_tags("$messageok\n"));
	fclose($fl);

}


function show_last_posts(){

}

function load_last_posts(){

}

function save_last_posts(){

}

function add_in_last_posts($topics, $post){



}

?>
