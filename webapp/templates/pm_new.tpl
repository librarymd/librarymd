<center><h3>{lang_inbox_compose}</h3></center>
    <table class=main width=750 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
	<div align=center>

	<form method=post action=takemessage.php>
 	<table border=0 cellspacing=0 cellpadding=5 class=tdLeft>
	<tr>
	  <td style="border-right-style:none">{lang_inbox_recipient_name}:<br><input autocomplete="off" maxLength=256 size=70 name="receiver_name" value="" tabindex=1></td><td style="border-left-style: none;"><a href="users.php">{lang_inbox_see_all_users}</a></td>
	<tr>
	  <td colspan=2>{lang_inbox_message}:<br><textarea name=msg cols=80 rows=15 tabindex=2></textarea></td>
	</tr>
	<tr><td colspan=2 align=center><input type=submit tabindex=4 value="{lang_inbox_send_message}" class=btn></td></tr>
	</table>
	<input type=hidden name=receiver value={pm_to}>
	</form>
 	</div></td></tr></table>