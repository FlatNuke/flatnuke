<?php

if (preg_match("/ffadmin.php/i",$_SERVER['PHP_SELF'])) {
    Header("Location: ../../index.php");
    fd_die("You cannot call ffadmin.php!",__FILE,__LINE);
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

if (!is_admin() and !is_forum_moderator()) ff_die("Only admins and moderators can include ffadmin.php",__FILE__,__LINE__);

/**
 * Maschera per creare un nuovo gruppo
 *
 * Questa funzione mostra la maschera per creare un nuovo gruppo.
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 *
 */
function create_group_interface($root){
if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
if (!check_path($root,get_forum_root(),"false")) ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$mod = _FN_MOD;
	?>
	<script type="text/javascript">
	function validate_group_form()
		{
			if(document.getElementById('ffnewgroup').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._GROUP?>');
					document.getElementById('ffnewgroup').focus();
					document.getElementById('ffnewgroup').value='';
					return false;
				}
			else return true;
		}
	</script>
	<?php
	echo "<b>"._CHOOSEGROUPNAME.":</b><br/><br/>";
	echo "<form action=\"index.php?mod=$mod\" method=\"POST\" onsubmit=\"return validate_group_form()\">
	<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"creategroup\" />
	<input type=\"text\" name=\"ffnewgroup\" id=\"ffnewgroup\" class=\"ffinput\" /><br/><br/>
	<input type=\"submit\" name=\"ffok\" value=\""._CREATEGROUP."\">
	</form>";
}

/**
 * Crea un nuovo gruppo a partire da $root
 *
 * Funzione che si occupa di creare il gruppo.
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 */
function create_group($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);
	$group = getparam("ffnewgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	$mod = _FN_MOD;

	$group= preg_replace("/ /","_",$group);

	if (is_dir("$root/$group")){
		echo _THEGROUP." <b>$group</b> "._ALREADYEXISTS;
		return;
	}
	else {
		fn_mkdir(stripslashes("$root/$group"),0777);
		fnwrite("$root/$group/level.php","10","w",array("nonull"));
		fnwrite("$root/$group/group.php","in futuro conterrà le proprietà del gruppo","w",array("nonull"));
		fflogf("created group $group");
	}

	echo "<br/><div align=\"center\"><b>"._GROUPCREATED."</b><br/><br/><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a></div>";

}

/**
 * Maschera per creare un nuovo argomento
 *
 * Questa funzione mostra la maschera per creare un argomento
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 *
 */
function create_argument_interface($root){
if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
if (!check_path($root,get_forum_root(),"false")) ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);
global $theme;
	$mod = _FN_MOD;
	?>
	<script type="text/javascript">
	function validate_argument_form()
		{
			if(document.getElementById('ffnewargument').value=='')
				{
					alert('<?php echo _REQUIREDFIELD.": "._ARGUMENT?>');
					document.getElementById('ffnewargument').focus();
					document.getElementById('ffnewargument').value='';
					return false;
				}
			else return true;
		}
	</script>
	<?php
	echo "<form action=\"index.php?mod=$mod\" method=\"post\" onsubmit=\"return validate_argument_form()\">
	<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"createargument\" />";

	echo "<br/><b>"._ARGUMENTGROUP.":</b><br/><br/>";



	$groups = array();
	$groups = list_forum_groups($root);
	$group = "";
	if (count($groups)>0){
		echo "<select name=\"ffgroup\" id=\"ffgroup\">";
		foreach ($groups as $group){
			if (is_dir("$root/$group") and is_writable("$root/$group"))
				echo "<option>$group</option>";
			else echo "<option disabled=\"disabled\">$group</option>";
		}
		echo "</select><br/><br/>";
	}
	else echo "<b>No group avaible!</b><br/><br/>";


	echo "<b>"._ARGUMENTNAME.":</b><br/><br/>";

	echo "<input type=\"text\" name=\"ffnewargument\" id=\"ffnewargument\" class=\"ffinput\" /><br/><br/>";

	echo "<b>"._ARGUMENTLEVEL.":</b><br/><br/>";

	echo "<select name=\"fflevel\">";
	echo "<option value=\"-1\">---</option>";
	$countlevel=0;
	for ($countlevel;$countlevel<11;$countlevel++){
		echo "<option value=\"$countlevel\" >$countlevel</option>";
	}
	echo "</select><br/><br/>";

	echo "<b>"._ARGUMENTIMAGE.":</b><br><br>";
	$argicons=array();
	$argicons = glob("forum/icons/argument_icons/*");
	if (!$argicons) $argicons = array(); // glob may returns boolean false instead of an empty array on some systems
	$argicon="";
	echo "<img src=\"".$argicons[0]."\" name=\"argicon\">&nbsp;&nbsp;";
	echo "<select name=\"ffargicon\" id=\"ffargicon\" onChange='document.argicon.src=this.options[this.selectedIndex].value'>";

	foreach ($argicons as $argicon){
		echo "<option value=\"$argicon\">".basename($argicon)."</option>";
	}
	echo "<option value=\"themes/$theme/images/section.png\">default</option>";

	echo "</select><br><br>";

	echo "<b>"._ARGUMENTDESC.":</b><br/><br/>";

	echo "<textarea name=\"ffargumentdesc\" cols=\"40\" rows=\"2\"></textarea><br/><br/>";

	echo "<input type=\"submit\" name=\"ffok\" value=\""._CREATEARGUMENT."\" />
	</form>";
}

/**
 * Crea un nuovo argomento a partire da $root
 *
 * Funzione che si occupa di creare un argomento
 *
 * @param string $root la cartella nella quale creare l'argomento
 * @since 0.1
 * @author Aldo Boccacci
 */
function create_argument($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$argument = getparam("ffnewargument",PAR_POST,SAN_FLAT);
// 	$argument=str_replace("/","&#47;",$argument);

	$argument = preg_replace("/ /","_",$argument);

	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$level = getparam("fflevel",PAR_POST,SAN_FLAT);
	if (!check_var($level,"digit") and $level!="-1"){
		ff_die("\$level is not valid! (".strip_tags($level).")",__FILE__,__LINE__);
	}
	if ($level!="-1" and ($level>10 or $level<0))
		ff_die("\$level is not valid! (".strip_tags($level).")",__FILE__,__LINE__);

	$description = strip_tags(getparam("ffargumentdesc",PAR_POST,SAN_FLAT));
	if (!check_var($description,"text"))
		ff_die("\$description is not valid! (".strip_tags($description).")",__FILE__,__LINE__);

	$argicon=getparam("ffargicon",PAR_POST,SAN_FLAT);
	if (!check_path($argicon,"","false"))
		ff_die("\$argicon is not valid! (".strip_tags($argicon).")",__FILE__,__LINE__);

	$mod = _FN_MOD;


	if (is_dir("$root/$group/$argument")){
		echo _THEARGUMENT." <b>$argument</b> "._ARGUMENTEXISTS." <b>$group</b>, "._ARGUMENTCHANGENAME;
		echo "<br><br><a href=\"javascript:history.back()\">"._INDIETRO."</a>";

	}
	else {
		if (fn_mkdir("$root/$group/$argument",0777)){
			fnwrite("$root/$group/$argument/level.php","10","w",array("nonull"));
			fflogf("Created argument $argument in the group $group");
			echo "<div align=\"center\"><br><b>"._ARGUMENTCREATED."!</b>";
			echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a></div>";
		}
	}

	$argumentprops = array();
	$argumentprops['description'] = $description;
	$argumentprops['level'] = $level;
	$argumentprops['icon']=$argicon;
	save_argument_props($root,$group,$argument,$argumentprops);

}


/**
 * Imposta se il topic deve essere messo in cima alla lista nella visualizzazione dell'argomento
 *
 * Imposta se il topic deve essere messo in cima alla lista nella visualizzazione dell'argomento
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @param boolean $evidenzia specifica se evidenziare il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function evidenzia_topic($group,$argument,$topic,$evidenzia){
	if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam("group",PAR_GET,SAN_FLAT);
	$argument = getparam("argument",PAR_GET,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	$argument = getparam("argument",PAR_GET,SAN_FLAT);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	$topic = getparam("topic",PAR_GET,SAN_FLAT);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (!check_var($evidenzia,"boolean")) ff_die("\$evidenzia must be a boolean value!",__FILE__,__LINE__);

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	if (file_exists($topicfile)){
		if ($evidenzia=="true"){
			if (preg_match("/top_/i",trim($topic))){
				echo "topic file is already on top!";
			}
			else {
				rename($topicfile,dirname($topicfile)."/top_$topic");
				fflogf("Topic".strip_tags($topicfile)." is now sticky.");
			}

		}
		else if ($evidenzia=="false"){
			if (!preg_match("/top_/i",trim($topic))){
				echo "$topic topic file is already normal";
			}
			else {
				rename($topicfile,dirname($topicfile)."/".preg_replace("/top_/i","",$topic));
				fflogf("Topic ".strip_tags($topicfile)." is now normal (not sticky).");
			}

		}
	}

	update_topics_list($group,$argument);
	update_argument_stats($group,$argument);
	echo "<div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" >"._RETURN."</a>
	</div>";
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" >";
// 	header("index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument));
}


/**
 * Nasconde il topic specificato
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function hide_topic($group, $argument, $topic){
	if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		if (!preg_match("/hide_/i",basename($topicfile))){
			if (!rename($topicfile,dirname($topicfile)."/hide_".basename($topicfile)))
				fflogf("I cannot set hide status of the file ".strip_tags($topicfile),"ERROR");
			else {
				update_argument_stats($group,$argument);
			}
		}
	}
	else {
		fflogf("I cannot set hide status because the file ".strip_tags($topicfile)." doesn't exists!","ERROR");
	}

	echo "<div align=\"center\"><b>"._TOPICHIDDEN."</b><br><br>
	<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" >"._RETURN."</a>
	</div>";

	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" ></div>";

}

/**
 * Mostra il topic specificato
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function show_topic($group, $argument, $topic){
	if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		if (preg_match("/hide_/i",basename($topicfile))){
			if (!rename($topicfile, dirname($topicfile)."/".preg_replace("/hide_/i","",basename($topicfile))))
			fflogf("I cannot unset hide status of the file ".strip_tags($topicfile),"ERROR");
		}
	}
	else {
		fflogf("I cannot unset hide status because the file ".strip_tags($topicfile)." doesn't exists!","ERROR");
	}

	update_topics_list($group,$argument);
	update_argument_stats($group,$argument);

	echo "<div align=\"center\"><b>"._TOPICSHOWED."<b/><br><br>
	<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" >"._RETURN."</a>
	</div>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" ></div>";

}

/**
 * Blocca il topic specificato
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function lock_topic($group, $argument, $topic){
	if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);
		$topicdata['properties']['locked'] = "true";

		save_topic($topicfile,$topicdata);

	}
	else {
		fflogf("I cannot set lock status because the file ".strip_tags($topicfile)." doesn't exists!","ERROR");
	}
	echo "<div align=\"center\"><b>"._TOPICLOCKED."<b/><br><br>
	<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" >"._RETURN."</a>
	</div>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" ></div>";

}

/**
 * Blocca il topic specificato
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function unlock_topic($group, $argument, $topic){
	if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);
		$topicdata['properties']['locked'] = "false";

		save_topic($topicfile,$topicdata);

	}
	else {
		fflogf("I cannot set lock status because the file ".strip_tags($topicfile)." doesn't exists!","ERROR");
	}
	echo "<div align=\"center\"><b>"._TOPICUNLOCKED."</b><br><br>
	<a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" >"._RETURN."</a>
	</div>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."\" ></div>";

}

/**
 * Elimina il post indicato
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @param integer $number il numero del post da eliminare
 * @author Aldo Boccacci
 * @since 0.1
 */
function delete_post($group,$argument,$topic,$number){
	if (!_FN_IS_ADMIN) ff_die("only admins can delete posts!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	$number = getparam($number,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);
	if (!check_var($number,"digit"))
		ff_die("\$number is invalid! (".strip_tags($number).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);
		if ($number>count($topicdata['posts']))
			ffdie("The post: ".strip_tags($number)."doesn't exists!",__FILE__,__LINE__);

		unset($topicdata['posts'][$number]);
	}
	//gestisco l'eliminazione del post in testa
	if ($topicdata['properties']['postontop']==$number){
		$topicdata['properties']['postontop']="";
	}

	save_topic($topicfile,$topicdata);

	//aggiorno le statistiche dell'argomento
	update_argument_stats($group,$argument);
	update_topics_list($group,$argument);

	echo "<div align=\"center\"><b>"._POSTDELETED."</b></div>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic\" ></div>";

}

/**
 * Fa in modo che il posto specificato sia sempre mostrato in cima al thread
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @param integer $number il numero del post da evidenziare
 * @author Aldo Boccacci
 * @since 0.1
 */
function set_post_on_top($group,$argument,$topic,$number){
if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	$number = getparam($number,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);
	if (!check_var($number,"digit"))
		ff_die("\$number is invalid! (".strip_tags($number).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);
		if ($number>count($topicdata['posts']))
			ffdie("The post: ".strip_tags($number)."doesn't exists!",__FILE__,__LINE__);

		$topicdata['properties']['postontop'] = $number;
	}

	save_topic($topicfile,$topicdata);

	echo "<div align=\"center\">Il post sar&agrave; visualizzato in cima</div>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic\" ></div>";
}

/**
 * Rimuove l'impostazione di mostrare un post sempre in cima.
 *
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function remove_post_on_top($group,$argument,$topic){
if (!is_forum_moderator()) ff_die("only moderators and admins can do this!",__FILE__,__LINE__);

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	$topic = getparam($topic,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);
	if (!check_path($topic,"","true"))
		ff_die("\$topic is invalid! (".strip_tags($topic).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$topicfile = get_forum_root()."/$group/$argument/$topic";

	$topicdata = array();
	if (file_exists($topicfile)){
		$topicdata = load_topic($topicfile);
		$topicdata['properties']['postontop'] = "";
	}

	save_topic($topicfile,$topicdata);

	echo "<div align=\"center\">Il post sar&agrave; visualizzato normalmente</div>";
	//ritorno
	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."&amp;argument=".rawurlencodepath($argument)."&amp;topic=$topic\" ></div>";
}

/**
 * Visualizza l'interfaccia per modificare un argomento
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @author Aldo Boccacci
 * @since 0.1
 *
 */
function edit_argument_interface($root,$group,$argument){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	global $theme;

	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$argumentdata =array();
	$argumentdata = load_argument_props(get_forum_root(),$group,$argument);

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">
	<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"modifyargument\" />
	<input type=\"hidden\" name=\"ffgroup\" readonly=\"readonly\" value=\"$group\" />
	<input type=\"hidden\" name=\"ffargument\" readonly=\"readonly\" value=\"$argument\" />";

// 	echo "<b>Scegli il nome dell'argomento:</b><br/><br/>";
//
// 	echo "<input type=\"text\" name=\"ffnewargument\" class=\"ffinput\"><br/><br/>";

	echo "<b>"._ARGUMENTLEVEL.":</b><br/><br/>";

	echo "<select name=\"fflevel\">";
	echo "<option value=\"-1\">---</option>";
	$countlevel=0;
	for ($countlevel;$countlevel<11;$countlevel++){
		echo "<option value=\"$countlevel\"";
		if ($countlevel==$argumentdata['level']) echo "selected=\"selected\"";
		echo ">$countlevel</option>";
	}
	echo "</select><br/><br/>";

	echo "<b>"._ARGUMENTIMAGE.":</b><br><br>";
	$argicons=array();
	$argicons = glob("forum/icons/argument_icons/*");
	if (!$argicons) $argicons = array(); // glob may returns boolean false instead of an empty array on some systems
	$argicon="";
	echo "<img src=\"".$argumentdata['icon']."\" name=\"argicon\">&nbsp;&nbsp;";
	echo "<select name=\"ffargicon\" id=\"ffargicon\" onChange='document.argicon.src=this.options[this.selectedIndex].value'>";
	foreach ($argicons as $argicon){
		echo "<option value=\"$argicon\"";
		if (basename($argicon)==basename($argumentdata['icon']))
			echo " selected=\"selected\"";
		echo ">".basename($argicon)."</option>";
	}
	echo "<option value=\"themes/$theme/images/section.png\">default</option>";

	echo "</select><br><br>";

	echo "<b>"._ARGUMENTDESC.":</b><br/><br/>";

	echo "<textarea name=\"ffargumentdesc\" cols=\"40\" rows=\"2\">".$argumentdata['description']."</textarea><br/><br/>";

	echo "<input type=\"submit\" name=\"ffok\" value=\""._ARGUMENTEDIT."\" />
	</form>";
}

/**
 *
 * Salva l'argomento modificato
 *
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function save_argument(){
	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$description = strip_tags(getparam("ffargumentdesc",PAR_POST,SAN_FLAT));
	if (!check_var(strip_tags($description),"text"))
		ff_die("\$description is not valid! (".strip_tags($description).")",__FILE__,__LINE__);
	$level = strip_tags(getparam("fflevel",PAR_POST,SAN_FLAT));
	if (!check_var(strip_tags($level),"digit") and trim($level)!="-1")
		ff_die("\$level is not valid! (".strip_tags($level).")",__FILE__,__LINE__);

	$argicon=getparam("ffargicon",PAR_POST,SAN_FLAT);
	if (!check_path($argicon,"","false"))
		ff_die("\$argicon is not valid! (".strip_tags($argicon).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	$data = array();
	$data['description'] = $description;
	$data['level'] = $level;
	$data['icon']=$argicon;

	save_argument_props(get_forum_root(),$group,$argument,$data);

	echo "<div align=\"center\"><b>"._ARGUMENTEDITED."!</b>
	<br/><br/><a href=\"index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."\"><b>"._RETURN."</b></a>
	</div>";


// 	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."\" ></div>";
}

/**
 * Interfaccia per rinominare un argomento
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function rename_argument_interface($root,$group,$argument){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">";
	echo "<input type=\"hidden\" readonly name=\"ffgroup\" value=\"$group\" />";
	echo "<input type=\"hidden\" readonly name=\"ffoldargumentname\" value=\"$argument\" />";
	echo "<input type=\"hidden\" readonly name=\"ffaction\" value=\"renameargument\" />";
	echo "<br/>"._RENAMETHEARGUMENT." <b>$argument</b><br/><br/>";

	echo "<input type=\"text\" name=\"ffnewargumentname\" class=\"ffinput\" value=\"$argument\" /><br/><br/>
	<input type=\"submit\" name=\"ffok\" value=\""._RENAMEARGUMENT."\" />";

	echo "</form>";
}

/**
 * Rinomina l'argomento specificato mediante la maschera.
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 */
function rename_argument($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$oldargumentname = getparam("ffoldargumentname",PAR_POST,SAN_FLAT);
	if (!check_path($oldargumentname,"","false"))
		ff_die("\$oldargumentname is not valid! (".strip_tags($oldargumentname).")",__FILE__,__LINE__);
	$newargumentname = getparam("ffnewargumentname",PAR_POST,SAN_FLAT);
	if (!check_path($newargumentname,"","false"))
		ff_die("\$newargumentname is not valid! (".strip_tags($newargumentname).")",__FILE__,__LINE__);

	$newargumentname = preg_replace("/ /","_",$newargumentname);

	$mod = _FN_MOD;

	if (trim($oldargumentname)==trim($newargumentname)){
	echo "<div align=\"center\">Il nome dell'argomento non &egrave; cambiato!</div>";
	}

	if (!is_dir("$root/$group/$oldargumentname"))
		echo "<b>"._ATTENTION.":</b> "._THEARGUMENT." <b>$root/$group/$oldargumentname</b> "._DOESNTEXISTS."!";

	if (is_dir("$root/$group/$newargumentname")){
		echo "<div align=\"center\"><b>"._ATTENTION.":</b> "._THEARGUMENT." <b>$newargumentname</b> "._ALREADYEXISTS.".";

		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;
	}


	if (rename("$root/$group/$oldargumentname","$root/$group/$newargumentname")){

		fflogf("Renamed argument $root/$group/$oldargumentname in $root/$group/$newargumentname");

		update_argument_stats($group,$newargumentname);

		echo "<br/><div align=\"center\"><b>"._ARGUMENTRENAMED."!</b></div>";
	}

	echo "<br/><br/><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._ARGUMENTRENAMED."</b></a></div>";
}

/**
 *
 * Impedisce di scrivere nei topics contenuti nell'argomento
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1

 */
function lock_argument($root,$group,$argument){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (file_exists("$root/$group/$argument/lock")){
		echo "<br/><div align=\"center\">"._ARGALREADYLOCKED."!</div>";
	}
	else {
		if (touch("$root/$group/$argument/lock"))
			echo "<br/><div align=\"center\"><b>"._ARGUMENTLOCKED."!</b><br><br>
			<a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a>
			</div>";
	}

	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."\" >";
}

/**
 *
 * Sblocca i topic contenuti nell'argomento
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function unlock_argument($root,$group,$argument){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is invalid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is invalid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (file_exists("$root/$group/$argument/lock")){
		if (unlink("$root/$group/$argument/lock"))
			echo "<br/><div align=\"center\"><b>"._ARGUMENTUNLOCKED."!</b>
			<br><br><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a>
			</div>";
	}
	else {
		echo "<br/><div align=\"center\">"._ARGALREADYUNLOCKED."!</div>";
	}

	echo "<meta http-equiv=\"Refresh\" content=\"1; URL=index.php?mod=".rawurlencodepath($mod)."&amp;group=".rawurlencodepath($group)."\" >";
}

/**
 *
 * Funzione che mostra l'interfaccia per eliminare un argomento
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function delete_argument_interface($root,$group,$argument){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">";
	echo "<input type=\"hidden\" readonly name=\"ffgroup\" value=\"$group\" />";
	echo "<input type=\"hidden\" readonly name=\"ffargument\" value=\"$argument\" />";
	echo "<input type=\"hidden\" readonly name=\"ffaction\" value=\"deleteargument\" />";
	echo _DELETEARGUMENT1." <b>$argument</b> "._DELETEARGUMENT2." <b>$group</b>?<br/>";
	echo _DELETEARGUMENT3."<br/><br/>";
	echo "<input type=\"checkbox\" name=\"ffconfirmdelete\" /> "._DELETEARGUMENT." <b>$argument</b><br/><br/>";
	echo "<input type=\"submit\" value=\"OK\" />";
	echo "</form>";
}

/**
 * Elimina l'argomento specificato mediante la maschera.
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 */
function delete_argument($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$confirmdelete = getparam("ffconfirmdelete",PAR_POST,SAN_FLAT);
	if (!check_var($confirmdelete,"boolean"))
		ff_die("\$confirmdelete is not valid! (".strip_tags($confirmdelete).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (!$confirmdelete){
		echo "<div align=\"center\">"._DELETEARGNOCONFIRM;
		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;

	}

	if (!is_dir("$root/$group/$argument")){
		echo "<div align=\"center\"><b>"._ATTENTION.":</b> "._THEARGUMENT." <b>$root/$group/$argument</b> "._DOESNTEXISTS."!";

		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;
	}


	if (!file_exists("$root/$group/$argument/argument.php")){
		echo "<div align=\"center\"><b>"._ATTENTION.":</b> "._THEFILE." argument.php "._DOESNTEXISTS."!";

		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;
	}

	if (!unlink("$root/$group/$argument/argument.php")){
		echo "<b>"._ATTENTION.":</b> "._FILENOTDELETED." argument.php!";
	}

	if (file_exists("$root/$group/$argument/lock")){
		if (!unlink("$root/$group/$argument/lock")){
			echo "<b>"._ATTENTION.":</b> "._FILENOTDELETED." lock!";
		}
	}

	if (file_exists("$root/$group/$argument/level.php")){
		if (!unlink("$root/$group/$argument/level.php")){
			echo "<b>"._ATTENTION.":</b> "._FILENOTDELETED." level.php!";
		}
	}

	if (file_exists("$root/$group/$argument/stats.php")){
		if (!unlink("$root/$group/$argument/stats.php")){
			echo "<b>"._ATTENTION.":</b> "._FILENOTDELETED." stats.php!";
		}
	}

	if (file_exists("$root/$group/$argument/topicslist.php")){
		if (!unlink("$root/$group/$argument/topicslist.php")){
			echo "<b>"._ATTENTION.":</b> "._FILENOTDELETED." topicslist.php!";
		}
	}

	$topics = array();

	$topics = glob("$root/$group/$argument/*.ff.php");
	if (!$topics) $topics = array(); // glob may returns boolean false instead of an empty array on some systems
	if (count($topics)>0){
		foreach ($topics as $topic){
			if (!unlink($topic)){
				echo _FILENOTDELETED." <b>$topic</b><br/>";
			}
		}
	}

	//infine elimino la cartella
	if (!rmdir("$root/$group/$argument")){
		echo "<b>"._ATTENTION."! </b>"._DIRNOTDELETED.": $root/$group/$argument";
	}
	else echo "<br><div style=\"align: center;\"><b>"._ARGUMENTDELETED."!</b></div><br><br><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a></div>";
}

/**
 * Funzione che esegue un backup dei dati del forum
 *
 * @param string $root la root del forum
 * @author Aldo Boccacci
 * @since 0.1
 */
function backup_forum_structure($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	include_once("forum/include/archive.php");
	include_once("include/filesystem/DeepDir.php");

	$dir = new DeepDir();
	$dir->setDir($root);
	$dir->load();

	if (file_exists(get_fn_dir("var")."/backup_forum_".date("d-m-Y").".zip"))
		unlink(get_fn_dir("var")."/backup_forum_".date("d-m-Y").".zip");

	$backup = new zip_file(get_fn_dir("var")."/backup_forum_".date("d-m-Y").".zip");
	$backup->set_options(array('inmemory'=>"0",'overwrite'=>1,'prepend','level'=>1));

	//aggiungo i singoli files
	foreach( $dir->files as $n => $pathToFile ){
		$backup->add_files($pathToFile);
	}
	$backup->create_archive();
	header("Location: ".get_fn_dir("var")."/backup_forum_".date("d-m-Y").".zip");

}

/**
 * Maschera per spostare l'argomento prescelto.
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @param string argument l'argomento di riferimento
 * @param string $topic il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 *
 */
function move_argument_interface($root,$group,$argument){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);
	$argument = getparam($argument,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">";
	echo "<input type=\"hidden\" readonly name=\"ffgroup\" value=\"$group\" />";
	echo "<input type=\"hidden\" readonly name=\"ffargument\" value=\"$argument\" />";
	echo "<input type=\"hidden\" readonly name=\"ffaction\" value=\"moveargument\" />";

	echo "<br/>"._ARGUMENTMOVE.":<br/><br/>";

	$groups = array();
	$groups = list_forum_groups($root);
	$tmpgroup="";
	if (count($groups)>1){
		echo "<select name=\"ffnewgroup\" id=\"ffgroup\">";
		foreach ($groups as $tmpgroup){
			if ($tmpgroup==$group) continue;
			if (is_dir("$root/$tmpgroup") and is_writable("$root/$tmpgroup")){
				if (!is_dir("$root/$tmpgroup/$argument"))
					echo "<option>$tmpgroup</option>";
				else echo "<option disabled=\"disabled\">$tmpgroup</option>";
			}
			else echo "<option disabled=\"disabled\">$tmpgroup</option>";
		}
		echo "</select><br/><br/>";
	}
	else{
		echo "<b>"._NOGROUP."!</b><br/><br/>";
		echo "<br/><div align=\"center\"><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";


		return;
	}

	echo "<input type=\"submit\" value=\""._MOVE."\" />";
	echo "</form>";
}


/**
 * Sposta l'argomento specificato mediante la maschera.
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 */
function move_argument($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$newgroup = getparam("ffnewgroup",PAR_POST,SAN_FLAT);
	if (!check_path($newgroup,"","false"))
		ff_die("\$newgroup is not valid! (".strip_tags($newgroup).")",__FILE__,__LINE__);

	if (trim($newgroup)=="") {
		echo "The new group is not set!";
		return;
	}

	$argument = getparam("ffargument",PAR_POST,SAN_FLAT);
	if (!check_path($argument,"","false"))
		ff_die("\$argument is not valid! (".strip_tags($argument).")",__FILE__,__LINE__);

	$mod = _FN_MOD;


	if (!is_dir("$root/$group/$argument")){
		echo "<b>"._ATTENTION.":</b> "._THEARGUMENT." <b>$root/$group/$argument</b> ".DOESNTEXISTS."!";
		return;
	}

	if (!is_dir("$root/$newgroup/")){
		echo "<b>"._ATTENTION.":</b> "._THEGROUP." <b>$root/$newgroup</b> ".DOESNTEXISTS."!";
		return;
	}

	if (!is_writable("$root/$newgroup")){
		echo "<b>"._ATTENTION.":</b> "._THEGROUP." <b>$root/$newgroup</b> "._ISNTWRITABLE."!";
		return;
	}

	if (rename("$root/$group/$argument","$root/$newgroup/$argument")){
		update_argument_stats($newgroup,$argument);
		fflogf("Moved argument $root/$group/$argument in $root/$newgroup/$argument");

		echo "<br/><div align=\"center\"><b>"._ARGUMENTMOVED."!</b></div>";
	}

	echo "<br/><br/><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a></div>";
}

/**
 * Interfaccia che permette di eliminare un gruppo
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @author Aldo Boccacci
 * @since 0.1

 */
function delete_group_interface($root,$group){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">";
	echo "<input type=\"hidden\" readonly name=\"ffgroup\" value=\"$group\" />";
	echo "<input type=\"hidden\" readonly name=\"ffaction\" value=\"deletegroup\" />";
	echo _DELETEGROUPALERT1." <b>$group</b>?<br/>";
	echo _DELETEGROUPALERT2."<br/><br/>";
	echo "<input type=\"checkbox\" name=\"ffconfirmdelete\" /> "._DELETEGROUPALERT3." <b>$group</b><br/><br/>";
	echo "<input type=\"submit\" value=\"OK\" />";
	echo "</form>";
}


/**
 * Elimina l'argomento specificato mediante la maschera.
 *
 * @param string $root la cartella nella quale creare il gruppo
 * @since 0.1
 * @author Aldo Boccacci
 */
function delete_group($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);


	$confirmdelete = getparam("ffconfirmdelete",PAR_POST,SAN_FLAT);
	if (!check_var($confirmdelete,"boolean"))
		ff_die("\$confirmdelete is not valid! (".strip_tags($confirmdelete).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	if (!$confirmdelete){
		echo "<div align=\"center\"><b>"._ATTENTION.":</b> "._DELETEGROUPNOCONFIRM;
		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;

	}

	if (!is_dir("$root/$group")){
		echo "<div align=\"center\"><b>"._ATTENTION.":</b> "._THEGROUP." <b>$root/$group</b> "._DOESNTEXISTS;

		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;
	}

	$contains = array();
	$contains = glob("$root/$group/*");
	if (!$contains) $contains = array(); // glob may returns boolean false instead of an empty array on some systems

	for ($count=0;$count<count($contains);$count++){
		if (is_dir($contains[$count])){
			echo "<div align=\"center\"><br/><b>"._ATTENTION.":</b> "._THEGROUP." $group "._GROUPNOTEMPTY." (<b>".basename($contains[$count])."</b>)";
			echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

			return;
		}
	}

	//il file del livello
	if (file_exists("$root/$group/level.php")){
		if (!unlink("$root/$group/level.php")){
			echo "<b>"._ATTENTION.":</b> It was impossible to delete the file level.php!";
		}
	}

	//il file del gruppo
	if (file_exists("$root/$group/group.php")){
		if (!unlink("$root/$group/group.php")){
			echo "<b>"._ATTENTION.":</b> It was impossible to delete the file group.php!";
		}
	}

	//infine elimino la cartella
	if (!rmdir("$root/$group")){
		echo "I'm not able to delete the directory: ".strip_tags("$root/$group")."!";
		fflogf("I'm not able to delete the directory: ".strip_tags("$root/$group")."!");
	}

	fflogf("Deleted group $root/$group");

	echo "<div align=\"center\"><br/><b>"._DELETEGROUPOK."</b><br/><br/><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a></div>";
}

/**
 * Interfaccia per rinominare un gruppo
 *
 * @param string $root la root del forum
 * @param string $group il gruppo di riferimento
 * @author Aldo Boccacci
 * @since 0.1
 */
function rename_group_interface($root,$group){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);

	$root = getparam($root,PAR_NULL,SAN_FLAT);
	$group = getparam($group,PAR_NULL,SAN_FLAT);

	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);


	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$mod = _FN_MOD;

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">";
	echo "<input type=\"hidden\" readonly name=\"ffgroup\" value=\"$group\" />";
	echo "<input type=\"hidden\" readonly name=\"ffaction\" value=\"renamegroup\" />";
	echo "<br/>"._RENAMEGROUP." <b>$group</b><br/><br/>";

	echo "<input type=\"text\" name=\"ffnewgroupname\" class=\"ffinput\" value=\"$group\" /><br/><br/>
	<input type=\"submit\" name=\"ffok\" value=\""._RENAMEGROUP."\" />";

	echo "</form>";
}

/**
 * Rinomina il gruppo specificato mediante la maschera.
 *
 * @param string $root la root del forum
 * @since 0.1
 * @author Aldo Boccacci
 */
function rename_group($root){
	if (!_FN_IS_ADMIN) ff_die(_NONPUOI,__FILE__,__LINE__);
	if (!check_path($root,get_forum_root(),"false"))
		ff_die("\$root is not valid! (".strip_tags($root).")",__FILE__,__LINE__);

	$group = getparam("ffgroup",PAR_POST,SAN_FLAT);
	if (!check_path($group,"","false"))
		ff_die("\$group is not valid! (".strip_tags($group).")",__FILE__,__LINE__);

	$newgroupname = getparam("ffnewgroupname",PAR_POST,SAN_FLAT);
	if (!check_path($newgroupname,"","false"))
		ff_die("\$newgroupname is not valid! (".strip_tags($newgroupname).")",__FILE__,__LINE__);

	$newgroupname = preg_replace("/ /","_",$newgroupname);

	$mod = _FN_MOD;

	if (trim($group)==trim($newgroupname)){
	echo "<div align=\"center\">"._GROUPNOCHANGE."</div>";
	}

	if (!is_dir("$root/$group"))
		echo "<b>"._ATTENTION.":</b> "._THEGROUP." <b>$root/$group</b> "._DOESNTEXISTS;

	if (is_dir("$root/$newgroupname")){
		echo "<div align=\"center\"><b>"._ATTENTION.":</b> "._THEGROUP." <b>$newgroupname</b> "._ALREADYEXISTS;

		echo "<br/><br/><a href=\"javascript:history.back()\">"._INDIETRO."</a></div>";

		return;
	}


	if (rename("$root/$group","$root/$newgroupname")){
		update_forum_stats();
		fflogf("Group $root/$group renamed in $root/$newgroupname");

		echo "<br/><div align=\"center\"><b>"._GROUPRENAMED."</b></div>";
	}

	echo "<br/><br/><div align=\"center\"><a href=\"index.php?mod=".rawurlencodepath($mod)."\"><b>"._RETURN."</b></a></div>";
}

/**
 * Funzione che sposta un topic in un altro argomento
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function move_topic(){
	if (!_FN_IS_ADMIN) ff_die("Only admins can move topics!",__FILE__,__LINE__);

	$topicpath = getparam("fftopicpath",PAR_POST,SAN_FLAT);
	if (!check_path($topicpath,get_forum_root(),"true"))
		ff_die("\$topicpath is not valid! (".strip_tags($topicpath).")",__FILE__,__LINE__);

	$destargument = getparam("ffdestargument",PAR_POST,SAN_FLAT);
	if (!check_path($destargument,"","false"))
		ff_die("\$destargument is not valid! (".strip_tags($destargument).")",__FILE__,__LINE__);

	$origmod = getparam("ffmod",PAR_POST,SAN_FLAT);
	if (!check_path($origmod,"","false"))
		ff_die("\$origmod is not valid! (".strip_tags($origmod).")",__FILE__,__LINE__);

	if (!file_exists($topicpath)){
		ff_die("<b>".ATTENTION."</b>: the topic ".strip_tags($topicpath)." doesn't exists!");
	}

	if (!is_writable($topicpath)){
		ff_die("<b>".ATTENTION."</b>: il topic ".strip_tags($topicpath)." non &egrave; scrivibile, impossibile spostarlo!");
	}

	if (!preg_match("/ff.php$/i",$topicpath)){
		ff_die("<b>".ATTENTION."</b>: the file ".strip_tags($topicpath)." is not managed by Flatforum. It isn't possible to move it.");
	}

	if (!is_dir(get_forum_root()."/$destargument/")){
		ff_die("<b>".ATTENTION."</b>: the argument ".strip_tags($destargument)." is not a valid directory! It is impossible to move it.");
	}

	if (!is_writable(get_forum_root()."/$destargument/")){
		ff_die("<b>".ATTENTION."</b>: the directory ".strip_tags($destargument)." is not writeable! Please check permissions!");
	}

	if (rename($topicpath,get_forum_root()."/$destargument/".basename($topicpath))){
		echo "<div style=\"text-align: center;\"><b>"._TOPICMOVED."!</b>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($origmod)."\"><b>"._RETURN."</b></a>";
		echo "</div>";
		update_forum_stats();
	}
	else{
		echo "<div style=\"align: center;\"><b>"._TOPICNOTMOVED."!</b>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($origmod)."\"><b>"._RETURN."</b></a></div>";
	}
}

/**
 * Interfaccia per spostare un topic
 *
 * @param string $topicpath il file contenente il topic
 * @author Aldo Boccacci
 * @since 0.1
 */
function move_topic_interface($topicpath){
	if (!_FN_IS_ADMIN) ff_die("Only admins can move topics!",__FILE__,__LINE__);

	$topicpath = getparam($topicpath,PAR_NULL,SAN_FLAT);
	if (!check_path($topicpath,get_forum_root(),"true"))
		ff_die("\$topicpath is not valid! (".strip_tags($topicpath).")",__FILE__,__LINE__);

	$topictitle="";
	$topicdata=array();
	$topicdata = load_topic_properties($topicpath);

	$mod = _FN_MOD;
	$root="";
	$root=get_forum_root();
	$groups = array();
	$groups = list_forum_groups(get_forum_root());
	$group = "";
	$arguments=array();
	$argument="";
	foreach ($groups as $group){
		$tmparguments=array();
		$tmparguments=list_group_arguments(get_forum_root(),$group);
		foreach ($tmparguments as $argument){
			$arguments[]="$group/$argument";
		}
	}
// 	print_r($arguments);

	echo "<form action=\"index.php?mod=$mod\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"movetopic\" />";
	echo "<input type=\"hidden\" name=\"fftopicpath\" readonly=\"readonly\" value=\"$topicpath\" />";
	echo "<input type=\"hidden\" name=\"ffmod\" readonly=\"readonly\" value=\"$mod\" />";
	echo _CHOOSETOPICDEST." <b>".$topicdata['properties']['topictitle']."</b>:";
	echo "<br><br><select name=\"ffdestargument\">";
	$argument="";
	foreach ($arguments as $argument){
		if (is_dir("$root/$argument") and is_writable("$root/$argument")){
			if (!file_exists("$root/$argument/".basename($topicpath)))
				echo "<option>$argument</option>";
		}
		else echo "<option disabled=\"disabled\">$argument</option>";
	}
	echo "</select>";
	if (count($arguments)<2)
		echo "<br><br><b>"._CREATEARGUMENTS."</b>";
	echo "<br><br><input ";
	if (count($arguments)<2) echo "disabled=\"disabled\"";
	echo "type=\"submit\" name=\"ffok\" value=\""._MOVETOPIC."\" />";
	echo "</form>";

}

/**
 * Interfaccia per eliminare il topic indicato come parametro
 *
 * @param string $topicpath Il percorso del topic da eliminare
 * @since 0.1
 * @author Aldo Boccacci
 */
function delete_topic_interface($topicpath){
	$topicpath = getparam($topicpath,PAR_NULL,SAN_FLAT);
	if (!check_path($topicpath,get_forum_root(),"true"))
		ff_die("\$topicpath is not valid! (".strip_tags($topicpath).")",__FILE__,__LINE__);

	$topicdata=array();
	$topicdata = load_topic_properties($topicpath);
// 	print_r($topicdata);
	$mod = _FN_MOD;
	echo "<div style=\"text-align: center;\">"._ASKDELETETOPIC.": <b> ".$topicdata['properties']['topictitle']."</b>?<br>";

	echo "<form action=\"index.php?mod=$mod\" method=\"POST\">";
	echo "<input type=\"hidden\" name=\"ffaction\" readonly=\"readonly\" value=\"deletetopic\" />";
	echo "<input type=\"hidden\" name=\"fftopicpath\" readonly=\"readonly\" value=\"".rawurlencodepath($topicpath)."\" />";
	echo "<input type=\"hidden\" name=\"ffmod\" readonly=\"readonly\" value=\"$mod\" />";
	echo "<br><br><input type=\"submit\" name=\"ffok\" value=\""._DELETETOPIC."\" />";
	echo "</form></div>";



}
/**
 * Funzione per eliminare un topic
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function delete_topic(){
if (!_FN_IS_ADMIN) ff_die("Only admins can delete topics!",__FILE__,__LINE__);

	$topicpath = getparam("fftopicpath",PAR_POST,SAN_FLAT);
	if (!check_path($topicpath,get_forum_root(),"true"))
		ff_die("\$topicpath is not valid! (".strip_tags($topicpath).")",__FILE__,__LINE__);

	$topicdata=array();
	$topicdata = load_topic_properties($topicpath);

	$origmod = getparam("ffmod",PAR_POST,SAN_FLAT);
	if (!check_path($origmod,"","false"))
		ff_die("\$origmod is not valid! (".strip_tags($origmod).")",__FILE__,__LINE__);

	if (!file_exists($topicpath)) ff_die("Attenzione: il topic ".strip_tags($topicpath)."non esiste!",__FILE__,__LINE__);

	if (unlink($topicpath)){

		//aggiorno le statistiche
		$tmp = dirname($topicpath);
		$argument = basename($tmp);
		$tmp = dirname($tmp);
		$group = basename($tmp);
		update_argument_stats($group,$argument);

		update_topics_list($group,$argument);

		echo "<div style=\"text-align: center;\">"._TOPICDELETED.": <b> ".$topicdata['properties']['topictitle']."</b><br>";

		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($origmod)."&group=".$group."&argument=".$argument."\"><b>"._RETURN."</b></a></div>";
	}
	else {
		echo "<div style=\"text-align: center;\"><b>"._ATTENTION."!</b>I wasn't able to delete the topic: <b> ".$topicdata['properties']['topictitle']."</b><br>";
		echo "<br/><br/><a href=\"index.php?mod=".rawurlencodepath($origmod)."\"><b>"._RETURN."</b></a></div>";
	}

}

/**
 * Mostra il pannello di controllo del forum
 *
 * @author Aldo Boccacci
 * @since 0.1
 */
function ff_control_panel(){
if (_FN_IS_ADMIN){

global $theme;
$mod = _FN_MOD;

if (!file_exists(get_forum_root()."/ffmotd.php"))
	fnwrite(get_forum_root()."/ffmotd.php"," ","w",array());

if (!file_exists(get_forum_root()."/rules.php"))
	fnwrite(get_forum_root()."/rules.php"," ","w",array());
?>
<h3><?php echo _FFCONTROLPANEL; ?>: </h3>
<hr>
<div style="text-align : center;">

<br>
<fieldset><legend><?php echo _FORUMMANAGEMENT; ?></legend>
<form action="index.php?mod=<?php echo $mod; ?>" method="post">
<input type="hidden" name="ffaction" value="newgroup" />
<input type="submit" value="<?php echo _NEWGROUP; ?>"<?php
if (!is_writable(get_forum_root())) echo "disabled=\"disabled\"";
?> />
</form>
<?php
if (!is_writable(get_forum_root())) echo "La cartella <b>".get_forum_root()."</b> non &egrave; scrivible: controllare i permessi.<br/><br/>";
?>
<form action="index.php?mod=<?php echo $mod; ?>" method="post">
<input type="hidden" name="ffaction" value="newargument" />
<input type="submit" value="<?php echo _NEWARGUMENT; ?>" <?php

if (count(list_forum_groups(get_forum_root()))==0) echo "disabled=\"disabled\"";
?> />
<?php if (count(list_forum_groups(get_forum_root()))==0) echo "<br><i>"._CREATEGROUPS."</i>";?>
</form>

<form action="index.php?mod=<?php echo $mod; ?>" method="post">
<input type="hidden" name="ffaction" value="backupforum" />
<input type="submit" value="<?php echo _FORUMBACKUP; ?>" />
</form>
</fieldset>

<fieldset>
<legend><?php echo _CONFIG; ?></legend>
<form action="index.php" method="get">
	<input type="hidden" name="mod" value='modcont' />
	<input type='hidden' name='from' value='<?php echo str_replace("&","&amp;",getparam("REQUEST_URI",PAR_SERVER,SAN_FLAT));?>' />
	<input type='hidden' name='file' value='<?php echo get_forum_root();?>/ffmotd.php' />
	<input type='submit' value="<?php echo _EDITFORUMMOTD; ?>" />
</form>

<form action='index.php' method='get'>
	<input type='hidden' name='mod' value='modcont' />
	<input type='hidden' name='from' value='<?php echo str_replace("&","&amp;",getparam("REQUEST_URI",PAR_SERVER,SAN_FLAT));?>' />
	<input type='hidden' name='file' value='forum/help.php' />
	<input type='submit' value="<?php echo _EDITFORUMHELP; ?>" />
</form>


<form action='index.php' method='get'>
	<input type='hidden' name='mod' value='modcont' />
	<input type='hidden' name='from' value='<?php echo str_replace("&","&amp;",getparam("REQUEST_URI",PAR_SERVER,SAN_FLAT));?>' />
	<input type='hidden' name='file' value='<?php echo get_forum_root();?>rules.php' />
	<input type='submit' value="<?php echo _EDITFORUMRULES; ?>" />
</form>

</fieldset>

<fieldset>
<legend>Logs</legend>
<form action='index.php' method='get'>
	<input type='hidden' name='mod' value='modcont' />
	<input type='hidden' name='from' value='<?php echo str_replace("&","&amp;",getparam("REQUEST_URI",PAR_SERVER,SAN_FLAT));?>' />
	<input type='hidden' name='file' value='<?php echo get_fn_dir("var")?>/log/forumlog.php' />
	<input type='submit' value="<?php echo _VIEWFFLOG; ?>" />
</form>
<form action='index.php' method='get'>
	<input type='hidden' name='mod' value='modcont' />
	<input type='hidden' name='from' value='<?php echo str_replace("&","&amp;",getparam("REQUEST_URI",PAR_SERVER,SAN_FLAT));?>' />
	<input type='hidden' name='file' value='<?php echo get_fn_dir("var")?>/log/forumlogerror.php' />
	<input type='submit' value="<?php echo _VIEWFFERRORLOG; ?>" />
</form>

</fieldset>

<br><br>
		<strong><a href="javascript:history.back()"><?php echo _INDIETRO; ?></a></strong>

</div>
<br/>
<?php
}//fine controllo admin

}

?>
