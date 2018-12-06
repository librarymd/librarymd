<html>
<head>
<script language=javascript>
function StampIT(stamp){
	var text=parent.document.getElementById('{text}');
	text.value += " "+stamp+" ";
    text.focus();
}
</script>
<title>Smilies</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="./styles/default.css" type="text/css">
<style type="text/css">
html,body {
  background: url(../pic/px_transparent.gif) repeat-x;
}
img {
  border:0;
}
</style>
</head>
<body>

<div align="right"><a class="pointer" onclick="parent.document.getElementById('{container}').className='hideit'">[x]</a>&nbsp;</div>
<table width="99%" border=0 cellspacing="2" cellpadding="2" align="center">
<tr><td colspan="3" align=center><h1>{stamps_label}</h1></td></tr>
{smilies_html}
</table>
<div align="right"><a class="pointer" onclick="parent.document.getElementById('{container}').className='hideit'">[x]</a>&nbsp;</div>
<br>&nbsp;
</body></html>