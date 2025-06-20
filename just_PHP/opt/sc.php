<?php
//SaveCode�Զ�����
$type=isset($_GET['t'])?$_GET['t']:"c";

$strCODE=isset($_POST['CODE'])?$_POST['CODE']:"";	//����
$strINPUT=isset($_POST['DATA'])?$_POST['DATA']:"";	//����
$strARGS=isset($_POST['ARGS'])?$_POST['ARGS']:"";	//����
$strCN=isset($_POST['CN'])?$_POST['CN']:"";		//�༶
$taskID=isset($_POST["TKID"])?$_POST["TKID"]:"";		//��Ŀ����

$username=isset($_COOKIE["USERNAME"])?$_COOKIE["USERNAME"]:exit("���ȵ�¼��");	//�û���

//$username="����ʦ";

//$username=iconv("GBK","UTF-8",$username);
define("TIMEOUT", 3);					//��ʱ���ƣ�3��

function kill($pid) {
    return stripos(php_uname('s'), 'win') > -1 ? exec("taskkill /F /T /PID $pid") : exec("kill -9 $pid");
}

//ʹ�� proc_open() �����ܵ���ִ������
//Ҫִ�е���������в�����ֻ��Ҫƴ����$command����Ϳ�����
//$CHECK_TIMEOUT���ڿ��Ƴ����ִ��ʱ���������п��ܴ�������ѭ����Infinite Loops���������������Ĵ�����CPU��������Ҫ��ʱ����ֹ��
function runCMD($command, $stdInput="",$CHECK_TIMEOUT=false){
   $process = proc_open($command, [
      0 => ['pipe', 'r'],  				// ��׼���룬�ӽ��̴Ӵ˹ܵ��ж�ȡ����
      1 => ['pipe', 'w'],  				// ��׼������ӽ�����˹ܵ���д������
      2 => ['pipe', 'w']   				// ��׼�����ӽ�����˹ܵ���д�������Ϣ
   ],$pipes);

   // �ӱ�׼�����ж�ȡ���ݲ�д�뵽��׼���
   if (is_resource($process)) {
      stream_set_blocking($pipes[1], 0); 		// ���� stdout Ϊ������ģʽ���Ա����ǿ��Լ�������Ƿ�ɶ�
      stream_set_blocking($pipes[2], 0); 		// ���� stdout Ϊ������ģʽ���Ա����ǿ��Լ�������Ƿ�ɶ�

      if($stdInput) fwrite($pipes[0],$stdInput);		//�û�����
      fclose($pipes[0]); 				//����Ҫ�����ӽ��̴����κ������ˣ����Թرմ˹ܵ�

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
               //proc_terminate($pipes[1], 9);	//LINUX/UNIX�����ã����� SIGKILL �ź���ֹ����
               kill($status['pid']);		//Windows��ʹ��taskkill��ɱ����
               break;
            }
            usleep(10000);
         }
      }
      $output = stream_get_contents($pipes[1]);
      fclose($pipes[1]);
      $error_output = stream_get_contents($pipes[2]);
      fclose($pipes[2]);
      proc_close($process);      			//�رչܵ���Դ
      if($bTimeOut)				//�����ʱ
         return Array(0=>-1,"resrun"=>$output,"rescomp"=>"���������ѳ��� ".TIMEOUT." �룬����ֹ���У���ǰ�õ������н�����ܲ�������");
      if (!empty($error_output))      		//��ӡ�������������У�
         return Array(0=>-1,"resrun"=>$output,"rescomp"=>$error_output);
      else
         return Array(0=>0,"resrun"=>$output,"rescomp"=>$error_output);
   }
   else {
      return Array(0=>-1,"resrun"=>$output,"rescomp"=>"��������ʧ��");
   }
}

//���������C����
function runCCode($filename,$code,$input,$args) {
   $code=iconv("UTF-8","GBK",$code);
   saveCode($filename,$code);
   $strCMD="gcc ".$filename." -Wall -o ".$filename.".exe";	//python��subprocess����ִ��.out��PHP�Ĳ��С�
   $retArray=runCMD($strCMD,"",true);

   if($retArray[0]==0)
       $retArray=runCMD($filename.".exe".($args?" ".$args:""),$input,true);	//ִ�е�ʱ��
   									//    ����в������͸��ӵ���ִ�г����
   return $retArray;							//    ��������룬����Ϊ�ڶ����������ݡ�
}

//���������C++����
function runCPPCode($filename,$code,$input,$args) {
   $code=iconv("UTF-8","GBK",$code);
   saveCode($filename,$code);
   $strCMD="g++ ".$filename." -Wall -o ".$filename.".exe";	//python��subprocess����ִ��.out��PHP�Ĳ��С�
   $retArray=runCMD($strCMD,"",true);
   if($retArray[0]==0)
       $retArray=runCMD($filename.".exe".($args?" ".$args:""),$input,true);
   return $retArray;
}

//����Python����
function runPythonCode($filename,$code,$input,$args){
   saveCode($filename,$code);
   $strCMD="python.exe ".$filename.($args?" ".$args:"");	
   $retArray=runCMD($strCMD,$input,true);
   return $retArray;
}

//�������
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
        
        // ����ASCII�ַ�
        if ($byte1 <= 0x7F) {
            $result .= chr($byte1);
            $i++;
            continue;
        }
        
        // ���UTF-8���ֽ��ַ�
        $utf8Processed = false;
        $charLength = 0;
        if (($byte1 & 0xE0) == 0xC0) { // 2�ֽ�
            $charLength = 2;
        } elseif (($byte1 & 0xF0) == 0xE0) { // 3�ֽ�
            $charLength = 3;
        } elseif (($byte1 & 0xF8) == 0xF0) { // 4�ֽ�
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
        
        // ����GBK�ַ�
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
        
        // �޷�ʶ����ַ����滻Ϊ?
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