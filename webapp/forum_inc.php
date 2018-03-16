<?php
  function catch_up() {
    global $CURUSER;

    $userid = $CURUSER["id"];

	// Only from last 30 days
	$days30 = 60*60*24*30;

    $res = q("SELECT topics.id AS topicid, topics.lastpost AS lastPostid, readposts.id AS readpostsid
    		  FROM topics
    		  LEFT JOIN posts ON topics.lastpost = posts.id AND posts.forumid = topics.forumid
    		  LEFT JOIN readposts ON readposts.userid=$userid AND topics.id = readposts.topicid
    		  WHERE DATE_SUB(CURDATE(),INTERVAL 31 DAY) <= posts.added AND (readposts.lastpostread < topics.lastpost OR readposts.lastpostread IS NULL)",
    	array('userid'=>$userid));

    while ($arr = mysql_fetch_assoc($res))
    {

      $topicid = $arr['topicid'];

	  $readpostsid = $arr['readpostsid'];

	  $postid = $arr['lastPostid'];

	  if (is_numeric($readpostsid)) {
	  	  q("UPDATE readposts SET lastpostread=$postid WHERE id=" . $readpostsid);
	  } else {
	  	  q("INSERT INTO readposts (userid, topicid, lastpostread) VALUES ($userid, $topicid, $postid)");
	  }

	  q("UPDATE watches SET lastSeenMsg=$postid WHERE user=$userid AND thread=$topicid AND type='topic'");
    }
  }

  //-------- Returns the minimum read/write class levels of a forum

  function get_forum_access_levels($forumid) {
    //$res = q("SELECT minclassread, minclasswrite, minclasscreate FROM forums WHERE id=$forumid");
    $arr = get_forum_data($forumid);

    /*if (mysql_num_rows($res) != 1)
      return false;

    $arr = mysql_fetch_assoc($res);*/
    if (!is_array($arr)) return false;

    return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"], "create" => $arr["minclasscreate"]);
  }

  //-------- Returns the forum ID of a topic, or false on error

  function get_topic_forum($topicid) {
    $res = q("SELECT forumid FROM topics WHERE id=$topicid");

    if (mysql_num_rows($res) != 1)
      return false;

    $arr = mysql_fetch_row($res);

    return $arr[0];
  }

  function get_forum_data($forumid) {
  	  global $userlang;
  	  $forumid = (int)$forumid;

  	  $mem_key = 'forum:'.$forumid.':'.$userlang;
  	  $forum = mem_get($mem_key);

  	  if ($forum != FALSE) return $forum;

  	  $forum = fetchRow("SELECT forums.*, name_$userlang AS name FROM forums WHERE id=$forumid");
  	  mem_set($mem_key,$forum,86400);

  	  return $forum;
  }

  function subcat_number_changed($forumid) {
  	  mem_delete('subcat_list:ro:forum:'.$forumid);
  	  mem_delete('subcat_list:ru:forum:'.$forumid);
  	  mem_delete('subcat_list:all:ro');
  	  mem_delete('subcat_list:all:ru');
  }

  function get_subcat($subcatid) {
  	  global $userlang;
  	  $subcatid = (int)$subcatid;

  	  $mem_key = 'subcat:'.$subcatid.':'.$userlang;
  	  $subcat = mem_get($mem_key);

  	  if ($subcat != FALSE) return $subcat;

  	  $subcat = fetchRow("SELECT forums_tags.*, name_$userlang AS name FROM forums_tags WHERE id=$subcatid");
  	  mem_set($mem_key,$subcat,10800);

  	  return $subcat;
  }

function topic_post_without_forum($topic, $body = "Error - No Body")  {
   $forumid = fetchOne('SELECT forumid FROM topics WHERE id = :topicid', array('topicid'=>$topic));
   return topic_post($topic, $body, $forumid);
}

function topic_post($topic, $body = "Error - No Body", $forumid)  {
    $added = get_date_time();

    q("INSERT INTO posts (topicid, userid, added, body, forumid) VALUES (:topic, 0, :added, :body, :forumid)
      ", array("topic"=> $topic, 'body'=> $body, 'added' => $added, 'forumid'=> $forumid));

    $postid = q_mysql_insert_id();
    q("UPDATE topics SET lastpost=$postid, posts=posts+1 WHERE id=$topic");

    after_topic_post($postid, $topic, true, $forumid);
}

  function after_topic_post($postid, $topicid, $newPost=false, $forumid) {
    update_post_page($postid, $topicid);
    update_topic_last_post($topicid, $newPost);
    if ($newPost) {
      update_topic_posts_count($topicid, $forumid);
    }
  }

  //-------- Returns the ID of the last post of a forum

  function update_topic_last_post($topicid, $newPost=false) {
    // Update the cache lastpost forumId cache
    $forumid = q_singleval("SELECT forumid FROM topics WHERE id=$topicid");

    if (!$forumid) return;

    $postid = q_singleval('
      SELECT MAX(id) FROM posts
      WHERE
      topicid=:topicid AND forumid=:forumid AND
      (mod_approved = "yes" OR mod_approved = "not_needed")
    ', array('topicid' => $topicid, 'forumid' => $forumid));

    if (!$postid) $postid = 0;

    q_delayed("UPDATE topics SET lastpost=$postid WHERE id=$topicid");

    $hName = 'forumLastPost'.$forumid;
    mem_set($hName, $postid, 86400);

    if ($newPost) {
    	q("UPDATE forums SET lastPost=$postid WHERE id=$forumid");
    } else {
    	// That mean what last post has been deleted
    	$lastThreadPost = fetchOne('SELECT MAX(lastpost) FROM topics WHERE forumid=:forum',array('forum'=>$forumid));
    	$lastThreadId = fetchOne('SELECT id FROM topics WHERE lastpost=:last',array('last'=>$lastThreadPost));
    	$lastThreadPostReal = fetchOne('SELECT MAX(id) FROM posts WHERE topicid=:last AND forumid=:forum', array('last'=>$lastThreadId,'forum'=>$forumid));
    	if (!$forumid) return;
    	q("UPDATE forums SET lastPost=$lastThreadPostReal WHERE id=$forumid");
    }

    // Update watch index
    q("UPDATE watches SET lastThreadMsg=$postid WHERE thread=$topicid AND type='topic'");
    q("UPDATE watches SET lastSeenMsg=$postid WHERE thread=$topicid AND type='topic' AND lastSeenMsg > lastThreadMsg");

  }

  function update_forum_last_post($forum) {
	  $lastpost = fetchOne('SELECT MAX(lastpost) FROM topics WHERE forumid=:forum',array('forum'=>$forum));
  	  if (!$lastpost) return;
  	  q("UPDATE forums SET lastPost=$lastpost WHERE id=$forum");
  }


  function update_topic_posts_count($topicid,$forumid,$recount=false)
  {
  	  if ($recount) {
  	  	  $count = q_singleval("SELECT COUNT(id) FROM posts WHERE topicid=$topicid AND forumid=$forumid");
  	  	  q("UPDATE topics SET posts=$count WHERE id=$topicid");
  	  } else {
  	  	  q("UPDATE topics SET posts=posts+1 WHERE id=$topicid");
  	  }
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

  function update_posts_page_repage($topicid, $forumid) {
  	  $res = q('SELECT * FROM posts WHERE topicid=:topicid AND forumid=:forumid ORDER BY id',
  	  			array('topicid'=>$topicid, 'forumid'=>$forumid) );

  	  $previous = 0;

  	  while ($post = mysql_fetch_assoc($res)) {
  	  	  if ($previous != $post['topicid']) {
  	  	  	  $post_count = 0;
  	  	  	  $previous = $post['topicid'];
  	  	  }
  	  	  $current_page = ceil( (($post_count==0)?1:$post_count) / 25 );
  	  	  $topicid = $post['id'];
  	  	  $post_count++;
  	  	  q('UPDATE posts SET page=:page WHERE id=:id AND forumid=:forumid',
  	  	  		array('id'=>$post['id'], 'page'=>$current_page, 'forumid'=>$forumid) );
  	  }
  }


  function get_forum_last_post($forumid)
  {
  	// Check the cache
  	$hName = 'forumLastPost'.$forumid;
  	$postid = mem_get($hName);

  	if ($postid == null) {
    	$postid = q_singleval("SELECT MAX(lastpost) FROM topics WHERE forumid=$forumid");
    	if (!$postid) $postid = 0;
    	mem_set($hName, $postid, 86400);
	}
	return $postid;
  }
  function force_expire_forum_last_post_cache($forumid) {
  	  return;
  	  //update_topic_last_post();
  	  $hName = 'forumLastPost'.$forumid;
  	  mem_delete($hName);

  	  $lastpost = q_singleval("SELECT MAX(lastpost) FROM topics WHERE forumid=$forumid");
  	  if (!$lastpost) return;

  	  q("UPDATE forums SET lastPost=$lastpost WHERE id=$forumid");
  }

  //-------- Inserts a quick jump menu

  function insert_quick_jump_menu($currentforum = 0)
  {
  	global $userlang;
    print("<p align=center><form method=get action=? name=jump>\n");

    print("<input type=hidden name=action value=viewforum>\n");

    print("<center>" . __('Navigare rapidă') . ": ");

    print("<select name=forumid onchange=\"if(this.options[this.selectedIndex].value != -1){ forms['jump'].submit() }\"></center>\n");


    $rows = fetchAll_memcache("SELECT sort,id,name_$userlang AS name, description_$userlang AS description,minclassread,minclasswrite,postcount,topiccount,minclasscreate
                        FROM forums ORDER BY name_$userlang",43200);

	foreach( $rows AS $arr ) {
      if (get_user_class() >= $arr["minclassread"])
        print("<option value=" . $arr["id"] . ($currentforum == $arr["id"] ? " selected>" : ">") . $arr["name"] . "\n");
    }

    print("</select>\n");

    print("<input type=submit value='Go!'>\n");

    print("</form>\n</p>");
  }

  function update_forums_posts_count () {
  	  return;
	$forums = q("select id from forums");
	while ($forum = mysql_fetch_assoc($forums))
	{
		$postcount = 0;
		$topiccount = 0;
		$topiccount = q_singleval("select count(id) from topics where forumid=$forum[id]");
		//$postcount = q_singleval("select count(id) from posts where forumid=$forum[id]");

		q("update forums set postcount=$postcount, topiccount=$topiccount where id=$forum[id]");
	}
  }


  /**
  	If $arang_key_id=true, then will be returned an array with [subcat_id]=subcat_row
  **/
  function getSubcategories($forumid, $lang) {
  	  global $here_cache;

  	  $key_name = 'subcat_list:'.$lang.':forum:'.$forumid;

  	  // Speed up if multiple call to for same data in exection of script
  	  if (isset($here_cache[$key_name])) return $here_cache[$key_name];

  	  $subcat_list = mem_get($key_name);
  	  if ($subcat_list != FALSE) {
  	  	  $here_cache[$key_name] = $subcat_list;
  	  	  return $subcat_list;
  	  }

  	  $subcat_list = fetchAll('SELECT forums_tags.*, name_'.$lang.' AS name FROM forums_tags WHERE forum=:forum ORDER BY total DESC,name', array('forum'=>$forumid) );
  	  mem_set($key_name, $subcat_list, 86400);

  	  $here_cache[$key_name] = $subcat_list;
  	  return $subcat_list;
  }

  /**
  	Special function what will make a new array with column_name as row array key
  **/
  function new_array_name_as_index($column_name,$arr) {
  	  $new_arr = array();
  	  foreach($arr AS $row) {
  	  	  $new_arr[$row[$column_name]] = $row;
  	  }
  	  return $new_arr;
  }

  // It also order it a bit into arr[forumid]
  function getAllSubcategories($lang) {
  	  $key_name = 'subcat_list:all:'.$lang;
  	  $subcat_list_arange = mem_get($key_name);
  	  if ($subcat_list_arange != FALSE) return $subcat_list_arange;

  	  $subcat_list = fetchAll('SELECT forums_tags.*, name_'.$lang.' AS name FROM forums_tags ORDER BY total DESC,name' );
  	  $subcat_list_arange = array();
  	  foreach ($subcat_list AS $subcat) {
  	  	  $subcat_forum = $subcat['forum'];
  	  	  if (!isset( $subcat_list_arange[ $subcat_forum ] ) ) $subcat_list_arange[ $subcat_forum ] = array();
  	  	  $subcat_list_arange[ $subcat_forum ][] = $subcat;
  	  }

  	  mem_set($key_name, $subcat_list_arange, 86400);

  	  return $subcat_list_arange;
  }




  //-------- Inserts a compose frame

  function insert_compose_frame($id, $newtopic = true, $quote = false)
  {
    global $maxsubjectlength, $CURUSER, $userlang;


    if ($newtopic)
    {
      $res = q("SELECT name_$userlang AS name FROM forums WHERE id=$id");

      $arr = mysql_fetch_assoc($res) or die("Bad forum id");

      $forumname = $arr["name"];

      check_forum_view_persmision_die($id);
      $forumid = $id;

      print("<p align=center>New topic in <a href=?action=viewforum&forumid=$id>$forumname</a> forum</p>\n");
    }
    else
    {
      $res = q("SELECT * FROM topics WHERE id=$id");

      $arr = mysql_fetch_assoc($res) or stderr("Forum error", "Topic not found.",true,true);

	  check_forum_view_persmision_die($arr['forumid']);
	  $forumid = $arr['forumid'];

      $subject = esc_html($arr["subject"]);

      print("<p align=center>". __('Răspuns pentru tema:') ." <a href=?action=viewtopic&topicid=$id>$subject</a></p>");
    }

    begin_frame(__('Сompune răspuns'), true);

	echo '<script type="text/javascript" src="js/forum_v1.js"></script>';
    echo '<form method=post enctype="multipart/form-data" action=?action=post>',"\n";

    if ($newtopic)
      print("<input type=hidden name=forumid value=$id>\n");

    else
      print("<input type=hidden name=topicid value=$id>\n");

    begin_table();

    if ($newtopic)
      print("<tr><td class=rowhead>Subject</td>" .
        "<td align=left style='padding: 0px'><input type=text size=100 maxlength=$maxsubjectlength name=subject " .
        "style='border: 0px; height: 19px'></td></tr>\n");

    if ($quote)
    {
       $postid = (int) $_GET["postid"];
       if (!is_valid_id($postid)) {
       	   die;
       }
	   $res = q("SELECT posts.*, users.username
	   	         FROM posts
	   			 JOIN users ON posts.userid = users.id
	   			 WHERE posts.id=$postid AND forumid=$forumid");

	   if (mysql_num_rows($res) != 1) {
	   	   stderr("Error", "No post with ID $postid.", true, true);
	   }

	   $arr = mysql_fetch_assoc($res);
    }

    if (isset($arr['forumid'])) {
    	check_forum_view_persmision_die($arr['forumid']);
    }

    if ($arr['censored'] == 'y') {
      die('Msg is censored');
    }

    print("<tr><td class=rowhead>". __('Conţinut') ."</td><td align=left style='padding: 0px'>" .

    "<textarea name=body cols=100 rows=20 style='border: 0px'>".
    ($quote?(("[quote=".esc_html($arr["username"])."]".esc_html($arr["body"])."[/quote]")):"").
    "</textarea></td></tr>\n");

    echo '<tr><td colspan=2 align=left>';

    echo $GLOBALS['lang']['forum_about_upload_img'],':<br>';
	echo '<input name="file_image" size="72" type="file"><br><br>';

    echo '<center><input type=submit value="',$GLOBALS['lang']['forum_reply_send'],'"></center></td></tr>',"\n";

    end_table();

    print("</form>\n");

		print("<p align=center><a href=./tags.php target=_blank>". __('Tag-uri') ."</a> | <a href=./smilies.php target=_blank>". __('Smile-uri') ."</a></p>\n");

    end_frame();

  insert_quick_jump_menu();

  }

function check_post_image() {
	//Check if a image is also uploaded
	if (isset($_FILES['file_image']) && $_FILES['file_image']['name'] != "") { //Photo upload
		$f = $_FILES['file_image'];
		$fname = strtolower($f['name']);
		$tmpname = $f['tmp_name'];
		// Let's see if image has been uploaded
		if (!is_uploaded_file($tmpname)) { stderr('Image upload', __('EROARE! Imaginea nu a putut fi încărcată, încercaţi s-o reîncărcaţi.')); }
		if (!filesize($tmpname)) { stderr('Image upload','ERROR! Uploaded image is empty.'); }
		// Now let's validate the image
		list($width, $height, $type, $attr) = getimagesize($tmpname);
		if ($type != 1 && $type != 2 && $type != 3) { stderr('ERROR!', 'Only a jpg or png or gif image are allowed.'); }
		if ($type == 1) $ext = 'gif';
		elseif ($type == 2) $ext = 'jpg';
		elseif ($type == 3) $ext = 'png';
		// New location name
		if ($width < 5 || $height < 5) {
			stderr('Image upload','ERROR! The minimum image dimension is 5x5.');
		}
		$sh = substr(sha1_file($tmpname) , 0, 3);
		$filename = $sh . '.' . $ext;
		return array($filename,$tmpname);
	}
	return array(0,0);
}

function valid_post($post,&$reson) {
	return true;
	//Validate $body, allow only 2 stamps
	if (substr_count($post, '/pic/stamps/') > 2) die('Abuz de stampile, va rugam sa folositi mai putine, apasati Back si editati-va mesajul.');
	if (strlen($post) < 25) {
		die('<b>Eroare, lungimea minima a mesajului este de 25 caractere. <br>Incearca sa scrii propozitii, fraze, sa argumentezi pozitia ta, replici ce constau doar din smile-uri si cuvinte gen "oe, 1" nu sunt interesante nimanui.</b>');
	}
	return true;
}

function check_if_logged() {
	global $user_logged;
	if (!$user_logged) {
		header("Location: ./forum.php");
		exit();
	}
}

function check_forum_view_persmision_die($forumid) {
	global $CURUSER;
	$userlang = get_lang();

	$forums = mem_get('forum_forums_'.$userlang.$forumid);

	if (!$forums) {
	    $forums = fetchRow("SELECT sort, id, name_$userlang AS name, minclassread,minclasswrite,postcount,topiccount,minclasscreate
         			   FROM forums WHERE id=$forumid");
    	mem_set('forum_forums_'.$userlang.$forumid,serialize($forums),3600);
    } else {
    	$forums = unserialize($forums);
    }

    $forum = $forums["name"];

    if ($CURUSER["class"] < $forums["minclassread"])
		stderr("Error", "You are not permitted to view this topic.");;
}

/**
* This function will add lastpostread to array
* @param array &forums_rows - Array of rows
* @param string topic_key - Key name of topic id in the array, default 'id'
**/
function _fill_arr_topics_lastpostread(&$forums_rows,$topic_key='id') {
	global $CURUSER;
	// Step 1, get topics id from our arr
	// Prepare a bulk SELECT on readposts table
	$forum_topics_ids = array();
	foreach($forums_rows AS $forums_row_k=>$forums_row_v) {
		if (is_numeric($forums_row_v[$topic_key])) $forum_topics_ids[] = $forums_row_v[$topic_key];
	}

	// Step 2
    $forum_topics_ids = join(',',$forum_topics_ids);
    $lastpostread_list = array();
    $lastpostread_key = array();
    if (strlen($forum_topics_ids)) {
    	$lastpostread_list = fetchAll("SELECT lastpostread,topicid FROM readposts WHERE readposts.userid=$CURUSER[id] AND readposts.topicid IN ($forum_topics_ids)");
    	if (count($lastpostread_list)) {
    		foreach($lastpostread_list AS $lastpostread_item) {
    			$lastpostread_key[$lastpostread_item['topicid']] = $lastpostread_item['lastpostread'];
    		}
  	 	}
   }

   // Step 3, put lastpostread into the original array
   // Fil original answer with readposts results
   foreach($forums_rows AS $forums_row_k=>$forums_row_v) {
   	   $t_lastTopicId = $forums_row_v[$topic_key];
   	   $t_lastpostread = "";
   	   if (isset($lastpostread_key[$t_lastTopicId])) {
   	   	   $t_lastpostread = $lastpostread_key[$t_lastTopicId];
   	   }
   	   $forums_rows[$forums_row_k]['lastpostread'] = $t_lastpostread;
   }
}

function allow_censoring($topicrow=array(),$topicid=0,$postrow=array(),$postid=0) {
	global $CURUSER;

	if (get_user_class() >= UC_MODERATOR) return true;

  if (get_config_variable('forum', 'moderators_activated') == false) {
    return false;
  }

	if (empty($topicrow)) {
		if ($topicid) $topicrow = fetchRow("SELECT * FROM topics WHERE id=:id",array('id'=>$topicid));
	}

	if (empty($topicrow)) throw new Exception('Insuficient params');

	if (have_flag('moderator_pe_tema_sa') && $topicrow['userid'] == $CURUSER["id"]) return true;

	if ( have_flag('forum_moderator') )
	{
		if ( !mem_get('forum_moderator_f' . $topicrow['forumid']. '_u' . $CURUSER["id"]) )
		{
			$moder = fetchOne("SELECT statut FROM forum_moderators WHERE user_id=:id AND forum_category_id=:categId",
				array('id'=>$CURUSER["id"], 'categId'=>$topicrow['forumid']));
			mem_set('forum_moderator_f' . $topicrow['forumid']. '_u' . $CURUSER["id"], ($moder)?$moder:'nu', 3600);
		}

		$moder = mem_get('forum_moderator_f' . $topicrow['forumid']. '_u' . $CURUSER["id"]);
		if ( $moder=='moderator_primar' || $moder == 'moderator_secundar' )
			return true;
	}

	return false;
}


function forum_getAvatar($arr) {
    global $CURUSER;
    $default_avatar = './pic/forum/default_avatar.gif';

    $posterid = $arr["userid"];

    if ($CURUSER["avatars"] == 'yes' && $arr["avatar"] == 'yes') {
      $avatar_file = avatarWww($posterid,$arr["avatar_version"]);
      $width_height = mem_get($avatar_file);
      if ($width_height == null || strpos($width_height,'.') === FALSE) { //If null, get the height and store it
      	  list($width,$height,,) = getimagesize(avatarWww($posterid,$arr["avatar_version"],false) );
      	  if ($width > 0 && $height > 0) {//It's ok
      	  	  mem_set($avatar_file, $width . '.' . $height, 86400);
      	  	  $avatar = avatarWww($posterid,$arr["avatar_version"],true);
      	  } else {
      	  	  mem_set($avatar_file, '0.0', 86400);
      	  	  $avatar = $default_avatar;
      	  }
      } else {
    	list($width,$height) = explode('.', $width_height);
    	$avatar = avatarWww($posterid,$arr["avatar_version"],true);
        if ($width == 0 || $height == 0) $avatar = $default_avatar;
      }
    } else { //If no image at the user/option show no avatars*/
      $avatar = $default_avatar;
      $width = 150; $height = 75;
    }

    if ($avatar == $default_avatar) { $width = 150; $height = 75; }

    return array($width,$height,$avatar);
}

function rightForumSubcat($forumid,$subcat) {
   $result=q_singleval("SELECT id FROM forums_tags WHERE id=:subcat AND forum=:forumid",array('subcat'=>$subcat,'forumid'=>$forumid));
   if ($result) return true;
   else return false;
}

function updateSubcatCount($subcatid, $forumid)
{
	if (!$subcatid) return false;

	$topicCount = fetchOne( "SELECT COUNT(id) FROM topics WHERE subcat=:subcat", array('subcat'=>$subcatid) );
	q( "UPDATE forums_tags SET total=:total WHERE id=:id", array('total'=>$topicCount, 'id'=>$subcatid) );

	mem_delete('subcat:'.$subcatid.':ro');
	mem_delete('subcat:'.$subcatid.':ru');
	mem_delete('subcat_list:ro:forum:'.$forumid);
	mem_delete('subcat_list:ru:forum:'.$forumid);
	return true;
}

function write_to_forumslog($username, $userid, $action, $topicid, $forumid, $postid=NULL, $lastname=NULL, $newname=NULL)
{
  /*actions: censor, uncensore, renametopic, setlocked_yes, setlocked_no */
  if ( !$username || !$userid || !$action || !$topicid || !$forumid )
    return false;

  $data = array(
    'username' => $username,
    'userid' => $userid,
    'action' => $action,
    'topicid' => $topicid,
    'postid' => $postid,
    'lastname' => $lastname,
    'newname' => $newname
  );

  $data = serialize($data);

  q('INSERT INTO forumslog (added, data, forumid) VALUES (NOW(), :data, :forumid)', array('data'=>$data, 'forumid'=>$forumid));
  return true;
}

function forbidIfAnonymous($forumid) {
  $forumUserOnly = array(12); // Soft to be accesible only to auth users

  if (in_array($forumid,$forumUserOnly)) {
    loggedinorreturn(true);
  }
}

function forbidIfNotEnoughRights($forumArr) {
  $userClass = get_user_class();

  $isTopicForForumModerators = $forumArr["minclassread"] == UC_SANITAR;
  $isUserVIP = $userClass == UC_VIP;
  $vipNotAllowedToSeeModeratorsTopic = ($isTopicForForumModerators && $isUserVIP);

  if ($userClass < $forumArr["minclassread"] || $vipNotAllowedToSeeModeratorsTopic)
    die(__('Accesul este interzis.'));

  notFoundIfNotDomain($forumArr);
}

function notFoundIfNotDomain($forumArr) {
  if (!userCanSeeForum($forumArr)) {
    stderr(__('Eroare'), "Forum not found.");
  }
}

function getReferencedUsernamesFromPost($message) {
  $matches_all = array();

  if (preg_match_all('/^\[quote=(.+?)\]/im',$message, $matches)) {
    $matches_all = array_merge($matches_all, $matches[1]);
  }

  if (preg_match_all('/^\@(.+?)[ ,]/im',$message,$matches)) {
    $matches_all = array_merge($matches_all, $matches[1]);
  }

  if (preg_match_all('/^#(.+?) (.+?),/im',$message,$matches)) {
    $matches_all = array_merge($matches_all, $matches[2]);
  }

  return array_values(array_unique($matches_all));
}

function sendNotifications($fromUser, $topic, $messageId, $message) {
  $referencedUsernames = getReferencedUsernamesFromPost($message);
  foreach (array_slice($referencedUsernames, 0, 5) as $referencedUsername) {
    $referencedUser = User::findByUsername($referencedUsername);
    sendNotificationsForUser($fromUser, $topic, $messageId, $message, $referencedUser['id']);
  }
}

function sendNotificationsForUser($fromUser, $topic, $messageId, $message, $referencedUserId) {
  $msg_topic = "[b][url=/forum.php?action=viewtopic&topicid=$topic[id]&page=p$messageId#$messageId]$topic[subject][/url][/b].";
  $quote = "\n\n[quote=$fromUser]{$message}[/quote]";
  if (strlen($quote) > 1000) $quote = '';

  $msg = "$fromUser ti-a scris un mesaj pe forum in topicul $msg_topic $quote";
  newNotification($referencedUserId, $msg);
}

function sendNotificationsToAdmins($fromUser, $topic, $messageId, $message) {
  if (isAdmin() && strpos($message, '@staff') === 0) {
    $modersId = fetchAll("SELECT id FROM users WHERE class>=:moderatorClassId", array('moderatorClassId' => UC_MODERATOR));
    foreach ($modersId as $moderId) {
      sendNotificationsForUser($fromUser, $topic, $messageId, $message, $moderId['id']);
    }
  }
}

function userCanSeeForum($forums_arr) {
  return true;
}

function getFirstPostInTopic($topicid, $forumid) {
  global $CURUSER;

  return fetchRow('
    SELECT posts.*,
                  users.username, users.class,   users.avatar, users.avatar_version, users.donor,
                  users.title,    users.enabled, users.warned, users.user_opt,       users.gender,
                  UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(posts.added) AS added_seconds_ago,
                  posts_likes.added AS likeAdded, posts_likes.type as like_sign,
                  posts_for_review.reason AS refusal_reason,
                  users_additional.total_wall_posts
    FROM posts
    LEFT JOIN users ON users.id = posts.userid
    LEFT JOIN users_additional ON users_additional.id = users.id
    LEFT JOIN posts_likes ON posts_likes.postid = posts.id AND posts_likes.userid = :curuserid
    LEFT JOIN posts_for_review ON posts_for_review.post_id = posts.id
    WHERE posts.forumid = :forumid AND topicid=:topicid AND page=1
    ORDER BY id LIMIT 1', array(
      "forumid" => $forumid,
      "topicid" => $topicid,
      "curuserid" => $CURUSER['id']
    ));
}

function getPostLikes($topicid, $postid) {
  $key = 'forum_first_post_likes'.$topicid;
  $firstPostLikes = mem_get($key);

  if (!$firstPostLikes) {
    $firstPostLikes = fetchAll("
      SELECT *
      FROM posts_likes
      WHERE postid = :postid
    ", array('postid' => $postid));

    mem_set($key, serialize($firstPostLikes), 3600);
  } else {
    $firstPostLikes = unserialize($firstPostLikes);
  }

  return $firstPostLikes;
}

function getPostLikeUnlikeActionHtml($topicid, $postid, $postRow) {
  global $CURUSER;

  $currentUserIsThePostAuthor = $postRow['userid'] == $CURUSER['id'];

  $yesBtnClass = $postRow['like_sign'] == 'plus'  ? 'selected' : '';
  $noBtnClass  = $postRow['like_sign'] == 'minus' ? 'selected' : '';

  $userCanClickLinks = !$currentUserIsThePostAuthor && Users::isLogged();

  ob_start();
  ?>
      <span class="useful_link" data-topicid="<?=$topicid?>" data-customid="<?=$postid?>">
        <?=__('Mesaj util ?')?>

        &nbsp;
        <span class="<?=$userCanClickLinks?'lnk':''?> <?=$yesBtnClass?>" data-action="like"><?=__('Da')?></span>
        /
        <span class="<?=$userCanClickLinks?'lnk':''?> <?=$noBtnClass?>" data-action="unlike"><?=__('Nu')?></span>

        &nbsp;
        <?=$postRow['likes']?> / <?=$postRow['unlikes']?>
        &nbsp;

        <span class="result"><?=$postRow['likes'] - $postRow['unlikes']?></span> <?=__('puncte')?>
      </span>
  <?php

  $html = ob_get_contents();
  ob_end_clean();
  return $html;
}

function doLikeActionDb($action, $postid) {
  global $CURUSER;
  $postIdUserId = array('postid'=>$postid, 'userid'=>$CURUSER['id']);

  switch ($action) {
    case 'addLike':
      Q('INSERT INTO posts_likes VALUES (:postid, :userid, NOW(),"plus")', $postIdUserId);
      Q('UPDATE posts SET likes = likes + 1 WHERE id=:postid', array('postid'=>$postid));
      break;

    case 'removeLike':
      Q('DELETE FROM posts_likes WHERE postid=:postid AND userid=:userid', $postIdUserId);
      Q('UPDATE posts SET likes = likes - 1 WHERE id=:postid', array('postid'=>$postid));
      break;

    case 'addUnlike':
      Q('INSERT INTO posts_likes VALUES (:postid, :userid, NOW(), "minus")', $postIdUserId);
      Q('UPDATE posts SET unlikes = unlikes+1 WHERE id=:postid', array('postid'=>$postid));
      break;

    case 'removeUnlike':
      Q('DELETE FROM posts_likes WHERE postid=:postid AND userid=:userid', $postIdUserId);
      Q('UPDATE posts SET unlikes = unlikes-1 WHERE id=:postid', array('postid'=>$postid));
      break;

    default:
      die('Unknow state');
      break;
  }
}

function processForumLikeAction($like_action, $postid, $topicid) {
  global $CURUSER;

  mem_delete('forum_first_post'       . $topicid);
  mem_delete('forum_first_post_likes' . $topicid);

  $unlike_action = !$like_action;

  q('START TRANSACTION');

  $typeOfExistingLike = q_singleval(
    "SELECT type FROM posts_likes WHERE postid=:postid AND userid={$CURUSER['id']}",
    array('postid'=>$postid)
  );
  $noLikeRecordExists = $typeOfExistingLike == NULL;

  /**
   * State machine:
   *
   * If no existing like: just do the action
   * If existing like but action is to unlike: first remove the like and then add the unlike
   * If existing like but action is to remove like: remove the like
   * If existing unlike but action is to like: first remove the unlike and then add the like
   * If existing unlike but action is to remove unlike: remove the unlike
   */

  if ($noLikeRecordExists) {
    if ($like_action) {
      doLikeActionDb('addLike', $postid);
    } else {
      doLikeActionDb('addUnlike', $postid);
    }
  } else {
    $wasItALike   = $typeOfExistingLike == 'plus';
    $wasItAUnlike = $typeOfExistingLike == 'minus';

    if ($wasItALike) {
      doLikeActionDb('removeLike', $postid);
      if ($unlike_action) {
        doLikeActionDb('addUnlike', $postid);
      }
    }

    if ($wasItAUnlike) {
      doLikeActionDb('removeUnlike', $postid);
      if ($like_action) {
        doLikeActionDb('addLike', $postid);
      }
    }
  }

  q('COMMIT');
}