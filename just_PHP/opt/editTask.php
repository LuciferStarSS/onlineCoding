<?php
$act=isset($_GET['act'])?$_GET['act']:"";

$taskID=isset($_POST['TKID'])?$_POST['TKID']:"";
$taskDescription=isset($_POST['DATA'])?$_POST['DATA']:"";
$testID=isset($_POST['TTID'])?$_POST['TTID']:"";
$inputData=isset($_POST['IN'])?$_POST['IN']:"";
$outputData=isset($_POST['OUT'])?$_POST['OUT']:"";
$evaluation=isset($_POST['E'])?intval($_POST['E']):"";


switch($act)
{
   case "new":
      if(!file_exists("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID))
      {
         mkdir("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID,0777,true);
         echo "{\"TKID\":\"".$taskID."\"}";
      }
      else echo "{\"TKID\":\"\"}";
   break;

   case "save":
      if(!file_exists("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."demo".DIRECTORY_SEPARATOR))
         mkdir("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."demo".DIRECTORY_SEPARATOR,0777,true);
      file_put_contents("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."task.txt",$taskDescription);
      file_put_contents("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."demo".DIRECTORY_SEPARATOR."input.txt",$inputData);
      file_put_contents("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."demo".DIRECTORY_SEPARATOR."output.txt",$outputData);
      echo "{\"SAVE\":\"DONE\"}";
   break;

   case "del":
      if(deleteDirectory("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID))
      {
         echo "{\"DELETE\":\"DONE\"}";
      }
      else echo "{\"DELETE\":\"FAILED\"}";
   break;

   case "all":
      $add="..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR;
      $list=Array();
      if ($handle = opendir($add))
      {
         while (false !== ($file = readdir($handle)))
         {
            if ($file!="." && $file!=".." && !is_dir($add.$file))
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
      if( $taskID)
      {
         $add="..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR;
         echo @file_get_contents($add."task.txt");			//任务描述
         echo "<+-NOJSON-+>";
         echo @file_get_contents($add."demo".DIRECTORY_SEPARATOR."input.txt");		//任务输入样例
         echo "<+-NOJSON-+>";
         echo @file_get_contents($add."demo".DIRECTORY_SEPARATOR."output.txt");		//任务输出样例
         if($evaluation==1)						//内部评测数据列表，此数据仅供出题使用。
         {
            $tests="";
            $add="..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR;

            if ($handle = @opendir($add))
            {
               while (false !== ($file = readdir($handle)))
               {
                  if ($file!="." && $file!=".." && is_dir($add.$file))
                  {
                     $tests.=$file."|";
                  }
               }
            }
            echo "<+-NOJSON-+>";
            echo $tests;
         }
      }
   break;
}

function deleteDirectory($dirPath) {
    if (!file_exists($dirPath)) {
        return true;
    }
    if (!is_dir($dirPath)) {
        return unlink($dirPath);
    }
    foreach (scandir($dirPath) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        $itemPath = $dirPath . DIRECTORY_SEPARATOR . $item;
        if (is_dir($itemPath)) {
            deleteDirectory($itemPath);
        } else {
            unlink($itemPath);
        }
    }
    return rmdir($dirPath);
}
 
?>