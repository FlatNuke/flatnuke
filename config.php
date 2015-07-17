<?php

/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003-2004 by Simone Vellei                             */
/* http://flatnuke.sourceforge.net                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/


# IMPOSTAZIONI PER FLATNUKE

# Titolo del sito
$sitename = "FlatNuke Home page";
# Descrizione del sito
$sitedescription = "This is my personal website powered by Flatnuke technology";
# Keywords del sito
$keywords = "News, news, New, new, Technology, technology, Headlines, headlines, Nuke, nuke, FlatNuke, flatnuke, Geek, geek, Geeks, geeks, Hacker, hacker, Hackers, hackers, Linux, linux, Software, software, Download, download, Downloads, downloads, Free, FREE, free, Community, community, Forum, forum, Forums, forums, Bulletin, bulletin, Board, board, Boards, boards, PHP, php, Survey, survey, Kernel, kernel, Comment, comment, Comments, comments, Portal, portal, ODP, odp, Open, open, Open Source, OpenSource, Opensource, opensource, open source, Free Software, FreeSoftware, Freesoftware, free software, GNU, gnu, GPL, gpl, License, license, Unix, UNIX, *nix, unix, MySQL, mysql, SQL, sql, Database, DataBase, Blogs, blogs, Blog, blog, database, Mandrake, mandrake, Red Hat, RedHat, red hat, Slackware, slackware, SUSE, SuSE, suse, Debian, debian, Gnome, GNOME, gnome, Kde, KDE, kde, Enlightenment, enlightenment, Intercative, interactive, Programming, programming, Extreme, extreme, Game, game, Games, games, Web Site, web site, Weblog, WebLog, weblog, Guru, GURU, guru";
# Tema preferito
$theme = "fnluke";
# News per pagina
$newspp="9";
# Nome Admin
$admin="Me";
# Admin mail
$admin_mail = "";
# lingua (it, en, es, fr, ..)
$lang="it";
# è possibile registrarsi (1=VERO,0=FALSO,2=REGISTRAZIONE CON CONVALIDA VIA MAIL)
$reguser=1;
# un utente non registrato può segnalare le news (1=VERO,0=FALSO)
$guestnews=1;
# un utente non registrato può inserire i commenti (1=VERO,0=FALSO)
$guestcomment=1;
# Memorizzare i cookies dell'utente autenticato (1=VERO,0=FALSO)
$remember_login=0;
# Utile per impostare l'ora italiana su web server USA (1,5 = 1h 30m)
$fuso_orario=0;
# Sito in manutenzione (1=VERO,0=FLASO)
$maintenance=0;
# Home page section
$home_section="";
#news editor (ckeditor,fckeditor,bbcode)
$news_editor = "bbcode";

#Indica un elenco di utenti che potranno agire come moderatori delle news
# I nomi degli utenti abilitati devono essere separati da una virgola (,).
# Es: $news_moderators = "utente,utente1,utente2";
$news_moderators = "";

# IMPOSTAZIONI PER IL FORUM

# numero di topic per pagina
$topicperpage=10;
# numero di post per pagina
$postperpage=5;
# numero di membri per pagina
$memberperpage=15;

#Indica un elenco di utenti che potranno agire come moderatori del forum
# I nomi degli utenti abilitati devono essere separati da una virgola (,).
# Es: $forum_moderators = "utente,utente1,utente2";
$forum_moderators = "";

?>
