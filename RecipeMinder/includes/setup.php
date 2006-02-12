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

$admin=$_GET['db_admin'];
$adminpass=$_GET['db_pass'];
$adminvpass=$_GET['db_vpass'];

$rmConfig['dbuser'] = $_GET['rm_user'];
$rmConfig['dbpasswd'] = $_GET['rm_passwd'];
$rmConfig['dbase'] = $_GET['db_name'];
$rmConfig['dbhost'] = $_GET['db_host'];
$rmConfig['root'] = "$baseDir";
$rmConfig['base'] = "$baseUrl";
//$rmConfig[''] = "";

echo '<img src="'.$baseUrl.'/graphics/rm_logo.png"><br>';
echo 'Setup received the following information:<br>';
echo '<table>';
foreach($_GET as $variable => $value)
{
	echo "<tr><td>Variable:</td><td> " . $variable . "</td><td>Value:</td><td> $value</td></tr>";
}
echo '</table>';
die();
if ( $adminpass != $adminvpass )
{
	die('Error!  Password mismatch for administrator . . .');
}

if ( $rmConfig['dbpasswd'] != $_GET['rm_vpasswd'] )
{
	die('Error!  Password mismatch for Recipe Minder user . . .');
}

// now we get to see if we can connect to the database
$link = mysql_connect($rmConfig['dbhost'], $admin, $adminpass)
	or die('Unable to connect as '.$admin.': '.mysql_error());

echo 'Connected to database as '.$admin,'<br>';
$dblist=mysql_list_dbs($link);
while ($row = mysql_fetch_object($dblist))
{
	if ( $row->Database == $rmConfig['dbase'] )
	{
		echo 'Database '.$rmConfig['dbase'].' exists . . .<br>';
		$db_exists='Y';
		break;
	}
}
if ( $db_exists != 'Y' )
{
	echo 'Attempting to create database . . .<br>';
	$sql='create database '.$rmConfig['dbase'];
	if (mysql_query($sql, $link))
	{
		echo 'Database '.$rmConfig['dbase'].' has been created.<br>';
	}
	else
	{
		die('Error '.mysql_errno($link).': '.mysql_error($link));
	}
}

// need to grant access to user here . . .
$sql='GRANT ALL PRIVILEGES ON '.$rmConfig['dbase'].'.* to \''.$rmConfig['dbuser'].'\'@\'';
echo 'Granting user '.$rmConfig['dbuser'].' access . . .<br>';
if ( $rmConfig['dbhost'] == 'localhost' )
{
	$sql.='localhost\'';
}
else
{
	$sql.='%\'';
}
$sql.=' identified by \''.$rmConfig['dbpasswd'].'\' with grant option';
if (mysql_query($sql,$link))
{
	echo 'Access granted . . .<br>';
}
else
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}


mysql_select_db($rmConfig['dbase']);
if (mysql_errno == 0)
{
	echo 'Selected '.$rmConfig['dbase'].' as current database.<br>';
}
else
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `main` ( `rindex` bigint(20) unsigned NOT NULL auto_increment, `name` varchar(80) NOT NULL default \'\',';
$sql.=' `source` varchar(128) default NULL, `instructions` text NOT NULL, `portions` int(10) unsigned NOT NULL default \'0\',';
$sql.=' `type` int(11) unsigned NOT NULL default \'0\', UNIQUE KEY `rindex` (`rindex`)) TYPE=MyISAM AUTO_INCREMENT=1';
$sql.=' COMMENT=\'Main recipe table\'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `ingredients` ( `ing_index` bigint(20) unsigned NOT NULL auto_increment, `ing_name` varchar(80) NOT NULL default \'\',';
$sql.='  `searchable` set(\'Yes\',\'No\') NOT NULL default \'Yes\',  UNIQUE KEY `ing_index` (`ing_index`)) TYPE=MyISAM AUTO_INCREMENT=1' ;
$sql.=' COMMENT=\'All available ingredients for all recipes\'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `links` ( `rindex` bigint(20) unsigned NOT NULL default \'0\', `ing_index` bigint(20) unsigned NOT NULL default \'0\',';
$sql.='  `amount` float default NULL,  `measurement` varchar(80) default NULL) TYPE=MyISAM COMMENT=\'Links main recipe entry to all its';
$sql.=' ingredients and set amount and measurement unit\'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `associated` (  `primary_rindex` bigint(20) unsigned NOT NULL default \'0\',';
$sql.='  `secondary_rindex` bigint(20) unsigned NOT NULL default \'0\',  `type` set(\'1\',\'2\',\'3\') NOT NULL default \'1\',';
$sql.='  KEY `primary_rindex` (`primary_rindex`,`secondary_rindex`)) TYPE=MyISAM';
$sql.=' COMMENT=\'For linking recipes to each other using the following types 1) primary needs secondary to be complete. 2) recipes are similar or 3) Primary goes good with secondary\'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `keywords` (  `rindex` bigint(20) unsigned NOT NULL default \'0\',  `words` varchar(128) NOT NULL default \'\'';
$sql.=') TYPE=MyISAM COMMENT=\'Key words associated with recipe not in name or ingredients (i.e. Cookie, Summer, Chocalate, etc.) \'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `comments` (  `r_index` bigint(20) unsigned NOT NULL default \'0\',  `name` varchar(80) default NULL,';
$sql.='  `comment` mediumtext NOT NULL, `date_added` date NOT NULL default \'0000-00-00\', KEY `r_index` (`r_index`)) TYPE=MyISAM';
$sql.=' COMMENT=\'User added comments about the recipe.\'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}

$sql='CREATE TABLE `recipe_types` (`type_id` int(10) unsigned NOT NULL auto_increment, `type_name` varchar(60) NOT NULL default \'\',';
$sql.=' PRIMARY KEY  (`type_id`), KEY `type_name` (`type_name`)) TYPE=MyISAM AUTO_INCREMENT=1 COMMENT=\'List of available recipe types\'';
if ( ! mysql_query($sql, $link))
{
	die('Error '.mysql_errno($link).': '.mysql_error($link));
}
$sql='INSERT INTO `recipe_types` (`type_name`) VALUES (\'Appetizer\'),(\'Dessert / Cookie\'),(\'Dessert / Pie\'),(\'Dessert / Cake\')';
$sql.=',(\'Dessert / Other\'),(\'Entree / Beef\'),(\'Entree / Chicken\'),(\'Entree / Fish\'),(\'Entree / Pork\')';
$sql.=',(\'Entree / Vegetable\'),(\'Drink / Alcoholic\'),(\'Drink - Non-alcoholic\'),(\'Entree / Other\')';
$sql.=',("Hor\'devrs"),(\'Bread\'),(\'Sweet Bread\')';
mysql_query($sql) or die('Error '.mysql_errno($link).': '.mysql_error($link));

/*
CREATE TABLE `recipe_types` (
  `type_id` int(10) unsigned NOT NULL auto_increment,
  `type_name` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`type_id`),
  KEY `type_name` (`type_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- 
-- Dumping data for table `recipe_types`
-- 

INSERT INTO `recipe_types` VALUES (1, 'Appetizer');
INSERT INTO `recipe_types` VALUES (2, 'Dessert / Cookie');
INSERT INTO `recipe_types` VALUES (3, 'Dessert / Pie');
INSERT INTO `recipe_types` VALUES (4, 'Dessert / Cake');
INSERT INTO `recipe_types` VALUES (5, 'Dessert / Other');
INSERT INTO `recipe_types` VALUES (6, 'Entree / Beef');
INSERT INTO `recipe_types` VALUES (7, 'Entree / Chicken');
INSERT INTO `recipe_types` VALUES (8, 'Entree / Fish');
INSERT INTO `recipe_types` VALUES (9, 'Entree / Pork');
INSERT INTO `recipe_types` VALUES (10, 'Entree / Vegetable');
INSERT INTO `recipe_types` VALUES (11, 'Drink / Alcoholic');
INSERT INTO `recipe_types` VALUES (12, 'Drink - Non-alcoholic');
INSERT INTO `recipe_types` VALUES (13, 'Entree / Other');
INSERT INTO `recipe_types` VALUES (14, 'Hor''devrs');
INSERT INTO `recipe_types` VALUES (15, 'Bread');
INSERT INTO `recipe_types` VALUES (16, 'Sweet Bread');
*/

// Now that the database is set up, we need to create the configuration file . . .
$fname=$baseDir.'/config/RecipeMinder.php';
if (!$cfgfile = fopen($fname,'w'))
{
	echo 'Unable to open configuration file for writing<br>';
	echo 'File:  '.$fname;
	die();
}
$line="<?php\n";
if ( fwrite($cfgfile, $line) == FALSE )
{
	echo 'Unable to write to configuration file';
	die();
}

fwrite($cfgfile, '$rmConfig[\'dbuser\'] = "'.$rmConfig['dbuser']."\";\n");
fwrite($cfgfile, '$rmConfig[\'dbpasswd\'] = "'.$rmConfig['dbpasswd']."\";\n");
fwrite($cfgfile, '$rmConfig[\'dbase\'] = "'.$rmConfig['dbase']."\";\n");
fwrite($cfgfile, '$rmConfig[\'dbhost\'] = "'.$rmConfig['dbhost']."\";\n");
fwrite($cfgfile, '$rmConfig[\'root\'] = "'.$rmConfig['root']."\";\n");
fwrite($cfgfile, '$rmConfig[\'base\'] = "'.$rmConfig['base']."\";\n");
fwrite($cfgfile, '?>'."\n");

echo'<br>Set up complete . . .<br><br>';
echo '<form action="'.$baseUrl.'" method="get"><input type="submit" value="Continue"></form>';
/*
	if (!$handle = fopen($filename, 'a'))
	{
		echo "Cannot open file ($filename)";
		exit;
	}

	// Write $somecontent to our opened file.
	if (fwrite($handle, $somecontent) === FALSE)
	{
		echo "Cannot write to file ($filename)";
		exit;
	}
 
	echo "Success, wrote ($somecontent) to file ($filename)";
 
	fclose($handle);
*/

die();
echo 'Setup received the following information:<br>';
echo '<table>';
foreach($_GET as $variable => $value)
{
	echo "<tr><td>Variable:</td><td> " . $variable . "</td><td>Value:</td><td> $value</td></tr>";
}
echo '</table>';
die();
?>



<?php
$rmConfig['dbuser'] = "rm_user";
$rmConfig['dbpasswd'] = "scidzeg7";
$rmConfig['dbase'] = "recipe";
$rmConfig['dbhost'] = "localhost";
$rmConfig['root'] = "$baseDir";
$rmConfig['base'] = "$baseUrl";
//$rmConfig[''] = "";
?>
