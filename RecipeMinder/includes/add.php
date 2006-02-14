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
		echo '</font>Ingredient: <input type="text" name="ingredient" size="40"><br>';
		echo '<input type="checkbox" name="add_search" value="TRUE"> Add to searchlist<br><br>';
		echo '<input type="submit" name="button" value="Add Ingredient">';
		echo '<hr size="5" width="100%"/>';
		echo '<font color="#00008C">';
		$query="select distinct upper(substring(ing_name,1,1)) from ingredients";
		$letters=mysql_query($query);
		$numletters=mysql_num_rows($letters);
		$query="select * from ingredients order by ing_name asc";
		$ingList=mysql_query($query);
		$numrows=mysql_num_rows($ingList)+$numletters;
		$longCols=$numrows % 4;
		$rowspercol=($numrows - $longCols)/4;
		if ($longCols)
			$rowspercol++;
		echo '<table width="100%"><tr><td align="center" valign="top" width="25%"><font color=\"#00008C\">';
		$lastLetter="";
		$rowcount=0;
		$letterCount=1;
		while ($nextIng=mysql_fetch_assoc($ingList))
		{
			if ($lastLetter != strtoupper(substr($nextIng['ing_name'],0,1)))
			{
				echo "<h5 style=\"color:#FFFFFF; background:#8C8C00\">";
				$lastLetter=strtoupper(substr($nextIng['ing_name'],0,1));
				echo $lastLetter.'</h5>';
				$rowcount++;
				if (($rowcount % $rowspercol) == 0)
				{
					echo '</td><td align="center" valign="top" width="25%"><font color=\"#00008C\">';
					if ($longCols > 0)
					{
						$longCols--;
						if ($longCols == 0)
						{
							$rowspercol--;
							$rowcount--;
						}
					}
				}
			}
			if ( strtoupper($nextIng['searchable']) == 'YES' )
				echo '<a href="search.php?stype=Ingredients&smatch='.$nextIng['ing_name'].'">';
			echo $nextIng['ing_name'];
			if ( strtoupper($nextIng['searchable']) == 'YES' )
				echo ' *</a>';
			echo '<br>';
			$rowcount++;
			if (($rowcount % $rowspercol) == 0)
			{
				echo '</td><td align="center" valign="top"><font color=\"#00008C\">';
				if ($longCols > 0)
				{
					$longCols--;
					if ($longCols == 0)
					{
						$rowspercol--;
						$rowcount--;
					}
				}
			}
		}
		echo '</td></tr></table>';
		break;
}
?>



