<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$baseDir = dirname(__FILE__);
$baseDir = dirname($baseDir);

$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
if (@$pathInfo) {
  $baseUrl .= dirname($pathInfo);
} else {
  $baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
}
$baseUrl = dirname($baseUrl);
$rmConfig = array();

require_once "../config/RecipeMinder.php";
require_once "functions.php";
?>
<html>
<head>
<script>
	function do_add_recipe()
	{
		top.rm_recipe.location.replace("add_recipe.php");
	}
</script>
</head>
<body>
<table cellspacing="0" border="1" width="100%" cellpadding="3">
<tr><td align="">

<form action="search.php" target="rm_recipe" method="get">
Search <select name="stype" size="1" id="stype" class="text">
	<option selected="selected">Ingredients</option>
	<option>Name</option>
	<option>Type</option>
	<option>Comments</option>
	<option>Key Words</option>
</select>
for: <input type="text" name="smatch" id="smatch" size="40" />
<br><br><center><input type="submit" name="button" value="Submit" /></center>
</form>
</td>
<td valign="top">Add:
<form action="add.php" target="rm_recipe" method="get">
<center><input type="button" name="add" value="Recipe" onclick="do_add_recipe();"><br>
<input type="submit" name="button" value="Ingredient"></center>
</form>
</td></tr></table>
</body>
</html>
