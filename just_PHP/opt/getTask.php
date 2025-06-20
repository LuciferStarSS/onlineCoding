<?php
error_reporting(0);
header("Pragma:no-cache");
header("Cache-Control:no-cache,must-revalidate");

$taskID=isset($_POST['TKID'])?intval($_POST['TKID']):"";

$evaluation=isset($_POST['E'])?intval($_POST['E']):"";

$act=isset($_GET['act'])?$_GET['act']:"";


switch($act)
{
   case "all":

      $add="..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR;
      if ($handle = opendir($add))
      {
         while (false !== ($file = readdir($handle)))
         {
            if ($file!="." && $file!=".." && is_dir($add.$file))
            { 
               $list[]=$file;
            }
        }
         closedir($handle); 
      }
      sort($list);
      echo  json_encode($list);

   break;

   case "get":

      $list=Array();

      if( $taskID)
      {
         $add="..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR;
         if(file_exists($add))
         {
         echo file_get_contents($add."task.txt");			//任务描述
         echo "<+-NOJSON-+>";
         echo file_get_contents($add."demo".DIRECTORY_SEPARATOR."input.txt");		//任务输入样例
         echo "<+-NOJSON-+>";
         echo file_get_contents($add."demo".DIRECTORY_SEPARATOR."output.txt");		//任务输出样例
         }
         else echo iconv("GBK","UTF-8","无此试题。");
      }
   break;
}
?>