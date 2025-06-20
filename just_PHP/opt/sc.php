<?php
//SaveCode自动备份
$type=isset($_GET['t'])?$_GET['t']:"c";

$strCODE=isset($_POST['CODE'])?$_POST['CODE']:"";	//代码
$strINPUT=isset($_POST['DATA'])?$_POST['DATA']:"";	//输入
$strARGS=isset($_POST['ARGS'])?$_POST['ARGS']:"";	//参数
$strCN=isset($_POST['CN'])?$_POST['CN']:"";		//班级
$taskID=isset($_POST["TKID"])?$_POST["TKID"]:"";		//项目名称

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit("请先登录。");	//用户名

//$username="吴老师";

//$username=iconv("GBK","UTF-8",$username);
define("TIMEOUT", 3);					//超时控制，3秒

function kill($pid) {
    return stripos(php_uname('s'), 'win') > -1 ? exec("taskkill /F /T /PID $pid") : exec("kill -9 $pid");
}

//使用 proc_open() 创建管道并执行命令
//要执行的命令如果有参数，只需要拼接在$command后面就可以了
//$CHECK_TIMEOUT用于控制程序的执行时长，代码中可能存在无限循环（Infinite Loops），这类代码会消耗大量的CPU，所以需要及时地终止。
function runCMD($command, $stdInput="",$CHECK_TIMEOUT=false){
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

      if($CHECK_TIMEOUT) {
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
         return Array(0=>-1,"resrun"=>$output,"rescomp"=>"程序运行已超过 ".TIMEOUT." 秒，被终止运行，当前得到的运行结果可能不完整。");
      if (!empty($error_output))      		//打印错误输出（如果有）
         return Array(0=>-1,"resrun"=>$output,"rescomp"=>$error_output);
      else
         return Array(0=>0,"resrun"=>$output,"rescomp"=>$error_output);
   }
   else {
      return Array(0=>-1,"resrun"=>$output,"rescomp"=>"启动进程失败");
   }
}

//编译和运行C代码
function runCCode($filename,$code,$input,$args) {
   $code=iconv("UTF-8","GBK",$code);
   saveCode($filename,$code);
   $strCMD="gcc ".$filename." -Wall -o ".$filename.".exe";	//python的subprocess可以执行.out，PHP的不行。
   $retArray=runCMD($strCMD,"",true);

   if($retArray[0]==0)
       $retArray=runCMD($filename.".exe".($args?" ".$args:""),$input,true);	//执行的时候，
   									//    如果有参数，就附加到待执行程序后；
   return $retArray;							//    如果有输入，就作为第二个参数传递。
}

//编译和运行C++代码
function runCPPCode($filename,$code,$input,$args) {
   $code=iconv("UTF-8","GBK",$code);
   saveCode($filename,$code);
   $strCMD="g++ ".$filename." -Wall -o ".$filename.".exe";	//python的subprocess可以执行.out，PHP的不行。
   $retArray=runCMD($strCMD,"",true);
   if($retArray[0]==0)
       $retArray=runCMD($filename.".exe".($args?" ".$args:""),$input,true);
   return $retArray;
}

//运行Python代码
function runPythonCode($filename,$code,$input,$args){
   saveCode($filename,$code);
   $strCMD="python.exe ".$filename.($args?" ".$args:"");	
   $retArray=runCMD($strCMD,$input,true);
   return $retArray;
}

//保存代码
function saveCode($filename,$code){
   file_put_contents($filename,$code);
}

function convertMixedToUtf8($str) {
    $bytes = unpack('C*', $str);
    $result = '';
    $i = 1;
    $len = count($bytes);
    
    while ($i <= $len) {
        $byte1 = $bytes[$i];
        
        // 处理ASCII字符
        if ($byte1 <= 0x7F) {
            $result .= chr($byte1);
            $i++;
            continue;
        }
        
        // 检测UTF-8多字节字符
        $utf8Processed = false;
        $charLength = 0;
        if (($byte1 & 0xE0) == 0xC0) { // 2字节
            $charLength = 2;
        } elseif (($byte1 & 0xF0) == 0xE0) { // 3字节
            $charLength = 3;
        } elseif (($byte1 & 0xF8) == 0xF0) { // 4字节
            $charLength = 4;
        }
        
        if ($charLength > 0 && ($i + $charLength - 1) <= $len) {
            $valid = true;
            $charBytes = [$byte1];
            for ($j = 1; $j < $charLength; $j++) {
                $nextByte = $bytes[$i + $j];
                if (($nextByte & 0xC0) != 0x80) {
                    $valid = false;
                    break;
                }
                $charBytes[] = $nextByte;
            }
            if ($valid) {
                $utf8Char = pack('C*', ...$charBytes);
                if (mb_check_encoding($utf8Char, 'UTF-8')) {
                    $result .= $utf8Char;
                    $i += $charLength;
                    $utf8Processed = true;
                }
            }
        }
        
        if ($utf8Processed) {
            continue;
        }
        
        // 处理GBK字符
        if ($byte1 >= 0x81 && $byte1 <= 0xFE && $i + 1 <= $len) {
            $byte2 = $bytes[$i + 1];
            if ($byte2 >= 0x40 && $byte2 <= 0xFE && $byte2 != 0x7F) {
                $gbkChar = pack('C*', $byte1, $byte2);
                $utf8Char = @iconv('GBK', 'UTF-8//IGNORE', $gbkChar);
                if ($utf8Char !== false) {
                    $result .= $utf8Char;
                    $i += 2;
                    continue;
                }
            }
        }
        
        // 无法识别的字符，替换为?
        $result .= '?';
        $i++;
    }
    
    return $result;
}


include "../include/config.inc.php";
//var_dump($taskID);

if($strCODE && $taskID){
   $retArr=Array();
   $path="..".DIRECTORY_SEPARATOR.$student_works_dir.$strCN.DIRECTORY_SEPARATOR;
   if(!file_exists($path)) mkdir($path,0777,true);

   switch($type)
   {
      case "c":
         $filename=$path.DIRECTORY_SEPARATOR.$username."_".$taskID.".c";
         $retArr=runCCode($filename,$strCODE,$strINPUT,$strARGS);
      break;

      case "cpp":
         $filename=$path.DIRECTORY_SEPARATOR.$username."_".$taskID.".cpp";
         $retArr=runCPPCode($filename,$strCODE,$strINPUT,$strARGS);
      break;

      case "py":
         $filename=$path.DIRECTORY_SEPARATOR.$username."_".$taskID.".py";
         $retArr=runPythonCode($filename,$strCODE,$strINPUT,$strARGS);
      break;
   }
   echo $retArr[0]."<+-NOJSON-+>".iconv("GBK","UTF-8//IGNORE",$retArr['resrun'])."<+-NOJSON-+>".iconv("GBK","UTF-8//IGNORE",$retArr['rescomp']);

}
?>