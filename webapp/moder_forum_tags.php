<?php
require_once("include/bittorrent.php");

loggedinorreturn();

if (get_user_class() < UC_MODERATOR) {
  die();
}

stdhead("Forum's tags");

function cleanForumTagsCache($forumid) {
	mem_delete('subcat_list:ro:forum:'.$forumid);
	mem_delete('subcat_list:ru:forum:'.$forumid);
}

if (ispost()) {

	$action = post('action');

	if ($action == 'new') {
		$category = post('category');
		$name_ro = post('name_ro');
		$name_ru = post('name_ru');
		Q('INSERT INTO forums_tags SET forum=:cat, name_ro=:ro, name_ru=:ru',
			array('cat'=>$category, 'ro'=>$name_ro, 'ru'=>$name_ru ) );
		cleanForumTagsCache($category);
	}

	if ($action == 'edit') {
		$tag_id = post('tag_id');
		$category = post('category');
		$name_ro = post('name_ro');
		$name_ru = post('name_ru');
		Q('UPDATE forums_tags SET forum=:cat, name_ro=:ro, name_ru=:ru WHERE id=:id',
			array('cat'=>$category, 'ro'=>$name_ro, 'ru'=>$name_ru, 'id'=>$tag_id) );
		cleanForumTagsCache($category);
	}
}


$tags = fetchAll(
	'SELECT forums_tags.*, forums.name_'.get_lang().' AS forum_name
	FROM forums_tags
	LEFT JOIN forums ON forums_tags.forum = forums.id
	ORDER BY forums.sort, forums_tags.name_ro'
);

?>

<h1>Taguri pe forum</h1>

	<a href="moder_forum_tags.php?action=new">Adaugă</a><br><br>
<?php if ( isset($_GET['action']) && ($_GET['action'] == 'new' || ($_GET['action'] == 'edit' && is_numeric($_GET['tag_id']) ) ) ): ?>

	<?php

		$action = $_GET['action'];

		echo '<h2>';
		if ($action == 'new') echo 'Adăugarea unui nou tag';
		elseif ($action == 'edit') echo 'Editarea unui tag';
		echo '</h2>';


		if ($action == 'edit') {
			$tag_id = $_GET['tag_id'];
			$tag = fetchRow('SELECT * FROM forums_tags WHERE id=:id', array('id'=>$tag_id) );
			$category = $tag['forum'];
			$name_ro = $tag['name_ro'];
			$name_ru = $tag['name_ru'];
		}
	?>



<form action="moder_forum_tags.php" method="POST">
<?php if ($action == 'new'): ?>
	<input type="hidden" name="action" value="new">
<?php elseif ($action == 'edit'): ?>
	<input type="hidden" name="action" value="edit">
	<input type="hidden" name="tag_id" value="<?=$tag_id?>">
<?php endif; ?>

<table cellpadding="10">
<tr>
		<td>Categoria</td>
		<td>
			<select name="category">
			<?php
			$userlang = get_lang();
			$categs = fetchAll("SELECT id,name_$userlang AS name,minclasswrite
							 FROM forums
							 ORDER BY sort" );
			foreach ($categs AS $categ) : ?>
				<option value="<?=$categ['id']?>" <?=(@$category == $categ['id'])?'selected':''?> ><?=$categ['name']?></option>
	  <?php endforeach; ?>
			</select>
		</td>
</tr>

<tr>
		<td>Nume română</td>
		<td><input type="text" name="name_ro" value="<?=esc_html(@$name_ro)?>"></td>
</tr>

<tr>
		<td>Nume rusă</td>
		<td><input type="text" name="name_ru" value="<?=esc_html(@$name_ru)?>"></td>
</tr>

<tr>
	<td colspan="2" align="center"> <input type="submit" value="<?=$action=='edit'?'Editează':'Adaugă'?>"> </td>
</tr>

</table>


</form>

<?php endif; ?>


<table cellpadding="10">

<tr>
	<td>Nume forum</td> <td>Nume romana / rusă</td> <td>Actiune</td>
</tr>

<?php
foreach ($tags AS $tag) :
?>

	<tr>
		<td><?=$tag['forum_name']?></td>
		<td><?=esc_html($tag['name_ro'])?> / <?=esc_html($tag['name_ru'])?></td>
		<td>[<a href="moder_forum_tags.php?action=edit&tag_id=<?=$tag['id']?>">Edit</a>] [<a href="moder_forum_tags.php?action=del&id=<?=$tag['id']?>">Del</a>]</td>
	</tr>

<?php endforeach;?>
</table>

<?php
stdfoot();
?>