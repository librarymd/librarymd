<?php
  require "include/bittorrent.php";

  loggedinorreturn();

  if (get_user_class() < UC_SYSOP) {
  	  die();
  }

  if ($CURUSER['id'] != 1) {
  	  die('Um?');
  }

  // delete items older than a week
  $secs = 30 * 24 * 60 * 60;
  stdhead("Moders log");
  q("DELETE FROM adminslog WHERE " . time() . " - UNIX_TIMESTAMP(added) > $secs");
  $res = q("SELECT added, txt FROM adminslog ORDER BY added DESC");
  print("<h1>Moders log</h1>\n");
  if (mysql_num_rows($res) == 0)
    print("<b>Log is empty</b>\n");
  else
  {
    print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=colhead align=left>Date</td><td class=colhead align=left>Time</td><td class=colhead align=left>Event</td></tr>\n");
    while ($arr = mysql_fetch_assoc($res))
    {
       $bgcolor = '';
       //$arr['txt'] = esc_html($arr['txt']);
       if (strpos($arr['txt'],'nabled') || strpos($arr['txt'],'added')) $bgcolor = '#B0FFB5';
	   elseif (strpos($arr['txt'],'Ban_ ') !== false) $bgcolor = '#A52A2A';
       elseif (strpos($arr['txt'],'removed') || strpos($arr['txt'],'disabled') || strpos($arr['txt'],'disabled')) $bgcolor = '#FF9999';
       elseif (strpos($arr['txt'],'arned')) $bgcolor = '#FFD586';
       elseif (strpos($arr['txt'],'Promoted') !== false) $bgcolor = '#99CDFF';
       elseif (strpos($arr['txt'],'Demoted') !== false) $bgcolor = '#FF9999';

       if (isset($bgcolor)) $bgcolor = " bgcolor='".$bgcolor."'";

      $date = substr($arr['added'], 0, strpos($arr['added'], " "));
      $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
      $arr['txt'] = format_comment($arr['txt']);
      print("<tr{$bgcolor}><td>$date</td><td>$time</td><td align=left>$arr[txt]</td></tr>\n");
    }
    print("</table>");
  }
  print("<p>Times are in GMT.</p>\n");
  stdfoot();
?>
