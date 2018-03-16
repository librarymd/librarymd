	<br>

{lang_comment_rules}

    <h3 class="center">{lang_comment_add_new}</h3>

	  <form method="post" action="comment.php?action=add" name="comment" id="comment">
	  <input type="hidden" name="tid" value="{torrentid}"/>
	  <textarea name="text" id="posttext" style="width:100%; height: 300px;" value="" style="width:98%;border-color:#A79F72;"></textarea>
	  <p>
      <input type="submit" id="abc" value="{lang_comment_send_comment}"/>
      <b>(asigură-te că respecți eticheta de comunicare)</b>
    </p>

	  </form>
    <div id="after_comment_box"><br></div>
