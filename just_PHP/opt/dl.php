<?php
include "../include/config.inc.php";
$type=isset($_GET['t'])?$_GET['t']:"c";

$strCN=isset($_GET['CN'])?$_GET['CN']:"";		//班级
$taskID=isset($_GET["TKID"])?$_GET["TKID"]:"";		//项目名称

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit("请先登录。");	//用户名

   $path="..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR;

   $filename=$path.$username."_".$taskID.".".$type.".exe";
   if(file_exists($filename))
   {



         //$fn=iconv("UTF-8","GBK",$filename);
         header("Content-type:application/$type");
         header("Location: ".$filename);
         exit;


//header("Content-Type: application/octet-stream");
//header("Content-Disposition: attachment");
         //FILE *$fi;
         echo file_get_contents($filename);
         //$fi=fopen($filename,"r");
         //while(!feof($fi))
         //{ 
         //   echo fgetc($fi);
         //}
         //fclose($fp);

//      header("Location: $filename");
   }

