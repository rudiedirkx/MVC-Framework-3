<?$this->assign('_szHtmlTitle', 'Oeleboele ;)')?>
<?$this->assign('_szHtmlHead', '<script type="text/javascript" src="/js/mods/kk/juden.js"></script>')?>

<link rel="stylesheet" type="text/css" href="/css/oele.css" />
<script type="text/javascript" src="/js/mootools.js"></script>
<style type="text/css">
* {
	margin : 0;
	padding : 0;
}
table table td { padding : 20px; }
</style>

<table border="1"><tr>
<td>
<?$width = 150; $border=5; include 'subtpls/inc.menu.tpl.php'?>
</td><td>
<ul>
	<? foreach ( $arrList AS $v ): ?><li><? echo $v?></li><? endforeach ?>
</ul>
</td><td>
	<?= $template->helpers->html_options($arrList, 'somelist') ?>
</td>
</tr></table>
