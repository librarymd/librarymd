<?php
global $lang;

$lang['comment_add_new'] = 'Comentariu nou';
$lang['comment_send_comment'] = 'Adaugă comentariul (Ctrl+Enter)';
$lang['comment_new_warring'] = 'Citiţi <a href="./rules.php#comments">regulile</a> înainte de a posta, căci riscaţi de a fi banat!';
$lang['comment_more_smiles'] = 'Mai multe smile-uri';
$lang['comment_stamps'] = 'Ştampile';
$lang['comment_smilies'] = 'Zîmbete';
$lang['comment_sayd_thanks'] = 'Au Mulţumit';
$lang['comment_thanks'] = 'Mulţumesc!';

$lang['watch_on'] = 'Urmăreşte comentariile la acest torrent';
$lang['watch_off'] = 'Comentariile se urmăresc';

$lang['details_rate'] = 'Nota';
$lang['details_rate_0'] = 'apreciază';
$lang['details_rate_submit'] = 'Părerea mea';
$lang['details_rate_votes'] = 'voturi';
$lang['details_rate_vote'] = 'vot';
$lang['details_rate_onlyAfterSnatch'] = 'Veţi putea aprecia doar după ce copiaţi torrentul, în submeniul <a href="./to_appreciate.php">Apreciază</a>';
$lang['details_comments_hidden'] = 'La decizia administraţiei comentariile au fost ascunse';
$lang['details_comments_locked'] = 'Scrierea comentariilor la acest torrent a fost blocată de administraţie';
/*
$lang_input['yes'] = 'Da';
$lang_input['descr'] = 'Despre %category%';
$lang_input['language'] = 'Limba';
$lang_input['year'] = 'An';
$lang_input['sound'] = 'Sunet';
$lang_input['video'] = 'Video';
$lang_input['sample'] = 'Sample';
$lang_input['runtime'] = 'Durata';
$lang_input['serial'] = 'Serial';
$lang_input['movie_translated_name'] = 'Denumire';
$lang_input['movie_original_name'] = 'Denumirea originala';
$lang_input['movie_genres_list_ids'] = 'Gen';
$lang_input['season'] = 'Sezon';
$lang_input['episode'] = 'Serie';
$lang_input['subtitles'] = 'Subtitre';
$lang_input['movie_quality'] = 'Calitate';
$lang_input['directed'] = 'Regizor';
$lang_input['name'] = 'Denumire';
$lang_input['bookz_author'] = 'Autor';
$lang_input['artist'] = 'Artist';
$lang_input['title'] = 'Denumire';
$lang_input['actors'] = 'Actori';
$lang_input['writing_by'] = 'Autorul scenariului';
$lang_input['bookz_genre'] = 'Gen';
$lang_input['appz_version'] = 'Versiune';
$lang_input['appz_license'] = 'Licen&#355;a';
$lang_input['appz_os'] = 'Sistem de operare';
$lang_input['music_genre'] = 'Gen';
$lang_input['music_format'] = 'Format';
$lang_input['music_bitrate'] = 'Bitrate';
$lang_input['games_recomanded_hardware'] = 'Hardware recomandat';
$lang_input['games_genre'] = 'Gen';
$lang_input['games_minimum_hardware'] = 'Hardware minim';
//Separators
$lang_input['File_info'] = 'Fişier';
$lang_input['Hardware'] = 'Cerinţe';
*/

global $lang_input_all_names, $lang_input_all_values, $lang_category;

//> pentru a preveni replaceurile false, <b>descr</b> replace >descr cu >O %cat..
$lang_input_all_names = array('{Yes}','>File_info<','>Hardware<','>descr', '>language', '>year', '>sound', '>video', '>sample', '>runtime', '>serial', '>movie_translated_name', '>movie_original_name', '>movie_genres_list_ids', '>season', '>episode', '>subtitles', '>movie_quality', '>directed', '>name', '>bookz_author', '>artist', '>title', '>actors', '>writing_by', '>bookz_genre', '>appz_version', '>appz_license', '>appz_os', '>music_genre', '>music_format', '>music_bitrate', '>games_recomanded_hardware', '>games_genre', '>games_minimum_hardware', '>sport_genre', '>anime_genre','>dvd_genre','>country', '>hdtv_quality');
$lang_input_all_values = array('Da','>Fişier<','>Cerinţe<','>Despre %category%', '>Limba', '>An', '>Sunet', '>Video', '>Sample', '>Durata', '>Serial', '>Denumire', '>Denumirea originală', '>Gen', '>Sezon', '>Episod', '>Subtitrare', '>Calitate', '>Regizor', '>Denumire', '>Autor', '>Artist', '>Denumire', '>Actori', '>Autorul scenariului', '>Gen', '>Versiune', '>Licen&#355;a', '>Sistem de operare', '>Gen', '>Format', '>Bitrate', '>Hardware recomandat', '>Gen', '>Hardware minim', '>Gen', '>Tip', '>Tip','>Țara', '>Calitatea HDTV');

$translation_type_tr = array(
	'1'=>'Original (fără traducere)',
    '5'=>'Amator (o voce)',
    '7'=>'Amator (două voci)',
	'4'=>'Amator (mai multe voci)',
    '8'=>'Profesionistă (o voce)',
    '9'=>'Profesionistă (două voci)',
    '3'=>'Profesionistă (mai multe voci, voice-over)',
	'2'=>'Profesionistă (dublată)',
	'6'=>'Traducere sincronizată'
);

$lang_category[1] = 'film';
$lang_category[2] = 'muzică';
$lang_category[3] = 'program';
$lang_category[4] = 'joc';
$lang_category[5] = 'emisiune';
$lang_category[6] = 'film';
$lang_category[7] = 'conţinut';
$lang_category[8] = 'carte';
$lang_category[9] = 'video clip';
$lang_category[10] = 'film';
$lang_category[11] = 'film';
$lang_category[12] = 'film';
$lang_category[13] = 'film';
$lang_category[14] = 'audiobook';
$lang_category[15] = 'lecţii';
$lang_category[16] = 'fotografii';
$lang_category[17] = 'film';
$lang_category[18] = 'film';

$lang['uploaded_redownload'] = 'Pentru a putea seeda, trebuie să recopiaţi torrentul (autodownload)</a>';

$lang['neverificat'] = 'Neverificat';
$lang['se_verifica'] = 'Se verifică';
$lang['inchis'] = 'Închis';
$lang['controlat'] = 'Controlat';
$lang['necomplet'] = 'Descriere incompletă';
$lang['parital_necomplet'] = 'Descriere parțial incompletă';
$lang['dublare'] = 'Dublare';

$lang['comment_rules'] = '
<div class="generic_box_default">
  Scriind un mesaj, te rog să te asiguri că corespunde <b>etichetei de comunicare</b>.
<br/>
<ul>

<li>
  <b>Vorbește cu alții așa cum ți-ai fi dorit să se vorbească cu tine.</b>
</li>

<li>
  <b>Nu agresa și nu insulta pe nimeni.</b> Dacă nu ești deacord cu cineva din comentarii, incearcă să-ți imaginezi ce i-ar face pe alții să gandească așa cum gandesc. După asta exprimăți părerea într-un mod în care nu insulți și nu ataci pe nimeni.
</li>

<li>
  <b>Nu agresa și nu insulta uploaderul.</b> Dacă ai de făcut vreo remarcă la adresa torentului, ține minte că el a fost incărcat voluntar de cineva. Iar dacă există vreo eroare. Cel mai probabil ea nu a fost făcută intenționat. De asta orice remarcă, exprim-o într-un mod incat să nu superi uploaderul.
</li>

<li>
  <b>Semnalează mesajele care incalcă eticheta de comunicare utilzand butonul Raportează.</b> Moderatorii vor interveni cat de curand posibil. În caz că cineva insultă, este intuil să încerci să demonstrezi cuiva ceva insultand mai mult.
</li>

<li>
  <b>Muțumește înainte să critici.</b>
  Descarci informatia gratuit, este important să-i muțumești pe cei care îți oferă această opțiune.<br/>
  Un model de comunicare cu uploaderul, cel mai bine trimis in privat ar fi următor:<br/>
  "Mulțumesc pentru că ai incărcat torentul [link]. Am observat că lispeste x in descriere. Ai putea să o adaugi te rog ? Muțumesc anticipat."
</li>

<li>
  <b>Dacă calitatea este sub așteptările tale.</b>
  Dacă calitatea este clar indicată în descriere, atunci nu poți avea nici o pretenție.
  Autorul torentului cel mai probabil a descărcat torentul de pe alt tracker,
  deci el a reincarcat ce a găsit și pentru asta trebuie să-i fim recunoscători. Căci s-ar fi putut ca nici acestă versiune să nu existe pe acest tracker.<br/>
  <b>Caută calitatea mai bună dacă cineva a incărcat-o sau ești bun venit s-o încarci tu singur.</b>
</li>

<li>
  <b>Te rugăm să aderi la aceleași standarte de comportare in spațiul online la care aderi și în viața reala.</b>
</li>
<li>
  <b>Ține minte că vorbești cu alți oameni.</b> Când comunici online, totul ce vezi este un ecran de computer. Scriind, poți să te întrebi "I-aș fi spus acestei persoane în față același lucru ?" sau "S-ar fi supărat un prieten dacă i-aș fi răspuns așa ?".<br/>
</li>

<li>
  <b>Utilizează sfaturile de comunicare din cartea "How to Win Friends and Influence People" de Dale Carnegie.</b>
  Nimeni nu iubește să fie criticat sau să i se zică că n-a facut ceva corect. Este important să vă exprimați părerea într-un mod în care nu face pe nimeni să se simte rău. <b><a href="https://en.wikipedia.org/wiki/How_to_Win_Friends_and_Influence_People#Fundamental_Techniques_in_Handling_People">Citeste aici rezumatul cărții</a></b>. Posibil să-ți schimbe viața ta și apropiaților tăi!
</li>

</ul>

</div>';
?>
