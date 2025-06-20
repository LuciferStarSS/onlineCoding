<?php
//教师端保存小白板图片数据
//print_r($_POST);
$imgname=isset($_POST['IMGNAME'])?$_POST['IMGNAME']:"";
$imgdata=isset($_POST['IMGDATA'])?$_POST['IMGDATA']:"";
$GID=isset($_POST['GID'])?intval($_POST['GID']):0;
$room=isset($_COOKIE['R'])?intval($_COOKIE['R']):0;
if($imgname!="" && $imgdata!="")// && $GID!=0)
{
   $imgArr=explode("base64,",$imgdata);

   if(count($imgArr)==2 )
   {
      preg_match_all("/data:image\/([^^]*?);/",$imgArr[0],$imgType);
      //print_r($imgType);
      if(count($imgType)==2)
      {
         file_put_contents("./".$room."/".$GID."/".$imgname.".".$imgType[1][0],base64_decode($imgArr[1]));
         //exit("OK");
         exit('["'.$imgname.'.'.$imgType[1][0].'"]');


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