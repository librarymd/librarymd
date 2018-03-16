<?php
	include dirname(__FILE__).'/conf.php';
	
	title_header('Editează',true);
	
	$row = fetchRow('SELECT * FROM '.get_table_name().' WHERE '.get_pk_name().'=:id',array('id'=>$_GET[get_pk_name()]) );
	
	if (!$row) barkk('No row found');
	
	echo '<form method="POST">';
	echo '<input type="hidden" name="action" value="edit">';
	echoe ('<input type="hidden" name="%s" value="%d">',get_pk_name(),$row[get_pk_name()]);
	echo '<table cellspacing="0" cellpadding="5">';
	
	$action_type = 'edit';
	
	include dirname(__FILE__).'/new_edit_same.php';
	
	form_element('Editează','submit','Editează','');
	
	
	echo '</table>';
	echo '</form>';
	
	stdfoot();