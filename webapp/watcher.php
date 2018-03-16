<?php

$GLOBALS['user_watch_ignore_cach'] = true; // Trick the user ;o)

require_once("include/bittorrent.php");
require_once("include/torrent_opt.php");
require_once($INCLUDE . 'classes/watches.php');

loggedinorreturn();

function bark($msg) {
	if ( isset($_POST['ajax']) ) {
		die($msg);
	}
	stdhead();
   	stdmsg('Eroare', $msg);
	stdfoot();
	exit;
}
function successfully($msg) {
	stdhead();
   	stdmsg('Succes', $msg);
	stdfoot();
	exit;
}

$userid = $CURUSER["id"];

//ajax=1&type=topic&action='+action+'&thread='+topicId

function redirect_to_source() {
	header('Location: ./'.$_SERVER["REQUEST_URI"]);
	exit();
}

if ( isset($_POST['action']) ) {

	//ajax=1&type=topic&action='+action+'&thread='+topicId
	$type = $_POST['type'];

	if ($type != 'topic' && $type != 'torrent') {
		bark('Bad type');
	}

	$action = $_POST['action'];

	if ($action != 'add' && $action != 'del') {
		bark('Bad action');
	}

	$thread = $_POST['thread'];

	if (!is_numeric($thread)) {
		bark('Thread id must be numerical');
	}

	$is_ajax = (isset($_POST['ajax']))?true:false;

	if ($action == 'add') {

    Watches::startForType($type, $userid, $thread);

		if ($is_ajax) {
			die('1');
		} else {
			redirect_to_source();
		}
	}
	if ($action == 'del') {
		// Check if it is already in watch list
		q_singleval("DELETE FROM watches WHERE user=$userid AND thread=$thread AND type='$type'");

		if ($is_ajax) {
			die('1');
		} else {
			redirect_to_source();
		}
	}
	die();
}

stdhead($lang['watcher_title']);

?>

<h1><?=$lang['watcher_title']?></h1>

<table class="fullWidth" border=1 cellspacing=0 cellpadding=5>
	<tr>
		<td class=colhead><?=__('Nume')?></td>
		<td class=colhead><?=__('Replici')?></td>
		<td class=colhead><?=__('Vizualizări')?></td>
		<td class=colhead><?=__('Autor')?></td>
		<td class=colhead><?=__('Ultimul mesaj')?></td>
	</tr>

<?php

$GLOBALS['query_with_union'] = true;
$thread_res = q('SELECT *, if( (CAST(lastThreadMsg AS SIGNED) - CAST(lastSeenMsg AS SIGNED) )>0,1,0) AS unseen FROM watches WHERE user = '.$CURUSER['id'].'
					ORDER BY unseen DESC, lastThreadMsg DESC
          LIMIT 2000
				');
$GLOBALS['query_with_union'] = false;

// Collect all ids, to make one shot selects

$topicids = array();
$torrentids = array();
while( $thread = mysql_fetch_assoc($thread_res) ) {
	$threads[] = $thread;
	// Collect topic ids
	if ($thread['type'] == 'topic') {
		$topicid = $thread['thread'];
		$topicids[] = $topicid;
	} elseif ($thread['type'] == 'torrent') {
		$torrentid = $thread['thread'];
		$torrentids[] = $torrentid;
	}
}

if (count($topicids)) {
	$topicsArr = fetchAll("SELECT topics.id, topics.lastpost, subject, posts, views, topics.userid, readposts.lastpostread, users.username AS topicAuthor, users.gender AS autothorGender,
							posts.userid AS lpuserid, posts.added AS lpadded, users_last_user.username AS lpusername, users_last_user.gender AS lpuserGender
		   		FROM topics
		   		LEFT JOIN readposts ON (topics.id = readposts.topicid AND readposts.userid = $userid)
		   		LEFT JOIN users ON users.id = topics.userid

				LEFT JOIN posts ON topics.lastpost = posts.id AND topics.forumid = posts.forumid
				LEFT JOIN users users_last_user ON posts.userid = users_last_user.id
		   		WHERE topics.id IN (". join(',',$topicids) .")");
	// Now arrange a bit, put in [id]=torrent_array form
	$topicsArr2 = array();
	foreach($topicsArr AS $topicArr) {
		$topicsArr2[$topicArr['id']] = $topicArr;
	}

}

if (count($torrentids)) {
	$torrentsArr = fetchAll("SELECT torrents.id, name AS subject, comments AS posts, views, torrents.owner, users.username AS topicAuthor, users.gender AS autothorGender,
							  torrents.lastComment, torrents.torrent_opt,
							comments.user AS lpuserid, comments.added AS lpadded, users_last_user.username AS lpusername, users_last_user.gender AS lpuserGender
		   FROM torrents
		   LEFT JOIN users ON users.id = torrents.owner

		   LEFT JOIN comments ON torrents.lastComment = comments.id
		   LEFT JOIN users users_last_user ON comments.user = users_last_user.id

		   WHERE torrents.id IN (". join(',',$torrentids) .")" );
	// Now arrange a bit, put in [id]=torrent_array form
	$torrentsArr2 = array();
	foreach($torrentsArr AS $torrentArr) {
		$torrentsArr2[$torrentArr['id']] = $torrentArr;
	}

}

$topicTotalNew = 0;

foreach ($threads AS $thread) {
	if ($thread['type'] == 'topic') {
		$topicid = $thread['thread'];

		$topic = $topicsArr2[$topicid];

		// Skip unfound topics
		if ( !isset($topic['subject']) ) continue;

		$replies = $topic['posts'];
		$views = $topic['views'];
		$topic_userid = $topic['userid'];
		$topic_author = $topic['topicAuthor'];
		$topic_author_gender = $topic['autothorGender'];

         //---- Get userID and date of last post

        $topicLastPost = $topic["lastpost"];
        $lpuserid = $topic["lpuserid"];
        $lpadded = "<nobr>" . $topic["lpadded"] . "</nobr>";
        $lpusername = $topic['lpusername'];
        $lpuserGender = $topic['lpuserGender'];

        //------ Get name of last poster

        if ($lpusername != '') {
           $lpusername = "<a href=userdetails.php?id=$lpuserid".(($lpuserGender=='fem')?' style="color:#F93EA0;"':'')."><b>$lpusername</b></a>";
        } else {
        	if ($lpuserid == 0) {
               $lpusername = "System";
        	} else {
        		$lpusername = "unknown[$lpuserid]";
        	}
        }

        //------ Get author

        if ($topic_author != '') {
          $lpauthor = "<a href=userdetails.php?id=$topic_userid".(($topic_author_gender=='fem')?' style="color:#F93EA0;"':'')."><b>$topic_author</b></a>";
        } else {
        	if ($topic_userid == 0) {
   	           $lpauthor = "System";
   	        } else {
   	        	$lpauthor = "unknown[$topic_userid]";
   	        }
   	    }

        $lastPostRead = $topic['lastpostread'];
        $new          = $lastPostRead && $topicLastPost > $lastPostRead;
        $topicpic     = ($new ? "unlockednew" : "unlocked");

        if ($new) {
          $topicTotalNew++;
        }

		if ($lastPostRead === null) { //User have never read this topic
			$url = "<a href=forum.php?action=viewtopic&topicid=$topicid>";
		} else {
			$url = "<a href=forum.php?action=viewtopic&topicid=$topicid&page=p$lastPostRead#$lastPostRead>";
		}

        $subject = $url . '<b>' . esc_html($topic["subject"]) . '</b></a>';
        $subject .= '&nbsp;&nbsp;<span class="lnk watchDel" custom_type="topic" custom_thread="'.$thread['thread'].'"><small>[x]</small></span>';


        echo("<tr><td align=left><table border=0 cellspacing=0 cellpadding=0><tr>
        	<td class=embedded style='padding-right: 5px'><img src=./pic/forum/$topicpic.gif>
			<td class=embedded align=left>\n" .
        	"$subject</td></tr></table></td><td align=right>$replies</td>\n" .
        	"<td align=right>$views</td><td align=left>$lpauthor</td>\n" .
        	"<td align=left>$lpadded<br>by&nbsp;$lpusername</td></tr>");

	} // End if ($row['type'] == 'topic')
	elseif ($thread['type'] == 'torrent') {
		$new = ($thread['lastThreadMsg'] > $thread['lastSeenMsg']);
		$pic = ($new ? "unlockednew" : "unlocked");

		$torrentid = $thread['thread'];

		$topic = $torrentsArr2[$torrentid];

		// Skip unfound topics
		if ( !isset($topic['subject']) ) continue;

		$replies = $topic['posts'];
		$views = $topic['views'];
		$topic_userid = $topic['owner'];
		$topic_author = $topic['topicAuthor'];
		$topic_author_gender = $topic['autothorGender'];

         //---- Get userID and date of last post

        $topicLastPost = $topic["lastComment"];

        if ( $topic["lpuserid"] ) {
	        $lpuserid = $topic["lpuserid"];
	        $lpadded = "<nobr>" . $topic["lpadded"] . "</nobr>";
	        $lpusername = $topic['lpusername'];
	        $lpuserGender = $topic['lpuserGender'];
	    } else
			$lpuserid = 0;


        //------ Get name of last poster

        if ($lpusername != '') {
           $lpusername = "<a href=userdetails.php?id=$lpuserid".(($lpuserGender=='fem')?' style="color:#F93EA0;"':'')."><b>$lpusername</b></a>";
        } else {
        	if ($lpuserid == 0) {
               $lpusername = "System";
        	} else {
        		$lpusername = "unknown[$lpuserid]";
        	}
        }
		if ($lpuserid)
			$lplaststr = "$lpadded<br>by&nbsp;$lpusername";
		else
			$lplaststr = "- - -";

        //------ Get author
        if ( torrent_have_flag('anonim_unverified', $topic['torrent_opt']) || torrent_have_flag('anonim', $topic['torrent_opt']) )
			$lpauthor = 'Anonim';
		else {
			if ($topic_author != '') {
				$lpauthor = "<a href=userdetails.php?id=$topic_userid".(($topic_author_gender=='fem')?' style="color:#F93EA0;"':'')."><b>$topic_author</b></a>";
			} else {
				if ($topic_userid == 0) {
					$lpauthor = "System";
				} else {
					$lpauthor = "unknown[$topic_userid]";
				}
			}
		}


		if (!$new) { //User have never read this topic
			$url = "<a href=details.php?id=$torrentid>";
		} else {
			$lastSeenMsg = $thread['lastSeenMsg'];
			$url = "<a href=details.php?id=$torrentid&viewcomm={$lastSeenMsg}#comm{$lastSeenMsg}>";
		}

        $subject = $url . '<b>' . esc_html($topic["subject"]) . "</b></a>";

        $subject .= '&nbsp;&nbsp;<span class="lnk watchDel" custom_type="torrent" custom_thread="'.$thread['thread'].'"><small>[x]</small></span>';


        echo("<tr class='watchTorrent'><td align=left><table border=0 cellspacing=0 cellpadding=0><tr>
        	<td class=embedded style='padding-right: 5px'><img src=./pic/forum/$pic.gif>
			<td class=embedded align=left>\n" .
        	"$subject</td></tr></table></td><td align=right>$replies</td>\n" .
        	"<td align=right>$views</td><td align=left>$lpauthor</td>\n" .
        	"<td align=left>$lplaststr</td></tr>");
	}
}

echo '</table><br>';

if ($CURUSER['unread_watched_number'] != $topicTotalNew) {
  $unwatchedTopics = fetchAll('SELECT thread FROM watches WHERE user = :user AND lastThreadMsg > lastSeenMsg and type="topic"',
    array('user' => $CURUSER['id'])
  );

  foreach ($unwatchedTopics as $topicArr) {
    $topicId = $topicArr['thread'];

    $topicsToFix = fetchAll('
      SELECT topics.id, topics.lastpost, readposts.lastpostread
      FROM topics
      LEFT JOIN readposts ON (topics.id = readposts.topicid AND readposts.userid = :myUserId)
      WHERE topics.id = :topicId', array('topicId' => $topicId, 'myUserId' => $CURUSER['id']));

    foreach ($topicsToFix as $topicToFix) {
      $topicId       = $topicToFix['id'];
      $topicLastPost = $topicToFix['lastpost'];
      $lastPostRead  = $topicToFix['lastpostread'];

      q('UPDATE watches
        SET lastThreadMsg = :lastThreadMsg, lastSeenMsg = :lastSeenMsg
        WHERE type="topic" AND user = :user AND thread = :topicId',
        array(
          'user'          => $CURUSER['id'],
          'topicId'       => $topicId,
          'lastThreadMsg' => $topicLastPost,
          'lastSeenMsg'   => $lastPostRead
        )
      );

    }

    mem_delete('user_watch_'.$CURUSER['id']);
  }
}
?>

(maxim 2000 itemi se vor afisa)
<script>
jQuery(function($) {
$('span.watchDel').click(function(){
	if (confirm("<?=__('Într-adevăr doriţi să ştergeţi tema de la urmărire?')?>") != true) return;
	var type = $(this).attr('custom_type');
	var thread = $(this).attr('custom_thread');
	$.post("watcher.php", { ajax:'1', action:'del', type: type, thread: thread } );
	$(this).parents('tr:first').parents('tr:first').remove();
});
});
</script>
<?php
stdfoot();
