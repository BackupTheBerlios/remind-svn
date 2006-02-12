<?
	require("Sajax.php");

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

	$link = mysql_connect($rmConfig['dbhost'], $rmConfig['dbuser'], $rmConfig['dbpasswd'])
		or die('Could not connect: ' . mysql_error());
	mysql_select_db($rmConfig['dbase']) or die('Could not select database');
	$query='select ing_name, ing_index from ingredients order by ing_name asc';
	$result=mysql_query($query) or die('Query failed: ' . mysql_error());
	$ing_len=0;
	while ($ings=mysql_fetch_assoc($result))
	{
		$inglist.='<option value="'.$ings['ing_index'].'"> '.$ings['ing_name'].'</option>';
		if ( strlen($ings['ing_name']) > $ing_len )
			$ing_len=strlen($ings['ing_name']);
	}
	$query='select * from recipe_types order by type_name, sub_type';
	$result=mysql_query($query) or die('Query failed: ' . mysql_error());
	$type_len=0;
	while ($types=mysql_fetch_assoc($result))
	{
		$typelist.='<option value="'.$types["type_id"].'"> '.$types["type_name"];
		if ($types["sub_type"] != "")
		{
			$typelist.=' / '.$types["sub_type"];
		}
		$typelist.='</option>';
		if (strlen($types["type_name"]) > $type_len)
			$type_len=strlen($types["type_name"]);
	}

	function add_ingredient($msg) {
		global $ing_len;
		parse_str($msg);
//		$f=fopen('/tmp/input.msg',"w");
//		fwrite($f, $msg);
//		fclose($f);
//		$fname='/tmp/'.metaphone($recipe).'.'.$rindex;
//		$fname='/tmp/recipe.html';
//		$f = fopen($fname, "a");
//		$lines=file($fname);
//		foreach ($lines as $thisline => $line) ;
/*		if ($line == "" )
			$thisline=0;
		else
			$thisline++; */
/*		fwrite($f, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		fwrite($f, '<input type="text" disabled="disabled" size="5" name="amt'.$thisline.'" value="'.$amt.'">&#09;');
		fwrite($f, '<input type="text" disabled="disabled" size="10" name="measure'.$thisline.'" value="'.$measure.'">&#09;');
		fwrite($f, '<input type="text" disabled="disabled" size="'.$ing_len.'" name="iname'.$thisline.'" value="'.$ing_name.'">&#09;');
		fwrite($f, '<input type="hidden" name="ing'.$thisline.'" value="'.$ing.'"><br>'."\n"); */
		$retval='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$retval.='<input type="text" readonly="readonly" size="5" name="amt'.$thisline.'" value="'.$amt.'">&#09;';
		$retval.='<input type="text" readonly="readonly" size="17" name="measure'.$thisline.'" value="'.$measure.'">&#09;';
		$retval.='<input type="text" readonly="readonly" size="'.$ing_len.'" name="iname'.$thisline.'" value="'.$ing_name.'">&#09;';
		$retval.='<input type="hidden" name="ing'.$thisline.'" value="'.$ing.'"><br>'."\n";
//		fclose($f);
/*		echo '<input type="text" name="amt">&#09;';
		echo '<input type="text" name="measure">&#09;';
		echo '<select name="ing">'.$inglist.'</select><input type="button" onclick="add(); return false;">'; */
		return $retval;
	}
	
	function refresh() {
//		parse_str($msg);
//		$fname='/tmp/'.metaphone($recipe).'.'.$rindex;
		$fname='/tmp/recipe.html';
		$lines = file($fname);
		// return the last 25 lines
		return join("\n", $lines);
	}
	
	$sajax_request_type = "GET";
	sajax_init();
	sajax_export("add_ingredient", "refresh");
	sajax_handle_client_request();
	
?>
<html>
<head>
	<script>
	<?
	sajax_show_javascript();
	?>
	
	var check_n = 0;
	
	function refresh_cb(new_data)
	{
		existing = document.getElementById("inglist").innerHTML;
		document.getElementById("inglist").innerHTML = existing + new_data;
//		document.getElementById("therest").innerHTML = "Checked #" + check_n++;
	}
	
	function refresh()
	{
//		document.getElementById("inglist").innerHTML = "Checking..";
		x_refresh(refresh_cb);
	}
	
	function add_cb(new_data)
	{
		existing = document.getElementById("inglist").innerHTML;
		document.getElementById("inglist").innerHTML = existing + new_data;
		// we don't care..
	}

	function add()
	{
		var amt;
		var measure;
		var ing;
		var recipe;
		var ingindex;
		var thisline;
		recipe = document.getElementById("recipe").value;
		amt = document.getElementById("amt").value;
		measure = document.getElementById("measure").value;
		ingsel = document.getElementById("ing").selectedIndex;
		ingindex = document.getElementById("ing").options[ingsel].value;
		ing_name = document.getElementById("ing").options[ingsel].text;
		thisline = document.getElementById("ing_no").value;
		if ((amt == "") &&
			 (measure == ""))
			return;
		x_add_ingredient('?recipe=' + recipe + '&amt=' + amt + '&measure=' + measure + '&ing=' + ingindex + '&ing_name=' + ing_name + '&thisline=' + thisline , add_cb);
		thisline++;
		document.getElementById("measure").value="";
		document.getElementById("ing").options[ingsel] = null;
		document.getElementById("amt").value="";
		document.getElementById("amt").focus();
		document.getElementById("ing_no").value=thisline;
//		setTimeout("refresh()", 250);
//		document.getElementById("line").value = "";
	}

	function do_setup()
	{
		document.getElementById("recipe").focus();
	}
	
	function validate_and_submit()
	{
//		alert("Validating . . .");
		rname=document.getElementById("recipe").value;
		if (rname == "" )
		{
			alert("No recipe name given.");
			return false;
		}
		rinstruct=document.getElementById("instructions").value;
		if ((rinstruct == "" ) ||
			 (rinstruct == "Enter instructions here."))
		{
			alert("No recipe instructions given.");
			return false;
		}
		document.getElementById("f").submit();
	}

	function clear_form()
	{
		location="blank.html";
	}

	</script>
	
</head>
<body  onload="do_setup()">
	<form name="f" id="f" action="submit_recipe.php">
		<div id="recipe_name">
		<table width="100%" cellpadding="5"><tr><td align="right">
			Title:</td><td>
			<input type="text" name="recipe" id="recipe" size="80"><input type="hidden" value=0 name="ing_no" id="ing_no"></td></tr>
			<tr><td align="right">Type:</td><td>
			<select name="recipe_type" id="recipe_type"><? echo $typelist; ?></select>&nbsp;&nbsp;
			Serves:&nbsp;&nbsp;
			<input type="text" size="2" maxlength="2" name="portions">&nbsp;&nbsp;
			Source:&nbsp;&nbsp;
			<input type="text" name="source" size="40"></td></tr></table>
			Ingredients:
		</div>
		<div id="inglist">
		</div>
		<div id="ing_input">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="amt" id="amt" size="5" maxlength="5">&#09;
			<input type="text" name="measure" id="measure" size="17" maxlength="15">&#09;
	<?php
		echo '<select name="ing" id="ing">'.$inglist.'</select>';
	?>
			<input type="button" onclick="add(); return false" value="Add">
		</div>
			<br><textarea id="instructions" cols="85" rows="15" name="instructions" onfocus="this.select()">Enter instructions here.</textarea>
			<br>
			Key Words:<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="text" name="keywords" size="80"><br>
			<br>
			<input type="button" name="button" id="Button" value="Cancel" onclick="clear_form(); return false">
			&#09;&#09;&#09;&#09;&#09;
			<input type="button" value="Save" onclick="return validate_and_submit();">
	</form>
</body>
</html>
