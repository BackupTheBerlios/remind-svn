<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

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
echo '</table>';
//die();
*/
$link = mysql_connect($rmConfig['dbhost'], $rmConfig['dbuser'], $rmConfig['dbpasswd'])
		or die('Could not connect: ' . mysql_error());
mysql_select_db($rmConfig['dbase']) or die('Could not select database');
echo '<form name="Addition" action="add.php" method="get">';
// first time through, ask for name
switch ( $_GET['button'])
{
	case "Recipe":
		require($baseUrl."/includes/add_recipe.php");
/*		echo 'Name: <input type="text" name="rname" size="80" value="'.$_GET['rname'].'"><br><br>';
		echo 'Type:  <select name="rtype" size="1" id="rtype" class="text">
				<option'.(($_GET['rtype']=='Appetizer')?'selected="selected"':'').'>Appetizer</option>
				<option'.(($_GET['rtype']=='Dessert')?'selected="selected"':'').'>Dessert</option>
				<option'.(($_GET['rtype']=='Drink')?'selected="selected"':'').'>Drink</option>
				<option'.(($_GET['rtype']=='Entree')?'selected="selected"':'').'>Entree</option>
				<option'.(($_GET['rtype']=='Snack')?'selected="selected"':'').'>Snack</option>
				<option'.(($_GET['rtype']=='Other')?'selected="selected"':'').'>Other</option>
				</select>&#09;';
		echo 'Portions: <input type="text" name="rportions" size="2" value="'.(int)$_GET['rportions'].'">&#09;';
		echo 'Number of Ingredients:  <input type="text" size="2" name="ing_count" value="'.$_GET['ing_count'].'"><br><br>';
		echo '<input type="submit" name="button" value="Next">';
		break;
	case "Next":
		// insert into database
		$ins='insert into main (name, instructions, portions, type) values ("';
		$ins.=$_GET['rname'].'", "x", ';
		$ins.=$_GET['rportions'].', "';
		$ins.=$_GET['rtype'].'")';
//		echo "$ins<br>";
		if ( mysql_query($ins) == FALSE )
		{
			echo "Error: (".mysql_errno($link) .") - ".mysql_error($link); 
		}
		else
		{
			$rindex=mysql_insert_id();
			$inglist="";
			$query='select ing_name, ing_index from ingredients order by ing_name asc';
			$result=mysql_query($query) or die('Query failed: ' . mysql_error());
			while ($ings=mysql_fetch_assoc($result))
			{
				$inglist.='<option value="'.$ings['ing_index'].'"> '.$ings['ing_name'].'</option>';
			}
			echo '<input type="hidden" name="indexval" value="'.$rindex.'">';
			echo '<h3>'.$_GET['rname'].'</h3>'.$_GET['rtype'].' for '.$_GET['rportions'].'<br>';
			echo '<table width="60%"><tr><th width="15%">Amount</td><th width="20%">Measure</td><th>Ingredient</td></tr>'."\n";
			for ($rnum=0; $rnum<$_GET['ing_count']+0;$rnum++)
			{
					  echo '<tr><td><input type="text" name="amt'.$rnum.'"></td>';
				echo '<td><input type="text" name="measure'.$rnum.'"></td>';
				echo '<td><select name="ing'.$rnum.'">'.$inglist.'</option></td></tr>'."\n";
			}
			echo '</table>'."\n";
			echo '<textarea id="instructions" cols="80" rows="15" class="textarea" name="instructions"></textarea><br>'."\n";
			echo 'Key Words:&nbsp;&nbsp;<input type="text" name="keywords" size="80"><br>';
			echo '<input type="submit" name="button" value="Cancel">&#09;&#09;&#09;&#09;&#09;<input type="submit" name="button" value="Save">';
			echo '</form>';
		}
		break;
	case "Cancel":
		$del='delete from main where rindex = '.$_GET['indexval'];
		mysql_query($del);
		echo "Recipe creation canceled . . .";
		break;
	case "Save":
		$recipe_index=$_GET['indexval'];
		for($i=0;;$i++)
		{
			if ( $_GET["amt{$i}"] == "" )
			{
				break;
			}
//			echo $i.')  amt: '.$_GET["amt$i"].' measure:  '.$_GET["measure$i"].' ing_index:  '.$_GET["ing$i"].'&nbsp;&nbsp;';
			$insamt=toDecimal($_GET["amt$i"]);
			$query='insert into links values (';
			$query.=$recipe_index;
			$query.=', '.$_GET["ing$i"].', '.$insamt.', "'.$_GET["measure$i"].'")';
//			echo $query.'<br>';
			mysql_query($query);
		}	
		$query='update main set instructions="'.$_GET['instructions'].'" where rindex='.$recipe_index;
		echo $query.'<br>';
		mysql_query($query);
		if (strlen($_GET['keywords']))
		{
			$query='insert into keywords values('.$recipe_index.', "'.$_GET['keywords'].'")';
			mysql_query($query);
		} */
		break;
	case "Add Ingredient":
		$newIngredient=stripcslashes($_GET['ingredient']);
		if (strlen($newIngredient))
		{
			$exists=0;
			$mySound=genSoundex($newIngredient);
			$query='select * from ingredients';
			$result=mysql_query($query);
			if ( mysql_num_rows($result) > 0 )
			{
				while($nextIng=mysql_fetch_assoc($result))
				{
					if($mySound == genSoundex($nextIng['ing_name']))
					{
						echo $newIngredient.' is already entered as '.$nextIng['ing_name'].'<br>';
						$exists=1;
						break;
					}
				}
			}
			if ($exists == 0)
			{
				$query='insert into ingredients (ing_name, searchable) values("'.$newIngredient.'", "';
				if ( $_GET['add_search'] == TRUE )
					$query.='Yes';
				else
					$query.='No';
				$query.='")';
				mysql_query($query);
			}
		}
	case "Ingredient":
		echo "<font color=\"#00008C\">";
		$ingNum=1;
		$query="select ing_name from ingredients order by ing_name asc";
		$ingList=mysql_query($query);
		while ($nextIng=mysql_fetch_assoc($ingList))
		{
			if ($ingNum % 2)
				echo "<font color=\"#8C223D\">";
			echo $nextIng['ing_name']." ";
			if ($ingNum % 2)
				echo "</font>";
			$ingNum++;
		}
		echo '<hr size="5" width="100%"/>';
		echo '</font>Ingredient: <input type="text" name="ingredient" size="40"><br>';
		echo '<input type="checkbox" name="add_search" value="TRUE"> Add to searchlist<br><br>';
		echo '<input type="submit" name="button" value="Add Ingredient">';
		break;
}
?>



