<?php
  include("include/bittorrent.php");
  loggedinorreturn();
  $messagesperpage = 25;

  if (isset($_GET['recount_unread'])) {
      expirePmCache($CURUSER['id']);
      redirect('/inbox.php');
  }

  if (isset($_GET['recount'])) {
      userPmsCountRegenerate($CURUSER['id']);
      expirePmCache($CURUSER['id']);
      redirect('/inbox.php');
  }


  if (isset($_GET['search']) && $isPrivMessSearchEnabled) // Search
  {
    stdhead(__('Căutare'));
    print("<table class='main mCenter' width=860 border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>\n");
    echo '<div align="center"><h1 style="display:inline;">'. __('Căutare') .'</h1></div>';

    //The Sentbox links bar
    print("<div align=center style=\"margin-top:8px;\"><a href=friends.php>". __('Prieteni/Lista neagră') ."</a>&nbsp;|&nbsp;<a href=sendmessage.php>{$lang['inbox_compose']}</a>&nbsp;|&nbsp;<a href=inbox.php>". __('Primite') ." ({$CURUSER['received']})</a>&nbsp;|&nbsp;<a href=inbox.php?out=1>". __('Trimise') ." ({$CURUSER['sended']})</a>&nbsp;|&nbsp; <a href=./inbox_archive.php?out=1>". __('Arhivă') ."</a> </div>\n");

    //params
    $keywords = trim(@$_GET["keywords"]);
    $location = @$_GET['location'];
    $search_username = trim(@$_GET['username']);


    ?>

  <script type="text/javascript">
  $j(function($)
  {
    var $p = $('#searchMessages');
  var $l = $p.find('#location').change(function()
    {
      showHide($(this).val());
    });

    function showHide(loc)
    {
      loc = loc||'both';
      $l.val(loc);
      $p.find('td.u span').each(function()
      {
        var $v=$(this);
        if($v.hasClass('u'+loc))
          $v.fadeIn();
        else
          $v.hide(50);
      });
    }

    showHide('<?=esc_html($location)?>');
  });
  </script>
  <br />
  <br />
  <form id="searchMessages" method="get" action="/inbox.php">
    <input type="hidden" name="search" value="" />
    <table border="1" align="center" cellpadding="5" cellspacing="0">
      <tbody>
     <?php if ($isPrivMessSearchByKeywordEnabled) {?>
        <tr>
          <td><?=__('Cuvintele de căutare')?></td>
          <td align="left">
            <input type="text" size="55" name="keywords" value="<?=esc_html($keywords)?>">
            <br> <font class="small" size="-1"><?=__('Puteţi introduce unul sau mai multe cuvinte.')?></font>
          </td>
        </tr>
    <?php } //if ?>
        <tr>
          <td><?=__('În mesaje')?></td>
          <td>
            <select name="location" id="location">
              <option value="both">- <?=__('Toate')?></option>
              <option value="in"><?=__('Primite')?></option>
              <option value="out"><?=__('Trimise')?></option>
            </select>
          </td>
        </tr>
        <tr>
          <td class="u"><span class="uin uboth"><?=__('De la')?></span> <span class="uboth">/</span> <span class="uout uboth"><?=__('Către')?></span></td>
          <td>
            <input type="text" name="username" value="<?=esc_html($search_username)?>">
            <!-- <br> <font class="small" size="-1"><?=__('Nu e neapărat să specifici vreun nume de utilizator')?></font> !-->
          </td>
        </tr>
        <tr>
          <td align="center" colspan="2"><input type="submit" value="Caută" class="btn"></td>
        </tr>
      </tbody>
    </table>
  </form>


  <?php
      if ($keywords != "" || $search_username != "") {
        $search_userid = 0;
        if ($search_username != '') {
          $search_userid = (int)fetchOne('SELECT id FROM users WHERE username=?', array($search_username) );

          if ($search_userid <= 0) {
            stderr(__('Informaţie'),__('Nu am găsit utilizatorul dat.'), true);
          }
        }

        $select = 'SELECT messages.id, messages.msg, messages.receiver, messages.sender, messages.added, messages.unread';
        $from = 'FROM messages';
        $join = '';
        $where = array();


        if($keywords != "" && $isPrivMessSearchByKeywordEnabled) {
          $search_str = $keywords;
          $search_str = preg_replace('/\s\s+/', ' ', $search_str);
          $search_str = trim($search_str);
          $search_str = "%$search_str%";

          $where[] = sqlEscapeBind("(`msg` LIKE ?)", array($search_str));
        }

        if($location != "" && ($location=='in' || $location=='out' || $location=='both')) {
          if ($location=='both') {
            $qLocation = "'in','out','both'";

            $select .= ', u1.username as ureceiver, u2.username as usender, u1.gender AS genderReceiver, u2.gender AS genderSender';
            $join .= 'LEFT JOIN users u1 ON messages.receiver = u1.id
                      LEFT JOIN users u2 ON messages.sender = u2.id ';

            $qUser = "(receiver = {$CURUSER["id"]} or sender = {$CURUSER["id"]})";
            if ($search_userid > 0) {
              $qUser = sqlEscapeBind(
                '((`sender`=:curuser_id AND `receiver` = :search_userid) OR
                  (`sender`=:search_userid AND `receiver`=:curuser_id))',
                array('curuser_id' => $CURUSER["id"], 'search_userid' => $search_userid)
              );
            }
          } else {
            $qLocation = sqlEscapeBind("?, 'both'", $location);
            $qHe = '';

            if ($location=='in') {
              $select .= ', u2.username as usender, u2.gender AS genderSender';
              $join .= 'LEFT JOIN users u2 ON messages.sender = u2.id ';

              if ($search_userid>0)
                $qHe = "AND (sender = $search_userid)";

              $qMe = "(receiver = {$CURUSER["id"]})";
            } else {
              $select .= ', u1.username as ureceiver, u1.gender AS genderReceiver';
              $join .= 'LEFT JOIN users u1 ON messages.receiver = u1.id';

              if ($search_userid > 0)
                $qHe = sqlEscapeBind("AND (receiver = ?)", array($search_userid));

              $qMe =  sqlEscapeBind("(sender = ?)", array($CURUSER["id"]));
            }

            $qUser = $qMe.' '.$qHe;
          }

          if($qUser)
            $where[] = ''.$qUser;
          $where[] = "(location IN ($qLocation))";
        }

      if(count($where))
          $where = 'WHERE '. implode(" AND ", $where);


      $searchRows=0;

      $q = "$select $from $join $where ORDER BY id DESC ";

      //Nu-i chiar buna metoda data, facem acelasi query de 2ori
      $searchRows = (int)trim(@$_GET['searchRows']);
      if ($searchRows <= 0)
      {
        $res = q($q);
        $searchRows = mysql_num_rows($res);
      }


      if ($searchRows == 0)
        stdmsg(__('Informaţie'),__('Nu am găsit nici un mesaj.'));
      else {
        list($null, $null, $limit) = pager($messagesperpage, $searchRows, '');

        $q .= $limit;
        $res = q($q);


        if($isPrivMessSearchByKeywordEnabled)
            $pageFormater[] = 'keywords='.esc_html($keywords);
        $pageFormater[] = 'location='.esc_html($location);
        $pageFormater[] = 'username='.esc_html($search_username);
        $pageFormater[] = 'searchRows='.esc_html($searchRows);
        $pageFormater[] = '';

        $pageFormat = "/inbox.php?search=&". implode('&', $pageFormater);

          list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, $searchRows, $pageFormat);


          echo $pagertop;
          echo '<div id="messages">';

          while ($arr = mysql_fetch_assoc($res))
          {
            if($arr["sender"] == $CURUSER["id"])
            {

              if (is_valid_id($arr["receiver"])) {
                if($arr["ureceiver"] != '')
              $sender = "<a href=userdetails.php?id=" . $arr["receiver"] .(getUserGenderColor($arr['genderReceiver'])).">" . $arr["ureceiver"] . "</a>";
                else
                  $sender = "[Deleted]";
              } else
                $sender = "System";

              $elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]));
              $new = ($arr["unread"] == "yes")?"<b>(<font color=red>Unread!</font>)</b>":"";

              $additional = isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>=0?'&page='.$_GET['page']:'';
        ?>
  <p>
  <table width=100% border=1 cellspacing=0 cellpadding=10 class='pmMessage out'>
    <tr>
      <td class=text><?=__('Către')?> <b><?=$sender?> </b> <?=__('la')?> <?=$arr["added"]?>
        (<?=$elapsed?>) GMT <?=$new?>

        <p>
          <table class=main width=100% border=1 cellspacing=0 cellpadding=10>
            <tr>
              <td class=text>
                  <?php print(format_comment($arr["msg"])); ?>
              </td>
            </tr>
          </table>
        </p>
        <p>
          <table width=100% border=0>
            <tr>
              <td class=embedded>
                 <a href=deletemessage.php?id=<?=$arr["id"]?>&type=out<?=$additional?>><b><?=__('Şterge')?> </b></a>
              </td>
            </tr>
          </table>
        </p>
      </td>
    </tr>
  </table>
  </p>
  <?php
          } else {

            if (is_valid_id($arr["sender"]))
            {
              if($arr["usender"] != '')
              $sender = "<a href=userdetails.php?id=" . $arr["sender"] .(getUserGenderColor($arr['genderSender'])) .">" . $arr["usender"] . "</a>";
              else
              $sender = "[Deleted]";
            } else
            $sender = "System";


            $elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]));
            $new = ($arr["unread"] == "yes")?"<b>(<font color=red>NEW!</font>)</b>":"";

            $additional = isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>=0?'&page='.$_GET['page']:'';
  ?>
  <p>
  <table width=100% border=1 cellspacing=0 cellpadding=10 class=pmMessage>
    <tr>
      <td class=text><?=__('De la')?> <b><?=$sender?> </b> <?=__('la')?> <?=$arr["added"]?> (<?=$elapsed?>) GMT <?=$new?>

        <p>
          <table class=main width=100% border=1 cellspacing=0 cellpadding=10>
            <tr>
              <td class=text>
                <?php print(format_comment($arr["msg"])); ?>
              </td>
            </tr>
          </table>
        </p>
        <p>
          <table width=100% border=0>
            <tr>
              <td class=embedded>
                      <?php if ($arr["sender"]) { ?> <a class='reply' href=sendmessage.php?receiver=<?=$arr["sender"]?>&replyto=<?=$arr["id"]?>><b><?=__('Răspunde')?></b></a>
                      <?php } else { ?>
                        <font class=gray><b><?=__('Răspunde')?></b></font>
                      <?php }?>
                      | <a href=deletemessage.php?id=<?=$arr["id"]?>&type=in<?=$additional?>><b><?=__('Şterge')?></b></a>

                      <span class='success'></span>
                    </td>
            </tr>
          </table>
        </p>
      </td>
    </tr>
  </table>
  </p>
  <?php
          }//^^ if

        }//while

        echo '</div>';//messages
        echo $pagerbottom;
        }// if $searchRows == 0

      } else if(isset($location)) {//if ($keywords != "" || $search_username !="")
        stdmsg(__('Informaţie'),__('Nu ați specificat nici un criteriu de căutare'));
      }

    } else


  // Sentbox
  if (isset($_GET['out'])) {
    stdhead(__('Trimise'));
    print("<table class='main mCenter' width=860 border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>\n");

    list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, 1000, "");

    // Last 7 days
    $res = q("SELECT messages.id, messages.msg, messages.receiver, messages.added, messages.unread, users.username, users.gender FROM messages
             LEFT JOIN users ON messages.receiver = users.id
         WHERE sender=" . $CURUSER["id"] . "  AND location IN ('out','both') ORDER BY id DESC $limit") or die("barf!");

    $sentbox = $CURUSER['sended'];

    list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, $sentbox, "inbox.php?out=1&");

    echo '<div align="center"><h1 style="display:inline;">'. __('Trimise') .'</h1> (',$sentbox,'/100) [<a href="deletemessage.php?deleteall=1&type=out">'. __('şterge toate') .'</a>]</div>';

    //Calculate the progress bar percentage and construct the table for it
    $used = floor(($sentbox / 100) * 100);
    $free = 100 - $used;
    echo '<table style="border:2px groove" align="center" cellpadding="0" cellspacing="1" border="0" width="200">',"\n";
    echo '<tr>',"\n";
    if ($used == 100) echo '<td width="',$used,'" style="background-color:red; font-size:10px">&nbsp;</td>';
    elseif ($free == 100) echo '<td width="',$free,'" style="background-color:green; font-size:10px">&nbsp;</td>';
    else {
      echo '<td width="',$used,'" style="background-color:red; font-size:10px">&nbsp;</td>';
      echo '<td width="',$free,'" style="background-color:green; font-size:10px">&nbsp;</td>';
    }
    echo '</tr>',"\n";
    echo '</table>',"\n";
    //The Sentbox links bar
    print("<div align=center style=\"margin-top:8px;\"><a href=friends.php>". __('Prieteni/Lista neagră') ."</a>&nbsp;|&nbsp;<a href=sendmessage.php>{$lang['inbox_compose']}</a>&nbsp;|&nbsp;<a href=inbox.php>". __('Primite') ." ({$CURUSER['received']})</a>&nbsp;|&nbsp; ".(($isPrivMessSearchEnabled)?" <a href=/inbox.php?search><b>". __('Căutare') ."</b></a>&nbsp;|&nbsp; ":'')."<a href=./inbox_archive.php?out=1>". __('Arhivă') ."</a> </div>\n");

    if ($sentbox != 0) {
      echo $pagertop;
      echo '<div id="messages">';
      while ($arr = mysql_fetch_assoc($res)) {
        if ($arr["username"] != '') $receiver = "<a href=userdetails.php?id=" . $arr["receiver"] . (getUserGenderColor($arr['gender'])) .">" . $arr["username"] . "</a>";
        else $receiver = '[Deleted]';
        $elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]));
        print("<p><table width=100% border=1 cellspacing=0 cellpadding=10 class=pmMessage><tr><td class=text>\n");
        print(__('Către')." <b>$receiver</b> ". __('la') ."\n" . $arr["added"] . " ($elapsed) GMT\n");
        if (get_user_class() >= UC_POWER_USER && $arr["unread"] == "yes") {
          print("<b>(<font color=red>Unread!</font>)</b>");
        }

        //PM View Conversation
        if (is_valid_id($arr["receiver"]))
          print('<a href="./inbox.php?search=&location=both&username='. $arr["username"] .'" class="pmViewAll">['. __('vezi conversația') .']</a>');

        print("<p><table class=main width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>\n");
        print(format_comment($arr["msg"]));
        print("</td></tr></table></p>\n<p>");
        print("<table width=100%  border=0><tr><td class=embedded>\n");
        $additional = isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>=0?'&page='.$_GET['page']:'';
        print("<a href=deletemessage.php?id=" . $arr["id"] . "&type=out{$additional}><b>". __('Şterge') ."</b></a></td>\n");
        print("</tr></table></tr></table></p>\n");
      }
      echo '</div>';//messages
      echo $pagerbottom;
    } else {
      stdmsg(__('Informaţie'),__('Căsuţa poştală este goală!'));
    }
  }
  // Inbox
  else {
    stdhead(__('Primite'));
    print("<table class='main mCenter' width=860 border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>\n");
    $outmessages = $CURUSER['sended'];
    $inbox = $CURUSER['received'];
    echo '<div align="center"><h1 style="display:inline;">'. __('Primite') .'</h1> (',$inbox,'/100) [<a href="deletemessage.php?deleteall=1&type=in">'. __('şterge toate') .'</a>]</div>';

    //Calculate the progress bar percentage and construct the table for it
    $used = floor(($inbox / 100) * 100);
    $free = 100 - $used;
    echo '<table style="border:2px groove" align="center" cellpadding="0" cellspacing="1" border="0" width="200">',"\n";
    echo '<tr>',"\n";
    if($used == 100) echo '<td width="',$used,'" style="background-color:red; font-size:10px">&nbsp;</td>';
    elseif($free == 100) echo '<td width="',$free,'" style="background-color:green; font-size:10px">&nbsp;</td>';
    else {
      echo '<td width="',$used,'" style="background-color:red; font-size:10px">&nbsp;</td>';
      echo '<td width="',$free,'" style="background-color:green; font-size:10px">&nbsp;</td>';
    }
    echo '</tr>',"\n";
    echo '</table>',"\n";
    //The inbox links bar
    print("<div align=center style=\"margin-top:8px;\"><a href=friends.php>". __('Prieteni/Lista neagră') ."</a>&nbsp;|&nbsp;<a href=sendmessage.php>{$lang['inbox_compose']}</a>&nbsp;|&nbsp;<a href=inbox.php?out=1>". __('Trimise') ." ({$outmessages})</a>&nbsp;|&nbsp; ".(($isPrivMessSearchEnabled)?" <a href=/inbox.php?search><b>". __('Căutare') ."</b></a>&nbsp;|&nbsp; ":'') . " <a href=./inbox_archive.php>". __('Arhivă') ."</a></div>\n");

    list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, 1000, "");

    $res = q("SELECT messages.id, messages.sender, messages.receiver, messages.added, messages.unread, messages.msg, users.username, users.gender
            FROM messages LEFT JOIN users ON messages.sender = users.id
              WHERE receiver=" . $CURUSER["id"] . " AND location IN ('in','both') ORDER BY id DESC $limit") or die("barf!");
    if (mysql_num_rows($res) == 0) {
      stdmsg(__('Informaţie'),__('Căsuţa poştală este goală!'));
      if ($CURUSER['unread'] > 0) {
        // In order to fix bugs when messages were deleted manually
        userPmsCountRegenerate($CURUSER['id']);
        expirePmCache($CURUSER['id']);
      }
    } else {
      list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, $inbox, 'inbox.php?');
      echo $pagertop;
      echo '<div id="messages">';
      $cachePmToExpire = false;
      while ($arr = mysql_fetch_assoc($res))
      {
        if (is_valid_id($arr["sender"]))
        {
          if($arr["username"] != '') $sender = "<a href=userdetails.php?id=" . $arr["sender"] . (getUserGenderColor($arr['gender'])) .">" . $arr["username"] . "</a>";
          else $sender = "[Deleted]";
        }
        else
          $sender = "System";
        $elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]));
        print("<p><table width=100% border=1 cellspacing=0 cellpadding=10 class=pmMessage><tr><td class=text>\n");
        print(__('De la') ." <b>$sender</b> ". __('la') ."\n" . $arr["added"] . " ($elapsed) GMT\n");
        if ($arr["unread"] == "yes")
        {
          print("<b>(<font color=red>NEW!</font>)</b>");
          q("UPDATE messages SET unread='no' WHERE id=" . $arr["id"] . ' AND receiver='.$arr['receiver']);
          $cachePmToExpire = true;
        }

      //PM View Conversation
      if (is_valid_id($arr["sender"]))
        print('<a href="./inbox.php?search=&location=both&username='. $arr["username"] .'" class="pmViewAll">['. __('vezi conversația') .']</a>');

        print("<p><table class=main width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>\n");
        print(format_comment($arr["msg"]));
        print("</td></tr></table></p>\n<p>");
        print("<table width=100%  border=0><tr><td class=embedded>\n");
        $additional = isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>=0?'&page='.$_GET['page']:'';
        print( ($arr["sender"] ? "<a class='reply' href=sendmessage.php?receiver=" . $arr["sender"] . "&replyto=" . $arr["id"] .
          "><b>". __('Răspunde') ."</b></a>" : "<font class=gray><b>". __('Răspunde') ."</b></font>") .
          " | <a href=deletemessage.php?id=" . $arr["id"] . "&type=in{$additional}><b>". __('Şterge') ."</b></a> <span class='success'></span></td>\n");
        print("</tr></table></tr></table></p>\n");
      }
      if ($cachePmToExpire) {
        expirePmCache($CURUSER['id']);
      }
      // All readed
      if ($cachePmToExpire == false && $CURUSER['unread']) {
        expirePmCache($CURUSER['id']);
      }
      echo '</div>';//messages
     echo $pagerbottom;
    }
  }
  print("</td></tr></table>\n");

  echo '<script src="./js/inbox.js?v=10"></script>';

  stdfoot();
?>
