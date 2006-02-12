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

$recipeIndex=$_GET['rindex'];


