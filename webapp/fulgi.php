<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors','1');

require "include/bittorrent.php";


loggedinorreturn();

$cur_lang = get_lang();

$fulgi_url_array = array('/pic/fulgi/snow0.gif',
						'/pic/fulgi/snow1.gif',
						'/pic/fulgi/snow2.gif',
						'/pic/fulgi/snow4.gif',
						'/pic/fulgi/snow5.gif',
						'/pic/fulgi/snow6.gif',
						'/pic/fulgi/snow7.gif',
						'/pic/fulgi/snow8.gif',
						'/pic/fulgi/heart.gif',
						'/pic/fulgi/marguerit.gif',
						'/pic/fulgi/sne.gif',
						'/pic/fulgi/star.gif');

if(count($_POST) >= 1){
	$f_no =		(int)$_POST['fulgi_no'];

	if ($f_no > 75) $f_no = 75; // Max 100

	$f_enable = ($_POST['fulgi_enable']==1?1:0);
	$f_url =	(strlen($_POST['custom_url'])?$_POST['custom_url']: $_POST['fulgi_url']); // custom_url have priority

	$fulgi = fetchRow('SELECT * FROM an_nou_fulgi WHERE user_id=:user_id', array('user_id'=>get_current_id()) );

	if ($fulgi == NULL) {
		q('INSERT INTO an_nou_fulgi
			SET user_id=:user_id, fulgi_url=:fulgi_url, fulgi_no=:fulgi_no, fulgi_enable=:fulgi_enable',
			array('user_id'=>get_current_id(), 'fulgi_url'=>$f_url, 'fulgi_no'=>$f_no, 'fulgi_enable'=>$f_enable));
	} else {
		Q('UPDATE an_nou_fulgi
			SET fulgi_url=:fulgi_url, fulgi_no=:fulgi_no, fulgi_enable=:fulgi_enable
			WHERE user_id=:user_id',
			array('user_id'=>get_current_id(), 'fulgi_url'=>$f_url, 'fulgi_no'=>$f_no, 'fulgi_enable'=>$f_enable));
	}
	mem_delete('fulgi_'.get_current_id()); // Se va regenera automat in bittorrent.php
	header('Location: fulgi.php');exit();
}

$cur_lang = get_lang();
if($cur_lang == 'ro' )
	stdhead(('Fulgi'));
else
	stdhead(('Снежки'));

?>

<form action="fulgi.php" method="POST">
<table border="1" cellpadding="5">
	<tr>
		<td colspan="3">
			<input type="checkbox" value="1" name="fulgi_enable" <?=$SNOW_Enabled_UP==1?"CHECKED":""?>><?php if($cur_lang == 'ro' ) echo "Activează fulgi"; else echo "Включить снежки"?>
		</td>
	</tr>
	<tr>
		<td>
		<?php
		if($cur_lang == 'ro')
			echo 'Număr fulgi';
		else
			echo 'Количество снежков';
		?>
		</td>
		<td colspan="2">
			<input type="text" name="fulgi_no" value="<?=$SNOW_no_UP?>"/>
		</td>
	</tr>
	<tr>
		<td>
		<?php
		if($cur_lang == 'ro')
			echo 'Tip fulgi';
		else
			echo 'Тип снежков';
		?>
		</td>
		<td>
			<select name="fulgi_url" onChange="javascript: document.getElementById('fulgi_img').src=this.options[this.selectedIndex].value;">
				<?php
					$standart_snow = false;
					foreach($fulgi_url_array as $f_url){
						if ($f_url == $SNOW_Picture_UP) {
							echo "<option value=\"", $f_url, "\" SELECTED>", $f_url, PHP_EOL;
							$standart_snow = true;
						} else
							echo "<option value=\"", $f_url, "\">", $f_url, PHP_EOL;
					}
				?>
			</select>
		</td>
		<td style="background-color: #3E6692">
			<img name="fulgi_img" id="fulgi_img" src="<?=esc_html($SNOW_Picture_UP)?>"/>
		</td>

	</tr>

	<tr>
		<td>Custom</td>
		<td colspan="2"><input type="text" name="custom_url" size="65" <?php if (!$standart_snow):?>value="<?=esc_html($SNOW_Picture_UP)?>"<?php endif;?>  >
			<br><?php if($cur_lang == 'ro' ) echo "Exemplu"; else echo "Пример"?>: http://www.toto.com/pic/fulgi/snow2.gif
		</td>
	</tr>

	<tr>
		<td colspan="3" align="center">
			<br/>
			<input type="submit" value="<?php if($cur_lang == 'ro') echo "Salvează"; else echo 'Сохранить'?>">
		</td>
	</tr>

</table>
</form>

<? stdfoot(); ?>
