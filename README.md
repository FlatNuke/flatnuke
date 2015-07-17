
FLATNUKE
--------

FlatNuke è un CMS (Content Management System) che non fa uso di DBMS,
appoggiandosi esclusivamente a file di testo (da qui il nome).


INTRODUZIONE
------------

FlatNuke è stato progettato per esigenze personali, mi occorreva,
infatti, un template per sviluppare siti che non richiedesse un DBMS.
Ho iniziato a svilupparlo seguendo le principali caratteristiche degli altri CMS
in circolazione, e sono riuscito a fornire le seguenti caratteristiche:


	- Supporto per le sezioni
	- Supporto per i blocchi
	- Supporto per le news
	- Supporto per i commenti
	- Lettura delle headlines esterne
	- Esportazione delle news in RSS
	- Supporto per i temi
	- Gestore di Download
	- Gestore di galleria immagini
	- Gestore dei sondaggi
	- Gestore delle statistiche
	- Forum


BLOCCHI
-------
I blocchi sono dei contenitori posizionati a destra e a sinistra della
pagina. All'interno possono contenere link, testo, immagini, ecc. FlatNuke
gestisce i blocchi in modo molto semplice: sarà sufficiente creare un file in
un'apposita directory (blocks). Tale file può contenere codice PHP e/o
HTML. Tutto verrà caricato automaticamente!
Facciamo un esempio:
Voglio inserire un blocco a sinistra con titolo Prova e con contenuto

Ciao Mondo

vado in blocks/sx
e creo un file Prova.php all'interno scriverò Ciao Mondo.
Ora basterà ricaricare la pagina e il blocco apparirà!
Per semplificare il lavoro ho creato dei blocchi di esempio.
Il blocco Menu è fisso e contiene le sezioni (vedi prossimo
capitolo).
Per ordinare i blocchi all'interno della colonna rinominate il file
con un prefisso "xx_" dove "xx" è un numero che identifica
la posizione del blocco. Ad esempio: "01_Admin.php".
Per impedire l'inclusione di un blocco rinomina il file con il prefisso "none_"
(Es. none_01_Calendario.php)


SEZIONI
-------

Le sezioni servono per contenere gli argomenti.  L'organizzazione gerarchica
delle sezioni è data dalla struttura gerarchica delle directory, FlatNuke
provvederà ad organizzarle automaticamente. Ogni sezione viene inserita nella
directory principale sections.

Facciamo un esempio:
Voglio una sezione di nome Felini.  Vado in sections/ e creo la directory
Felini, per inserire l'intestazione di una sezione basterà creare un file
section.php all'interno della nuova directory. Se voglio creare una
sottosezione Tigri creo all'interno di sections/Felini/ una directory Tigri e
così via.  L'intestazione di una sezione che non ha sottosezioni è il corpo
dell'argomento.

Tutte le sezioni vengono automaticamente inserite nel menu, esiste tuttavia un
metodo per impedirne l'inclusione. Sarà infatti sufficiente apporre il prefisso
none_ al nome della directory/sezione. Es. sections/none_Rapaci non sarà
inserita nel menu.
Come nei blocchi, anche nelle sezioni esiste la regola per ordinare le voci
(all'interno del menu), sarà, infatti, sufficiente chiamare la directory con il
prefisso "xx_" dove "xx" à un numero che identifica la posizione della sezione.
La stessa procedura vale anche per le sottosezioni.

Per vedere un esempio pratico di sezione si guardi sections/none_Prova.

E' possibile personalizzare l'icona di una sezione/sottosezione, distinguendola
da quella utilizzata di default dal tema: è sufficiente copiare un'immagine
chiamata section.png nella sezione/sottosezione scelta.


NEWS
----

FlatNuke supporta l'inserimento di news nella home page. Le news sono divise in
una intestazione (visibile direttamente in home) e un corpo che è leggibile per
interno grazie al link Leggi tutto posto al di sotto della notizia.
E' previsto, inoltre, l'inserimento di commenti alle news.


HEADLINES
---------

FlatNuke supporta l'esportazione delle news nel formato
RDF/RSS. Il link al file RSS in fondo pagina contiene le news di
questo sito.


DOWNLOAD
--------

Per utilizzare il download manager è sufficiente creare una directory all'interno
di /sections (es. /sections/nome-directory-di-download), creare al suo interno un
file vuoto chiamato "download", e usare la comoda interfaccia web per personalizzare
la propria sezione come si vuole.
Per personalizzare la propria sezione download con una descrizione, è sufficiente
creare all'interno della cartella un file section.php che conterrà il testo da
visualizzare.


GALLERIA IMMAGINI
-----------------

Permette di creare automaticamente una galleria di immagini. Il metodo è semplicissimo:
creare una directory all'interno di /sections (es. /sections/nome-directory-con-immagini),
creare al suo interno un file vuoto chiamato "gallery", ed infine copiarci tutte le
immagini che si vuole visualizzare.
Per personalizzare la propria galleria di immagini con una descrizione, è sufficiente
creare all'interno della cartella un file section.php che conterrà il testo da
visualizzare.


LIVELLI
-------

Ogni utente registrato ha un livello (in partenza 0) che definisce i suoi
permessi all'interno del portale. Ogni sezione può avere un livello (memorizzato
nel file "sections/miasezione/level.php") compreso tra 0 e 10. Se il livello è
-1 (cioè il file level.php non esiste) vuol dire che la sezione è visibile anche
ad utenti non registrati (default), 0 vuol dire che la sezione è visibile solo
agli utenti registrati (visto che ogni utente registrato ha minimo 0 di
livello). Se impostiamo il livello a 5 vuol dire che la sezione sarà visibile
agli utenti con livello >= 5. Gli amministratori, che hanno livello 10, possono
cambiare online il livello di una sezione.

Attenzione! Per impedire che le sezioni siano comunque accessibili direttamente,
occorre inserire all'inizio di section.php questo codice:

--------------------------------------------------------
<?php
if (eregi("section.php",$_SERVER['PHP_SELF'])) {
	Header("Location: ../../index.php");
	die();
}
?>
--------------------------------------------------------

ACCESSKEY
---------

In Flatnuke sono presenti di default i seguenti accesskey:
h: torna alla Home del portale
0: vai all'inizio del testo della sezione (solo nelle sezioni)
1-9: leggi le prime 9 news (solo in home)

Si raccomanda di non visualizzare più di 9 news in Home Page, in modo che
ciascuna abbia un proprio accesskey dedicato per la lettura. Se si supera tale limite
si ha l'unico inconveniente di non avere più a disposizione l'accesskey dalla decima news
in poi.

Se volete inserire un access key nei link che portano alle sezioni, dovete inserire nella sezione
interessata un file chiamato accesskey.php. All'interno di questo file dovrete inserire una singola
lettera o numero che diventerà l'access key nei link che portano a quella sezione.
L'acceskey deve essere un singolo carattere alfanumerico. Non sono supportati simboli o caratteri speciali.

Di default sono presenti i seguenti access key per le sezioni:
d: Download
f: Forum
m: Mappa sito

Per modificarli è sufficiente editare il file accesskey.php presente nella sezione interessata.

SAFE MODE
---------
Dalla versione 3.0 Flatnuke supporta il safe mode del php (attivo quando è impostato safe_mode=on
in php.ini).
Quando il safe_mode è attivo l'intrprete php controlla che i file e le cartelle su cui andiamo
ad operare appartengano allo stesso utente cui appartiene lo script in esecuzione. Siccome
lo script viene caricato via ftp è necessario che anche le cartelle vengano create usando
le funzioni ftp del php.
Per abilitare il supporto al safe mode rinominare il file:
"include/redefine/FOR_SAFE_MODE_fn_mkdir.php" togliendo la parte in maiuscolo (dovrà rimanere
soltanto "fn_mkdir.php". Al suo interno inserire i dati di accesso allo spazio ftp che ospita
il sito e il percorso della cartella nella quale è installato Flatnuke.

NOINDEX
-------
Inserendo un file vuoto chiamato "noindex" in una sezione verrà inserito nell'header della pagina
il seguente metatag:
	<meta name="robots" content="noindex, nofollow">
utile per evitare che specifiche pagine del sito vengano inserite negli archivi dei
motori di ricerca.

CONTRIBUIRE
-----------

Puoi contribuire allo sviluppo di FlatNuke in diversi modi:

	- donazione libera tramite Paypal o altri canali
	- donazioni di materiale informatico (componenti Hardware, manuali)
	- segnalazione link http://flatnuke.sourceforge.net
	- invito a manifestazioni per presentare FlatNuke
	- sviluppo di codice per flatnuke (core, moduli, temi)

Se hai apprezzato FlatNuke, segnala la tua pagina sul sito ufficiale http://www.flatnuke.org/
