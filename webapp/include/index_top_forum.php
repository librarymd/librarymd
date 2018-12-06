<?php
    $userlang = get_lang();

    $cache_key_suffix = 'ttt';
    $trash_forum_id = 33;
    // Check the topics cache
    $topics = mem2_get('topics_new_top10_3d.'.$userlang.$cache_key_suffix);

    if ($topics === FALSE) {
        $topics = fetchAll('
           SELECT topics.*, users.username AS topicAuthor, users.gender AS authorGender,
                  posts.id AS postId, posts.userid AS lpuserId, posts.added AS lpAdded, lastUsers.username AS lpusername, lastUsers.gender AS lpuserGender,
                  forums.minclassread, forums.id AS forum_id, forums.name_'.$userlang.' AS forum_name, forums_tags.id AS subcat_id, forums_tags.name_'.$userlang.' AS subcat_name
           FROM topics

           LEFT JOIN users ON users.id = topics.userid
           LEFT JOIN posts ON topics.lastpost = posts.id AND topics.forumid = posts.forumid
           LEFT JOIN users lastUsers ON posts.userid = lastUsers.id

           LEFT JOIN forums ON topics.forumid = forums.id
           LEFT JOIN forums_tags ON topics.subcat = forums_tags.id

           WHERE
            topics.created > :pointThreeDays
            AND topics.locked = "no"
           ORDER BY posts DESC LIMIT 50',

           array('pointThreeDays'=>time()- 60*60*24* 3)
        );

        mem2_set('topics_new_top10_3d.'.$userlang.$cache_key_suffix, serialize($topics), 600);
    } else {
        $topics = unserialize($topics);
    }
    index_forum_top_fragment(__('Cele mai populare teme noi, create in ultimele 3 zile'), $topics, 20);
?>

<?php
    // Check the topics cache
    $topics = mem2_get('topics_active_last10.'.$userlang.$cache_key_suffix);
    if ($topics === FALSE) {
        $topics = fetchAll('
           SELECT topics.*, users.username AS topicAuthor, users.gender AS authorGender,
                  posts.id AS postId, posts.userid AS lpuserId, posts.added AS lpAdded, lastUsers.username AS lpusername, lastUsers.gender AS lpuserGender,
                  forums.minclassread, forums.id AS forum_id, forums.name_'.$userlang.' AS forum_name, forums_tags.id AS subcat_id, forums_tags.name_'.$userlang.' AS subcat_name
           FROM topics

           LEFT JOIN users ON users.id = topics.userid
           LEFT JOIN posts ON topics.lastpost = posts.id AND topics.forumid = posts.forumid
           LEFT JOIN users lastUsers ON posts.userid = lastUsers.id

           LEFT JOIN forums ON topics.forumid = forums.id
           LEFT JOIN forums_tags ON topics.subcat = forums_tags.id

           WHERE topics.locked = "no"
           ORDER BY topics.lastpost DESC LIMIT 35');

        mem2_set('topics_active_last10.'.$userlang.$cache_key_suffix, serialize($topics), 10);
    } else {
        $topics = unserialize($topics);
    }
    index_forum_top_fragment(__('Temele cu cele mai recente mesaje'), $topics, 20);
?>


<?php
  // Check the topics cache
  $forum_top_10_key = 'topics_top10_3d.'.$userlang.$cache_key_suffix;
  $topics = mem2_get($forum_top_10_key);
  if ($topics === FALSE && !mem_is_locked($forum_top_10_key) && false ) {
    mem_lock($forum_top_10_key);

    // Get id of a post posted exact 3 days ago
    $topic_3d = fetchOne('SELECT id,forumid
       FROM topics
       WHERE created > UNIX_TIMESTAMP(NOW() - INTERVAL 3 day) AND posts > 0
       ORDER BY id
       LIMIT 1' );


    if ($topic_3d['id'])
      // Post created 3 days ago
      $post_3d = fetchOne("SELECT id FROM posts WHERE topicid=$topic_3d[id] AND forumid=$topic_3d[forumid] ORDER BY id LIMIT 1");

    if (!is_numeric($post_3d)) {
      $post_3d = fetchOne('SELECT id FROM posts WHERE added > NOW() - INTERVAL 3 day ORDER BY id ASC limit 1');
    }

    q("CREATE temporary TABLE most_activ_topics SELECT count(id) AS totalPosts, topicid
      FROM posts
      WHERE id > $post_3d
      GROUP BY topicid
      ORDER BY totalPosts DESC
      LIMIT 20");

    $topics = fetchAll("
       SELECT topics.*, users.username AS topicAuthor, users.gender AS authorGender,
            posts.id AS postId, posts.userid AS lpuserId, posts.added AS lpAdded, lastUsers.username AS lpusername, lastUsers.gender AS lpuserGender,
            forums.minclassread, forums.id AS forum_id, forums.name_$userlang AS forum_name, forums_tags.id AS subcat_id, forums_tags.name_$userlang AS subcat_name
         FROM most_activ_topics

         LEFT JOIN topics ON topics.id = most_activ_topics.topicid

         LEFT JOIN users ON users.id = topics.userid
         LEFT JOIN posts ON topics.lastpost = posts.id AND topics.forumid = posts.forumid
         LEFT JOIN users lastUsers ON posts.userid = lastUsers.id

         LEFT JOIN forums ON topics.forumid = forums.id
         LEFT JOIN forums_tags ON topics.subcat = forums_tags.id

         WHERE topics.locked = 'no'
         ORDER BY most_activ_topics.totalPosts DESC LIMIT 15");

      mem2_set($forum_top_10_key, serialize($topics), 3600);
  } else {
    $topics = unserialize($topics);
  }

  index_forum_top_fragment(__('Cele mai active teme de pe forum din ultimele 3 zile'), $topics, 20);

?>


<?php
  function index_forum_top_fragment($header, $rows, $to_show) {
?>
  <h2 align=left><?php echo $header;?></h2>
  <table width=100% border=1 cellspacing=0 cellpadding=10>
    <tr>
      <td width="100%">
          <table width="100%" border="1" cellspacing="0" cellpadding="5">
              <tr>
                  <td class="colheadGalb"><?=__('Tema');?></td> <td class="colheadGalb" width="1%"><?=__('Replici');?></td> <td class="colheadGalb" width="1%"><?=__('Autor');?></td> <td class="colheadGalb" width="15%"><?=__('Ultimul mesaj');?></td>
              </tr>
  <?php index_show_forum_topics($rows, 20); ?>
          </table>
      </td>
    </tr>
  </table>
  <br/>
<?php
  }
  function index_show_forum_topics($topics,$howmuch=10) {
      global $CURUSER;
      if (!$CURUSER) $CURUSER['class'] = 0;
      $cur_i = 0;
      foreach ($topics AS $topic) {
          if ($topic['minclassread'] > $CURUSER["class"]) {
              continue;
          }

          if ($cur_i == $howmuch) break;
          $cur_i++;

          $cat = " &nbsp; (<a href=./forum.php?action=viewforum&forumid=$topic[forum_id]>".esc_html($topic['forum_name'])."</a>";
          if ($topic['subcat_id']) {
              $cat .= " - <a href=./forum.php?action=viewforum&forumid=$topic[forum_id]&subcat=$topic[subcat_id]>".esc_html($topic['subcat_name'])."</a>";
          }
          $cat .= ')';

          echo stringInto('
              <tr>
                  <td><a href="./forum.php?action=viewtopic&topicid=:topicid&page=lastseen">:subject</a>'.$cat.'</td>
                  <td>:replies</td> <td>:author</td> <td class="nowrap">:lpAdded<br> :lastPostAuthor</td>
              </tr>',
              array('subject'=>esc_html($topic['subject']), 'replies'=>$topic['posts'],
                    'author'=>$topic['topicAuthor'], 'topicid'=>$topic['id'], 'lpAdded'=>$topic['lpAdded'],
                     'lastPostAuthor'=>$topic['lpusername'] ) );
      }
  }
?>