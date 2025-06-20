<?php
//教师端保存小白板图片数据
//print_r($_POST);
$vname=isset($_POST['VIDEONAME'])?$_POST['VIDEONAME']:"";
$vdata=isset($_POST['VIDEODATA'])?$_POST['VIDEODATA']:"";

$GID=isset($_POST['GID'])?intval($_POST['GID']):0;
$room=isset($_COOKIE['R'])?intval($_COOKIE['R']):0;

if($vname!="" && $vdata!="")// && $GID!=0)
{
   $vArr=explode("base64,",$vdata);

   if(count($vArr)==2 )
   {
      preg_match_all("/data:video\/([^^]*?);/",$vArr[0],$vType);
      //print_r($vType);
      if(count($vType)==2)
      {
         file_put_contents("./".$room."/".$GID."/".$vname.".".$vType[1][0],base64_decode($vArr[1]));
         //exit("OK");

         $add="./".$room."/".$GID."/";
         $files=Array();//获取附件文件名
         if(is_dir($add))
         {
            if ($handle_date = opendir($add))
            {
               while (false !== ($file = readdir($handle_date)))
               {
                  if($file!="." && $file!=".." && $file!="bak")
                  {
                     if (is_dir($add))
                     {
                        //if(!in_array($file,$idArr))//过滤掉已经使用的附件。
                        //{
                        $files[]=$file;
                        //}
                      }
                   }
               }
               closedir($handle_date); 
            }
         }
         //print_r($files);
         $fc=count($files);
         echo json_encode($files);
         exit();
      }
      else
      {
         exit('["ERROR:UNKNOWN"]');
      }
   }
}
exit('["ERROR:NOFILE"]');