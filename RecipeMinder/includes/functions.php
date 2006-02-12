<?php

function getParam( &$arr, $name, $def=null )
{
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}

function toFraction( $value )
{
	$whole=(int)($value);
	$dec=(int)(($value-(int)($value)) * 1000);
	$retstr=($whole>0)?(string)$whole." ":"";
	if ( $dec > 0 )
	{
		for ($max=$dec; $max>1; $max--)
		{
			if ( (1000 / $max == (int)(1000 / $max)) &&
				  ($dec / $max == (int)($dec / $max)))
				  break;
		}
		$fract=(string)($dec / $max).'/'.(string)(1000 / $max);
		if (strlen($fract) > 3) //probably a repeating decimal
		{
			for ($max=$dec; $max>1; $max--)
			{
				if ( (999 / $max == (int)(999 / $max)) &&
					  ($dec / $max == (int)($dec / $max)))
					  break;
			}
			$fract=(string)($dec / $max).'/'.(string)(999 / $max);
		}
		$retstr.=$fract;
	}
	return  $retstr;
}
function toDecimal( $value )
{
	$numbers= explode(" ", $value);
	if ( $numbers[1] == "" )
	{
		$retval=$numbers[0];
	}
	else
	{
		$retval="$numbers[0] + $numbers[1]";
	}
	return $retval;
}
function genSoundex($word)
{
	$word=strtoupper(trim($word));
	$aStr=$word{0};
	switch($aStr)
	{
		case 'C':
			switch($word{1})
			{
				case 'I':
				case 'E':
				case 'Y':
					$aStr='S';
					break;
				case 'A':
				case 'O':
				case 'U':
				case 'R':
				case 'L':
					$aStr='K';
					break;
			}
			break;
		case 'G':
			if ($word{1} == 'Y')
			{
				$aStr='J';
			}
			break;
		case 'K':
			if ($word{1} == 'N')
			{
				$aStr='N';
				$word=substr($word,1);
			}
			break;
		case 'P':
			if ($word{1} == 'H')
			{
				$aStr='F';
				$word=substr($word,1);
			}
			break;
		case 'W':
			if ($word{1} == 'R')
			{
				$aStr='R';
				$word=substr($word,1);
			}
			break;
	}
	for ($ind=1;$ind<strlen($word);$ind++)
	{
		if (strpos(',./<>?;\':"[]{}`~@#$%^&*()-_=+\\|', $word{$ind}) !== false)
		{
			continue;
		}
		switch($word{$ind})
		{
			case 'A':
			case 'E':
			case 'I':
			case 'O':
			case 'U':
			case 'H':
			case 'W':
			case 'Y':
				$aStr.=0;
				break;
			case 'B':
			case 'F':
			case 'P':
			case 'V':
				$aStr.=1;
				break;
			case 'C':
			case 'G':
			case 'J':
			case 'K':
			case 'Q':
			case 'S':
			case 'X':
			case 'Z':
				$aStr.=2;
				break;
			case 'D':
			case 'T':
				$aStr.=3;
				break;
			case 'L':
				$aStr.=4;
				break;
			case 'M':
			case 'N':
				$aStr.=5;
				break;
			case 'R':
				$aStr.=6;
				break;
			default:
				$aStr.=9;
				break;
		}
	}
	$bStr=$aStr{0};
	$aInd=$bInd=1;
	$bStr.=$aStr{$aInd++};
	while($aInd < strlen($aStr))
	{
		if($bStr{$bInd} != $aStr{$aInd})
		{
			$bStr.=$aStr{$aInd};
			$bInd++;
		}
		$aInd++;
	}
	$cStr=$bStr{0};
	for($bInd=1;$bInd<strlen($bStr);$bInd++)
	{
		if($bStr{$bInd} != '0')
		{
			$cStr.=$bStr{$bInd};
		}
	}
	$cStr.='0000';
	$retStr=substr($cStr,0,4);
	return $retStr;
}

function get_query($sfield, $smatch)
{
	switch ($sfield)
	{
		case "Ingredients":
			$query="select distinct name, m.rindex, t.type_id, type_name, sub_type ";
			$query.="from main m, links, ingredients i, recipe_types t ";
			$query.="where (i.searchable = 'Yes' or '".$smatch."' = '') ";
			$query.="and LOWER(ing_name) like '%".strtolower($smatch)."%' ";
			$query.="and m.rindex = links.rindex ";
			$query.="and links.ing_index = i.ing_index ";
			$query.="and m.type = t.type_id ";
			break;
		case "Name":
			$query="select distinct name, rindex, t.type_id, type_name, sub_type ";
			$query.="from main m, recipe_types t ";
			$query.="where LOWER(name) like '%".strtolower($smatch)."%' ";
			$query.="and m.type = t.type_id ";
			break;
		case "Type":
			$query="select distinct name, rindex, t.type_id, type_name, sub_type ";
			$query.="from main m, recipe_types t ";
			$query.="where ((LOWER(type_name) like '%".strtolower($smatch)."%') or (LOWER(`sub_type`) like '%".strtolower($smatch)."%')) ";
			$query.="and m.type = t.type_id ";
			break;
		case "Comments":
			$query="select distinct m.name, m.rindex, t.type_id, type_name, sub_type ";
			$query.="from main m, comments c, recipe_types t ";
			$query.="where LOWER(`comment`) like '%".strtolower($smatch)."%' ";
			$query.="and m.rindex = c.r_index ";
			$query.="and m.type = t.type_id ";
			break;
		case "Key Words":
			$query= "select distinct m.name, m.rindex, t.type_id, type_name, sub_type ";
			$query.="from main m, keywords k, recipe_types t ";
			$query.="where LOWER(words) like '%".strtolower($smatch)."%' ";
			$query.="and m.rindex = k.rindex ";
			$query.="and m.type = t.type_id ";
			break;
	}
	return $query;
}

?>
