<?php

$allowed_img_hosts = array('tinypic.com', 'imgur.com','imageshack.us','bayimg.com','radikal.ru','xs.to','imagevenue.com','screensnapr.com','fastpic.ru','imageshack.us','iceimg.com','directupload.net');

function bbcode_check_permission($s,$html='') {
  if (isModerator()) return;
  if (empty($html)) $html = format_comment($s);
}

function bbcode_img_only_allowed_domains($text,$text_parsed='') {
  global $allowed_img_hosts;
  if (empty($text_parsed)) {
    $text_parsed = format_comment($text);
  }
  $matches2_r = preg_match_all('#<var(.+?)title="(.+?)"(.+?)>#i',$text_parsed,$matches2);
  $not_allowed_because_of = array();
  if (preg_match_all('#<img(.+?)src="(.+?)"(.+?)>#i',$text_parsed,$matches) || $matches2_r) {
    if ($matches2_r) $matches[2] = array_merge($matches[2],$matches2[2]);
    foreach($matches[2] AS $host) {
      if (strpos($host,'://') === FALSE) {
        continue;
      }
      $host_parts = explode('.', strtolower(parse_url($host,PHP_URL_HOST)) );
      $host_parts_l = count($host_parts);
      $domain = $host_parts[$host_parts_l-2].'.'.$host_parts[$host_parts_l-1];
      //echo $domain;
      if (!in_array($domain,$allowed_img_hosts)) {
        $not_allowed_because_of[] = $host;
      }
    }
  }

  if (count($not_allowed_because_of)) {
    $errBody = __('Din <a href="./forum.php?action=viewtopic&topicid=361692#11" target="_blank">motive de securitate</a>, imaginile pentru tag-ul [img] se acceptă doar de pe site-urile din <a href="/imagestorage.php" target="_blank">această listă</a>, puteți să le încărați acolo. Apăsați butonul Înapoi, și edițați-vă mesajul. Imaginile: ').
    esc_html(join(' , ',$not_allowed_because_of));
    barkk($errBody);
  }
}

function format_quotes($s) {
  $old_s = '';
  while ($old_s != $s) {
    $old_s = $s;

    //find first occurrence of [/quote]
    $close = strpos($s, "[/quote]");
    if ($close === false)
      return $s;

    //find last [quote] before first [/quote]
    //note that there is no check for correct syntax
    $open = strripos(substr($s,0,$close), "[quote");
    if ($open === false)
      return $s;

    $quote = substr($s,$open,$close - $open + 8);

    //[quote]Text[/quote]
    $quote = preg_replace(
      "/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
      '<p class="sub"><b>Quote:</b></p><table class="main" border="1" cellspacing="0" cellpadding="10"><tr><td style="border:1px #000000 dotted;">\\1</td></tr></table><br />', $quote);

    //[quote=Author]Text[/quote]
    $quote = preg_replace(
      "/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i",
      '<p class="sub"><b>\\1 wrote:</b></p><table class="main" border="1" cellspacing="0" cellpadding="10"><tr><td style="border:1px #000000 dotted;">\\2</td></tr></table><br />', $quote);

    $s = substr($s, 0, $open) . $quote . substr($s,$close + 8);
  }

  return $s;
}



function format_spoiler($text) {
  $text = " " . $text; // p/u bbencode_first_pass_pda

  $uid = rand(99,10000);
  $text = bbencode_first_pass_pda($text, $uid, '/\[spoiler=([^<>\n]*?)\]/i', '[/spoiler]', '', false, 'bbspoiler', '[spoiler:'.$uid.'=\\1]');
  $text = bbencode_first_pass_pda($text, $uid, '/\[ospoiler=([^<>\n]*?)\]/i', '[/ospoiler]', '[/spoiler]', false, 'bbspoiler', '[ospoiler:'.$uid.'=\\1]');
  $text = bbencode_first_pass_pda($text, $uid, '[spoiler]', '[/spoiler]', '', false, 'bbspoiler', false);

  $text = str_replace("[spoiler:$uid]", '<div class="sp-wrap"><div class="sp-head folded clickable"><span class="lang-ro-hide-all">text ascuns</span><span class="lang-ru-hide-all">скрытый текст</span></div><div class="sp-body">', $text);
  $text = preg_replace('/\[spoiler:'.$uid.'=([^<>\n]*?)\]/i','<div class="sp-wrap"><div class="sp-head folded clickable">\\1</div><div class="sp-body">', $text);
  $text = preg_replace('/\[ospoiler:'.$uid.'=([^<>\n]*?)\]/i','<div class="sp-wrap"><div class="sp-head folded clickable unfolded">\\1</div><div class="sp-body" style="display: block;">', $text);

    $endSp = '<div class="sp-foot"><span class="lang-ro-hide-all">Închide</span><span class="lang-ru-hide-all">Закрыть</span></div></div></div>';

  $text = str_replace(array("[/spoiler:$uid]\r\n","[/spoiler:$uid]\n","[/spoiler:$uid]\r","[/spoiler:$uid]"),
                        array($endSp, $endSp, $endSp, $endSp),  $text);

  return substr($text, 1);
}

function bbspoiler($text, $uid) {
  return str_replace(array('[img]','[/img]','[img'),array('[himg]','[/himg]','[himg'),$text);
}

function format_audio($text)
{
  $text = preg_replace('#\[audio\]https?://(www.)?soundcloud.com/(.*?)\[/audio\]#i','<div class="sndsc_container"><object height="81" width="100%"><param name="movie" value="https://player.soundcloud.com/player.swf?url=http%3A//soundcloud.com/\\2&amp;show_comments=false&amp;auto_play=false&amp;color=0A50A1"></param><param name="wmode" value="transparent" /><param name="allowscriptaccess" value="always"></param><embed allowscriptaccess="always" height="81" src="https://player.soundcloud.com/player.swf?url=http%3A//soundcloud.com/\\2/&amp;show_comments=false&amp;auto_play=false&amp;color=0A50A1" type="application/x-shockwave-flash" width="100%"></embed></object></div><div class="clear"></div>', $text);

  return $text;
}


function format_video_template($is, $regexp_id) {
  $url = $regexp_id;
  switch($is)
  {
    case 'yt':
      return '<div class="yt_container"> <iframe width="640" height="385" src="https://www.youtube-nocookie.com/embed/'.$url.'hl=en&fs=1" frameborder="0" allowfullscreen></iframe> </div> <div class="clear"> </div>';

    case 'vimeo':
      return '<div class="vimeo_container"> <iframe src="http://player.vimeo.com/video/'.$url.'?byline=0&portrait=0" width="640" height="360" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div><div class="clear"> </div>';

    case 'ted':
      return '<iframe src="https://embed.ted.com/talks/' . $url .'" width="640" height="360" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

    case 'giphy':
      return '<iframe src="https://giphy.com/embed/'. $url .'" width="640" height="270" frameBorder="0" class="giphy-embed" allowFullScreen></iframe>';

  }

  return '';
}
function format_video($text) {
  $text = preg_replace('#\[video\]https?://www.youtube.com/watch\?v=(([\w\d-]){11})\#t=((\d)+).*\[/video\]#i', format_video_template('yt', '\\1?start=\\3&') , $text);
  //$text = preg_replace('#\[video\]http://www.youtube.com/watch\?.*v=(([\w\d-]){11}).*t=(.+).*\[/video\]#i', 'm4 \\1 \\2 \\3 \\0'.format_video_template('yt', '\\1&t=\\3') , $text);
  $text = preg_replace('#\[video\]https?://www.youtube.com/watch\?.*v=(([\w\d-]){11}).*\[/video\]#i', format_video_template('yt' ,'\\1?'), $text);

  $text = preg_replace('#\[video\]https?://(www.)?vimeo.com/((\d)+).*\[/video\]#i', format_video_template('vimeo' ,'\\2'), $text);

  $text = preg_replace('#\[video\]https?://(www.)?ted.com/talks/(([\w\d_-])+).*\[/video\]#i', format_video_template('ted' ,'\\2'), $text);

  $text = preg_replace('#\[video\]https://(www.)?giphy.com/gifs/(.+?)-([\w\d]+)/?\[/video\]#i', format_video_template('giphy' ,'\\3'), $text);


  $replace_what = array();
  $replace_with = array();
  if (preg_match_all('#\[fb\](https://(www.)?facebook.com/.+?)\[/fb\]#i',$text,$matches)) {
    foreach($matches[1] AS $url) {
      $encodedUrl = esc_html(urlencode($url));
      $replace_what[]='[fb]'.$url.'[/fb]';
      $replace_with[]='<iframe src="https://www.facebook.com/plugins/post.php?href='. $encodedUrl .'&width=640" width="640" height="447" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
    }
    if (is_array($replace_what)) $text = str_replace($replace_what,$replace_with,$text);
  }

  return $text;
}




$_colors = array('aliceblue'=>'F0F8FF','antiquewhite'=>'FAEBD7','aqua'=>'00FFFF','aquamarine'=>'7FFFD4','azure'=>'F0FFFF','beige'=>'F5F5DC','bisque'=>'FFE4C4','black'=>'000000','blanchedalmond'=>'FFEBCD','blue'=>'0000FF','blueviolet'=>'8A2BE2','brown'=>'A52A2A','burlywood'=>'DEB887','cadetblue'=>'5F9EA0','chartreuse'=>'7FFF00','chocolate'=>'D2691E','coral'=>'FF7F50','cornflowerblue'=>'6495ED','cornsilk'=>'FFF8DC','crimson'=>'DC143C','cyan'=>'00FFFF','darkblue'=>'00008B','darkcyan'=>'008B8B','darkgoldenrod'=>'B8860B','darkgray'=>'A9A9A9','darkgreen'=>'006400','darkkhaki'=>'BDB76B','darkmagenta'=>'8B008B','darkolivegreen'=>'556B2F','darkorange'=>'FF8C00','darkorchid'=>'9932CC','darkred'=>'8B0000','darksalmon'=>'E9967A','darkseagreen'=>'8FBC8F','darkslateblue'=>'483D8B','darkslategray'=>'2F4F4F','darkturquoise'=>'00CED1','darkviolet'=>'9400D3','deeppink'=>'FF1493','deepskyblue'=>'00BFFF','dimgray'=>'696969','dodgerblue'=>'1E90FF','firebrick'=>'B22222','floralwhite'=>'FFFAF0','forestgreen'=>'228B22','fuchsia'=>'FF00FF','gainsboro'=>'DCDCDC','ghostwhite'=>'F8F8FF','gold'=>'FFD700','goldenrod'=>'DAA520','gray'=>'808080','green'=>'008000','greenyellow'=>'ADFF2F','honeydew'=>'F0FFF0','hotpink'=>'FF69B4','indianred'=>'CD5C5C','indigo'=>'4B0082','ivory'=>'FFFFF0','khaki'=>'F0E68C','lavender'=>'E6E6FA','lavenderblush'=>'FFF0F5','lawngreen'=>'7CFC00','lemonchiffon'=>'FFFACD','lightblue'=>'ADD8E6','lightcoral'=>'F08080','lightcyan'=>'E0FFFF','lightgoldenrodyellow'=>'FAFAD2','lightgreen'=>'90EE90','lightgrey'=>'D3D3D3','lightpink'=>'FFB6C1','lightsalmon'=>'FFA07A','lightseagreen'=>'20B2AA','lightskyblue'=>'87CEFA','lightslategray'=>'778899','lightsteelblue'=>'B0C4DE','lightyellow'=>'FFFFE0','lime'=>'00FF00','limegreen'=>'32CD32','linen'=>'FAF0E6','magenta'=>'FF00FF','maroon'=>'800000','mediumaquamarine'=>'66CDAA','mediumblue'=>'0000CD','mediumorchid'=>'BA55D3','mediumpurple'=>'9370DB','mediumseagreen'=>'3CB371','mediumslateblue'=>'7B68EE','mediumspringgreen'=>'00FA9A','mediumturquoise'=>'48D1CC','mediumvioletred'=>'C71585','midnightblue'=>'191970','mintcream'=>'F5FFFA','mistyrose'=>'FFE4E1','moccasin'=>'FFE4B5','navajowhite'=>'FFDEAD','navy'=>'000080','oldlace'=>'FDF5E6','olive'=>'808000','olivedrab'=>'6B8E23','orange'=>'FFA500','orangered'=>'FF4500','orchid'=>'DA70D6','palegoldenrod'=>'EEE8AA','palegreen'=>'98FB98','paleturquoise'=>'AFEEEE','palevioletred'=>'DB7093','papayawhip'=>'FFEFD5','peachpuff'=>'FFDAB9','peru'=>'CD853F','pink'=>'FFC0CB','plum'=>'DDA0DD','powderblue'=>'B0E0E6','purple'=>'800080','red'=>'FF0000','rosybrown'=>'BC8F8F','royalblue'=>'4169E1','saddlebrown'=>'8B4513','salmon'=>'FA8072','sandybrown'=>'F4A460','seagreen'=>'2E8B57','seashell'=>'FFF5EE','sienna'=>'A0522D','silver'=>'C0C0C0','skyblue'=>'87CEEB','slateblue'=>'6A5ACD','slategray'=>'708090','snow'=>'FFFAFA','springgreen'=>'00FF7F','steelblue'=>'4682B4','tan'=>'D2B48C','teal'=>'008080','thistle'=>'D8BFD8','tomato'=>'FF6347','turquoise'=>'40E0D0','violet'=>'EE82EE','wheat'=>'F5DEB3','white'=>'FFFFFF','whitesmoke'=>'F5F5F5','yellow'=>'FFFF00','yellowgreen'=>'9ACD32');

function postProcessingRemoveNewPageLink($html) {
  return str_replace('target="_blank"', '', $html);
}

function format_comment($s, $strip_html = true, $images_into_container=false, $images_into_container_class="imgforumcontainer")
{
  global $_colors;

  $s = str_replace(array(';)'), array(':wink:'), $s);
  $s = esc_html($s);

  if (strpos($s, "[") === false) {
    $s = make_clickable($s);
    $s = format_smiles($s);
    $s = nl2br($s);

    return $s;
  }

  $s = " " . $s; // p/u bbencode_first_pass_pda
  $uid = rand(99,10000);

  $s = bbencode_first_pass_pda($s, $uid, '[code]', '[/code]', '', false, 'tag_code_pre', false);
  $s = str_replace(array("[code:$uid]","[/code:$uid]"), array('<div class="sp-wrap"><div class="c-head">Code</div><div class="c-body">','</div></div>'), $s);

  $s = format_quotes($s);

  $replace_what = array();
  $replace_with = array();
  if (preg_match_all('/\[color=([a-zA-Z]+)\]/',$s,$matches)) {
    foreach($matches[1] AS $match) {
      $match_to_lower = strtolower($match);
      if (isset($_colors[$match_to_lower])) {
        $replace_what[]="[color=$match]";
        $replace_with[]="[color=#$_colors[$match_to_lower]]";
      }
    }
    if (is_array($replace_what)) $s = str_replace($replace_what,$replace_with,$s);
  }

  $basic_bbcode = array('#\[b\](.*?)\[/b\]#si' => '<b>\\1</b>',
    '#\[i\](.*?)\[/i\]#si' => '<i>\\1</i>',
    '#\[u\](.*?)\[/u\]#si' => '<u>\\1</u>',
    '#\[s\](.*?)\[/s\]#si' => '<s>\\1</s>',
    '/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\](.*?)\[\/color\]/si' => '<font color=\\1>\\2</font>',
    '#\[size=([1-7])\](.*?)\[\/size\]#si' => '<font style="line-height:normal;" size="\\1">\\2</font>',
    '#\[font=([a-zA-Z ,]+)\](.*?)\[\/font\]#si'=>'<font face="\\1">\\2</font>',
    '#\[center\](.*?)\[\/center\]#si'=>'<div align="center">\\1</div>',
    '#\[right\](.*?)\[\/right\]#si'=>'<div align="right">\\1</div>',
    "#\[\*\](.+)(\n)*#i" => "<ul><li>\\1</li></ul>",
    "#\[\*\*\](.+)(\n)*#i" => "<ul><ul><li>\\1</li></ul></ul>",
    "#\[\*\*\*\](.+)(\n)*#i" => "<ul><ul><ul><li>\\1</li></ul></ul></ul>",
    '#\[lang-ro\](.*?)\[\/lang-ro\]#s'=>'<div class="lang-ro-hide">\\1</div>',
    '#\[lang-ru\](.*?)\[\/lang-ru\]#s'=>'<div class="lang-ru-hide">\\1</div>',
    '#\[yt\]https?://www.youtube.com/watch\?v=(([\w\d-]){11})\#t=((\d)+)\[/yt\]#i'=>'<div class="yt_container"><iframe width="640" height="385" src="//www.youtube-nocookie.com/embed/\\1?start=\\3" frameborder="0" allowfullscreen></iframe></div> <div class="clear"> </div>',
    '#\[yt\]https?://www.youtube.com/watch\?v=(([\w\d-]){11})\[/yt\]#i'=>'<div class="yt_container"><iframe width="640" height="385" src="//www.youtube-nocookie.com/embed/\\1" frameborder="0" allowfullscreen></iframe></div> <div class="clear"> </div>',
  );

  $s = format_audio($s);
  $s = format_video($s);

  foreach($basic_bbcode AS $basic_bbcode_reg=>$basic_bbcode_repl) {
    $s = preg_replace($basic_bbcode_reg, $basic_bbcode_repl, $s);
  }

  $s = format_spoiler($s);

  // [img]http://www/image.gif[/img]
  if ($images_into_container) $s = preg_replace("/\[img\]((https?:\/\/|\.)[^\s'\"<>?]+(\.(jpg|jpeg|gif|png)))\[\/img\]/i","<div class=$images_into_container_class><img src=\"\\1\"></div>", $s);
  else $s = preg_replace("/\[img\]((https?:\/\/|\.)[^\s'\"<>?]+(\.(jpg|jpeg|gif|png)))\[\/img\]/i",'<img src="\\1" />', $s);

  // [img=http://www/image.gif]
  if ($images_into_container) $s = preg_replace("/\[img=((https?:\/\/|\.)[^\s'\"<>?]+(\.(jpg|jpeg|gif|png)))\]/i","<div class=$images_into_container_class><img src=\"\\1\"></div>", $s);
  else $s = preg_replace("/\[img=((https?:\/\/|\.)[^\s'\"<>?]+(\.(jpg|jpeg|gif|png)))\]/i",'<img src="\\1" />', $s);

  // [himg]http://www/image.gif[/himg] [himg=http://www/image.gif]
  $s = preg_replace("/\[himg\]((https?:\/\/|\.)[^\s'\"<>?]+(\.(jpg|jpeg|gif|png)))\[\/himg\]/i",'<var class="postImg" title="\\1" ></var>', $s);
  $s = preg_replace("/\[himg=((https?:\/\/|\.)[^\s'\"<>?]+(\.(jpg|jpeg|gif|png)))\]/i",'<var class="postImg" title="\\1" ></var>', $s);


  // [url=http://www.example.com]Text[/url]
  $s = preg_replace(
    "/\[url=((?:http|https):\/\/[^()<>\s]*?)\]((\s|.)+?)\[\/url\]/i",
    '<a href="\\1" target="_blank" rel="nofollow">\\2</a>', $s);

  // [url=http://www.example.com]Text[/url]
  $s = preg_replace(
    "/\[url=((?:\\.|\\/)[^()<>\s]*?)\]((\s|.)+?)\[\/url\]/i",
    '<a href="\\1" target="_blank" rel="nofollow">\\2</a>', $s);

  // [nurl=http://www.example.com]Text[/url]
  $s = preg_replace(
    "/\[nurl=((?:http|ftp|https|ftps|irc):\/\/[^()<>\s]+?)\]((\s|.)+?)\[\/nurl\]/i",
    '<a target="_blank" href="\\1">\\2</a>', $s);

  // Light box image url
  $s = preg_replace(
    '/\[iurl=((?:http|ftp|https|ftps|irc):\/\/[^()<>\s]+?)\]{([\w\d\s\pL,]*?)}((\s|.)+?)\[\/iurl\]/iu',
    '<a class="lbimg" href="\\1" title="\\2" target="_blank">\\3</a>', $s);

  $s = preg_replace(
    "/\[iurl=((?:http|ftp|https|ftps|irc):\/\/[^()<>\s]+?)\]((\s|.)+?)\[\/iurl\]/i",
    '<a class="lbimg" href="\\1" target="_blank" rel="nofollow">\\2</a>', $s);


  $patterns = array();
  $replacements = array();

  // matches a [url]xxxx://www.phpbb.com[/url] code..
  $patterns[] = "#\[url\]([\w]+?://([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
  $replacements[] = '<a href="\1" target="_blank" class="postlink" rel="nofollow">\1</a>';

  // [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
  $patterns[] = "#\[url\]((www|ftp)\.([\w\#$%&~/.\-;:=,?@\]+]+|\[(?!url=))*?)\[/url\]#is";
  $replacements[] = '<a href="http://\1" target="_blank" class="postlink" rel="nofollow">\1</a>';

  // [url=xxxx://www.phpbb.com]phpBB[/url] code..
  $patterns[] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
  $replacements[] = '<a href="\1" target="_blank" class="postlink" rel="nofollow">\2</a>';

  // [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
  $patterns[] = "#\[url=((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
  $replacements[] = '<a href="http://\1" target="_blank" class="postlink" rel="nofollow">\3</a>';

  foreach($patterns AS $pattern_i => $pattern) {
    $s = preg_replace($pattern, $replacements[$pattern_i], $s);
  }

  // [anchor]alphanum[/anchor]
  $s = preg_replace(
    "/\[anchor\]([a-zA-Z0-9\.]+?)\[\/anchor\]/i",
    '<a name="\\1"></a>', $s);

  // [url=#alphanum]Text[/url]
  $s = preg_replace(
    "/\[url=#([a-zA-Z0-9\.]+?)\]((\s|.)+?)\[\/url\]/i",
    "<a href=\"#\\1\">\\2</a>", $s);

  if ($strip_html) $s = nl2br($s);

  $s = bbencode_first_pass_pda($s, $uid, '[pre]', '[/pre]', '', false, 'pre_remove_br', false);
  $s = str_replace(array("[pre:$uid]","[/pre:$uid]"), array('<pre>','</pre>'), $s);

  $s = make_clickable($s);
  $s = format_smiles($s);

  $s = substr($s, 1);

  $s = tag_code_post($s,$uid);

  return $s;
}

function format_smiles($s) {
  include_once $GLOBALS['SETTINGS_PATH'] . 'sml_cache_all';
  include_once $GLOBALS['SETTINGS_PATH'] . 'smiles_with_sizes_array_php';
  global $smilies;
  global $smilies_with_size;
  reset($smilies);
  reset($smilies_with_size);
  while (list($code, $url) = each($smilies)) {
    $s = str_replace($code, '<img src="/pic/smilies/'.$url.'" alt="' . esc_html($code) . '">', $s);
  }

  /** url_with_size format => array('name' => '', 'size' => array('width'=>1, 'height' =>1)) */
  while (list($code, $url_with_size) = each($smilies_with_size)) {
    list($url, $size)     = array($url_with_size['name'], $url_with_size['size']);
    list($width, $height) = array($size['width'], $size['height']);
    $image = sprintf('<img src="/pic/smilies/%s" alt="%s" height="%s" width="%s">', $url, $code, $width, $height);
    $s = str_replace($code, $image, $s);
  }

  return $s;
}

function make_clickable($text)
{
  $text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

  // pad it with a space so we can match things at the start of the 1st line.
  $ret = ' ' . $text;

  // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
  // xxxx can only be alpha characters.
  // yyyy is anything up to the first space, newline, comma, double quote or <
  $ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);

  // matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
  // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
  // zzzz is optional.. will contain everything up to the first space, newline,
  // comma, double quote or <.
  $ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a>", $ret);


  // Remove our padding..
  $ret = substr($ret, 1);

  return($ret);
}


// In pre we don't need <br> since \n is enough, revert nl2br
function pre_remove_br($s) {
  return str_replace('<br />','',$s);
}

// This should be called at the beggining of all the tags
function tag_code_pre($s,$uid) {
  $to_replace = array('_simbol_sub_'=>'_','_simbol_double_points_'=>':','_simbol_point_virg_'=>';',
            '_simbol_open_paran_'=>'[','_simbol_close_paran_'=>']','');
  $replace_what = array();
  $replace_with = array();

  foreach ($to_replace AS $replace_simbol=>$replace_char) {
    $replace_what[] = $replace_char;
    $replace_with[] = $uid.$replace_simbol.$uid;
  }
  return str_replace($replace_what,$replace_with,$s);
}

// This should be called at the end of all the tags
function tag_code_post($s,$uid) {
  $to_replace = array('_simbol_sub_'=>'_','_simbol_double_points_'=>':','_simbol_point_virg_'=>';',
            '_simbol_open_paran_'=>'[','_simbol_close_paran_'=>']');
  $replace_what = array();
  $replace_with = array();

  foreach ($to_replace AS $replace_simbol=>$replace_char) {
    $replace_what[] = $uid.$replace_simbol.$uid;
    $replace_with[] = $replace_char;
  }
  $replace_what[] = "[code:{$uid}]";
  $replace_with[] = '[code]';
  $replace_what[] = "[/code:{$uid}]";
  $replace_with[] = '[/code]';
  $replace_what[] = '  ';
  $replace_with[] = '&nbsp; ';

  return str_replace($replace_what,$replace_with,$s);
}


/**
 * $text - The text to operate on.
 * $uid - The UID to add to matching tags.
 * $open_tag - The opening tag to match. Can be an array of opening tags.
 * $close_tag - The closing tag to match.
 * $close_tag_new - The closing tag to replace with.
 * $mark_lowest_level - boolean - should we specially mark the tags that occur
 *          at the lowest level of nesting? (useful for [code], because
 *            we need to match these tags first and transform HTML tags
 *            in their contents..
 * $func - This variable should contain a string that is the name of a function.
 *        That function will be called when a match is found, and passed 2
 *        parameters: ($text, $uid). The function should return a string.
 *        This is used when some transformation needs to be applied to the
 *        text INSIDE a pair of matching tags. If this variable is FALSE or the
 *        empty string, it will not be executed.
 * If open_tag is an array, then the pda will try to match pairs consisting of
 * any element of open_tag followed by close_tag. This allows us to match things
 * like [list=A]...[/list] and [list=1]...[/list] in one pass of the PDA.
 *
 * NOTES: - this function assumes the first character of $text is a space.
 *        - every opening tag and closing tag must be of the [...] format.
 */
function bbencode_first_pass_pda($text, $uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false)
{
  $open_tag_count = 0;

  $orig_text = $text;

  if (!$close_tag_new || ($close_tag_new == ''))
  {
    $close_tag_new = $close_tag;
  }

  $close_tag_length = strlen($close_tag);
  $close_tag_new_length = strlen($close_tag_new);
  $uid_length = strlen($uid);

  $use_function_pointer = ($func && ($func != ''));


  $stack = array();

  if (is_array($open_tag))
  {
    if (0 == count($open_tag))
    {
      // No opening tags to match, so return.
      return $text;
    }
    $open_tag_count = count($open_tag);
  }
  else
  {
    // only one opening tag. make it into a 1-element array.
    $open_tag_temp = $open_tag;
    $open_tag = array();
    $open_tag[0] = $open_tag_temp;
    $open_tag_count = 1;
  }

  $open_is_regexp = false;

  if ($open_regexp_replace)
  {
    $open_is_regexp = true;
    if (!is_array($open_regexp_replace))
    {
      $open_regexp_temp = $open_regexp_replace;
      $open_regexp_replace = array();
      $open_regexp_replace[0] = $open_regexp_temp;
    }
  }

  if ($mark_lowest_level && $open_is_regexp)
  {
    die('error');
  }

  // Start at the 2nd char of the string, looking for opening tags.
  $curr_pos = 1;
  $_substr_times = 0;
  while ($curr_pos && ($curr_pos < strlen($text)))
  {
    $curr_pos = strpos($text, "[", $curr_pos);

    // If not found, $curr_pos will be 0, and the loop will end.
    if ($curr_pos)
    {
      // We found a [. It starts at $curr_pos.
      // check if it's a starting or ending tag.
      $found_start = false;
      $which_start_tag = "";
      $start_tag_index = -1;

      for ($i = 0; $i < $open_tag_count; $i++)
      {
        // Grab everything until the first "]"...
        $possible_start = substr($text, $curr_pos, strpos($text, ']', $curr_pos + 1) - $curr_pos + 1);
        $_substr_times++; // Max 100
        if ($_substr_times > 10000) break;
        //
        // We're going to try and catch usernames with "[' characters.
        //
        if( preg_match('#\[quote=\\\&quot;#si', $possible_start, $match) && !preg_match('#\[quote=\\\&quot;(.*?)\\\&quot;\]#si', $possible_start) )
        {
          // OK we are in a quote tag that probably contains a ] bracket.
          // Grab a bit more of the string to hopefully get all of it..
          if ($close_pos = strpos($text, '&quot;]', $curr_pos + 14))
          {
            if (strpos(substr($text, $curr_pos + 14, $close_pos - ($curr_pos + 14)), '[quote') === false)
            {
              $possible_start = substr($text, $curr_pos, $close_pos - $curr_pos + 7);
            }
          }
        }

        // Now compare, either using regexp or not.
        if ($open_is_regexp)
        {
          $match_result = array();
          if (preg_match($open_tag[$i], $possible_start, $match_result))
          {
            $found_start = true;
            $which_start_tag = $match_result[0];
            $start_tag_index = $i;
            break;
          }
        }
        else
        {
          // straightforward string comparison.
          if (0 == strcasecmp($open_tag[$i], $possible_start))
          {
            $found_start = true;
            $which_start_tag = $open_tag[$i];
            $start_tag_index = $i;
            break;
          }
        }
      }

      if (sizeof($stack) > 5) return $orig_text;

      if ($found_start)
      {

        // We have an opening tag.
        // Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
        $match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);

        array_push($stack, $match);

        //
        // Rather than just increment $curr_pos
        // Set it to the ending of the tag we just found
        // Keeps error in nested tag from breaking out
        // of table structure..
        //
        $curr_pos += strlen($possible_start);
      }
      else
      {
        // check for a closing tag..
        $possible_end = substr($text, $curr_pos, $close_tag_length);
        if (0 == strcasecmp($close_tag, $possible_end))
        {
          // We have an ending tag.
          // Check if we've already found a matching starting tag.
          if (sizeof($stack) > 0)
          {
            // There exists a starting tag.
            $curr_nesting_depth = sizeof($stack);
            // We need to do 2 replacements now.
            $match = array_pop($stack);
            $start_index = $match['pos'];
            $start_tag = $match['tag'];
            $start_length = strlen($start_tag);
            $start_tag_index = $match['index'];

            if ($open_is_regexp)
            {
              $start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
            }

            // everything before the opening tag.
            $before_start_tag = substr($text, 0, $start_index);

            // everything after the opening tag, but before the closing tag.
            $between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);

            // Run the given function on the text between the tags..
            if ($use_function_pointer)
            {
              $between_tags = $func($between_tags, $uid);
            }

            // everything after the closing tag.
            $after_end_tag = substr($text, $curr_pos + $close_tag_length);

            // Mark the lowest nesting level if needed.
            if ($mark_lowest_level && ($curr_nesting_depth == 1))
            {
              /*if ($open_tag[0] == '[code]')
              {
                $code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
                $code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
                $between_tags = preg_replace($code_entities_match, $code_entities_replace, $between_tags);
              }*/
              $text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$uid]";
              $text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$uid]";
            }
            else
            {
              if ($open_tag[0] == '[code]' && false)
              {
                $text = $before_start_tag . '&#91;code&#93;';
                $text .= $between_tags . '&#91;/code&#93;';
              }
              else
              {
                if ($open_is_regexp)
                {
                  $text = $before_start_tag . $start_tag;
                }
                else
                {
                  $text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$uid]";
                }
                $text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$uid]";
              }
            }

            $text .= $after_end_tag;

            // Now.. we've screwed up the indices by changing the length of the string.
            // So, if there's anything in the stack, we want to resume searching just after it.
            // otherwise, we go back to the start.
            if (sizeof($stack) > 0)
            {
              $match = array_pop($stack);
              $curr_pos = $match['pos'];
//              bbcode_array_push($stack, $match);
//              ++$curr_pos;
            }
            else
            {
              $curr_pos = 1;
            }
          }
          else
          {
            // No matching start tag found. Increment pos, keep going.
            ++$curr_pos;
          }
        }
        else
        {
          // No starting tag or ending tag.. Increment pos, keep looping.,
          ++$curr_pos;
        }
      }
    }
  } // while

  return $text;

} // bbencode_first_pass_pda()