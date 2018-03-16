<?php
	include dirname(__FILE__).'/conf.php';
	
	title_header('Nou',true);
	
	echo '<form method="POST">';
	echo '<input type="hidden" name="action" value="new">';
	echo '<table cellspacing="0" cellpadding="5">';
	
	$action_type = 'new';
	include dirname(__FILE__).'/new_edit_same.php';
	
	form_element('Adugă','submit','Adaugă','');
	
	
	echo '</table>';
	echo '</form>';
	
	stdfoot();