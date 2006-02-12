<?php
global $baseUrl;
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$baseDir = dirname(__FILE__);

$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
if (@$pathInfo) {
  $baseUrl .= dirname($pathInfo);
} else {
  $baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
}

header ("Expires: Tue, 17 Dec 1957 07:23:00 EST");
header ("Last-Modified: ".date("D, d M Y H:i:s") . " EST");
header ("Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0");
header ("Pragma: no-cache");

?>
<head>
<style>
.shiftRight
{
	position:relative;
	left:225;
}
</style>
<script>
function doCancel()
{
		  alert("Gonna cancel now . . .");
		  location.replace("includes/blank.html")
		  return false;
}
</script>
</head>
<?php
if ( !is_file("$baseDir/config/RecipeMinder.php"))
{
	// Need to set up for this system
	echo '<body>';
	echo '<img src="graphics/rm_logo.png"><br>';
	echo 'Recipe Minder is not configured.  Please enter the following and click Save to set it up.<br><br>';
	echo '<form action="'.$baseUrl.'/includes/setup.php" method="get">';
	echo '<table>';
	echo '<tr><td align="right">Database Name:</td><td><input type="text" name="db_name" value="recipe"/></td></tr>';
	echo '<tr><td align="right">Database Host:</td><td><input type="text" name="db_host" value="localhost"/></td></tr>';
	echo '<tr><td align="right">Administrator Name:</td><td><input type="text" name="db_admin">*</td></tr>';
	echo '<tr><td align="right">Admin Password:</td><td><input type="password" name="db_pass">*</td></tr>';
	echo '<tr><td align="right">Verify Admin Password:</td><td><input type="password" name="db_vpass">*</td></tr>';

	echo '<tr><td align="right">User Name:</td><td><input type="text" name="rm_user"/></td></tr>';
	echo '<tr><td align="right">Password:</td><td><input type="password" name="rm_passwd"/></td></tr>';
	echo '<tr><td align="right">Verify Password:</td><td><input type="password" name="rm_vpasswd"/></td></tr>';
	echo '</table>* These entries will only be used for setup.  They will not be stored in the configuration for this application.<br><br>';
	echo '<input type="submit" action="includes/setup.php" value="Save" class="shiftRight"/></form>';
	echo ' </body> </html>';
}
else
{
	$rmConfig = array();

	require_once "$baseUrl/config/RecipeMinder.php";
	require_once "$baseUrl/includes/functions.php";

	//$suppressHeaders = getParam( $_GET, 'suppressHeaders', false );

	echo '<frameset rows="130,*">';
	echo '<frame name="rm_top" src="'.$baseUrl.'/includes/top_frame.php" frameborder="0" noresize scrolling="no" >';
	echo '<frame name="rm_recipe" frameborder="0">';
	echo '</frameset>';
}
?>
