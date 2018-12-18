<?php
global $lang;

$lang['comment_add_new'] = 'Новый комментарий';
$lang['comment_send_comment'] = 'Добавить комментарий (Ctrl+Enter)';
$lang['comment_new_warring'] = 'Прочитайте <a href="./rules.php#comments">правила</a> перед тем как что-нибудь написать или вы можете быть забанены!';
$lang['comment_more_smiles'] = 'Больше смайликов';
$lang['comment_stamps'] = 'Штампы';
$lang['comment_smilies'] = 'Смайлики';
$lang['comment_sayd_thanks'] = 'Поблагодарили';
$lang['comment_thanks'] = 'Спасибо!';

$lang['watch_on'] = 'Следи за комментариями торента';
$lang['watch_off'] = 'Комментарии наблюдаются';

$lang['details_rate'] = 'Оценки';
$lang['details_rate_0'] = 'оцени';
$lang['details_rate_submit'] = 'Моё мнение';
$lang['details_rate_votes'] = 'голосов';
$lang['details_rate_vote'] = 'голос';
$lang['details_rate_onlyAfterSnatch'] = 'Сможете оценить только после того как скачаете, на странице <a href="./to_appreciate.php">Оценить</a>';
$lang['details_comments_hidden'] = 'По решению администрации, все комментарии были спрятаны';
$lang['details_comments_locked'] = 'Пост новых комментариев к этому торренту был запрещен администрацией';
$lang['cum se copie ?'] = 'как скачать ?';
/*
$lang_input['yes'] = 'Да';
$lang_input['descr'] = 'О %category%';
$lang_input['language'] = 'Язык';
$lang_input['year'] = 'Год выхода';
$lang_input['sound'] = 'Звук';
$lang_input['video'] = 'Видео';
$lang_input['sample'] = 'Сэмпл';
$lang_input['runtime'] = 'Продолжительность';
$lang_input['serial'] = 'Сериал';
$lang_input['movie_translated_name'] = 'Название';
$lang_input['movie_original_name'] = 'Оригинальное название';
$lang_input['movie_genres_list_ids'] = 'Жанр';
$lang_input['season'] = 'Сезон';
$lang_input['episode'] = 'Серия';
$lang_input['subtitles'] = 'Субтитры';
$lang_input['movie_quality'] = 'Качество';
$lang_input['directed'] = 'Режиссер';
$lang_input['name'] = 'Название';
$lang_input['bookz_author'] = 'Автор';
$lang_input['artist'] = 'Артист';
$lang_input['title'] = 'Название';
$lang_input['actors'] = 'В ролях';
$lang_input['writing_by'] = 'Автор сценариев';
$lang_input['bookz_genre'] = 'Жанр';
$lang_input['appz_version'] = 'Версия';
$lang_input['appz_license'] = 'Лицензия';
$lang_input['appz_os'] = 'Операционая система';
$lang_input['music_genre'] = 'Жанр';
$lang_input['music_format'] = 'Формат';
$lang_input['music_bitrate'] = 'Битрэйт';
$lang_input['games_recomanded_hardware'] = 'Системные требования';
$lang_input['games_genre'] = 'Жанр';
$lang_input['games_minimum_hardware'] = 'Минимальные системные требования';
//Separators
$lang_input['File_info'] = 'Файл';
$lang_input['Hardware'] = 'Требования';
*/

global $lang_input_all_names, $lang_input_all_values, $lang_category;

//> pentru a preveni replaceurile false, <b>descr</b> replace >descr cu >O %cat..
$lang_input_all_names = array('{Yes}','>File_info<','>Hardware<','>descr', '>language', '>year', '>sound', '>video', '>sample', '>runtime', '>serial', '>movie_translated_name', '>movie_original_name', '>movie_genres_list_ids', '>season', '>episode', '>subtitles', '>movie_quality', '>directed', '>name', '>bookz_author', '>artist', '>title', '>actors', '>writing_by', '>bookz_genre', '>appz_version', '>appz_license', '>appz_os', '>music_genre', '>music_format', '>music_bitrate', '>games_recomanded_hardware', '>games_genre', '>games_minimum_hardware', '>sport_genre', '>anime_genre','>dvd_genre','>country', '>hdtv_quality');
$lang_input_all_values = array('Да','>Файл<','>Требования<','>О %category%', '>Язык', '>Год выхода', '>Звук', '>Видео', '>Сэмпл', '>Продолжительность', '>Сериал', '>Название', '>Оригинальное название', '>Жанр', '>Сезон', '>Серия', '>Субтитры', '>Качество', '>Режиссер', '>Название', '>Автор', '>Артист', '>Название', '>В ролях', '>Автор сценариев', '>Жанр', '>Версия', '>Лицензия', '>Операционая система', '>Жанр', '>Формат', '>Битрэйт', '>Системные требования', '>Жанр', '>Минимальные системные требования', '>Жанр', '>Тип', '>Тип', '>Страна', '>Качество HDTV');

$translation_type_tr = array(
	'1'=>'Оригинал (без перевода)',
    '5'=>'Любительский (одноголосный)',
    '7'=>'Любительский (двухголосый)',
	'4'=>'Любительский (многоголосый)',
    '8'=>'Профессиональный (одноголосный)',
    '9'=>'Профессиональный (двухголосый)',
    '3'=>'Профессиональный (многоголосый, закадровый)',
	'2'=>'Профессиональный (полное дублирование)',
	'6'=>'Синхронный перевод'
);

$lang['Traducere'] = 'Перевод';
$lang_category[1] = 'фильме';
$lang_category[2] = 'музыке';
$lang_category[3] = 'программе';
$lang_category[4] = 'игре';
$lang_category[5] = 'передаче';
$lang_category[6] = 'фильме';
$lang_category[7] = 'содержании';
$lang_category[8] = 'книге';
$lang_category[9] = 'видео клипе';
$lang_category[10] = 'фильме';
$lang_category[11] = 'фильме';
$lang_category[12] = 'фильме';
$lang_category[13] = 'фильме';
$lang_category[14] = 'аудио книге';
$lang_category[15] = 'уроке';
$lang_category[16] = 'фотографии';
$lang_category[17] = 'фильме';
$lang_category[18] = 'фильме';

$lang['uploaded_redownload'] = 'Для того чтобы сидировать, вам нужно скачать этот торрент с трэкера (autodownload)';

$lang['Copiază'] = 'Скачать';
$lang['Nu puteţi copia torrente'] = 'Вы не можете скачивать торренты';

$lang['Raportează'] = 'Сообщить админам';
$lang['Raportat, mulţumim'] = 'Спасибо';

$lang['<span style="color:green">activat</span> - Nici o limită la copiere, se va considera doar uploadul'] = '<span style="color:green">включён</span> - Считается только аплоад и нет никаких ограничений';
$lang['Lista celor care au mulțumit'] = 'Последние поблагодарившие';
$lang['Descriere'] = 'Описание';
$lang['Încă nu sunt suficiente voturi'] = 'Недостаточно голосов';

$lang['Statutul torrentului tău: %s a fost schimbat în: %s de către %s'] = 'Статус вашего торрента: %s был изменен на: %s пользователем %s';
$lang['Statutul torrentului'] = 'Статус торрента';
$lang['voturi'] = 'голоса';
$lang['Vizibil'] = 'Видимый';
$lang['nu'] = 'нет';
$lang['Ultimul seeder'] = 'Последний сидер';
$lang['în urmă'] = 'назад';
$lang['(date înnoite fiecare 24 ore)'] = '(данные обновляются каждые 24 часа)';
$lang['Nici un vot'] = 'Ни одного голоса';
$lang['Văzut'] = 'Просмотров';
$lang['ori'] = 'раз';
$lang['La moment nu este trafic'] = 'В данный момент нет трафика';
$lang['Viteza totală'] = 'Общая скорость';
$lang['Editează acest torrent'] = 'Отредактировать торрент';
$lang['Descărcări'] = 'Скачан';
$lang['Utilizator'] = 'Пользователь';
$lang['Ratio global'] = 'Общий рейтинг';
$lang['Cînd'] = 'Когда';
$lang['Vezi întreaga listă'] = 'Посмотреть полный список';
$lang['Peer-uri'] = 'Пиры';
$lang['Comentariile torrentului'] = 'Комментарии к торренту';
$lang['torrente cu același număr IMDB'] = 'торрентов с одинаковым номером IMDB';

$lang['Echipa'] = 'Команда';
$lang['Statut'] = 'Статус';
$lang['Neverificat'] = 'Не проверено';
$lang['Verificat'] = 'Проверено';
$lang['Se verifică'] = 'Проверяется';
$lang['Închis'] = 'Закрыто';
$lang['Descriere parțial necompletă'] = 'Недооформлено';
$lang['Descriere necompletă'] = 'Неоформлено';
$lang['Dublare'] = 'Повтор';
$lang['Închis de către deținătorul dreptului de autor'] = 'Раздача закрыта правообладателями';
$lang['Absorbit'] = 'Поглощено';
$lang['Dubios'] = 'Сомнительно';
$lang['Schimbă'] = 'Изменить';
$lang['Nu există comentarii'] = 'Комментариев пока нет';
$lang['Fişiere'] = 'Файлы';
$lang['fişiere'] = 'файлов';
$lang['Lista fişierelor'] = 'Список файлов';
$lang['Ascunde lista'] = 'Спрятать список';
$lang['Locaţia'] = 'Местоположение';
$lang['Mărime'] = 'Размер';
$lang['Utilizator'] = 'Пользователь';
$lang['Conectabil'] = 'Конн.';
$lang['Rată'] = 'Скорость';
$lang['Descărcat'] = 'Скачал';
$lang['Raport'] = 'Соотн.';
$lang['Completat'] = 'Завершил';
$lang['Inactiv'] = 'Неактив.';
$lang['Client'] = 'Клиент';
$lang['Încărcat'] = 'Загрузил';
$lang['Da'] = 'Да';
$lang['Nu'] = 'Нет';
$lang['Încărcat cu succes!'] = 'Успешно загружен!';
$lang['Acum puteţi începe seeding-ul. <b>Reţineţi</b> că torrent-ul nu va fi vizibil până când nu veţi face asta!'] = 'Теперь вы можете начать раздавать. <b>Обратите внимание</b> на то, что торрент не будет виден, пока вы этого не сделаете!';
$lang['Eroare'] = 'Ошибка';
$lang['Nu există torrent cu ID-ul'] = 'Нет торрента с ID';
$lang['Editează'] = 'Редактировать';
$lang['Şterge'] = 'Удалить';
$lang['Cenzurează'] = 'Cenzurează';
$lang['Decenzurează'] = 'Decenzurează';
$lang['Imaginea nouă a fost încărcată!'] = 'Новое изображение загружено!';
$lang['Redactat cu succes!'] = 'Успешно отредактировано!';
$lang['Înapoi.'] = 'Вернуться.';
$lang['Temporar nu se poate de copiat, se verifică.'] = 'Торрент временно недоступен - проверяется.';
$lang['Torrentul este închis, nu poate fi copiat.'] = 'Торрент закрыт';
$lang['În descriere sunt abateri semnificative de la reguli, nu poate fi copiat.'] = 'В описании есть значительные отклонения от правил - загрузка недоступна.';
$lang['Torrentul e o dublare, nu poate fi copiat.'] = 'Торрент - повтор - загрузка недоступна. ';
$lang['Deținătorul drepturilor a închis acest torrent.'] = 'Правообладатель закрыл этот торрент.';
$lang['Torrentul a fost absorbit, nu poate fi copiat, vezi în ultimul mesaj din comentarii de către ce a fost absorbit.'] = 'Торрент был поглощён, поэтому не может быть скачан, узнать, каким торрентом он был поглощён, можно из последнего сообщения в комментариях.';
$lang['Vezi autorul'] = 'Запросить автора';
$lang['Renunț la acest torrent'] = 'Отказываюсь от торрента';
$lang['Sunteți sigur că doriți să renunțați la acest torrent?'] = 'Вы уверены что желаете отказаться от торрента?';
$lang['Vă amintim că această acțiune rupe orice legătură dintre tine și torrentul dat, prin urmare nu o să-l mai puteți edita vreodată iar numărul de mulțumiri a torrentului la care renunțați nu vă va modifica cifra totală a mulțumirilor.'] = 'Напоминаем, что это действие влечет за собой прерывание любой связи между Вами и данным торрентом. Следовательно, Вы не сможете никогда более его отредактировать, а количество благодарностей данного тореннта не изменит общего количество полученных Вами благодарностей.';
$lang['Sunt sigur că doresc să renunț la acest torrent'] = 'Подтверждаю отказ от торрента';
$lang['Atenție!'] = 'Внимание!';
$lang['Arată toate categoriile'] = 'Покажи все категории';
$lang['Editează categoriile'] = 'Изменить категории';

$lang['Subtitrări']='Субтитры';
$lang['engleză']='английском';
$lang['română']='румынском';
$lang['rusă']='русском';
$lang['În'] = 'На';
$lang['nou'] = 'новинка';

$lang['Torentul dat conține fișiere executabile (.exe).'] = 'Торрент содержит исполняемый файл .exe.';
$lang['Înțeleg riscul și doresc să downloadez torrentul'] = 'Я понимаю риск и все-равно хочу скачать торрент';
$lang['Categorii'] = 'Категории';

$lang['Detaliile torrentului'] = 'Детали торрента';

$lang['comment_rules'] = <<<EOT
<div class="generic_box_default">
  Написав сообщение, убедитесь что оно следует <b>этикету общения</b>.
<br/>
<ul>

<li>
  <b>Общайтесь с другими так, как вам бы хотелось что бы с вами общались.</b>
</li>

<li>
<b>Не ссорьтесь и не оскорбляйте кого-либо.</b> Если вы не согласны с кем-либо в комментариях, попробуйте представить, что заставило других думать так, как они думают. После этого, выразите свое мнение таким образом, каким никто не был оскорблён.
</li>

<li>
  <b>Не ссорьтесь и не оскорбляйте uploader'а.</b> Если у вас есть какие-либо замечания к торренту, помните, что он был кем-то загружен добровольно. Если присутствует ошибка, она скорее всего, не была допущена намеренно. Поэтому любое замечание следует выразить так, дабы не обидеть uploader'a.
</li>

<li>
  <b>Отмечать сообщения, которые противоречат этикету общения, с помощью кнопки «Сообщить админам».</b> Модераторы вмешаются настолько скоро, насколько это возможно. В случае, если кто-то оскорбляет, немыслимо пытаться доказывать что-либо кому-то оскорбляя еще больше.
</li>

<li>
  <b>Поблагодарите, прежде чем критиковать.</b> Скачивая информацию бесплатно, важно отблагодарить тех, кто вам ее предоставил.<br/>
Модель общения с uploader'ом, лучше всего отправить как личное сообщение, будет следующей:<br/>
«Спасибо, что вы загрузили торрент [ссылка] Я заметил, что отсутствует x в описании, не могли бы вы добавить?»

</li>

<li>
  <b>Если качество ниже ваших ожиданий.</b>
  Если качество чётко указано в описании, то у вас не может быть претензий. Автор раздачи, скорее всего, скачал торрент с другого трекера, поэтому перезагрузил то, что нашел, и именно поэтому мы должны быть ему благодарны. Поскольку, данная версия могла бы и не быть на данном трекере.
  </br>
  <b>Используйте поиск чтобы найти лучшее качество, если кто-то уже загрузил его, или загрузите его самостоятельно, все будут вам блгодарны.</b>
</li>

<li>
  <b>Пожалуйста, придерживайтесь тех же поведенческих стандартов в онлайн-пространстве, которых вы придерживаетесь и в реальной жизни.</b>
</li>
<li>
  <b>Помните, что вы общаетесь с другими людьми.</b>
  Когда вы общаетесь в Интернете, все, что вы видите, это экран монитора. Написав, можете спросить себя: «Сказал бы я этому человеку в лицо то же самое?» или «Обиделся бы мой друг, если бы я ответил ему так?»
</li>

<li>
    <b>Используйте советы для общения в книге Дейла Карнеги «Как завоевывать друзей и оказывать влияние на людей».</b>
    Никто не любит быть окритикованным. Важно выражать свое мнение таким образом, чтобы никто не был обижен. <b><a href="https://ru.wikipedia.org/wiki/%D0%9A%D0%B0%D0%BA_%D0%B7%D0%B0%D0%B2%D0%BE%D1%91%D0%B2%D1%8B%D0%B2%D0%B0%D1%82%D1%8C_%D0%B4%D1%80%D1%83%D0%B7%D0%B5%D0%B9_%D0%B8_%D0%BE%D0%BA%D0%B0%D0%B7%D1%8B%D0%B2%D0%B0%D1%82%D1%8C_%D0%B2%D0%BB%D0%B8%D1%8F%D0%BD%D0%B8%D0%B5_%D0%BD%D0%B0_%D0%BB%D1%8E%D0%B4%D0%B5%D0%B9">Прочтите описание книги здесь</a></b>. Возможно изменит вашу жизнь и окружающих!


</li>

</ul>


</div>
EOT;

$lang['Programat pentru update'] = 'Запланировано обновление';
$lang['ultimul update'] = 'последнее обновление';
$lang['DHT peer-uri'] = 'DHT пиры';
$lang['Acțiuni'] = 'Действия';
$lang['Semnalează torrentul'] = 'Сообщить админам';
?>