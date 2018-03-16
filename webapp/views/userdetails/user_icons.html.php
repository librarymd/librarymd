<tr id="user_icons">
  <td class="rowhead">Iconițe</td>
  <td colspan="2">
  <?php foreach ($context->user_icons_config as $icon): ?>
    <div>
      <a href="<?=$icon['url']?>">
        <img src="<?=$icon['img']?>" height="16"/><br/>
      </a>
      <input type="hidden" name="user_icons_available" value="1"/>
      <input type="checkbox" name="user_icons[]" value="<?=$icon['id']?>" <?php
        if (in_array($icon['id'], $context->user_icons)) echo "checked";
      ?>/><br/>
      <?=$icon['name']?>
    </div>
  <?php endforeach ?>
  </td>
</tr>