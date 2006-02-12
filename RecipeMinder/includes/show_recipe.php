<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

require("Sajax.php");

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

function saveInstructions($msg)
{
	parse_str($msg);
	$sql='update main set instructions="'.$ins.'" where rindex='.$tindex;
//	return $sql;
	mysql_query($sql) or die('Error:  '. mysql_error());
	return $ins;
}
function get_recipe_types($msg)
{
	$returnString='<select id="rType" onchange="getRecipesOfType()"><option value=0></option>';
	$sql='select * from recipe_types';
	$result=mysql_query($sql);
	while ($rtypes=mysql_fetch_assoc($result))
	{
		$returnString.='<option value='.$rtypes["type_id"].'>'.$rtypes["type_name"].'</option>';
	}
	$returnString.='</select>';
	return $returnString;
}
function get_recipe_names($msg)
{
	parse_str($msg);
	$returnString='<select id="rName">';
	$sql='select rindex, name from main where type='.$recipeType.' and rindex !='.$excIndex;
	$result=mysql_query($sql);
	while ($recipes=mysql_fetch_assoc($result))
	{
		$returnString.='<option value='.$recipes["rindex"].'>'.$recipes["name"].'</option>';
	}
	$returnString.='</select>';
	return $returnString;
}
function get_assoc($recipeIndex)
{
	$query='select a.*, name from associated a, main ';
	$query.='where primary_rindex = '.$recipeIndex;
	$query.=' and rindex = secondary_rindex';
	$query.=' order by type asc';
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	$numrows=mysql_num_rows($result);
	if ( $numrows > 0 )
	{
		$prevType=0;
		$retVal='<table width="80%" class="assoc" cellpadding="5">';
		while ($line=mysql_fetch_assoc($result))
		{
			$retVal.='<tr><td width="15%" align="right">';
			if ( $prevType != $line['type'] )
			{
				switch ($line['type'])
				{
					case 1:
						$retVal.='Needs:';
						break;
					case 2:
						$retVal.='Similar to:';
						break;
					case 3:
						$retVal.='Goes good with:';
						break;
				}
			}
			$retVal.='</td><td><a href="show_recipe.php?index='.$line["secondary_rindex"].'&calcamt=' . $adjserv . '">'.$line["name"].'</a></td></tr>';
		}
		$retVal.='</table>';
	}
	return $retVal;
}
function update_assoc($msg)
{
	parse_str($msg);
	$sql='insert into associated values('.$pIndex.','.$aIndex.',"'.$aType.'")';
	mysql_query($sql) or die('Error:  '.mysql_error());
	return get_assoc($pIndex);
}
function get_comments($recipeIndex)
{
	$sql='select r_index, name, comment, date_format(date_added, "%M %d, %Y") cdate from comments where r_index = '.$recipeIndex;
	$result=mysql_query($sql) or die('Error:  '.mysql_error());
	$numrows=mysql_num_rows($result);
	if ($numrows > 0)
	{
		$retval='<h4>Comments:</h4><table width="100%">';
		while ($line=mysql_fetch_assoc($result))
		{
			$retval.='<tr><td colspan=2><i>on '.$line["cdate"].' '.$line["name"].' said:</i></td></tr><tr><td width="50px"></td><td colspan=2>'.$line["comment"].'</td></tr>';
		}
		$retval.='</tr></table>';
	}
	return $retval;
}
function submit_comment($msg)
{
	parse_str($msg);
	$sql='insert into comments values('.$rIndex.', "'.$cName.'", "'.$newComment.'", curdate())';
	mysql_query($sql) or die('Error:  '.mysql_error());
	return get_comments($rIndex);
}

$sajax_request_type = "GET";
sajax_init();
sajax_export("saveInstructions","get_recipe_types","get_recipe_names","update_assoc","submit_comment");
sajax_handle_client_request();
?>
<html>
<head>
<script>
<?
sajax_show_javascript();
?>
function editSetUp()
{
	var Instructions;
	var buttonIndex;
	Instructions=document.getElementById("RecipeInstructions").innerHTML;
	buttonIndex=Instructions.indexOf("<br");
	document.getElementById("RecipeInstructions").innerHTML='<textarea id="Instructions" cols="60" rows="15">'+Instructions.substring(0,buttonIndex)+'</textarea>';
	document.getElementById("edInst").className="lefthide";
	document.getElementById("saveInst").className="leftshow";
}
function j_saveInstructions()
{
	var uRequest;
	uRequest="?&ins="+document.getElementById("Instructions").value+"&tindex="+document.getElementById("index").value;
	x_saveInstructions(uRequest, showInstructions);
}
function showInstructions(new_data)
{
	document.getElementById("RecipeInstructions").innerHTML=new_data+'<br>Serves: '+document.getElementById("adjServ").value;
	document.getElementById("edInst").className="";
	document.getElementById("saveInst").className="lefthide";
}
function assocRecipeType()
{
	var assocIndex;
	assocIndex=document.getElementById("assoc").selectedIndex;
	if (assocIndex == 0)
	{
		document.getElementById("assocRecipes").innerHTML='Add association: <select id="assoc" onchange="assocRecipeType()"><option value=0></option><option value=1>Needs</option><option value=2>Similar to</option><option value=3>Goes good with</option></select><br>';
	}
	else
	{
		x_get_recipe_types("",showAssocRecipeTypes)
	}
}
function showAssocRecipeTypes(new_data)
{
	var assocIndex;
	assocIndex=document.getElementById("assoc").selectedIndex;
	document.getElementById("assocRecipes").innerHTML='Add association: <select id="assoc" onchange="assocRecipeType()"><option value=0></option><option value=1>Needs</option><option value=2>Similar to</option><option value=3>Goes good with</option></select>&nbsp;&nbsp;'+new_data;
	document.getElementById("assoc").selectedIndex=assocIndex;
}
function getRecipesOfType()
{
	var rTypeIndex;
	var tIndex
	var requestStr;
	tIndex=document.getElementById("rType").selectedIndex;
	rTypeIndex=document.getElementById("rType").options[tIndex].value;
	requestStr='?&recipeType='+rTypeIndex+'&excIndex='+document.getElementById("index").value;
	x_get_recipe_names(requestStr,showRecipeSelect);
}
function showRecipeSelect(new_data)
{
	var beginningHTML;
	var oldHTML;
	var getTo;
	var assocIndex;
	var typeIndex;
	assocIndex=document.getElementById("assoc").selectedIndex;
	typeIndex=document.getElementById("rType").selectedIndex;
	oldHTML=document.getElementById("assocRecipes").innerHTML;
	getTo=oldHTML.search('<select id="rName">');
	if (getTo > 0)
	{
		beginningHTML=oldHTML.substring(0, getTo);
	}
	else
	{
		beginningHTML=oldHTML;
	}
	document.getElementById("assocRecipes").innerHTML=beginningHTML+'&nbsp;&nbsp;'+new_data+'&nbsp;&nbsp;<input type="button" value="Save" onclick="saveAssoc()">';
	document.getElementById("assoc").selectedIndex=assocIndex;
	document.getElementById("rType").selectedIndex=typeIndex;
}
function saveAssoc()
{
	var pIndex;
	var aIndex;
	var aType;
	var uRequest;
	pIndex=document.getElementById("index").value;
	aIndex=document.getElementById("rName").options[document.getElementById("rName").selectedIndex].value;
	aType=document.getElementById("assoc").options[document.getElementById("assoc").selectedIndex].value;
	uRequest='?&pIndex='+pIndex+'&aIndex='+aIndex+'&aType='+aType;
	x_update_assoc(uRequest,showAssoc);
}
function showAssoc(new_data)
{
	document.getElementById("currAssoc").innerHTML=new_data;
	document.getElementById("assocRecipes").innerHTML='Add association: <select id="assoc" onchange="assocRecipeType()"><option value=0></option><option value=1>Needs</option><option value=2>Similar to</option><option value=3>Goes good with</option></select><br>';
}
function submitComment()
{
	alert("We're here . . .");
	var newComment;
	var rIndex;
	var uRequest;
	var cName;
	rIndex=document.getElementById("index").value;
	newComment=document.getElementById("newComment").value;
	cName=document.getElementById("cName").value;
	if ( newComment == "")
	{
		alert("Please enter your comment");
		return false;
	}
	if ( cName == "")
	{
		cName="Someone";
	}
	uRequest='?&rIndex='+rIndex+'&newComment='+newComment+'&cName='+cName;
	x_submit_comment(uRequest, updateComments);
}
function updateComments(new_data)
{
	document.getElementById("recipeComments").innerHTML=new_data;
	document.getElementById("newComment").value="";
	document.getElementById("cName").value="";
}
</script>
<style type="text/css">
img {
border-style:none;
position:absolute;
top:1px;
right:20px;
}
table.assoc {position:relative;left:20}
div.instr {
border-bottom-style: ridge;
margin-right: 10%
}
.lefthide {
position:relative;
left:-100px;
visibility:hidden;
}
.leftshow {
position:relative;
left:-100px;
visibility:visible
}
</style>
</head>
<body>
<div id="RecipeIngredents">
<?php
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

echo '<h3>'.$main["name"];
echo '<a href="pf_recipe.php?index='.$recipeIndex.'&calcamt='.$adjserv.'" target="_blank" title="Printer Friendly Version"><img src="'.$baseUrl.'/graphics/pf_image.png"></a></h3>';
echo '<table>';
//echo "Ingredients go here . . .<br>";
while ($ings=mysql_fetch_assoc($result))
{
	echo '<tr><td align=right>' . toFraction($ings["amount"]) . '</td>';
	echo '<td>' . $ings["measurement"] . '</td>';
	echo '<td>' . $ings["ing_name"] . '</td></tr>';
}
echo '</table><br>';
?>
</div>
<div id="RecipeInstructions" class="instr">
<?php
echo $main["instructions"].'<br>';
if ($main['portions'] >0)
{
	echo '<br>Serves: ' . $adjserv.'<br>';
}
echo '</div><div id="recipeComments">'.get_comments($recipeIndex);
echo '</div><div id="currAssoc">'.get_assoc($recipeIndex);
?>
</div>
<div id="adjustRecipe">
<input type="hidden" id="adjServ" value=<? echo $adjserv ?> >
<input type="button" id="edInst" value="Edit instructions" onclick="editSetUp();"> <input type="button" id="saveInst" value="Submit" onclick="j_saveInstructions();" class="lefthide"><br><br>
<?php
echo '<form action="show_recipe.php" method="get">';
echo '<input type="hidden" name="index" id="index" value="'.$recipeIndex.'"/>';
if ($main['portions'] >0)
{
	echo 'Adjust for <input type="text" size="3" name="calcamt" value='.$defserv.'> servings';
}
else
{
	echo '<input type="radio" name="calcamt" value=".5" /> halve ';
	echo '<input type="radio" name="calcamt" value="2" checked="checked"/> double ';
	echo '<input type="radio" name="calcamt" value="3" /> triple original recipe<br>';
}
echo ' <input type="submit" value="Go">';
echo '</form>';
?>
</div>
<div id="assocRecipes">
Add association: <select id="assoc" onchange="assocRecipeType()"><option value=0></option><option value=1>Needs</option><option value=2>Similar to</option><option value=3>Goes good with</option></select>
</div>
Add a comment:<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name: <input type="text" id="cName" size="20"><br><br>Comment: <input type="text" id="newComment" size="60">
&nbsp;&nbsp;<input type="button" value="Add" onclick="submitComment()"><br>
</body>
</html>
