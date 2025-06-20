<?php
error_reporting(0);
header("Pragma:no-cache");
header("Cache-Control:no-cache,must-revalidate");
include("../include/config.inc.php");

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit(iconv("GBK","UTF-8","请登录"));
$strUN=isset($_POST['UN'])?$_POST['UN']:"";
$strCN=isset($_POST['CN'])?$_POST['CN']:"";
$language=isset($_POST['L'])?$_POST['L']:"";
$taskID=isset($_POST['TKID'])?intval($_POST['TKID']):"";
$strFN=isset($_POST['FN'])?$_POST['FN']:"";

if($strUN !=$username) exit(iconv("GBK","UTF-8","请登录"));

include("../../include/config.inc.php");

$admin=0;

if(isset($scratch_class[$username])) $admin=1;

if($strCN && $strUN && $taskID && $language)
{
   if($strFN=="" && $admin)//输出列表
   {
      $add="..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR;
      $list=Array();
      if ($handle = opendir($add))
      {
         while (false !== ($file = readdir($handle)))
         {
            if ($file!="." && $file!=".." && !is_dir($add.$file))
            { 
               $f=pathInfo($add.$file);
               if ( $f["extension"]==$language  && strstr($file,"_".$taskID.".") ) //  && ($admin==1 || strstr($file,$username) || strstr($file,"ALL_") || strstr($file,"_EDITABLE_") ))
                  $list[]=$file;
            }
        }
         closedir($handle); 
      }
      rsort($list);
      echo  json_encode($list);

   }
   else{ //输出文件
      if($strFN)//教师获取文件
      {
         if($language=="php" || $language=="java")
            echo file_get_contents("..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR.$strFN);
         else
            echo iconv("GBK","UTF-8",file_get_contents("..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR.$strFN));
      }
      else//学生获取文件
      {
         if($language=="php"|| $language=="java")
            echo file_get_contents("..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR.$strFN);
         else
            echo iconv("GBK","UTF-8",file_get_contents("..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR.$strUN."_".$taskID.".".$language));
      }
   }
}
?>