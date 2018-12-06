<?php
require "include/bittorrent.php";
loggedinorreturn();
function insert_tag($name, $description, $syntax, $example, $remarks='', $anchor_name='')
{
	$description = nl2br($description);
	$syntax = nl2br($syntax);
	$result = format_comment($example);
	$example = nl2br($example);
	if ($anchor_name != '') {
		echo '<a name="'.$anchor_name.'"></a>';
	}
	print("<p class=sub><b>$name</b></p>\n");
	print("<table class=main width=100% border=1 cellspacing=0 cellpadding=5>\n");
	print("<tr valign=top><td width=25%>". __('Descriere') .":</td><td>$description\n");
	print("<tr valign=top><td>". __('Sintaxă') .":</td><td><tt>$syntax</tt>\n");
	print("<tr valign=top><td>". __('Exemplu') .":</td><td><tt>$example</tt>\n");
	print("<tr valign=top><td>". __('Rezultat') .":</td><td>$result\n");
	if ($remarks != "")
		print("<tr><td>". __('Remarcă') .":</td><td>$remarks\n");
	print("</table>\n");
}

stdhead(__('Taguri'));
begin_main_frame();
begin_frame(__('Taguri'));
$test = $_POST["test"];
?>
<p><?=__('Torrents.MD suportă un număr de <i>BB coduri</i> pe care le puteţi folosi pentru a modifica modul în care sunt afişate mesajele dumneavoastră.')?></p>

<form method=post action=?>
<textarea name=test cols=100 rows=15><? print($test ? esc_html($test) : "")?></textarea>
<input type=submit value="<?=__('Testează codul dat!')?>" style='height: 23px; margin-left: 5px'>
</form>
<?php

function remove_nl($s) {
	return str_replace(array("\n","\r"),array("",""),$s);
}

if ($test != "") {
	$result = format_comment($test);
	print("<p><hr>" . $result . "<hr></p>\n");
}

$nou = " (<font color=red>". __('nou!') ."</font>)";


insert_tag(
	__('Audio'.$nou),
	__('Permite încorporarea rapidă a cântecelor'),
	__('[audio]link[/audio]'),
	'[audio]http://soundcloud.com/tmdsoundproducers/moscraciun-that-day-solo[/audio]',
	__('La moment sunt suportate doar următoarele site-uri: <br />'. '[*] http://soundcloud.com/'),
	'audio'
);


insert_tag(
	__('Video'.$nou),
	__('Permite încorporarea rapidă a videourilor'),
	__('[video]link[/video]'),
	'[video]http://www.youtube.com/watch?v=3um8Gyzne4Q[/video]',
	__('La moment sunt suportate doar următoarele site-uri: <br />'. '[*] http://www.youtube.com <br />[*] http://vimeo.com'),
	'video'
);

insert_tag(
	__('Video TED'),
	__('Permite încorporarea rapidă a videourilor de pe ted'),
	__('[video]link[/video]'),
	'[video]https://www.ted.com/talks/ken_robinson_how_to_escape_education_s_death_valley[/video]','',
	'video'
);

insert_tag(
	__('Giphy'),
	__('Permite încorporarea imaginilor animate de pe giphy'),
	__('[video]link[/video]'),
	'[video]https://giphy.com/gifs/guy-2017-epic-xUPGcl22XkTmLNCfQI[/video]','',
	'video'
);

insert_tag(
	__('Postări facebook'),
	__('Permite încorporarea rapidă a postărilor facebook'),
	__('[fb]link[/fb]'),
	'[fb]https://www.facebook.com/EurovisionSongContest/videos/10155310895808007/[/fb]','',
	'fb'
);

insert_tag(
	__('Spoiler'),
	__('Spoiler permite să ascundeți text, iar la click el apare, comod cînd doriți să ascundeți mult text sau multe imagini. Imaginile ascunse in spoiler nu vor fi incărcate pînă nu se face click (astfel se economisește banda în caz cînd utilizatorul nu va apăsa pe spoiler).'),
	__('[spoiler=careva nume]Text[/spoiler]'),
	'[spoiler=Screenshot]'. __('Aici e un text şi o imagine ascunsă') .'
	[img]http://www.torrentsmd.com/pic/torrents_logo.png[/img]
	[/spoiler]',
	__('Utilizați shift+click dacă doriți să deschideți toate nivelurile din spoiler.'),
	'spoiler'
);

insert_tag(
	__('Galerie de imagini'),
	__('Permite să creați galerie navigabilă chiar în pagină.'),
	__('[iurl=adresa spre imagine jpg/gif/png]Text[/iurl]'),	'[iurl=http://farm3.static.flickr.com/2336/2129252744_3d412f1e05_o.jpg]
	[img]http://farm3.static.flickr.com/2336/2129252744_14946f56be_t.jpg[/img][/iurl]
	[iurl=http://farm4.static.flickr.com/3165/3056953388_4512c89d0a_b.jpg]2[/iurl]',
	"",
	'iurl'
);

insert_tag(
	__('Galerie de imagini cu descriere sub fotografie'),
	__('Permite să creați galerie navigabilă chiar în pagină.'),
	__('[iurl=adresa spre imagine jpg/gif/png]{mesaj sub fotografie}Text[/iurl]'),
	'[iurl=http://farm1.static.flickr.com/108/301925295_5beb3fa964.jpg]{'. __("Descriere fotografie") .'1}[img]http://farm1.static.flickr.com/108/301925295_5beb3fa964_t.jpg[/img][/iurl]
	[iurl=http://farm4.static.flickr.com/3141/2675676767_8f6981437f_b.jpg]{'. __("Descriere fotografie") .'2}2[/iurl]',
	__('Caracterul <b>{</b> trebuie sa apară îndată după <b>]</b>.'),
	'iurl'
);

insert_tag(
	__('Text aldin'),
	__('Transformă textul anexat în text aldin.'),
	"[b]". __('Text') ."[/b]",
	"[b]". __('Text aldin') ."[/b]",
	"",
	'b'
);

insert_tag(
	__('Text italic'),
	__('Transformă textul anexat în text italic.'),
	"[i]". __('Text') ."[/i]",
	"[i]". __('Text italic') ."[/i]",
	"",
	'i'
);

insert_tag(
	__('Text subliniat'),
	__('Transformă textul anexat în text subliniat.'),
	"[u]". __('Text') ."[/u]",
	"[u]". __('Text subliniat') ."[/u]",
	"",
	'u'
);

insert_tag(
	__('Text barat'),
	__('Transformă textul anexat în text barat.'),
	"[s]". __('Text') ."[/s]",
	"[s]". __('Text barat') ."[/s]",
	"",
	's'
);

insert_tag(
	__('Culoare (metoda ') . "1)",
	__('Schimbă culoarea textului anexat.'),
	"[color=". __('Culoare') ."]". __('Text') ."[/color]",
	"[color=blue]". __('Text Albastru') ."[/color]",
	__('Carе culori sunt valabile depinde de browser-ul dvs. Dacă utilizaţi culori de bază (red, green, blue, yellow, pink etc) puteţi să fiţi în siguranţă.'),
	'color'
);

insert_tag(
	__('Culoare (metoda ') . "2)",
	__('Schimbă culoarea textului anexat.'),
	"[color=#<i>RGB</i>]". __('Text') ."[/color]",
	"[color=#0000ff]". __('Text Albastru') ."[/color]",
	"<i>RGB</i> ". __('trebuie să fie un număr hexazecimal de şase cifre.')
);

insert_tag(
	__('Mărime'),
	__('Setează mărimea textului anexat.'),
	"[size=<i>n</i>]". __('Text') ."[/size]",
	"[size=4]". __('Mărimea acestui text este 4') ."[/size]",
	"<i>n</i> ". __('n trebuie să fie un număr întreg de la 1(cel mai mic) pînă la 7(cel mai mare). Marimea originală este 2'),
	'size'
);

insert_tag(
	__('Font'),
	__('Setează fontul pentru textul anexat.'),
	"[font=<i>Font</i>]". __('Text') ."[/font]",
	"[font=Impact]Hello world![/font]",
	__('Specificaţi fonturi alternative, prin separarea lor cu o virgulă.'),
	'font'
);

insert_tag(
	"Center",
	__('Aliniază textul anexat la centru.'),
	"[center]". __('Text') ."[/center]",
	"[center]". __('Text la centru') ."[/center]",
	__('Pentru aliniere la dreapta utilizează tag-ul right.'),
	'center'
);

insert_tag(
	"Right",
	__('Aliniază textul anexat la dreapta.'),
	"[right]". __('Text') ."[/right]",
	"[right]". __('Text la dreapta') ."[/right]",
	__('Pentru aliniere la mijloc, utilizează tag-ul center.'),
	'right'
);

insert_tag(
	"Hyperlink (alt. 1)",
	__('Introduce un hyperlink.'),
	"[url]<i>URL</i>[/url]",
	"[url]http://torrentsmd.com/[/url]",
	__('Acest tag este de prisos; toate URL-urile sunt în mod automat transformate în hyperlink.'),
	'url'
);

insert_tag(
	"Hyperlink (alt. 2)",
	__('Introduce un hyperlink.'),
	"[url=<i>URL</i>]<i>Link text</i>[/url]",
	"[url=http://torrentsmd.com/]Torrents.MD[/url]",
	__('Utilizaţi aceast tag doar în cazul în care doriţi să setaţi numele link-ului, toate URL-urile simple sunt în mod automat transformate în hyperlink.'),
	'url'
);

insert_tag(
	__('Ancoră'),
	__('Este utilizat pentru a însemna o poziție pe pagină ca mai apoi cu ajutorul unui link să se poată sări la această poziție.'),
	"[anchor]". __('nume ancoră(se admit doar litere și numere)') ."[/anchor]",
	"[anchor]ancora1[/anchor]",
	__('Ancora nu este vizibilă. Ca să sari la ancora de pe pagina curentă utilizeaza sintaxa de mai jos.'),
	'anchor'
);

insert_tag(
	__('Hyperlink (pentru ancoră de pe pagina curentă)'),
	__('Introduce un hyperlink.'),
	"[url=#". __('nume ancoră') ."]". __('Text') ."[/url]",
	"[url=#ancora1]". __('Sari spre ancora1') ."[/url]",
	__('Ancora se generează cu tagul anchor.')
);

insert_tag(
	__('Imagine (metoda ') . "1)",
	__('Afişează o imagine'),
	"[img=<i>URL</i>]",
	"[img=http://www.torrentsmd.com/pic/torrents_logo.png]",
	__('URL(adresa web) trebuie să se termine cu .gif, .jpg sau .png.'),
	'img'
);

insert_tag(
	__('Imagine (metoda ') . "2)",
	__('Afişează o imagine'),
	"[img]<i>URL</i>[/img]",
	"[img]http://www.torrentsmd.com/pic/torrents_logo.png[/img]",
	__('URL(adresa web) trebuie să se termine cu .gif, .jpg sau .png.')
);

insert_tag(
	__('Citată (metoda ') . "1)",
	__('Introduce o citată'),
	"[quote]". __('Citata') ."[/quote]",
	"[quote]The quick brown fox jumps over the lazy dog.[/quote]",
	"",
	'quote'
);

insert_tag(
	__('Citată (metoda ') . "2)",
	__('Introduce o citată'),
	"[quote=". __('Autor') ."]". __('Citata') ."[/quote]",
	"[quote=John Doe]The quick brown fox jumps over the lazy dog.[/quote]",
	""
);

insert_tag(
	__('Listă'),
	__('Introduce o listă'),
	"[*]". __('Text'),
	"[*] This is item 1\n[*] This is item 2",
	"",
	'list'
);

insert_tag(
	__('Text preformatat'),
	__('Afişează textul cu un font de lăţime fixă şi lasă spaţiile libere intacte.'),
	"[pre]". __('Text') ."[/pre]",
	"[pre]". __('Text preformatat') ."[/pre]",
	"",
	'pre'
);

end_frame();
end_main_frame();
stdfoot();
?>
