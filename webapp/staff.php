<?php
		require "include/bittorrent.php";

	loggedinorreturn();

	global $pic_base_url;
	stdhead(__('Echipa'));
	begin_main_frame();

	// Display Staff List to all users
	begin_frame(__('Echipa'));

	// Get current datetime
	$dt = get_date_time(time() - 180);
	// Search User Database for Moderators and above and display in alphabetical order
	$res = q(
		"SELECT users.*, u_du.last_access
		FROM users
		LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
		WHERE class>=".UC_MODERATOR. " ORDER BY username");
	$num = mysql_num_rows($res);

	while ($arr = mysql_fetch_assoc($res))
	{

		$staff_table[$arr['class']]=$staff_table[$arr['class']].
			"<td class=embedded><img src={$pic_base_url}staff/button_o".($arr[last_access]>$dt?"n":"ff")."line.gif></td>".
				"<td class=embedded><a class=altlink href=userdetails.php?id=$arr[id]>$arr[username]</a></td>".
			"<td class=embedded><a href=sendmessage.php?receiver=$arr[id]>".
				"<img src={$pic_base_url}staff/button_pm.gif border=0></a></td>".
				"<td class=embedded></td>";
		// Show 3 staff per row, separated by an empty column
		++ $col[$arr['class']];
		if ($col[$arr['class']]<=3)
				$staff_table[$arr['class']]=$staff_table[$arr['class']]."<td class=embedded>&nbsp;</td>";
		else {
				$staff_table[$arr['class']]=$staff_table[$arr['class']]."</tr><tr height=15>";
				$col[$arr['class']]=0; //pus 0, acum tabela o sa arate mai bine ;D
		}


	}
?>
<div style="padding:10px;">
<?=__('Intrebarile raspunse în <a href=faq.php><b>FAQ</b></a> (lista întrebărilor frecvente) vor fi ignorate.')?><br/>
<br/>
<?=__('Vrei sa scrii întregii echipe ? <a href="write_to_admins.php">Scrie-ne aici</a>.')?><br/>
<br/>
<?=__('Ești deținătorul <b>drepturilor de autor</b> a căruiva torrent și vreai să fie scos ? Scrie intregii echipe.')?>
</div>
<br/>
<?php if (get_user_class() >= UC_SANITAR) : ?>
<table width=850 cellspacing=0 align=center>
<tr>
	<td class=embedded colspan=19><b>SysOp</b></td>
</tr>
<tr>
	<td class=embedded colspan=19><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
	<?=$staff_table[UC_SYSOP]?>
</tr>
<tr>
	<td class=embedded colspan=19>&nbsp;</td>
</tr>
<tr>
	<td class=embedded colspan=19><b>Administrators</b></td>
</tr>
<tr>
	<td class=embedded colspan=19><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
	<?=$staff_table[UC_ADMINISTRATOR]?>
</tr>
<tr>
	<td class=embedded colspan=19>&nbsp;</td>
</tr>
<tr>
	<td class=embedded colspan=19><b>Moderators</b></td>
</tr>
<tr>
	<td class=embedded colspan=19><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
	<?=$staff_table[UC_MODERATOR]?>
	<br/>
</tr>
<tr>
		<!-- Define table column widths -->
		<td class=embedded width="20"></td>
		<td class=embedded width="100"></td>
		<td class=embedded width="25"></td>
		<td class=embedded width="35"></td>
		<td class=embedded width="90"></td>
		<td class=embedded width="20"></td>
		<td class=embedded width="100"></td>
		<td class=embedded width="25"></td>
		<td class=embedded width="35"></td>
		<td class=embedded width="90"></td>
		<td class=embedded width="20"></td>
		<td class=embedded width="100"></td>
		<td class=embedded width="25"></td>
		<td class=embedded width="35"></td>
		<td class=embedded width="90"></td>
		<td class=embedded width="20"></td>
		<td class=embedded width="100"></td>
		<td class=embedded width="25"></td>
		<td class=embedded width="35"></td>
</tr>
</table>
<?php endif; ?>
<?php	end_frame(); ?>

<br/>
<a href="./users.php?search=%25&class=2"><?=__('Arată toți Uploader-ii');?></a> | <a href="./users.php?search=%25&class=5"><?=__('Arată toți Releaser-ii');?></a> | <a href="./users.php?search=%25&class=7"><?=__('Arată toți VIP-ii');?></a> | <a href="./users.php?search=%25&class=6"><?=__('Arată toți Moderatorii de torrente');?></a> | <a href="./forum_moderators.php"><?=__('Arată toți Moderatorii de forum');?></a>
<br/>

<style type="text/css">
	#link-with-buttons form {
		margin: 10px;
	}
	#link-with-buttons form input[type=submit] {
			height: 20px;
			width: 150px;
			margin-right: 10px;
	}
</style>

<div id="link-with-buttons">
<?php function staff_button($link, $name, $description) { ?>
		<form action="<?=esc_html($link)?>">
			<input type="submit" value="<?=esc_html($name)?>"/> <?=esc_html($description)?>
		</form>
<?php }

	/* Display Site Owner Tools if user is Sysop */
		if (get_user_class() >= UC_SYSOP)
		{

		begin_frame("Site Owner Tools<font color=#FF0000> - Viewable by SysOp only.</font>"); ?>
		<?php
			staff_button("log_admin.php", "Sysop's Logs", "Loguri pentru Sysopi");

			end_frame();
		}
	/* Display Administrator Tools if user is Administrator or above */
		if (get_user_class() >= UC_ADMINISTRATOR) {
			begin_frame("Administrators Tools<font color=#FF0000> - Viewable by Administrators & above only.</font>");
			end_frame();
	 }
	/* Display Moderator Tools if user is Moderator or above */
	if (get_user_class() >= UC_MODERATOR) {
		begin_frame("Moderators Tools<font color=#FF0000> - Viewable by Moderators & above only.</font>");
		staff_button("moder_delete_messages.php", "Comments/Posts mass delete", "Stergerea commentelor/post-urilor a unui user din ultimile 24 ore.");

		echo "<h1>Forum tools</h1>";
		staff_button("moder_forum_tags.php", "Forum Tags", "Forum tags.");

		end_frame();
	}

	/* Display Moderator Tools if user is Moderator or above */
	if (get_user_class() == UC_SANITAR || get_user_class() >= UC_MODERATOR) {
		begin_frame("Torrents Moderators Tools");
		staff_button("log_torrents_moders.php", "Logs", "Torrents moderators logs.");
		end_frame();
	}

	/**
		For teams owners
	*/
	if ($CURUSER['team']) {
		$isTeamOwner = fetchOne('SELECT id FROM teams WHERE owner=:owner',array('owner'=>$CURUSER['id']));

		if ($isTeamOwner) {
			begin_frame("Team Owner Tools");
			staff_button("team_members.php", "Members", "Aici puteți adăuga/scoate membri din team.");
			staff_button("team_info.php", "Edit Team Info", "Modifică date despre team.");
			end_frame();
		}
	}

	if (get_user_class() >= UC_POWER_USER && !$CURUSER['team']) {
		begin_frame("Cerere");
		staff_button("new_team.php", "New team", "New team");
		end_frame();
	}
?>
</div> <!-- #link-with-buttons -->
<?php
	end_main_frame();
	stdfoot();
?>
