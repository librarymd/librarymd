<?php
function torrent_updatelastcomment($torrentid) {
	$lastest = fetchOne('SELECT max(id) FROM comments WHERE torrent=:id', array('id'=>$torrentid) );
	if (!$lastest) $lastest = 0;
	q('UPDATE torrents SET lastcomment=:comment WHERE id=:id', array('id'=>$torrentid, 'comment'=>$lastest ) );

	q("UPDATE watches SET lastThreadMsg=:comment WHERE thread=:id AND type='torrent'",
		array('id' => $torrentid, 'comment' => $lastest));
	return $lastest;
}

function cleanCommentsCache($torrent,$page) {
  	  $comments_mem_key = "comments:$torrent:$page";
  	  if ($GLOBALS['CURUSER']["id"] == 1) {
  	  	  echo "Cleanup $comments_mem_key \n<br>";
  	  }
  	  mem_delete($comments_mem_key);
}

function getCommentPage($torrent,$comment) {
  	  $theCommentOrderNumber = fetchOne("SELECT count(id) FROM comments WHERE torrent=:torrent AND id <= :newId",
  	  		array('torrent'=>$torrent, 'newId'=>$comment) );
  	  return ceil( $theCommentOrderNumber / 20 ) - 1;
}

/**
	This will clean the cache for certain comment.
	If torrentid is available pass it to increase the performance.
**/
function postCleanCache($postid,$torrentid='') {
	if (empty($torrentid))
		$torrentid = fetchOne('SELECT torrent FROM comments WHERE id=:id', array('id'=>$postid) );

	cleanCommentsCache($torrentid, getCommentPage($torrentid,$postid) );
}

function torrentRegenerateCommentsCount($torrentid) {
	$comments_count = fetchOne('SELECT count(id) FROM comments WHERE torrent=:torrent',
				array('torrent'=>$torrentid) );

	q("UPDATE torrents SET comments = :commentsCount WHERE id = :torrent",
		array('commentsCount'=>$comments_count, 'torrent'=>$torrentid) );
	cleanTorrentCache($torrentid);
}

function draw_smiles()
{
?>
	<script language=javascript>
	function SmileIT(smile,form,text){
		document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
		document.forms[form].elements[text].focus();
	}
	</script>

	<table cellpadding="3" cellspacing="1">
	<tr>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':)','comment','text')"><img src=pic/smilies/smile.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':smile:','comment','text')"><img src=pic/smilies/smile2.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':-D','comment','text')"><img src=pic/smilies/grin.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':lol:','comment','text')"><img src=pic/smilies/lol.gif></a></td></tr><tr>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':w00t:','comment','text')"><img src=pic/smilies/w00t.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':-P','comment','text')"><img src=pic/smilies/tongue.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(';-)','comment','text')"><img src=pic/smilies/wink.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':-|','comment','text')"><img src=pic/smilies/noexpression.gif></a></td></tr><tr>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':-/','comment','text')"><img src=pic/smilies/confused.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':-(','comment','text')"><img src=pic/smilies/sad.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':\'-(','comment','text')"><img src=pic/smilies/cry.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':weep:','comment','text')"><img src=pic/smilies/weep.gif></a></td></tr><tr>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':-O','comment','text')"><img src=pic/smilies/ohmy.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':o)','comment','text')"><img src=pic/smilies/clown.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT('8-)','comment','text')"><img src=pic/smilies/cool1.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT('|-)','comment','text')"><img src=pic/smilies/sleeping.gif></a></td></tr><tr>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':innocent:','comment','text')"><img src=pic/smilies/innocent.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':whistle:','comment','text')"><img src=pic/smilies/whistle.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':unsure:','comment','text')"><img src=pic/smilies/unsure.gif></a></td>
	<td class=embedded style='padding: 3px; margin: 2px'><a href="javascript: SmileIT(':closedeyes:','comment','text')"><img src=pic/smilies/closedeyes.gif></a></td>
	</tr>
	</table>
<?php
}
function unhtmlentities ($string)  {
   $trans_tbl = get_html_translation_table (HTML_ENTITIES);
   $trans_tbl = array_flip ($trans_tbl);
   $ret = strtr ($string, $trans_tbl);
   return  preg_replace('/\&\#([0-9]+)\;/me',
       "chr('\\1')",$ret);
}

function messages_die_if_spam($text,$text_parsed='') {
	  global $CURUSER;
	  if (empty($text_parsed)) $text_parsed = format_comment($text);

	  messages_die_if_url_blacklist($text,$text_parsed);

	  if ($CURUSER['class'] >= UC_RELEASER) return;

	  if (substr_count($text_parsed, 'pic/smilies/') > 2) barkk(__('Abuz de smile-uri, vă rugăm să folosiți mai puține, apăsați Back și editați-vă mesajul.'));
	  if (substr_count($text_parsed, '<img') > 5) barkk(__('Abuz de imagini, vă rugăm să folosiți mai puține, apăsați Back și editați-vă mesajul.'));

	  //Validate message, allow only 2 url
	  if (substr_count($text, '://') > 2 && $CURUSER['class'] < 1) barkk(__('Abuz de adrese internet.'));

	  //Validate message, allow only 2 url
	  if (substr_count($text, 'www.') > 2 && $CURUSER['class'] < 1) barkk(__('Abuz de adrese internet.'));

	  if (substr_count($text, '[url') > 2 && $CURUSER['class'] < 1) barkk(__('Abuz de adrese internet.'));

	  $userIsLessThan1DaysOld = (time() - strtotime($CURUSER['added'])) < 60*60*24*1;
	  $userIsLessThan30DaysOld = (time() - strtotime($CURUSER['added'])) < 60*60*24*30;
	  $userDidntUploadOrDownloadAnything = $CURUSER['uploaded'] == 0 || $CURUSER['downloaded'] == 0;

	  if ($userIsLessThan1DaysOld) {
	  	  barkk(__('Utilizatorul dvs este prea nou pentru a scrie comentarii.').'<br><br>'.
	  	  		__('După aproximativ 1 zi de la înregistare puteți începe a scrie comentarii.').'<br><br>'.
	  	  		__('Ne pare rău pentru incomoditățile provocate.')
	  	  );
	  }

	  if ($userIsLessThan30DaysOld && $CURUSER['uploaded'] == 0) {
	  	  $array_url_list = array('://','www.','[url');
	  	  foreach($array_url_list AS $array_url_item) {
	  	  	  if (stripos($text,$array_url_item) !== FALSE) {
				barkk(__('Utilizatorul dvs este prea nou ca să poată scri comentarii cu adrese internet.').'<br><br>'.
					  __('Din cauza multiplelor abuzuri a trebuit să introducem această restricție.').'<br><br>'.
				  	  __('Ne pare rău pentru incomoditățile provocate.')
				);
	  	  	  }
	  	  }
	  }
}
