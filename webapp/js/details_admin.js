(function($){
	$('#get_torrent_owner').click(function() {
		if (confirm("Autorul trebuie cerut doar in cazuri strict necesare. Aceasta actiune va fi inregistrata!") != true) return;
		$(this).hide();
		$.ajax({
			url: '/details.php',
			type: 'POST',
			data: {id:torrents_md_torrent_id, get_torrent_owner:1},
			success: function(data){
				$('#owner_username').html(data);
			}
		});
	});
})($j);