<?php
if (!isset($CURUSER)) die();

if ( get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class() ) {
  begin_frame("Edit User", true);
  print("<form method=post action=modtask.php>\n");
  print("<input type=hidden name='action' value='edituser'>\n");
  print("<input type=hidden name='userid' value='$id'>\n");
  print("<input type=hidden name='returnto' value='userdetails.php?id=$id'>\n");
  print('<br><table id="userdetails_modform" class=main border=1 cellspacing=0 cellpadding=5>');
  print("<tr><td class=rowhead>Title</td><td colspan=2 align=left><input id=title type=text style='width: 60%' name=title value=\"" . esc_html($user['title']) . "\"> <span id=\"TitleLen\"></span> caractere<br></td></tr>\n");
	$avatar = esc_html($user["avatar"]);
  //Avatar
  echo '<tr><td class=rowhead>'. __('Avatar') .'</td><td colspan=2 align=left><input type=radio name=avatar value=yes',
  	    ($user['avatar'] == 'yes')?' checked':'', '>'. __('Da') .' <input type=radio name=avatar value=no', ($user['avatar'] != 'yes')?' checked':'','>'. __('Nu') .'</tr>',"\n";


 $upload = mksize($user['uploaded']);
 print("<tr><td class=rowhead>". __('Încărcat') ."</td><td colspan=2 align=left><input type=text size=15 name=uploaded id=uploaded value=\"$upload\"> <span id=\"LowerUploadByTwenty\" class=\"lnk\"><b>-20%</b></span> puteţi doar micşora, de mărit nu se poate</td></tr>\n");
 $download = mksize($user['downloaded']);
 print("<tr><td class=rowhead>". __('Descărcat') ."</td><td colspan=2 align=left><input type=text size=15 name=downloaded value=\"$download\"> puteţi doar micşora, de mărit nu se poate</td></tr>\n");
	// we do not want mods to be able to change user classes or amount donated...
	if ($CURUSER["class"] < UC_ADMINISTRATOR)
	  print("<input type=hidden name=donor value=$user[donor]>\n");
	else
	{
	  echo '<tr><td class=rowhead>'. __('Donor') .'</td><td colspan=2 align=left><input type=radio name=donor value=yes', ($user["donor"] == "yes" ? ' checked' : ''),'>'. __('Da') .' <input type=radio name=donor value=no',($user["donor"] != "yes" ? " checked" : ""),'>'. __('Nu') .'</td></tr>',"\n";
	}

	/**
		User icons On/Off
	**/
	if ($CURUSER["class"] == UC_SYSOP) {
		foreach( $user_icons AS $flag => $flag_data ) {
			if ( !$flag_data['canSet'] || !$flag_data['award'] )
				continue;
			$current_flag = have_flag( $flag, $user["user_opt"] );
			printf ('<tr><td class="rowhead">%s</td><td colspan="2"><input name="%s" value="yes" %s type=radio>%s <input name="%s" value="no" %s type=radio>%s</td></tr>',
				__($flag_data['title']), $flag_data['name'], ($current_flag?'checked':''), __('Da'), $flag_data['name'], ($current_flag?'':'checked'), __('Nu'));
		}
	}

	if ($CURUSER["class"] >= UC_ADMINISTRATOR && ($user['user_opt'] & $conf_user_opt['spanked'])) {
		printf ('<tr><td class="rowhead">%s</td><td colspan="2"><input type="checkbox" value="no" name="spanked"></td>',
			__('Dezactivează bătaia'));
	}

	if (get_user_class() == UC_MODERATOR && $user["class"] > UC_VIP)
	  printf("<input type=hidden name=class value=$user[class]\n");
	else {
	  print("<tr><td class=rowhead>". __('Clasă') ."</td><td colspan=2 align=left><select name=class>\n");
	  if (get_user_class() == UC_MODERATOR)
	    $maxclass = UC_VIP;
	  else
	    $maxclass = get_user_class() - 1;
	  for ($i = 0; $i <= $maxclass; ++$i) {
	  	  if (get_user_class_name($i)) print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">" . get_user_class_name($i) . "\n");
	  }
	  print("</select></td></tr>\n");
	}

	echo '<tr><td class=rowhead>'. __('Adaugă<br>comentariu') .'</td><td colspan=2>A simple text appendent to log, for other moders/admins:<br><textarea rows=3 name="usr_comment" class="textarea_mobile_long"></textarea></td></tr>';

	$modcomment = esc_html( br2nl(fetchOne('SELECT modcomment FROM users_rare WHERE id=:id', array('id'=>$user['id']))) );
	print("<tr><td class=rowhead>". __('Log') ."</td><td colspan=2 align=left><textarea rows=6 readonly class=textarea_mobile_long>$modcomment</textarea></td></tr>\n");


  // if (isAdmin()) {
  //   if (!empty($user['user_json'])) {
  //     $user_json = UserJson::initWithJson($user['id'], $user['user_json']);
  //     $userIcons = $user_json->getUserIcons();
  //   }

  //   echo renderTemplateToString("userdetails/user_icons.html.php",
  //     array("user_icons_config" => User_Icons::icons(), "user_icons" => $userIcons)
  //   );
  // }


  print("<tr><td class=rowhead>". __('Activat') ."</td><td colspan=2 align=left><input name=enabled value='yes' type=radio" . ($enabled ? " checked" : "") . ">". __('Da') ." <input name=enabled value='no' type=radio" . (!$enabled ? " checked" : "") . ">". __('Nu') ."</td></tr>\n");

	$warned = $user["warned"] == "yes";

 	print("<tr><td class=rowhead" . (!$warned ? " rowspan=2": "") . ">". __('Avertizare') ."</td>
 	<td align=left width=20%>" .
  ( $warned
  ? "<input name=warned value='yes' type=radio checked>". __('Da') ."<input name=warned value='no' type=radio>". __('Nu')
 	: __('Nu') ) ."</td>");

	if ($warned)
	{
		$warneduntil = $user['warneduntil'];
		if ($warneduntil == '0000-00-00 00:00:00')
    	print("<td align=center>(". __('nelimitat') .")</td></tr>\n");
		else
		{
    	print("<td align=center>Until $warneduntil");
	    print(" (" . mkprettytime(strtotime($warneduntil) - time()) . " to go)</td></tr>\n");
 	  }
  }
  else {
    print("<td>". __('Perioadă') ." <select name=warnlength>\n");
    print("<option value=0>------</option>\n");
    print("<option value=1>1 ". __('săptămână') ."</option>\n");
    print("<option value=2>2 ". __('săptămâni') ."</option>\n");
    print("<option value=4>4 ". __('săptămâni') ."</option>\n");
    print("<option value=8>8". __(' săptămâni') ."</option>\n");
    print("<option value=255>". __('nelimitat') ."</option>\n");
    print("</select></td></tr>\n");
    print("<tr><td colspan=2 align=left>". __('Motiv (trimis şi la utilizator în PM)') .":<br><input type=text class=textarea_mobile_long name=warnpm></td></tr>");
  }

  /// Posting ban

  if ($user['user_opt'] & $conf_user_opt['postingban']) $postingban = true;
  else $postingban = false;


   	print("<tr><td class=rowhead" . (!$postingban ? " rowspan=2": "") . ">". __('Post Ban') ."</td>
 	<td align=left width=20%>" .
  ( $postingban
  ? "<input name=postingban value='yes' type=radio checked>". __('Da') ."<input name=postingban value='no' type=radio>". __('Nu')
 	: __('Nu') ) ."</td>");


  if ($postingban) {
  	  $postingbanuntil = $user['postingbanuntil'];
  	  if ($postingbanuntil == '0000-00-00 00:00:00') {
  	  	  print("<td align=center>(". __('nelimitat') .")</td></tr>\n");
  	  } else {
  	  	  print("<td align=center>Until $postingbanuntil");
  	  	  print(" (" . mkprettytime(strtotime($postingbanuntil) - time()) . " to go)</td></tr>\n");
  	  }
  } else {
  	  print("<td>". __('Perioadă') ." <select name=postingbanlength>\n");
  	  print("<option value=0>------</option>\n");
  	  print("<option value=24>1 ". __('zi') ."</option>\n");
  	  print("<option value=72>3 ". __('zile') ."</option>\n");
  	  print("<option value=168>7". __(' zile') ."</option>\n");
  	  print("<option value=336>14". __(' zile') ."</option>\n");
  	  print("<option value=744>1 ". __('lună') ."</option>\n");
  	  print("<option value=1536>2 ". __('luni') ."</option>\n");
  	  print("<option value=2232>3 ". __('luni') ."</option>\n");
  	  print("<option value=4464>6". __(' luni') ."</option>\n");
  	  print("<option value=8928>12". __(' luni') ."</option>\n");
  	  print("<option value=255>". __('nelimitat') ."</option>\n");
  	  print("</select></td></tr>\n");
  	  print("<tr><td colspan=2 align=left>". __('Motiv (trimis şi la utilizator în PM)') .":<br><input type=text class=textarea_mobile_long name=postingbanpm></td></tr>");
  }


  // Upload disable

  if ($user['user_opt'] & $conf_user_opt['torrentsUploadBan']) $torrentsUploadBan = true;
  else $torrentsUploadBan = false;


   	print("<tr><td class=rowhead" . (!$torrentsUploadBan ? " rowspan=2": "") . ">Upload Ban</td>
 	<td align=left width=20%>" .
  ( $torrentsUploadBan
  ? "<input name=torrentsUploadBan value='yes' type=radio checked>". __('Da') ."<input name=torrentsUploadBan value='no' type=radio>". __('Nu')
 	: __('Nu') ) ."</td>");


  if ($torrentsUploadBan) {
  	  $torrentsUploadBanUntil = $user['uploadbanuntil'];
  	  if ($torrentsUploadBanUntil == '0000-00-00 00:00:00') {
  	  	  print("<td align=center>(". __('nelimitat') .")</td></tr>\n");
  	  } else {
  	  	  print("<td align=center>Until $torrentsUploadBanUntil");
  	  	  print(" (" . mkprettytime(strtotime($torrentsUploadBanUntil) - time()) . " to go)</td></tr>\n");
  	  }
  } else {
  	  print("<td>". __('Perioadă') ." <select name=torrentsUploadBanLength>\n");
  	  print("<option value=0>------</option>\n");
  	  print("<option value=168>7". __(' zile') ."</option>\n");
  	  print("<option value=336>14". __(' zile') ."</option>\n");
  	  print("<option value=744>1 ". __('lună') ."</option>\n");
  	  print("<option value=2232>3 ". __('luni') ."</option>\n");
  	  print("<option value=4464>6". __(' luni') ."</option>\n");
  	  print("<option value=8928>12". __(' luni') ."</option>\n");
  	  print("<option value=255>". __('nelimitat') ."</option>\n");
  	  print("</select></td></tr>\n");
  	  print("<tr><td colspan=2 align=left>". __('Motiv (trimis şi la utilizator în PM)') .":<br><input type=text class=textarea_mobile_long name=torrentsUploadBanPm></td></tr>");
  }




  // Download disable

  if ($user['user_opt'] & $conf_user_opt['torrentsDownloadBan']) $torrentsDownloadBan = true;
  else $torrentsDownloadBan = false;


   	print("<tr><td class=rowhead" . (!$torrentsDownloadBan ? " rowspan=2": "") . ">Download Ban</td>
 	<td align=left width=20%>" .
  ( $torrentsDownloadBan
  ? "<input name=torrentsDownloadBan value='yes' type=radio checked>". __('Da') ."<input name=torrentsDownloadBan value='no' type=radio>". __('Nu')
 	: __('Nu') ) ."</td>");


  if ($torrentsDownloadBan) {
  	  $torrentsDownloadBanUntil = $user['downloadbanuntil'];
  	  if ($torrentsDownloadBanUntil == '0000-00-00 00:00:00') {
  	  	  print("<td align=center>(". __('nelimitat') .")</td></tr>\n");
  	  } else {
  	  	  print("<td align=center>Until $torrentsDownloadBanUntil");
  	  	  print(" (" . mkprettytime(strtotime($torrentsDownloadBanUntil) - time()) . " to go)</td></tr>\n");
  	  }
  } else {
  	  print("<td>". __('Perioadă') ." <select name=torrentsDownloadBanLength>\n");
  	  print("<option value=0>------</option>\n");
  	  print("<option value=72>3 ". __('zile') ."</option>\n");
  	  print("<option value=168>7". __(' zile') ."</option>\n");
  	  print("<option value=336>14". __(' zile') ."</option>\n");
  	  print("<option value=744>1 ". __('lună') ."</option>\n");
  	  print("<option value=2232>3 ". __('luni') ."</option>\n");
  	  print("<option value=4464>6". __(' luni') ."</option>\n");
  	  print("<option value=8928>12". __(' luni') ."</option>\n");
  	  print("<option value=255>". __('nelimitat') ."</option>\n");
  	  print("</select></td></tr>\n");
  	  print("<tr><td colspan=2 align=left>". __('Motiv (trimis şi la utilizator în PM)') .":<br><input type=text class=textarea_mobile_long name=torrentsDownloadBanPm></td></tr>");
  }
  // Invite disable

  $invite_disabled = $user["user_opt"] & $conf_user_opt['invite_disabled'];
  printf ( '<tr><td class="rowhead">%s</td><td colspan="2"><input name="invite_disabled" value="yes" %s type=radio>%s <input name="invite_disabled" value="no" %s type=radio>%s</td>',
  				__('Interdicție de a invita'), // First td
  			   ($invite_disabled?'checked':''), // Yes
  			   __('Da'),
  			   ($invite_disabled?'':'checked'), // No
  			   __('Nu')
  		 );
  if (!$invite_disabled) {
  	printf ('<tr><td></td><td align="left" colspan="2">%s:<br><input type="text" class=textarea_mobile_long name="invitedisablepm"></td></tr>',__('Motiv (trimis şi la utilizator în PM)'));
  }

  // Moderator pe temele sale din forum

  $moderator_pe_temele_sale = $user["user_opt"] & $conf_user_opt['moderator_pe_tema_sa'];
  printf ( '<tr><td class="rowhead">%s</td><td colspan="2"><input name="moderator_pe_temele_sale" value="yes" %s type=radio>%s <input name="moderator_pe_temele_sale" value="no" %s type=radio>%s</td>',
  				__('Moderator pe temele sale'), // First td
  			   ($moderator_pe_temele_sale?'checked':''), // Yes
  			   __('Da'),
  			   ($moderator_pe_temele_sale?'':'checked'), // No
  			   __('Nu')
  		 );


  print("<tr><td colspan=3 align=center><input type=submit class=btn value='Okay'></td></tr>\n");
  print("</table>\n");
  print("</form>\n");
  end_frame();
}
?>
<script type="text/javascript">
  $j(document).ready(function($) {
    $("#LowerUploadByTwenty").click(function() {
      $(this).animate({ opacity: 'hide' }, "slow");
      var astr=$('#uploaded').val().split(' ');
      var newstr=(astr[0]*0.8).toFixed(2);
      $('#uploaded').val(newstr+" "+astr[1]);
    });

    titleCheck();

    $('#title').bind("change keyup", function() {
      titleCheck();
    });

    function titleCheck() {
      $('#TitleLen').text($('#title').val().length);

      if ($('#title').val().length > 25)
        $('#title').css('background-color', '#FFCFCF');
      else
        $('#title').css('background-color', '#FFF');
    }
  });
</script>