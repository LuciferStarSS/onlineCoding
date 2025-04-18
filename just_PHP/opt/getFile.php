<?php
//此文件暂时废弃
error_reporting(0);
header("Pragma:no-cache");
header("Cache-Control:no-cache,must-revalidate");
include("../../include/config.inc.php");
include("../include/config.inc.php");


$classid=isset($_POST['C'])?intval($_POST['C']):"";
$strCN=isset($_POST['CN'])?validateFilename($_POST['CN']):"";
$folder=isset($_POST['DD'])?validateFilename($_POST['DD']):"";
$filename=isset($_POST['FN'])?validateFilename($_POST['FN']):"";

//echo "..\\".$student_works_dir.$strCN."\\".$folder."\\".$filename;

if($strCN && $folder && $filename)
{
   //echo "..\\".$student_works_dir.$strCN."\\".$folder."\\".$filename;
   echo iconv("GBK","UTF-8",file_get_contents("..\\".$student_works_dir.$strCN."\\".$folder."\\".$filename));
}