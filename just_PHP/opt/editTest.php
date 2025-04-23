<?php
$act=isset($_GET['act'])?$_GET['act']:"";

$taskID=isset($_POST['TKID'])?$_POST['TKID']:exit();
$testID=isset($_POST['TTID'])?$_POST['TTID']:"";
$inputData=isset($_POST['IN'])?$_POST['IN']:"";
$outputData=isset($_POST['OUT'])?$_POST['OUT']:"";


switch($act)
{
   case "get":
      if( $taskID && $testID)
      {
         $add="..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID;
         if(file_exists($add))
         {
            echo @file_get_contents($add.DIRECTORY_SEPARATOR."input.txt");
            echo "<+-NOJSON-+>";
            echo @file_get_contents($add.DIRECTORY_SEPARATOR."output.txt");
         }
      }
   break;

   case "new":
      $testID=md5($taskID.time().rand(1,65535));
      if(!file_exists("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID))
         mkdir("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID,0777,true);
      echo "{\"TTID\":\"".$testID."\"}";
   break;

   case "save":
      if(!file_exists("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID))
         mkdir("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID,0777,true);
      file_put_contents("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID.DIRECTORY_SEPARATOR."input.txt",$inputData);
      file_put_contents("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID.DIRECTORY_SEPARATOR."output.txt",$outputData);
      echo "{\"SAVE\":\"DONE\"}";
      
   break;

   case "del":
      if(deleteDirectory("..".DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$taskID.DIRECTORY_SEPARATOR."checking".DIRECTORY_SEPARATOR.$testID))
      {
         echo "{\"DELETE\":\"DONE\"}";
      }
      else echo "{\"DELETE\":\"FAILED\"}";

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