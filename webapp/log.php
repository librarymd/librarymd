<?php
  require "include/bittorrent.php";

  loggedinorreturn();

  // delete items older than a week
  $secs = 3 * 24 * 60 * 60;
  stdhead("Site log");
  q("DELETE FROM sitelog WHERE added < now() - interval 3 day");
  $res = q("SELECT added, txt FROM sitelog WHERE " . time() . " - UNIX_TIMESTAMP(added) < $secs ORDER BY added DESC");
  print("<h1>". __('Log-ul site-ului pentru ultimele 3 zile') ."</h1>\n");
  if (mysql_num_rows($res) == 0)
    print("<b>". __('Log-ul e gol') ."</b>\n");
  else
  {
    print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=colhead align=left>". __('Data') ."</td><td class=colhead align=left>". __('Ora') ."</td><td class=colhead align=left>". __('Eveniment') ."</td></tr>\n");
    while ($arr = mysql_fetch_assoc($res))
    {
    	$arr['txt'] = esc_html($arr['txt']);
       $bgcolor = '';
       if (strpos($arr['txt'],'was uploaded by')) $bgcolor = '#B0FFB5';
       elseif (strpos($arr['txt'],'was deleted by')) $bgcolor = '#FF9999';
       elseif (strpos($arr['txt'],'was edited by')) $bgcolor = '#FFD586';
       elseif (strpos($arr['txt'],'faq was updated by')) $bgcolor = '#999AFF';

       if (isset($bgcolor)) $bgcolor = " bgcolor='".$bgcolor."'";

      $date = substr($arr['added'], 0, strpos($arr['added'], " "));
      $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
      print("<tr{$bgcolor}><td>$date</td><td>$time</td><td align=left>$arr[txt]</td></tr>\n");
    }
    print("</table>");
  }
  print("<p>". __('Timpul este indicat în GMT +2') ."</p>\n");
  stdfoot();
?>
