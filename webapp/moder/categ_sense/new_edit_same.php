<?php
	if (isset($_POST['action'])) {
		$action = $_POST['action'];
		if ($action == 'new') {
			$parts = array();
			foreach($_script_conf_form AS $elementName=>$elementConf) {
				if ( isset($elementConf['default_value']) && empty($_POST[$elementName]) ) {
					$_POST[$elementName] = $elementConf['default_value'];
				}
				if (isset($elementConf['val_join']) && $elementConf['val_join']) $_POST[$elementName] = join(',',$_POST[$elementName]);
				$parts[] = sqlEscapeBind($elementName . ' = :g',array('g'=>$_POST[$elementName]));
			}
			$parts = join(',',$parts);
			q('INSERT INTO '.get_table_name().' SET '.$parts);
		}
		if ($action == 'edit') {
			$parts = array();
			foreach($_script_conf_form AS $elementName=>$elementConf)
			{
				if (isset($elementConf['val_join']) && $elementConf['val_join']) $_POST[$elementName] = join(',',$_POST[$elementName]);
				$parts[] = sqlEscapeBind($elementName . ' = :g',array('g'=>$_POST[$elementName]));
			}
			$parts = join(',',$parts);
			q('UPDATE '.get_table_name().' SET '.$parts . ' WHERE '.get_pk_name() . ' = :id', array('id'=>$_POST[get_pk_name()]) );
		}
		_categtags_modified();
	}
    
	// Code shared by new and edit
	foreach($_script_conf_form AS $elementName=>$elementConf) {
		$selectAutocomplete = '';
		$value = '';
		$rows = array();
		if (isset($elementConf['source'])) {
			if (isset($elementConf['source']['sql'])) {
				$rows = fetchAll($elementConf['source']['sql']);
			} else
			if (isset($elementConf['source']['function'])) {
				$rows = call_user_func($elementConf['source']['function']);
			}
			else throw new Exception('Source not found');
			$selectAutocomplete = array($elementConf['source'][0],$elementConf['source'][1],$rows);
		}
		if (isset($elementConf['values'])) {
			$selectAutocomplete = $elementConf['values'];
		}
		if (isset($elementConf['default'])) {
			$value = $elementConf['default'];
		}
		if (isset($elementConf['use_incomming_arg_as_value']) && $action_type == 'new') $value = $_GET['id'];
		if ($action_type == 'edit') $value = $row[$elementName];
		$html_opt = '';
		
		if (isset($_script_conf['form_default_text_html_opt']) && $elementConf['t'] == 'text' ) {
			if (!isset($elementConf['html_opt'])) $html_opt = $_script_conf['form_default_text_html_opt'];
		}
		if ($html_opt == '') {
			if (isset($elementConf['html_opt'])) $html_opt = $elementConf['html_opt'];
		}
		
		form_element($elementName,$elementConf['t'],$elementConf['l'],$value,$selectAutocomplete,$html_opt);
	}