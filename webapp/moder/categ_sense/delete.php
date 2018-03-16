<?php
	include dirname(__FILE__).'/conf.php';
	
	// Doar sysopii
	title_header('Șterge',true);
	if (isset($_POST['confirm'])) {
		$childs = fetchOne('SELECT count(*) FROM '.get_table_name().' WHERE father=:id',array('id'=>$_POST[get_pk_name()]) );
		if ($childs > 0) {
			die('Categoria are subcategorii, sterge dintai subcategoriile');
		}
		q('DELETE FROM '.get_table_name().' WHERE '.get_pk_name().'=:id',array('id'=>$_POST[get_pk_name()]) );
		
		_categtags_modified();
		redirect('./index.php');
	}
	
	$row = fetchRow('SELECT * FROM '.get_table_name().' WHERE '.get_pk_name().'=:id',array('id'=>$_GET[get_pk_name()]) );
	
	if (!$row) barkk('No row found');
	
	echoe('Confirmă ștergerea lui %s',$row[get_column_name()]);
?>
<form method="POST">
	<input type="hidden" name="confirm" value="1">
	<input type="hidden" name="<?=get_pk_name()?>" value="<?=$_GET[get_pk_name()]?>">
	<input type="submit" value="Confirmă">
</form>