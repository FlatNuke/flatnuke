<?php

//User interface
define("_FDNAME","Nome");
define("_FDSIZE","Dimensione");
define("_FDDATE","Data");
define("_FDVERSION", "Versione");
define("_FDDESC","Descrizione");
define("_FDHITS","Downloads");
define("_FDDOWNLOAD","DOWNLOAD!");
define("_FDSTAT","Statistiche di Download");
define("_FDSTATTITLE","Vai alla sezione delle statistiche di download");
define("_FDDOWNLOADFILE","Scarica il file: ");
define("_FDARCHIVEDIR","Cartella di archivio");
/*define("_GOTOSECTION","Vai alla sezione");*/
define("_FDUPLOADER","Caricato da");
define("_FDUPLOADERTITLE","Visualizza il profilo dell&#8217;utente");
define("_FDSUMMARY","Sommario");
define("_FDSUBDIRS","Sottosezioni");
define("_FDRATING","Voto");
define("_FDRATEFILE","Vota questo file");
define("_FDALREADYRATED","Hai gi&agrave; votato questo file!");
define("_FDVOTES","voti");
define("_FDTOVOTEAFILE","per votare un file");
define("_ENABLEVOTE","Attiva voto");
//FORMATTAZIONE DATA
define("_FDDATEFORMAT","d/m/Y H:i:s");

//gpg sign
define("_FDGPGSIGN","firma gpg");
define("_FDGPGSIGNTITLE","Scarica la firma gpg del file: ");
define("_FDDELETESIG","Elimina firma");

//screenshot
define("_FDDELETESCREEN","Elimina screenshot");
define("_FDSCREENSHOT","Screenshot del file: ");

define("_FDBACK","Indietro");

//Admin
define("_FDARCHIVE","Archivia");
define("_FDRESTORE","Ripristina");
define("_FDEDIT","Modifica");
define("_FDDELETE","Elimina");
define("_FDHIDE","Nascondi");
define("_FDSHOW","Mostra");
define("_FDRENAME","Rinomina");
define("_FDPUBLISH","Pubblica");
define("_FDADDFILE","Aggiungi un nuovo file");

define("_FDARCHIVETITLE","Archivia il file: ");
define("_FDRESTORETITLE","Ripristina il file: ");
define("_FDEDITTITLE","Modifica la descrizione del file: ");
define("_FDDELETETITLE","Elimina il file: ");
define("_FDHIDETITLE","Nascondi il file: ");
define("_FDSHOWTITLE","Rendi nuovamente visibile il file: ");
define("_FDRENAMETITLE","Rinomina il file: ");
define("_FDADDFILETITLE","Aggiungi un file in questa sezione.");
define("_FADMINPANEL","Pannello di amministrazione");
define("_FDALLOWUSERUPLOAD","Permetti agli utenti di proporre file in questa sezione");
define("_FDDONOTALLOWUSERUPLOAD","Non permettere agli utenti di proporre file in questa sezione");
define("_FDUSERUPLOADPERMSREMOVED","Gli utenti non potranno pi&ugrave; proporre file nella sezione");
define("_FDUSERUPLOADPERMSADDED","Gli utenti potranno proporre file nella sezione");

//Edit/Add file
define("_FDRETURN","Ritorna");
define("_FDEDITDESC","Modifica la descrizione del file: ");
define("_FDEDITHEADER","In questa pagina potrai modificare le note esplicative allegate al file gestito con FlatDownloadPlus. Tali informazioni saranno memorizzate nel file:");
define("_FDEDITFOOTER","Volendo puoi anche modificare il file messo in download. <br>Non &egrave; obbligatorio che il nuovo file abbia lo stesso nome del vecchio. Se il nome del nuovo file sar&agrave; diverso dal vecchio, FlatDownloadPlus si occuper&agrave; di modificare il riferimento al corrispondente file \".description\"<br>Il vecchio file sar&agrave; eliminato.");
define("_FDEDITSAVE", "Salva modifiche");
define("_FDEDITDONE", "Ho applicato le modifiche");
define("_FDSHOWINBLOCKS","mostra il file nei blocchi di statistica");
define("_FDBLOCKS","Blocchi");
define("_FDUSERLABEL","Etichetta");
define("_FDUSERVALUE","Valore");
define("_FDUSERBOTH","Devono essere settati entrambi");
define("_FDBASIC","Base");
define("_FDADVANCED","Avanzate");

//Rename File
define("_FDRENAMECHOOSE","Scegli un nuovo nome:");
define("_FDRENAMEEXTLIMIT","Per motivi di sicurezza non puoi modificare l&#8217;estensione del file.");
define("_FDRENAMEFILE","Il file ");
define("_FDRENAMENOFD","non &egrave; gestito da FD+.");
// define("_FDRENAME","");
define("_FDRENAMEOK","File rinominato con successo");
define("_FDRENAMENOTCHANGED","Il nuovo nome &egrave; uguale al precedente.");
define("_FDRENAMEEXISTS1","Esiste gi&agrave; un file chiamato");
define("_FDRENAMEEXISTS2","nella cartella");
define("_FDRENAMECHANGENAME","Scegliere un altro nome.");
define("_FDRENAMEALERT","<b>Attenzione!</b> ho avuto problemi a rinominare il file ");

//Delete file
define("_FDDELSURE","Sei sicuro di voler eliminare il file: ");
define("_FDDEL", "Elimina");
define("_FDCANC", "Annulla");
define("_FDDELOK","ho eliminato correttamente: ");
define("_FDAND","e");

//Archive file
define("_FDARCHIVEOK","il file &egrave; stato archiviato con successo.");
define("_FDARCHIVEGO","vai alla cartella di archivio.");
define("_FDRESTOREOK","il file &egrave; stato ripristinato con successo.");
define("_FDARCHIVERETURN","ritorna alla sezione di partenza");
define("_GOTOARCHIVEDIR","Vai alla cartella di archivio");

//Add file
define("_FDADDWHERE","Aggiungi un nuovo file nella cartella: ");
define("_FDADDHEADER","In questa pagina potrai mettere in download un nuovo file, nonch&egrave; inserire tutte le informazioni descrittive.");
define("_FDADDFOOTER","Qui sotto potrai selezionare il file da mettere in download.");
define("_NOTVALIDEXT","L&#8217;estensione del file che hai cercato di caricare sul server non &egrave; tra quelle supportate da FD+. Modifica la variabile &#036;extensions all&#8217;inizio del file \"fd+.php\"");

//upload file
define("_FDTOOBIG","<b>Attenzione!</b><br>Il file &egrave; troppo grande e non sar&agrave; salvato sul server.");
define("_FDUPLOADOK","file caricato con successo");
define("_FDUPLOADEXISTS","<b>Attenzione!</b> Esiste gi&agrave; un file con lo stesso nome nella cartella prescelta. <br>Prima di ripetere l&#8217;operazione eliminatelo.<br><br>Se invece era vostra intenzione modificare il file messo in download, usate la funzione <b>modifica</b>.");
define("_FDFILENOTSELECT","Devi selezionare un file!");

//save file
define("_FDSAVESIZE","<b>Attenzione!</b> il file misura ");

//move file
define("_FDMOVE","Sposta");
define("_FDMOVEFILE","Sposta il file ");
define("_FDMOVEFILESECTION"," nella sezione");
define("_FDMOVELINK","(clicca sul link per spostare)");
define("_FDMOVECONFIRM","Spostare il file ");
define("_FDMOVEFAIL","Non sono riuscito a spostare il file ");
define("_FDMOVESUCCESS","spostato con successo nella sezione");
define("_FDMOVETITLE","Sposta il file: ");
define("_FDDIR","La cartella");
define("_FDNOTWRITE","non &egrave; scrivibile!");


//creazione cartelle
define("_FDCREATESECTDOWNLOAD","Crea sezione di Download");
define("_FDCREATESECT","Crea sezione");
define("_FDCREATESECTOK","creata con successo!");
define("_FDCREATEONLYDIR","sono riuscito a creare la cartella ma non a creare il file section.php");
define("_FDCREATEDIRERROR","<b>Attenzione!</b>: non sono riuscito a creare la directory: ");
define("_FDCHECKPERM","Verificare i permessi.");
define("_FDHIDDENSECT","sezione nascosta");
define("_FDCHOOSESECTNAME","Scegli il nome della sezione:");
define("_FDCREATESUBSECT","Crea una nuova sezione all&#8217;interno di: ");


//Alert
define("_FDDESCTOOLONG","<b>Attenzione!</b> la stringa contenente la descrizione &egrave; troppo lunga!");
define("_FDNAMETOOLONG","<b>Attenzione!</b> la stringa contenente la descrizione &egrave; troppo lunga!");
define("_FDREADONLYDIR","<b>Attenzione:</b> non &egrave; possibile scrivere nella cartella. Verificare i permessi.");
define("_FDERROR","<B>Errore!</B>");

//error
define("_FDNONPUOI","<b>Non lo puoi fare!</b> (Errore di sicurezza.)<br>");
define("_FDERROR1","Il file inviato eccede le dimensioni specificate nel parametro <b>upload_max_filesize</b> di php.ini.");
define("_FDERROR2","Il file inviato eccede le dimensioni specificate nel parametro <b>MAX_FILE_SIZE</b> del form.");
define("_FDERROR3","Upload eseguito parzialmente.");
define("_FDERROR4","Nessun file &egrave; stato inviato.");
define("_FDERROR6","Cartella temporanea mancante.");
define("_FDERROR7","Fallita la scrittura su disco.");

//DOWNLOAD STATS
define("_FDSECT","Sezione");

//TOP DOWNLOAD
define("_FDTOPSTAT","Statistiche");
define("_FDTOPSTATS", "Statistiche&nbsp;Download");

//remote file/url
define("_FDADDURL","Aggiungi file remoto");
define("_FDCHOOSEURL","Specifica l&#8217;url del file da mettere in download:");

//FDuser
define("_FDWAITFORADMIN","Il file non sar&agrave; immediatamente visibile. Attendere l&#8217;approvazione dell&#8217;amministratore del sito.");
define("_FDLIMIT","Raggiunto il limite massimo di file proponibili.<br> Attendi che l&#8217;amministratore pubblichi i file in attesa di approvazione.");
define("_FDPUBLISHFILES","Pubblica i file");
define("_FDPROPOSE","Proponi un file");
define("_FDWAITINGFILES","file in attesa di validazione");
?>