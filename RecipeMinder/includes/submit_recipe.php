<?php
	$baseDir = dirname(__FILE__);
	$baseDir = dirname($baseDir);

	$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
	$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
	$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
	if (@$pathInfo)
	{
		$baseUrl .= dirname($pathInfo);
	}
	else
	{
		$baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
	}
	$baseUrl = dirname($baseUrl);
	$rmConfig = array();
 
	require_once "../config/RecipeMinder.php";
	require_once "functions.php";
/*
// Uncomment this section to see what has been passed into this script.
// if execution is to end after display, uncomment the "die" command.
echo 'Received the following information:<br>';
echo '<table>';
foreach($_GET as $variable => $value)
{
        echo "<tr><td>Variable:</td><td> " . $variable . "</td><td>Value:</td><td> $value</td></tr>";
}
echo '</table><br><br>';
//die();
*/
	$link = mysql_connect($rmConfig['dbhost'], $rmConfig['dbuser'], $rmConfig['dbpasswd'])
		or die('Could not connect: ' . mysql_error());
	mysql_select_db($rmConfig['dbase']) or die('Could not select database');
	if ( $_GET["portions"] == "" )
	{
		$portions=0;
	}
	else
	{
		$portions=$_GET["portions"];
	}
	
	$sql='insert into main (name, source, instructions, portions, type)values ("'.$_GET["recipe"].'", "'.$_GET["source"].'", "'.$_GET["instructions"].'", ';
	$sql.=$portions.', '.$_GET["recipe_type"].')';
//	echo "<br>SQL>  ".$sql;
	mysql_query($sql) or die('Error:  '.mysql_error());
	$rindex=mysql_insert_id($link);

	$ing_index=0;
	while (true)
	{
// Add conversion to decimal in this loop for amt!!
		$insamt=toDecimal($_GET["amt".$ing_index]);
		$lnk_insert='insert into links values ('.$rindex.', '.$_GET["ing".$ing_index].', '.$insamt.', "'.$_GET["measure".$ing_index].'")';
//		echo "<br>SQL>  ".$link_insert;
		mysql_query($lnk_insert) or die('Error:  '.mysql_error());
		$ing_index++;
		if ($_GET["ing".$ing_index] == "" )
			break;
	}

	if ($_GET["keywords"] != "")
	{
		$sql='insert into keywords values ('.$rindex.', "'.$_GET["keywords"].'")';
//		echo "<br>SQL>  ".$sql;
		mysql_query($sql) or die('Error:  '.mysql_error());
	}
	echo 'Recipe submitted';

?>
