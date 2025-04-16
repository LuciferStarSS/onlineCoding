<?php
flush();
$type=isset($_GET['t'])?$_GET['t']:"c";

$strCODE=isset($_POST['CODE'])?$_POST['CODE']:"";	//代码
$strINPUT=isset($_POST['DATA'])?$_POST['DATA']:"";	//输入
$strARGS=isset($_POST['ARGS'])?$_POST['ARGS']:"";	//参数

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit("请先登录。");	//用户名
$classid=isset($_COOKIE["CLASSID"])?$_COOKIE["CLASSID"]:exit("请先登录。");	//班级
$gradeid=isset($_COOKIE["GRADEID"])?$_COOKIE["GRADEID"]:-1;			//年级
$projname=isset($_COOKIE["PRJNAME"])?$_COOKIE["PRJNAME"]:"p1";		//项目名称
$datedir=isset($_COOKIE["DD"])?$_COOKIE["DD"]:"";				//日期目录

define("TIMEOUT", 3);					//超时控制，3秒

function kill($pid) {
    return stripos(php_uname('s'), 'win') > -1 ? exec("taskkill /F /T /PID $pid") : exec("kill -9 $pid");
}


// 使用 proc_open() 创建管道并执行命令
function runCMD($command, $stdInput="",$CHECK_TIMEOUT=false)
{
   $process = proc_open($command, [
    0 => ['pipe', 'r'],  				// 标准输入，子进程从此管道中读取数据
    1 => ['pipe', 'w'],  				// 标准输出，子进程向此管道中写入数据
    2 => ['pipe', 'w']   				// 标准错误，子进程向此管道中写入错误信息
   ],$pipes);

   // 从标准输入中读取数据并写入到标准输出
   if (is_resource($process)) {

      stream_set_blocking($pipes[1], 0); 		// 设置 stdout 为非阻塞模式，以便我们可以检查数据是否可读
      stream_set_blocking($pipes[2], 0); 		// 设置 stdout 为非阻塞模式，以便我们可以检查数据是否可读

      if($stdInput) fwrite($pipes[0],$stdInput);		//用户输入
      fclose($pipes[0]); 				//不需要再向子进程传递任何输入了，所以关闭此管道

      $output="";
      $error_output = "";

      $bTimeOut = false;

      if($CHECK_TIMEOUT)
      {
         $startTime = time();

         while(true){
            $status=proc_get_status($process);
            if(!$status['running']) break;

            if(time()-$startTime > TIMEOUT){
               $bTimeOut = true;
               //proc_terminate($pipes[1], 9);	//LINUX/UNIX下适用：发送 SIGKILL 信号终止进程
               kill($status['pid']);		//Windows下使用taskkill来杀进程
               break;
            }
            usleep(10000);
         }
      }

      $output = stream_get_contents($pipes[1]);
      fclose($pipes[1]);
      $error_output = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
      proc_close($process);      			//关闭管道资源

      if($bTimeOut)				//如果超时
         return Array(0=>-1,"resrun"=>$output,"rescomp"=>"程序已运行超过 ".TIMEOUT." 秒，被终止运行，当前得到的运行结果可能不完整。");

      if (!empty($error_output)) {      		//打印错误输出（如果有）
         return Array(0=>-1,"resrun"=>$output,"rescomp"=>$error_output);
      }
      else
         return Array(0=>0,"resrun"=>$output,"rescomp"=>$error_output);
   } else {
      return Array(0=>-1,"resrun"=>$output,"rescomp"=>"启动进程失败");
   }
}

//编译和运行C代码
function runCCode($filename,$code,$input,$args)
{
   saveCode($filename,$code);
   $strCMD="gcc ".$filename." -Wall -o ".$filename.".exe";	//python的subprocess可以执行.out，PHP的不行。
   $retArray=runCMD($strCMD);
   if($retArray[0]==0)
   {
       $retArray=runCMD($filename.".exe".($args?" ".$args:""),$input,true);	//执行的时候，
   }									//    如果有参数，就附加到待执行程序后；
   return $retArray;							//    如果有输入，就作为第二个参数传递。
}

//编译和运行C++代码
function runCPPCode($filename,$code,$input,$args)
{
   saveCode($filename,$code);
   $strCMD="g++ ".$filename." -Wall -o ".$filename.".exe";	//python的subprocess可以执行.out，PHP的不行。
   $retArray=runCMD($strCMD);
   if($retArray[0]==0)
   {
       $retArray=runCMD($filename.".exe".($args?" ".$args:""),$input,true);
   }
   return $retArray;
}

//运行Python代码
function runPythonCode($filename,$code,$input,$args)
{
   saveCode($filename,$code);

   $strCMD="python.exe ".$filename.($args?" ".$args:"");	
   $retArray=runCMD($strCMD,$input,true);
   return $retArray;
}

function saveCode($filename,$code)
{
   file_put_contents($filename,$code);
}

if($strCODE)
{
   $retArr=Array();

   $path=".\\works\\".$classid."\\".$datedir;
   if(!file_exists($path)) mkdir($path,0777,true);

   switch($type)
   {
   case "c":
      $filename=$path."\\".$username."_".$projname.".c";
      $retArr=runCCode($filename,$strCODE,$strINPUT,$strARGS);
   break;

   case "cpp":
      $filename=$path."\\".$username."_".$projname.".cpp";
      $retArr=runCPPCode($filename,$strCODE,$strINPUT,$strARGS);
   break;

   case "py":
      $filename=$path."\\".$username."_".$projname.".py";
      $retArr=runPythonCode($filename,$strCODE,$strINPUT,$strARGS);
   break;
   }
   echo json_encode($retArr);
}

?>