function PopStamps(name,lang) {
	if ($('iframe_stamps') == null) {
		var _iframe = document.createElement('iframe');
		_iframe.src = './stamps_popup.php?&text='+name+'&container=iframe_stamps&lang='+lang;
		_iframe.width=790;
		_iframe.height=200;
		_iframe.id='iframe_stamps';
		$('after_comment_box').appendChild(_iframe);
	}
	show_it('iframe_stamps');
}
function PopSmilies(name,lang) {
	if ($('iframe_smilies') == null) {
		var _iframe = document.createElement('iframe');

		_iframe.src = './smilies_popup.php?&text='+name+'&container=iframe_smilies&lang='+lang;
		_iframe.width=790;
		_iframe.height=350;
		_iframe.id='iframe_smilies';


		$('after_comment_box').appendChild(_iframe);
	}

	show_it('iframe_smilies');
}

var autoCheckTimer = false;
var autoCheckText = '', newMsgTimer;
var autoCheckNoChangesCounter = 0;
var checkerOneTimeSoundOff = false;
var checkerError = 0;

function TurnAutoCheckTimer() {
	if (autoCheckTimer == false) {
		autoCheckText = $('activateTimerText').innerHTML;
		$('activateTimerText').innerHTML = langPostsChecking;
		autoCheckTimer = true;
		// Check if we have the sound loaded
		if ( !($('newPostSndNew')) ) {
			$audio = '<audio id="newPostSndNew" src="/sounds/inbound.mp3" style="display:none">';
			jQuery($audio).appendTo(document.body);
		}
	} else {
		$('activateTimerText').innerHTML = autoCheckText;
		$('activateTimerText').style.backgroundColor='#ECE9D8';
		autoCheckTimer = false;
		clearTimeout(newMsgTimer);
	}
	checkForNewPosts();
}

function PlayInboundAudio() {
	var newPostSndNew = jQuery('#newPostSndNew');
	if (newPostSndNew) {
		newPostSndNew[0].play();
	}
}

function checkForNewPosts() {
	// Give a clue to the user, what now we check for new messages
	$('activateTimerText').style.backgroundColor='#D8D7C4';
	post_data('./forum_new_post.php', 'topicId=' + topicId + '&lastMsg=' + lastMsg, function(a,b,c){
		checkForNewPosts_dataReceive(a,b,c);}, true
	);
}

function checkForNewPosts_dataReceive(data, statusCode, statusMessage) {
	$('activateTimerText').style.backgroundColor='#ECE9D8';
	if (autoCheckTimer == false) return;

	if (data.search(/^\{(.+)\}$/) == 0) { //Check integrity
		eval('var naturalizator='+data); //because data must be in json format
	} else {
		// Try 3 times before give up
		if (checkerError >= 3) {
			TurnAutoCheckTimer(); //Data corupted, we can't use ajax
			alert('Bad data received, a error occur, try 3 in 3 sec checker later.');
			checkerError = 0;
			return;
		}
		newMsgTimer = setTimeout(function(){checkForNewPosts();},10000);
		checkerError = checkerError +1;
		return;
	}
	// Anulate any counter errors
	checkerError = 0;

	// That mean error
	if (naturalizator.state == 0) {
		alert(naturalizator.error);
		TurnAutoCheckTimer();
		return;
	}
	autoCheckNoChangesCounter++;
	var frequence = 3000;
	// Decrease the frequence of check, when no posts
	if (autoCheckNoChangesCounter > 10) var frequence = 6000;
	else if (autoCheckNoChangesCounter > 30) var frequence = 15000;
	else if (autoCheckNoChangesCounter > 50) var frequence = 30000;
	else if (autoCheckNoChangesCounter > 100) var frequence = 60000;


	// That mean, no changes
	if (naturalizator.state == 2) {
		newMsgTimer = setTimeout(function(){checkForNewPosts();},frequence);
		return;
	}
	// That mean, new messages
	if (naturalizator.state == 1) {
		// Reset the frequence decreaser
		autoCheckNoChangesCounter = 0;
		frequence = 3000;

		lastMsg = naturalizator.lastmsg;
		// Hm, some error
		if ( !(lastMsg > 0) ) {
			alert('Some error occur, new posts checker has been disabled');
			TurnAutoCheckTimer();
			return;
		}

		var forumPosts = $('forumPosts');

		// Check if line doesnt exist
		if ( !($('newPostStartLine')) ) {
			// Put on the last message
			var redLine = document.createElement('hr');
			redLine.id = 'newPostStartLine';
			forumPosts.appendChild(redLine);
			// Remove the green line after 3 minutes
			setTimeout( function(){_removeNode($('newPostStartLine')); }, 180000);
		}


		var div = document.createElement('div');
		div.innerHTML = naturalizator.html;

		var divelements = div.childNodes.length;
		for (var i=0;i<divelements;i++) {
			if (!div.childNodes[i]) break;
			forumPosts.appendChild( div.childNodes[i].cloneNode(true) );
		}

		//Initializam spoilerele si iurl pentru noile posturi ;]
		initSpoilers();
		initIurl();
		newMsgTimer = setTimeout(function(){checkForNewPosts();},frequence);

		// Play the sound
		if (checkerOneTimeSoundOff == false) {
			PlayInboundAudio();
		}
		// Disable sound when user post itself
		if (checkerOneTimeSoundOff) checkerOneTimeSoundOff = false;

		return;
	}
}

function _insertAfter(newElement, referenceElement) {
	referenceElement.parentNode.insertBefore(newElement, referenceElement);
}

function submitPostAjax() {
	// We can't handle the images
	//if (_ge_by_name('file_image').value.length > 0) return true;
	// Ajax only when 3in3 check
	if (autoCheckTimer == false) return true;

	if ( _ge_by_name('body').value.length < 20 ) {
		alert('Mesajul e prea scurt.');
		return false;
	}

	var sendStr = 'action=post&ajax=1&topicid='+_ge_by_name('topicid').value + '&body=' + encodeURIComponent(_ge_by_name('body').value);
	post_data('./forum.php', sendStr,function(a,b,c){submitPostAjax_answer(a,b,c);}, true);

	return false;
}

function submitPostAjax_answer(data, statusCode, statusMessage) {
	if (data.search(/^\{(.+)\}$/) == 0) { //Check integrity
		eval('var naturalizator='+data); //because data must be in json format
	} else {
		alert('Your post was not added. Deactivate the 3in3 sec checker, and click submit again.');
		return;
	}
	// Reset the counters
	autoCheckNoChangesCounter = 0;
	frequence = 3000;
	clearTimeout(newMsgTimer);
	checkForNewPosts();
	checkerOneTimeSoundOff = true;
	// Clear the text box
	_ge_by_name('body').value = '';
}

/*
	Watcher section
var langWatchOn="',$lang['watch_on'],'";
	    var langWatchOff="',$lang['watch_off'],'";
	    var watchStatut=',(($watchOn)?'true':'false'),';
		var topicId=',$topicid,';
*/

function Watcher() {
	if (watchStatut) {
		$('watcherText').innerHTML = langWatchOn;
		$('watcherText').style.backgroundColor = '#ECE9D8';
		watchStatut = false;
		var action = 'del';
	} else {
		$('watcherText').innerHTML = langWatchOff;
		$('watcherText').style.backgroundColor = '#D3F1E2';
		watchStatut = true;
		var action = 'add';
	}

	sendStr = 'ajax=1&type=topic&action='+action+'&thread='+topicId;

	var callback = function(data) {
		if (naturalizator != 1) {
			document.location = './watcher.php?'+sendStr;
		}
	}

	post_data('./watcher.php', sendStr, callback, true);
}

/**
	Cenzurator
**/

function initCens() {
	var censSpan = getElementsByClass('censlnk',$('forumPosts'),'span');
	for (var i=0,l=censSpan.length;i<l;i++) {
		var curElm = censSpan[i];
		addEvent(curElm,'click',function(e){cens(e);});
	}
}

function cens(e) {

	var lnk = get_object_from_event(e);

	var postId = lnk.getAttribute('customId');

	var msgTable = $j(lnk).parents("table:first").next('table:first')[0];
        	//if (firstTry && firstTry.tagName && firstTry.tagName.toLowerCase()=='table') var msgTable = firstTry;

	var tds = msgTable.getElementsByTagName('td');

	// Avatar
	tds[0].parentNode.style.backgroundColor='#E6CCD0';
	tds[0].innerHTML = '';
	// Message
	tds[1].innerHTML = '<div align="center">Cenzurat</div>';

	sendStr = 'ajax=1&action=censor&postId='+postId;
	post_data('./forum.php', sendStr,function(){}, true);


	var cenzText = document.createTextNode('Cenzurat');

	lnk.parentNode.insertBefore(cenzText, lnk);

	_removeNode(lnk);
}

/**
	Raport a msg
*/

function initRaport() {
	var censSpan = getElementsByClass('raportareLnk',$('forumPosts'),'span');
	censSpan[censSpan.length] = getElementsByClass('raportareLnk',$('forumPosts_first'),'span')[0];

	for (var i=0,l=censSpan.length;i<l;i++) {
		var curElm = censSpan[i];
		if (!curElm) continue;
		addEvent(curElm,'click',function(e){raport(e);});
	}
}

function raport(e) {
	var lnk = get_object_from_event(e);

	var postId = lnk.getAttribute('customId');
	var forumId = lnk.getAttribute('customforumid');

	lnk.innerHTML = lang_raport_ok;
	lnk.className = '';

	var callback = function(a,b,c) {

	}

	sendStr = 'ajax=1&action=postRaport&forumid='+forumId+'&postId='+postId;
	post_data('./forum.php', sendStr,function(a,b,c){callback(a,b,c);}, true);
}

function onForumLoad() {
	initCens();
	initRaport();
}

$j(document).ready(function(){ onForumLoad(); });

function _insertBefore(newElement, referenceElement) {
	referenceElement.parentNode.insertBefore(newElement, referenceElement);
}

initSpoilers();


(function($){ // Closures, nu poluam spatiul global

var forumLikeLoading = $('<span class="like_loader" style="display:none"></span>');
$(document).ready(function($){
	$('.post_like').click(function(){
			var postid = this.getAttribute('customId');
			var topicid = this.getAttribute('topicId');
			var curElm = $(this);
            var spanParent = curElm.parent();

            if ($('.like_loader',spanParent).length == 0)
                curElm.before(forumLikeLoading);

            $('.like_loader',spanParent).show();

			curElm.hide();

			$.ajax({
				url: '/forum.php',
				type: 'POST',
				data: {ajax:1, postid:postid, topicid:topicid, action:'like'},
				success: function(data){
					$('.like_loader',spanParent).hide();
                    $('.post_unlike',spanParent).show();

				    var likes=parseInt(data);
                    var numberContainer = $('.likes_number',spanParent);
                    var thumb = $('.thumbup',spanParent);
                    if (isNaN(likes)) likes = 0;
                    if (likes == 0) {
                        numberContainer.text('');
                        thumb.hide();
                    } else {
                        numberContainer.text(' ' + likes + ' ' + langOameni);
                        thumb.show();
                    }
				}
			});

		});

	$('.post_unlike').click(function(){
			var postid = this.getAttribute('customId');
			var topicid = this.getAttribute('topicId');
			var curElm = $(this);
            var spanParent = curElm.parent();

            if ($('.like_loader',spanParent).length == 0)
                curElm.before(forumLikeLoading);

            $('.like_loader',spanParent).show();
			$(this).hide();

			var curElm = $(this);
			jQuery.ajax({
				url: '/forum.php',
				type: 'POST',
				data: {ajax:1, postid:postid, topicid:topicid, action:'delete_like'},
				success: function(data){
					$('.like_loader',spanParent).hide();
                    $('.post_like',spanParent).show();

				    var likes=parseInt(data);
                    var numberContainer = $('.likes_number',spanParent);
                    var thumb = $('.thumbup',spanParent);
                    if (isNaN(likes)) likes = 0;
                    if (likes == 0) {
                        numberContainer.text('');
                        thumb.hide();
                    } else {
                        numberContainer.text(' ' + likes + ' ' + langOameni);
                        thumb.show();
                    }
				}
			});

		});

	/*
		ajax-ing edit post
	 */
	var $formEditPost = $('<div>').html('<textarea name="post_edit_body" class="withBbcode"></textarea><span>Okay</span>');
	$('.post_edit').click(function(e)
	{
		if(e.which!==1) return;
		var $v = $(this);
		var $td = $v.closest('table').next().find('td').eq(1);

		if($td.hasClass('post_editing')) return e.preventDefault();

		var $loadingImg = forumLikeLoading.clone().insertAfter($v).show();
		$v.hide();

		var url = $v.attr('href') +'&ajax=1';

		var cache = $td.data('cacheEdit');
		(cache) ? doWithTextPost(cache): $.getJSON(url, doWithTextPost);

		function doWithTextPost(data)
		{
			//TODO: check if no error in data

			var h = $td.addClass('post_editing').outerHeight();
			$td.empty();
			var $form = $formEditPost.clone().appendTo($td);
			var $text  = $form.find('textarea').val(data).height(h).keydown(function(e)
			{
				if (e.ctrlKey && e.keyCode === 13)
					$btn.click();
			});
			var $btn = $form.find('span').one('click', function()
			{
				$(this).html('<img src="/pic/loading2.gif" />');
				$td.data('cacheEdit', $text.val());
				$.post(url, { body: $text.val() }, function(pdata) {
					$td.removeClass('post_editing').html( pdata );
				} );
			});

			$loadingImg.hide();
			$v.show();
		}

		e.preventDefault();
	});


	/*
	 ajax-ing delete post
	 */
	$('.post_delete').click(function(e)
	{
		if(e.which!==1) return;
		var $v = $(this);
		var $td = $v.closest('table').next().find('td');

		var $loadingImg = forumLikeLoading.clone().insertAfter($v).show();
		$v.hide();

		var sure = confirm("Sanity check: You are about to delete a post. Are you sure?");

		if(sure)
		{
			var id = $v.attr('href').replace(/.+postid=/,'');
			l('delete post', id);

			$.post('forum.php', { action: 'deletepost', sure: "here", postid: id }, function(data)
			{
				$v.siblings().remove();
				$td.eq(0).empty().end().eq(1).html('Mesajul a fost șters').css('text-align','center');
			});
		} else {
			$v.show();
			$loadingImg.hide();
		}

		e.preventDefault();
	});

});
})($j);


/* Post likes / unlikes */

(function($) {

$(document).ready(function($){
  $(document).on('click', '.useful_link .lnk', function() {
  	var spanParent = $(this).parent();
    var postid     = spanParent.data('customid');
    var topicid    = spanParent.data('topicid');

    var linkAction = $(this).data('action');

    $.ajax({
      url: '/forum.php',
      type: 'POST',
      data: {ajax: 1, postid: postid, topicid: topicid, action: linkAction},
      dataType: 'json',
      success: function(data) {
        spanParent.replaceWith(data.html);
      }
    });

  });
});

$('a.show_reveal_warning').click(function() {
	if (confirm('Atenție, mesajul poate fi jignitor. Întradevăr dorești să-l vezi ?')) {

	} else {
		return false;
	}
});

})($j);

