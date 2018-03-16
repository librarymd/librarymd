<?php
  require "./include/bittorrent.php";
loggedinorreturn();

if (have_flag('postingban')) stderr('Eroare', __('Dvs. aveţi post ban şi nu aveţi posibilitatea de a ne scrie.') );

if (isPost()) {
	$subject = $_POST['subject'];
	$body = $_POST['body'];
	if (!strlen($subject) || !strlen($body)) {
		stderr(__('Eroare'), __('Subiectul şi textul sunt câmpuri obligatorii şi nu pot fi lăsate necompletate.') );
	}

  $is_flood_banned = AntiFloodMessages::check_flood($CURUSER['id'], $body);
  if ($is_flood_banned !== false) {
    barkk($is_flood_banned);
  }

	post_to_admins($CURUSER["id"],$subject,$body);

	stderr(__('Succes!'), __('Mesajul a fost trimis administraţiei.') );
}

function post_to_admins($user,$subject = "Error - Subject Missing",$body = "Error - No Body", $stickey = false) {
 $forumid = '24';  // Remember to change this if the forum is recreated for some reason.
 $user = (int)$user;
 $subject = sqlesc($subject);
 $body = sqlesc($body);

 	q("INSERT INTO topics (userid, forumid, subject, created) VALUES ($user, $forumid, $subject, UNIX_TIMESTAMP(NOW()))");
 	$topicid = @q_mysql_insert_id();


 $added = "'" . get_date_time() . "'";

 q("INSERT INTO posts (topicid, userid, added, body, forumid) " .
              "VALUES($topicid, $user, $added, $body, $forumid)");
 $postid = @q_mysql_insert_id();

 q("UPDATE topics SET lastpost=$postid WHERE id=$topicid");
 q("UPDATE forums SET lastPost=$postid WHERE id=$forumid");
 update_topic_posts_count($topicid,true);
 update_post_page($arr[0],$topicid);

}

  // To call before posts=posts+1
  function update_post_page($postid,$topicid) {
  	  /**
  	  	The follow algorithm is used:
  	  		Get the total number of posts
  	  		-1+1, devide by 25 and ceil, put this as page number
  	  **/
  	  $row = fetchRow('SELECT posts,forumid FROM topics WHERE id=:topic', array('topic'=>$topicid) );
  	  $posts = $row['posts'];
  	  $forumid = $row['forumid'];
  	  $newpostPage = ceil($posts / 25);

  	  if ($newpostPage == 0) $newpostPage = 1;

  	  Q('UPDATE posts SET page=:page WHERE id=:id AND forumid=:forumid', array('page'=>$newpostPage, 'id'=>$postid, 'forumid'=>$forumid) );
  }
  function update_topic_posts_count($topicid,$recount=false)
  {
  	  //if ($recount) {
  	  //	  $count = q_singleval("SELECT COUNT(id) FROM posts WHERE topicid=$topicid");
  	  //	  q("UPDATE topics SET posts=$count WHERE id=$topicid");
  	  //} else {
  	  	  q("UPDATE topics SET posts=posts+1 WHERE id=$topicid");
  	  //}
  }

stdhead(__('Scrie un mesaj administraţiei') );
?>
<h1 class="center"><?=__('Scrie un mesaj administraţiei')?></h1>

<form action="write_to_admins.php" method="POST">

<table class="mCenter" cellpadding="10">
<tr>
	<td><?=__('Subiect')?></td>
	<td align="center"> <input type="text" name="subject" style="width:535px;"> </td>
</tr>
<tr>
	<td valign="top">
	<br><br><br><br><br>
<?=__('Conţinut')?>
	</td>
	<td align="center">
		<textarea name="body" rows="10" style="width:535px;"></textarea><br>
		<br>
		<input type="submit" value="<?=__('Trimite *')?>"><br>
		<br>
		<?=__('* Atenţie! Orice insultă în adresa administraţiei va rezulta în închiderea contului vostru.')?>
	</td>
</tr>
</table>

</form>

<?php
stdfoot();
?>
