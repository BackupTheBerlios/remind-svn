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

$recipeIndex=$_GET['index'];

$link = mysql_connect($rmConfig['dbhost'], $rmConfig['dbuser'], $rmConfig['dbpasswd'])
   or die('Could not connect: ' . mysql_error());
mysql_select_db($rmConfig['dbase']) or die('Could not select database');

$query='select m.*, type_name from main m, recipe_types r where rindex = '.$recipeIndex.' and m.type = r.type_id';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$main=mysql_fetch_assoc($result);

$defserv=$main['portions'];
if ($defserv == 0)
{
	$defserv=1;
}
$adjserv=$_GET['calcamt'];
if ( !strlen($adjserv))
{
	$adjserv=$defserv;
}

$query='select amount*'.$adjserv.' / '.$defserv.' amount, measurement, ing_name from links, ingredients where links.ing_index = ingredients.ing_index and links.rindex='.$recipeIndex;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

echo '<table width="100%" align="center"><tr><td valign="center" align="center"><h1>'.$main["name"].'</h1></td>';
echo '<td><img src="'.$baseUrl.'/graphics/rm_logo.png"></td></tr></table>';
echo '<table>';
//echo "Ingredients go here . . .<br>";
while ($ings=mysql_fetch_assoc($result))
{
	echo '<tr><td align=right>' . toFraction($ings["amount"]) . '</td>';
	echo '<td>' . $ings["measurement"] . '</td>';
	echo '<td>' . $ings["ing_name"] . '</td></tr>';
}
echo '</table><br>';
echo '<div id="inst" style="margin-right:10%">';
echo $main["instructions"].'<br><br>';
echo '</div>';
if ($main['portions'] >0)
{
	echo '<br>Serves: ' . $adjserv.'<br>';
}
$query='select a.*, name from associated a, main ';
$query.='where primary_rindex = '.$recipeIndex;
$query.=' and rindex = secondary_rindex';
$query.=' order by type asc';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$numrows=mysql_num_rows($result);
if ( $numrows > 0 )
{
	$prevType=0;
	echo '<table width="80%" class="assoc" cellpadding="5">';
	while ($line=mysql_fetch_assoc($result))
	{
		echo '<tr><td width="15%" align="right">';
		switch ($line['type'])
		{
			case 1:
				echo 'Needs:';
				break;
			case 2:
				echo 'Similar to:';
				break;
			case 3:
				echo 'Goes good with:';
				break;
		}
		echo '</td><td>'.$line["name"].'</td></tr>';
	}
	echo '</table>';
}
$sql='select r_index, name, comment, date_format(date_added, "%M %d, %Y") cdate from comments where r_index = '.$recipeIndex;
$result=mysql_query($sql);
$numrows=mysql_num_rows($result);
if ($numrows > 0)
{
echo '<h4>Comments:</h4><table width="100%">';
	while ($line=mysql_fetch_assoc($result))
	{
		echo '<tr><td colspan=2><i>on '.$line["cdate"].' '.$line["name"].' said:</i></td></tr><tr><td width="50px"></td><td colspan=2>'.$line["comment"].'</td></tr>';
	}
	echo '</tr></table>';
}
?>
