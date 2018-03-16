<?php
include_once($WWW_ROOT . 'sphinx/utf8_normalize_map.php');
require_once($WWW_ROOT . 'moder/categ_sense/include.php');
require_once(dirname(__FILE__).'/desc_tags_map.php');

function normalizeString($s) {
	global $unicode_map_replace_what,$unicode_map_replace_with;
	return str_replace($unicode_map_replace_what,$unicode_map_replace_with, str_replace('.','', trim(mb_strtolower($s))) );
}

/*
 * Cam urat arata declararea functiei... :)
 */
function perform_switch($rule, $values,$debug,  $inputName, $tags, $values_orig, $allCategTags)
{
	//global $debug,  $inputName, $tags, $values_orig, $allCategTags;
		list($rule_type,$rule_param) = $rule;
		switch($rule_type) {
			case 'tag':
				$tags[] = $rule_param;
				if ($debug) {
					echo "$inputName Rule type: $rule_type Tag: $rule_param<br>\n";
				}
				break;
			case 'map':
				foreach($rule_param AS $tagId=>$mapValue) {
          if (isset($allCategTags[$tagId]) && $debug)
            echo "Duplicate map! $tagId<br/>\n";
          $allCategTags[$tagId] = true;

					// Handle the numerical ranges, nr1-nr2 / nr1<nr2
					if (strpos($mapValue,'-') !== false) {
						$mapValues = explode('-',$mapValue);
						if (is_numeric($mapValues[0]) && is_numeric($mapValues[1]) && $mapValues[0] < $mapValues[1]) {
							$mapValue = array();
							for($v=$mapValues[0]; $v<=$mapValues[1]; $v++) {
								$mapValue[] = $v;
							}
						}
					}
					if (!is_array($mapValue) && strpos($mapValue,',') !== false) {
						$mapValue = explode(',',$mapValue);
					}
					if (!is_array($mapValue)) $mapValue = array($mapValue);
					// Finally we will test for equality
					foreach($mapValue AS $mapValueCheck) {
						$mapValueCheck = normalizeString($mapValueCheck);
						if (in_array($mapValueCheck,$values)) {
							$tags[] = $tagId;
							if ($debug) echo "$inputName Rule type: $rule_type Tag: $tagId Match: $mapValueCheck in ".join(",",$values)."<br>\n";
							break;
						}
					}
				}
				break;
			case 'branch':
				// We should generate an map, for instance not
				$tagsToCheck = tags_get_all_tags_flat_by_parent($rule_param);
				$matched_tags = array();
				foreach($tagsToCheck AS $tag) {
					$tagId = $tag['id'];

          if (isset($allCategTags[$tagId]) && $debug)
            echo "Duplicate branch $rule_param! $tagId<br/>\n";
          $allCategTags[$tagId] = true;

					//$mapValue = array($tag['name_ro'],$tag['name_ru'],$tag['name_en']);

					$mapValue = array_merge(explode(",",$tag['name_ro']),explode(",",$tag['name_ru']),explode(",",$tag['name_en']));

					foreach($mapValue AS $mapValueCheck) {
						if (empty($mapValueCheck)) continue;
						$mapValueCheck = normalizeString($mapValueCheck);
						if ($debug == 2) echo $mapValueCheck,' in ',join(',',$values), ' ';
						if (in_array($mapValueCheck,$values) || strpos($values_orig,$mapValueCheck) !== false ) {
							$tags[] = $tagId;
							if ($debug) echo "$inputName Rule type: $rule_type Tag: $tagId Match: $mapValueCheck in ".join(",",$values)."<br>\n";
						}
					}
				}
				break;

		case 'mix':
			//Mix of options, gonna make it recursive
			if ($debug)
			{
				echo "$inputName Rule type: $rule_type Tag: array(".count($rule_param).") => <br>\n";
				if ($debug == 2 ) print_r($rule_param);
			}

			foreach($rule_param AS $rule)
			{
				if($debug)
				{
					echo "\n\t$inputName Rule type: $rule_type Curent Tag: {$rule[0]} - Type: {$rule[1]} => <br>\n";
				}
				list($tags, $allCategTags) = perform_switch($rule, $values,$debug,  $inputName, $tags, $values_orig, $allCategTags);
			}
			break;

			default:
				die($rule_type);
		}

	return array($tags, $allCategTags);
	}

function mapDescToTags($desc,$torrent)
{
	global $tagMap, $unicode_map_replace_what,$unicode_map_replace_with;
	$desc['category'] = $torrent['category'];
	$desc['torrentTitle'] = $torrent['name'];
	$debug = 0; // Can be 1 or 2, 2 more verbose
	$tags = array();
    $allCategTags = array();

	foreach($tagMap AS $inputName=>$rule) {
		if (!isset($desc[$inputName]))
		{
			continue;
		}
		$values_orig = normalizeString($desc[$inputName]);
		$values = $values_orig;
		$values = explode('-',$values);
		$values = join(',', $values);
		$values = explode(',',$values);
		// Clean and uniformize
		foreach($values AS &$value) {
			$value = normalizeString($value);
		}

		list($tags, $allCategTags) = perform_switch($rule, $values,$debug,  $inputName, $tags, $values_orig, $allCategTags);

	}

	//var_dump($tags);
	//$tags = join(',',$tags);
	//var_dump( fetchAll("select name_ro FROM torrents_catetags WHERE id IN ($tags)") );

	$rows = _get_all_catetags();
	$rows = array_set_index($rows,'id');

	$inherited_tags = array();


	if ($debug) echo "<br>";
	foreach($tags AS $tag) {
		$trace = tagGetTrace($tag,$rows);
		for($i=count($trace)-1;$i>=0;$i--) {
			if ($trace[$i]['checkable'] == 'yes' && $i!=0) {
				$inherited_tags[] = $trace[$i]['id'];
			}
			if (!isset($trace[$i]['name_ro'])) {
				if ($debug) var_dump($trace[$i]);
				//break;
			}
			if ($debug)	echo $trace[$i]['name_ro'].(($i!=0)?'->':'');
		}
		if ($debug) echo "<br>";
	}
	$tags = array_merge($tags,$inherited_tags);
  // Remove duplicates
  $tags = array_unique($tags); // It preserve index

  return array_values($tags); // Reorder indexes
}
function tagGetTrace($id,&$categ) {
	if ($categ[$id][0]['father'] == 0) return array($categ[$id][0]);
	$ret = array_merge( array($categ[$id][0]), tagGetTrace($categ[$id][0]['father'],$categ) ) ;
	return $ret;
}

/*
	This function will return an array of tags
*/
function get_tag_by_country($country_name) {
	$tags = tags_get_all_tags_flat_by_parent(164);
	$matched_tags = array();
	foreach($tags AS $tag) {
		$patern = '/'.escape_regexp($tag['name_ro']).'|'.escape_regexp($tag['name_ru']).'/ui';
		if (preg_match($patern,$country_name,$matches)) {
			$matched_tags[] = $tag['id'];
		}
	}
	return $matched_tags;
}
function escape_regexp($s) {
	return preg_replace('/[{}()*+?.\\^$|\/]/', '\\\\$0', $s);
}
function tags_get_all_tags_flat_by_parent($start_parent=0,$rows='') {
	if ($rows == '') $rows = _get_all_categories_sense_0();
	$result = array();
	foreach($rows[$start_parent] AS $row) {
		$result = array_merge($result,array($row));
		if (isset($rows[ $row['id'] ])) {
			$result = array_merge($result, tags_get_all_tags_flat_by_parent($row['id'], $rows ) );
		}
	}
	return $result;
}