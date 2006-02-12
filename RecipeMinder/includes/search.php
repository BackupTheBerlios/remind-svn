<?php

require("Sajax.php");

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

$sfield=$_GET['stype'];
$smatch=$_GET['smatch'];
$ltype=$_GET['ltype'];
$query=get_query($sfield, $smatch);

$query.=' order by type_name, sub_type';

$link = mysql_connect($rmConfig['dbhost'], $rmConfig['dbuser'], $rmConfig['dbpasswd'])
   or die('Could not connect: ' . mysql_error());
mysql_select_db($rmConfig['dbase']) or die('Could not select database');

function list_recipes($msg)
{
	parse_str($msg);
//	$sql='select name, rindex from main where type='.$rType.' order by name';
	$sql=get_query($sfield, $smatch);
	$sql.=" and m.type=".$rType." order by name";
	$result=mysql_query($sql);
	echo '<table width="99%"><tr>';
	$col=1;
	while($entry=mysql_fetch_assoc($result))
	{
		echo '<td width="33%" align="center">';
		echo '<br><a href="show_recipe.php?&index='.$entry['rindex'].'">'.$entry['name'].'</a></td>';
		$col++;
		if ($col % 3 == 1)
		{
			echo '</tr><tr>';
		}
	}
	echo '</tr></table>';
	return;
}

$sajax_request_type = "GET";
sajax_init();
sajax_export("list_recipes");
sajax_handle_client_request();

?>
<html>
<head>
<style>
ul
{
float:left;
width:100%;
padding:0;
margin:0;
list-style-type:none;
}
.pop {
background-color:#ffffff;
position:relative;
top:-1;
border-right:3px solid #787878;
border-bottom:3px solid #787878;
border-left:3px solid #efefef;
border-top:3px solid #efefef;
}
li
{
display:inline;
cursor:pointer;
text-decoration:none;
color:black;
background-color:#c8c8c8;
height:20px;
border-right:3px solid #efefef;
border-bottom:3px solid #efefef;
border-left:3px solid #787878;
border-top:3px solid #787878;
}
div#toprow
{
height:24px;
}
div#rowtwo
{
height:24px;
}
</style>
<script>
<?
sajax_show_javascript();
?>
function raiseTopTab(data)
{
	var up;
	up=document.getElementById("topLastClick").value;
	if (up != '')
	{
		document.getElementById(up).className="";
	}
	document.getElementById(data).className="pop";
	document.getElementById("topLastClick").value=data;
	document.getElementById("subLastClick").value="";
	switch(data)
	{
<?php
$tLine="";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($line = mysql_fetch_assoc($result))
{
	if ($line['type_name'] == $tLine)
		continue;
	echo "\t\tcase 'item".$line['type_id']."':\n\t\t\tshow".$line['type_name']."();\n\t\t\tbreak;\n";
	$tLine=$line['type_name'];
}
?>
	}
	document.getElementById("showEm").innerHTML="";
}
function raiseSubTab(data)
{
	var up;
	var rType;
	var sType;
	var sMatch;
	up=document.getElementById("subLastClick").value;
	if (up != '')
	{
		document.getElementById(up).className="";
	}
	document.getElementById(data).className="pop";
	document.getElementById("subLastClick").value=data;
	rType=data.substr(3)
	sType=document.getElementById("sType").value;
	sMatch=document.getElementById("sMatch").value;
	x_list_recipes("?&rType="+rType+"&sfield="+sType+"&smatch="+sMatch, showList);
}

function showList(new_data)
{
	document.getElementById("showEm").innerHTML=new_data;
}
<?php
$tLine="";
$eFunc="";
$tSub="";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($line = mysql_fetch_assoc($result))
{
	if ($line['type_name'] != $tLine)
	{
		echo $eFunc."\nfunction show".$line['type_name']."()\n{\n\tdocument.getElementById(\"rowTwo\").innerHTML='<ul>";
		$eFunc="</ul>'\n}";
	}
	if ($line['sub_type'] == $tSub)
		continue;
	echo "\t<li id=\"sub".$line['type_id']."\" onclick=\"raiseSubTab(this.id);\">&nbsp;".$line['sub_type']."&nbsp;</li>";
	$tLine=$line['type_name'];
	$tSub=$line['sub_type'];
}
echo $eFunc;
?>
</script>
</head>
<body>
<input type="hidden" id="topLastClick" value=""><input type="hidden" id="subLastClick">
<input type="hidden" id="sType" value="<? echo $sfield ?>"><input type="hidden" id="sMatch" value="<? echo $smatch ?>">
<div id="topRow">
<ul>
<?php
$tLine="";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($line = mysql_fetch_assoc($result))
{
	if ($line['type_name'] == $tLine)
		continue;
	echo '<li id="item'.$line['type_id'].'" name="'.$line['type_name'].'" onclick="raiseTopTab(this.id);">&nbsp;'.$line['type_name'].'&nbsp;</li>
';
	$tLine=$line['type_name'];
}
echo '</ul>';

?>
</div>
<div id="rowTwo">
</div>
<div id="showEm">
</div>
</body>
</html>
