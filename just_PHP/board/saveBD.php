<?php
//��ʦ������϶�С�װ���Ԫ��ʱ���������ݱ��������
//��$type==1ʱ�����С�װ�浵������
//$pergroup=isset($_POST['pg'])?$_POST['pg']:"";			//ÿ������
$type=isset($_POST['t'])?$_POST['t']:"";			//��ǩ��������
$pos=isset($_POST['pos'])?$_POST['pos']:"";			//��ǩ��������
$room=isset($_COOKIE['R'])?$_COOKIE['R']:0;
$classid=isset($_COOKIE['CLASSID'])?$_COOKIE['CLASSID']:0;	//�༶

$GID=isset($_POST['GID'])?intval($_POST['GID']):0;
$memo=isset($_POST['memo'])?$_POST['memo']:"��";

include "../../include/config.inc.php";

$ip=$_SERVER['REMOTE_ADDR'];					//������IP
if($ip=="::1") $ip="127.0.0.1";

if(isset($teacher_room[$ip]))					//���޽�ʦ���ʣ���ֹ����Ȩ���ʵ������ݶ�ʧ��
{
   $room=$teacher_room[$ip][1];					//����

   if(!file_exists("./".$room."/".$GID."/bd/"))
   {
      //mkdir("./".$room."/".$GID."/");
      mkdir("./".$room."/".$GID."/bd/",0744,true);
   }

//   file_put_contents("../../data/config/".$room."/".$classid.".bdpos.dat",$memo."\r\n".$pos);
   file_put_contents("./".$room."/".$classid.".bdpos.dat",$memo."\r\n".$pos);

   if($type)
      file_put_contents("./".$room."/".$GID."/bd/".time(),$memo."\r\n".$pos);

   echo "['OK']";
}
?>