<?php
//教师端删除小白板单个历史存档数据
$ID=isset($_POST['ID'])?$_POST['ID']:"";
$GID=isset($_POST['GID'])?intval($_POST['GID']):0;
$room=isset($_COOKIE['R'])?intval($_COOKIE['R']):0;
if(file_exists("./".$room."/".$GID."/bd/".$ID))
{
   if(unlink("./".$room."/".$GID."/bd/".$ID))
   {
      exit("[\"OK\"]");
   }
   else
      exit("[ERROR:FAILED]");
}
else
{
   exit("[ERROR:NOSUCHFILE]");
}
?>