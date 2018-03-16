<!DOCTYPE html>
<html><head>
<title><?= $title ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="/styles/default.css" type="text/css"/>
<link rel="stylesheet" href="/styles/main.css" type="text/css"/>

<script type="text/javascript" src="/lang/message.js"></script>
<script type="text/javascript" src="/js/lib.js"></script>
<script type="text/javascript" src="/js/menu.js"></script>
<script type="text/javascript" src="/js/jquery_v1.8.0.min.js"></script>
<script type="text/javascript" src="/js/jquery.plugins.js"></script>
<script type="text/javascript" src="/js/all.ourJS.js"></script>
<script type="text/javascript" src="/js/tmd_bbcode.js"></script>

<!--<link rel="icon"  href="/pic/favicon.png" type="image/png" />-->
</head>
<body>
<script>
var logo = '<?=$logo[0]?>',logo_width = '<?=$logo[1]?>',logo_main_title= '<?=$logo[2]?>', logo_map_title = '<?=$logo[2]?>',logo_map = '<?=$logo[4]?>',logo_map_link= '<?=$logo[5]?>';
message.lang = '<?php echo get_lang(); ?>';

var custom_html_to_menu = '';
</script>
<?php if (false) {
/*
<style>
#main_menu {
  background-color: #3e6691;
  border-width: 3px 0 3px 0;
  border-style: solid;
  border-color: #ada36c;
}
#tbl_menu {
  height: 46px;
}
#tbl_menu a {
  border-right: 2px solid #ada36c;
  display: inline-block;
  line-height: 24px;
  padding-right: 10px;
  padding-left: 8px;
}
#tbl_menu a:last-of-type {
  border-right: 0;
}
#tbl_menu img {
  display: none;
}
</style>
*/
}
?>

<?php
echo showCssLangHide();
if (!is_logged()) {
?>
<img style="display:none" src="/pic/transparency.gif">
<div id="overlay"></div>
<div id="navbar_login_menu" class="zburator_invizibil">
<form method="post" name="login_form" action="takelogin.php" onsubmit="return startLoginVerify();">
  <table id="login_form" border="0" cellpadding=5>
  <tr>
    <td colspan="2" align="right">
      <img style="cursor:pointer;" onClick="close_login_box();" src="/pic/close.gif" align="right">
    </td>
  </tr>
  <tr>
    <td class=rowhead style="padding-left:25px;"><?=__('User')?>:</td>
    <td align=left style="padding-right:25px;">
      <input type="text" size=30 name="username" id="navbar_login_menu_input_to_focus_on" />
    </td>
  </tr>
  <tr>
    <td class=rowhead><?=__('Parola')?>:</td>
    <td align=left><input type="password" size=30 name="password" /></td>
  </tr>
  <tr>
    <td colspan=2 align="left" style="border-bottom:0px;">
      <input type="checkbox" name="autologin" id="autologin" value="1" checked="checked" />
        <label for="autologin"><?=__('Autentificare automată la următoarea vizită')?></label>
    </td>
  </tr>
  <tr>
    <td colspan=2 align="center" style="border-top:0px;">
      <input type="submit" value="  <?=__('Intră')?>  " class="but_blue" style="font-weight: bold;padding: 4px;">
      <div id="login_status">&nbsp;</div>
    </td>
  </tr>
  <tr>
    <td colspan=2 align="center">
      <a href="signup.php"><?=__('Înregistrare')?></a> |
      <a href="recover.php"><?=__('Restabilirea parolei')?></a> |
      <br/>
      <a href="confirm_no_email.php"><?=__('Scrisoarea cu confirmare nu a venit')?></a>
    </td>
  </tr>
  </table>
</form>
</div>
<?php } ?>
<div id="no_td_border">
<!-- For those who have JS disabled-->
<noscript><style type="text/css">.sp-body { display:block; }</style></noscript>
<div id="top_menu"></div><!-- Top Menu Writed By JS -->
<div id="main_menu"></div><!-- Main Menu Writed By JS -->
<?php if(!is_logged()) { ?>

<div align="right">
<script type="text/javascript">
// Lang switcher
lang = readCookie('lang');
if (!lang || lang == 'ro') document.write('<a style="cursor:pointer;" onclick="createCookie(\'lang\',\'ru\');location.reload();"><b>RU</b></a> | RO&nbsp;');
else document.write('RU | <a style="cursor:pointer;" onclick="createCookie(\'lang\',\'ro\');location.reload();"><b>RO</b></a>&nbsp;');
</script>
</div>
<?php } ?>
