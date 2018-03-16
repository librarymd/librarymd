function ShowMenu() {
  if (ShowMenu.arguments.length > 0) _guest_menu( );
  else _registred_menu();
  var top_menu = document.getElementById('top_menu');

  var logo_html = '<a href="/"><img id="tmd_logo" height="76" src="/pic/logo/'+logo+'" border="0" alt="Logo" title="'+logo_main_title+'" '+logo_map+' style="border-style:none"></a>';

  if(message.lang=='ro')
	  var menuHtml = '<table cellSpacing="0" cellPadding="0" class="transparent" align="center" border="0"><tr><td style="line-height:0">' + logo_html +'</td><td class="space2" vAlign="top" align="right" width="100%">   <table cellSpacing="0" cellPadding="0" width="0" border="0" class="transparent"><tr><td class="topheader" style="padding-top:8px;" id="top_menu" nowrap><a href="/staff.php">echipa</a>&nbsp;|&nbsp;<a href="/faq.php?page=tools">instrumente</a></td></tr></table>   <table cellSpacing="0" cellPadding="0" width="0" border="0" class="transparent"><tr><td class="topheader"><form method="get" action="search.php" style="display:inline;"><input name="search_str" type="text" id="main_search_text" placeholder="Nume torrent...">&nbsp;&nbsp;<input type="submit" value="Caută" id="main_search_button"></form></td></tr><tr><td>&nbsp;</td></tr></table>   </td></tr></table> ';
  else
	  var menuHtml = '<table cellSpacing="0" cellPadding="0" class="transparent" align="center" border="0"><tr><td style="line-height:0">' + logo_html +'</td><td class="space2" vAlign="top" align="right" width="100%"> 	  <table cellSpacing="0" cellPadding="0" width="0" border="0" class="transparent"><tr><td class="topheader" style="padding-top:8px;" id="top_menu" nowrap><a href="/staff.php">команда</a>&nbsp;|&nbsp;<a href="/faq.php?page=tools">инструменты</a></td></tr></table> 	  <table cellSpacing="0" cellPadding="0" width="0" border="0" class="transparent"><tr><td class="topheader"><form method="get" action="search.php" style="display:inline;"><input name="search_str" id="main_search_text" type="text" placeholder="Имя торрента...">&nbsp;&nbsp;<input type="submit" value="Найти" id="main_search_button"></form></td></tr></table> 	  </td></tr></table>';

  var logoMapHtml = '<map id="torrents_logo_map" name="torrents_logo_map">   <area shape="rect" coords="274,4,382,75" target="_blank" href="'+logo_map_link+'" title="'+logo_map_title+'" />   <area shape="default" href="/" alt="" />   </map>';
  top_menu.innerHTML = menuHtml + logoMapHtml;
}
men_sep_img = '<img height=49 src="/pic/meniu_separator.gif" width=16 align=absMiddle>';

function _registred_menu()
{
  var main_menu = document.getElementById('main_menu');

  if(message.lang=='ro')
	  main_menu.innerHTML = '<table id="tbl_menu" cellSpacing="0" cellPadding="0" align="center"><tbody><tr><td align="left" id="menutable"><a href="/index.php">Pagina Principală</a>' + men_sep_img + '<a href="/browse.php">Torrente</a>' + men_sep_img + '<a href="/upload.php">Încarcă</a>' + men_sep_img + '<a href="/forum.php">FORUM</a>' + men_sep_img + '<a href="/users.php">Utilizatori</a>' + custom_html_to_menu + '</td><td align="right"><a href="/logout.php">Ieşire</a></td></tr></tbody></table>';
  else
	  main_menu.innerHTML = '<table id="tbl_menu" cellSpacing="0" cellPadding="0" align="center"><tbody><tr><td align="left" id="menutable"><a href="/index.php">Главная</a>' + men_sep_img + '<a href="/browse.php">Торренты</a>' + men_sep_img + '<a href="/upload.php">Загрузить</a>' + men_sep_img + '<a href="/forum.php">Форум</a>' + men_sep_img + '<a href="/users.php">Пользователи</a>' + custom_html_to_menu + ' </td><td align="right"><a href="/logout.php">Выход</a></td></tr></tbody></table>';
}

function _guest_menu()
{
  var main_menu = document.getElementById('main_menu');

  if(message.lang=='ro')
	  main_menu.innerHTML = '<table id="tbl_menu" cellSpacing="0" cellPadding="0" align="center"><tbody><td align="left" id="menu" class="anon"><a href="/index.php">Principala</a>'+ men_sep_img + '<a id="menu_item_login" href="./login.php">Autentificare</a>' + men_sep_img + '<a href="/browse.php" class="torrents">Torrente</a>'  + men_sep_img + '<a href="/signup.php">Înregistrare</a>' + men_sep_img + '<a href="/forum.php">Forum</a></td></tbody></table>';
  else
	  main_menu.innerHTML = '<table id="tbl_menu" cellSpacing="0" cellPadding="0" align="center"><tbody><td align="left" id="menu" class="anon"><a href="/index.php">Главная</a>'+ men_sep_img + '<a id="menu_item_login" href="./login.php">Авторизация</a>' + men_sep_img + '<a href="/browse.php" class="torrents">Торренты</a>' + men_sep_img + '<a href="/signup.php">Регистрация</a>' + men_sep_img + '<a href="/forum.php">Форум</a></td></tbody></table>';
}

function print_browse_top_menu()
{
	if(message.lang=='ro')
		document.write('<br><div id="topmenu"><span id="menu_1" class="showit"><a class="a_cur_selected" onclick="show_lastest(this);">Ultimele torrente</a> | <a class="a_inactive" onclick="show_top(\'24h\',this);">Top din ultimele 24 ore</a> | <a class="a_inactive" onclick="show_top(\'3d\',this);">Top din ultimele 3 zile</a> | <a class="a_inactive" onclick="change_topmenu(\'next\');">-></a></span><span id="menu_2" class="hideit"><a class="a_inactive" onclick="change_topmenu(\'back\');"><-</a> | <a class="a_inactive" onclick="show_top(\'7d\',this);">Top din ultima saptamana</a> | <a class="a_inactive" onclick="show_top(\'1m\',this);">Top din ultima luna</a> | <a class="a_inactive" onclick="show_top(\'all\',this);">Top din toate torrentele</a></span></div>');
	else
		document.write('<br><div id="topmenu"><span id="menu_1" class="showit"><a class="a_cur_selected" onclick="show_lastest(this);">Последние торренты</a> | <a class="a_inactive" onclick="show_top(\'24h\',this);">Топ за последние 24 часа</a> | <a class="a_inactive" onclick="show_top(\'3d\',this);">Топ за последние 3 дня</a> | <a class="a_inactive" onclick="change_topmenu(\'next\');">-></a></span><span id="menu_2" class="hideit"><a class="a_inactive" onclick="change_topmenu(\'back\');"><-</a> | <a class="a_inactive" onclick="show_top(\'7d\',this);">Топ за последнюю неделю</a> | <a class="a_inactive" onclick="show_top(\'1m\',this);">Топ за последний месяц</a> | <a class="a_inactive" onclick="show_top(\'all\',this);">Топ всех торрентов</a></span></div>');
}


