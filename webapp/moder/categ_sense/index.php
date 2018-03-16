<?php
	include dirname(__FILE__).'/conf.php';
	
	stdhead($_script_conf['title']);
	
	echo '<div align="left">';
	
	//$rows = fetchAll("SELECT * FROM {$_script_conf['table']}");
	$rows = _get_all_catetags();
	
	if ($_script_conf['architecture'] == 'tree') {
		$rows = array_merge($default_categs,$rows);
		
		// Search for father 0, and then recursive..
		
		$categs_org = array_set_index($rows,'father');
		
		tree_show_sense_categories('root',$categs_org);
	}
	echo '</div>';
	stdfoot();