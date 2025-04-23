<?php
error_reporting(0);
header("Pragma:no-cache");
header("Cache-Control:no-cache,must-revalidate");
include("../include/config.inc.php");

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit(iconv("GBK","UTF-8","ว๋ตวยผ"));
$strUN=isset($_POST['UN'])?$_POST['UN']:"";
$strCN=isset($_POST['CN'])?$_POST['CN']:"";
$language=isset($_POST['L'])?$_POST['L']:"";
$taskID=isset($_POST['TKID'])?intval($_POST['TKID']):"";

if($strUN !=$username) exit(iconv("GBK","UTF-8","ว๋ตวยผ"));


if($strCN && $strUN && $taskID && $language)
{
   echo iconv("GBK","UTF-8",file_get_contents("..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR.$strUN."_".$taskID.".".$language));
}